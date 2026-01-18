<?php
use App\Utils\Auth;

// Authorization check
if (!Auth::hasAnyRole(['coordenador', 'membro', 'supervisor', 'vigilante'])) {
    redirect('/dashboard?error=unauthorized');
    exit;
}

$title = 'Minhas Notificações';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Minhas Notificações</h1>

        <?php if (Auth::hasAnyRole(['coordenador'])): ?>
            <a href="<?= url('/notifications/create') ?>"
                class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
                Nova Notificação
            </a>
        <?php endif; ?>
    </div>

    <!-- Tabs -->
    <div class="border-b border-gray-200">
        <nav class="-mb-px flex space-x-8">
            <a href="?filter=unread"
                class="<?= !isset($_GET['filter']) || $_GET['filter'] === 'unread' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Não Lidas
            </a>
            <a href="?filter=all"
                class="<?= isset($_GET['filter']) && $_GET['filter'] === 'all' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                Todas
            </a>
        </nav>
    </div>

    <!-- Notifications List -->
    <div class="space-y-3">
        <?php if (empty($notifications)): ?>
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <svg class="w-16 h-16 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9">
                    </path>
                </svg>
                <p class="text-gray-500">Nenhuma notificação</p>
            </div>
        <?php else: ?>
            <?php foreach ($notifications as $notification): ?>
                <?php
                $isUnread = $notification['read_at'] === null;
                $typeColors = [
                    'informativa' => 'blue',
                    'alerta' => 'orange',
                    'urgente' => 'red'
                ];
                $color = $typeColors[$notification['type']] ?? 'gray';
                ?>
                <div
                    class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 hover:shadow-md transition <?= $isUnread ? 'border-l-4 border-l-primary-500' : '' ?>">
                    <div class="flex items-start gap-4">
                        <!-- Icon -->
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-<?= $color ?>-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-<?= $color ?>-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z">
                                    </path>
                                </svg>
                            </div>
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <span
                                    class="inline-block px-2 py-0.5 text-xs font-medium rounded-full bg-<?= $color ?>-100 text-<?= $color ?>-800">
                                    <?= ucfirst($notification['type']) ?>
                                </span>
                                <?php if ($isUnread): ?>
                                    <span class="inline-block w-2 h-2 bg-primary-600 rounded-full"></span>
                                <?php endif; ?>
                                <span class="text-xs text-gray-500">
                                    <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                                </span>
                            </div>

                            <h3 class="font-medium text-gray-900 mb-1">
                                <?= htmlspecialchars($notification['subject']) ?>
                            </h3>

                            <p class="text-sm text-gray-600 line-clamp-2">
                                <?= htmlspecialchars($notification['message']) ?>
                            </p>

                            <div class="flex items-center gap-3 mt-3">
                                <?php if ($notification['context_type'] === 'password_reset_request' && Auth::hasAnyRole(['coordenador', 'admin'])): ?>
                                    <a href="<?= url('/admin/password-reset/' . $notification['context_id']) ?>"
                                        class="text-xs px-3 py-1 bg-primary-600 text-white rounded hover:bg-primary-700 font-medium">
                                        Resolver Pedido
                                    </a>
                                <?php endif; ?>

                                <?php if ($isUnread): ?>
                                    <button onclick="markAsRead(<?= $notification['id'] ?>)"
                                        class="text-xs text-primary-600 hover:text-primary-700 font-medium">
                                        Marcar como lida
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<script>
    async function markAsRead(notificationId) {
        try {
            const response = await fetch(`<?= url('/notifications') ?>/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();

            if (result.success) {
                window.location.reload();
            }
        } catch (error) {
            console.error('Error marking as read:', error);
        }
    }
</script>