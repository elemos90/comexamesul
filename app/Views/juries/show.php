<?php
$title = 'Júri #' . $jury['id'];
$breadcrumbs = [
    ['label' => 'Júris', 'url' => url('/juries')],
    ['label' => $jury['subject']]
];

// Calculate totals from report
$totalPresentes = ($report['present_m'] ?? 0) + ($report['present_f'] ?? 0);
$totalAusentes = ($report['absent_m'] ?? 0) + ($report['absent_f'] ?? 0);
$totalFraudes = ($report['fraudes_m'] ?? 0) + ($report['fraudes_f'] ?? 0);
$totalGeral = $totalPresentes + $totalAusentes;
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>
    <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6 space-y-6">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between border-b pb-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800"><?= htmlspecialchars($jury['subject']) ?></h1>
                <p class="text-sm text-gray-500 mt-1">
                    <?= htmlspecialchars(date('d/m/Y', strtotime($jury['exam_date']))) ?> ·
                    <?= htmlspecialchars(substr($jury['start_time'], 0, 5)) ?> -
                    <?= htmlspecialchars(substr($jury['end_time'], 0, 5)) ?> ·
                    <?= htmlspecialchars($jury['location']) ?> / Sala <?= htmlspecialchars($jury['room']) ?>
                </p>
            </div>
            <?php if ((int) $jury['supervisor_id'] === (int) \App\Utils\Auth::id() && !$report): ?>
                <a href="<?= url('/juries/' . $jury['id'] . '/report') ?>"
                    class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Submeter relatório
                </a>
            <?php endif; ?>
        </div>

        <?php if (!empty($jury['notes'])): ?>
            <div class="bg-primary-50 border border-primary-200 rounded p-4 text-sm text-primary-700">
                <strong>Observações:</strong> <?= htmlspecialchars($jury['notes']) ?>
            </div>
        <?php endif; ?>

        <!-- Vigilantes -->
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                </svg>
                Vigilantes alocados
            </h2>
            <ul class="grid md:grid-cols-2 gap-3">
                <?php foreach ($vigilantes as $vigilante): ?>
                    <li class="bg-gray-50 border border-gray-100 rounded p-3">
                        <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($vigilante['name']) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($vigilante['email']) ?> ·
                            <?= htmlspecialchars($vigilante['phone']) ?>
                        </p>
                    </li>
                <?php endforeach; ?>
                <?php if (!$vigilantes): ?>
                    <li class="text-sm text-gray-500 italic">Sem vigilantes atribuídos.</li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Supervisor -->
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
                Supervisor
            </h2>
            <?php if (!empty($jury['supervisor_name'])): ?>
                <p class="text-sm font-medium text-gray-700 bg-purple-50 inline-block px-3 py-1 rounded">
                    <?= htmlspecialchars($jury['supervisor_name']) ?>
                </p>
            <?php else: ?>
                <p class="text-sm text-gray-500 italic">Não definido</p>
            <?php endif; ?>
        </div>

        <!-- Relatório Submetido -->
        <?php if ($report): ?>
            <div class="bg-gradient-to-r from-green-50 to-blue-50 border border-green-200 rounded-lg p-5">
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Relatório Submetido
                </h3>

                <!-- Tabela de Dados -->
                <div class="overflow-hidden rounded-lg border border-gray-200 mb-4">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 font-medium">
                                <th class="text-left px-4 py-2">Estado</th>
                                <th class="text-center px-4 py-2">Masculino</th>
                                <th class="text-center px-4 py-2">Feminino</th>
                                <th class="text-center px-4 py-2 bg-gray-200">Total</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr>
                                <td class="px-4 py-3 font-medium text-green-700">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                                        Presentes
                                    </span>
                                </td>
                                <td class="text-center px-4 py-3"><?= (int) $report['present_m'] ?></td>
                                <td class="text-center px-4 py-3"><?= (int) $report['present_f'] ?></td>
                                <td class="text-center px-4 py-3 bg-green-50 font-bold text-green-700">
                                    <?= $totalPresentes ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-red-700">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                                        Ausentes
                                    </span>
                                </td>
                                <td class="text-center px-4 py-3"><?= (int) $report['absent_m'] ?></td>
                                <td class="text-center px-4 py-3"><?= (int) $report['absent_f'] ?></td>
                                <td class="text-center px-4 py-3 bg-red-50 font-bold text-red-700"><?= $totalAusentes ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-yellow-700">
                                    <span class="flex items-center gap-2">
                                        <span class="w-2 h-2 rounded-full bg-yellow-500"></span>
                                        Fraudes
                                    </span>
                                </td>
                                <td class="text-center px-4 py-3"><?= (int) ($report['fraudes_m'] ?? 0) ?></td>
                                <td class="text-center px-4 py-3"><?= (int) ($report['fraudes_f'] ?? 0) ?></td>
                                <td class="text-center px-4 py-3 bg-yellow-50 font-bold text-yellow-700">
                                    <?= $totalFraudes ?>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Resumo Visual -->
                <div class="grid grid-cols-4 gap-3 mb-4">
                    <div class="bg-white rounded-lg p-3 text-center shadow-sm">
                        <div class="text-2xl font-bold text-green-600"><?= $totalPresentes ?></div>
                        <div class="text-xs text-gray-500 uppercase">Presentes</div>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center shadow-sm">
                        <div class="text-2xl font-bold text-red-600"><?= $totalAusentes ?></div>
                        <div class="text-xs text-gray-500 uppercase">Ausentes</div>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center shadow-sm">
                        <div class="text-2xl font-bold text-yellow-600"><?= $totalFraudes ?></div>
                        <div class="text-xs text-gray-500 uppercase">Fraudes</div>
                    </div>
                    <div class="bg-white rounded-lg p-3 text-center shadow-sm border-2 border-blue-200">
                        <div class="text-2xl font-bold text-blue-600"><?= $totalGeral ?></div>
                        <div class="text-xs text-gray-500 uppercase">Total</div>
                    </div>
                </div>

                <?php if (!empty($report['occurrences'])): ?>
                    <div class="bg-white rounded-lg p-4 border border-gray-200">
                        <p class="font-medium text-gray-700 mb-1 flex items-center gap-2">
                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z" />
                            </svg>
                            Observações
                        </p>
                        <p class="text-sm text-gray-600"><?= nl2br(htmlspecialchars($report['occurrences'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>