<?php
use App\Utils\Auth;

// Authorization check
if (!Auth::hasAnyRole(['coordenador'])) {
    redirect('/dashboard?error=unauthorized');
    exit;
}

$title = 'Editar Utilizador';
$breadcrumbs = [
    ['label' => 'Administração'],
    ['label' => 'Utilizadores', 'url' => '/admin/users'],
    ['label' => 'Editar']
];
?>

<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <p class="text-sm font-medium text-green-800">Utilizador atualizado com sucesso!</p>
        </div>
    <?php endif; ?>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Form -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Informações Básicas</h2>

                <form method="POST" action="<?= url('/admin/users/' . $user['id']) ?>" class="space-y-6">
                    <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nome Completo</label>
                        <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefone</label>
                        <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t">
                        <a href="<?= url('/admin/users') ?>"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                            Voltar
                        </a>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                            Guardar Alterações
                        </button>
                    </div>
                </form>
            </div>

            <!-- Role Management -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Gestão de Papéis</h2>

                <form id="rolesForm" class="space-y-4">
                    <?php foreach (\App\Services\UserRoleService::getValidRoles() as $role): ?>
                        <div class="flex items-center">
                            <input type="checkbox" id="role_<?= $role ?>" name="roles[]" value="<?= $role ?>"
                                <?= in_array($role, $user['roles_array']) ? 'checked' : '' ?>
                                class="h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded">
                            <label for="role_<?= $role ?>" class="ml-3 block text-sm">
                                <span class="font-medium text-gray-900">
                                    <?= \App\Services\UserRoleService::getRoleDisplayName($role) ?>
                                </span>
                            </label>
                        </div>
                    <?php endforeach; ?>

                    <button type="button" onclick="updateRoles()"
                        class="mt-4 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700">
                        Atualizar Papéis
                    </button>
                </form>
            </div>

            <!-- Audit Log -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Histórico de Alterações</h2>

                <?php if (!empty($auditLog)): ?>
                    <div class="space-y-4">
                        <?php foreach ($auditLog as $log): ?>
                            <div class="flex items-start gap-3 pb-4 border-b last:border-b-0">
                                <div class="flex-shrink-0 mt-0.5">
                                    <div class="h-8 w-8 rounded-full bg-gray-100 flex items-center justify-center">
                                        <svg class="w-4 h-4 text-gray-600" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900">
                                        <?= htmlspecialchars($log['details']) ?>
                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        <?= htmlspecialchars($log['performed_by_name'] ?? 'Sistema') ?> •
                                        <?= date('d/m/Y H:i', strtotime($log['created_at'])) ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <p class="text-sm text-gray-500">Nenhuma alteração registada</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sidebar Actions -->
        <div class="space-y-6">
            <!-- Quick Promotions -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Promoções Rápidas</h3>
                <div class="space-y-3">
                    <?php if (!in_array('supervisor', $user['roles_array'])): ?>
                        <button onclick="promoteToSupervisor()"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700">
                            Promover a Supervisor
                        </button>
                    <?php endif; ?>

                    <?php if (!in_array('membro', $user['roles_array'])): ?>
                        <button onclick="promoteToMember()"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                            Promover a Membro
                        </button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Status -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Estado da Conta</h3>
                <div class="text-center">
                    <?php if ($user['is_active']): ?>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800 mb-3">
                            Ativa
                        </span>
                        <button onclick="toggleStatus(false)"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-red-600 rounded-lg hover:bg-red-700">
                            Desativar Conta
                        </button>
                    <?php else: ?>
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800 mb-3">
                            Inativa
                        </span>
                        <?php if ($user['deactivation_reason']): ?>
                            <p class="text-xs text-gray-600 mb-3">
                                <strong>Motivo:</strong>
                                <?= htmlspecialchars($user['deactivation_reason']) ?>
                            </p>
                        <?php endif; ?>
                        <button onclick="toggleStatus(true)"
                            class="w-full px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                            Reativar Conta
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    const userId = <?= $user['id'] ?>;

    async function updateRoles() {
        const formData = new FormData(document.getElementById('rolesForm'));
        const roles = formData.getAll('roles[]');

        try {
            const response = await fetch(`<?= url('/admin/users') ?>/${userId}/roles`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ roles })
            });

            const result = await response.json();
            if (result.success) {
                alert(result.message);
                window.location.reload();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            alert('Erro ao processar pedido');
        }
    }

    async function promoteToSupervisor() {
        if (!confirm('Promover este utilizador a Supervisor?')) return;

        try {
            const response = await fetch(`<?= url('/admin/users') ?>/${userId}/promote-supervisor`, {
                method: 'POST'
            });

            const result = await response.json();
            if (result.success) {
                alert(result.message);
                window.location.reload();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            alert('Erro ao processar pedido');
        }
    }

    async function promoteToMember() {
        if (!confirm('Promover este utilizador a Membro da Comissão?')) return;

        try {
            const response = await fetch(`<?= url('/admin/users') ?>/${userId}/promote-member`, {
                method: 'POST'
            });

            const result = await response.json();
            if (result.success) {
                alert(result.message);
                window.location.reload();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            alert('Erro ao processar pedido');
        }
    }

    async function toggleStatus(activate) {
        let reason = null;
        if (!activate) {
            reason = prompt('Motivo da desativação (opcional):');
            if (reason === null) return;
        }

        const action = activate ? 'reativar' : 'desativar';
        if (!confirm(`Tem certeza que deseja ${action} esta conta?`)) return;

        try {
            const response = await fetch(`<?= url('/admin/users') ?>/${userId}/toggle-status`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
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
        }
    }
</script>