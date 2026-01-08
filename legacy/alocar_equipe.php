<?php
/**
 * ALOCAÇÃO DE EQUIPE - Vigilantes e Supervisores
 * Sistema inteligente com validação de conflitos
 */

session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['coordenador', 'membro'])) {
    header('Location: login_direto.php');
    exit;
}

require_once __DIR__ . '/../bootstrap.php';
use App\Database\Connection;

$db = Connection::getInstance();
$message = '';
$messageType = '';

// Configurar locale para português
setlocale(LC_TIME, 'pt_BR.UTF-8', 'pt_BR', 'portuguese');

// PROCESSAR AÇÕES
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Alocar Vigilante
    if (isset($_POST['action']) && $_POST['action'] === 'add_vigilante') {
        try {
            $juryId = (int)$_POST['jury_id'];
            $vigilanteId = (int)$_POST['vigilante_id'];
            
            // VALIDAÇÃO 1: Verificar se já é supervisor neste júri
            $isSupervisor = $db->prepare("SELECT id FROM juries WHERE id = ? AND supervisor_id = ?");
            $isSupervisor->execute([$juryId, $vigilanteId]);
            if ($isSupervisor->fetch()) {
                throw new Exception('CONFLITO: Esta pessoa já é SUPERVISOR deste júri. Uma pessoa não pode ser supervisor e vigilante no mesmo júri.');
            }
            
            // VALIDAÇÃO 2: Verificar conflito de horário
            $conflict = $db->prepare("
                SELECT j2.id, j2.exam_date, j2.start_time, j2.end_time,
                       d.code as discipline_code, el.name as location_name
                FROM jury_vigilantes jv
                INNER JOIN juries j1 ON j1.id = :jury_id
                INNER JOIN juries j2 ON j2.id = jv.jury_id
                LEFT JOIN disciplines d ON d.id = j2.discipline_id
                LEFT JOIN exam_locations el ON el.id = j2.location_id
                WHERE jv.vigilante_id = :vigilante_id
                AND j2.exam_date = j1.exam_date
                AND (
                    (j2.start_time < j1.end_time AND j2.end_time > j1.start_time)
                )
            ");
            $conflict->execute(['jury_id' => $juryId, 'vigilante_id' => $vigilanteId]);
            
            if ($conflictData = $conflict->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception(sprintf(
                    'CONFLITO: Vigilante já alocado em %s às %s-%s no %s',
                    $conflictData['discipline_code'] ?? 'N/A',
                    substr($conflictData['start_time'], 0, 5),
                    substr($conflictData['end_time'], 0, 5),
                    $conflictData['location_name'] ?? 'Local'
                ));
            }
            
            // Inserir
            $stmt = $db->prepare("
                INSERT INTO jury_vigilantes (jury_id, vigilante_id, assigned_by, created_at)
                VALUES (:jury_id, :vigilante_id, :assigned_by, NOW())
            ");
            $stmt->execute([
                'jury_id' => $juryId,
                'vigilante_id' => $vigilanteId,
                'assigned_by' => $_SESSION['user_id']
            ]);
            
            $message = 'Vigilante alocado com sucesso!';
            $messageType = 'success';
            
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    }
    
    // Remover Vigilante
    if (isset($_POST['action']) && $_POST['action'] === 'remove_vigilante') {
        try {
            $stmt = $db->prepare("DELETE FROM jury_vigilantes WHERE jury_id = ? AND vigilante_id = ?");
            $stmt->execute([(int)$_POST['jury_id'], (int)$_POST['vigilante_id']]);
            $message = 'Vigilante removido!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Erro ao remover: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
    
    // Alocar Supervisor
    if (isset($_POST['action']) && $_POST['action'] === 'add_supervisor') {
        try {
            $juryId = (int)$_POST['jury_id'];
            $supervisorId = (int)$_POST['supervisor_id'];
            
            // VALIDAÇÃO 1: Verificar se já é vigilante neste júri
            $isVigilante = $db->prepare("SELECT id FROM jury_vigilantes WHERE jury_id = ? AND vigilante_id = ?");
            $isVigilante->execute([$juryId, $supervisorId]);
            if ($isVigilante->fetch()) {
                throw new Exception('CONFLITO: Esta pessoa já é VIGILANTE deste júri. Uma pessoa não pode ser supervisor e vigilante no mesmo júri.');
            }
            
            // Buscar dados do júri
            $juryData = $db->prepare("SELECT exam_date, start_time, end_time, location_id FROM juries WHERE id = ?");
            $juryData->execute([$juryId]);
            $jury = $juryData->fetch(PDO::FETCH_ASSOC);
            
            // VALIDAÇÃO 2: Verificar se supervisor já está em outro local no mesmo horário
            $conflict = $db->prepare("
                SELECT j.id, el.name as location_name
                FROM juries j
                LEFT JOIN exam_locations el ON el.id = j.location_id
                WHERE j.supervisor_id = :supervisor_id
                AND j.exam_date = :exam_date
                AND j.location_id != :location_id
                AND (
                    (j.start_time < :end_time AND j.end_time > :start_time)
                )
            ");
            $conflict->execute([
                'supervisor_id' => $supervisorId,
                'exam_date' => $jury['exam_date'],
                'location_id' => $jury['location_id'],
                'start_time' => $jury['start_time'],
                'end_time' => $jury['end_time']
            ]);
            
            if ($conflictData = $conflict->fetch(PDO::FETCH_ASSOC)) {
                throw new Exception(sprintf(
                    'CONFLITO: Supervisor já alocado em outro local (%s) neste horário',
                    $conflictData['location_name']
                ));
            }
            
            // Atualizar
            $stmt = $db->prepare("UPDATE juries SET supervisor_id = ? WHERE id = ?");
            $stmt->execute([$supervisorId, $juryId]);
            
            $message = 'Supervisor alocado com sucesso!';
            $messageType = 'success';
            
        } catch (Exception $e) {
            $message = $e->getMessage();
            $messageType = 'error';
        }
    }
    
    // Remover Supervisor
    if (isset($_POST['action']) && $_POST['action'] === 'remove_supervisor') {
        try {
            $stmt = $db->prepare("UPDATE juries SET supervisor_id = NULL WHERE id = ?");
            $stmt->execute([(int)$_POST['jury_id']]);
            $message = 'Supervisor removido!';
            $messageType = 'success';
        } catch (Exception $e) {
            $message = 'Erro: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

// BUSCAR JÚRIS FUTUROS
$juries = $db->query("
    SELECT 
        j.*,
        d.code as discipline_code,
        d.name as discipline_name,
        el.id as location_id,
        el.name as location_name,
        el.code as location_code,
        er.name as room_name,
        er.capacity as room_capacity,
        u.name as supervisor_name,
        (SELECT COUNT(*) FROM jury_vigilantes WHERE jury_id = j.id) as vigilantes_count
    FROM juries j
    LEFT JOIN disciplines d ON d.id = j.discipline_id
    LEFT JOIN exam_locations el ON el.id = j.location_id
    LEFT JOIN exam_rooms er ON er.id = j.room_id
    LEFT JOIN users u ON u.id = j.supervisor_id
    WHERE j.exam_date >= CURDATE()
    ORDER BY el.name, j.exam_date, j.start_time
")->fetchAll(PDO::FETCH_ASSOC);

// BUSCAR TODOS OS VIGILANTES DE TODOS OS JÚRIS (1 query ao invés de N)
$juryIds = array_column($juries, 'id');
$allVigilantes = [];
if (!empty($juryIds)) {
    $placeholders = str_repeat('?,', count($juryIds) - 1) . '?';
    $stmt = $db->prepare("
        SELECT jv.jury_id, u.id, u.name, u.email
        FROM jury_vigilantes jv
        INNER JOIN users u ON u.id = jv.vigilante_id
        WHERE jv.jury_id IN ($placeholders)
        ORDER BY u.name
    ");
    $stmt->execute($juryIds);
    $vigilantesData = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($vigilantesData as $vig) {
        $juryId = $vig['jury_id'];
        if (!isset($allVigilantes[$juryId])) {
            $allVigilantes[$juryId] = [];
        }
        $allVigilantes[$juryId][] = [
            'id' => $vig['id'],
            'name' => $vig['name'],
            'email' => $vig['email']
        ];
    }
}

// Associar vigilantes aos júris
foreach ($juries as &$jury) {
    $jury['vigilantes_list'] = $allVigilantes[$jury['id']] ?? [];
}
unset($jury);

// AGRUPAR POR LOCAL → DATA → JÚRIS
$groupedJuries = [];
foreach ($juries as $jury) {
    $locationKey = $jury['location_id'] ?? 0;
    $locationName = $jury['location_name'] ?? 'Sem Local';
    $date = $jury['exam_date'];
    
    if (!isset($groupedJuries[$locationKey])) {
        $groupedJuries[$locationKey] = [
            'location_name' => $locationName,
            'location_code' => $jury['location_code'] ?? 'N/A',
            'dates' => []
        ];
    }
    
    if (!isset($groupedJuries[$locationKey]['dates'][$date])) {
        $groupedJuries[$locationKey]['dates'][$date] = [];
    }
    
    $groupedJuries[$locationKey]['dates'][$date][] = $jury;
}

// BUSCAR VIGILANTES DISPONÍVEIS
$vigilantes = $db->query("
    SELECT id, name, email, phone
    FROM users
    WHERE role = 'vigilante' AND available_for_vigilance = 1
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

// BUSCAR SUPERVISORES DISPONÍVEIS
$supervisors = $db->query("
    SELECT id, name, email, phone, role
    FROM users
    WHERE supervisor_eligible = 1
    ORDER BY name
")->fetchAll(PDO::FETCH_ASSOC);

// CALCULAR ESTATÍSTICAS DE PROGRESSO
$totalJuries = count($juries);
$juriesWithSupervisor = count(array_filter($juries, fn($j) => $j['supervisor_id']));
$juriesWithVigilantes = count(array_filter($juries, fn($j) => $j['vigilantes_count'] >= 2));
$completedJuries = count(array_filter($juries, fn($j) => 
    $j['supervisor_id'] && $j['vigilantes_count'] >= 2
));
$progressPercent = $totalJuries > 0 ? ($completedJuries / $totalJuries) * 100 : 0;

// Calcular júris por status
$juriesIncomplete = $totalJuries - $completedJuries;
$juriesWithoutSupervisor = $totalJuries - $juriesWithSupervisor;
$juriesWithoutVigilantes = $totalJuries - $juriesWithVigilantes;

?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alocação de Equipe</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between">
            <div class="flex items-center space-x-4">
                <a href="dashboard_direto.php" class="text-blue-600">← Voltar</a>
                <h1 class="text-xl font-bold">👥 Alocação de Equipe</h1>
            </div>
            <div class="flex items-center space-x-4">
                <span class="text-sm text-gray-600"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                <a href="logout_direto.php" class="text-red-600">Sair</a>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        
        <?php if ($message): ?>
        <div role="alert" class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <!-- Resumo -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg p-6 shadow-sm border">
                <div class="text-3xl font-bold text-blue-600"><?= count($juries) ?></div>
                <div class="text-sm text-gray-600 mt-1">Júris a Alocar</div>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-sm border">
                <div class="text-3xl font-bold text-green-600"><?= count($vigilantes) ?></div>
                <div class="text-sm text-gray-600 mt-1">Vigilantes Disponíveis</div>
            </div>
            <div class="bg-white rounded-lg p-6 shadow-sm border">
                <div class="text-3xl font-bold text-purple-600"><?= count($supervisors) ?></div>
                <div class="text-sm text-gray-600 mt-1">Supervisores Disponíveis</div>
            </div>
        </div>

        <!-- Barra de Progresso -->
        <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
            <div class="flex justify-between items-center mb-3">
                <h3 class="font-bold text-gray-900 text-lg">📊 Progresso da Alocação</h3>
                <span class="text-2xl font-bold <?= $progressPercent >= 100 ? 'text-green-600' : ($progressPercent >= 50 ? 'text-blue-600' : 'text-orange-600') ?>">
                    <?= round($progressPercent) ?>%
                </span>
            </div>
            <div class="h-6 bg-gray-200 rounded-full overflow-hidden mb-4">
                <div class="h-full bg-gradient-to-r from-blue-500 via-purple-500 to-green-500 transition-all duration-500" 
                     style="width: <?= $progressPercent ?>%"></div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div class="text-center p-3 bg-green-50 rounded-lg border border-green-200">
                    <div class="text-2xl font-bold text-green-600"><?= $completedJuries ?></div>
                    <div class="text-gray-600 mt-1">Completos</div>
                </div>
                <div class="text-center p-3 bg-orange-50 rounded-lg border border-orange-200">
                    <div class="text-2xl font-bold text-orange-600"><?= $juriesIncomplete ?></div>
                    <div class="text-gray-600 mt-1">Incompletos</div>
                </div>
                <div class="text-center p-3 bg-red-50 rounded-lg border border-red-200">
                    <div class="text-2xl font-bold text-red-600"><?= $juriesWithoutSupervisor ?></div>
                    <div class="text-gray-600 mt-1">Sem Supervisor</div>
                </div>
                <div class="text-center p-3 bg-amber-50 rounded-lg border border-amber-200">
                    <div class="text-2xl font-bold text-amber-600"><?= $juriesWithoutVigilantes ?></div>
                    <div class="text-gray-600 mt-1">Sem Vigilantes</div>
                </div>
            </div>
        </div>

        <!-- Filtros e Busca -->
        <div class="bg-white rounded-lg shadow-sm border p-4 mb-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="relative">
                    <input 
                        type="text" 
                        id="search-jury" 
                        placeholder="🔍 Buscar júri, disciplina..."
                        class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                        onkeyup="filterJuries()"
                    >
                </div>
                <select id="filter-location" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" onchange="filterJuries()">
                    <option value="">📍 Todos os Locais</option>
                    <?php foreach ($groupedJuries as $locId => $locData): ?>
                    <option value="<?= $locId ?>"><?= htmlspecialchars($locData['location_name']) ?></option>
                    <?php endforeach; ?>
                </select>
                <select id="filter-status" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-blue-500" onchange="filterJuries()">
                    <option value="">📋 Todos os Status</option>
                    <option value="complete">✅ Completos</option>
                    <option value="incomplete">⚠️ Incompletos</option>
                    <option value="no-supervisor">❌ Sem Supervisor</option>
                    <option value="no-vigilantes">❌ Sem Vigilantes</option>
                </select>
                <button onclick="resetFilters()" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                    🔄 Limpar Filtros
                </button>
            </div>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
            <div class="flex justify-between items-start">
                <div class="flex-1">
                    <h3 class="font-bold text-blue-900 mb-2">ℹ️ Regras de Alocação</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>✅ <strong>Vigilantes:</strong> Não podem estar em 2 júris ao mesmo tempo</li>
                        <li>✅ <strong>Supervisores:</strong> Podem supervisionar múltiplos júris, mas apenas no MESMO local</li>
                        <li>✅ <strong>Uma pessoa por júri:</strong> Não pode ser supervisor E vigilante no mesmo júri</li>
                        <li>✅ Sistema valida automaticamente todos os conflitos</li>
                    </ul>
                </div>
                <a href="distribuicao_automatica.php" class="ml-4 px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-lg hover:from-blue-700 hover:to-purple-700 font-medium shadow-lg transition whitespace-nowrap">
                    🤖 Distribuição Automática
                </a>
            </div>
        </div>

        <!-- Lista de Júris Agrupada -->
        <div class="space-y-6">
            <?php foreach ($groupedJuries as $locationId => $locationData): ?>
            <?php
                // Calcular totais do local
                $locationTotalJuries = 0;
                $locationWithSupervisor = 0;
                $locationWithVigilantes = 0;
                
                foreach ($locationData['dates'] as $dateJuries) {
                    foreach ($dateJuries as $j) {
                        $locationTotalJuries++;
                        if ($j['supervisor_id']) $locationWithSupervisor++;
                        if ($j['vigilantes_count'] > 0) $locationWithVigilantes++;
                    }
                }
            ?>
            
            <!-- Card do Local -->
            <div class="bg-white rounded-lg shadow-lg border-2 border-gray-200 overflow-hidden" data-location-id="<?= $locationId ?>">
                <!-- Header do Local (Clicável) -->
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6 cursor-pointer hover:from-indigo-700 hover:to-purple-700 transition" onclick="toggleLocation(<?= $locationId ?>)">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center space-x-3">
                            <span class="text-2xl font-bold" id="location-icon-<?= $locationId ?>">▼</span>
                            <span class="text-3xl">📍</span>
                            <div>
                                <h2 class="text-2xl font-bold"><?= htmlspecialchars($locationData['location_name']) ?></h2>
                                <p class="text-indigo-100 text-sm">Código: <?= htmlspecialchars($locationData['location_code']) ?></p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold"><?= $locationTotalJuries ?></div>
                            <div class="text-sm text-indigo-100">Júri(s)</div>
                        </div>
                    </div>
                    
                    <!-- Estatísticas do Local -->
                    <div class="mt-4 flex space-x-4 text-sm">
                        <div class="bg-white/20 px-3 py-1 rounded">
                            👔 <?= $locationWithSupervisor ?>/<?= $locationTotalJuries ?> Supervisores
                        </div>
                        <div class="bg-white/20 px-3 py-1 rounded">
                            👁️ <?= $locationWithVigilantes ?>/<?= $locationTotalJuries ?> com Vigilantes
                        </div>
                    </div>
                </div>

                <!-- Datas dentro do Local -->
                <div id="location-content-<?= $locationId ?>" class="p-6 bg-gray-50">
                    <?php foreach ($locationData['dates'] as $date => $dateJuries): ?>
                    <div class="mb-6 last:mb-0">
                        <!-- Header da Data -->
                        <div class="bg-blue-100 border-l-4 border-blue-600 px-4 py-3 mb-4 rounded">
                            <div class="flex justify-between items-center">
                                <div class="flex items-center space-x-2">
                                    <span class="text-xl">📅</span>
                                    <div>
                                        <span class="font-bold text-blue-900">
                                            <?= date('d/m/Y', strtotime($date)) ?>
                                        </span>
                                        <span class="text-blue-700 text-sm ml-2">
                                            (<?= strftime('%A', strtotime($date)) ?>)
                                        </span>
                                    </div>
                                </div>
                                <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm font-medium">
                                    <?= count($dateJuries) ?> exame(s)
                                </span>
                            </div>
                        </div>

                        <!-- Júris desta Data -->
                        <div class="space-y-4">
                            <?php foreach ($dateJuries as $jury): ?>
                            <?php
                                // Usar lista de vigilantes já carregada (sem N+1)
                                $juryVigilantes = $jury['vigilantes_list'] ?? [];
                            ?>
                            
                            <div class="jury-card bg-white rounded-lg shadow-sm border-2 border-gray-200 hover:border-blue-300 transition overflow-hidden" 
                                 data-jury-id="<?= $jury['id'] ?>"
                                 data-has-supervisor="<?= $jury['supervisor_id'] ? '1' : '0' ?>"
                                 data-vigilantes-count="<?= $jury['vigilantes_count'] ?>">
                <!-- Header do Júri -->
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-4 border-b">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <div class="flex items-center space-x-3">
                                <span class="text-2xl">📚</span>
                                <div>
                                    <div class="font-bold text-gray-900 text-lg">
                                        <?= htmlspecialchars($jury['discipline_code'] ?? 'N/A') ?> - 
                                        <?= htmlspecialchars($jury['discipline_name'] ?? $jury['subject']) ?>
                                    </div>
                                    <div class="text-sm text-gray-600 mt-1">
                                        📅 <?= date('d/m/Y', strtotime($jury['exam_date'])) ?> • 
                                        🕐 <?= substr($jury['start_time'], 0, 5) ?>-<?= substr($jury['end_time'], 0, 5) ?>
                                    </div>
                                    <div class="text-sm text-gray-600">
                                        📍 <?= htmlspecialchars($jury['location_name'] ?? $jury['location']) ?> • 
                                        🏛️ <?= htmlspecialchars($jury['room_name'] ?? $jury['room']) ?> • 
                                        👥 <?= $jury['candidates_quota'] ?> vagas
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="inline-flex items-center space-x-2 text-sm">
                                <span class="supervisor-status px-3 py-1 rounded-full <?= $jury['supervisor_id'] ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= $jury['supervisor_id'] ? '✓ Supervisor' : '⚠️ Sem Supervisor' ?>
                                </span>
                                <span class="vigilantes-count px-3 py-1 rounded-full <?= $jury['vigilantes_count'] > 0 ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' ?>">
                                    <?= $jury['vigilantes_count'] ?> Vigilante(s)
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Corpo: Supervisor e Vigilantes -->
                <div class="p-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <!-- SUPERVISOR -->
                        <div>
                            <h3 class="font-bold text-gray-900 mb-3 flex items-center">
                                <span class="text-xl mr-2">👔</span>
                                Supervisor
                            </h3>
                            
                            <?php if ($jury['supervisor_id']): ?>
                            <div class="bg-green-50 border border-green-200 rounded-lg p-3 flex justify-between items-center">
                                <div>
                                    <div class="font-medium text-green-900"><?= htmlspecialchars($jury['supervisor_name']) ?></div>
                                    <div class="text-xs text-green-700">ID: <?= $jury['supervisor_id'] ?></div>
                                </div>
                                <form method="POST" class="inline">
                                    <input type="hidden" name="action" value="remove_supervisor">
                                    <input type="hidden" name="jury_id" value="<?= $jury['id'] ?>">
                                    <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">Remover</button>
                                </form>
                            </div>
                            <?php else: ?>
                            <form method="POST" class="space-y-2">
                                <input type="hidden" name="action" value="add_supervisor">
                                <input type="hidden" name="jury_id" value="<?= $jury['id'] ?>">
                                <select name="supervisor_id" required class="w-full px-3 py-2 border rounded-lg text-sm">
                                    <option value="">-- Selecione um supervisor --</option>
                                    <?php foreach ($supervisors as $sup): ?>
                                    <option value="<?= $sup['id'] ?>">
                                        <?= htmlspecialchars($sup['name']) ?> (<?= $sup['role'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 text-sm font-medium">
                                    Alocar Supervisor
                                </button>
                            </form>
                            <?php endif; ?>
                        </div>

                        <!-- VIGILANTES -->
                        <div>
                            <h3 class="font-bold text-gray-900 mb-3 flex items-center">
                                <span class="text-xl mr-2">👁️</span>
                                Vigilantes
                            </h3>
                            
                            <!-- Lista de vigilantes alocados -->
                            <?php if (!empty($juryVigilantes)): ?>
                            <div class="space-y-2 mb-3">
                                <?php foreach ($juryVigilantes as $vig): ?>
                                <div class="bg-blue-50 border border-blue-200 rounded-lg p-2 flex justify-between items-center">
                                    <div class="text-sm">
                                        <div class="font-medium text-blue-900"><?= htmlspecialchars($vig['name']) ?></div>
                                        <div class="text-xs text-blue-700"><?= htmlspecialchars($vig['email']) ?></div>
                                    </div>
                                    <form method="POST" class="inline">
                                        <input type="hidden" name="action" value="remove_vigilante">
                                        <input type="hidden" name="jury_id" value="<?= $jury['id'] ?>">
                                        <input type="hidden" name="vigilante_id" value="<?= $vig['vigilante_id'] ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-xs font-medium">✕</button>
                                    </form>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Adicionar vigilante -->
                            <form method="POST" class="space-y-2">
                                <input type="hidden" name="action" value="add_vigilante">
                                <input type="hidden" name="jury_id" value="<?= $jury['id'] ?>">
                                <select name="vigilante_id" required class="w-full px-3 py-2 border rounded-lg text-sm">
                                    <option value="">-- Adicionar vigilante --</option>
                                    <?php foreach ($vigilantes as $vig): ?>
                                    <option value="<?= $vig['id'] ?>">
                                        <?= htmlspecialchars($vig['name']) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium">
                                    + Adicionar Vigilante
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; // Fim júris da data ?>
                        </div>
                    </div>
                    <?php endforeach; // Fim datas do local ?>
                </div>
            </div>
            <?php endforeach; // Fim locais ?>
        </div>

        <?php if (empty($groupedJuries)): ?>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <span class="text-4xl mb-3 block">📭</span>
            <p class="text-gray-600">Nenhum júri futuro encontrado.</p>
            <a href="criar_juri.php" class="inline-block mt-4 px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                Criar Júri
            </a>
        </div>
        <?php endif; ?>

    </div>

    <script>
        // Função para colapsar/expandir locais
        function toggleLocation(locationId) {
            const content = document.getElementById('location-content-' + locationId);
            const icon = document.getElementById('location-icon-' + locationId);
            
            if (content.style.display === 'none') {
                content.style.display = 'block';
                icon.textContent = '▼';
            } else {
                content.style.display = 'none';
                icon.textContent = '▶';
            }
        }
        
        // Animação suave ao adicionar/remover
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const button = this.querySelector('button[type="submit"]');
                if (button) {
                    button.disabled = true;
                    button.innerHTML = '<span class="animate-pulse">⏳ Processando...</span>';
                }
            });
        });
        
        // Auto-hide mensagens após 5 segundos
        const messages = document.querySelectorAll('[role="alert"]');
        if (messages.length > 0) {
            setTimeout(() => {
                messages.forEach(msg => {
                    msg.style.transition = 'opacity 0.5s';
                    msg.style.opacity = '0';
                    setTimeout(() => msg.remove(), 500);
                });
            }, 5000);
        }
        
        // ==================================
        // FILTROS E BUSCA
        // ==================================
        
        function filterJuries() {
            const searchTerm = document.getElementById('search-jury').value.toLowerCase();
            const selectedLocation = document.getElementById('filter-location').value;
            const selectedStatus = document.getElementById('filter-status').value;
            
            // Iterar por todos os cards de júri
            document.querySelectorAll('.jury-card').forEach(card => {
                let show = true;
                
                // Filtro de busca textual
                if (searchTerm) {
                    const text = card.textContent.toLowerCase();
                    show = show && text.includes(searchTerm);
                }
                
                // Filtro de localização
                if (selectedLocation) {
                    const cardLocation = card.closest('[data-location-id]');
                    if (cardLocation) {
                        show = show && (cardLocation.dataset.locationId === selectedLocation);
                    }
                }
                
                // Filtro de status
                if (selectedStatus) {
                    const hasSupervisor = card.querySelector('.supervisor-status')?.textContent.includes('✓');
                    const vigilantesCount = parseInt(card.querySelector('.vigilantes-count')?.textContent || '0');
                    
                    if (selectedStatus === 'complete') {
                        show = show && hasSupervisor && vigilantesCount >= 2;
                    } else if (selectedStatus === 'incomplete') {
                        show = show && (!hasSupervisor || vigilantesCount < 2);
                    } else if (selectedStatus === 'no-supervisor') {
                        show = show && !hasSupervisor;
                    } else if (selectedStatus === 'no-vigilantes') {
                        show = show && vigilantesCount === 0;
                    }
                }
                
                // Mostrar ou ocultar
                card.style.display = show ? '' : 'none';
            });
            
            // Ocultar locais vazios
            document.querySelectorAll('[data-location-id]').forEach(location => {
                const visibleJuries = location.querySelectorAll('.jury-card[style=""], .jury-card:not([style*="display: none"])');
                if (visibleJuries.length === 0 && (searchTerm || selectedLocation || selectedStatus)) {
                    location.style.display = 'none';
                } else {
                    location.style.display = '';
                }
            });
            
            updateFilterStats();
        }
        
        function resetFilters() {
            document.getElementById('search-jury').value = '';
            document.getElementById('filter-location').value = '';
            document.getElementById('filter-status').value = '';
            filterJuries();
        }
        
        function updateFilterStats() {
            const visibleJuries = document.querySelectorAll('.jury-card[style=""], .jury-card:not([style*="display: none"])').length;
            const totalJuries = document.querySelectorAll('.jury-card').length;
            
            // Pode adicionar um contador de resultados aqui se desejar
            console.log(`Mostrando ${visibleJuries} de ${totalJuries} júris`);
        }
        
        // Atalhos de teclado
        document.addEventListener('keydown', (e) => {
            // Ctrl+F: Focar busca
            if (e.ctrlKey && e.key === 'f') {
                e.preventDefault();
                document.getElementById('search-jury')?.focus();
            }
            
            // Esc: Limpar filtros
            if (e.key === 'Escape') {
                resetFilters();
            }
        });
    </script>
</body>
</html>
