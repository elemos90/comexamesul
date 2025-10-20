<?php

namespace App\Controllers;

use App\Database\Connection;
use App\Utils\Auth;
use PDO;

/**
 * Controller de Sugestões Inteligentes "Top-3"
 * 
 * Ao focar um slot (Vigilante/Supervisor), retorna os 3 melhores docentes
 * ordenados por: livre no horário, menor score, aptidão, campus, preferências.
 * 
 * Pesos de Ranking (ajustáveis):
 * - Conflito: +1000 (bloqueia)
 * - Score global: +4 por ponto (1*vigia + 2*supervisor)
 * - Aptidão: -2 por ponto (quanto maior, melhor)
 * - Distância campus: +1 se diferente
 * - Preferência: -1 se tem preferência
 */
class SuggestController extends Controller
{
    // ========================================
    // PESOS DO RANKING (AJUSTAR AQUI)
    // ========================================
    private const PESO_CONFLITO = 1000;     // Bloqueia completamente se conflito
    private const PESO_SCORE = 4;           // Equilibrar carga
    private const PESO_APTIDAO = 2;         // Priorizar mais experientes
    private const PESO_DISTANCIA = 1;       // Preferir mesmo campus
    private const PESO_PREFERENCIA = 1;     // Bonificar preferências declaradas
    
    /**
     * GET /api/suggest-top3?juri_id=<int>&papel=<vigilante|supervisor>
     * 
     * Retorna Top-3 docentes para alocar no slot especificado
     */
    public function top3(): void
    {
        // Autenticação
        if (!Auth::check()) {
            $this->jsonResponse(['ok' => false, 'error' => 'Não autenticado'], 401);
            return;
        }
        
        // Parâmetros
        $juriId = (int)($_GET['juri_id'] ?? 0);
        $papel = $_GET['papel'] ?? 'vigilante';
        
        if (!$juriId || !in_array($papel, ['vigilante', 'supervisor'])) {
            $this->jsonResponse(['ok' => false, 'error' => 'Parâmetros inválidos'], 400);
            return;
        }
        
        try {
            $db = Connection::getInstance();
            
            // 1. Buscar informações do júri
            $juri = $this->getJuriInfo($db, $juriId);
            if (!$juri) {
                $this->jsonResponse(['ok' => false, 'error' => 'Júri não encontrado'], 404);
                return;
            }
            
            // 2. Buscar docentes elegíveis
            $docentes = $this->getDocentesElegiveis($db);
            
            // 3. Calcular ranking para cada docente
            $candidatos = [];
            foreach ($docentes as $docente) {
                // Verificar conflito de horário
                $temConflito = $this->verificarConflito(
                    $db, 
                    $docente['id'], 
                    $juri['inicio'], 
                    $juri['fim']
                );
                
                // Buscar score global do docente
                $scoreGlobal = $this->getScoreGlobal($db, $docente['id']);
                
                // Calcular aptidão para o papel
                $aptidao = $this->calcularAptidao($docente, $papel);
                
                // Calcular distância (campus diferente)
                $distancia = ($docente['campus'] === $juri['location']) ? 0 : 1;
                
                // Verificar preferências
                $preferencia = $this->verificarPreferencia($docente, $juri);
                
                // Calcular rank_value (menor é melhor)
                $rankValue = 
                    self::PESO_CONFLITO * ($temConflito ? 1 : 0) +
                    self::PESO_SCORE * $scoreGlobal +
                    (-self::PESO_APTIDAO) * $aptidao +
                    self::PESO_DISTANCIA * $distancia +
                    (-self::PESO_PREFERENCIA) * $preferencia +
                    $this->epsilon($docente['id']); // Desempate estável
                
                // Gerar motivo textual
                $motivo = $this->gerarMotivo(
                    $temConflito,
                    $scoreGlobal,
                    $aptidao,
                    $distancia,
                    $preferencia,
                    $papel
                );
                
                $candidatos[] = [
                    'docente_id' => $docente['id'],
                    'nome' => $docente['name'],
                    'score' => $scoreGlobal,
                    'aptidao' => round($aptidao, 2),
                    'dist' => $distancia,
                    'prefer' => $preferencia,
                    'rank' => $rankValue,
                    'motivo' => $motivo,
                    'bloqueado' => $temConflito
                ];
            }
            
            // 4. Ordenar por rank_value (ASC)
            usort($candidatos, fn($a, $b) => $a['rank'] <=> $b['rank']);
            
            // 5. Pegar Top-3 (somente não bloqueados)
            $candidatosLivres = array_filter($candidatos, fn($c) => !$c['bloqueado']);
            $top3 = array_slice($candidatosLivres, 0, 3);
            
            // Remover campo interno 'rank' e 'bloqueado' da resposta
            $top3 = array_map(function($c) {
                unset($c['rank'], $c['bloqueado']);
                return $c;
            }, $top3);
            
            // 6. Responder
            $this->jsonResponse([
                'ok' => true,
                'slot' => [
                    'juri_id' => $juriId,
                    'papel' => $papel,
                    'inicio' => $juri['inicio'],
                    'fim' => $juri['fim'],
                    'local' => $juri['location'],
                    'room' => $juri['room'],
                    'subject' => $juri['subject']
                ],
                'top3' => $top3,
                'fallbacks' => max(0, 3 - count($top3))
            ]);
            
        } catch (\Exception $e) {
            error_log("Erro em suggest/top3: " . $e->getMessage());
            $this->jsonResponse(['ok' => false, 'error' => 'Erro interno'], 500);
        }
    }
    
    /**
     * POST /api/suggest-apply
     * 
     * Aplica uma sugestão (insere alocação)
     * Body: juri_id, docente_id, papel
     */
    public function apply(): void
    {
        // Autenticação
        if (!Auth::check()) {
            $this->jsonResponse(['ok' => false, 'error' => 'Não autenticado'], 401);
            return;
        }
        
        // CSRF
        if (!$this->verifyCsrf()) {
            $this->jsonResponse(['ok' => false, 'error' => 'Token CSRF inválido'], 403);
            return;
        }
        
        // Parâmetros
        $juriId = (int)($_POST['juri_id'] ?? 0);
        $docenteId = (int)($_POST['docente_id'] ?? 0);
        $papel = $_POST['papel'] ?? 'vigilante';
        
        if (!$juriId || !$docenteId || !in_array($papel, ['vigilante', 'supervisor'])) {
            $this->jsonResponse(['ok' => false, 'error' => 'Parâmetros inválidos'], 400);
            return;
        }
        
        try {
            $db = Connection::getInstance();
            $db->beginTransaction();
            
            // Buscar janela temporal do júri
            $stmt = $db->prepare("
                SELECT inicio, fim FROM juries WHERE id = ?
            ");
            $stmt->execute([$juriId]);
            $juri = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$juri) {
                $db->rollBack();
                $this->jsonResponse(['ok' => false, 'error' => 'Júri não encontrado'], 404);
                return;
            }
            
            // Validações (mesmas do AllocationPlannerService)
            $this->validarAlocacao($db, $juriId, $docenteId, $papel, $juri);
            
            // Inserir alocação
            $stmt = $db->prepare("
                INSERT INTO jury_vigilantes 
                (jury_id, vigilante_id, papel, juri_inicio, juri_fim, assigned_by, created_at)
                VALUES (?, ?, ?, ?, ?, ?, NOW())
            ");
            $stmt->execute([
                $juriId, 
                $docenteId, 
                $papel, 
                $juri['inicio'], 
                $juri['fim'], 
                Auth::id() ?? 1
            ]);
            
            $db->commit();
            
            $this->jsonResponse([
                'ok' => true,
                'message' => 'Alocação aplicada com sucesso',
                'allocation_id' => $db->lastInsertId()
            ]);
            
        } catch (\PDOException $e) {
            if (isset($db)) $db->rollBack();
            
            $error = $e->getMessage();
            $userError = 'Erro ao aplicar alocação';
            
            if (strpos($error, 'Conflito') !== false) {
                $userError = 'Conflito de horário detectado';
            } elseif (strpos($error, 'Capacidade') !== false) {
                $userError = 'Capacidade de vigilantes atingida';
            } elseif (strpos($error, 'supervisor') !== false) {
                $userError = 'Júri já possui supervisor';
            }
            
            $this->jsonResponse(['ok' => false, 'error' => $userError], 400);
            
        } catch (\Exception $e) {
            if (isset($db)) $db->rollBack();
            error_log("Erro em suggest/apply: " . $e->getMessage());
            $this->jsonResponse(['ok' => false, 'error' => 'Erro interno'], 500);
        }
    }
    
    // ========================================
    // MÉTODOS AUXILIARES
    // ========================================
    
    /**
     * Buscar informações do júri
     */
    private function getJuriInfo(PDO $db, int $juriId): ?array
    {
        $stmt = $db->prepare("
            SELECT 
                id,
                subject,
                location,
                room,
                inicio,
                fim,
                vigilantes_capacidade
            FROM juries
            WHERE id = ?
        ");
        $stmt->execute([$juriId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    
    /**
     * Buscar docentes elegíveis (ativos e disponíveis para vigilância)
     */
    private function getDocentesElegiveis(PDO $db): array
    {
        // Verificar se coluna experiencia_supervisao existe
        $stmt = $db->query("SHOW COLUMNS FROM users LIKE 'experiencia_supervisao'");
        $hasExperiencia = $stmt->rowCount() > 0;
        
        if ($hasExperiencia) {
            $stmt = $db->query("
                SELECT 
                    id,
                    name,
                    campus,
                    COALESCE(experiencia_supervisao, 0) AS experiencia_supervisao
                FROM users
                WHERE role IN ('coordenador', 'membro', 'docente')
                  AND active = 1
                  AND available_for_vigilance = 1
                ORDER BY name
            ");
        } else {
            // Fallback: sem coluna experiencia_supervisao
            $stmt = $db->query("
                SELECT 
                    id,
                    name,
                    campus,
                    0 AS experiencia_supervisao
                FROM users
                WHERE role IN ('coordenador', 'membro', 'docente')
                  AND active = 1
                  AND available_for_vigilance = 1
                ORDER BY name
            ");
        }
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    /**
     * Verificar conflito de horário para um docente
     */
    private function verificarConflito(PDO $db, int $docenteId, string $inicio, string $fim): bool
    {
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM jury_vigilantes jv
            INNER JOIN juries j ON j.id = jv.jury_id
            WHERE jv.vigilante_id = ?
              AND j.fim > ?
              AND j.inicio < ?
        ");
        $stmt->execute([$docenteId, $inicio, $fim]);
        return (int)$stmt->fetchColumn() > 0;
    }
    
    /**
     * Buscar score global do docente (1*vigia + 2*supervisor)
     */
    private function getScoreGlobal(PDO $db, int $docenteId): int
    {
        $stmt = $db->prepare("
            SELECT COALESCE(SUM(
                CASE 
                    WHEN papel = 'vigilante' THEN 1
                    WHEN papel = 'supervisor' THEN 2
                    ELSE 0
                END
            ), 0) AS score
            FROM jury_vigilantes
            WHERE vigilante_id = ?
        ");
        $stmt->execute([$docenteId]);
        return (int)$stmt->fetchColumn();
    }
    
    /**
     * Calcular aptidão para o papel (0.0 a 1.0)
     */
    private function calcularAptidao(array $docente, string $papel): float
    {
        if ($papel === 'supervisor') {
            // Normalizar experiência (0-10 → 0.0-1.0)
            $exp = (int)($docente['experiencia_supervisao'] ?? 0);
            return min(1.0, $exp / 10);
        }
        
        // Vigilante: aptidão padrão
        return 0.5;
    }
    
    /**
     * Verificar preferências do docente (mock - adaptar conforme BD)
     */
    private function verificarPreferencia(array $docente, array $juri): int
    {
        // TODO: Implementar lógica de preferências quando campo existir
        // Por enquanto, retorna 0 (sem preferência)
        return 0;
    }
    
    /**
     * Epsilon para desempate estável (baseado no ID)
     */
    private function epsilon(int $docenteId): float
    {
        // Usa hash do ID para gerar valor estável entre 0 e 0.1
        return (crc32((string)$docenteId) % 100) / 1000;
    }
    
    /**
     * Gerar motivo textual para a sugestão
     */
    private function gerarMotivo(
        bool $temConflito,
        int $score,
        float $aptidao,
        int $distancia,
        int $preferencia,
        string $papel
    ): string {
        if ($temConflito) {
            return 'Conflito de horário';
        }
        
        $motivos = [];
        
        // Score baixo = boa distribuição
        if ($score <= 2) {
            $motivos[] = 'baixa carga';
        } elseif ($score <= 4) {
            $motivos[] = 'carga moderada';
        }
        
        // Aptidão alta
        if ($papel === 'supervisor' && $aptidao >= 0.7) {
            $motivos[] = 'supervisor experiente';
        }
        
        // Mesmo campus
        if ($distancia === 0) {
            $motivos[] = 'mesmo campus';
        }
        
        // Preferência
        if ($preferencia === 1) {
            $motivos[] = 'preferência declarada';
        }
        
        return !empty($motivos) 
            ? ucfirst(implode('; ', $motivos)) 
            : 'Disponível';
    }
    
    /**
     * Validar alocação (antes de inserir)
     */
    private function validarAlocacao(PDO $db, int $juriId, int $docenteId, string $papel, array $juri): void
    {
        // 1. Verificar capacidade de vigilantes
        if ($papel === 'vigilante') {
            $stmt = $db->prepare("
                SELECT vigilantes_capacidade FROM juries WHERE id = ?
            ");
            $stmt->execute([$juriId]);
            $capacidade = (int) $stmt->fetchColumn();
            
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM jury_vigilantes 
                WHERE jury_id = ? AND papel = 'vigilante'
            ");
            $stmt->execute([$juriId]);
            $atual = (int) $stmt->fetchColumn();
            
            if ($atual >= $capacidade) {
                throw new \PDOException('Capacidade de vigilantes atingida');
            }
        }
        
        // 2. Verificar supervisor único
        if ($papel === 'supervisor') {
            $stmt = $db->prepare("
                SELECT COUNT(*) FROM jury_vigilantes 
                WHERE jury_id = ? AND papel = 'supervisor'
            ");
            $stmt->execute([$juriId]);
            $existe = (int) $stmt->fetchColumn();
            
            if ($existe > 0) {
                throw new \PDOException('Júri já possui supervisor');
            }
        }
        
        // 3. Verificar conflito de horário
        if ($this->verificarConflito($db, $docenteId, $juri['inicio'], $juri['fim'])) {
            throw new \PDOException('Conflito de horário');
        }
    }
    
    /**
     * Enviar resposta JSON
     */
    private function jsonResponse(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
    
    /**
     * Verificar CSRF token
     */
    private function verifyCsrf(): bool
    {
        $token = $_POST['_token'] ?? '';
        return !empty($token) && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
    }
}
