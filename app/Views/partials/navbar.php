<?php
use App\Utils\Auth;

$user = Auth::user();
$roleNames = [
    'vigilante' => 'Vigilante',
    'membro' => 'Membro da Comissão',
    'coordenador' => 'Coordenador'
];
$roleName = $roleNames[$user['role']] ?? ucfirst($user['role']);
?>
<nav class="bg-white border-b border-gray-200 shadow-sm">
    <div class="max-w-full mx-auto px-4 sm:px-6">
        <div class="flex justify-between h-16 items-center">
            <div class="flex items-center space-x-3">
                <!-- Botão Hamburguer (Mobile) -->
                <button @click="mobileMenuOpen = true" class="md:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                
                <a href="/dashboard" class="flex items-center gap-2">
                    <img src="/assets/images/logo_unilicungo.png" alt="UniLicungo" class="h-10 w-auto object-contain">
                    <span class="text-lg font-semibold text-primary-600 hidden md:inline"><?= htmlspecialchars(env('APP_NAME', 'Portal')) ?></span>
                </a>
            </div>
            
            <!-- User Dropdown -->
            <div class="relative" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="flex items-center gap-3 px-2 py-2 hover:bg-gray-50 rounded-lg transition-colors focus:outline-none">
                    <!-- Avatar -->
                    <div class="w-9 h-9 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                        <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                    </div>
                    <!-- Nome e Role -->
                    <div class="text-left hidden sm:block">
                        <div class="text-sm font-semibold text-gray-900 leading-tight">
                            <?= htmlspecialchars($user['name'] ?? '') ?>
                        </div>
                        <div class="text-xs text-gray-500">
                            <?= htmlspecialchars($roleName) ?>
                        </div>
                    </div>
                    <!-- Seta -->
                    <svg class="w-4 h-4 text-gray-400 transition-transform ml-1" :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                
                <!-- Dropdown Menu -->
                <div x-show="open" 
                     x-transition:enter="transition ease-out duration-100"
                     x-transition:enter-start="transform opacity-0 scale-95"
                     x-transition:enter-end="transform opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-75"
                     x-transition:leave-start="transform opacity-100 scale-100"
                     x-transition:leave-end="transform opacity-0 scale-95"
                     class="absolute right-0 mt-2 w-64 bg-white rounded-lg shadow-xl border border-gray-200 overflow-hidden z-50"
                     style="display: none;">
                    
                    <!-- User Info -->
                    <div class="px-4 py-4 bg-white">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-base">
                                <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-900 text-sm truncate">
                                    <?= htmlspecialchars($user['name'] ?? '') ?>
                                </div>
                                <div class="text-xs text-gray-500 truncate">
                                    <?= htmlspecialchars($user['email'] ?? '') ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Role Badge -->
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gray-50 rounded-md">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span class="text-xs font-medium text-gray-700"><?= htmlspecialchars($roleName) ?></span>
                        </div>
                    </div>
                    
                    <!-- Divider -->
                    <div class="border-t border-gray-200"></div>
                    
                    <!-- Logout Button -->
                    <div class="p-2">
                        <form method="POST" action="/logout">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Terminar Sessão
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>
