<?php
use App\Utils\Auth;

$title = 'Gestão de Utilizadores';
$breadcrumbs = [
    ['label' => 'Administração'],
    ['label' => 'Utilizadores']
];

// Use Auth::hasAnyRole() instead of checking user array
if (!Auth::hasAnyRole(['coordenador'])) {
    redirect('/dashboard?error=unauthorized');
    exit;
}
?>

<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Gestão de Utilizadores</h1>
            <p class="text-sm text-gray-600 mt-1">Administre utilizadores, papéis e permissões</p>
        </div>
        <a href="<?= url('/admin/users/create') ?>" class="btn btn-primary flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Adicionar Utilizador
        </a>
    </div>

    <!-- Success/Error Messages -->
    <!-- Success/Error Messages -->
    <?php if (\App\Utils\Flash::has('success')): ?>
        <?php foreach (\App\Utils\Flash::get('success') as $msg): ?>
            <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                    <div class="text-sm font-medium text-green-800">
                        <?= $msg ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <?php if (isset($_GET['success']) && !\App\Utils\Flash::has('success')): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded mb-6">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd" />
                </svg>
                <p class="text-sm font-medium text-green-800">
                    <?php
                    $messages = [
                        'user_created' => 'Utilizador criado com sucesso! Email de boas-vindas enviado.',
                        'user_updated' => 'Utilizador atualizado com sucesso!'
                    ];
                    echo $messages[$_GET['success']] ?? 'Operação realizada com sucesso!';
                    ?>
                </p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Users Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Email
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefone
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Papéis
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado
                    </th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php foreach ($users as $usr): ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0 h-10 w-10">
                                    <div class="h-10 w-10 rounded-full bg-primary-100 flex items-center justify-center">
                                        <span class="text-primary-600 font-semibold text-sm">
                                            <?= strtoupper(substr($usr['name'], 0, 2)) ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($usr['name']) ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">
                                <?= htmlspecialchars($usr['email']) ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-500">
                                <?= htmlspecialchars($usr['phone'] ?? '—') ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex gap-2 flex-wrap">
                                <?php foreach ($usr['roles_array'] as $role): ?>
                                    <?php
                                    $displayName = \App\Services\UserRoleService::getRoleDisplayName($role);
                                    $colorClass = \App\Services\UserRoleService::getRoleBadgeColor($role);
                                    $bgColor = "bg-{$colorClass}-100";
                                    $textColor = "text-{$colorClass}-800";
                                    ?>
                                    <span
                                        class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $bgColor ?> <?= $textColor ?>">
                                        <?= $displayName ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <?php if ($usr['is_active']): ?>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    Ativo
                                </span>
                            <?php else: ?>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                    Inativo
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <div class="flex items-center justify-end gap-2">
                                <a href="<?= url('/admin/users/' . $usr['id'] . '/edit') ?>"
                                    class="text-indigo-600 hover:text-indigo-900 font-medium" title="Editar">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                    </svg>
                                </a>
                                <button
                                    onclick="toggleUserStatus(<?= $usr['id'] ?>, <?= $usr['is_active'] ? 'false' : 'true' ?>)"
                                    class="<?= $usr['is_active'] ? 'text-red-600 hover:text-red-900' : 'text-green-600 hover:text-green-900' ?> font-medium"
                                    title="<?= $usr['is_active'] ? 'Desativar' : 'Ativar' ?>">
                                    <?php if ($usr['is_active']): ?>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                    <?php else: ?>
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    <?php endif; ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (empty($users)): ?>
            <div class="text-center py-12">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">Nenhum utilizador</h3>
                <p class="mt-1 text-sm text-gray-500">Comece criando um novo utilizador</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
    async function toggleUserStatus(userId, activate) {
        let reason = null;

        if (!activate) {
            reason = prompt('Motivo da desativação (opcional):');
            if (reason === null) return; // User clicked cancel
        }

        const action = activate ? 'ativar' : 'desativar';
        if (!confirm(`Tem certeza que deseja ${action} este utilizador?`)) return;

        try {
            const response = await fetch(`<?= url('/admin/users') ?>/${userId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ reason })
            });

            const result = await response.json();

            if (result.success) {
                window.location.reload();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            alert('Erro ao processar pedido');
            console.error(error);
        }
    }
</script>