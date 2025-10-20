<?php
use App\Utils\Auth;

$user = Auth::user();
$current = strtok($_SERVER['REQUEST_URI'] ?? '/', '?');
$role = $user['role'] ?? '';

$items = [
    ['label' => 'Dashboard', 'href' => '/dashboard', 'roles' => ['vigilante','membro','coordenador'], 'icon' => 'dashboard', 'color' => 'blue'],
    ['label' => 'Vagas', 'href' => '/vacancies', 'roles' => ['membro','coordenador'], 'icon' => 'work', 'color' => 'green'],
    ['label' => 'Candidaturas', 'href' => '/availability', 'roles' => ['vigilante'], 'icon' => 'assignment_turned_in', 'color' => 'purple'],
    ['label' => 'Candidaturas', 'href' => '/applications', 'roles' => ['membro','coordenador'], 'icon' => 'fact_check', 'color' => 'purple', 'children' => [
        ['label' => 'Dashboard', 'href' => '/applications/dashboard'],
        ['label' => 'Lista de Vigilantes', 'href' => '/applications'],
    ]],
    ['label' => 'Júris', 'href' => '/juries', 'roles' => ['vigilante','membro','coordenador'], 'icon' => 'gavel', 'color' => 'orange', 'children' => [
        ['label' => 'Planeamento por Vaga', 'href' => '/juries/planning-by-vacancy', 'roles' => ['membro','coordenador']],
        ['label' => 'Planeamento Avançado', 'href' => '/juries/planning', 'roles' => ['membro','coordenador']],
        ['label' => 'Lista de Júris', 'href' => '/juries'],
    ]],
    ['label' => 'Júris por Local', 'href' => '/locations', 'roles' => ['membro','coordenador'], 'icon' => 'map', 'color' => 'red', 'children' => [
        ['label' => 'Visualização por Local', 'href' => '/locations'],
        ['label' => 'Dashboard', 'href' => '/locations/dashboard'],
        ['label' => 'Templates', 'href' => '/locations/templates'],
        ['label' => 'Importar', 'href' => '/locations/import'],
    ]],
    ['label' => 'Dados Mestres', 'href' => '/master-data/disciplines', 'roles' => ['coordenador'], 'icon' => 'storage', 'color' => 'indigo', 'children' => [
        ['label' => 'Disciplinas', 'href' => '/master-data/disciplines'],
        ['label' => 'Cadastro de Locais', 'href' => '/master-data/locations'],
        ['label' => 'Salas', 'href' => '/master-data/rooms'],
    ]],
    ['label' => 'Perfil', 'href' => '/profile', 'roles' => ['vigilante','membro','coordenador'], 'icon' => 'account_circle', 'color' => 'gray'],
];

// Mapeamento de cores
$colorClasses = [
    'blue' => 'text-blue-600 bg-blue-50',
    'green' => 'text-green-600 bg-green-50',
    'purple' => 'text-purple-600 bg-purple-50',
    'orange' => 'text-orange-600 bg-orange-50',
    'red' => 'text-red-600 bg-red-50',
    'indigo' => 'text-indigo-600 bg-indigo-50',
    'gray' => 'text-gray-600 bg-gray-50',
];
?>
<!-- Sidebar Desktop -->
<aside class="hidden md:block w-72 bg-gradient-to-b from-slate-50 to-white border-r border-gray-200 h-full shadow-sm">
    <!-- Menu Items -->
    <nav class="p-4 pr-2 space-y-1 overflow-y-auto h-full">
        <?php foreach ($items as $item): ?>
            <?php if (!in_array($role, $item['roles'], true)) { continue; } ?>
            <?php $active = str_starts_with($current, $item['href']); ?>
            
            <?php 
            $color = $item['color'] ?? 'gray';
            $colorClass = $colorClasses[$color] ?? $colorClasses['gray'];
            ?>
            
            <?php if (!empty($item['children'])): ?>
                <!-- Item com submenu (accordion) -->
                <div class="space-y-1">
                    <button type="button" class="submenu-toggle group w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 <?= $active ? $colorClass . ' shadow-sm' : 'text-gray-700 hover:bg-gray-50' ?>" data-submenu="<?= htmlspecialchars($item['label']) ?>">
                        <span class="flex items-center gap-3">
                            <span class="material-symbols-rounded text-xl <?= $active ? '' : 'text-gray-400 group-hover:text-gray-600' ?>"><?= $item['icon'] ?? 'radio_button_unchecked' ?></span>
                            <span><?= htmlspecialchars($item['label']) ?></span>
                        </span>
                        <svg class="submenu-icon w-5 h-5 transition-transform duration-300 <?= $active ? 'transform rotate-180' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div class="submenu-content ml-8 space-y-0.5 overflow-hidden transition-all duration-300 <?= $active ? 'mt-1' : 'hidden max-h-0' ?>">
                        <?php foreach ($item['children'] as $child): ?>
                            <?php 
                            // Verificar roles do child se definidas
                            if (isset($child['roles']) && !in_array($role, $child['roles'], true)) {
                                continue;
                            }
                            $childActive = $current === $child['href']; 
                            ?>
                            <a href="<?= $child['href'] ?>" class="group flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 <?= $childActive ? 'bg-white text-gray-900 font-medium shadow-sm' : 'text-gray-600 hover:bg-white hover:text-gray-900' ?>">
                                <div class="w-1.5 h-1.5 rounded-full transition-colors <?= $childActive ? 'bg-' . $color . '-600' : 'bg-gray-300 group-hover:bg-gray-400' ?>"></div>
                                <span><?= htmlspecialchars($child['label']) ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Item simples -->
                <a href="<?= $item['href'] ?>" class="group flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 <?= $active ? $colorClass . ' shadow-sm' : 'text-gray-700 hover:bg-gray-50' ?>">
                    <span class="material-symbols-rounded text-xl <?= $active ? '' : 'text-gray-400 group-hover:text-gray-600' ?>"><?= $item['icon'] ?? 'radio_button_unchecked' ?></span>
                    <span><?= htmlspecialchars($item['label']) ?></span>
                </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
</aside>

<!-- Sidebar Mobile (Slide-out) -->
<div @keydown.escape.window="mobileMenuOpen = false" class="md:hidden">
    <!-- Overlay -->
    <div x-show="mobileMenuOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="mobileMenuOpen = false"
         class="fixed inset-0 bg-gray-900 bg-opacity-75 z-40"
         style="display: none;">
    </div>
    
    <!-- Menu Slide Panel -->
    <aside x-show="mobileMenuOpen"
           x-transition:enter="transform transition ease-in-out duration-300"
           x-transition:enter-start="-translate-x-full"
           x-transition:enter-end="translate-x-0"
           x-transition:leave="transform transition ease-in-out duration-300"
           x-transition:leave-start="translate-x-0"
           x-transition:leave-end="-translate-x-full"
           class="fixed inset-y-0 left-0 w-80 bg-gradient-to-b from-slate-50 to-white shadow-2xl z-50 flex flex-col"
           style="display: none;">
        
        <!-- Header Mobile -->
        <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200 bg-white">
            <div class="flex items-center gap-2">
                <img src="/assets/images/logo_unilicungo.png" alt="UniLicungo" class="h-8 w-auto object-contain">
                <span class="text-base font-semibold text-primary-600"><?= htmlspecialchars(env('APP_NAME', 'Portal')) ?></span>
            </div>
            <button @click="mobileMenuOpen = false" class="p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <!-- Menu Items Mobile -->
        <nav class="flex-1 p-4 space-y-1 overflow-y-auto">
            <?php foreach ($items as $item): ?>
                <?php if (!in_array($role, $item['roles'], true)) { continue; } ?>
                <?php $active = str_starts_with($current, $item['href']); ?>
                
                <?php 
                $color = $item['color'] ?? 'gray';
                $colorClass = $colorClasses[$color] ?? $colorClasses['gray'];
                ?>
                
                <?php if (!empty($item['children'])): ?>
                    <!-- Item com submenu (accordion) -->
                    <div class="space-y-1">
                        <button type="button" class="submenu-toggle-mobile group w-full flex items-center justify-between px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 <?= $active ? $colorClass . ' shadow-sm' : 'text-gray-700 hover:bg-gray-50' ?>" data-submenu="<?= htmlspecialchars($item['label']) ?>">
                            <span class="flex items-center gap-3">
                                <span class="material-symbols-rounded text-xl <?= $active ? '' : 'text-gray-400 group-hover:text-gray-600' ?>"><?= $item['icon'] ?? 'radio_button_unchecked' ?></span>
                                <span><?= htmlspecialchars($item['label']) ?></span>
                            </span>
                            <svg class="submenu-icon w-5 h-5 transition-transform duration-300 <?= $active ? 'transform rotate-180' : '' ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div class="submenu-content-mobile ml-8 space-y-0.5 overflow-hidden transition-all duration-300 <?= $active ? 'mt-1' : 'hidden max-h-0' ?>">
                            <?php foreach ($item['children'] as $child): ?>
                                <?php 
                                // Verificar roles do child se definidas
                                if (isset($child['roles']) && !in_array($role, $child['roles'], true)) {
                                    continue;
                                }
                                $childActive = $current === $child['href']; 
                                ?>
                                <a href="<?= $child['href'] ?>" @click="mobileMenuOpen = false" class="group flex items-center gap-2 px-4 py-2 text-sm rounded-lg transition-all duration-200 <?= $childActive ? 'bg-white text-gray-900 font-medium shadow-sm' : 'text-gray-600 hover:bg-white hover:text-gray-900' ?>">
                                    <div class="w-1.5 h-1.5 rounded-full transition-colors <?= $childActive ? 'bg-' . $color . '-600' : 'bg-gray-300 group-hover:bg-gray-400' ?>"></div>
                                    <span><?= htmlspecialchars($child['label']) ?></span>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Item simples -->
                    <a href="<?= $item['href'] ?>" @click="mobileMenuOpen = false" class="group flex items-center gap-3 px-4 py-3 text-sm font-medium rounded-xl transition-all duration-200 <?= $active ? $colorClass . ' shadow-sm' : 'text-gray-700 hover:bg-gray-50' ?>">
                        <span class="material-symbols-rounded text-xl <?= $active ? '' : 'text-gray-400 group-hover:text-gray-600' ?>"><?= $item['icon'] ?? 'radio_button_unchecked' ?></span>
                        <span><?= htmlspecialchars($item['label']) ?></span>
                    </a>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        
        <!-- Footer Mobile (Info do usuário e Logout) -->
        <div class="border-t border-gray-200 bg-white">
            <div class="p-4">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                        <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-gray-900 text-sm truncate">
                            <?= htmlspecialchars($user['name'] ?? '') ?>
                        </div>
                        <div class="text-xs text-gray-500 truncate">
                            <?= htmlspecialchars($roleName) ?>
                        </div>
                    </div>
                </div>
                <!-- Botão Logout -->
                <form method="POST" action="/logout">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-red-600 hover:bg-red-700 rounded-lg transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Terminar Sessão
                    </button>
                </form>
            </div>
        </div>
    </aside>
</div>

<script>
// Script para toggle de submenu (accordion) - Desktop e Mobile
document.addEventListener('DOMContentLoaded', function() {
    // Desktop toggles
    const submenuToggles = document.querySelectorAll('.submenu-toggle');
    submenuToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const submenuContent = this.nextElementSibling;
            const submenuIcon = this.querySelector('.submenu-icon');
            
            if (submenuContent.classList.contains('hidden')) {
                submenuContent.classList.remove('hidden', 'max-h-0');
                submenuContent.style.maxHeight = submenuContent.scrollHeight + 'px';
                submenuIcon.classList.add('rotate-180');
            } else {
                submenuContent.style.maxHeight = '0';
                setTimeout(() => {
                    submenuContent.classList.add('hidden', 'max-h-0');
                }, 200);
                submenuIcon.classList.remove('rotate-180');
            }
        });
    });
    
    // Mobile toggles
    const mobileToggles = document.querySelectorAll('.submenu-toggle-mobile');
    mobileToggles.forEach(toggle => {
        toggle.addEventListener('click', function(e) {
            e.preventDefault();
            const submenuContent = this.nextElementSibling;
            const submenuIcon = this.querySelector('.submenu-icon');
            
            if (submenuContent.classList.contains('hidden')) {
                submenuContent.classList.remove('hidden', 'max-h-0');
                submenuContent.style.maxHeight = submenuContent.scrollHeight + 'px';
                submenuIcon.classList.add('rotate-180');
            } else {
                submenuContent.style.maxHeight = '0';
                setTimeout(() => {
                    submenuContent.classList.add('hidden', 'max-h-0');
                }, 200);
                submenuIcon.classList.remove('rotate-180');
            }
        });
    });
    
});
</script>
