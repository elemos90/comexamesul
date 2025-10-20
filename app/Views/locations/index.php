<?php
$title = 'Júris por Local';
$breadcrumbs = [
    ['label' => 'Júris', 'url' => '/juries'],
    ['label' => 'Por Local']
];
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Júris Agrupados por Local</h1>
            <p class="text-sm text-gray-500">Visualização organizada por local de realização e data</p>
        </div>
        <div class="flex gap-2">
            <a href="/locations/dashboard" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-500 flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M2 11a1 1 0 011-1h2a1 1 0 011 1v5a1 1 0 01-1 1H3a1 1 0 01-1-1v-5zM8 7a1 1 0 011-1h2a1 1 0 011 1v9a1 1 0 01-1 1H9a1 1 0 01-1-1V7zM14 4a1 1 0 011-1h2a1 1 0 011 1v12a1 1 0 01-1 1h-2a1 1 0 01-1-1V4z"/>
                </svg>
                Dashboard
            </a>
            <a href="/juries" class="px-4 py-2 bg-gray-600 text-white text-sm font-medium rounded hover:bg-gray-500">Voltar</a>
        </div>
    </div>

    <?php if (empty($locationGroups)): ?>
        <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="mt-4 text-sm text-gray-500">Nenhum júri registado ainda.</p>
        </div>
    <?php else: ?>
        <div class="space-y-6">
            <?php foreach ($locationGroups as $locationGroup): ?>
                <!-- Card do Local -->
                <div class="bg-white border-2 border-blue-200 rounded-lg shadow-md overflow-hidden">
                    <!-- Cabeçalho do Local -->
                    <div class="px-6 py-4 bg-blue-50 border-b-2 border-blue-200">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="flex items-center justify-center w-12 h-12 bg-blue-600 text-white rounded-lg">
                                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <div>
                                    <h2 class="text-xl font-bold text-blue-900"><?= htmlspecialchars($locationGroup['location']) ?></h2>
                                    <p class="text-sm text-blue-700">
                                        <svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd"/>
                                        </svg>
                                        <?= htmlspecialchars(date('d/m/Y', strtotime($locationGroup['exam_date']))) ?>
                                    </p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3">
                                <div class="text-right">
                                    <p class="text-2xl font-bold text-blue-900"><?= count($locationGroup['disciplines']) ?></p>
                                    <p class="text-xs text-blue-700">Disciplina(s)</p>
                                </div>
                                <div class="text-right">
                                    <?php 
                                    $totalRooms = 0;
                                    foreach ($locationGroup['disciplines'] as $disc) {
                                        $totalRooms += count($disc['juries']);
                                    }
                                    ?>
                                    <p class="text-2xl font-bold text-blue-900"><?= $totalRooms ?></p>
                                    <p class="text-xs text-blue-700">Sala(s)</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Disciplinas -->
                    <div class="p-6 space-y-4">
                        <?php foreach ($locationGroup['disciplines'] as $discipline): ?>
                            <div class="border border-gray-200 rounded-lg overflow-hidden">
                                <!-- Cabeçalho da Disciplina -->
                                <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($discipline['subject']) ?></h3>
                                            <p class="text-sm text-gray-600">
                                                <svg class="inline w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                                </svg>
                                                <?= htmlspecialchars(substr($discipline['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr($discipline['end_time'], 0, 5)) ?>
                                            </p>
                                        </div>
                                        <span class="px-3 py-1 bg-primary-100 text-primary-700 text-sm font-semibold rounded">
                                            <?= count($discipline['juries']) ?> sala(s)
                                        </span>
                                    </div>
                                </div>

                                <!-- Salas -->
                                <div class="p-4 bg-white">
                                    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-3">
                                        <?php foreach ($discipline['juries'] as $jury): ?>
                                            <a href="/juries/<?= $jury['id'] ?>" class="block border border-gray-200 rounded-lg p-3 hover:border-primary-400 hover:shadow-md transition-all">
                                                <div class="flex items-start justify-between">
                                                    <div class="flex items-center gap-2">
                                                        <div class="flex items-center justify-center w-8 h-8 bg-primary-600 text-white text-sm font-bold rounded">
                                                            <?= htmlspecialchars($jury['room']) ?>
                                                        </div>
                                                        <div>
                                                            <p class="text-sm font-semibold text-gray-800">Sala <?= htmlspecialchars($jury['room']) ?></p>
                                                            <p class="text-xs text-gray-500"><?= (int)$jury['candidates_quota'] ?> candidatos</p>
                                                        </div>
                                                    </div>
                                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                                    </svg>
                                                </div>
                                                <?php if (!empty($jury['supervisor_name'])): ?>
                                                    <div class="mt-2 pt-2 border-t border-gray-100">
                                                        <p class="text-xs text-amber-600">
                                                            <svg class="inline w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3zM6 8a2 2 0 11-4 0 2 2 0 014 0zM16 18v-3a5.972 5.972 0 00-.75-2.906A3.005 3.005 0 0119 15v3h-3zM4.75 12.094A5.973 5.973 0 004 15v3H1v-3a3 3 0 013.75-2.906z"/>
                                                            </svg>
                                                            <?= htmlspecialchars($jury['supervisor_name']) ?>
                                                        </p>
                                                    </div>
                                                <?php endif; ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
