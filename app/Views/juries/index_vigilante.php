<?php
$title = 'Meus Júris';
$breadcrumbs = [
    ['label' => 'Júris']
];
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Meus Júris</h1>
            <p class="text-sm text-gray-500">Júris onde estou alocado como vigilante</p>
        </div>
    </div>

    <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Disciplina</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sala</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horário
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        Supervisor</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php foreach ($juries as $jury): ?>
                    <tr>
                        <td class="px-4 py-3 text-sm font-medium text-gray-700"><?= htmlspecialchars($jury['subject']) ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($jury['room']) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <?= htmlspecialchars(date('d/m/Y', strtotime($jury['exam_date']))) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <?= htmlspecialchars(substr($jury['start_time'], 0, 5)) ?> -
                            <?= htmlspecialchars(substr($jury['end_time'], 0, 5)) ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($jury['location'] ?? '—') ?></td>
                        <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($jury['supervisor_name'] ?? '—') ?>
                        </td>
                        <td class="px-4 py-3 text-sm text-right">
                            <a href="<?= url('/juries/' . $jury['id'] . '/report') ?>"
                                class="text-indigo-600 hover:text-indigo-900 font-medium">
                                Submeter Relatório
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$juries): ?>
                    <tr>
                        <td colspan="6" class="px-4 py-6 text-center text-sm text-gray-500">Ainda não foi alocado a júris.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>