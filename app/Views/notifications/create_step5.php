<?php
$title = 'Nova Notificação - Confirmação';
$breadcrumbs = [
    ['label' => 'Notificações'],
    ['label' => 'Nova Notificação'],
    ['label' => 'Passo 5/5']
];

// Map types and contexts
$typeNames = [
    'informativa' => 'Informativa',
    'alerta' => 'Alerta',
    'urgente' => 'Urgente'
];

$contextNames = [
    'general' => 'Geral',
    'exam' => 'Exame',
    'jury' => 'Júri',
    'payment' => 'Pagamentos',
    'report' => 'Relatórios',
    'user' => 'Utilizadores'
];

$channelNames = [
    'internal' => 'Notificação Interna',
    'email' => 'Email',
    'sms' => 'SMS'
];
?>

<div class="max-w-3xl mx-auto">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <!-- Progress Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-primary-600">Passo 5 de 5</span>
            <span class="text-sm text-gray-500">Confirmação</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-primary-600 h-2 rounded-full" style="width: 100%"></div>
        </div>
    </div>

    <!-- Confirmation -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex items-center gap-3 mb-6">
            <div class="flex-shrink-0">
                <svg class="w-12 h-12 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                        clip-rule="evenodd"></path>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-semibold text-gray-900">Tudo Pronto!</h2>
                <p class="text-sm text-gray-600">Revise os detalhes antes de enviar</p>
            </div>
        </div>

        <!-- Summary Grid -->
        <div class="grid md:grid-cols-2 gap-6 mb-6">
            <!-- Type -->
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-1">Tipo</p>
                <p class="font-medium text-gray-900">
                    <?= $typeNames[$wizard['type']] ?? $wizard['type'] ?>
                </p>
            </div>

            <!-- Context -->
            <div class="bg-gray-50 rounded-lg p-4">
                <p class="text-xs text-gray-500 mb-1">Contexto</p>
                <p class="font-medium text-gray-900">
                    <?= $contextNames[$wizard['context_type']] ?? $wizard['context_type'] ?>
                    <?php if (!empty($wizard['context_id'])): ?>
                        <span class="text-sm text-gray-500">(ID:
                            <?= $wizard['context_id'] ?>)
                        </span>
                    <?php endif; ?>
                </p>
            </div>

            <!-- Recipients -->
            <div class="bg-gray-50 rounded-lg p-4 md:col-span-2">
                <p class="text-xs text-gray-500 mb-1">Destinatários</p>
                <p class="font-medium text-gray-900">
                    <?= $wizard['recipient_count'] ?? 0 ?> utilizadores
                </p>
            </div>

            <!-- Channels -->
            <div class="bg-gray-50 rounded-lg p-4 md:col-span-2">
                <p class="text-xs text-gray-500 mb-2">Canais de Envio</p>
                <div class="flex flex-wrap gap-2">
                    <?php foreach ($wizard['channels'] as $channel): ?>
                        <span
                            class="inline-flex items-center gap-1 px-3 py-1 bg-white border border-gray-300 rounded-full text-sm">
                            <?php if ($channel === 'internal'): ?>
                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6z">
                                    </path>
                                </svg>
                            <?php elseif ($channel === 'email'): ?>
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                    </path>
                                </svg>
                            <?php endif; ?>
                            <?= $channelNames[$channel] ?? $channel ?>
                        </span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Content Preview -->
        <div class="border border-gray-200 rounded-lg p-4 mb-6">
            <p class="text-xs text-gray-500 mb-3">Conteúdo</p>
            <h3 class="font-semibold text-gray-900 mb-2">
                <?= htmlspecialchars($wizard['subject']) ?>
            </h3>
            <p class="text-sm text-gray-600 whitespace-pre-wrap">
                <?= htmlspecialchars($wizard['message']) ?>
            </p>
        </div>

        <!-- Warning -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd"></path>
                </svg>
                <div>
                    <h4 class="text-sm font-medium text-yellow-900">Atenção</h4>
                    <p class="text-sm text-yellow-800 mt-1">
                        Esta ação não pode ser desfeita. A notificação será enviada imediatamente para todos os
                        destinatários selecionados.
                    </p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="<?= url('/notifications/send') ?>">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

            <div class="flex items-center justify-between pt-4 border-t">
                <a href="<?= url('/notifications/create/step4') ?>"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    ← Voltar
                </a>
                <button type="submit"
                    class="px-6 py-3 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Enviar Notificação
                </button>
            </div>
        </form>
    </div>
</div>