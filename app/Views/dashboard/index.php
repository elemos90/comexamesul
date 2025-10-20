<?php
$title = 'Painel principal';
$breadcrumbs = [
    ['label' => 'Dashboard']
];
$isVigilante = ($user['role'] === 'vigilante');
$helpPage = 'dashboard'; // Identificador para o sistema de ajuda
?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <?php include view_path('partials/breadcrumbs.php'); ?>
        <?php include view_path('partials/help_button.php'); ?>
    </div>

    <div class="grid md:grid-cols-3 gap-5">
        <div class="bg-white border border-gray-100 rounded-lg p-5 shadow-sm">
            <p class="text-sm text-gray-500">Vagas abertas</p>
            <p class="text-3xl font-semibold text-primary-600 mt-2"><?= (int) ($openVacancies ?? 0) ?></p>
        </div>
        <?php if ($isVigilante): ?>
            <div class="bg-white border border-gray-100 rounded-lg p-5 shadow-sm">
                <p class="text-sm text-gray-500">Minha disponibilidade</p>
                <p class="text-3xl font-semibold <?= $isAvailable ? 'text-green-600' : 'text-gray-500' ?> mt-2">
                    <?= $isAvailable ? 'Disponivel' : 'Indisponivel' ?>
                </p>
            </div>
        <?php else: ?>
            <div class="bg-white border border-gray-100 rounded-lg p-5 shadow-sm">
                <p class="text-sm text-gray-500">Vigilantes disponiveis</p>
                <p class="text-3xl font-semibold text-primary-600 mt-2"><?= (int) ($availableVigilantes ?? 0) ?></p>
            </div>
        <?php endif; ?>
        <div class="bg-white border border-gray-100 rounded-lg p-5 shadow-sm">
            <p class="text-sm text-gray-500">Próximo dia de júris</p>
            <p class="text-3xl font-semibold text-primary-600 mt-2"><?= isset($upcomingJuries) ? count($upcomingJuries) : 0 ?></p>
            <?php if (!empty($upcomingJuries)): ?>
                <p class="text-xs text-gray-500 mt-1"><?= date('d/m/Y', strtotime($upcomingJuries[0]['exam_date'])) ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($isVigilante): ?>
        <div class="bg-blue-50 border border-blue-100 text-blue-800 text-sm rounded-lg px-4 py-3">
            Actualize a sua <a href="/availability" class="underline font-medium">disponibilidade</a> sempre que necessitar. A comissao usa esta informacao para planear as vigias.
        </div>
    <?php endif; ?>

    <div class="bg-white border border-gray-100 rounded-lg shadow-sm">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Próximos Júris</h2>
                <?php if (!empty($upcomingJuries)): ?>
                    <p class="text-xs text-gray-500 mt-1">
                        <?= date('d/m/Y', strtotime($upcomingJuries[0]['exam_date'])) ?> - 
                        <?= count($upcomingJuries) ?> júri<?= count($upcomingJuries) > 1 ? 's' : '' ?> agendado<?= count($upcomingJuries) > 1 ? 's' : '' ?>
                    </p>
                <?php endif; ?>
            </div>
            <?php if (count($upcomingJuries) > 3): ?>
                <span class="text-xs text-gray-500 bg-blue-50 px-2 py-1 rounded">
                    <?= count($upcomingJuries) ?> júris no mesmo dia
                </span>
            <?php endif; ?>
        </div>
        <div class="overflow-x-auto max-h-[240px] overflow-y-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50 sticky top-0 z-10">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplina</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horario</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Local</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($upcomingJuries as $jury): ?>
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-4 py-3 text-sm text-gray-700 font-medium"><?= htmlspecialchars($jury['subject']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars(date('d/m/Y', strtotime($jury['exam_date']))) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars(substr($jury['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr($jury['end_time'], 0, 5)) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($jury['location']) ?> / Sala <?= htmlspecialchars($jury['room']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($jury['supervisor_name'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$upcomingJuries): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Sem registos a apresentar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<style>
/* Estilo personalizado para scroll suave na agenda de júris */
.overflow-y-auto::-webkit-scrollbar {
    width: 8px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: #f1f5f9;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}

/* Garantir que o cabeçalho fixo tenha fundo sólido */
thead.sticky {
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
}
</style>
