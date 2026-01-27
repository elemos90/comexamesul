<?php
use App\Utils\Auth;

$authUser = Auth::user();
$isAuthenticated = Auth::check();
$messages = flash_messages();
$title = $title ?? env('APP_NAME', 'Portal');
$isPublic = $isPublic ?? false;
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token()) ?>">
    <title><?= htmlspecialchars($title) ?></title>

    <!-- Global JavaScript Configuration -->
    <script>
        // Base URL para uso em arquivos JavaScript externos
        window.BASE_URL = '<?= url('/') ?>';
        window.CSRF_TOKEN = '<?= htmlspecialchars(csrf_token()) ?>';

        // Helper function para construir URLs
        window.appUrl = function (path) {
            return window.BASE_URL + (path.startsWith('/') ? path.substring(1) : path);
        };
    </script>

    <!-- Favicon e Icons -->
    <link rel="icon" type="image/x-icon" href="<?= url('/assets/images/favicon.ico') ?>">
    <link rel="shortcut icon" type="image/x-icon" href="<?= url('/assets/images/favicon.ico') ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= url('/assets/images/logo_unilicungo.png') ?>">

    <!-- Tailwind CSS (Compilado Localmente) -->
    <link rel="stylesheet" href="<?= url('/css/tailwind.css') ?>">

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Fontes e Ícones -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;700&family=Inter:wght@400;500;600&display=swap"
        rel="stylesheet">
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:wght@300;400;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <link rel="stylesheet" href="<?= url('/css/components.css') ?>">
    <style>
        /* Animação de entrada dos toasts */
        @keyframes toast-slide-in {
            from {
                opacity: 0;
                transform: translateX(100%);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .toast-item {
            animation: toast-slide-in 0.3s ease-out;
        }

        /* Layout fixo: header e sidebar fixos, apenas conteúdo com scroll */
        /* Apenas para páginas autenticadas e em telas desktop */
        @media (min-width: 768px) {

            <?php if (!$isPublic): ?>
                body,
                html {
                    height: 100vh;
                    overflow: hidden;
                }

            <?php endif; ?>
        }

        /* Em mobile, permitimos scroll no body se necessário, mas evitamos horizontal */
        @media (max-width: 767px) {

            body,
            html {
                overflow-x: hidden;
                height: auto;
                min-height: 100vh;
            }
        }

        /* Páginas públicas permitem scroll normal */
        <?php if ($isPublic): ?>
            body,
            html {
                overflow: auto;
                height: auto;
            }

        <?php endif; ?>

        /* ===================================
           SCROLLBARS MODERNOS E DISCRETOS
           =================================== */

        /* Firefox */
        * {
            scrollbar-width: thin;
            scrollbar-color: rgba(156, 163, 175, 0.3) transparent;
        }

        /* Chrome, Edge, Safari */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.3);
            border-radius: 10px;
            transition: background 0.2s ease;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.5);
        }

        /* Scrollbar invisível até passar o mouse - apenas para sidebar */
        aside ::-webkit-scrollbar-thumb {
            background: transparent;
        }

        aside:hover ::-webkit-scrollbar-thumb {
            background: rgba(156, 163, 175, 0.3);
        }

        aside:hover ::-webkit-scrollbar-thumb:hover {
            background: rgba(156, 163, 175, 0.5);
        }
    </style>
</head>

<body class="bg-gray-100 text-gray-800 <?= $isPublic ? '' : 'h-screen overflow-hidden' ?>"
    x-data="{ mobileMenuOpen: false }">
    <?php
    $alertConfig = [
        'success' => [
            'bg' => 'bg-gradient-to-r from-emerald-50 to-green-50',
            'border' => 'border-emerald-400',
            'text' => 'text-emerald-900',
            'icon_bg' => 'bg-emerald-500',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>'
        ],
        'error' => [
            'bg' => 'bg-gradient-to-r from-red-50 to-rose-50',
            'border' => 'border-red-400',
            'text' => 'text-red-900',
            'icon_bg' => 'bg-red-500',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>'
        ],
        'warning' => [
            'bg' => 'bg-gradient-to-r from-amber-50 to-yellow-50',
            'border' => 'border-amber-400',
            'text' => 'text-amber-900',
            'icon_bg' => 'bg-amber-500',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>'
        ],
        'info' => [
            'bg' => 'bg-gradient-to-r from-blue-50 to-sky-50',
            'border' => 'border-blue-400',
            'text' => 'text-blue-900',
            'icon_bg' => 'bg-blue-500',
            'icon' => '<svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>'
        ]
    ];
    ?>

    <?php if (!empty($messages)): ?>
        <div id="toast-container" class="pointer-events-none fixed top-4 right-4 z-50 space-y-2 max-w-sm">
            <?php foreach ($messages as $type => $items): ?>
                <?php $config = $alertConfig[$type] ?? $alertConfig['info']; ?>
                <?php foreach ($items as $message): ?>
                    <div class="toast-item pointer-events-auto rounded-lg border-2 shadow-xl backdrop-blur-sm <?= e($config['bg']) ?> <?= e($config['border']) ?>"
                        data-type="<?= e($type) ?>" data-message="<?= e($message) ?>">
                        <div class="flex items-center gap-2.5 px-3 py-2.5">
                            <!-- Ícone -->
                            <div class="flex-shrink-0 rounded-full p-1 <?= e($config['icon_bg']) ?>">
                                <?= $config['icon'] ?>
                            </div>

                            <!-- Mensagem -->
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium <?= e($config['text']) ?> leading-tight">
                                    <?= e($message) ?>
                                </p>
                            </div>

                            <!-- Botão Fechar -->
                            <button type="button"
                                class="toast-close flex-shrink-0 rounded-full p-0.5 hover:bg-black/10 active:bg-black/20 transition-all duration-150 <?= e($config['text']) ?>"
                                aria-label="Fechar" data-dismiss="toast">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?php if ($isPublic): ?>
        <main class="min-h-screen flex flex-col">
            <?php include view_path('partials/navbar_public.php'); ?>
            <div class="flex-1">
                <?= $content ?>
            </div>
        </main>
    <?php else: ?>
        <div class="flex h-screen overflow-hidden bg-gray-100">
            <!-- Sidebar (Esquerda - Altura Total) -->
            <div class="flex-shrink-0 h-full z-20 relative print:hidden">
                <?php include view_path('partials/sidebar.php'); ?>
            </div>

            <!-- Área Direita (Navbar + Conteúdo) - Com margem para separação em desktop -->
            <div class="flex-1 flex flex-col h-full overflow-hidden md:m-3">
                <!-- Container com fundo branco para header + conteúdo -->
                <div class="flex-1 flex flex-col bg-white shadow-sm md:border md:border-gray-200 overflow-hidden">
                    <!-- Navbar (Topo da área direita) -->
                    <div class="w-full z-10 bg-white border-b border-gray-100 print:hidden">
                        <?php include view_path('partials/navbar.php'); ?>
                    </div>

                    <!-- Conteúdo Principal (Scroll independente) -->
                    <main class="flex-1 overflow-y-auto overflow-x-hidden p-4 md:p-6 bg-gray-50 scroll-smooth">
                        <div class="max-w-[1600px] mx-auto">
                            <?= $content ?>
                        </div>
                    </main>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Modal de Ajuda Contextual -->
    <?php if (!$isPublic): ?>
        <?php include view_path('partials/help_modal.php'); ?>
    <?php endif; ?>

    <script src="<?= url('/assets/libs/sortable.min.js') ?>"></script>
    <!-- jQuery (necessário para toastr.js) -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"
        integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script src="<?= url('/assets/js/app.js?v=' . time()) ?>"></script>

    <!-- Sistema de Ajuda -->
    <?php if (!$isPublic): ?>
        <script src="<?= url('/js/help.js?v=' . time()) ?>"></script>
        <script>
            // Injeta dados de ajuda no JavaScript
            <?php
            // Carrega conteúdo de ajuda para JavaScript
            $userRole = $authUser['role'] ?? 'vigilante';
            $pages = [
                'dashboard',
                'availability',
                'vacancies',
                'applications',
                'juries',
                'juries-planning',
                'locations',
                'locations-templates',
                'locations-import',
                'locations-dashboard',
                'master-data-disciplines',
                'master-data-locations',
                'master-data-rooms',
                'profile'
            ];
            $helpDataJson = [];
            foreach ($pages as $page) {
                $helpDataJson[$page] = \App\Utils\HelpContent::get($page, $userRole);
            }
            ?>
            setHelpData(<?= json_encode($helpDataJson, JSON_HEX_TAG | JSON_HEX_AMP) ?>);
        </script>
    <?php endif; ?>
</body>

</html>
<?php
old_clear();
validation_errors_clear();
?>