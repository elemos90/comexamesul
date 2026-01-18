<?php
use App\Utils\Auth;

// Authorization check
if (!Auth::hasAnyRole(['coordenador'])) {
    redirect('/dashboard?error=unauthorized');
    exit;
}

$title = 'Criar Utilizador';
$breadcrumbs = [
    ['label' => 'Administração'],
    ['label' => 'Utilizadores', 'url' => '/admin/users'],
    ['label' => 'Criar']
];
?>

<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="max-w-3xl">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-6">Novo Utilizador</h2>

            <?php if (isset($_GET['error'])): ?>
                <div class="mb-4 bg-red-50 border-l-4 border-red-500 p-4 rounded">
                    <p class="text-sm font-medium text-red-800">
                        <?php
                        $errors = [
                            'missing_fields' => 'Por favor preencha todos os campos obrigatórios',
                            'username_exists' => 'Este nome de utilizador já está em uso',
                            'email_exists' => 'Este email já está registado no sistema'
                        ];
                        echo $errors[$_GET['error']] ?? 'Erro ao criar utilizador';
                        ?>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Info Box -->
            <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-blue-900">Sistema sem dependência de email</h3>
                        <ul class="text-xs text-blue-700 mt-1 list-disc list-inside space-y-1">
                            <li>O utilizador faz login com <strong>username</strong></li>
                            <li>Uma senha temporária será gerada automaticamente</li>
                            <li>O utilizador será forçado a alterar a senha no primeiro login</li>
                            <li>O utilizador receberá uma <strong>notificação interna</strong> com as credenciais</li>
                        </ul>
                    </div>
                </div>
            </div>

            <form method="POST" action="<?= url('/admin/users') ?>" class="space-y-6">
                <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                        Nome Completo <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="name" name="name" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="Ex: João Silva">
                </div>

                <!-- Username -->
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 mb-1">
                        Nome de Utilizador <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="username" name="username" required pattern="[a-zA-Z0-9._]+"
                        title="Apenas letras, números, pontos e underscores"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="ex: joao.silva">
                    <p class="mt-1 text-xs text-gray-500">O utilizador usará este nome para fazer login</p>
                </div>

                <!-- Email (Optional) -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                        Email <span class="text-gray-400">(opcional)</span>
                    </label>
                    <input type="email" id="email" name="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="exemplo@licungo.ac.mz">
                    <p class="mt-1 text-xs text-gray-500">Pode ser adicionado pelo utilizador mais tarde</p>
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">
                        Telefone
                    </label>
                    <input type="tel" id="phone" name="phone"
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                        placeholder="+258 84 123 4567">
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                        Papel Inicial <span class="text-red-500">*</span>
                    </label>
                    <select id="role" name="role" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="">Selecione um papel...</option>
                        <?php foreach ($roles as $role): ?>
                            <option value="<?= $role ?>">
                                <?= \App\Services\UserRoleService::getRoleDisplayName($role) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        <strong>Nota:</strong> Supervisor inclui automaticamente Vigilante. Coordenador inclui Membro da
                        Comissão.
                    </p>
                </div>

                <!-- Active Status -->
                <div class="flex items-center">
                    <input type="checkbox" id="is_active" name="is_active" checked
                        class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                    <label for="is_active" class="ml-2 block text-sm text-gray-900">
                        Conta ativa (pode fazer login imediatamente)
                    </label>
                </div>

                <!-- Generated Password Info -->
                <div class="p-4 bg-amber-50 border border-amber-200 rounded-lg">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                        </svg>
                        <div class="flex-1">
                            <h3 class="text-sm font-semibold text-amber-900">Senha Temporária</h3>
                            <p class="text-xs text-amber-700 mt-1">
                                Uma senha temporária de 16 caracteres será gerada automaticamente.
                                O utilizador será <strong>obrigado a alterá-la</strong> no primeiro login.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <a href="<?= url('/admin/users') ?>"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </a>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4">
                            </path>
                        </svg>
                        Criar Utilizador
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>