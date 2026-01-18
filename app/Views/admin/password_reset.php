<?php
// Layout: main
?>

<div class="max-w-2xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">
            <?= $title ?>
        </h1>
        <p class="text-gray-600 mt-1">Gerar nova senha temporária para o utilizador.</p>
    </div>

    <!-- User Info Card -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Dados do Pedido</h2>
        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-xs text-gray-500 uppercase">Utilizador</label>
                <p class="text-gray-900 font-medium">
                    <?= htmlspecialchars($resetRequest['name']) ?>
                </p>
            </div>
            <div>
                <label class="block text-xs text-gray-500 uppercase">Username</label>
                <p class="text-gray-900 font-medium">@
                    <?= htmlspecialchars($resetRequest['username']) ?>
                </p>
            </div>
            <div>
                <label class="block text-xs text-gray-500 uppercase">Email</label>
                <p class="text-gray-900 font-medium">
                    <?= htmlspecialchars($resetRequest['email'] ?? 'N/A') ?>
                </p>
            </div>
            <div>
                <label class="block text-xs text-gray-500 uppercase">Solicitado em</label>
                <p class="text-gray-900 font-medium">
                    <?= date('d/m/Y H:i', strtotime($resetRequest['requested_at'])) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- Reset Form -->
    <form action="<?= url('/admin/password-reset/' . $resetRequest['id']) ?>" method="POST"
        class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

        <!-- Password Type Selection -->
        <div class="mb-6">
            <label class="block text-sm font-medium text-gray-700 mb-2">Método de Geração</label>
            <div class="flex gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="password_type" value="auto" checked
                        class="text-primary-600 focus:ring-primary-500"
                        onclick="document.getElementById('manual-input').classList.add('hidden')">
                    <span class="text-gray-900">Gerar Automaticamente</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="radio" name="password_type" value="manual"
                        class="text-primary-600 focus:ring-primary-500"
                        onclick="document.getElementById('manual-input').classList.remove('hidden')">
                    <span class="text-gray-900">Definir Manualmente</span>
                </label>
            </div>
        </div>

        <!-- Manual Input (Hidden by default) -->
        <div id="manual-input" class="mb-6 hidden">
            <label for="manual_password" class="block text-sm font-medium text-gray-700 mb-1">Nova Senha</label>
            <input type="text" name="manual_password" id="manual_password"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                placeholder="Mínimo 6 caracteres">
            <p class="text-xs text-gray-500 mt-1">O utilizador será obrigado a alterar esta senha no próximo login.</p>
        </div>

        <!-- Warning Alert -->
        <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-yellow-700">
                        Ao confirmar, a senha atual será invalidada imediatamente. O utilizador receberá uma notificação
                        com a nova senha provisória.
                    </p>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
            <a href="<?= url('/notifications') ?>"
                class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-900">
                Cancelar
            </a>
            <button type="submit"
                class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Confirmar Reset
            </button>
        </div>
    </form>
</div>