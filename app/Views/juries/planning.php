<?php
$title = 'Calendário de Vigilância - Extensão da Beira';
$breadcrumbs = [
    ['label' => 'Júris', 'url' => '/juries'],
    ['label' => 'Gestão de Alocações']
];
$helpPage = 'juries-planning'; // Identificador para o sistema de ajuda
?>

<style>
    .allocation-table { border-collapse: collapse; width: 100%; font-size: 0.875rem; }
    .allocation-table th { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: white; font-weight: 600; padding: 12px 8px; text-align: center; border: 1px solid #1e40af; font-size: 0.813rem; text-transform: uppercase; }
    .allocation-table td { border: 1px solid #d1d5db; padding: 8px; vertical-align: middle; }
    .allocation-table tbody tr:hover:not(.subtotal-row):not(.total-row) { background-color: #f9fafb; }
    .group-header { background: linear-gradient(135deg, #e5e7eb 0%, #d1d5db 100%) !important; font-weight: 700; text-align: left; padding: 8px 12px !important; color: #374151; font-size: 0.875rem; letter-spacing: 0.025em; }
    .subtotal-row { background-color: #fef3c7 !important; font-weight: 600; }
    .subtotal-row td { border-top: 2px solid #f59e0b !important; border-bottom: 2px solid #f59e0b !important; }
    .total-row { background-color: #fed7aa !important; font-weight: 700; font-size: 0.938rem; }
    .total-row td { border-top: 3px solid #ea580c !important; border-bottom: 3px solid #ea580c !important; }
    .contact-cell { background-color: #fef3c7; font-weight: 600; }
    .btn-allocate { padding: 4px 10px; font-size: 0.75rem; border-radius: 4px; cursor: pointer; border: none; transition: all 0.2s; font-weight: 500; }
    .btn-auto { background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; }
    .btn-auto:hover { transform: scale(1.05); box-shadow: 0 4px 6px rgba(16, 185, 129, 0.4); }
    .btn-manual { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); color: white; }
    .btn-manual:hover { transform: scale(1.05); box-shadow: 0 4px 6px rgba(59, 130, 246, 0.4); }
    .btn-remove { background: #ef4444; color: white; padding: 2px 6px; font-size: 0.688rem; border-radius: 3px; cursor: pointer; border: none; transition: all 0.2s; }
    .btn-remove:hover { background: #dc2626; transform: scale(1.1); }
    .empty-slot { color: #9ca3af; font-style: italic; text-align: center; padding: 4px 0; }
    
    /* Botões de ações (editar/eliminar) */
    button[onclick*="editJuryInVacancy"], button[onclick*="deleteJury"] {
        transition: all 0.2s;
    }
    button[onclick*="editJuryInVacancy"]:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 4px rgba(37, 99, 235, 0.3);
    }
    button[onclick*="deleteJury"]:hover {
        transform: scale(1.1);
        box-shadow: 0 2px 4px rgba(220, 38, 38, 0.3);
    }
    
    /* Impressão */
    @media print {
        .btn-allocate, .btn-remove, button[onclick*="editJuryInVacancy"], button[onclick*="deleteJury"] { display: none !important; }
        .allocation-table th:last-child, .allocation-table td:last-child { display: none !important; }
        .allocation-table { font-size: 0.75rem; }
        body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    }
    
    /* Animação de spinner */
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    .animate-spin {
        animation: spin 1s linear infinite;
    }
</style>

<div class="space-y-6">
    <div class="flex items-center justify-between">
        <?php include view_path('partials/breadcrumbs.php'); ?>
        <?php include view_path('partials/help_button.php'); ?>
    </div>
    
    <div class="max-w-full mx-auto px-4">
        <!-- Cabeçalho -->
        <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-start">
                <div>
                    <?php 
                    // Título dinâmico baseado na vaga selecionada
                    if (!empty($vacancy)) {
                        $pageTitle = '📅 ' . htmlspecialchars($vacancy['title']);
                    } else {
                        $pageTitle = '📅 Calendário de Vigilância aos Exames de Admissão ' . date('Y');
                    }
                    ?>
                    <h1 class="text-3xl font-bold mb-2"><?= $pageTitle ?></h1>
                    <p class="text-blue-100 text-sm">Extensão da Beira - Comissão de Coordenação dos Exames de Admissão</p>
                </div>
                <div class="flex gap-2">
                    <?php if (!empty($vacancyId)): ?>
                    <a href="/juries/vacancy/<?= $vacancyId ?>/manage" class="px-4 py-2 bg-white text-blue-900 text-sm font-medium rounded hover:bg-blue-50 inline-flex items-center gap-2">
                        ➕ Criar Júris para Esta Vaga
                    </a>
                    <?php else: ?>
                    <a href="/juries/planning-by-vacancy" class="px-4 py-2 bg-white text-blue-900 text-sm font-medium rounded hover:bg-blue-50 inline-flex items-center gap-2">
                        ➕ Criar Júris
                    </a>
                    <?php endif; ?>
                    <button type="button" onclick="window.print()" class="px-4 py-2 bg-white text-blue-900 text-sm font-medium rounded hover:bg-blue-50">
                        🖨️ Imprimir
                    </button>
                </div>
            </div>
        </div>

        <!-- NOVO: Banner de Filtro por Vaga com Dropdown -->
        <?php if (!empty($vacancy)): ?>
        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-blue-900">📋 Mostrando júris de:</p>
                        <p class="text-sm text-blue-800 font-medium"><?= htmlspecialchars($vacancy['title']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select onchange="window.location.href='/juries/planning?vacancy_id='+this.value" class="px-3 py-2 text-sm border border-blue-300 rounded-lg bg-white text-gray-700 hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="current" <?= ($vacancyId ?? 'current') === $vacancyId ? 'selected' : '' ?>>📌 Vaga Atual</option>
                        <option value="all" <?= empty($vacancyId) ? 'selected' : '' ?>>📚 Todas as Vagas</option>
                        <optgroup label="Histórico">
                            <?php foreach ($allVacancies ?? [] as $v): ?>
                                <?php if ($v['status'] !== 'aberta'): ?>
                                <option value="<?= $v['id'] ?>" <?= ($vacancyId ?? 0) == $v['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($v['title']) ?> (<?= ucfirst($v['status']) ?>)
                                </option>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </optgroup>
                    </select>
                </div>
            </div>
        </div>
        <?php else: ?>
        <!-- Sem vaga aberta - Mostrar alerta -->
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-yellow-900">⚠️ Nenhuma vaga aberta no momento</p>
                    <p class="text-xs text-yellow-800 mt-1">Não há vagas abertas. Crie uma nova vaga ou selecione do histórico.</p>
                </div>
                <?php if (!empty($allVacancies)): ?>
                <select onchange="window.location.href='/juries/planning?vacancy_id='+this.value" class="px-3 py-2 text-sm border border-yellow-300 rounded-lg bg-white text-gray-700">
                    <option value="">Ver Histórico</option>
                    <?php foreach ($allVacancies as $v): ?>
                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['title']) ?> (<?= ucfirst($v['status']) ?>)</option>
                    <?php endforeach; ?>
                </select>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Banner de Filtro por Vaga (OLD - Remover depois se não precisar) -->
        <?php if (false && !empty($vacancy)): ?>
        <div class="bg-indigo-50 border-l-4 border-indigo-600 p-4 mb-6 rounded-lg shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-indigo-900">🎯 Filtrando por Vaga</h3>
                        <p class="text-sm text-indigo-800 mt-1">
                            <strong><?= e($vacancy['title']) ?></strong><br>
                            <span class="text-xs">Mostrando apenas júris e vigilantes desta vaga específica</span>
                        </p>
                        <div class="flex gap-2 mt-2">
                            <a href="/juries/vacancy/<?= $vacancy['id'] ?>/manage" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded hover:bg-indigo-700">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                                </svg>
                                Gestão de Júris desta Vaga
                            </a>
                            <a href="/juries/planning" 
                               class="inline-flex items-center gap-1 px-3 py-1.5 border border-indigo-600 text-indigo-700 text-xs font-medium rounded hover:bg-indigo-50">
                                Remover Filtro (Ver Todos)
                            </a>
                        </div>
                    </div>
                </div>
                <button onclick="this.closest('div[class*=bg-indigo-50]').style.display='none'" 
                        class="text-indigo-600 hover:text-indigo-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
        <?php endif; ?>

        <!-- Estatísticas Rápidas -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-blue-600">
                <div class="text-xs text-gray-500 uppercase font-semibold">Total Júris</div>
                <div class="text-2xl font-bold text-gray-900"><?= $stats['total_juries'] ?? 0 ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-green-600">
                <div class="text-xs text-gray-500 uppercase font-semibold">Vigilantes Alocados</div>
                <div class="text-2xl font-bold text-green-600"><?= $stats['total_allocated'] ?? 0 ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-orange-600">
                <div class="text-xs text-gray-500 uppercase font-semibold">Vagas Livres</div>
                <div class="text-2xl font-bold text-orange-600"><?= ($stats['slots_available'] ?? 0) - ($stats['total_allocated'] ?? 0) ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-red-600">
                <div class="text-xs text-gray-500 uppercase font-semibold">Sem Supervisor</div>
                <div class="text-2xl font-bold text-red-600"><?= $stats['juries_without_supervisor'] ?? 0 ?></div>
            </div>
            <div class="bg-white p-4 rounded-lg shadow border-l-4 border-indigo-600">
                <div class="text-xs text-gray-500 uppercase font-semibold">Total Candidatos</div>
                <div class="text-2xl font-bold text-indigo-600"><?= $stats['total_candidates'] ?? 0 ?></div>
            </div>
        </div>

        <!-- Tabela de Alocações -->
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="allocation-table">
                    <thead>
                        <tr>
                            <th style="width: 100px;">DIA</th>
                            <th style="width: 80px;">HORA</th>
                            <th style="width: 180px;">EXAME</th>
                            <th style="width: 200px;">SALAS</th>
                            <th style="width: 80px;">Nº Cand</th>
                            <th style="min-width: 300px;">VIGILANTE</th>
                            <th style="width: 60px;">Nº Vig</th>
                            <th style="width: 100px;">AÇÕES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($groupedJuries)): ?>
                        <tr>
                            <td colspan="8" class="text-center text-gray-500 py-8">
                                <div class="flex flex-col items-center gap-3">
                                    <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="text-lg font-medium">Nenhum júri criado ainda</p>
                                    <?php if (!empty($vacancyId)): ?>
                                    <a href="/juries/vacancy/<?= $vacancyId ?>/manage" class="btn-allocate btn-manual inline-block">
                                        ➕ Criar Júris para Esta Vaga
                                    </a>
                                    <?php else: ?>
                                    <a href="/juries/planning-by-vacancy" class="btn-allocate btn-manual inline-block">
                                        ➕ Criar Júris Agora
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php else:
                            // Agrupar júris por local dentro de cada exame
                            $lastSupervisor = null;
                            $totalCandidatos = 0;
                            $totalVigilantes = 0;
                            
                            foreach ($groupedJuries as $groupIndex => $group):
                                // Resetar supervisor para cada grupo de exame
                                $lastSupervisor = null;
                                
                                // Reagrupar júris por local
                                $juriesByLocation = [];
                                foreach ($group['juries'] as $jury) {
                                    $location = !empty($jury['location']) ? $jury['location'] : 'Local não especificado';
                                    if (!isset($juriesByLocation[$location])) {
                                        $juriesByLocation[$location] = [];
                                    }
                                    $juriesByLocation[$location][] = $jury;
                                }
                                
                                // Contar total de júris para rowspan
                                $totalJuriesInGroup = count($group['juries']);
                                $juryCounter = 0;
                                $examCandidates = 0;
                                $examVigilantes = 0;
                                
                                // Renderizar cada local
                                foreach ($juriesByLocation as $location => $juries):
                                    // Cabeçalho do local
                                    ?>
                                    <tr>
                                        <td colspan="8" class="group-header">📍 <?= strtoupper(htmlspecialchars($location)) ?></td>
                                    </tr>
                                    <?php
                                    
                                    // Renderizar cada júri do local
                                    foreach ($juries as $juryIndex => $jury):
                                        $isFirstJury = ($juryCounter === 0);
                                        $vigilantesCount = count($jury['vigilantes'] ?? []);
                                        $examCandidates += $jury['candidates_quota'];
                                        $examVigilantes += $vigilantesCount;
                                        $totalCandidatos += $jury['candidates_quota'];
                                        $totalVigilantes += $vigilantesCount;
                                        
                                        // Atualizar supervisor apenas se supervisor_id não for null
                                        if (!empty($jury['supervisor_id']) && !empty($jury['supervisor_name'])) {
                                            $lastSupervisor = $jury['supervisor_name'];
                                        }
                                        ?>
                                        <tr data-jury-id="<?= $jury['id'] ?>">
                                            <?php if ($isFirstJury): 
                                                // Dias da semana em Português de Portugal
                                                $diasSemana = [
                                                    'Monday' => 'Segunda-feira',
                                                    'Tuesday' => 'Terça-feira',
                                                    'Wednesday' => 'Quarta-feira',
                                                    'Thursday' => 'Quinta-feira',
                                                    'Friday' => 'Sexta-feira',
                                                    'Saturday' => 'Sábado',
                                                    'Sunday' => 'Domingo'
                                                ];
                                                $diaIngles = date('l', strtotime($jury['exam_date']));
                                                $diaPortugues = $diasSemana[$diaIngles] ?? $diaIngles;
                                            ?>
                                            <td rowspan="<?= $totalJuriesInGroup ?>" style="text-align: center; font-weight: 600; vertical-align: middle; border-right: 2px solid #d1d5db;">
                                                <?= date('d/m/Y', strtotime($jury['exam_date'])) ?><br>
                                                <span style="font-size: 0.75rem; color: #6b7280;">(<?= $diaPortugues ?>)</span>
                                            </td>
                                            <td rowspan="<?= $totalJuriesInGroup ?>" style="text-align: center; font-weight: 600; vertical-align: middle; border-right: 2px solid #d1d5db;">
                                                <?= date('H:i', strtotime($jury['start_time'])) ?>
                                            </td>
                                            <td rowspan="<?= $totalJuriesInGroup ?>" style="text-align: center; font-weight: 600; vertical-align: middle; background-color: #f3f4f6; border-right: 2px solid #d1d5db;">
                                                <?= e($group['subject']) ?>
                                            </td>
                                            <?php endif; ?>
                                            
                                            <td style="padding: 8px 12px;">
                                                <div>
                                                    <div class="font-semibold text-gray-900" style="font-size: 0.875rem;">
                                                        <?= e($jury['room']) ?>
                                                    </div>
                                                    <?php if (!empty($jury['room_capacity'])): ?>
                                                    <div class="text-xs text-gray-600 mt-1">
                                                        <span class="inline-flex items-center">
                                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                                <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                                            </svg>
                                                            Capacidade: <?= $jury['room_capacity'] ?>
                                                        </span>
                                                    </div>
                                                    <?php endif; ?>
                                                    <?php if (!empty($jury['notes'])): ?>
                                                    <div class="text-xs text-blue-600 mt-1 italic">
                                                        <span class="inline-flex items-start">
                                                            <svg class="w-3 h-3 mr-1 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                                            </svg>
                                                            <span class="flex-1"><?= e($jury['notes']) ?></span>
                                                        </span>
                                                    </div>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td style="text-align: center; font-weight: 500;"><?= number_format($jury['candidates_quota'], 0) ?></td>
                                            <td style="padding: 4px 8px;">
                                                <?php if (!empty($jury['vigilantes'])): ?>
                                                    <?php foreach ($jury['vigilantes'] as $v): ?>
                                                        <div class="flex items-center justify-between mb-1 bg-blue-50 px-2 py-1 rounded" style="font-size: 0.813rem;">
                                                            <span><?= e($v['name']) ?></span>
                                                            <button onclick="removeVigilante(<?= $jury['id'] ?>, <?= $v['id'] ?>)" class="btn-remove" title="Remover vigilante">✕</button>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <div class="empty-slot" style="text-align: center; font-style: italic; color: #9ca3af;">Sem vigilante</div>
                                                <?php endif; ?>
                                                <?php if ($vigilantesCount < 2): ?>
                                                <div style="display: flex; gap: 4px; margin-top: 4px; justify-content: center;">
                                                    <button onclick="openManualModal(<?= $jury['id'] ?>)" class="btn-allocate btn-manual">✋ Manual</button>
                                                </div>
                                                <?php endif; ?>
                                            </td>
                                            <td style="text-align: center; font-weight: 600;"><?= $vigilantesCount ?></td>
                                            <td style="text-align: center; padding: 8px;">
                                                <div class="flex items-center justify-center gap-1">
                                                    <button onclick="editJuryInVacancy(<?= $jury['vacancy_id'] ?? 0 ?>, <?= $jury['id'] ?>)" 
                                                            class="p-1.5 text-blue-600 hover:text-blue-800 hover:bg-blue-50 rounded transition-colors" 
                                                            title="Editar júri">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                                        </svg>
                                                    </button>
                                                    <button onclick="deleteJury(<?= $jury['id'] ?>, '<?= e($jury['room']) ?>')" 
                                                            class="p-1.5 text-red-600 hover:text-red-800 hover:bg-red-50 rounded transition-colors" 
                                                            title="Eliminar júri">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php
                                        $juryCounter++;
                                    endforeach;
                                endforeach;
                                
                                // Subtotal do exame
                                // Encontrar ID do júri representativo para alocação de supervisor
                                $firstJuryOfExam = $group['juries'][0] ?? null;
                                $supervisorJuryId = $firstJuryOfExam['id'] ?? 0;
                                $supervisorId = $firstJuryOfExam['supervisor_id'] ?? null;
                                ?>
                                <tr class="subtotal-row">
                                    <td colspan="4" style="text-align: right; padding-right: 16px; font-weight: 600;">Subtotal</td>
                                    <td style="text-align: center; font-weight: 600;"><?= number_format($examCandidates, 0) ?></td>
                                    <td style="padding: 8px; font-weight: 600;">
                                        <div style="display: flex; flex-direction: column; gap: 6px; align-items: center;">
                                            <span style="color: #6b7280; font-style: italic; font-size: 0.875rem;">
                                                <?php if ($lastSupervisor): ?>
                                                    Supervisor: <?= e($lastSupervisor) ?>
                                                <?php else: ?>
                                                    Sem supervisor
                                                <?php endif; ?>
                                            </span>
                                            <?php if (!$lastSupervisor && $supervisorJuryId > 0): ?>
                                                <button onclick="openSupervisorModal(<?= $supervisorJuryId ?>)" 
                                                        class="px-3 py-1 bg-purple-600 text-white text-xs rounded hover:bg-purple-700 transition-colors"
                                                        title="Alocar supervisor">
                                                    👔 Alocar Supervisor
                                                </button>
                                            <?php elseif ($lastSupervisor && $supervisorId && $supervisorJuryId > 0): ?>
                                                <button onclick="removeSupervisor(<?= $supervisorJuryId ?>)" 
                                                        class="px-2 py-1 bg-red-600 text-white text-xs rounded hover:bg-red-700 transition-colors"
                                                        title="Remover supervisor">
                                                    ✕ Remover
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td style="text-align: center; font-weight: 700;"><?= $examVigilantes ?></td>
                                    <td></td>
                                </tr>
                                <?php
                            endforeach;
                            
                            // Total Geral
                            if ($totalCandidatos > 0):
                                ?>
                                <tr class="total-row">
                                    <td colspan="4" style="text-align: right; padding-right: 16px; font-weight: 700;">TOTAL</td>
                                    <td style="text-align: center; font-weight: 700;"><?= number_format($totalCandidatos, 0) ?></td>
                                    <td></td>
                                    <td style="text-align: center; font-weight: 700;"><?= $totalVigilantes ?></td>
                                    <td></td>
                                </tr>
                                <?php
                            endif;
                        endif;
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Legenda -->
        <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
            <h3 class="font-semibold text-blue-900 mb-3">📋 Legenda e Ações Disponíveis</h3>
            <div class="grid md:grid-cols-3 gap-4 text-sm text-blue-800">
                <div>
                    <p class="font-semibold mb-2 text-blue-900">Vigilantes:</p>
                    <p><strong>✋ Manual:</strong> Alocar vigilante manualmente</p>
                    <p><strong>✕:</strong> Remover vigilante alocado</p>
                    <p class="mt-2"><strong>Supervisor:</strong></p>
                    <p><strong>👔 Alocar:</strong> Definir supervisor do exame (no Subtotal)</p>
                    <p><strong>✕ Remover:</strong> Remover supervisor alocado</p>
                </div>
                <div>
                    <p class="font-semibold mb-2 text-blue-900">Júris/Salas:</p>
                    <p><strong>✏️ Editar:</strong> Editar disciplina, data, horário, sala e candidatos</p>
                    <p><strong>🗑️ Eliminar:</strong> Remover júri permanentemente</p>
                </div>
                <div>
                    <p class="font-semibold mb-2 text-blue-900">Totalizadores:</p>
                    <p><strong>Subtotal:</strong> Total de candidatos por exame (com supervisor)</p>
                    <p><strong>TOTAL:</strong> Total geral de candidatos e vigilantes</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Alocação Manual -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-manual-allocation">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-2xl mx-4 rounded-lg shadow-lg p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">✋ Selecionar Vigilante</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div id="manual-allocation-content">Carregando...</div>
    </div>
</div>

<!-- Modal: Editar Júri -->
<div id="modal-edit-jury" class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white z-10">
            <h2 class="text-xl font-bold text-gray-900">✏️ Editar Júri</h2>
            <button type="button" onclick="closeEditJuryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="form-edit-jury" onsubmit="saveJuryChanges(event)" class="p-6">
            <input type="hidden" id="edit_jury_id" name="jury_id">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">
            
            <!-- Informações Básicas -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase">📚 Disciplina</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Disciplina *</label>
                    <input type="text" id="edit_subject" name="subject" 
                           class="w-full rounded border border-gray-300 px-3 py-2" required>
                </div>
            </div>
            
            <!-- Data e Horário -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase">📅 Data e Horário</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data *</label>
                        <input type="date" id="edit_exam_date" name="exam_date" 
                               class="w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Início *</label>
                        <input type="time" id="edit_start_time" name="start_time" 
                               class="w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fim *</label>
                        <input type="time" id="edit_end_time" name="end_time" 
                               class="w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>
                </div>
            </div>
            
            <!-- Local e Sala -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase">📍 Local e Sala</h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Local *</label>
                        <select id="edit_location_id" name="location_id" onchange="loadEditRoomsByLocation()" 
                                class="w-full rounded border border-gray-300 px-3 py-2" required>
                            <option value="">Carregando locais...</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sala *</label>
                        <select id="edit_room_id" name="room_id" 
                                class="w-full rounded border border-gray-300 px-3 py-2" required>
                            <option value="">Selecione um local primeiro</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Candidatos -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase">👥 Candidatos</h3>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nº Candidatos *</label>
                    <input type="number" id="edit_candidates_quota" name="candidates_quota" 
                           min="1" max="300"
                           class="w-full rounded border border-gray-300 px-3 py-2" required>
                    <p id="edit-vigilantes-needed" class="text-xs text-blue-600 mt-1 font-medium"></p>
                </div>
            </div>
            
            <!-- Observações -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                <textarea id="edit_notes" name="notes" rows="3"
                          class="w-full rounded border border-gray-300 px-3 py-2"
                          placeholder="Observações adicionais sobre este júri..."></textarea>
            </div>
            
            <!-- Aviso de Impacto -->
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-yellow-900">⚠️ Atenção ao Editar</h4>
                        <ul class="text-xs text-yellow-800 mt-1 space-y-1">
                            <li>• Alterar data/horário pode criar conflitos com outros júris</li>
                            <li>• Alterar nº de candidatos recalcula vigilantes necessários</li>
                            <li>• Vigilantes já alocados serão mantidos (se possível)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <button type="button" onclick="closeEditJuryModal()" class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
const csrfToken = '<?= csrf_token() ?>';

/**
 * Escape HTML para prevenir XSS em JavaScript
 */
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function editJury(juryId) {
    // Redirecionar para a página de gestão de júris onde a edição está implementada
    const currentUrl = new URL(window.location.href);
    const vacancyId = currentUrl.searchParams.get('vacancy_id');
    
    if (vacancyId) {
        window.location.href = `/juries/vacancy/${vacancyId}/manage`;
    } else {
        alert('ℹ️ Para editar júris, acesse via "Planeamento por Vaga"');
    }
}

// Variáveis globais para o modal de edição
let editMasterData = {
    locations: [],
    rooms: []
};

async function editJuryInVacancy(vacancyId, juryId) {
    if (!vacancyId || vacancyId == 0) {
        alert('⚠️ Este júri não está associado a uma vaga específica.\n\nPara editar, acesse via:\n"Planeamento por Vaga" → Selecione a vaga → Gerir Júris');
        return;
    }
    
    // Abrir modal de edição
    await openEditJuryModal(juryId);
}

async function openEditJuryModal(juryId) {
    const modal = document.getElementById('modal-edit-jury');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    try {
        // Carregar dados mestre (locais e salas) se ainda não carregados
        if (editMasterData.locations.length === 0) {
            const masterResponse = await fetch('/api/master-data/locations-rooms');
            const masterResult = await masterResponse.json();
            
            if (masterResult.success) {
                editMasterData.locations = masterResult.locations;
                editMasterData.rooms = masterResult.rooms;
            }
        }
        
        // Carregar dados do júri
        const juryResponse = await fetch(`/juries/${juryId}/details`);
        const juryResult = await juryResponse.json();
        
        if (!juryResult.success) {
            throw new Error(juryResult.message || 'Erro ao carregar júri');
        }
        
        const jury = juryResult.jury;
        console.log('Dados do júri:', jury);
        
        // Preencher formulário
        document.getElementById('edit_jury_id').value = jury.id;
        document.getElementById('edit_subject').value = jury.subject || '';
        document.getElementById('edit_exam_date').value = jury.exam_date || '';
        document.getElementById('edit_start_time').value = jury.start_time || '';
        document.getElementById('edit_end_time').value = jury.end_time || '';
        document.getElementById('edit_candidates_quota').value = jury.candidates_quota || '';
        document.getElementById('edit_notes').value = jury.notes || '';
        
        // Calcular vigilantes necessários
        const vigilantesNeeded = Math.ceil((jury.candidates_quota || 0) / 30);
        document.getElementById('edit-vigilantes-needed').textContent = 
            `Vigilantes necessários: ${vigilantesNeeded} (baseado em 1 vigilante / 30 candidatos)`;
        
        // Preencher dropdown de locais
        const locationSelect = document.getElementById('edit_location_id');
        locationSelect.innerHTML = '<option value="">Selecione um local...</option>';
        
        editMasterData.locations.forEach(loc => {
            const option = document.createElement('option');
            option.value = loc.id;
            option.textContent = loc.name;
            if (loc.id == jury.location_id) {
                option.selected = true;
            }
            locationSelect.appendChild(option);
        });
        
        // Carregar salas do local
        await loadEditRoomsByLocation();
        
        // Selecionar sala atual
        if (jury.room_id) {
            document.getElementById('edit_room_id').value = jury.room_id;
        }
        
    } catch (error) {
        console.error('Erro ao carregar dados do júri:', error);
        alert('❌ Erro ao carregar dados do júri: ' + error.message);
        closeEditJuryModal();
    }
}

async function loadEditRoomsByLocation() {
    const locationId = document.getElementById('edit_location_id').value;
    const roomSelect = document.getElementById('edit_room_id');
    
    roomSelect.innerHTML = '<option value="">Selecione uma sala...</option>';
    
    if (!locationId) {
        return;
    }
    
    // Filtrar salas do local selecionado
    const locationRooms = editMasterData.rooms.filter(r => r.location_id == locationId);
    
    if (locationRooms.length === 0) {
        roomSelect.innerHTML = '<option value="">Nenhuma sala cadastrada</option>';
        return;
    }
    
    locationRooms.forEach(room => {
        const option = document.createElement('option');
        option.value = room.id;
        
        // Formato: Nome (Building | Piso) - Cap: XX
        let displayText = room.name || room.code;
        
        if (room.building && room.floor) {
            displayText += ` (${room.building} | ${room.floor})`;
        } else if (room.building) {
            displayText += ` (${room.building})`;
        } else if (room.floor) {
            displayText += ` (${room.floor})`;
        }
        
        displayText += ` - Cap: ${room.capacity}`;
        
        option.textContent = displayText;
        roomSelect.appendChild(option);
    });
}

function closeEditJuryModal() {
    const modal = document.getElementById('modal-edit-jury');
    modal.classList.add('hidden');
    modal.classList.remove('flex');
}

async function saveJuryChanges(event) {
    event.preventDefault();
    
    const form = event.target;
    const juryId = document.getElementById('edit_jury_id').value;
    const formData = new FormData(form);
    
    // Desabilitar botão de submit
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalHTML = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<svg class="animate-spin w-4 h-4 mr-2 inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Salvando...';
    
    try {
        const response = await fetch(`/juries/${juryId}/update`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        });
        
        console.log('Resposta:', response.status);
        
        if (response.ok) {
            const result = await response.json();
            console.log('Resultado:', result);
            
            // Feedback de sucesso
            const successDiv = document.createElement('div');
            successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
            successDiv.innerHTML = `
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                ${result.message || 'Júri atualizado com sucesso!'}
            `;
            document.body.appendChild(successDiv);
            
            // Fechar modal
            closeEditJuryModal();
            
            // Recarregar página após 1s
            setTimeout(() => {
                location.reload();
            }, 1000);
            
        } else {
            const error = await response.json();
            alert('❌ ' + (error.message || 'Erro ao atualizar júri'));
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalHTML;
        }
    } catch (error) {
        console.error('Erro ao salvar:', error);
        alert('❌ Erro de conexão ao salvar alterações');
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalHTML;
    }
}

async function deleteJury(juryId, roomName) {
    if (!confirm(`⚠️ ATENÇÃO: Eliminar júri "${roomName}"?\n\n⚠️ Esta ação irá:\n- Remover o júri permanentemente\n- Desalocar todos os vigilantes associados\n- Esta ação NÃO PODE ser desfeita!\n\nTem certeza?`)) {
        return;
    }
    
    try {
        const response = await fetch(`/juries/${juryId}/delete`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ 
                csrf: csrfToken 
            })
        });
        
        console.log('Resposta recebida:', response.status, response.statusText);
        
        if (response.ok) {
            const contentType = response.headers.get('content-type');
            console.log('Content-Type:', contentType);
            
            if (contentType && contentType.includes('application/json')) {
                const result = await response.json();
                console.log('Resultado:', result);
                
                // Feedback visual
                const row = document.querySelector(`tr[data-jury-id="${juryId}"]`);
                if (row) {
                    row.style.backgroundColor = '#fee2e2'; // Vermelho claro
                    row.style.transition = 'background-color 0.3s';
                }
                
                // Mostrar mensagem de sucesso
                const successDiv = document.createElement('div');
                successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center gap-2';
                successDiv.innerHTML = `
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    ${result.message || 'Júri eliminado com sucesso!'}
                `;
                document.body.appendChild(successDiv);
                
                // Recarregar após 1s
                setTimeout(() => {
                    location.reload();
                }, 1000);
            } else {
                const text = await response.text();
                console.error('Resposta não é JSON:', text.substring(0, 500));
                alert('❌ Erro: resposta inválida do servidor (não-JSON). Verifique o console.');
            }
            
        } else {
            console.error('Resposta com erro:', response.status);
            try {
                const error = await response.json();
                alert('❌ ' + (error.message || 'Erro ao eliminar júri.'));
            } catch (e) {
                const text = await response.text();
                console.error('Erro ao parsear resposta:', text.substring(0, 500));
                alert('❌ Erro ao eliminar júri. Status: ' + response.status);
            }
        }
    } catch (error) {
        console.error('Erro detalhado ao eliminar júri:', error);
        alert('❌ Erro de conexão: ' + error.message + '\nVerifique o console do navegador (F12).');
    }
}

async function removeVigilante(juryId, vigilanteId) {
    if (!confirm('⚠️ Remover este vigilante?\n\nEsta ação não pode ser desfeita.')) return;
    
    try {
        const response = await fetch(`/juries/${juryId}/unassign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ 
                vigilante_id: vigilanteId, 
                csrf: csrfToken 
            })
        });
        
        if (response.ok) {
            const result = await response.json();
            
            // Mostrar feedback visual temporário
            const row = document.querySelector(`tr[data-jury-id="${juryId}"]`);
            if (row) {
                row.style.backgroundColor = '#dcfce7'; // Verde claro
                row.style.transition = 'background-color 0.3s';
            }
            
            // Recarregar após 500ms
            setTimeout(() => {
                location.reload();
            }, 500);
            
        } else {
            const error = await response.json();
            alert('❌ ' + (error.message || 'Erro ao remover vigilante.'));
        }
    } catch (error) {
        console.error('Erro ao remover vigilante:', error);
        alert('❌ Erro de conexão ao remover vigilante.');
    }
}

async function openSupervisorModal(juryId) {
    const modal = document.getElementById('modal-supervisor-allocation');
    if (!modal) {
        // Criar modal dinamicamente se não existir
        createSupervisorModal();
    }
    
    const content = document.getElementById('supervisor-allocation-content');
    const modalElement = document.getElementById('modal-supervisor-allocation');
    
    modalElement.classList.remove('hidden');
    modalElement.classList.add('flex');
    
    content.innerHTML = '<p class="text-center py-4">Carregando candidatos aprovados...</p>';
    
    try {
        const response = await fetch(`/api/allocation/eligible-vigilantes/${juryId}`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        console.log('Dados recebidos:', data); // Debug
        
        if (!data.success) {
            content.innerHTML = '<p class="text-center text-red-500 py-4">Erro: ' + (data.message || 'Erro desconhecido') + '</p>';
            return;
        }
        
        if (!data.vigilantes || data.vigilantes.length === 0) {
            content.innerHTML = `
                <div class="text-center py-8">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p class="text-gray-500 font-semibold">Nenhum candidato aprovado disponível</p>
                    <p class="text-gray-400 text-sm mt-2">Aprove candidaturas primeiro em "Candidaturas de Vigilantes"</p>
                </div>
            `;
            return;
        }
        
        let html = '<div class="space-y-2 max-h-96 overflow-y-auto">';
        data.vigilantes.forEach(vigilante => {
            const isEligible = vigilante.supervisor_eligible == 1;
            const borderClass = isEligible ? 'border-2 border-purple-300 bg-purple-50' : 'border border-gray-200';
            const buttonClass = isEligible ? 'bg-purple-600 hover:bg-purple-700 shadow-md' : 'bg-gray-600 hover:bg-gray-700';
            
            html += `
                <div class="flex items-center justify-between p-3 ${borderClass} rounded-lg hover:shadow-sm transition-all">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <div class="font-medium text-gray-900">${escapeHtml(vigilante.name || vigilante.vigilante_name)}</div>
                            ${isEligible ? '<span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-600 text-white text-xs font-semibold rounded"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>ELEGÍVEL</span>' : ''}
                        </div>
                        <div class="text-xs text-gray-500">${escapeHtml(vigilante.email || vigilante.vigilante_email || 'Email não disponível')}</div>
                    </div>
                    <button onclick="allocateSupervisor(${juryId}, ${vigilante.id || vigilante.vigilante_id})" 
                            class="px-3 py-1.5 ${buttonClass} text-white text-sm font-medium rounded transition-colors">
                        👔 Definir como Supervisor
                    </button>
                </div>
            `;
        });
        html += '</div>';
        
        content.innerHTML = html;
        
    } catch (error) {
        console.error('Erro completo ao carregar candidatos:', error);
        content.innerHTML = `
            <div class="text-center py-8">
                <svg class="w-16 h-16 mx-auto text-red-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-600 font-semibold">Erro ao carregar candidatos</p>
                <p class="text-gray-500 text-sm mt-2">${error.message}</p>
            </div>
        `;
    }
}

async function allocateSupervisor(juryId, supervisorId) {
    console.log('Alocando supervisor:', { juryId, supervisorId }); // Debug
    
    try {
        const response = await fetch(`/juries/${juryId}/set-supervisor`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ 
                supervisor_id: supervisorId, 
                csrf: csrfToken 
            })
        });
        
        console.log('Response status:', response.status); // Debug
        console.log('Response headers:', response.headers); // Debug
        
        // Tentar ler a resposta como texto primeiro
        const textResponse = await response.text();
        console.log('Response text:', textResponse); // Debug
        
        // Tentar parsear como JSON
        let result;
        try {
            result = JSON.parse(textResponse);
        } catch (e) {
            console.error('Erro ao parsear JSON:', e);
            alert('❌ Resposta inválida do servidor.\n\n' + textResponse.substring(0, 200));
            return;
        }
        
        if (response.ok) {
            closeSupervisorModal();
            alert('✅ ' + result.message);
            setTimeout(() => location.reload(), 500);
        } else {
            alert('❌ ' + (result.message || 'Erro ao alocar supervisor.'));
        }
    } catch (error) {
        console.error('Erro completo ao alocar supervisor:', error);
        alert('❌ Erro de conexão: ' + error.message);
    }
}

async function removeSupervisor(juryId) {
    if (!confirm('⚠️ Remover supervisor?\n\nEsta ação removerá o supervisor de TODOS os júris deste exame.\n\nDeseja continuar?')) return;
    
    try {
        const response = await fetch(`/juries/${juryId}/set-supervisor`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ 
                supervisor_id: 0, 
                csrf: csrfToken 
            })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            alert('✅ ' + result.message);
            setTimeout(() => location.reload(), 500);
        } else {
            alert('❌ ' + (result.message || 'Erro ao remover supervisor.'));
        }
    } catch (error) {
        console.error('Erro ao remover supervisor:', error);
        alert('❌ Erro de conexão ao remover supervisor.');
    }
}

function createSupervisorModal() {
    const modalHTML = `
        <div id="modal-supervisor-allocation" class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center">
            <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-hidden">
                <div class="flex items-center justify-between p-6 border-b">
                    <h2 class="text-xl font-bold text-gray-900">👔 Alocar Supervisor</h2>
                    <button type="button" onclick="closeSupervisorModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div id="supervisor-allocation-content" class="p-6">
                    <!-- Conteúdo carregado dinamicamente -->
                </div>
            </div>
        </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);
}

function closeSupervisorModal() {
    const modal = document.getElementById('modal-supervisor-allocation');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

async function openManualModal(juryId) {
    const modal = document.getElementById('modal-manual-allocation');
    const content = document.getElementById('manual-allocation-content');
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    
    content.innerHTML = '<p class="text-center py-4">Carregando vigilantes disponíveis...</p>';
    
    try {
        const response = await fetch(`/juries/${juryId}/eligible-vigilantes`);
        
        if (!response.ok) {
            const errorText = await response.text();
            console.error('Erro HTTP:', response.status, errorText);
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Resposta não é JSON:', text.substring(0, 500));
            throw new Error('Servidor retornou resposta inválida (não-JSON)');
        }
        
        const data = await response.json();
        console.log('Dados recebidos:', data);
        const vigilantes = data.vigilantes || data;
        
        if (!vigilantes || vigilantes.length === 0) {
            content.innerHTML = `
                <div class="text-center py-8">
                    <svg class="w-16 h-16 text-gray-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <p class="text-gray-500 text-lg font-medium">Nenhum vigilante disponível</p>
                    <p class="text-gray-400 text-sm mt-2">Todos os vigilantes já estão alocados ou têm conflitos de horário.</p>
                </div>
            `;
            return;
        }
        
        let html = '<div class="space-y-2 max-h-96 overflow-y-auto">';
        vigilantes.forEach(v => {
            const workload = v.workload_score || 0;
            const workloadClass = workload === 0 ? 'bg-green-100 text-green-800' : (workload <= 2 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800');
            
            html += `
                <div class="border rounded-lg p-4 hover:bg-blue-50 cursor-pointer transition"
                     onclick="allocateVigilanteManual(${juryId}, ${v.id})">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <p class="font-semibold text-gray-900">${escapeHtml(v.name)}</p>
                            <p class="text-xs text-gray-500">${escapeHtml(v.email || '')}</p>
                            <div class="flex gap-2 mt-2 text-xs">
                                <span class="px-2 py-1 ${workloadClass} rounded">Carga: ${workload}</span>
                                <span class="px-2 py-1 bg-gray-100 text-gray-700 rounded">Vigílias: ${v.vigilance_count || 0}</span>
                            </div>
                        </div>
                        <button class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700">
                            Alocar
                        </button>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        
        content.innerHTML = html;
    } catch (error) {
        console.error('Erro detalhado ao carregar vigilantes:', error);
        const errorMsg = error.message || 'Erro desconhecido. Verifique o console do navegador.';
        content.innerHTML = `
            <div class="text-center py-8">
                <svg class="w-16 h-16 text-red-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-red-500 text-lg font-medium">Erro ao carregar vigilantes</p>
                <p class="text-gray-500 text-sm mt-2">${errorMsg.replace(/</g, '&lt;').replace(/>/g, '&gt;')}</p>
                <button onclick="openManualModal(${juryId})" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                    🔄 Tentar Novamente
                </button>
            </div>
        `;
    }
}

async function allocateVigilanteManual(juryId, vigilanteId) {
    try {
        const response = await fetch(`/juries/${juryId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: new URLSearchParams({ 
                vigilante_id: vigilanteId, 
                csrf: csrfToken 
            })
        });
        
        if (response.ok) {
            const result = await response.json();
            
            // Fechar modal
            const modal = document.getElementById('modal-manual-allocation');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            
            // Feedback visual
            const row = document.querySelector(`tr[data-jury-id="${juryId}"]`);
            if (row) {
                row.style.backgroundColor = '#dcfce7'; // Verde claro
                row.style.transition = 'background-color 0.3s';
            }
            
            // Mostrar mensagem de sucesso
            const successDiv = document.createElement('div');
            successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
            successDiv.innerHTML = '✅ ' + (result.message || 'Vigilante alocado com sucesso!');
            document.body.appendChild(successDiv);
            
            // Recarregar após 1s
            setTimeout(() => {
                location.reload();
            }, 1000);
            
        } else {
            const error = await response.json();
            alert('❌ ' + (error.message || 'Erro ao alocar vigilante.'));
        }
    } catch (error) {
        console.error('Erro ao alocar vigilante:', error);
        alert('❌ Erro de conexão ao alocar vigilante.');
    }
}

function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
}

// Event listeners para fechar modal
document.addEventListener('DOMContentLoaded', function() {
    const manualModal = document.getElementById('modal-manual-allocation');
    const closeButtons = manualModal?.querySelectorAll('.modal-close');
    const backdrop = manualModal?.querySelector('.modal-backdrop');
    
    closeButtons?.forEach(btn => {
        btn.addEventListener('click', () => {
            manualModal.classList.add('hidden');
            manualModal.classList.remove('flex');
        });
    });
    
    backdrop?.addEventListener('click', () => {
        manualModal.classList.add('hidden');
        manualModal.classList.remove('flex');
    });
});
</script>
