<?php
$title = 'Dashboard de Júris por Local';
$breadcrumbs = [
    ['label' => 'Júris por Local', 'url' => url('/locations')],
    ['label' => 'Dashboard']
];
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Dashboard de Júris por Local</h1>
            <p class="text-sm text-gray-500">Estatísticas e análise de júris por local de realização</p>
        </div>
        <a href="<?= url('/locations') ?>"
            class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded hover:bg-gray-500">Voltar</a>
    </div>

    <!-- Top Locais -->
    <?php if (!empty($topLocations)): ?>
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">🏆 Top Locais por Capacidade</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Local</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Júris</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Candidatos</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Vigilantes</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Dias de Exame</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-100">
                        <?php foreach ($topLocations as $index => $loc): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 text-sm font-bold text-gray-900"><?= $index + 1 ?></td>
                                <td class="px-4 py-3 text-sm font-medium text-gray-700">
                                    <?= htmlspecialchars($loc['location']) ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600">
                                    <?= number_format((int) $loc['total_juries']) ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-semibold text-blue-600">
                                    <?= number_format((int) $loc['total_candidates']) ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600">
                                    <?= number_format((int) $loc['total_vigilantes']) ?>
                                </td>
                                <td class="px-4 py-3 text-sm text-right text-gray-600"><?= (int) $loc['exam_days'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>

    <!-- Estatísticas Detalhadas por Local -->
    <?php if (!empty($statsByLocation)): ?>
        <div class="grid lg:grid-cols-2 gap-6">
            <?php foreach ($statsByLocation as $locStat): ?>
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="px-5 py-4 bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-gray-200">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center justify-center w-10 h-10 bg-blue-600 text-white rounded-lg">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd"
                                        d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                        clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="flex-1">
                                <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($locStat['location']) ?></h3>
                                <p class="text-sm text-gray-600"><?= count($locStat['dates']) ?> data(s) de exame</p>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Summary -->
                    <div class="grid grid-cols-3 divide-x divide-gray-200 bg-gray-50">
                        <div class="px-4 py-3 text-center">
                            <p class="text-2xl font-bold text-blue-600"><?= number_format($locStat['total_juries']) ?></p>
                            <p class="text-xs text-gray-600 mt-1">Júris</p>
                        </div>
                        <div class="px-4 py-3 text-center">
                            <p class="text-2xl font-bold text-green-600"><?= number_format($locStat['total_candidates']) ?></p>
                            <p class="text-xs text-gray-600 mt-1">Candidatos</p>
                        </div>
                        <div class="px-4 py-3 text-center">
                            <p class="text-2xl font-bold text-purple-600"><?= number_format($locStat['total_vigilantes']) ?></p>
                            <p class="text-xs text-gray-600 mt-1">Vigilantes</p>
                        </div>
                    </div>

                    <!-- Datas -->
                    <div class="p-4">
                        <h4 class="text-sm font-semibold text-gray-700 mb-3 uppercase">Datas de Exame</h4>
                        <div class="space-y-2">
                            <?php foreach ($locStat['dates'] as $dateStat): ?>
                                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <span
                                            class="text-sm font-medium text-gray-700"><?= date('d/m/Y', strtotime($dateStat['exam_date'])) ?></span>
                                    </div>
                                    <div class="flex items-center gap-3 text-xs text-gray-600">
                                        <span><?= $dateStat['total_disciplines'] ?> disc.</span>
                                        <span class="font-semibold text-blue-600"><?= $dateStat['total_candidates'] ?> cand.</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <p class="mt-4 text-sm text-gray-500">Nenhuma estatística disponível ainda.</p>
        </div>
    <?php endif; ?>
</div>