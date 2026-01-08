<?php
/**
 * DASHBOARD DIRETO - Após Login
 */

session_start();

// Verificar se está logado
if (!isset($_SESSION['user_id'])) {
    header('Location: login_direto.php');
    exit;
}

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Connection;

$user = [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'],
    'email' => $_SESSION['user_email'],
    'role' => $_SESSION['user_role']
];

// Buscar estatísticas
$db = Connection::getInstance();

try {
    $totalJuries = $db->query("SELECT COUNT(*) as count FROM juries")->fetch(PDO::FETCH_ASSOC)['count'];
    $totalDisciplines = $db->query("SELECT COUNT(*) as count FROM disciplines")->fetch(PDO::FETCH_ASSOC)['count'];
    $totalLocations = $db->query("SELECT COUNT(*) as count FROM exam_locations")->fetch(PDO::FETCH_ASSOC)['count'];
    $totalRooms = $db->query("SELECT COUNT(*) as count FROM exam_rooms")->fetch(PDO::FETCH_ASSOC)['count'];
} catch (Exception $e) {
    $totalJuries = $totalDisciplines = $totalLocations = $totalRooms = 0;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Portal de Exames</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen">
        <!-- Navbar -->
        <nav class="bg-white shadow-sm border-b">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <h1 class="text-xl font-bold text-gray-900">Portal da Comissão de Exames</h1>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-sm text-gray-600">👤 <?= htmlspecialchars($user['name']) ?></span>
                        <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded"><?= htmlspecialchars($user['role']) ?></span>
                        <a href="logout_direto.php" class="text-sm text-red-600 hover:text-red-800">Sair</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Welcome -->
            <div class="bg-white rounded-lg shadow-sm border p-6 mb-6">
                <h2 class="text-2xl font-bold text-gray-900 mb-2">Bem-vindo, <?= htmlspecialchars($user['name']) ?>! 👋</h2>
                <p class="text-gray-600">Sistema de Gestão de Júris de Exames de Admissão</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-500 rounded-md p-3">
                            <span class="text-white text-2xl">📅</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Júris Cadastrados</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $totalJuries ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500 rounded-md p-3">
                            <span class="text-white text-2xl">📚</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Disciplinas</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $totalDisciplines ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-500 rounded-md p-3">
                            <span class="text-white text-2xl">📍</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Locais</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $totalLocations ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border p-6">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-orange-500 rounded-md p-3">
                            <span class="text-white text-2xl">🏛️</span>
                        </div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-600">Salas</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $totalRooms ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white rounded-lg shadow-sm border p-6">
                <h3 class="text-lg font-bold text-gray-900 mb-4">🚀 Acesso Rápido</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    
                    <?php if ($user['role'] === 'coordenador'): ?>
                    <a href="ver_disciplinas.php" class="block p-4 bg-blue-50 hover:bg-blue-100 rounded-lg border border-blue-200 transition">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">📚</span>
                            <div>
                                <div class="font-semibold text-gray-900">Disciplinas</div>
                                <div class="text-sm text-gray-600">Gerir disciplinas</div>
                            </div>
                        </div>
                    </a>

                    <a href="ver_locais.php" class="block p-4 bg-purple-50 hover:bg-purple-100 rounded-lg border border-purple-200 transition">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">📍</span>
                            <div>
                                <div class="font-semibold text-gray-900">Locais</div>
                                <div class="text-sm text-gray-600">Gerir locais</div>
                            </div>
                        </div>
                    </a>

                    <a href="ver_salas.php" class="block p-4 bg-orange-50 hover:bg-orange-100 rounded-lg border border-orange-200 transition">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">🏛️</span>
                            <div>
                                <div class="font-semibold text-gray-900">Salas</div>
                                <div class="text-sm text-gray-600">Gerir salas</div>
                            </div>
                        </div>
                    </a>
                    <?php endif; ?>

                    <a href="test_master_data.php" class="block p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">🔍</span>
                            <div>
                                <div class="font-semibold text-gray-900">Ver Dados</div>
                                <div class="text-sm text-gray-600">Verificar instalação</div>
                            </div>
                        </div>
                    </a>

                    <a href="install.php" class="block p-4 bg-gray-50 hover:bg-gray-100 rounded-lg border border-gray-200 transition">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">⚙️</span>
                            <div>
                                <div class="font-semibold text-gray-900">Instalação</div>
                                <div class="text-sm text-gray-600">Verificar setup</div>
                            </div>
                        </div>
                    </a>

                    <a href="criar_juri.php" class="block p-4 bg-green-50 hover:bg-green-100 rounded-lg border border-green-200 transition">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">📅</span>
                            <div>
                                <div class="font-semibold text-gray-900">Criar Júri</div>
                                <div class="text-sm text-gray-600">Com dropdowns</div>
                            </div>
                        </div>
                    </a>

                    <a href="relatorios.php" class="block p-4 bg-indigo-50 hover:bg-indigo-100 rounded-lg border border-indigo-200 transition">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">📊</span>
                            <div>
                                <div class="font-semibold text-gray-900">Relatórios</div>
                                <div class="text-sm text-gray-600">Por disciplina/local</div>
                            </div>
                        </div>
                    </a>

                    <a href="alocar_equipe.php" class="block p-4 bg-yellow-50 hover:bg-yellow-100 rounded-lg border border-yellow-200 transition">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">👥</span>
                            <div>
                                <div class="font-semibold text-gray-900">Alocar Equipe</div>
                                <div class="text-sm text-gray-600">Vigilantes e supervisores</div>
                            </div>
                        </div>
                    </a>

                    <a href="mapa_alocacoes.php" class="block p-4 bg-pink-50 hover:bg-pink-100 rounded-lg border border-pink-200 transition">
                        <div class="flex items-center">
                            <span class="text-2xl mr-3">🗺️</span>
                            <div>
                                <div class="font-semibold text-gray-900">Mapa de Alocações</div>
                                <div class="text-sm text-gray-600">Visualização timeline</div>
                            </div>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Info -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <strong>ℹ️ Nota:</strong> Este é um dashboard temporário direto. 
                    O sistema completo com rotas será configurado em breve. 
                    Por enquanto, use os links acima para navegar.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
