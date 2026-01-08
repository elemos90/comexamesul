<?php
$title = 'Júri #' . $jury['id'];
$breadcrumbs = [
    ['label' => 'Júris', 'url' => url('/juries')],
    ['label' => $jury['subject']]
];
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>
    <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6 space-y-4">
        <div class="flex flex-wrap items-center justify-between">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800"><?= htmlspecialchars($jury['subject']) ?></h1>
                <p class="text-sm text-gray-500 mt-1">
                    <?= htmlspecialchars(date('d/m/Y', strtotime($jury['exam_date']))) ?> ·
                    <?= htmlspecialchars(substr($jury['start_time'], 0, 5)) ?> -
                    <?= htmlspecialchars(substr($jury['end_time'], 0, 5)) ?> ·
                    <?= htmlspecialchars($jury['location']) ?> / Sala <?= htmlspecialchars($jury['room']) ?></p>
            </div>
            <?php if ((int) $jury['supervisor_id'] === (int) \App\Utils\Auth::id() && !$report): ?>
                <a href="url('/juries/<?= $jury['id'] ?>/report"
                    class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500">Submeter
                    relatório</a>
            <?php endif; ?>
        </div>
        <?php if (!empty($jury['notes'])): ?>
            <div class="bg-primary-50 border border-primary-200 rounded p-4 text-sm text-primary-700">
                <strong>Observações:</strong> <?= htmlspecialchars($jury['notes']) ?>
            </div>
        <?php endif; ?>
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Vigilantes alocados</h2>
            <ul class="grid md:grid-cols-2 gap-3">
                <?php foreach ($vigilantes as $vigilante): ?>
                    <li class="bg-gray-50 border border-gray-100 rounded p-3">
                        <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($vigilante['name']) ?></p>
                        <p class="text-xs text-gray-500"><?= htmlspecialchars($vigilante['email']) ?> ·
                            <?= htmlspecialchars($vigilante['phone']) ?></p>
                    </li>
                <?php endforeach; ?>
                <?php if (!$vigilantes): ?>
                    <li class="text-sm text-gray-500">Sem vigilantes atribuídos.</li>
                <?php endif; ?>
            </ul>
        </div>
        <div>
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Supervisor</h2>
            <?php if (!empty($jury['supervisor_name'])): ?>
                <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($jury['supervisor_name']) ?></p>
            <?php else: ?>
                <p class="text-sm text-gray-500 italic">Não definido</p>
            <?php endif; ?>
        </div>
        <?php if ($report): ?>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <h3 class="text-lg font-semibold text-gray-800 mb-2">Relatório submetido</h3>
                <dl class="grid md:grid-cols-4 gap-4 text-sm text-gray-600">
                    <div>
                        <dt class="font-medium text-gray-700">Presentes (H)</dt>
                        <dd><?= (int) $report['present_m'] ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Presentes (M)</dt>
                        <dd><?= (int) $report['present_f'] ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Ausentes (H)</dt>
                        <dd><?= (int) $report['absent_m'] ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Ausentes (M)</dt>
                        <dd><?= (int) $report['absent_f'] ?></dd>
                    </div>
                    <div>
                        <dt class="font-medium text-gray-700">Total</dt>
                        <dd><?= (int) $report['total'] ?></dd>
                    </div>
                </dl>
                <?php if (!empty($report['occurrences'])): ?>
                    <div class="mt-3 text-sm text-gray-600">
                        <p class="font-medium text-gray-700">Ocorrências</p>
                        <p><?= nl2br(htmlspecialchars($report['occurrences'])) ?></p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>