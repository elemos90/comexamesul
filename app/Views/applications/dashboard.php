<?php
$title = 'Dashboard de Candidaturas';
$breadcrumbs = [
    ['label' => 'Candidaturas', 'url' => url('/applications')],
    ['label' => 'Dashboard']
];
?>

<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard de Candidaturas</h1>
            <p class="mt-2 text-sm text-gray-600">Estatísticas e análises do sistema de candidaturas</p>
        </div>
        <a href="<?= url('/applications/export') ?>"
            class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 transition-colors flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Exportar Relatório
        </a>
    </div>

    <!-- Cards de Estatísticas Principais -->
    <div class="grid md:grid-cols-4 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Total</p>
                    <p class="text-3xl font-bold text-gray-800">
                        <?= number_format($generalStats['total_applications']) ?>
                    </p>
                </div>
                <div class="p-3 bg-blue-100 rounded-full">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Candidaturas totais</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Pendentes</p>
                    <p class="text-3xl font-bold text-yellow-600"><?= number_format($generalStats['pending_count']) ?>
                    </p>
                </div>
                <div class="p-3 bg-yellow-100 rounded-full">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Aguardando revisão</p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Aprovadas</p>
                    <p class="text-3xl font-bold text-green-600"><?= number_format($generalStats['approved_count']) ?>
                    </p>
                </div>
                <div class="p-3 bg-green-100 rounded-full">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">Taxa: <?= number_format($generalStats['approval_rate'] ?? 0, 1) ?>%
            </p>
        </div>

        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 mb-1">Tempo Médio</p>
                    <p class="text-3xl font-bold text-purple-600">
                        <?= number_format($generalStats['avg_review_hours'] ?? 0, 1) ?>h
                    </p>
                </div>
                <div class="p-3 bg-purple-100 rounded-full">
                    <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-2">De revisão</p>
        </div>
    </div>

    <!-- Candidaturas Urgentes -->
    <?php if (!empty($urgentApplications)): ?>
        <div class="bg-red-50 border-l-4 border-red-400 p-4 rounded">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-3 flex-1">
                    <h3 class="text-sm font-semibold text-red-800">⚠️ Candidaturas Urgentes</h3>
                    <p class="text-sm text-red-700 mt-1">
                        <?= count($urgentApplications) ?> candidatura(s) pendente(s) há mais de 48 horas.
                        <a href="<?= url('/applications') ?>" class="underline font-medium">Revisar agora →</a>
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="grid md:grid-cols-2 gap-6">
        <!-- Distribuição de Status -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Distribuição por Status</h2>

            <?php if (!empty($statusDistribution)): ?>
                <div class="space-y-3">
                    <?php foreach ($statusDistribution as $item): ?>
                        <div>
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-gray-700"><?= htmlspecialchars($item['status']) ?></span>
                                <span class="text-sm font-semibold text-gray-800"><?= $item['count'] ?>
                                    (<?= $item['percentage'] ?>%)</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-2">
                                <div class="h-2 rounded-full transition-all"
                                    style="width: <?= $item['percentage'] ?>%; background-color: <?= $item['color'] ?>;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-500 text-center py-8">Sem dados disponíveis</p>
            <?php endif; ?>
        </div>

        <!-- Candidaturas por Dia -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Candidaturas por Dia (Últimos 30 dias)</h2>

            <?php if (!empty($applicationsByDay)): ?>
                <div class="space-y-2">
                    <?php
                    $maxCount = max(array_column($applicationsByDay, 'count'));
                    foreach (array_reverse(array_slice($applicationsByDay, 0, 10)) as $day):
                        $percentage = $maxCount > 0 ? ($day['count'] / $maxCount) * 100 : 0;
                        ?>
                        <div class="flex items-center gap-3">
                            <span
                                class="text-xs font-medium text-gray-500 w-20"><?= date('d/m', strtotime($day['date'])) ?></span>
                            <div class="flex-1 bg-gray-200 rounded-full h-6 relative">
                                <div class="bg-primary-500 h-6 rounded-full flex items-center justify-end pr-2"
                                    style="width: <?= max($percentage, 5) ?>%;">
                                    <span class="text-xs font-semibold text-white"><?= $day['count'] ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-500 text-center py-8">Sem dados disponíveis</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Top Vigilantes -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Top 10 Vigilantes Mais Ativos</h2>

        <?php if (!empty($topVigilantes)): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nome</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Aprovadas</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Rejeitadas</th>
                            <th class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase">Recandidaturas
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($topVigilantes as $index => $vigilante): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-semibold text-gray-600"><?= $index + 1 ?></td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-800">
                                    <?= htmlspecialchars($vigilante['name']) ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($vigilante['email']) ?></td>
                                <td class="px-4 py-3 text-sm text-center font-semibold text-gray-800">
                                    <?= $vigilante['total_applications'] ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-green-600"><?= $vigilante['approved_count'] ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-center text-red-600"><?= $vigilante['rejected_count'] ?></td>
                                <td class="px-4 py-3 text-sm text-center text-gray-600"><?= $vigilante['total_reapplies'] ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-sm text-gray-500 text-center py-8">Sem dados disponíveis</p>
        <?php endif; ?>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        <!-- Tempo de Revisão por Coordenador -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Tempo Médio de Revisão</h2>

            <?php if (!empty($avgReviewTime)): ?>
                <div class="space-y-3">
                    <?php foreach ($avgReviewTime as $coord): ?>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                            <div>
                                <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($coord['name']) ?></p>
                                <p class="text-xs text-gray-500"><?= $coord['reviews_count'] ?> revisões</p>
                            </div>
                            <div class="text-right">
                                <p class="text-lg font-bold text-primary-600"><?= number_format($coord['avg_hours'], 1) ?>h</p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-500 text-center py-8">Sem dados disponíveis</p>
            <?php endif; ?>
        </div>

        <!-- Top Motivos de Rejeição -->
        <div class="bg-white border border-gray-200 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Motivos de Rejeição Mais Comuns</h2>

            <?php if (!empty($topRejectionReasons)): ?>
                <div class="space-y-3">
                    <?php foreach ($topRejectionReasons as $reason): ?>
                        <div class="flex items-center justify-between p-3 bg-red-50 border border-red-100 rounded">
                            <div class="flex-1">
                                <p class="text-sm text-gray-800">
                                    <?= htmlspecialchars(substr($reason['rejection_reason'], 0, 60)) ?>
                                    <?= strlen($reason['rejection_reason']) > 60 ? '...' : '' ?>
                                </p>
                            </div>
                            <div class="ml-3">
                                <span
                                    class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-red-600 text-white">
                                    <?= $reason['count'] ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-sm text-gray-500 text-center py-8">Nenhuma rejeição registrada</p>
            <?php endif; ?>
        </div>
    </div>

    <!-- Estatísticas de Email -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">📧 Sistema de Notificações por Email</h2>

        <div class="grid md:grid-cols-4 gap-4">
            <div class="p-4 bg-blue-50 border border-blue-200 rounded">
                <p class="text-sm font-medium text-blue-600 mb-1">Total</p>
                <p class="text-2xl font-bold text-blue-700"><?= number_format($emailStats['total'] ?? 0) ?></p>
            </div>

            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded">
                <p class="text-sm font-medium text-yellow-600 mb-1">Pendentes</p>
                <p class="text-2xl font-bold text-yellow-700"><?= number_format($emailStats['pending'] ?? 0) ?></p>
            </div>

            <div class="p-4 bg-green-50 border border-green-200 rounded">
                <p class="text-sm font-medium text-green-600 mb-1">Enviados</p>
                <p class="text-2xl font-bold text-green-700"><?= number_format($emailStats['sent'] ?? 0) ?></p>
            </div>

            <div class="p-4 bg-red-50 border border-red-200 rounded">
                <p class="text-sm font-medium text-red-600 mb-1">Falhados</p>
                <p class="text-2xl font-bold text-red-700"><?= number_format($emailStats['failed'] ?? 0) ?></p>
            </div>
        </div>

        <div class="mt-4 p-3 bg-gray-50 rounded">
            <p class="text-sm text-gray-600">
                <span class="font-semibold">Taxa de Sucesso:</span>
                <span
                    class="text-lg font-bold text-green-600"><?= number_format($emailStats['success_rate'] ?? 0, 1) ?>%</span>
            </p>
        </div>
    </div>
</div>