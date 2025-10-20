<?php
/**
 * VER DISCIPLINAS - Gestão de Disciplinas
 */

session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'coordenador') {
    header('Location: login_direto.php');
    exit;
}

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Connection;
use App\Models\Discipline;

$db = Connection::getInstance();
$disciplineModel = new Discipline();

// Processar ações
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'create') {
            try {
                $disciplineModel->create([
                    'code' => strtoupper($_POST['code']),
                    'name' => $_POST['name'],
                    'description' => $_POST['description'] ?? null,
                    'active' => 1,
                    'created_by' => $_SESSION['user_id'],
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                $message = 'Disciplina criada com sucesso!';
                $messageType = 'success';
            } catch (Exception $e) {
                $message = 'Erro: ' . $e->getMessage();
                $messageType = 'error';
            }
        }
    }
}

$disciplines = $disciplineModel->withJuryCount();
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disciplinas - Portal de Exames</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <!-- Navbar -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-4">
                    <a href="dashboard_direto.php" class="text-blue-600 hover:text-blue-800">← Voltar</a>
                    <h1 class="text-xl font-bold text-gray-900">📚 Disciplinas</h1>
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
                <h2 class="text-2xl font-bold text-gray-900">Gestão de Disciplinas</h2>
                <p class="text-gray-600 mt-1">Disciplinas cadastradas no sistema</p>
            </div>
            <button onclick="document.getElementById('modal-create').classList.remove('hidden')" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition">
                + Nova Disciplina
            </button>
        </div>

        <!-- Tabela -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Júris</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach ($disciplines as $d): ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono font-semibold text-gray-900"><?= htmlspecialchars($d['code']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-gray-900"><?= htmlspecialchars($d['name']) ?></div>
                            <?php if ($d['description']): ?>
                            <div class="text-sm text-gray-500"><?= htmlspecialchars($d['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-blue-100 text-blue-800">
                                <?= (int)$d['jury_count'] ?> júris
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ((int)$d['active'] === 1): ?>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Ativo</span>
                            <?php else: ?>
                            <span class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Inativo</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Criar -->
    <div id="modal-create" class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-bold">Nova Disciplina</h3>
                <button onclick="document.getElementById('modal-create').classList.add('hidden')" class="text-gray-500 hover:text-gray-700">✕</button>
            </div>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="create">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Código *</label>
                    <input type="text" name="code" required maxlength="20" class="w-full px-3 py-2 border rounded-lg" placeholder="Ex: MAT1">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
                    <input type="text" name="name" required maxlength="180" class="w-full px-3 py-2 border rounded-lg" placeholder="Ex: Matemática I">
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
