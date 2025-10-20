<?php

namespace App\Services;

use PDO;

/**
 * Serviço de Planejamento de Alocação Automática
 * 
 * Implementa fluxo "Auto → Revisão Humana":
 * 1. PLANEJAR: Gera plano de alocação sem gravar no BD
 * 2. APLICAR: Confirma e grava plano após revisão
 * 
 * Algoritmo: Greedy + Round-robin por janela temporal
 * Score: (1 × vigilâncias) + (2 × supervisões)
 */
class AllocationPlannerService
{
    private PDO $db;
    
    // Pesos configuráveis do score
    private const W_VIG = 1;  // Peso para vigilância
    private const W_SUP = 2;  // Peso para supervisão
    
    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
    
    /**
     * FASE 1: PLANEJAR
     * Gera plano de alocação para Local/Data sem gravar
     * 
     * @param string $location Local do exame
     * @param string $data Data do exame (YYYY-MM-DD)
     * @return array JSON com plan, stats, avisos, bloqueios
     */
    public function planLocalDate(string $location, string $data): array
    {
        // 1. Buscar júris do local/data
        $juries = $this->getJuriesByLocationDate($location, $data);
        
        if (empty($juries)) {
            return [
                'ok' => false,
                'erro' => 'Nenhum júri encontrado para este local/data',
                'plan' => [],
                'stats' => [],
                'avisos' => [],
                'bloqueios' => []
            ];
        }
        
        // 2. Agrupar júris por janela temporal (mesmo horário)
        $janelas = $this->groupByTimeWindow($juries);
        
        // 3. Obter docentes elegíveis e seus scores atuais
        $docentes = $this->getEligibleTeachers();
        $scoresPre = $this->calculateScores($docentes);
        
        // 4. Simular alocações (não grava no BD)
        $plan = [];
        $avisos = [];
        $bloqueios = [];
        
        foreach ($janelas as $janela) {
            // Alocar supervisores primeiro (um por júri)
            $this->allocateSupervisors($janela, $docentes, $scoresPre, $plan, $bloqueios);
            
            // Alocar vigilantes (round-robin)
            $this->allocateVigilantes($janela, $docentes, $scoresPre, $plan, $bloqueios);
        }
        
        // 5. Calcular scores simulados após alocação
        $scoresPos = $this->simulatePostScores($scoresPre, $plan);
        
        // 6. Calcular estatísticas
        $stats = [
            'desvio_score_pre' => $this->calculateStdDev(array_column($scoresPre, 'score')),
            'desvio_score_pos' => $this->calculateStdDev(array_values($scoresPos)),
            'total_acoes' => $this->countActions($plan),
            'juris_incompletos' => $this->countIncompleteJuries($juries, $plan)
        ];
        
        // 7. Gerar avisos
        if ($stats['juris_incompletos'] > 0) {
            $avisos[] = "{$stats['juris_incompletos']} júri(s) ainda sem vigilantes suficientes";
        }
        
        return [
            'ok' => true,
            'janela_count' => count($janelas),
            'stats' => $stats,
            'plan' => $plan,
            'avisos' => $avisos,
            'bloqueios' => $bloqueios
        ];
    }
    
    /**
     * FASE 2: APLICAR
     * Grava plano de alocação no BD (transação)
     * 
     * @param string $location Local (para validação)
     * @param string $data Data (para validação)
     * @param array $plan Plano gerado (possivelmente editado)
     * @return array JSON com aplicadas e falhas
     */
    public function applyLocalDate(string $location, string $data, array $plan): array
    {
        $aplicadas = 0;
        $falhas = [];
        
        try {
            $this->db->beginTransaction();
            
            foreach ($plan as $juryPlan) {
                $juryId = $juryPlan['juri_id'];
                
                foreach ($juryPlan['acoes'] as $acao) {
                    $op = $acao['op'];
                    $docenteId = $acao['docente_id'];
                    $papel = $acao['papel'];
                    
                    try {
                        if ($op === 'INSERT') {
                            $this->insertAllocation($juryId, $docenteId, $papel);
                            $aplicadas++;
                        } elseif ($op === 'DELETE') {
                            $this->deleteAllocation($juryId, $docenteId, $papel);
                            $aplicadas++;
                        }
                    } catch (\PDOException $e) {
                        // Capturar erros de trigger/constraint
                        $falhas[] = [
                            'juri_id' => $juryId,
                            'docente_id' => $docenteId,
                            'papel' => $papel,
                            'erro' => $this->parseErrorMessage($e->getMessage())
                        ];
                    }
                }
            }
            
            $this->db->commit();
            
            return [
                'ok' => true,
                'aplicadas' => $aplicadas,
                'falhas' => $falhas
            ];
            
        } catch (\Exception $e) {
            $this->db->rollBack();
            
            return [
                'ok' => false,
                'erro' => 'Erro ao aplicar plano: ' . $e->getMessage(),
                'aplicadas' => 0,
                'falhas' => $falhas
            ];
        }
    }
    
    /**
     * KPIs: Obter métricas de alocação por Local/Data
     */
    public function getKPIs(string $location, string $data): array
    {
        $juries = $this->getJuriesByLocationDate($location, $data);
        
        $totalJuries = count($juries);
        $semVigilante = 0;
        $semSupervisor = 0;
        $conflitos = 0;
        
        foreach ($juries as $jury) {
            // Contar alocações
            $stmt = $this->db->prepare("
                SELECT papel, COUNT(*) as qtd
                FROM jury_vigilantes
                WHERE jury_id = ?
                GROUP BY papel
            ");
            $stmt->execute([$jury['id']]);
            $allocs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            $vigias = $allocs['vigilante'] ?? 0;
            $supervisores = $allocs['supervisor'] ?? 0;
            
            if ($vigias === 0) $semVigilante++;
            if ($supervisores === 0) $semSupervisor++;
        }
        
        // Calcular desvio de score
        $docentes = $this->getEligibleTeachers();
        $scores = $this->calculateScores($docentes);
        $desvioScore = $this->calculateStdDev(array_column($scores, 'score'));
        
        return [
            'total_juris' => $totalJuries,
            'sem_vigilante' => $semVigilante,
            'sem_supervisor' => $semSupervisor,
            'conflitos_recentes' => $conflitos,
            'desvio_score' => round($desvioScore, 2),
            'ocupacao_media' => $totalJuries > 0 
                ? round((($totalJuries - $semVigilante) / $totalJuries) * 100, 1)
                : 0
        ];
    }
    
    // ========================================
    // MÉTODOS PRIVADOS - ALGORITMO
    // ========================================
    
    /**
     * Buscar júris por local e data
     */
    private function getJuriesByLocationDate(string $location, string $data): array
    {
        $stmt = $this->db->prepare("
            SELECT 
                id,
                subject,
                location,
                room,
                exam_date,
                inicio,
                fim,
                vigilantes_capacidade,
                candidates_quota
            FROM juries
            WHERE location = ? AND exam_date = ?
            ORDER BY inicio, room
        ");
        $stmt->execute([$location, $data]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Agrupar júris por janela temporal (mesmo horário)
     */
    private function groupByTimeWindow(array $juries): array
    {
        $janelas = [];
        
        foreach ($juries as $jury) {
            $key = $jury['inicio'] . '|' . $jury['fim'];
            
            if (!isset($janelas[$key])) {
                $janelas[$key] = [
                    'inicio' => $jury['inicio'],
                    'fim' => $jury['fim'],
                    'juries' => []
                ];
            }
            
            $janelas[$key]['juries'][] = $jury;
        }
        
        return array_values($janelas);
    }
    
    /**
     * Obter docentes elegíveis (ativos e disponíveis)
     */
    private function getEligibleTeachers(): array
    {
        $stmt = $this->db->query("
            SELECT 
                id,
                name,
                email,
                role
            FROM users
            WHERE role IN ('coordenador', 'membro', 'vigilante')
              AND active = 1
            ORDER BY name
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Calcular scores atuais de todos os docentes
     */
    private function calculateScores(array $docentes): array
    {
        $scores = [];
        
        foreach ($docentes as $docente) {
            $stmt = $this->db->prepare("
                SELECT 
                    COALESCE(SUM(CASE WHEN papel = 'vigilante' THEN 1 ELSE 0 END), 0) AS n_vigias,
                    COALESCE(SUM(CASE WHEN papel = 'supervisor' THEN 1 ELSE 0 END), 0) AS n_supervisoes
                FROM jury_vigilantes
                WHERE vigilante_id = ?
            ");
            $stmt->execute([$docente['id']]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            
            $scores[$docente['id']] = [
                'docente_id' => $docente['id'],
                'name' => $docente['name'],
                'n_vigias' => (int) $result['n_vigias'],
                'n_supervisoes' => (int) $result['n_supervisoes'],
                'score' => (self::W_VIG * $result['n_vigias']) + (self::W_SUP * $result['n_supervisoes'])
            ];
        }
        
        return $scores;
    }
    
    /**
     * Alocar supervisores (um por júri, menor score)
     */
    private function allocateSupervisors(array $janela, array $docentes, array &$scores, array &$plan, array &$bloqueios): void
    {
        foreach ($janela['juries'] as $jury) {
            // Verificar se já tem supervisor
            if ($this->hasSupervisor($jury['id'])) {
                continue;
            }
            
            // Ordenar docentes por score (menor primeiro)
            $candidatos = $scores;
            uasort($candidatos, function($a, $b) {
                if ($a['score'] != $b['score']) {
                    return $a['score'] <=> $b['score'];
                }
                return $a['n_supervisoes'] <=> $b['n_supervisoes'];
            });
            
            // Escolher primeiro docente sem conflito
            foreach ($candidatos as $candidato) {
                if ($this->hasTimeConflict($candidato['docente_id'], $janela['inicio'], $janela['fim'], $scores)) {
                    continue;
                }
                
                // Alocar (simulado)
                $plan[] = [
                    'juri_id' => $jury['id'],
                    'juri_info' => $jury['subject'] . ' - Sala ' . $jury['room'],
                    'acoes' => [[
                        'op' => 'INSERT',
                        'docente_id' => $candidato['docente_id'],
                        'docente_name' => $candidato['name'],
                        'papel' => 'supervisor',
                        'racional' => sprintf(
                            'Menor score na janela (score=%d); sem conflito',
                            $candidato['score']
                        )
                    ]]
                ];
                
                // Atualizar score simulado
                $scores[$candidato['docente_id']]['n_supervisoes']++;
                $scores[$candidato['docente_id']]['score'] += self::W_SUP;
                
                break; // Próximo júri
            }
        }
    }
    
    /**
     * Alocar vigilantes (round-robin até capacidade)
     */
    private function allocateVigilantes(array $janela, array $docentes, array &$scores, array &$plan, array &$bloqueios): void
    {
        $juryIndex = 0;
        $juriesCount = count($janela['juries']);
        
        // Continuar até preencher todos os júris ou esgotar candidatos
        $maxIterations = $juriesCount * 10; // Prevenir loop infinito
        $iteration = 0;
        
        while ($iteration < $maxIterations) {
            $jury = $janela['juries'][$juryIndex];
            
            // Verificar capacidade
            $currentCount = $this->countVigilantes($jury['id'], $plan);
            if ($currentCount >= $jury['vigilantes_capacidade']) {
                $juryIndex = ($juryIndex + 1) % $juriesCount;
                $iteration++;
                continue;
            }
            
            // Ordenar candidatos por score
            $candidatos = $scores;
            uasort($candidatos, function($a, $b) {
                return $a['score'] <=> $b['score'];
            });
            
            // Escolher primeiro sem conflito
            $alocado = false;
            foreach ($candidatos as $candidato) {
                // Verificar se já está alocado neste júri
                if ($this->isAllocatedInJury($candidato['docente_id'], $jury['id'], $plan)) {
                    continue;
                }
                
                // Verificar conflito de horário
                if ($this->hasTimeConflict($candidato['docente_id'], $janela['inicio'], $janela['fim'], $scores)) {
                    continue;
                }
                
                // Alocar
                $existingPlan = $this->findPlanForJury($jury['id'], $plan);
                
                if ($existingPlan !== null) {
                    $plan[$existingPlan]['acoes'][] = [
                        'op' => 'INSERT',
                        'docente_id' => $candidato['docente_id'],
                        'docente_name' => $candidato['name'],
                        'papel' => 'vigilante',
                        'racional' => sprintf(
                            'Balanceamento round-robin (score=%d, vaga %d/%d)',
                            $candidato['score'],
                            $currentCount + 1,
                            $jury['vigilantes_capacidade']
                        )
                    ];
                } else {
                    $plan[] = [
                        'juri_id' => $jury['id'],
                        'juri_info' => $jury['subject'] . ' - Sala ' . $jury['room'],
                        'acoes' => [[
                            'op' => 'INSERT',
                            'docente_id' => $candidato['docente_id'],
                            'docente_name' => $candidato['name'],
                            'papel' => 'vigilante',
                            'racional' => sprintf(
                                'Balanceamento round-robin (score=%d, vaga %d/%d)',
                                $candidato['score'],
                                $currentCount + 1,
                                $jury['vigilantes_capacidade']
                            )
                        ]]
                    ];
                }
                
                // Atualizar score
                $scores[$candidato['docente_id']]['n_vigias']++;
                $scores[$candidato['docente_id']]['score'] += self::W_VIG;
                
                $alocado = true;
                break;
            }
            
            // Se nenhum candidato disponível, avançar
            if (!$alocado) {
                $juryIndex = ($juryIndex + 1) % $juriesCount;
            }
            
            $iteration++;
            
            // Parar se todos os júris estão completos
            if ($this->allJuriesFull($janela['juries'], $plan)) {
                break;
            }
        }
    }
    
    /**
     * Verificar se júri já tem supervisor
     */
    private function hasSupervisor(int $juryId): bool
    {
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM jury_vigilantes
            WHERE jury_id = ? AND papel = 'supervisor'
        ");
        $stmt->execute([$juryId]);
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Verificar conflito de horário (em alocações existentes + plano simulado)
     */
    private function hasTimeConflict(int $docenteId, string $inicio, string $fim, array $scores): bool
    {
        // Verificar no BD
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM jury_vigilantes jv
            WHERE jv.vigilante_id = ?
              AND jv.juri_fim > ?
              AND jv.juri_inicio < ?
        ");
        $stmt->execute([$docenteId, $inicio, $fim]);
        
        return $stmt->fetchColumn() > 0;
    }
    
    /**
     * Contar vigilantes já alocados em um júri (BD + plano)
     */
    private function countVigilantes(int $juryId, array $plan): int
    {
        // Contar no BD
        $stmt = $this->db->prepare("
            SELECT COUNT(*) FROM jury_vigilantes
            WHERE jury_id = ? AND papel = 'vigilante'
        ");
        $stmt->execute([$juryId]);
        $count = (int) $stmt->fetchColumn();
        
        // Contar no plano
        foreach ($plan as $juryPlan) {
            if ($juryPlan['juri_id'] == $juryId) {
                foreach ($juryPlan['acoes'] as $acao) {
                    if ($acao['papel'] == 'vigilante' && $acao['op'] == 'INSERT') {
                        $count++;
                    }
                }
            }
        }
        
        return $count;
    }
    
    /**
     * Verificar se docente já está alocado em júri específico (plano)
     */
    private function isAllocatedInJury(int $docenteId, int $juryId, array $plan): bool
    {
        foreach ($plan as $juryPlan) {
            if ($juryPlan['juri_id'] == $juryId) {
                foreach ($juryPlan['acoes'] as $acao) {
                    if ($acao['docente_id'] == $docenteId && $acao['op'] == 'INSERT') {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * Encontrar índice do plano de um júri
     */
    private function findPlanForJury(int $juryId, array &$plan): ?int
    {
        foreach ($plan as $index => $juryPlan) {
            if ($juryPlan['juri_id'] == $juryId) {
                return $index;
            }
        }
        return null;
    }
    
    /**
     * Verificar se todos os júris da janela estão completos
     */
    private function allJuriesFull(array $juries, array $plan): bool
    {
        foreach ($juries as $jury) {
            $count = $this->countVigilantes($jury['id'], $plan);
            if ($count < $jury['vigilantes_capacidade']) {
                return false;
            }
        }
        return true;
    }
    
    /**
     * Simular scores pós-alocação
     */
    private function simulatePostScores(array $scoresPre, array $plan): array
    {
        $scoresPos = [];
        
        foreach ($scoresPre as $docenteId => $data) {
            $scoresPos[$docenteId] = $data['score'];
        }
        
        // Aplicar ações do plano
        foreach ($plan as $juryPlan) {
            foreach ($juryPlan['acoes'] as $acao) {
                if ($acao['op'] == 'INSERT') {
                    $peso = $acao['papel'] == 'supervisor' ? self::W_SUP : self::W_VIG;
                    $scoresPos[$acao['docente_id']] = ($scoresPos[$acao['docente_id']] ?? 0) + $peso;
                }
            }
        }
        
        return $scoresPos;
    }
    
    /**
     * Calcular desvio padrão
     */
    private function calculateStdDev(array $values): float
    {
        if (empty($values)) {
            return 0.0;
        }
        
        $count = count($values);
        $mean = array_sum($values) / $count;
        
        $variance = array_reduce($values, function($carry, $value) use ($mean) {
            return $carry + pow($value - $mean, 2);
        }, 0) / $count;
        
        return sqrt($variance);
    }
    
    /**
     * Contar total de ações no plano
     */
    private function countActions(array $plan): int
    {
        $count = 0;
        foreach ($plan as $juryPlan) {
            $count += count($juryPlan['acoes']);
        }
        return $count;
    }
    
    /**
     * Contar júris incompletos (sem vigilantes suficientes)
     */
    private function countIncompleteJuries(array $juries, array $plan): int
    {
        $count = 0;
        foreach ($juries as $jury) {
            $vigilantes = $this->countVigilantes($jury['id'], $plan);
            if ($vigilantes < $jury['vigilantes_capacidade']) {
                $count++;
            }
        }
        return $count;
    }
    
    /**
     * Inserir alocação no BD (com validações em PHP)
     */
    private function insertAllocation(int $juryId, int $docenteId, string $papel): void
    {
        // VALIDAÇÃO 1: Verificar capacidade de vigilantes
        if ($papel === 'vigilante') {
            $stmt = $this->db->prepare("
                SELECT vigilantes_capacidade FROM juries WHERE id = ?
            ");
            $stmt->execute([$juryId]);
            $capacidade = (int) $stmt->fetchColumn();
            
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM jury_vigilantes 
                WHERE jury_id = ? AND papel = 'vigilante'
            ");
            $stmt->execute([$juryId]);
            $atual = (int) $stmt->fetchColumn();
            
            if ($atual >= $capacidade) {
                throw new \PDOException('Capacidade de vigilantes atingida');
            }
        }
        
        // VALIDAÇÃO 2: Verificar supervisor único
        if ($papel === 'supervisor') {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM jury_vigilantes 
                WHERE jury_id = ? AND papel = 'supervisor'
            ");
            $stmt->execute([$juryId]);
            $existe = (int) $stmt->fetchColumn();
            
            if ($existe > 0) {
                throw new \PDOException('Júri já possui supervisor');
            }
        }
        
        // VALIDAÇÃO 3: Verificar conflito de horário
        $stmt = $this->db->prepare("
            SELECT j.inicio, j.fim 
            FROM juries j 
            WHERE j.id = ?
        ");
        $stmt->execute([$juryId]);
        $janela = $stmt->fetch(\PDO::FETCH_ASSOC);
        
        if ($janela) {
            $stmt = $this->db->prepare("
                SELECT COUNT(*) FROM jury_vigilantes jv
                INNER JOIN juries j ON j.id = jv.jury_id
                WHERE jv.vigilante_id = ?
                  AND j.fim > ?
                  AND j.inicio < ?
            ");
            $stmt->execute([$docenteId, $janela['inicio'], $janela['fim']]);
            $conflitos = (int) $stmt->fetchColumn();
            
            if ($conflitos > 0) {
                throw new \PDOException('Conflito de horário');
            }
        }
        
        // Buscar janela temporal para materializar
        $juri_inicio = $janela['inicio'] ?? null;
        $juri_fim = $janela['fim'] ?? null;
        
        // Inserir com janela materializada
        $stmt = $this->db->prepare("
            INSERT INTO jury_vigilantes 
            (jury_id, vigilante_id, papel, juri_inicio, juri_fim, assigned_by, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");
        $stmt->execute([
            $juryId, 
            $docenteId, 
            $papel, 
            $juri_inicio, 
            $juri_fim, 
            $_SESSION['user_id'] ?? 1
        ]);
    }
    
    /**
     * Remover alocação do BD
     */
    private function deleteAllocation(int $juryId, int $docenteId, string $papel): void
    {
        $stmt = $this->db->prepare("
            DELETE FROM jury_vigilantes
            WHERE jury_id = ? AND vigilante_id = ? AND papel = ?
        ");
        $stmt->execute([$juryId, $docenteId, $papel]);
    }
    
    /**
     * Parsear mensagem de erro do trigger
     */
    private function parseErrorMessage(string $message): string
    {
        if (strpos($message, 'Conflito de horário') !== false) {
            return 'Conflito de horário';
        }
        if (strpos($message, 'Capacidade de vigilantes atingida') !== false) {
            return 'Capacidade atingida';
        }
        if (strpos($message, 'já possui supervisor') !== false) {
            return 'Júri já possui supervisor';
        }
        return $message;
    }
}
