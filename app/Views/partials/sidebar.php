<?php
use App\Utils\Auth;

$user = Auth::user();
$current = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$role = $user['role'] ?? '';
$roleName = match ($role) {
    'coordenador' => 'Coordenador',
    'membro' => 'Membro da Comissão',
    'supervisor' => 'Supervisor',
    'vigilante' => 'Vigilante',
    default => $role
};

$items = [
    // --- MENU PRINCIPAL ---
    ['header' => 'MENU PRINCIPAL'],
    ['label' => 'Dashboard', 'href' => url('/dashboard'), 'roles' => ['vigilante', 'supervisor', 'membro', 'coordenador'], 'icon' => 'dashboard', 'color' => 'blue'],

    // --- VIGILÂNCIA ---
    ['header' => 'GESTÃO DE VIGILÂNCIA'],
    ['label' => 'Vagas', 'href' => url('/vacancies'), 'roles' => ['membro', 'coordenador'], 'icon' => 'work', 'color' => 'green'],
    ['label' => 'Candidaturas', 'href' => url('/availability'), 'roles' => ['vigilante', 'supervisor'], 'icon' => 'assignment_turned_in', 'color' => 'purple'],
    [
        'label' => 'Candidaturas',
        'href' => url('/applications'),
        'roles' => ['membro', 'coordenador'],
        'icon' => 'fact_check',
        'color' => 'purple',
        'children' => [
            ['label' => 'Dashboard', 'href' => url('/applications/dashboard')],
            ['label' => 'Lista de Vigilantes', 'href' => url('/applications')],
        ]
    ],

    // --- JÚRIS & EXAMES ---
    ['header' => 'AVALIAÇÕES E EXAMES'],
    [
        'label' => 'Júris',
        'href' => url('/juries'),
        'roles' => ['vigilante', 'supervisor', 'membro', 'coordenador'],
        'icon' => 'groups',
        'color' => 'orange',
        'children' => [
            ['label' => 'Planeamento por Vaga', 'href' => url('/juries/planning-by-vacancy'), 'roles' => ['membro', 'coordenador']],
            ['label' => 'Planeamento Avançado', 'href' => url('/juries/planning'), 'roles' => ['membro', 'coordenador']],
            ['label' => 'Calendário', 'href' => url('/juries/calendar'), 'roles' => ['vigilante', 'supervisor', 'membro', 'coordenador']],
            ['label' => 'Lista de Júris', 'href' => url('/juries')],
        ]
    ],
    [
        'label' => 'Júris por Local',
        'href' => url('/locations'),
        'roles' => ['membro', 'coordenador'],
        'icon' => 'map',
        'color' => 'red',
        'children' => [
            ['label' => 'Visualização por Local', 'href' => url('/locations')],
            ['label' => 'Dashboard', 'href' => url('/locations/dashboard')],
            ['label' => 'Templates', 'href' => url('/locations/templates')],
            ['label' => 'Importar', 'href' => url('/locations/import')],
        ]
    ],

    // --- FINANCEIRO ---
    ['header' => 'FINANCEIRO'],
    [
        'label' => 'Pagamentos',
        'href' => url('/payments'),
        'roles' => ['membro', 'coordenador'],
        'icon' => 'payments',
        'color' => 'emerald',
        'children' => [
            ['label' => 'Mapa de Pagamentos', 'href' => url('/payments'), 'roles' => ['membro', 'coordenador']],
            ['label' => 'Taxas', 'href' => url('/payments/rates'), 'roles' => ['membro', 'coordenador']],
            ['label' => 'Meu Mapa', 'href' => url('/payments/my-map'), 'roles' => ['membro', 'coordenador']],
        ]
    ],
    // Meu Mapa de Pagamento (para vigilantes e supervisores - item separado)
    ['label' => 'Meu Mapa de Pagamento', 'href' => url('/payments/my-map'), 'roles' => ['vigilante', 'supervisor'], 'icon' => 'receipt_long', 'color' => 'emerald'],

    // --- RELATÓRIOS ---
    ['header' => 'RELATÓRIOS', 'roles' => ['membro', 'coordenador']],
    [
        'label' => 'Relatórios',
        'href' => url('/reports/consolidated'),
        'roles' => ['membro', 'coordenador'],
        'icon' => 'assessment',
        'color' => 'purple',
        'children' => [
            ['label' => 'Relatório Consolidado', 'href' => url('/reports/consolidated'), 'roles' => ['membro', 'coordenador']],
        ]
    ],

    // --- ADMINISTRAÇÃO ---
    ['header' => 'ADMINISTRAÇÃO', 'roles' => ['coordenador']],
    // Gestão de Utilizadores (apenas coordenador)
    ['label' => 'Gestão de Utilizadores', 'href' => url('/admin/users'), 'roles' => ['coordenador'], 'icon' => 'manage_accounts', 'color' => 'blue'],
    [
        'label' => 'Dados Mestres',
        'href' => url('/master-data/disciplines'),
        'roles' => ['coordenador'],
        'icon' => 'storage',
        'color' => 'indigo',
        'children' => [
            ['label' => 'Disciplinas', 'href' => url('/master-data/disciplines')],
            ['label' => 'Cadastro de Locais', 'href' => url('/master-data/locations')],
            ['label' => 'Salas', 'href' => url('/master-data/rooms')],
        ]
    ],
    // Administração - Feature Flags (apenas coordenador)
    ['label' => 'Config. Funcionalidades', 'href' => url('/admin/features'), 'roles' => ['coordenador'], 'icon' => 'tune', 'color' => 'purple'],

    // --- COMUNICAÇÃO ---
    ['header' => 'COMUNICAÇÃO', 'roles' => ['coordenador', 'membro', 'supervisor', 'vigilante']],
    [
        'label' => 'Notificações',
        'href' => url('/notifications'),
        'roles' => ['coordenador', 'membro', 'supervisor', 'vigilante'],
        'icon' => 'notifications',
        'color' => 'purple',
        'children' => [
            ['label' => 'Minhas Notificações', 'href' => url('/notifications'), 'roles' => ['coordenador', 'membro', 'supervisor', 'vigilante']],
            ['label' => 'Nova Notificação', 'href' => url('/notifications/create'), 'roles' => ['coordenador']],
            ['label' => 'Histórico', 'href' => url('/notifications/history'), 'roles' => ['coordenador']],
        ]
    ],

    // --- SISTEMA ---
    ['header' => 'SISTEMA', 'roles' => ['coordenador', 'membro', 'supervisor', 'vigilante']],
    ['label' => 'Perfil', 'href' => url('/profile'), 'roles' => ['vigilante', 'supervisor', 'membro', 'coordenador'], 'icon' => 'account_circle', 'color' => 'gray'],
];

// Mapeamento de cores
$colorClasses = [
    'blue' => 'text-blue-600 bg-blue-50',
    'green' => 'text-green-600 bg-green-50',
    'purple' => 'text-purple-600 bg-purple-50',
    'orange' => 'text-orange-600 bg-orange-50',
    'red' => 'text-red-600 bg-red-50',
    'indigo' => 'text-indigo-600 bg-indigo-50',
    'emerald' => 'text-emerald-600 bg-emerald-50',
    'gray' => 'text-gray-600 bg-gray-50',
];
?>

<!-- Injected CSS for Layout Stability -->
<style>
    @media (min-width: 768px) {
        aside#sidebar-desktop {
            display: flex !important;
        }

        .md\:hidden {
            display: none !important;
        }
    }

    aside#sidebar-desktop[data-collapsed="true"] {
        width: 4.5rem !important;
    }

    aside#sidebar-desktop[data-collapsed="false"] {
        width: 18rem !important;
    }

    aside#sidebar-desktop {
        transition: width 300ms ease-in-out;
    }

    /* Smooth hover effect for submenu items */
    .submenu-content a:hover {
        padding-left: 1.25rem;
    }

    /* Hide headers when collapsed */
    aside#sidebar-desktop[data-collapsed="true"] .sidebar-header {
        display: none;
    }

    /* Separator for collapsed functionality (optional, or rely on spacing) */
    aside#sidebar-desktop[data-collapsed="true"] .sidebar-separator {
        display: block;
    }
</style>

<!-- Sidebar Desktop -->
<aside id="sidebar-desktop"
    class="hidden md:flex flex-col w-72 bg-white border-r border-gray-200 shadow-md h-full transition-all duration-300 ease-in-out group/sidebar"
    data-collapsed="false">

    <!-- Header / Branding -->
    <div class="flex items-center justify-between px-3 h-24 border-b border-gray-100 flex-shrink-0">
        <!-- Logo Area -->
        <a href="<?= url('/dashboard') ?>"
            class="flex items-center gap-3 overflow-hidden transition-all duration-300 group/logo">
            <img src="<?= url('/assets/images/logo_unilicungo.png') ?>" alt="Logo"
                class="h-16 w-auto object-contain transition-transform duration-300">

            <div class="flex flex-col sidebar-text transition-opacity duration-300">
                <span class="font-bold text-gray-800 text-sm leading-tight tracking-tight">Portal</span>
                <span class="text-[10px] text-gray-500 font-medium uppercase tracking-wider">ComExames</span>
            </div>
        </a>

        <!-- Toggle Button -->
        <button id="sidebar-toggle-btn"
            class="p-1 text-gray-400 hover:text-primary-600 hover:bg-primary-50 rounded-full transition-all focus:outline-none flex-shrink-0"
            title="Fixar/Desafixar Menu">
            <svg id="sidebar-toggle-icon" class="w-5 h-5 transition-transform duration-300" fill="currentColor"
                viewBox="0 0 24 24">
                <!-- Icon injected by JS -->
            </svg>
        </button>
    </div>

    <!-- Menu Items -->
    <nav class="flex-1 px-3 py-4 space-y-2 overflow-y-auto overflow-x-hidden custom-scrollbar">
        <?php foreach ($items as $item): ?>

            <!-- Checks if it is a Header -->
            <?php if (isset($item['header'])): ?>
                <?php
                // Check if header should be shown for this role
                if (isset($item['roles']) && !in_array($role, $item['roles'], true)) {
                    continue;
                }
                ?>
                <div class="sidebar-header mt-2 mb-1 px-3 font-medium text-gray-400 uppercase tracking-widest transition-opacity duration-200"
                    style="font-size: 9px !important;">
                    <?= htmlspecialchars($item['header']) ?>
                </div>
                <!-- Divider for collapsed state (optional visual cue) -->
                <!-- <div class="sidebar-separator hidden h-px bg-gray-100 my-2 mx-2"></div> -->
                <?php continue; ?>
            <?php endif; ?>

            <?php if (isset($item['roles']) && !in_array($role, $item['roles'], true)) {
                continue;
            } ?>
            <?php $active = str_starts_with($current, $item['href']); ?>

            <?php
            $color = $item['color'] ?? 'gray';
            // Versão mais sutil do background ativo
            $activeClass = "bg-{$color}-50 text-{$color}-700";
            // Hover sutil
            $hoverClass = "hover:bg-gray-50 hover:text-gray-900";
            ?>

            <?php if (!empty($item['children'])): ?>
                <!-- Item com submenu -->
                <div class="submenu-wrapper relative group/item" data-has-submenu="true">

                    <button type="button"
                        class="submenu-toggle w-full flex items-center justify-between px-3 py-2.5 text-[13px] font-medium rounded-lg transition-all duration-200 border border-transparent <?= $active ? $activeClass : 'text-gray-600 ' . $hoverClass ?>"
                        data-submenu="<?= htmlspecialchars($item['label']) ?>">

                        <div class="flex items-center gap-2 min-w-0">
                            <span
                                class="material-symbols-rounded text-[14px] shrink-0 <?= $active ? '' : 'text-gray-400 group-hover/item:text-gray-600' ?>">
                                <?= $item['icon'] ?? 'radio_button_unchecked' ?>
                            </span>
                            <span class="sidebar-text truncate"><?= htmlspecialchars($item['label']) ?></span>
                        </div>

                        <svg class="submenu-icon sidebar-text w-4 h-4 shrink-0 text-gray-400 transition-transform duration-300"
                            fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Submenu Content -->
                    <div
                        class="submenu-content ml-4 pl-3 border-l text-[13px] border-gray-100 space-y-0.5 overflow-hidden transition-all duration-200 hidden max-h-0">
                        <?php foreach ($item['children'] as $child): ?>
                            <?php
                            if (isset($child['roles']) && !in_array($role, $child['roles'], true)) {
                                continue;
                            }
                            $childActive = $current === $child['href'];
                            ?>
                            <a href="<?= $child['href'] ?>"
                                class="flex items-center gap-2 px-3 py-1.5 rounded-md transition-all duration-200 <?= $childActive ? 'text-primary-700 font-medium bg-primary-50' : 'text-gray-500 hover:text-gray-800 hover:bg-gray-50' ?>">
                                <span><?= htmlspecialchars($child['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>

            <?php else: ?>
                <!-- Item simples -->
                <a href="<?= $item['href'] ?>"
                    class="group/item flex items-center gap-2 px-3 py-2.5 text-[13px] font-medium rounded-lg transition-all duration-200 border border-transparent <?= $active ? $activeClass : 'text-gray-600 ' . $hoverClass ?>">
                    <span
                        class="material-symbols-rounded text-[14px] shrink-0 <?= $active ? '' : 'text-gray-400 group-hover/item:text-gray-600' ?>">
                        <?= $item['icon'] ?? 'radio_button_unchecked' ?>
                    </span>
                    <span class="sidebar-text truncate"><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>

    <!-- Footer / User Profile -->
    <div class="p-3 border-t border-gray-100 sidebar-text">
        <div class="flex items-center gap-3 px-2 py-2 rounded-lg bg-gray-50 border border-gray-100">
            <div
                class="w-8 h-8 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold text-xs">
                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold text-gray-700 truncate"><?= htmlspecialchars($user['name'] ?? '') ?></p>
                <p class="text-[10px] text-gray-500 truncate"><?= $roleName ?></p>
            </div>
        </div>
    </div>
</aside>

<!-- Sidebar Mobile -->
<div @keydown.escape.window="mobileMenuOpen = false" class="md:hidden">
    <!-- Overlay -->
    <div x-show="mobileMenuOpen" x-transition:enter="transition-opacity ease-linear duration-300"
        x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0" @click="mobileMenuOpen = false"
        class="fixed inset-0 bg-gray-900 bg-opacity-75 z-40" style="display: none;">
    </div>

    <!-- Menu Slide Panel Mobile -->
    <aside x-show="mobileMenuOpen" x-transition:enter="transform transition ease-in-out duration-300"
        x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in-out duration-300" x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 w-80 bg-white shadow-2xl z-50 flex flex-col" style="display: none;">

        <!-- Mobile Header -->
        <div class="flex items-center justify-between px-4 py-4 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <img src="<?= url('/assets/images/logo_unilicungo.png') ?>" alt="UniLicungo" class="h-8 w-auto">
                <span class="font-bold text-gray-800">Portal</span>
            </div>
            <button @click="mobileMenuOpen = false" class="p-2 text-gray-500 hover:bg-gray-100 rounded-full">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <?php foreach ($items as $item): ?>
                <?php if (isset($item['header'])) {
                    // Check if header should be shown for this role
                    if (isset($item['roles']) && !in_array($role, $item['roles'], true)) {
                        continue;
                    }
                    // Renderizar header no mobile
                    echo '<div class="px-4 mt-4 mb-2 text-xs font-bold text-gray-400 uppercase tracking-wider">' . htmlspecialchars($item['header']) . '</div>';
                    continue;
                } ?>
                <?php if (isset($item['roles']) && !in_array($role, $item['roles'], true)) {
                    continue;
                } ?>
                <?php $active = str_starts_with($current, $item['href']); ?>
                <?php $color = $item['color'] ?? 'gray'; ?>

                <?php if (!empty($item['children'])): ?>
                    <div class="space-y-1">
                        <button type="button"
                            class="submenu-toggle-mobile w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-colors <?= $active ? 'bg-' . $color . '-50 text-' . $color . '-700' : 'text-gray-700 hover:bg-gray-50' ?>">
                            <span class="flex items-center gap-3">
                                <span
                                    class="material-symbols-rounded text-xl <?= $active ? '' : 'text-gray-400' ?>"><?= $item['icon'] ?? 'circle' ?></span>
                                <span><?= htmlspecialchars($item['label']) ?></span>
                            </span>
                            <svg class="submenu-icon w-5 h-5 transition-transform duration-300" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        <div
                            class="submenu-content-mobile ml-4 pl-4 border-l border-gray-100 space-y-1 hidden max-h-0 overflow-hidden">
                            <?php foreach ($item['children'] as $child): ?>
                                <?php if (isset($child['roles']) && !in_array($role, $child['roles'], true)) {
                                    continue;
                                } ?>
                                <a href="<?= $child['href'] ?>"
                                    class="block px-4 py-2 text-sm text-gray-600 rounded-lg hover:bg-gray-50 hover:text-gray-900">
                                    <?= htmlspecialchars($child['label']) ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="<?= $item['href'] ?>"
                        class="flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-colors <?= $active ? 'bg-' . $color . '-50 text-' . $color . '-700' : 'text-gray-700 hover:bg-gray-50' ?>">
                        <span
                            class="material-symbols-rounded text-xl <?= $active ? '' : 'text-gray-400' ?>"><?= $item['icon'] ?? 'circle' ?></span>
                        <span><?= htmlspecialchars($item['label']) ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
    </aside>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const sidebar = document.getElementById('sidebar-desktop');
        const toggleBtn = document.getElementById('sidebar-toggle-btn');
        const toggleIcon = document.getElementById('sidebar-toggle-icon');
        const navItems = sidebar.querySelectorAll('nav > a, nav > div > button.submenu-toggle');
        const submenuWrappers = sidebar.querySelectorAll('.submenu-wrapper');

        // Paths for Radio Icons
        const iconChecked = '<path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zm0-5C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/>';
        const iconUnchecked = '<path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8z"/>';

        // Load state
        const isCollapsedPreference = localStorage.getItem('sidebarCollapsed') === 'true';
        if (isCollapsedPreference) {
            applyCollapseState(true);
        } else {
            toggleIcon.innerHTML = iconChecked;
        }

        function applyCollapseState(collapsed) {
            if (collapsed) {
                sidebar.setAttribute('data-collapsed', 'true');
                toggleIcon.innerHTML = iconUnchecked;
                toggleBtn.setAttribute('title', 'Expandir e Fixar Menu');
                toggleBtn.classList.add('hidden'); // Hide toggle button

                document.querySelectorAll('.sidebar-text').forEach(el => el.classList.add('hidden'));

                navItems.forEach(item => {
                    item.classList.add('justify-center');
                    item.classList.remove('justify-between', 'px-3');
                    item.classList.add('px-0');

                    if (!item.getAttribute('title')) {
                        const label = item.getAttribute('data-submenu') || item.querySelector('.sidebar-text')?.textContent;
                        if (label) item.setAttribute('title', label.trim());
                    }
                });

                closeAllSubmenus();

            } else {
                sidebar.setAttribute('data-collapsed', 'false');
                toggleIcon.innerHTML = iconChecked;
                toggleBtn.setAttribute('title', 'Desafixar e Colapsar Menu');
                toggleBtn.classList.remove('hidden'); // Show toggle button

                document.querySelectorAll('.sidebar-text').forEach(el => el.classList.remove('hidden'));

                navItems.forEach(item => {
                    item.classList.remove('justify-center', 'px-0');
                    item.classList.add('px-3');
                    if (item.classList.contains('submenu-toggle')) {
                        item.classList.add('justify-between');
                    }
                    item.removeAttribute('title');
                });
            }
        }

        // --- Toggle Button Click ---
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const currentPref = localStorage.getItem('sidebarCollapsed') === 'true';
                const newPref = !currentPref;
                localStorage.setItem('sidebarCollapsed', newPref);
                applyCollapseState(newPref);
            });
        }

        // --- Hover Interaction with Debounce ---
        let hoverTimeout = null;
        let isHoverExpanded = false;

        sidebar.addEventListener('mouseenter', function () {
            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                // Add delay before expanding to prevent flickering on quick passes
                hoverTimeout = setTimeout(() => {
                    isHoverExpanded = true;
                    applyCollapseState(false);
                }, 150); // 150ms delay
            }
        });

        sidebar.addEventListener('mouseleave', function () {
            // Clear any pending expansion
            if (hoverTimeout) {
                clearTimeout(hoverTimeout);
                hoverTimeout = null;
            }

            if (localStorage.getItem('sidebarCollapsed') === 'true') {
                isHoverExpanded = false;
                applyCollapseState(true);
            }
        });

        // --- Accordion Logic (Click Only) ---
        function toggleSubmenu(toggle, forceOpen = null) {
            const submenuContent = toggle.nextElementSibling;
            const submenuIcon = toggle.querySelector('.submenu-icon');
            const isClosed = submenuContent.classList.contains('hidden');
            const shouldOpen = forceOpen !== null ? forceOpen : isClosed;

            if (shouldOpen) {
                // Close others
                document.querySelectorAll('.submenu-toggle').forEach(otherToggle => {
                    if (otherToggle !== toggle) {
                        const otherContent = otherToggle.nextElementSibling;
                        const otherIcon = otherToggle.querySelector('.submenu-icon');
                        if (!otherContent.classList.contains('hidden')) {
                            otherContent.style.maxHeight = '0';
                            setTimeout(() => otherContent.classList.add('hidden', 'max-h-0'), 200);
                            if (otherIcon) otherIcon.classList.remove('rotate-180');
                        }
                    }
                });

                // Open current
                submenuContent.classList.remove('hidden', 'max-h-0');
                requestAnimationFrame(() => {
                    submenuContent.style.maxHeight = submenuContent.scrollHeight + 'px';
                });
                if (submenuIcon) submenuIcon.classList.add('rotate-180');
            } else {
                // Close current
                submenuContent.style.maxHeight = '0';
                setTimeout(() => submenuContent.classList.add('hidden', 'max-h-0'), 200);
                if (submenuIcon) submenuIcon.classList.remove('rotate-180');
            }
        }

        function closeAllSubmenus() {
            document.querySelectorAll('.submenu-content:not(.hidden)').forEach(content => {
                content.style.maxHeight = '0';
                content.classList.add('hidden', 'max-h-0');
                const toggle = content.previousElementSibling;
                const icon = toggle.querySelector('.submenu-icon');
                if (icon) icon.classList.remove('rotate-180');
            });
        }

        // Desktop Click
        document.querySelectorAll('.submenu-toggle').forEach(toggle => {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                toggleSubmenu(this);
            });
        });

        // Mobile Click
        document.querySelectorAll('.submenu-toggle-mobile').forEach(toggle => {
            toggle.addEventListener('click', function (e) {
                e.preventDefault();
                toggleSubmenu(this);
            });
        });
    });
</script>