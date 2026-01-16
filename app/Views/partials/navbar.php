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
<nav class="bg-white">
    <div class="max-w-full mx-auto px-6">
        <div class="flex justify-between h-14 items-center">
            <!-- Botão Hamburguer (Mobile) -->
            <button @click="mobileMenuOpen = true"
                class="md:hidden p-2 rounded-lg text-gray-500 hover:text-gray-700 hover:bg-gray-100 focus:outline-none transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                </svg>
            </button>

            <!-- Espaçador (para alinhar ícones à direita) -->
            <div class="flex-1"></div>

            <!-- Ícones Direita (Estilo SIIP) -->
            <div class="flex items-center gap-1">
                <!-- Dark Mode Toggle (Placeholder) -->
                <button type="button"
                    class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors"
                    title="Modo Escuro (Em breve)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Notificações -->
                <button type="button"
                    class="p-2 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors relative"
                    title="Notificações">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <!-- Badge de notificação (se houver) -->
                    <!-- <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full"></span> -->
                </button>

                <!-- User Dropdown -->
                <div class="relative ml-2" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false"
                        class="flex items-center gap-2 px-2 py-1.5 hover:bg-gray-50 rounded-lg transition-colors focus:outline-none">
                        <!-- Nome e Role -->
                        <div class="text-right hidden sm:block">
                            <div class="text-sm font-semibold text-gray-800 leading-tight">
                                <?= htmlspecialchars($user['name'] ?? '') ?>
                            </div>
                            <div class="text-[11px] text-gray-500">
                                <?= htmlspecialchars($roleName) ?>
                            </div>
                        </div>
                        <!-- Avatar -->
                        <div
                            class="w-8 h-8 bg-blue-600 rounded-full flex items-center justify-center text-white font-semibold text-sm">
                            <?= strtoupper(substr($user['name'] ?? 'U', 0, 1)) ?>
                        </div>
                        <!-- Seta -->
                        <svg class="w-4 h-4 text-gray-400 transition-transform hidden sm:block"
                            :class="{'rotate-180': open}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="open" x-transition:enter="transition ease-out duration-100"
                        x-transition:enter-start="transform opacity-0 scale-95"
                        x-transition:enter-end="transform opacity-100 scale-100"
                        x-transition:leave="transition ease-in duration-75"
                        x-transition:leave-start="transform opacity-100 scale-100"
                        x-transition:leave-end="transform opacity-0 scale-95"
                        class="absolute right-0 mt-2 w-56 bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden z-50"
                        style="display: none;">

                        <!-- User Info -->
                        <div class="px-4 py-3 border-b border-gray-100">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold text-base">
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
                        </div>

                        <!-- Role Badge -->
                        <div class="px-4 py-2 border-b border-gray-100">
                            <div class="flex items-center gap-2 text-xs text-gray-600">
                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                </svg>
                                <span class="font-medium"><?= htmlspecialchars($roleName) ?></span>
                            </div>
                        </div>

                        <!-- Logout Button -->
                        <div class="p-2">
                            <form method="POST" action="<?= url('/logout') ?>">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                <button type="submit"
                                    class="w-full flex items-center gap-2 px-3 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded-md transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                    </svg>
                                    Terminar Sessão
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</nav>