<?php
/**
 * MAPA DE ALOCAÇÕES - Visualização gráfica das alocações
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login_direto.php');
    exit;
}

require_once __DIR__ . '/../bootstrap.php';
use App\Database\Connection;

$db = Connection::getInstance();

// Buscar todos os júris com alocações
$juries = $db->query("
    SELECT 
        j.*,
        d.code as discipline_code,
        d.name as discipline_name,
        el.name as location_name,
        er.name as room_name,
        us.name as supervisor_name
    FROM juries j
    LEFT JOIN disciplines d ON d.id = j.discipline_id
    LEFT JOIN exam_locations el ON el.id = j.location_id
    LEFT JOIN exam_rooms er ON er.id = j.room_id
    LEFT JOIN users us ON us.id = j.supervisor_id
    WHERE j.exam_date >= CURDATE()
    ORDER BY j.exam_date, j.start_time
")->fetchAll(PDO::FETCH_ASSOC);

// Organizar por data
$byDate = [];
foreach ($juries as $jury) {
    $date = $jury['exam_date'];
    if (!isset($byDate[$date])) {
        $byDate[$date] = [];
    }
    
    // Buscar vigilantes
    $vigilantes = $db->prepare("
        SELECT u.name
        FROM jury_vigilantes jv
        INNER JOIN users u ON u.id = jv.vigilante_id
        WHERE jv.jury_id = ?
    ");
    $vigilantes->execute([$jury['id']]);
    $jury['vigilantes'] = $vigilantes->fetchAll(PDO::FETCH_COLUMN);
    
    $byDate[$date][] = $jury;
}

// Estatísticas
$totalJuries = count($juries);
$withSupervisor = count(array_filter($juries, fn($j) => !empty($j['supervisor_id'])));
$totalVigilantes = $db->query("SELECT COUNT(*) FROM jury_vigilantes")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Mapa de Alocações</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between">
            <div class="flex items-center space-x-4">
                <a href="dashboard_direto.php" class="text-blue-600">← Dashboard</a>
                <h1 class="text-xl font-bold">🗺️ Mapa de Alocações</h1>
            </div>
            <a href="logout_direto.php" class="text-red-600">Sair</a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <!-- Resumo -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg p-6 shadow-sm border">
                <div class="text-3xl font-bold text-blue-600"><?= $totalJuries ?></div>
                <div class="text-sm text-gray-600 mt-1">Total de Júris</div>
                <div class="mt-2 text-xs text-gray-500">
                    ✅ <?= $withSupervisor ?> com supervisor
                </div>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-sm border">
                <div class="text-3xl font-bold text-purple-600"><?= $withSupervisor ?></div>
                <div class="text-sm text-gray-600 mt-1">Supervisores Alocados</div>
                <div class="mt-2 text-xs text-gray-500">
                    <?= round(($withSupervisor/$totalJuries)*100) ?>% cobertura
                </div>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-sm border">
                <div class="text-3xl font-bold text-green-600"><?= $totalVigilantes ?></div>
                <div class="text-sm text-gray-600 mt-1">Vigilantes Alocados</div>
                <div class="mt-2 text-xs text-gray-500">
                    ~<?= round($totalVigilantes/$totalJuries, 1) ?> por júri
                </div>
            </div>
        </div>

        <!-- Timeline por Data -->
        <div class="space-y-6">
            <?php foreach ($byDate as $date => $dayJuries): ?>
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
                <div class="bg-gradient-to-r from-blue-500 to-purple-500 text-white px-6 py-4">
                    <h2 class="text-xl font-bold">
                        📅 <?= date('d/m/Y', strtotime($date)) ?> - <?= date('l', strtotime($date)) ?>
                    </h2>
                    <p class="text-sm opacity-90"><?= count($dayJuries) ?> júri(s) programado(s)</p>
                </div>
                
                <div class="p-6 space-y-4">
                    <?php foreach ($dayJuries as $jury): ?>
                    <div class="border border-gray-200 rounded-lg p-4 hover:shadow-md transition">
                        <div class="flex justify-between items-start mb-3">
                            <div class="flex-1">
                                <div class="flex items-center space-x-2 mb-2">
                                    <span class="font-mono font-bold text-blue-600">
                                        <?= substr($jury['start_time'], 0, 5) ?>-<?= substr($jury['end_time'], 0, 5) ?>
                                    </span>
                                    <span class="text-gray-400">•</span>
                                    <span class="font-bold text-gray-900">
                                        <?= htmlspecialchars($jury['discipline_code'] ?? 'N/A') ?>
                                    </span>
                                </div>
                                <div class="text-sm text-gray-600">
                                    📍 <?= htmlspecialchars($jury['location_name']) ?> - 
                                    🏛️ <?= htmlspecialchars($jury['room_name']) ?> - 
                                    👥 <?= $jury['candidates_quota'] ?> vagas
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <?php if ($jury['supervisor_id']): ?>
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded text-xs">✓ Supervisor</span>
                                <?php else: ?>
                                <span class="px-2 py-1 bg-red-100 text-red-800 rounded text-xs">⚠️ Sem Supervisor</span>
                                <?php endif; ?>
                                
                                <?php if (count($jury['vigilantes']) > 0): ?>
                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs">
                                    <?= count($jury['vigilantes']) ?> Vigilante(s)
                                </span>
                                <?php else: ?>
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs">⚠️ Sem Vigilantes</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3 pt-3 border-t border-gray-100">
                            <!-- Supervisor -->
                            <div>
                                <div class="text-xs font-medium text-gray-500 mb-1">👔 SUPERVISOR</div>
                                <?php if ($jury['supervisor_name']): ?>
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($jury['supervisor_name']) ?></div>
                                <?php else: ?>
                                <div class="text-sm text-red-600 italic">Não alocado</div>
                                <?php endif; ?>
                            </div>

                            <!-- Vigilantes -->
                            <div>
                                <div class="text-xs font-medium text-gray-500 mb-1">👁️ VIGILANTES</div>
                                <?php if (!empty($jury['vigilantes'])): ?>
                                <div class="text-sm text-gray-900">
                                    <?= implode(', ', array_map('htmlspecialchars', $jury['vigilantes'])) ?>
                                </div>
                                <?php else: ?>
                                <div class="text-sm text-red-600 italic">Não alocados</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <?php if (empty($juries)): ?>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <span class="text-4xl mb-3 block">📭</span>
            <p class="text-gray-600">Nenhum júri futuro encontrado.</p>
        </div>
        <?php endif; ?>

        <!-- Ações -->
        <div class="mt-6 flex justify-center space-x-4">
            <a href="alocar_equipe.php" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                Gerenciar Alocações
            </a>
            <a href="distribuicao_automatica.php" class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                🤖 Distribuição Automática
            </a>
        </div>

    </div>
</body>
</html>
