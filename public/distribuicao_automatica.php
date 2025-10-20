<?php
/**
 * DISTRIBUIÇÃO AUTOMÁTICA - Sugestões inteligentes de alocação
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordenador') {
    header('Location: login_direto.php');
    exit;
}

require_once __DIR__ . '/../bootstrap.php';
use App\Database\Connection;

$db = Connection::getInstance();
$suggestions = [];
$applied = false;

// APLICAR SUGESTÕES
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply_suggestions'])) {
    try {
        $db->beginTransaction();
        
        $suggestions = json_decode($_POST['suggestions_data'], true);
        
        foreach ($suggestions as $suggestion) {
            $juryId = $suggestion['jury_id'];
            
            // Alocar supervisor
            if (!empty($suggestion['supervisor_id'])) {
                $supervisorId = $suggestion['supervisor_id'];
                
                // Verificar se não é vigilante deste júri
                $isVigilante = $db->prepare("SELECT id FROM jury_vigilantes WHERE jury_id = ? AND vigilante_id = ?");
                $isVigilante->execute([$juryId, $supervisorId]);
                
                if (!$isVigilante->fetch()) {
                    $stmt = $db->prepare("UPDATE juries SET supervisor_id = ? WHERE id = ?");
                    $stmt->execute([$supervisorId, $juryId]);
                }
            }
            
            // Alocar vigilantes
            if (!empty($suggestion['vigilantes'])) {
                // Buscar supervisor atual do júri
                $currentSupervisor = $db->prepare("SELECT supervisor_id FROM juries WHERE id = ?");
                $currentSupervisor->execute([$juryId]);
                $supervisorId = $currentSupervisor->fetchColumn();
                
                foreach ($suggestion['vigilantes'] as $vigilanteId) {
                    // Não alocar se for o supervisor
                    if ($vigilanteId == $supervisorId) {
                        continue;
                    }
                    
                    $stmt = $db->prepare("
                        INSERT IGNORE INTO jury_vigilantes (jury_id, vigilante_id, assigned_by, created_at)
                        VALUES (?, ?, ?, NOW())
                    ");
                    $stmt->execute([$juryId, $vigilanteId, $_SESSION['user_id']]);
                }
            }
        }
        
        $db->commit();
        $applied = true;
        
    } catch (Exception $e) {
        $db->rollBack();
        $error = $e->getMessage();
    }
}

// GERAR SUGESTÕES
if (!$applied) {
    // Buscar júris sem alocação completa
    $juries = $db->query("
        SELECT 
            j.*,
            d.code as discipline_code,
            d.name as discipline_name,
            el.name as location_name,
            er.capacity as room_capacity,
            (SELECT COUNT(*) FROM jury_vigilantes WHERE jury_id = j.id) as vigilantes_count
        FROM juries j
        LEFT JOIN disciplines d ON d.id = j.discipline_id
        LEFT JOIN exam_locations el ON el.id = j.location_id
        LEFT JOIN exam_rooms er ON er.id = j.room_id
        WHERE j.exam_date >= CURDATE()
        ORDER BY j.exam_date, j.start_time
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar vigilantes disponíveis
    $vigilantes = $db->query("
        SELECT id, name
        FROM users
        WHERE role = 'vigilante' AND available_for_vigilance = 1
        ORDER BY name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Buscar supervisores disponíveis
    $supervisors = $db->query("
        SELECT id, name, role
        FROM users
        WHERE supervisor_eligible = 1
        ORDER BY FIELD(role, 'coordenador', 'membro', 'vigilante'), name
    ")->fetchAll(PDO::FETCH_ASSOC);
    
    // Rastrear alocações para evitar conflitos
    $vigilanteSchedule = [];
    $supervisorSchedule = [];
    
    foreach ($juries as $jury) {
        $suggestion = [
            'jury_id' => $jury['id'],
            'jury_info' => $jury,
            'supervisor_id' => null,
            'supervisor_name' => null,
            'vigilantes' => [],
            'vigilante_names' => []
        ];
        
        $juryKey = $jury['exam_date'] . '-' . $jury['start_time'] . '-' . $jury['end_time'];
        
        // Buscar vigilantes já alocados neste júri
        $currentVigilantes = $db->prepare("SELECT vigilante_id FROM jury_vigilantes WHERE jury_id = ?");
        $currentVigilantes->execute([$jury['id']]);
        $juryVigilantesIds = $currentVigilantes->fetchAll(PDO::FETCH_COLUMN);
        
        // Sugerir supervisor (se não tiver)
        if (!$jury['supervisor_id']) {
            foreach ($supervisors as $sup) {
                // Verificar se já é vigilante neste júri
                if (in_array($sup['id'], $juryVigilantesIds)) {
                    continue; // Pular, já é vigilante
                }
                
                // Verificar se supervisor está disponível neste horário/local
                $supKey = $sup['id'] . '-' . $juryKey;
                $locationKey = $sup['id'] . '-' . $juryKey . '-' . $jury['location_id'];
                
                if (!isset($supervisorSchedule[$locationKey])) {
                    // Supervisor pode supervisionar múltiplos júris no mesmo local
                    $supervisorSchedule[$locationKey] = true;
                    $suggestion['supervisor_id'] = $sup['id'];
                    $suggestion['supervisor_name'] = $sup['name'];
                    break;
                }
            }
        }
        
        // Sugerir vigilantes (calcular quantos são necessários)
        $currentVigilantes = (int)$jury['vigilantes_count'];
        $roomCapacity = (int)$jury['room_capacity'];
        $candidatesQuota = (int)$jury['candidates_quota'];
        
        // Fórmula: 1 vigilante por 30 candidatos (mínimo 2)
        $neededVigilantes = max(2, ceil($candidatesQuota / 30));
        $toAdd = $neededVigilantes - $currentVigilantes;
        
        if ($toAdd > 0) {
            $added = 0;
            foreach ($vigilantes as $vig) {
                if ($added >= $toAdd) break;
                
                // Verificar se é o supervisor deste júri
                if ($vig['id'] == $jury['supervisor_id']) {
                    continue; // Pular, já é supervisor
                }
                
                // Verificar se é o supervisor sugerido
                if ($vig['id'] == $suggestion['supervisor_id']) {
                    continue; // Pular, será supervisor
                }
                
                $vigKey = $vig['id'] . '-' . $juryKey;
                
                // Verificar se vigilante está disponível neste horário
                if (!isset($vigilanteSchedule[$vigKey])) {
                    $vigilanteSchedule[$vigKey] = true;
                    $suggestion['vigilantes'][] = $vig['id'];
                    $suggestion['vigilante_names'][] = $vig['name'];
                    $added++;
                }
            }
        }
        
        // Só adicionar à sugestão se houver algo a alocar
        if ($suggestion['supervisor_id'] || !empty($suggestion['vigilantes'])) {
            $suggestions[] = $suggestion;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Distribuição Automática</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between">
            <div class="flex items-center space-x-4">
                <a href="alocar_equipe.php" class="text-blue-600">← Voltar</a>
                <h1 class="text-xl font-bold">🤖 Distribuição Automática</h1>
            </div>
            <a href="logout_direto.php" class="text-red-600">Sair</a>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 py-8">
        
        <?php if ($applied): ?>
        <div class="mb-6 p-6 bg-green-100 border border-green-200 rounded-lg">
            <h3 class="text-lg font-bold text-green-900 mb-2">✅ Distribuição Aplicada com Sucesso!</h3>
            <p class="text-green-800 mb-4">Todas as sugestões foram aplicadas aos júris.</p>
            <a href="alocar_equipe.php" class="inline-block px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                Ver Alocações
            </a>
        </div>
        <?php elseif (isset($error)): ?>
        <div class="mb-6 p-4 bg-red-100 border border-red-200 rounded-lg text-red-800">
            ❌ Erro: <?= htmlspecialchars($error) ?>
        </div>
        <?php endif; ?>

        <?php if (!$applied && !empty($suggestions)): ?>
        
        <!-- Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <h3 class="font-bold text-blue-900 mb-2">🤖 Como Funciona</h3>
            <ul class="text-sm text-blue-800 space-y-1">
                <li>✅ Sistema analisa todos os júris futuros</li>
                <li>✅ Sugere supervisores e vigilantes disponíveis</li>
                <li>✅ Respeita regras de conflito automaticamente</li>
                <li>✅ Garante que ninguém seja supervisor E vigilante no mesmo júri</li>
                <li>✅ Calcula quantidade ideal de vigilantes (1 por 30 candidatos, mínimo 2)</li>
                <li>⚠️ Revise as sugestões antes de aplicar</li>
            </ul>
        </div>

        <!-- Sugestões -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">📋 Sugestões de Alocação</h2>
                <span class="px-3 py-1 bg-blue-100 text-blue-800 rounded-full text-sm font-medium">
                    <?= count($suggestions) ?> júri(s)
                </span>
            </div>

            <form method="POST">
                <input type="hidden" name="suggestions_data" value='<?= htmlspecialchars(json_encode($suggestions)) ?>'>
                
                <div class="space-y-4 mb-6">
                    <?php foreach ($suggestions as $sug): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                        <div class="flex justify-between items-start mb-3">
                            <div>
                                <div class="font-bold text-gray-900">
                                    <?= htmlspecialchars($sug['jury_info']['discipline_code'] ?? 'N/A') ?> - 
                                    <?= htmlspecialchars($sug['jury_info']['discipline_name'] ?? $sug['jury_info']['subject']) ?>
                                </div>
                                <div class="text-sm text-gray-600">
                                    📅 <?= date('d/m/Y', strtotime($sug['jury_info']['exam_date'])) ?> • 
                                    🕐 <?= substr($sug['jury_info']['start_time'], 0, 5) ?>-<?= substr($sug['jury_info']['end_time'], 0, 5) ?> • 
                                    📍 <?= htmlspecialchars($sug['jury_info']['location_name']) ?>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php if ($sug['supervisor_id']): ?>
                            <div class="bg-purple-50 border border-purple-200 rounded p-3">
                                <div class="text-xs font-medium text-purple-900 mb-1">👔 SUPERVISOR</div>
                                <div class="font-medium text-purple-900"><?= htmlspecialchars($sug['supervisor_name']) ?></div>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($sug['vigilantes'])): ?>
                            <div class="bg-blue-50 border border-blue-200 rounded p-3">
                                <div class="text-xs font-medium text-blue-900 mb-1">👁️ VIGILANTES (<?= count($sug['vigilantes']) ?>)</div>
                                <div class="text-sm text-blue-900">
                                    <?= implode(', ', array_map('htmlspecialchars', $sug['vigilante_names'])) ?>
                                </div>
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t">
                    <a href="alocar_equipe.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit" name="apply_suggestions" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        ✓ Aplicar Todas as Sugestões
                    </button>
                </div>
            </form>
        </div>

        <?php elseif (!$applied && empty($suggestions)): ?>
        
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <span class="text-4xl mb-3 block">✅</span>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Tudo Pronto!</h3>
            <p class="text-gray-600 mb-4">Não há sugestões de alocação no momento.<br>Todos os júris futuros já têm equipe completa.</p>
            <a href="alocar_equipe.php" class="inline-block px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Ver Alocações
            </a>
        </div>

        <?php endif; ?>

    </div>
</body>
</html>
