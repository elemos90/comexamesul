<?php

namespace App\Services;

use App\Models\Jury;
use App\Models\JuryVigilante;
use App\Models\User;
use App\Services\ActivityLogger;
use PDO;

/**
 * Serviço de Alocação Inteligente de Vigilantes e Supervisores
 * 
 * Implementa:
 * - Validação de conflitos de horário
 * - Verificação de capacidade
 * - Algoritmo Greedy para equilíbrio de carga
 * - Auto-alocação (rápida e completa)
 * - Métricas e KPIs de alocação
 */
class AllocationService
{
    private PDO $db;
    
    // Pesos para cálculo de score de carga
    const WEIGHT_VIGILANCE = 1;
    const WEIGHT_SUPERVISION = 2;
    
    // Tolerância para equilíbrio (desvio padrão aceitável)
    const BALANCE_TOLERANCE = 1.0;
    
    public function __construct()
    {
        $this->db = database();
    }
    
    /**
     * Verifica se um vigilante pode ser alocado a um júri
     * 
     * @param int $vigilanteId
     * @param int $juryId
     * @return array ['can_assign' => bool, 'reason' => string|null, 'severity' => string]
     */
    public function canAssignVigilante(int $vigilanteId, int $juryId): array
    {
        // 1. Verificar se vigilante existe e está disponível
        $vigilante = (new User())->find($vigilanteId);
        if (!$vigilante || $vigilante['role'] !== 'vigilante') {
            return [
                'can_assign' => false,
                'reason' => 'Vigilante inválido',
                'severity' => 'error'
            ];
        }
        
        if ((int) $vigilante['available_for_vigilance'] !== 1) {
            return [
                'can_assign' => false,
                'reason' => 'Vigilante sem disponibilidade ativa',
                'severity' => 'error'
            ];
        }
        
        // 2. Verificar se júri existe
        $jury = (new Jury())->find($juryId);
        if (!$jury) {
            return [
                'can_assign' => false,
                'reason' => 'Júri não encontrado',
                'severity' => 'error'
            ];
        }
        
        // 3. Verificar se já está alocado neste júri
        $stmt = $this->db->prepare(
            "SELECT id FROM jury_vigilantes WHERE jury_id = :jury AND vigilante_id = :vigilante"
        );
        $stmt->execute(['jury' => $juryId, 'vigilante' => $vigilanteId]);
        if ($stmt->fetch()) {
            return [
                'can_assign' => false,
                'reason' => 'Vigilante já alocado neste júri',
                'severity' => 'warning'
            ];
        }
        
        // 4. Verificar capacidade do júri
        $capacity = (int) ($jury['vigilantes_capacity'] ?? 2);
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM jury_vigilantes WHERE jury_id = :jury"
        );
        $stmt->execute(['jury' => $juryId]);
        $currentCount = (int) $stmt->fetchColumn();
        
        if ($currentCount >= $capacity) {
            return [
                'can_assign' => false,
                'reason' => "Capacidade máxima atingida ({$capacity} vigilantes)",
                'severity' => 'error'
            ];
        }
        
        // 5. Verificar conflitos de horário
        $hasConflict = (new JuryVigilante())->vigilanteHasConflict(
            $vigilanteId,
            $jury['exam_date'],
            $jury['start_time'],
            $jury['end_time']
        );
        
        if ($hasConflict) {
            return [
                'can_assign' => false,
                'reason' => 'Conflito de horário: vigilante já alocado em júri no mesmo período',
                'severity' => 'error'
            ];
        }
        
        // 6. Verificar equilíbrio de carga (warning, não bloqueia)
        $workload = $this->getVigilanteWorkload($vigilanteId);
        $avgWorkload = $this->getAverageWorkload();
        
        if ($workload['workload_score'] > $avgWorkload + self::BALANCE_TOLERANCE) {
            return [
                'can_assign' => true,
                'reason' => "Vigilante tem carga acima da média ({$workload['workload_score']} vs {$avgWorkload})",
                'severity' => 'warning'
            ];
        }
        
        return [
            'can_assign' => true,
            'reason' => null,
            'severity' => 'success'
        ];
    }
    
    /**
     * Verifica se um supervisor pode ser alocado a um júri
     */
    public function canAssignSupervisor(int $supervisorId, int $juryId): array
    {
        // 1. Verificar se supervisor existe e é elegível
        $supervisor = (new User())->find($supervisorId);
        if (!$supervisor || (int) $supervisor['supervisor_eligible'] !== 1) {
            return [
                'can_assign' => false,
                'reason' => 'Supervisor inválido ou não elegível',
                'severity' => 'error'
            ];
        }
        
        // 2. Verificar se júri existe
        $jury = (new Jury())->find($juryId);
        if (!$jury) {
            return [
                'can_assign' => false,
                'reason' => 'Júri não encontrado',
                'severity' => 'error'
            ];
        }
        
        // 3. Verificar conflitos de horário
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM juries
            WHERE supervisor_id = :supervisor
              AND exam_date = :date
              AND id != :jury_id
              AND (start_time < :end_time AND :start_time < end_time)
        ");
        $stmt->execute([
            'supervisor' => $supervisorId,
            'date' => $jury['exam_date'],
            'jury_id' => $juryId,
            'end_time' => $jury['end_time'],
            'start_time' => $jury['start_time']
        ]);
        
        if ((int) $stmt->fetchColumn() > 0) {
            return [
                'can_assign' => false,
                'reason' => 'Conflito de horário: supervisor já alocado em júri no mesmo período',
                'severity' => 'error'
            ];
        }
        
        // 4. Verificar equilíbrio de carga
        $workload = $this->getVigilanteWorkload($supervisorId);
        $avgWorkload = $this->getAverageWorkload();
        
        if ($workload['workload_score'] > $avgWorkload + self::BALANCE_TOLERANCE * 2) {
            return [
                'can_assign' => true,
                'reason' => "Supervisor tem carga acima da média",
                'severity' => 'warning'
            ];
        }
        
        return [
            'can_assign' => true,
            'reason' => null,
            'severity' => 'success'
        ];
    }
    
    /**
     * Aloca vigilante a um júri com transação
     */
    public function assignVigilante(int $vigilanteId, int $juryId, int $assignedBy): array
    {
        $validation = $this->canAssignVigilante($vigilanteId, $juryId);
        
        if (!$validation['can_assign']) {
            return [
                'success' => false,
                'message' => $validation['reason']
            ];
        }
        
        try {
            $this->db->beginTransaction();
            
            $juryVigilante = new JuryVigilante();
            $juryVigilante->create([
                'jury_id' => $juryId,
                'vigilante_id' => $vigilanteId,
                'assigned_by' => $assignedBy,
                'created_at' => now()
            ]);
            
            ActivityLogger::log('jury_vigilantes', $juryId, 'assign', [
                'vigilante_id' => $vigilanteId,
                'assigned_by' => $assignedBy
            ]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Vigilante alocado com sucesso',
                'warning' => $validation['severity'] === 'warning' ? $validation['reason'] : null
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return [
                'success' => false,
                'message' => 'Erro ao alocar vigilante: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Remove vigilante de um júri
     */
    public function unassignVigilante(int $vigilanteId, int $juryId, int $removedBy): array
    {
        try {
            $this->db->beginTransaction();
            
            $stmt = $this->db->prepare(
                "DELETE FROM jury_vigilantes WHERE jury_id = :jury AND vigilante_id = :vigilante"
            );
            $stmt->execute(['jury' => $juryId, 'vigilante' => $vigilanteId]);
            
            ActivityLogger::log('jury_vigilantes', $juryId, 'unassign', [
                'vigilante_id' => $vigilanteId,
                'removed_by' => $removedBy
            ]);
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Vigilante removido com sucesso'];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Erro ao remover vigilante'];
        }
    }
    
    /**
     * Troca vigilantes entre júris ou dentro do mesmo júri
     */
    public function swapVigilantes(int $vigilante1Id, int $vigilante2Id, int $juryId, int $swappedBy): array
    {
        try {
            $this->db->beginTransaction();
            
            // Remover ambos temporariamente - CORRIGIDO: usando prepared statement para prevenir SQL injection
            $stmtDelete = $this->db->prepare(
                "DELETE FROM jury_vigilantes WHERE jury_id = :jury AND vigilante_id IN (:vig1, :vig2)"
            );
            $stmtDelete->execute([
                'jury' => $juryId,
                'vig1' => $vigilante1Id,
                'vig2' => $vigilante2Id
            ]);
            
            // Realocar invertido
            $stmt = $this->db->prepare(
                "INSERT INTO jury_vigilantes (jury_id, vigilante_id, assigned_by, created_at) VALUES (:jury, :vigilante, :by, :at)"
            );
            
            $stmt->execute([
                'jury' => $juryId,
                'vigilante' => $vigilante2Id,
                'by' => $swappedBy,
                'at' => now()
            ]);
            
            ActivityLogger::log('jury_vigilantes', $juryId, 'swap', [
                'from' => $vigilante1Id,
                'to' => $vigilante2Id,
                'swapped_by' => $swappedBy
            ]);
            
            $this->db->commit();
            
            return ['success' => true, 'message' => 'Vigilantes trocados com sucesso'];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Erro ao trocar vigilantes'];
        }
    }
    
    /**
     * Auto-aloca vigilantes em um júri específico usando algoritmo Greedy
     * 
     * @param int $juryId
     * @param int $allocatedBy
     * @return array
     */
    public function autoAllocateJury(int $juryId, int $allocatedBy): array
    {
        $jury = (new Jury())->find($juryId);
        if (!$jury) {
            return ['success' => false, 'message' => 'Júri não encontrado'];
        }
        
        $capacity = (int) ($jury['vigilantes_capacity'] ?? 2);
        
        // Buscar vigilantes elegíveis ordenados por carga (menor carga primeiro)
        // SELECT * seguro: vw_eligible_vigilantes é uma VIEW com campos específicos
        $stmt = $this->db->prepare("
            SELECT * FROM vw_eligible_vigilantes 
            WHERE jury_id = :jury AND has_conflict = 0
            ORDER BY workload_score ASC, RAND()
            LIMIT :capacity
        ");
        $stmt->bindValue(':jury', $juryId, PDO::PARAM_INT);
        $stmt->bindValue(':capacity', $capacity, PDO::PARAM_INT);
        $stmt->execute();
        $eligibles = $stmt->fetchAll();
        
        if (empty($eligibles)) {
            return ['success' => false, 'message' => 'Nenhum vigilante elegível encontrado'];
        }
        
        $allocated = 0;
        $errors = [];
        
        try {
            $this->db->beginTransaction();
            
            foreach ($eligibles as $eligible) {
                $result = $this->assignVigilante((int) $eligible['vigilante_id'], $juryId, $allocatedBy);
                if ($result['success']) {
                    $allocated++;
                } else {
                    $errors[] = $result['message'];
                }
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => "Auto-alocação concluída: {$allocated} vigilante(s) alocado(s)",
                'allocated' => $allocated,
                'errors' => $errors
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Erro na auto-alocação: ' . $e->getMessage()];
        }
    }
    
    /**
     * Auto-aloca vigilantes em todos os júris de uma disciplina (OTIMIZADO)
     * Usa processamento em lote para máxima performance
     */
    public function autoAllocateDiscipline(string $subject, string $examDate, int $allocatedBy): array
    {
        $startTime = microtime(true);
        
        // Buscar todos os júris da disciplina com detalhes
        $stmt = $this->db->prepare("
            SELECT j.id, j.exam_date, j.start_time, j.end_time,
                   COALESCE((SELECT COUNT(*) FROM jury_vigilantes WHERE jury_id = j.id), 0) as allocated,
                   COALESCE(j.vigilantes_capacity, 2) as capacity
            FROM juries j
            WHERE j.subject = :subject AND j.exam_date = :date
            ORDER BY j.start_time, j.room
        ");
        $stmt->execute(['subject' => $subject, 'date' => $examDate]);
        $juries = $stmt->fetchAll();
        
        if (empty($juries)) {
            return ['success' => false, 'message' => 'Nenhum júri encontrado'];
        }
        
        // Buscar todos os vigilantes disponíveis com carga (uma única query)
        $vigilantes = $this->getAvailableVigilantesForDiscipline($examDate);
        
        if (empty($vigilantes)) {
            return ['success' => false, 'message' => 'Nenhum vigilante disponível'];
        }
        
        // Ordenar vigilantes por carga (menor primeiro)
        usort($vigilantes, fn($a, $b) => $a['workload_score'] <=> $b['workload_score']);
        
        $totalAllocated = 0;
        $juriesProcessed = 0;
        $allocations = [];
        $vigilanteIndex = 0;
        
        try {
            $this->db->beginTransaction();
            
            // Algoritmo Greedy otimizado: alocar em batch
            foreach ($juries as $jury) {
                $needed = (int) $jury['capacity'] - (int) $jury['allocated'];
                
                if ($needed <= 0) {
                    continue; // Júri já preenchido
                }
                
                $juryAllocations = 0;
                
                // Buscar vigilantes elegíveis para este júri
                while ($needed > 0 && $vigilanteIndex < count($vigilantes)) {
                    $vigilante = $vigilantes[$vigilanteIndex];
                    
                    // Verificar conflito de horário
                    if ($this->hasTimeConflict(
                        (int) $vigilante['id'],
                        $jury['exam_date'],
                        $jury['start_time'],
                        $jury['end_time'],
                        $allocations
                    )) {
                        $vigilanteIndex++;
                        continue;
                    }
                    
                    // Alocar!
                    $allocations[] = [
                        'jury_id' => $jury['id'],
                        'vigilante_id' => $vigilante['id'],
                        'exam_date' => $jury['exam_date'],
                        'start_time' => $jury['start_time'],
                        'end_time' => $jury['end_time']
                    ];
                    
                    $juryAllocations++;
                    $totalAllocated++;
                    $needed--;
                    $vigilanteIndex++;
                }
                
                if ($juryAllocations > 0) {
                    $juriesProcessed++;
                }
                
                // Reset index para próximo júri (horário diferente)
                $vigilanteIndex = 0;
            }
            
            // Inserir todas as alocações em batch (muito mais rápido)
            if (!empty($allocations)) {
                $this->batchInsertAllocations($allocations, $allocatedBy);
            }
            
            $this->db->commit();
            
            $duration = round(microtime(true) - $startTime, 2);
            
            return [
                'success' => true,
                'message' => "✅ Auto-alocação completa: {$totalAllocated} alocações em {$juriesProcessed} júris ({$duration}s)",
                'juries_processed' => $juriesProcessed,
                'total_allocated' => $totalAllocated,
                'duration' => $duration
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Erro na auto-alocação: ' . $e->getMessage()];
        }
    }
    
    /**
     * Verifica conflito de horário considerando alocações pendentes
     */
    private function hasTimeConflict(int $vigilanteId, string $date, string $startTime, string $endTime, array $pendingAllocations): bool
    {
        // Verificar alocações já no banco
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM jury_vigilantes jv
            INNER JOIN juries j ON j.id = jv.jury_id
            WHERE jv.vigilante_id = :vigilante
              AND j.exam_date = :date
              AND (j.start_time < :end_time AND :start_time < j.end_time)
        ");
        $stmt->execute([
            'vigilante' => $vigilanteId,
            'date' => $date,
            'end_time' => $endTime,
            'start_time' => $startTime
        ]);
        
        if ((int) $stmt->fetchColumn() > 0) {
            return true;
        }
        
        // Verificar alocações pendentes (nesta transação)
        foreach ($pendingAllocations as $alloc) {
            if ($alloc['vigilante_id'] == $vigilanteId && 
                $alloc['exam_date'] == $date &&
                $alloc['start_time'] < $endTime && 
                $startTime < $alloc['end_time']) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Busca vigilantes disponíveis para uma data específica
     */
    private function getAvailableVigilantesForDiscipline(string $examDate): array
    {
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.email,
                   COALESCE(vw.vigilance_count, 0) as vigilance_count,
                   COALESCE(vw.supervision_count, 0) as supervision_count,
                   COALESCE(vw.workload_score, 0) as workload_score
            FROM users u
            LEFT JOIN vw_vigilante_workload vw ON vw.user_id = u.id
            WHERE u.role = 'vigilante' 
              AND u.available_for_vigilance = 1
            ORDER BY workload_score ASC, RAND()
        ");
        $stmt->execute();
        return $stmt->fetchAll();
    }
    
    /**
     * Insere múltiplas alocações em batch (INSERT múltiplo)
     */
    private function batchInsertAllocations(array $allocations, int $assignedBy): void
    {
        if (empty($allocations)) return;
        
        $now = now();
        $values = [];
        $params = [];
        
        foreach ($allocations as $i => $alloc) {
            $values[] = "(:jury_{$i}, :vigilante_{$i}, :by_{$i}, :at_{$i})";
            $params["jury_{$i}"] = $alloc['jury_id'];
            $params["vigilante_{$i}"] = $alloc['vigilante_id'];
            $params["by_{$i}"] = $assignedBy;
            $params["at_{$i}"] = $now;
        }
        
        $sql = "INSERT INTO jury_vigilantes (jury_id, vigilante_id, assigned_by, created_at) VALUES " . implode(', ', $values);
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        
        // Log em batch
        ActivityLogger::log('jury_vigilantes', 0, 'auto_allocate_batch', [
            'total' => count($allocations),
            'assigned_by' => $assignedBy
        ]);
    }
    
    /**
     * Obtém carga de trabalho de um vigilante/supervisor
     */
    public function getVigilanteWorkload(int $userId): array
    {
        // SELECT * seguro: vw_vigilante_workload é uma VIEW com campos específicos
        $stmt = $this->db->prepare("SELECT * FROM vw_vigilante_workload WHERE user_id = :id");
        $stmt->execute(['id' => $userId]);
        $workload = $stmt->fetch();
        
        return $workload ?: [
            'vigilance_count' => 0,
            'supervision_count' => 0,
            'workload_score' => 0
        ];
    }
    
    /**
     * Obtém carga média de todos os vigilantes disponíveis
     */
    public function getAverageWorkload(): float
    {
        $stmt = $this->db->query("
            SELECT AVG(workload_score) FROM vw_vigilante_workload 
            WHERE available_for_vigilance = 1
        ");
        return (float) $stmt->fetchColumn();
    }
    
    /**
     * Obtém estatísticas gerais de alocação
     */
    public function getAllocationStats(): array
    {
        // SELECT * seguro: vw_allocation_stats é uma VIEW com campos específicos
        $stmt = $this->db->query("SELECT * FROM vw_allocation_stats");
        $stats = $stmt->fetch();
        
        if (!$stats) {
            return [
                'total_juries' => 0,
                'total_capacity' => 0,
                'total_allocated' => 0,
                'slots_available' => 0,
                'juries_with_supervisor' => 0,
                'juries_without_supervisor' => 0,
                'avg_workload_score' => 0,
                'workload_std_deviation' => 0,
                'vigilantes_without_allocation' => 0
            ];
        }
        
        return $stats;
    }
    
    /**
     * Obtém slots e ocupação dos júris
     */
    public function getJurySlots(?int $juryId = null): array
    {
        if ($juryId) {
            // SELECT * seguro: vw_jury_slots é uma VIEW com campos específicos
            $stmt = $this->db->prepare("SELECT * FROM vw_jury_slots WHERE jury_id = :id");
            $stmt->execute(['id' => $juryId]);
            return $stmt->fetch() ?: [];
        }
        
        // SELECT * seguro: vw_jury_slots é uma VIEW com campos específicos
        $stmt = $this->db->query("SELECT * FROM vw_jury_slots ORDER BY exam_date, start_time, subject, room");
        return $stmt->fetchAll();
    }
    
    /**
     * Obtém vigilantes elegíveis para um júri (sem conflitos)
     */
    public function getEligibleVigilantes(int $juryId): array
    {
        // SELECT * seguro: vw_eligible_vigilantes é uma VIEW com campos específicos
        $stmt = $this->db->prepare("
            SELECT * FROM vw_eligible_vigilantes 
            WHERE jury_id = :jury AND has_conflict = 0
            ORDER BY supervisor_eligible DESC, workload_score ASC, vigilante_name
        ");
        $stmt->execute(['jury' => $juryId]);
        return $stmt->fetchAll();
    }
    
    /**
     * Obtém supervisores elegíveis para um júri (sem conflitos)
     * Prioriza vigilantes marcados como supervisor_eligible
     */
    public function getEligibleSupervisors(int $juryId): array
    {
        // SELECT * seguro: vw_eligible_supervisors é uma VIEW com campos específicos
        $stmt = $this->db->prepare("
            SELECT * FROM vw_eligible_supervisors 
            WHERE jury_id = :jury AND has_conflict = 0
            ORDER BY supervisor_eligible DESC, workload_score ASC, supervisor_name
        ");
        $stmt->execute(['jury' => $juryId]);
        return $stmt->fetchAll();
    }
}
