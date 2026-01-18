<?php
use App\Utils\Auth;

// Authorization check
if (!Auth::hasAnyRole(['coordenador'])) {
    redirect('/dashboard?error=unauthorized');
    exit;
}

$title = 'Histórico de Notificações';
?>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-semibold text-gray-900">Histórico de Notificações</h1>
        <a href="<?= url('/notifications/create') ?>"
            class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
            Nova Notificação
        </a>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <p class="text-sm font-medium text-green-800">Notificação enviada com sucesso!</p>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
        <form method="GET" class="grid md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
                <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos</option>
                    <option value="informativa" <?= ($filters['type'] ?? '') === 'informativa' ? 'selected' : '' ?>
                        >Informativa</option>
                    <option value="alerta" <?= ($filters['type'] ?? '') === 'alerta' ? 'selected' : '' ?>>Alerta</option>
                    <option value="urgente" <?= ($filters['type'] ?? '') === 'urgente' ? 'selected' : '' ?>>Urgente
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Contexto</label>
                <select name="context_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    <option value="">Todos</option>
                    <option value="general" <?= ($filters['context_type'] ?? '') === 'general' ? 'selected' : '' ?>>Geral
                    </option>
                    <option value="exam" <?= ($filters['context_type'] ?? '') === 'exam' ? 'selected' : '' ?>>Exame
                    </option>
                    <option value="jury" <?= ($filters['context_type'] ?? '') === 'jury' ? 'selected' : '' ?>>Júri
                    </option>
                    <option value="payment" <?= ($filters['context_type'] ?? '') === 'payment' ? 'selected' : '' ?>
                        >Pagamentos</option>
                    <option value="report" <?= ($filters['context_type'] ?? '') === 'report' ? 'selected' : '' ?>
                        >Relatórios</option>
                    <option value="user" <?= ($filters['context_type'] ?? '') === 'user' ? 'selected' : '' ?>>Utilizadores
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Início</label>
                <input type="date" name="date_from" value="<?= $filters['date_from'] ?? '' ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Data Fim</label>
                <input type="date" name="date_to" value="<?= $filters['date_to'] ?? '' ?>"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
            </div>

            <div class="md:col-span-4 flex gap-2">
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                    Filtrar
                </button>
                <a href="<?= url('/notifications/history') ?>"
                    class="px-4 py-2 bg-white border border-gray-300 text-sm font-medium rounded-lg hover:bg-gray-50">
                    Limpar
                </a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data/Hora</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Assunto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Contexto</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Destinatários</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Lidas</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Origem</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                <?php if (empty($notifications)): ?>
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            Nenhuma notificação encontrada
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($notifications as $notification): ?>
                        <?php
                        $typeColors = [
                            'informativa' => 'blue',
                            'alerta' => 'orange',
                            'urgente' => 'red'
                        ];
                        $color = $typeColors[$notification['type']] ?? 'gray';
                        ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= date('d/m/Y H:i', strtotime($notification['created_at'])) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-<?= $color ?>-100 text-<?= $color ?>-800">
                                    <?= ucfirst($notification['type']) ?>
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                <?= htmlspecialchars($notification['subject']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= ucfirst($notification['context_type']) ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <?= $notification['total_recipients'] ?? 0 ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= $notification['read_count'] ?? 0 ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                <?= $notification['is_automatic'] ? 'Automática' : ($notification['creator_name'] ?? 'Manual') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>