<?php
$title = 'Painel principal';
$breadcrumbs = [
    ['label' => 'Dashboard']
];
$isVigilante = ($user['role'] === 'vigilante');
$helpPage = 'dashboard';
?>
<div class="space-y-4">
    <?php $upcomingJuries = $upcomingJuries ?? []; ?>
    <!-- Header Compacto -->
    <div class="flex items-center justify-between">
        <?php include view_path('partials/breadcrumbs.php'); ?>
        <?php include view_path('partials/help_button.php'); ?>
    </div>

    <!-- Stats Cards - Grid Compacto -->
    <div class="grid grid-cols-3 gap-3">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vagas abertas</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?= (int) ($openVacancies ?? 0) ?></p>
        </div>

        <?php if ($isVigilante): ?>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Minha disponibilidade</p>
                <div class="flex items-center gap-2 mt-1">
                    <span class="w-2 h-2 rounded-full <?= $isAvailable ? 'bg-emerald-500' : 'bg-gray-400' ?>"></span>
                    <span class="text-lg font-semibold <?= $isAvailable ? 'text-emerald-600' : 'text-gray-500' ?>">
                        <?= $isAvailable ? 'Disponível' : 'Indisponível' ?>
                    </span>
                </div>
            </div>
        <?php else: ?>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Vigilantes disponíveis</p>
                <p class="text-2xl font-bold text-gray-900 mt-1"><?= (int) ($availableVigilantes ?? 0) ?></p>
            </div>
        <?php endif; ?>

        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <p class="text-xs font-medium text-gray-500 uppercase tracking-wide">Próximos júris</p>
            <div class="flex items-baseline gap-2 mt-1">
                <span
                    class="text-2xl font-bold text-gray-900"><?= isset($upcomingJuries) ? count($upcomingJuries) : 0 ?></span>
                <?php if (!empty($upcomingJuries)): ?>
                    <span
                        class="text-xs text-gray-500"><?= date('d/m', strtotime($upcomingJuries[0]['exam_date'])) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($isVigilante): ?>
        <!-- Alert Compacto -->
        <div class="flex items-center gap-2 bg-blue-50 border border-blue-200 rounded-md px-3 py-2 text-sm text-blue-700">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <span>Atualize a sua <a href="<?= url('/availability') ?>" class="underline font-medium">disponibilidade</a>
                sempre que necessário.</span>
        </div>
    <?php endif; ?>

    <!-- Tabela de Júris - Design Compacto -->
    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-semibold text-gray-900">Próximos Júris</h2>
                <?php if (!empty($upcomingJuries)): ?>
                    <p class="text-xs text-gray-500">
                        <?= date('d/m/Y', strtotime($upcomingJuries[0]['exam_date'])) ?> · <?= count($upcomingJuries) ?>
                        agendado<?= count($upcomingJuries) > 1 ? 's' : '' ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php if (count($upcomingJuries) > 3): ?>
                <span class="text-xs text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                    <?= count($upcomingJuries) ?> no mesmo dia
                </span>
            <?php endif; ?>
        </div>

        <div class="overflow-x-auto max-h-[200px] overflow-y-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Disciplina</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Horário</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local
                        </th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Supervisor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php foreach ($upcomingJuries as $jury): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-3 py-2 text-gray-900 font-medium"><?= htmlspecialchars($jury['subject']) ?></td>
                            <td class="px-3 py-2 text-gray-600"><?= date('d/m', strtotime($jury['exam_date'])) ?></td>
                            <td class="px-3 py-2 text-gray-600">
                                <?= substr($jury['start_time'], 0, 5) ?>-<?= substr($jury['end_time'], 0, 5) ?>
                            </td>
                            <td class="px-3 py-2 text-gray-600 truncate max-w-[150px]"
                                title="<?= htmlspecialchars($jury['location'] . ' / ' . $jury['room']) ?>">
                                <?= htmlspecialchars($jury['room']) ?>
                            </td>
                            <td class="px-3 py-2 text-gray-600"><?= htmlspecialchars($jury['supervisor_name'] ?? '-') ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($upcomingJuries)): ?>
                        <tr>
                            <td colspan="5" class="px-3 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center gap-1">
                                    <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                    </svg>
                                    <span class="text-sm">Sem júris agendados</span>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>