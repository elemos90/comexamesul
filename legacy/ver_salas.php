<?php
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordenador') {
    header('Location: login_direto.php'); exit;
}

require_once __DIR__ . '/../bootstrap.php';
use App\Models\ExamRoom;
use App\Models\ExamLocation;

$roomModel = new ExamRoom();
$locationModel = new ExamLocation();
$locationId = isset($_GET['location']) ? (int)$_GET['location'] : 0;
$selectedLocation = null;
$rooms = [];

if ($locationId > 0) {
    $selectedLocation = $locationModel->find($locationId);
    $rooms = $roomModel->withJuryCount($locationId);
}
$locations = $locationModel->getActive();

$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'create') {
    try {
        $roomModel->create([
            'location_id' => (int)$_POST['location_id'],
            'code' => strtoupper($_POST['code']),
            'name' => $_POST['name'],
            'capacity' => (int)$_POST['capacity'],
            'floor' => $_POST['floor'] ?? null,
            'building' => $_POST['building'] ?? null,
            'active' => 1,
            'created_by' => $_SESSION['user_id'],
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
        $message = 'success';
        $rooms = $roomModel->withJuryCount((int)$_POST['location_id']);
    } catch (Exception $e) {
        $message = 'error';
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Salas</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 py-4 flex justify-between">
            <div class="flex items-center space-x-4">
                <a href="ver_locais.php" class="text-blue-600">← Voltar</a>
                <h1 class="text-xl font-bold">🏛️ Salas</h1>
            </div>
            <a href="logout_direto.php" class="text-red-600">Sair</a>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <?php if ($message === 'success'): ?>
        <div class="mb-4 p-4 bg-green-100 text-green-800 rounded">✅ Sala criada!</div>
        <?php endif; ?>

        <div class="bg-white rounded-lg p-6 mb-6">
            <label class="block mb-2 font-medium">Selecione um Local:</label>
            <select onchange="window.location='ver_salas.php?location='+this.value" class="w-full md:w-1/2 px-3 py-2 border rounded">
                <option>-- Escolha --</option>
                <?php foreach ($locations as $loc): ?>
                <option value="<?=$loc['id']?>" <?=$locationId===$loc['id']?'selected':''?>><?=htmlspecialchars($loc['name'])?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <?php if ($selectedLocation): ?>
        <div class="flex justify-between mb-4">
            <h2 class="text-2xl font-bold"><?=htmlspecialchars($selectedLocation['name'])?></h2>
            <button onclick="document.getElementById('modal').classList.remove('hidden')" class="bg-blue-600 text-white px-4 py-2 rounded">+ Nova Sala</button>
        </div>

        <div class="bg-white rounded-lg overflow-hidden">
            <table class="min-w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Capacidade</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Andar/Bloco</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <?php foreach ($rooms as $r): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 font-mono font-semibold"><?=htmlspecialchars($r['code'])?></td>
                        <td class="px-6 py-4"><?=htmlspecialchars($r['name'])?></td>
                        <td class="px-6 py-4"><?=$r['capacity']?> pessoas</td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?=htmlspecialchars(($r['floor']??'').($r['building']?' - '.$r['building']:''))?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div id="modal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
            <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                <h3 class="text-lg font-bold mb-4">Nova Sala</h3>
                <form method="POST" class="space-y-4">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="location_id" value="<?=$locationId?>">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm mb-1">Código *</label>
                            <input type="text" name="code" required class="w-full px-3 py-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Capacidade *</label>
                            <input type="number" name="capacity" required value="30" class="w-full px-3 py-2 border rounded">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm mb-1">Nome *</label>
                        <input type="text" name="name" required class="w-full px-3 py-2 border rounded">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm mb-1">Andar</label>
                            <input type="text" name="floor" class="w-full px-3 py-2 border rounded">
                        </div>
                        <div>
                            <label class="block text-sm mb-1">Bloco</label>
                            <input type="text" name="building" class="w-full px-3 py-2 border rounded">
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2">
                        <button type="button" onclick="document.getElementById('modal').classList.add('hidden')" class="px-4 py-2 text-gray-600">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Criar</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
