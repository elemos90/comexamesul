<?php
/**
 * RELATÓRIOS - Por Disciplina e Local
 */

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login_direto.php');
    exit;
}

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Connection;

$db = Connection::getInstance();

// Relatório por Disciplina
$byDiscipline = $db->query("
    SELECT 
        d.code,
        d.name as discipline_name,
        COUNT(j.id) as total_juries,
        SUM(j.candidates_quota) as total_candidates,
        COUNT(DISTINCT j.location_id) as total_locations
    FROM disciplines d
    LEFT JOIN juries j ON j.discipline_id = d.id
    WHERE d.active = 1
    GROUP BY d.id, d.code, d.name
    ORDER BY d.code
")->fetchAll(PDO::FETCH_ASSOC);

// Relatório por Local
$byLocation = $db->query("
    SELECT 
        el.code,
        el.name as location_name,
        el.city,
        COUNT(DISTINCT er.id) as total_rooms,
        COUNT(DISTINCT j.id) as total_juries,
        SUM(j.candidates_quota) as total_candidates
    FROM exam_locations el
    LEFT JOIN exam_rooms er ON er.location_id = el.id
    LEFT JOIN juries j ON j.location_id = el.id
    WHERE el.active = 1
    GROUP BY el.id, el.code, el.name, el.city
    ORDER BY el.code
")->fetchAll(PDO::FETCH_ASSOC);

// Próximos Exames
$upcomingExams = $db->query("
    SELECT 
        j.*,
        d.code as discipline_code,
        d.name as discipline_name,
        el.name as location_name,
        er.name as room_name,
        er.capacity as room_capacity
    FROM juries j
    LEFT JOIN disciplines d ON d.id = j.discipline_id
    LEFT JOIN exam_locations el ON el.id = j.location_id
    LEFT JOIN exam_rooms er ON er.id = j.room_id
    WHERE j.exam_date >= CURDATE()
    ORDER BY j.exam_date, j.start_time
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Relatórios</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between">
            <div class="flex items-center space-x-4">
                <a href="dashboard_direto.php" class="text-blue-600">← Voltar</a>
                <h1 class="text-xl font-bold">📊 Relatórios</h1>
            </div>
            <a href="logout_direto.php" class="text-red-600">Sair</a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8 space-y-6">
        
        <!-- Resumo Geral -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-lg p-6 shadow-sm border">
                <div class="text-3xl font-bold text-blue-600"><?= count($byDiscipline) ?></div>
                <div class="text-sm text-gray-600 mt-1">Disciplinas Ativas</div>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-sm border">
                <div class="text-3xl font-bold text-purple-600"><?= count($byLocation) ?></div>
                <div class="text-sm text-gray-600 mt-1">Locais Ativos</div>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-sm border">
                <div class="text-3xl font-bold text-green-600"><?= array_sum(array_column($byDiscipline, 'total_juries')) ?></div>
                <div class="text-sm text-gray-600 mt-1">Total de Júris</div>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-sm border">
                <div class="text-3xl font-bold text-orange-600"><?= array_sum(array_column($byDiscipline, 'total_candidates')) ?></div>
                <div class="text-sm text-gray-600 mt-1">Total de Vagas</div>
            </div>
        </div>

        <!-- Relatório por Disciplina -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">📚 Júris por Disciplina</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disciplina</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Júris</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vagas</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Locais</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($byDiscipline as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-mono font-semibold"><?= htmlspecialchars($item['code']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($item['discipline_name']) ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    <?= (int)$item['total_juries'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center"><?= (int)$item['total_candidates'] ?></td>
                            <td class="px-6 py-4 text-center"><?= (int)$item['total_locations'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Relatório por Local -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">📍 Júris por Local</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Local</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cidade</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Salas</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Júris</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase">Vagas</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($byLocation as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap font-mono font-semibold"><?= htmlspecialchars($item['code']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($item['location_name']) ?></td>
                            <td class="px-6 py-4"><?= htmlspecialchars($item['city'] ?? '-') ?></td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                                    <?= (int)$item['total_rooms'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                    <?= (int)$item['total_juries'] ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center"><?= (int)$item['total_candidates'] ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Próximos Exames -->
        <?php if (!empty($upcomingExams)): ?>
        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">📅 Próximos Exames</h2>
            <div class="space-y-3">
                <?php foreach ($upcomingExams as $exam): ?>
                <div class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="font-bold text-gray-900">
                                <?= htmlspecialchars($exam['discipline_code'] ?? 'N/A') ?> - 
                                <?= htmlspecialchars($exam['discipline_name'] ?? 'Sem disciplina') ?>
                            </div>
                            <div class="text-sm text-gray-600 mt-1">
                                📍 <?= htmlspecialchars($exam['location_name'] ?? 'Sem local') ?>
                                <?php if ($exam['room_name']): ?>
                                    - 🏛️ <?= htmlspecialchars($exam['room_name']) ?>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-sm font-medium text-gray-900">
                                <?= date('d/m/Y', strtotime($exam['exam_date'])) ?>
                            </div>
                            <div class="text-xs text-gray-600">
                                <?= substr($exam['start_time'], 0, 5) ?> - <?= substr($exam['end_time'], 0, 5) ?>
                            </div>
                            <div class="text-xs text-blue-600 mt-1">
                                👥 <?= $exam['candidates_quota'] ?> vagas
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

    </div>
</body>
</html>
