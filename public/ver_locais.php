<?php
/**
 * VER LOCAIS - Gestão de Locais
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordenador') {
    header('Location: login_direto.php');
    exit;
}

require_once __DIR__ . '/../bootstrap.php';

use App\Models\ExamLocation;

$locationModel = new ExamLocation();

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            try {
                $locationModel->create([
                    'code' => strtoupper($_POST['code']),
                    'name' => $_POST['name'],
                    'address' => $_POST['address'] ?? null,
                    'city' => $_POST['city'] ?? null,
                    'capacity' => !empty($_POST['capacity']) ? (int)$_POST['capacity'] : null,
                    'description' => $_POST['description'] ?? null,
                    'active' => 1,
                    'created_by' => $_SESSION['user_id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $message = 'Local criado com sucesso!';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Erro: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

$locations = $locationModel->withDetails();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Locais - Portal de Exames</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="dashboard_direto.php" class="text-blue-600 hover:text-blue-800">← Voltar</a>
                    <h1 class="text-xl font-bold text-gray-900">📍 Locais</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-600"><?= htmlspecialchars($_SESSION['user_name']) ?></span>
                    <a href="logout_direto.php" class="text-sm text-red-600 hover:text-red-800">Sair</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        
        <?php if ($message): ?>
        <div class="mb-6 p-4 rounded-lg <?= $messageType === 'success' ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
        <?php endif; ?>

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Gestão de Locais</h2>
                <p class="text-gray-600 mt-1">Locais de realização de exames</p>
            </div>
            <button onclick="document.getElementById('modal-create').classList.remove('hidden')" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                + Novo Local
            </button>
        </div>

        <!-- Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($locations as $loc): ?>
            <div class="bg-white rounded-lg shadow-sm border p-6 hover:shadow-md transition">
                <div class="flex justify-between items-start mb-3">
                    <span class="text-xs font-mono font-semibold text-gray-600 bg-gray-100 px-2 py-1 rounded">
                        <?= htmlspecialchars($loc['code']) ?>
                    </span>
                    <?php if ((int)$loc['active'] === 1): ?>
                    <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-800">Ativo</span>
                    <?php endif; ?>
                </div>
                <h3 class="text-lg font-bold text-gray-900 mb-2"><?= htmlspecialchars($loc['name']) ?></h3>
                <?php if ($loc['address'] || $loc['city']): ?>
                <p class="text-sm text-gray-600 mb-3">
                    📍 <?= htmlspecialchars($loc['address'] ?? '') ?>
                    <?= $loc['city'] ? ', ' . htmlspecialchars($loc['city']) : '' ?>
                </p>
                <?php endif; ?>
                <div class="flex items-center space-x-4 text-sm text-gray-500">
                    <span>🏛️ <?= (int)$loc['room_count'] ?> salas</span>
                    <span>📅 <?= (int)$loc['jury_count'] ?> júris</span>
                </div>
                <div class="mt-4">
                    <a href="ver_salas.php?location=<?= $loc['id'] ?>" class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                        Ver Salas →
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Modal Criar -->
    <div id="modal-create" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-lg w-full mx-4 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Novo Local</h3>
                <button onclick="document.getElementById('modal-create').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                        <input type="text" name="code" required maxlength="20" class="w-full px-3 py-2 border rounded-lg" placeholder="Ex: CC">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Capacidade Total</label>
                        <input type="number" name="capacity" class="w-full px-3 py-2 border rounded-lg">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" required maxlength="150" class="w-full px-3 py-2 border rounded-lg" placeholder="Ex: Campus Central">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Endereço</label>
                    <input type="text" name="address" maxlength="255" class="w-full px-3 py-2 border rounded-lg">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Cidade</label>
                    <input type="text" name="city" maxlength="100" class="w-full px-3 py-2 border rounded-lg" placeholder="Ex: Beira">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Descrição</label>
                    <textarea name="description" rows="3" class="w-full px-3 py-2 border rounded-lg"></textarea>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="document.getElementById('modal-create').classList.add('hidden')" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Criar</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
