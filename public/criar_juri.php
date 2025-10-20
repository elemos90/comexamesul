<?php
/**
 * CRIAR JÚRI - Com Dropdowns de Disciplinas, Locais e Salas
 */

session_start();

if (!isset($_SESSION['user_id']) || !in_array($_SESSION['user_role'], ['coordenador', 'membro'])) {
    header('Location: login_direto.php');
    exit;
}

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Connection;
use App\Models\Discipline;
use App\Models\ExamLocation;
use App\Models\ExamRoom;

$db = Connection::getInstance();
$disciplineModel = new Discipline();
$locationModel = new ExamLocation();
$roomModel = new ExamRoom();

$message = '';
$messageType = '';

// Processar criação
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    try {
        // Validar conflito de sala
        $roomId = !empty($_POST['room_id']) ? (int)$_POST['room_id'] : null;
        $examDate = $_POST['exam_date'];
        $startTime = $_POST['start_time'];
        $endTime = $_POST['end_time'];
        
        if ($roomId) {
            $conflict = $db->prepare("
                SELECT COUNT(*) as count 
                FROM juries 
                WHERE room_id = :room_id 
                AND exam_date = :exam_date 
                AND (
                    (:start_time < end_time AND :end_time > start_time)
                )
            ");
            $conflict->execute([
                'room_id' => $roomId,
                'exam_date' => $examDate,
                'start_time' => $startTime,
                'end_time' => $endTime
            ]);
            
            if ($conflict->fetch(PDO::FETCH_ASSOC)['count'] > 0) {
                throw new Exception('Conflito: Esta sala já está ocupada neste horário.');
            }
        }
        
        // Inserir júri
        $stmt = $db->prepare("
            INSERT INTO juries (
                discipline_id, location_id, room_id,
                exam_date, start_time, end_time,
                candidates_quota, notes,
                created_by, created_at, updated_at
            ) VALUES (
                :discipline_id, :location_id, :room_id,
                :exam_date, :start_time, :end_time,
                :candidates_quota, :notes,
                :created_by, NOW(), NOW()
            )
        ");
        
        $stmt->execute([
            'discipline_id' => !empty($_POST['discipline_id']) ? (int)$_POST['discipline_id'] : null,
            'location_id' => !empty($_POST['location_id']) ? (int)$_POST['location_id'] : null,
            'room_id' => $roomId,
            'exam_date' => $examDate,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'candidates_quota' => (int)$_POST['candidates_quota'],
            'notes' => $_POST['notes'] ?? null,
            'created_by' => $_SESSION['user_id']
        ]);
        
        $message = 'Júri criado com sucesso!';
        $messageType = 'success';
        
    } catch (Exception $e) {
        $message = $e->getMessage();
        $messageType = 'error';
    }
}

// Buscar dados
$disciplines = $disciplineModel->getActive();
$locations = $locationModel->getActive();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Júri</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between">
            <div class="flex items-center space-x-4">
                <a href="dashboard_direto.php" class="text-blue-600">← Voltar</a>
                <h1 class="text-xl font-bold">📅 Criar Júri</h1>
            </div>
            <a href="logout_direto.php" class="text-red-600">Sair</a>
        </div>
    </nav>

    <div class="max-w-4xl mx-auto px-4 py-8">
        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow-sm border p-6">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Novo Júri de Exame</h2>
            
            <form method="POST" class="space-y-6">
                <input type="hidden" name="action" value="create">
                
                <!-- Disciplina -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        📚 Disciplina *
                    </label>
                    <select name="discipline_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Selecione uma disciplina --</option>
                        <?php foreach ($disciplines as $d): ?>
                        <option value="<?= $d['id'] ?>">
                            <?= htmlspecialchars($d['code']) ?> - <?= htmlspecialchars($d['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Data e Horário -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">📅 Data *</label>
                        <input type="date" name="exam_date" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🕐 Início *</label>
                        <input type="time" name="start_time" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">🕐 Fim *</label>
                        <input type="time" name="end_time" required class="w-full px-3 py-2 border rounded-lg">
                    </div>
                </div>

                <!-- Local -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        📍 Local *
                    </label>
                    <select name="location_id" id="location_id" required onchange="loadRooms(this.value)" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Selecione um local --</option>
                        <?php foreach ($locations as $l): ?>
                        <option value="<?= $l['id'] ?>">
                            <?= htmlspecialchars($l['name']) ?> (<?= htmlspecialchars($l['code']) ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Sala (carregada dinamicamente) -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        🏛️ Sala *
                    </label>
                    <select name="room_id" id="room_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Primeiro selecione um local --</option>
                    </select>
                    <p class="mt-1 text-sm text-gray-500" id="room_info"></p>
                </div>

                <!-- Vagas -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">👥 Vagas para Candidatos *</label>
                    <input type="number" name="candidates_quota" required min="1" value="30" class="w-full px-3 py-2 border rounded-lg">
                </div>

                <!-- Observações -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">📝 Observações</label>
                    <textarea name="notes" rows="3" class="w-full px-3 py-2 border rounded-lg" placeholder="Informações adicionais sobre o júri"></textarea>
                </div>

                <!-- Botões -->
                <div class="flex justify-end space-x-3 pt-4">
                    <a href="dashboard_direto.php" class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium">
                        Criar Júri
                    </button>
                </div>
            </form>
        </div>

        <!-- Info Box -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-bold text-blue-900 mb-2">ℹ️ Validação Automática</h3>
            <ul class="text-sm text-blue-800 space-y-1 ml-4">
                <li>✅ Sistema verifica automaticamente conflitos de horário na mesma sala</li>
                <li>✅ Dropdowns carregam apenas disciplinas e locais ativos</li>
                <li>✅ Salas são filtradas automaticamente pelo local selecionado</li>
            </ul>
        </div>
    </div>

    <script>
        async function loadRooms(locationId) {
            const roomSelect = document.getElementById('room_id');
            const roomInfo = document.getElementById('room_info');
            
            if (!locationId) {
                roomSelect.innerHTML = '<option value="">-- Primeiro selecione um local --</option>';
                roomInfo.textContent = '';
                return;
            }
            
            roomSelect.innerHTML = '<option value="">Carregando...</option>';
            roomInfo.textContent = '⏳ Carregando salas...';
            
            try {
                const response = await fetch(`get_rooms.php?location_id=${locationId}`);
                const rooms = await response.json();
                
                if (rooms.length === 0) {
                    roomSelect.innerHTML = '<option value="">Nenhuma sala disponível neste local</option>';
                    roomInfo.textContent = '⚠️ Este local não tem salas cadastradas';
                    return;
                }
                
                roomSelect.innerHTML = '<option value="">-- Selecione uma sala --</option>';
                rooms.forEach(room => {
                    const option = document.createElement('option');
                    option.value = room.id;
                    option.textContent = `${room.code} - ${room.name} (${room.capacity} vagas)`;
                    roomSelect.appendChild(option);
                });
                
                roomInfo.textContent = `✅ ${rooms.length} sala(s) disponível(is)`;
                
            } catch (error) {
                roomSelect.innerHTML = '<option value="">Erro ao carregar salas</option>';
                roomInfo.textContent = '❌ Erro ao carregar salas';
                console.error('Erro:', error);
            }
        }
    </script>
</body>
</html>
