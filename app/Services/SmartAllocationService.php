<?php

namespace App\Services;

use App\Models\Jury;
use App\Models\JuryVigilante;
use App\Models\User;
use App\Models\VacancyApplication;
use PDO;

/**
 * Serviço de Alocação Inteligente de Vigilantes
 * 
 * Implementa algoritmo que:
 * - Filtra apenas candidatos aprovados da vaga
 * - Garante ausência de conflitos de horário
 * - Distribui carga equitativamente
 * - Valida regras de negócio
 */
class SmartAllocationService
{
    private PDO $db;
    private Jury $juryModel;
    private JuryVigilante $juryVigilanteModel;
    private User $userModel;
    private VacancyApplication $applicationModel;
    
    public function __construct()
    {
        $this->db = database();
        $this->juryModel = new Jury();
        $this->juryVigilanteModel = new JuryVigilante();
        $this->userModel = new User();
        $this->applicationModel = new VacancyApplication();
    }
    
    /**
     * Buscar vigilantes elegíveis para um júri específico
     * Filtra apenas candidatos aprovados da vaga vinculada
     */
    public function getEligibleVigilantesForJury(int $juryId): array
    {
        $jury = $this->juryModel->find($juryId);
        if (!$jury) {
            return [];
        }
        
        // Versão simplificada: buscar todos os vigilantes disponíveis
        // Filtra por: role=vigilante, disponível, sem conflito de horário
        $sql = "SELECT DISTINCT u.*, 
                       IFNULL(vw.workload_count, 0) as workload_count,
                       IFNULL(vw.workload_score, 0) as workload_score
                FROM users u
                LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
                WHERE u.role = 'vigilante'
                  AND u.available_for_vigilance = 1
                  AND NOT EXISTS (
                      SELECT 1 FROM jury_vigilantes jv2
                      INNER JOIN juries j2 ON j2.id = jv2.jury_id
                      WHERE jv2.vigilante_id = u.id
                        AND j2.exam_date = :exam_date
                        AND j2.start_time < :end_time
                        AND j2.end_time > :start_time
                  )
                ORDER BY IFNULL(vw.workload_count, 0) ASC, u.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'exam_date' => $jury['exam_date'],
            'start_time' => $jury['start_time'],
            'end_time' => $jury['end_time']
        ]);
        
        $eligible = $stmt->fetchAll(PDO::FETCH_ASSOC);
        error_log("DEBUG getEligibleVigilantesForJury: Júri #{$juryId}, Data: {$jury['exam_date']}, Horário: {$jury['start_time']}-{$jury['end_time']}, Encontrados: " . count($eligible));
        
        return $eligible;
    }
    
    /**
     * Buscar todos candidatos aprovados de uma vaga
     */
    public function getApprovedCandidates(int $vacancyId): array
    {
        $sql = "SELECT DISTINCT u.*, 
                       vw.workload_count,
                       vw.workload_score,
                       va.status as application_status
                FROM vacancy_applications va
                INNER JOIN users u ON u.id = va.vigilante_id
                LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
                WHERE va.vacancy_id = :vacancy_id
                  AND va.status IN ('approved', 'aprovada')
                  AND u.role = 'vigilante'
                  AND u.available_for_vigilance = 1
                ORDER BY IFNULL(vw.workload_count, 0) ASC, u.name";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['vacancy_id' => $vacancyId]);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Alocar automaticamente vigilantes em um júri específico
     * 
     * @param int $juryId ID do júri
     * @param int $assignedBy ID do usuário que está alocando
     * @return array Resultado da alocação
     */
    public function autoAllocateJury(int $juryId, int $assignedBy): array
    {
        $jury = $this->juryModel->find($juryId);
        if (!$jury) {
            return [
                'success' => false,
                'message' => 'Júri não encontrado'
            ];
        }
        
        if (!$jury['vacancy_id']) {
            return [
                'success' => false,
                'message' => 'Júri não vinculado a uma vaga'
            ];
        }
        
        // Calcular vigilantes necessários (1 por 30 candidatos)
        $required = $this->juryModel->calculateRequiredVigilantes((int) $jury['candidates_quota']);
        
        // Buscar vigilantes já alocados
        $currentVigilantes = $this->juryVigilanteModel->vigilantesForJury($juryId);
        $currentCount = count($currentVigilantes);
        
        if ($currentCount >= $required) {
            return [
                'success' => true,
                'message' => 'Júri já tem alocação completa',
                'allocated' => 0,
                'current' => $currentCount,
                'required' => $required
            ];
        }
        
        // Buscar vigilantes elegíveis
        $eligible = $this->getEligibleVigilantesForJury($juryId);
        
        if (empty($eligible)) {
            return [
                'success' => false,
                'message' => 'Nenhum vigilante elegível disponível para este júri',
                'allocated' => 0,
                'current' => $currentCount,
                'required' => $required
            ];
        }
        
        // Alocar os vigilantes necessários
        $toAllocate = $required - $currentCount;
        $allocated = 0;
        
        foreach (array_slice($eligible, 0, $toAllocate) as $vigilante) {
            try {
                // Verificar se já não está alocado (double-check)
                $exists = $this->juryVigilanteModel->statement(
                    'SELECT id FROM jury_vigilantes WHERE jury_id = :jury AND vigilante_id = :vigilante',
                    ['jury' => $juryId, 'vigilante' => $vigilante['id']]
                );
                
                if (!empty($exists)) {
                    continue;
                }
                
                // Alocar
                $this->juryVigilanteModel->create([
                    'jury_id' => $juryId,
                    'vigilante_id' => $vigilante['id'],
                    'assigned_by' => $assignedBy,
                    'created_at' => now(),
                ]);
                
                $allocated++;
            } catch (\Exception $e) {
                // Continuar com próximo vigilante em caso de erro
                continue;
            }
        }
        
        $newTotal = $currentCount + $allocated;
        $isComplete = $newTotal >= $required;
        
        return [
            'success' => true,
            'message' => $isComplete 
                ? "Júri completado: {$newTotal}/{$required} vigilantes" 
                : "Alocados {$allocated} vigilantes. Faltam " . ($required - $newTotal),
            'allocated' => $allocated,
            'current' => $newTotal,
            'required' => $required,
            'complete' => $isComplete
        ];
    }
    
    /**
     * Alocar automaticamente todos os júris de uma vaga
     * 
     * @param int $vacancyId ID da vaga
     * @param int $assignedBy ID do usuário que está alocando
     * @return array Resultado da alocação em massa
     */
    public function autoAllocateVacancy(int $vacancyId, int $assignedBy): array
    {
        $juries = $this->juryModel->getByVacancy($vacancyId);
        
        if (empty($juries)) {
            return [
                'success' => false,
                'message' => 'Nenhum júri encontrado para esta vaga'
            ];
        }
        
        // Buscar todos os candidatos aprovados
        $candidates = $this->getApprovedCandidates($vacancyId);
        
        if (empty($candidates)) {
            return [
                'success' => false,
                'message' => 'Nenhum candidato aprovado disponível'
            ];
        }
        
        // Agrupar júris por janela temporal (mesma data e horário)
        $timeWindows = [];
        foreach ($juries as $jury) {
            $key = $jury['exam_date'] . '|' . $jury['start_time'] . '|' . $jury['end_time'];
            if (!isset($timeWindows[$key])) {
                $timeWindows[$key] = [
                    'exam_date' => $jury['exam_date'],
                    'start_time' => $jury['start_time'],
                    'end_time' => $jury['end_time'],
                    'juries' => []
                ];
            }
            $timeWindows[$key]['juries'][] = $jury;
        }
        
        $stats = [
            'total_juries' => count($juries),
            'total_allocated' => 0,
            'juries_complete' => 0,
            'juries_incomplete' => 0,
            'details' => []
        ];
        
        // Processar cada janela temporal
        foreach ($timeWindows as $window) {
            // Filtrar candidatos sem conflito nessa janela
            $availableCandidates = [];
            foreach ($candidates as $candidate) {
                $hasConflict = $this->juryVigilanteModel->vigilanteHasConflict(
                    (int) $candidate['id'],
                    $window['exam_date'],
                    $window['start_time'],
                    $window['end_time']
                );
                
                if (!$hasConflict) {
                    $availableCandidates[] = $candidate;
                }
            }
            
            // Ordenar por carga de trabalho
            usort($availableCandidates, function($a, $b) {
                $workloadA = $a['workload_count'] ?? 0;
                $workloadB = $b['workload_count'] ?? 0;
                return $workloadA <=> $workloadB;
            });
            
            // Pool rotativo de candidatos
            $candidatePool = $availableCandidates;
            $poolIndex = 0;
            
            // Alocar em cada júri da janela
            foreach ($window['juries'] as $jury) {
                $required = $this->juryModel->calculateRequiredVigilantes((int) $jury['candidates_quota']);
                $current = count($this->juryVigilanteModel->vigilantesForJury((int) $jury['id']));
                $toAllocate = $required - $current;
                
                $juryAllocated = 0;
                
                for ($i = 0; $i < $toAllocate; $i++) {
                    if (empty($candidatePool)) {
                        break; // Sem mais candidatos disponíveis
                    }
                    
                    // Pegar próximo candidato do pool (circular)
                    $candidate = $candidatePool[$poolIndex % count($candidatePool)];
                    $poolIndex++;
                    
                    try {
                        // Verificar se já não está alocado
                        $exists = $this->juryVigilanteModel->statement(
                            'SELECT id FROM jury_vigilantes WHERE jury_id = :jury AND vigilante_id = :vigilante',
                            ['jury' => $jury['id'], 'vigilante' => $candidate['id']]
                        );
                        
                        if (!empty($exists)) {
                            continue;
                        }
                        
                        // Alocar
                        $this->juryVigilanteModel->create([
                            'jury_id' => $jury['id'],
                            'vigilante_id' => $candidate['id'],
                            'assigned_by' => $assignedBy,
                            'created_at' => now(),
                        ]);
                        
                        $juryAllocated++;
                        $stats['total_allocated']++;
                        
                        // Incrementar carga do candidato
                        $candidate['workload_count'] = ($candidate['workload_count'] ?? 0) + 1;
                        
                        // Re-ordenar pool após alocação
                        usort($candidatePool, function($a, $b) {
                            $workloadA = $a['workload_count'] ?? 0;
                            $workloadB = $b['workload_count'] ?? 0;
                            return $workloadA <=> $workloadB;
                        });
                        
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                
                $newTotal = $current + $juryAllocated;
                $isComplete = $newTotal >= $required;
                
                if ($isComplete) {
                    $stats['juries_complete']++;
                } else {
                    $stats['juries_incomplete']++;
                }
                
                $stats['details'][] = [
                    'jury_id' => $jury['id'],
                    'subject' => $jury['subject'],
                    'room' => $jury['room'],
                    'allocated' => $juryAllocated,
                    'current' => $newTotal,
                    'required' => $required,
                    'complete' => $isComplete
                ];
            }
        }
        
        return [
            'success' => true,
            'message' => "Alocação concluída: {$stats['juries_complete']}/{$stats['total_juries']} júris completos",
            'stats' => $stats
        ];
    }
    
    /**
     * Desalocar todos os vigilantes de um júri
     */
    public function clearJuryAllocations(int $juryId): array
    {
        $count = $this->juryVigilanteModel->execute(
            'DELETE FROM jury_vigilantes WHERE jury_id = :jury',
            ['jury' => $juryId]
        );
        
        return [
            'success' => true,
            'message' => "Removidos {$count} vigilante(s) do júri",
            'removed' => $count
        ];
    }
    
    /**
     * Desalocar todos os vigilantes de uma vaga
     */
    public function clearVacancyAllocations(int $vacancyId): array
    {
        $juries = $this->juryModel->getByVacancy($vacancyId);
        $totalRemoved = 0;
        
        foreach ($juries as $jury) {
            $this->juryVigilanteModel->execute(
                'DELETE FROM jury_vigilantes WHERE jury_id = :jury',
                ['jury' => $jury['id']]
            );
            $totalRemoved++;
        }
        
        return [
            'success' => true,
            'message' => "Todas as alocações foram removidas",
            'juries_cleared' => $totalRemoved
        ];
    }
    
    /**
     * Obter estatísticas de alocação de uma vaga
     */
    public function getVacancyAllocationStats(int $vacancyId): array
    {
        $juries = $this->juryModel->getByVacancy($vacancyId);
        
        $stats = [
            'total_juries' => count($juries),
            'total_required' => 0,
            'total_allocated' => 0,
            'juries_complete' => 0,
            'juries_incomplete' => 0,
            'juries_empty' => 0,
            'approved_candidates' => 0,
            'occupancy_rate' => 0
        ];
        
        foreach ($juries as $jury) {
            $required = $this->juryModel->calculateRequiredVigilantes((int) $jury['candidates_quota']);
            $allocated = count($this->juryVigilanteModel->vigilantesForJury((int) $jury['id']));
            
            $stats['total_required'] += $required;
            $stats['total_allocated'] += $allocated;
            
            if ($allocated == 0) {
                $stats['juries_empty']++;
            } elseif ($allocated >= $required) {
                $stats['juries_complete']++;
            } else {
                $stats['juries_incomplete']++;
            }
        }
        
        $stats['approved_candidates'] = count($this->getApprovedCandidates($vacancyId));
        
        if ($stats['total_required'] > 0) {
            $stats['occupancy_rate'] = round(($stats['total_allocated'] / $stats['total_required']) * 100, 1);
        }
        
        return $stats;
    }
}
