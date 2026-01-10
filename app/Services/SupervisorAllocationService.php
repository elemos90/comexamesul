<?php

namespace App\Services;

use App\Models\Jury;
use App\Models\User;

/**
 * Serviço para alocação equilibrada de supervisores por blocos de júris
 * 
 * Responsabilidades:
 * - Calcular quantos supervisores são necessários para uma disciplina
 * - Seleccionar supervisores elegíveis ordenados por carga de trabalho
 * - Distribuir júris de forma equilibrada entre supervisores
 * - Suportar redistribuição manual
 */
class SupervisorAllocationService
{
    private array $config;
    private Jury $juryModel;
    private User $userModel;

    public function __construct()
    {
        $configPath = dirname(__DIR__) . '/Config/supervisor_settings.php';
        $this->config = file_exists($configPath) ? require $configPath : [
            'max_juries_per_supervisor' => 10,
            'min_supervisors_per_discipline' => 1,
            'allow_manual_override' => true,
            'balance_by_global_load' => true,
            'log_auto_allocation' => true,
        ];

        $this->juryModel = new Jury();
        $this->userModel = new User();
    }

    /**
     * Calcular quantos supervisores são necessários para um número de júris
     * 
     * @param int $juryCount Número total de júris
     * @return int Número de supervisores necessários
     */
    public function calculateRequiredSupervisors(int $juryCount): int
    {
        $max = $this->config['max_juries_per_supervisor'];
        $min = $this->config['min_supervisors_per_discipline'];

        if ($juryCount <= 0) {
            return 0;
        }

        $required = (int) ceil($juryCount / $max);

        return max($required, $min);
    }

    /**
     * Obter supervisores elegíveis para um determinado horário
     * 
     * @param string $examDate Data do exame (Y-m-d)
     * @param string $startTime Hora de início (H:i:s)
     * @param string $endTime Hora de fim (H:i:s)
     * @param int|null $vacancyId ID da vaga para calcular carga global
     * @return array Lista de supervisores ordenados por carga
     */
    public function getEligibleSupervisors(
        string $examDate,
        string $startTime,
        string $endTime,
        ?int $vacancyId = null
    ): array {
        // Buscar todos os utilizadores elegíveis para supervisão
        $sql = "SELECT u.id, u.name, u.phone, u.email,
                       (SELECT COUNT(*) FROM juries j2 
                        WHERE j2.supervisor_id = u.id" .
            ($vacancyId ? " AND j2.vacancy_id = :vacancy_id_count" : "") .
            ") as global_load
                FROM users u
                WHERE u.role = 'vigilante'
                  AND u.available_for_vigilance = 1
                  AND u.id NOT IN (
                      -- Excluir supervisores com conflito de horário
                      SELECT DISTINCT j.supervisor_id 
                      FROM juries j
                      WHERE j.supervisor_id IS NOT NULL
                        AND j.exam_date = :exam_date
                        AND j.start_time < :end_time
                        AND j.end_time > :start_time
                  )
                  AND u.id NOT IN (
                      -- Excluir vigilantes alocados com conflito de horário
                      SELECT DISTINCT jv.vigilante_id 
                      FROM jury_vigilantes jv
                      INNER JOIN juries j ON j.id = jv.jury_id
                      WHERE j.exam_date = :exam_date2
                        AND j.start_time < :end_time2
                        AND j.end_time > :start_time2
                  )
                ORDER BY global_load ASC, u.name ASC";

        $params = [
            'exam_date' => $examDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'exam_date2' => $examDate,
            'start_time2' => $startTime,
            'end_time2' => $endTime,
        ];

        if ($vacancyId) {
            $params['vacancy_id_count'] = $vacancyId;
        }

        return $this->juryModel->statement($sql, $params);
    }

    /**
     * Alocar supervisores de forma equilibrada para um conjunto de júris
     * 
     * @param array $juryIds IDs dos júris a alocar
     * @param int|null $vacancyId ID da vaga
     * @return array Resultado da alocação
     */
    public function allocateBlockedSupervisors(array $juryIds, ?int $vacancyId = null): array
    {
        if (empty($juryIds)) {
            return [
                'success' => false,
                'message' => 'Nenhum júri para alocar.',
                'allocations' => []
            ];
        }

        // Buscar dados do primeiro júri para determinar horário
        $firstJury = $this->juryModel->find($juryIds[0]);
        if (!$firstJury) {
            return [
                'success' => false,
                'message' => 'Júri não encontrado.',
                'allocations' => []
            ];
        }

        $examDate = $firstJury['exam_date'];
        $startTime = $firstJury['start_time'];
        $endTime = $firstJury['end_time'];
        $subject = $firstJury['subject'];

        $juryCount = count($juryIds);
        $requiredSupervisors = $this->calculateRequiredSupervisors($juryCount);

        // Buscar supervisores elegíveis
        $eligibleSupervisors = $this->getEligibleSupervisors(
            $examDate,
            $startTime,
            $endTime,
            $vacancyId
        );

        if (count($eligibleSupervisors) < $requiredSupervisors) {
            return [
                'success' => false,
                'message' => "Supervisores insuficientes. Necessários: {$requiredSupervisors}, Disponíveis: " . count($eligibleSupervisors),
                'allocations' => [],
                'required' => $requiredSupervisors,
                'available' => count($eligibleSupervisors)
            ];
        }

        // Seleccionar os supervisores necessários (já ordenados por carga)
        $selectedSupervisors = array_slice($eligibleSupervisors, 0, $requiredSupervisors);

        // Distribuir júris de forma equilibrada
        $distribution = $this->distributeJuriesBalanced($juryIds, $selectedSupervisors);

        // Persistir alocações
        $allocations = [];
        foreach ($distribution as $supervisorId => $supervisorJuryIds) {
            foreach ($supervisorJuryIds as $juryId) {
                $this->juryModel->update($juryId, [
                    'supervisor_id' => $supervisorId,
                    'updated_at' => now()
                ]);

                $allocations[] = [
                    'jury_id' => $juryId,
                    'supervisor_id' => $supervisorId
                ];
            }
        }

        // Log da actividade se configurado
        if ($this->config['log_auto_allocation']) {
            ActivityLogger::log('supervisor_allocation', null, 'auto_allocate', [
                'subject' => $subject,
                'exam_date' => $examDate,
                'start_time' => $startTime,
                'jury_count' => $juryCount,
                'supervisor_count' => $requiredSupervisors,
                'distribution' => array_map('count', $distribution)
            ]);
        }

        return [
            'success' => true,
            'message' => "Alocados {$juryCount} júris a {$requiredSupervisors} supervisor(es).",
            'allocations' => $allocations,
            'distribution' => $this->formatDistributionSummary($distribution, $selectedSupervisors)
        ];
    }

    /**
     * Distribuir júris de forma equilibrada entre supervisores
     * 
     * @param array $juryIds IDs dos júris
     * @param array $supervisors Lista de supervisores seleccionados
     * @return array Mapeamento supervisorId => [juryIds]
     */
    private function distributeJuriesBalanced(array $juryIds, array $supervisors): array
    {
        $distribution = [];
        $supervisorCount = count($supervisors);
        $juryCount = count($juryIds);

        // Inicializar distribuição
        foreach ($supervisors as $supervisor) {
            $distribution[$supervisor['id']] = [];
        }

        // Calcular base e resto
        $juriesPerSupervisor = (int) floor($juryCount / $supervisorCount);
        $remainder = $juryCount % $supervisorCount;

        $juryIndex = 0;
        $supervisorIndex = 0;

        foreach ($supervisors as $supervisor) {
            // Quantos júris este supervisor recebe
            $count = $juriesPerSupervisor;

            // Distribuir o resto entre os primeiros supervisores
            if ($supervisorIndex < $remainder) {
                $count++;
            }

            // Atribuir júris
            for ($i = 0; $i < $count && $juryIndex < $juryCount; $i++) {
                $distribution[$supervisor['id']][] = $juryIds[$juryIndex];
                $juryIndex++;
            }

            $supervisorIndex++;
        }

        return $distribution;
    }

    /**
     * Formatar resumo da distribuição para resposta
     */
    private function formatDistributionSummary(array $distribution, array $supervisors): array
    {
        $summary = [];
        $supervisorMap = [];

        foreach ($supervisors as $supervisor) {
            $supervisorMap[$supervisor['id']] = $supervisor['name'];
        }

        foreach ($distribution as $supervisorId => $juryIds) {
            $summary[] = [
                'supervisor_id' => $supervisorId,
                'supervisor_name' => $supervisorMap[$supervisorId] ?? 'Desconhecido',
                'jury_count' => count($juryIds),
                'jury_ids' => $juryIds
            ];
        }

        return $summary;
    }

    /**
     * Obter carga actual de um supervisor numa vaga
     * 
     * @param int $supervisorId ID do supervisor
     * @param int $vacancyId ID da vaga
     * @return int Número de júris supervisionados
     */
    public function getSupervisorLoad(int $supervisorId, int $vacancyId): int
    {
        $result = $this->juryModel->statement(
            "SELECT COUNT(*) as count FROM juries 
             WHERE supervisor_id = :supervisor_id AND vacancy_id = :vacancy_id",
            ['supervisor_id' => $supervisorId, 'vacancy_id' => $vacancyId]
        );

        return (int) ($result[0]['count'] ?? 0);
    }

    /**
     * Obter todos os júris de uma disciplina/horário (bloco)
     * 
     * @param string $subject Disciplina
     * @param string $examDate Data do exame
     * @param string $startTime Hora de início
     * @param string $endTime Hora de fim
     * @param int|null $vacancyId ID da vaga
     * @return array Lista de júris
     */
    public function getJuriesInBlock(
        string $subject,
        string $examDate,
        string $startTime,
        string $endTime,
        ?int $vacancyId = null
    ): array {
        $sql = "SELECT j.*, u.name as supervisor_name
                FROM juries j
                LEFT JOIN users u ON u.id = j.supervisor_id
                WHERE j.subject = :subject
                  AND j.exam_date = :exam_date
                  AND j.start_time = :start_time
                  AND j.end_time = :end_time";

        $params = [
            'subject' => $subject,
            'exam_date' => $examDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
        ];

        if ($vacancyId) {
            $sql .= " AND j.vacancy_id = :vacancy_id";
            $params['vacancy_id'] = $vacancyId;
        }

        $sql .= " ORDER BY j.location, j.room";

        return $this->juryModel->statement($sql, $params);
    }

    /**
     * Redistribuir um júri para outro supervisor (manual)
     * 
     * @param int $juryId ID do júri
     * @param int $newSupervisorId Novo supervisor ID
     * @return array Resultado
     */
    public function reassignJury(int $juryId, int $newSupervisorId): array
    {
        if (!$this->config['allow_manual_override']) {
            return [
                'success' => false,
                'message' => 'Redistribuição manual não permitida.'
            ];
        }

        $jury = $this->juryModel->find($juryId);
        if (!$jury) {
            return [
                'success' => false,
                'message' => 'Júri não encontrado.'
            ];
        }

        $oldSupervisorId = $jury['supervisor_id'];

        // Verificar se novo supervisor é válido (qualquer vigilante pode ser supervisor)
        if ($newSupervisorId > 0) {
            $supervisor = $this->userModel->find($newSupervisorId);
            if (!$supervisor || $supervisor['role'] !== 'vigilante') {
                return [
                    'success' => false,
                    'message' => 'Apenas vigilantes podem ser supervisores.'
                ];
            }

            // Verificar conflito de horário
            $conflicts = $this->juryModel->statement(
                "SELECT id FROM juries 
                 WHERE supervisor_id = :supervisor_id
                   AND exam_date = :exam_date
                   AND id != :jury_id
                   AND start_time < :end_time
                   AND end_time > :start_time
                   AND NOT (subject = :subject AND start_time = :start_time2 AND end_time = :end_time2)",
                [
                    'supervisor_id' => $newSupervisorId,
                    'exam_date' => $jury['exam_date'],
                    'jury_id' => $juryId,
                    'start_time' => $jury['start_time'],
                    'end_time' => $jury['end_time'],
                    'subject' => $jury['subject'],
                    'start_time2' => $jury['start_time'],
                    'end_time2' => $jury['end_time']
                ]
            );

            if (!empty($conflicts)) {
                return [
                    'success' => false,
                    'message' => 'Supervisor tem conflito de horário.'
                ];
            }
        }

        // Actualizar
        $this->juryModel->update($juryId, [
            'supervisor_id' => $newSupervisorId > 0 ? $newSupervisorId : null,
            'updated_at' => now()
        ]);

        if ($this->config['log_auto_allocation']) {
            ActivityLogger::log('supervisor_allocation', $juryId, 'reassign', [
                'old_supervisor_id' => $oldSupervisorId,
                'new_supervisor_id' => $newSupervisorId
            ]);
        }

        return [
            'success' => true,
            'message' => 'Júri reassociado com sucesso.'
        ];
    }

    /**
     * Obter estatísticas de supervisão para uma vaga
     * 
     * @param int $vacancyId ID da vaga
     * @return array Estatísticas por supervisor
     */
    public function getSupervisionStats(int $vacancyId): array
    {
        $sql = "SELECT 
                    u.id as supervisor_id,
                    u.name as supervisor_name,
                    COUNT(j.id) as jury_count,
                    :max_load as max_load,
                    CASE 
                        WHEN COUNT(j.id) > :max_load2 THEN 'overloaded'
                        WHEN COUNT(j.id) = :max_load3 THEN 'full'
                        ELSE 'available'
                    END as status
                FROM users u
                INNER JOIN juries j ON j.supervisor_id = u.id
                WHERE j.vacancy_id = :vacancy_id
                GROUP BY u.id, u.name
                ORDER BY jury_count DESC, u.name";

        $maxLoad = $this->config['max_juries_per_supervisor'];

        return $this->juryModel->statement($sql, [
            'vacancy_id' => $vacancyId,
            'max_load' => $maxLoad,
            'max_load2' => $maxLoad,
            'max_load3' => $maxLoad
        ]);
    }

    /**
     * Obter configuração actual
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
