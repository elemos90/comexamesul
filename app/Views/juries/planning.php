<?php
$title = 'Calendário de Vigilância - Extensão da Beira';
$breadcrumbs = [
    ['label' => 'Júris', 'url' => url('/juries')],
    ['label' => 'Gestão de Alocações']
];
$helpPage = 'juries-planning'; // Identificador para o sistema de ajuda
?>

<style>
    .allocation-table {
        border-collapse: collapse;
        width: 100%;
        font-size: 0.75rem;
    }

    .allocation-table th {
        background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
        color: white;
        font-weight: 500;
        padding: 8px 6px;
        text-align: center;
        border: 1px solid #1e40af;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .allocation-table td {
        border: 1px solid #e5e7eb;
        padding: 5px 6px;
        vertical-align: middle;
        font-size: 0.73rem;
    }

    .allocation-table tbody tr:hover:not(.subtotal-row):not(.total-row) {
        background-color: #f0f9ff;
    }

    .group-header {
        background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%) !important;
        font-weight: 600;
        text-align: left;
        padding: 6px 10px !important;
        color: #1e40af;
        font-size: 0.75rem;
        letter-spacing: 0.02em;
    }

    .subtotal-row {
        background-color: #fef9c3 !important;
        font-weight: 600;
        font-size: 0.72rem;
    }

    .subtotal-row td {
        border-top: 2px solid #facc15 !important;
        border-bottom: 2px solid #facc15 !important;
        padding: 6px !important;
    }

    .total-row {
        background-color: #fed7aa !important;
        font-weight: 700;
        font-size: 0.8rem;
    }

    .total-row td {
        border-top: 2px solid #f97316 !important;
        border-bottom: 2px solid #f97316 !important;
        padding: 8px !important;
    }

    .contact-cell {
        background-color: #fef9c3;
        font-weight: 500;
        font-size: 0.7rem;
    }

    .btn-allocate {
        padding: 3px 8px;
        font-size: 0.65rem;
        border-radius: 3px;
        cursor: pointer;
        border: none;
        transition: all 0.15s;
        font-weight: 500;
    }

    .btn-auto {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
    }

    .btn-auto:hover {
        transform: scale(1.03);
        box-shadow: 0 2px 4px rgba(16, 185, 129, 0.3);
    }

    .btn-manual {
        background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
        color: white;
    }

    .btn-manual:hover {
        transform: scale(1.03);
        box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
    }

    .btn-remove {
        background: #ef4444;
        color: white;
        padding: 1px 5px;
        font-size: 0.6rem;
        border-radius: 2px;
        cursor: pointer;
        border: none;
        transition: all 0.15s;
    }

    .btn-remove:hover {
        background: #dc2626;
        transform: scale(1.05);
    }

    .empty-slot {
        color: #9ca3af;
        font-style: italic;
        text-align: center;
        padding: 2px 0;
        font-size: 0.65rem;
    }

    /* Botões de ações (editar/eliminar) */
    button[onclick*="editJuryInVacancy"],
    button[onclick*="deleteJury"] {
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

        .btn-allocate,
        .btn-remove,
        button[onclick*="editJuryInVacancy"],
        button[onclick*="deleteJury"],
        .no-print {
            display: none !important;
        }

        .allocation-table th:last-child,
        .allocation-table td:last-child {
            display: none !important;
        }

        .allocation-table {
            font-size: 0.65rem;
            /* Reduzir fonte para caber */
            width: 100%;
        }

        /* Forçar cores de fundo em impressão */
        .group-header,
        .subtotal-row,
        .total-row,
        .allocation-table th {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        /* Fallback para navegadores teimosos (box-shadow hack) */
        .group-header {
            background-color: #f9fafb !important;
            box-shadow: inset 0 0 0 1000px #f9fafb !important;
            color: #374151 !important;
        }

        .subtotal-row {
            background-color: #fafafa !important;
            box-shadow: inset 0 0 0 1000px #fafafa !important;
        }

        .total-row {
            background-color: #f3f4f6 !important;
            box-shadow: inset 0 0 0 1000px #f3f4f6 !important;
            color: #1f2937 !important;
        }

        .discipline-total-row {
            background-color: #f3f4f6 !important;
            box-shadow: inset 0 0 0 1000px #f3f4f6 !important;
            color: #1f2937 !important;
        }

        .grand-total-row {
            background-color: #e5e7eb !important;
            box-shadow: inset 0 0 0 1000px #e5e7eb !important;
            color: #111827 !important;
        }

        /* Bordas suaves */
        .total-row td,
        .discipline-total-row td,
        .grand-total-row td {
            border-top: 1px solid #d1d5db !important;
            border-bottom: 1px solid #d1d5db !important;
        }

        .subtotal-row td {
            border-top: 1px solid #e5e7eb !important;
            border-bottom: 1px solid #e5e7eb !important;
        }

        body {
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            background-color: white;
        }

        @page {
            margin: 1cm;
            size: landscape;
        }
    }

    /* Animação de spinner */
    @keyframes spin {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
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

    <div class="max-w-full mx-auto px-3">
        <!-- Cabeçalho Compacto -->
        <div class="bg-gradient-to-r from-blue-900 to-blue-700 text-white rounded-lg shadow-md p-4 mb-4">
            <div class="flex justify-between items-center">
                <div>
                    <?php
                    // Título dinâmico baseado na vaga selecionada
                    if (!empty($vacancy)) {
                        $pageTitle = '📅 ' . htmlspecialchars($vacancy['title']);
                    } else {
                        $pageTitle = '📅 Calendário de Vigilância - ' . date('Y');
                    }
                    ?>
                    <h1 class="text-xl font-bold">
                        <?= $pageTitle ?>
                    </h1>
                    <p class="text-blue-200 text-xs mt-0.5">Extensão da Beira - Comissão de Exames de Admissão</p>
                </div>
                <div class="flex gap-2">
                    <?php if (!empty($vacancyId)): ?>
                        <a href="<?= url('/juries/vacancy/' . $vacancyId . '/manage') ?>"
                            class="px-3 py-1.5 bg-white text-blue-900 text-xs font-medium rounded hover:bg-blue-50 inline-flex items-center gap-1">
                            ➕ Criar Júris
                        </a>
                    <?php else: ?>
                        <a href="<?= url('/juries/planning-by-vacancy') ?>"
                            class="px-3 py-1.5 bg-white text-blue-900 text-xs font-medium rounded hover:bg-blue-50 inline-flex items-center gap-1">
                            ➕ Criar Júris
                        </a>
                    <?php endif; ?>
                    <button type="button" onclick="window.print()"
                        class="px-3 py-1.5 bg-white text-blue-900 text-xs font-medium rounded hover:bg-blue-50">
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
                        <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <div>
                            <p class="text-sm font-semibold text-blue-900">📋 Mostrando júris de:</p>
                            <p class="text-sm text-blue-800 font-medium">
                                <?= htmlspecialchars($vacancy['title']) ?>
                            </p>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <select onchange="window.location.href='<?= url('/juries/planning?vacancy_id=') ?>'+this.value"
                            class="px-3 py-2 text-sm border border-blue-300 rounded-lg bg-white text-gray-700 hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="current" <?= ($vacancyId ?? 'current') === $vacancyId ? 'selected' : '' ?>>📌 Vaga
                                Atual</option>
                            <optgroup label="Histórico">
                                <?php foreach ($allVacancies ?? [] as $v): ?>
                                    <?php if ($v['status'] !== 'aberta'): ?>
                                        <option value="<?= $v['id'] ?>" <?= ($vacancyId ?? 0) == $v['id'] ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($v['title']) ?> (
                                            <?= ucfirst($v['status']) ?>)
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
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm font-semibold text-yellow-900">⚠️ Nenhuma vaga aberta no momento</p>
                        <p class="text-xs text-yellow-800 mt-1">Não há vagas abertas. Crie uma nova vaga ou selecione do
                            histórico.</p>
                    </div>
                    <?php if (!empty($allVacancies)): ?>
                        <select onchange="window.location.href='<?= url('/juries/planning?vacancy_id=') ?>'+this.value"
                            class="px-3 py-2 text-sm border border-yellow-300 rounded-lg bg-white text-gray-700">
                            <option value="">Ver Histórico</option>
                            <?php foreach ($allVacancies as $v): ?>
                                <option value="<?= $v['id'] ?>">
                                    <?= htmlspecialchars($v['title']) ?> (
                                    <?= ucfirst($v['status']) ?>)
                                </option>
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
                        <svg class="w-6 h-6 text-indigo-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        <div>
                            <h3 class="text-sm font-semibold text-indigo-900">🎯 Filtrando por Vaga</h3>
                            <p class="text-sm text-indigo-800 mt-1">
                                <strong>
                                    <?= e($vacancy['title']) ?>
                                </strong><br>
                                <span class="text-xs">Mostrando apenas júris e vigilantes desta vaga específica</span>
                            </p>
                            <div class="flex gap-2 mt-2">
                                <a href="<?= url('/juries/vacancy/' . $vacancy['id'] . '/manage') ?>"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 bg-indigo-600 text-white text-xs font-medium rounded hover:bg-indigo-700">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                    </svg>
                                    Gestão de Júris desta Vaga
                                </a>
                                <a href="<?= url('/juries/planning') ?>"
                                    class="inline-flex items-center gap-1 px-3 py-1.5 border border-indigo-600 text-indigo-700 text-xs font-medium rounded hover:bg-indigo-50">
                                    Remover Filtro (Ver Todos)
                                </a>
                            </div>
                        </div>
                    </div>
                    <button onclick="this.closest('div[class*=bg-indigo-50]').style.display='none'"
                        class="text-indigo-600 hover:text-indigo-800">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        <?php endif; ?>

        <!-- Estatísticas Compactas -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-2 mb-4">
            <div class="bg-white px-3 py-2 rounded-lg shadow-sm border-l-3 border-blue-600 flex items-center gap-3">
                <div class="text-2xl font-bold text-gray-900">
                    <?= $stats['total_juries'] ?? 0 ?>
                </div>
                <div class="text-xs text-gray-500 font-medium leading-tight">Total<br>Júris</div>
            </div>
            <div class="bg-white px-3 py-2 rounded-lg shadow-sm border-l-3 border-green-600 flex items-center gap-3">
                <div class="text-2xl font-bold text-green-600">
                    <?= $stats['total_allocated'] ?? 0 ?>
                </div>
                <div class="text-xs text-gray-500 font-medium leading-tight">Vigilantes<br>Alocados</div>
            </div>
            <div class="bg-white px-3 py-2 rounded-lg shadow-sm border-l-3 border-orange-500 flex items-center gap-3">
                <div class="text-2xl font-bold text-orange-500">
                    <?= $stats['missing_allocations'] ?? (($stats['slots_available'] ?? 0) - ($stats['total_allocated'] ?? 0)) ?>
                </div>
                <div class="text-xs text-gray-500 font-medium leading-tight">Vagas<br>Livres</div>
            </div>
            <div class="bg-white px-3 py-2 rounded-lg shadow-sm border-l-3 border-red-500 flex items-center gap-3">
                <div class="text-2xl font-bold text-red-600">
                    <?= $stats['juries_without_supervisor'] ?? 0 ?>
                </div>
                <div class="text-xs text-gray-500 font-medium leading-tight">Sem<br>Supervisor</div>
            </div>
            <div class="bg-white px-3 py-2 rounded-lg shadow-sm border-l-3 border-indigo-600 flex items-center gap-3">
                <div class="text-2xl font-bold text-indigo-600">
                    <?= $stats['total_candidates'] ?? 0 ?>
                </div>
                <div class="text-xs text-gray-500 font-medium leading-tight">Total<br>Candidatos</div>
            </div>
        </div>

        <!-- BANNER DE ESTADO GLOBAL DO PLANEAMENTO -->
        <?php
        $totalJuries = $stats['total_juries'] ?? 0;
        $totalAllocated = $stats['total_allocated'] ?? 0;
        // CORREÇÃO: Usar missing_allocations para ignorar excesso de alocação em outras salas
        $vagasLivres = $stats['missing_allocations'] ?? (($stats['slots_available'] ?? 0) - ($stats['total_allocated'] ?? 0));
        $semSupervisor = $stats['juries_without_supervisor'] ?? 0;

        // Determinar estado global
        $isComplete = ($totalJuries > 0 && $vagasLivres <= 0 && $semSupervisor <= 0);
        $hasPendencies = ($totalJuries > 0 && ($vagasLivres > 0 || $semSupervisor > 0));
        $isEmpty = ($totalJuries == 0);

        if ($isComplete) {
            $statusColor = 'green';
            $statusLabel = '🟢 Planeamento Completo';
            $statusMessage = 'Todos os vigilantes e supervisores estão alocados.';
        } elseif ($hasPendencies) {
            $statusColor = 'orange';
            $statusLabel = '🟠 Planeamento com Pendências';
            $pendencias = [];
            if ($vagasLivres > 0)
                $pendencias[] = "$vagasLivres vaga(s) de vigilante";
            if ($semSupervisor > 0)
                $pendencias[] = "$semSupervisor júri(s) sem supervisor";
            $statusMessage = 'Pendentes: ' . implode(', ', $pendencias);
        } else {
            $statusColor = 'red';
            $statusLabel = '🔴 Planeamento Incompleto';
            $statusMessage = 'Nenhum júri criado para esta vaga.';
        }
        ?>

        <?php if (!empty($vacancy)): ?>
            <div class="mb-4 p-4 rounded-lg border-2 flex items-center justify-between 
            <?php if ($statusColor === 'green'): ?>bg-green-50 border-green-400<?php endif; ?>
            <?php if ($statusColor === 'orange'): ?>bg-orange-50 border-orange-400<?php endif; ?>
            <?php if ($statusColor === 'red'): ?>bg-red-50 border-red-400<?php endif; ?>
        ">
                <div class="flex items-center gap-3">
                    <div class="flex-shrink-0">
                        <?php if ($statusColor === 'green'): ?>
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        <?php elseif ($statusColor === 'orange'): ?>
                            <svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        <?php else: ?>
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg 
                        <?php if ($statusColor === 'green'): ?>text-green-800<?php endif; ?>
                        <?php if ($statusColor === 'orange'): ?>text-orange-800<?php endif; ?>
                        <?php if ($statusColor === 'red'): ?>text-red-800<?php endif; ?>
                    ">
                            <?= $statusLabel ?>
                        </h3>
                        <p class="text-sm 
                        <?php if ($statusColor === 'green'): ?>text-green-700<?php endif; ?>
                        <?php if ($statusColor === 'orange'): ?>text-orange-700<?php endif; ?>
                        <?php if ($statusColor === 'red'): ?>text-red-700<?php endif; ?>
                    ">
                            <?= $statusMessage ?>
                        </p>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <?php if ($isComplete): ?>
                        <button type="button" id="btn-validate-planning"
                            class="px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition-colors flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            ✓ Validar Planeamento
                        </button>
                    <?php else: ?>
                        <button type="button" disabled
                            class="px-4 py-2 bg-gray-300 text-gray-500 font-semibold rounded-lg cursor-not-allowed flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Validar Planeamento
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- MÓDULO DE FILTROS -->
        <?php if (!empty($vacancy)): ?>
            <div class="bg-white rounded-lg shadow-md p-4 mb-4" id="filter-module">
                <!-- Chips de Estado (Sempre Visíveis) -->
                <div class="flex flex-wrap items-center gap-2 mb-3">
                    <span class="text-sm font-medium text-gray-600 mr-2">Filtrar por estado:</span>

                    <button type="button"
                        class="filter-chip px-3 py-1.5 rounded-full border text-sm flex items-center gap-1.5 transition-all hover:shadow"
                        data-filter="no-vigilante" data-active="false">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span>Sem vigilante</span>
                        <span class="bg-red-100 text-red-800 px-1.5 py-0.5 rounded-full text-xs font-bold"
                            id="count-no-vigilante">0</span>
                    </button>

                    <button type="button"
                        class="filter-chip px-3 py-1.5 rounded-full border text-sm flex items-center gap-1.5 transition-all hover:shadow"
                        data-filter="no-supervisor" data-active="false">
                        <span class="w-2 h-2 rounded-full bg-red-500"></span>
                        <span>Sem supervisor</span>
                        <span class="bg-red-100 text-red-800 px-1.5 py-0.5 rounded-full text-xs font-bold"
                            id="count-no-supervisor">0</span>
                    </button>

                    <button type="button"
                        class="filter-chip px-3 py-1.5 rounded-full border text-sm flex items-center gap-1.5 transition-all hover:shadow"
                        data-filter="incomplete" data-active="false">
                        <span class="w-2 h-2 rounded-full bg-orange-500"></span>
                        <span>Incompleto</span>
                        <span class="bg-orange-100 text-orange-800 px-1.5 py-0.5 rounded-full text-xs font-bold"
                            id="count-incomplete">0</span>
                    </button>

                    <button type="button"
                        class="filter-chip px-3 py-1.5 rounded-full border text-sm flex items-center gap-1.5 transition-all hover:shadow"
                        data-filter="complete" data-active="false">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        <span>Completo</span>
                        <span class="bg-green-100 text-green-800 px-1.5 py-0.5 rounded-full text-xs font-bold"
                            id="count-complete">0</span>
                    </button>

                    <div class="flex-1"></div>

                    <!-- Botão expandir filtros -->
                    <button type="button" id="toggle-filters"
                        class="text-sm text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <svg class="w-4 h-4 transition-transform" id="filter-arrow" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                        Mais filtros
                    </button>
                </div>

                <!-- Painel de Filtros Avançados (Expansível) -->
                <div id="advanced-filters" class="hidden border-t pt-3 mt-2">
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-3">
                        <!-- Local -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">📍 Local</label>
                            <select id="filter-local"
                                class="w-full text-sm border rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <?php
                                $locations = [];
                                foreach ($groupedJuries ?? [] as $group) {
                                    if (isset($group['locations'])) {
                                        foreach ($group['locations'] as $locName => $data) {
                                            if ($locName && !in_array($locName, $locations)) {
                                                $locations[] = $locName;
                                            }
                                        }
                                    } elseif (isset($group['juries'])) {
                                        foreach ($group['juries'] as $jury) {
                                            $loc = $jury['location'] ?? '';
                                            if ($loc && !in_array($loc, $locations)) {
                                                $locations[] = $loc;
                                            }
                                        }
                                    }
                                }
                                sort($locations);
                                foreach ($locations as $loc): ?>
                                    <option value="<?= htmlspecialchars($loc) ?>">
                                        <?= htmlspecialchars($loc) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Data -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">📅 Data</label>
                            <select id="filter-date"
                                class="w-full text-sm border rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Todas</option>
                                <?php
                                $dates = [];
                                foreach ($groupedJuries ?? [] as $group) {
                                    $d = $group['exam_date'] ?? '';
                                    if ($d && !in_array($d, $dates)) {
                                        $dates[] = $d;
                                    }
                                }
                                sort($dates);
                                foreach ($dates as $d): ?>
                                    <option value="<?= $d ?>">
                                        <?= date('d/m/Y', strtotime($d)) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Disciplina -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">📘 Disciplina</label>
                            <select id="filter-subject"
                                class="w-full text-sm border rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Todas</option>
                                <?php
                                $subjects = [];
                                foreach ($groupedJuries ?? [] as $group) {
                                    $s = $group['subject'] ?? '';
                                    if ($s && !in_array($s, $subjects)) {
                                        $subjects[] = $s;
                                    }
                                }
                                sort($subjects);
                                foreach ($subjects as $s): ?>
                                    <option value="<?= htmlspecialchars($s) ?>">
                                        <?= htmlspecialchars($s) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Horário -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">⏰ Horário</label>
                            <select id="filter-time"
                                class="w-full text-sm border rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <?php
                                $times = [];
                                foreach ($groupedJuries ?? [] as $group) {
                                    $t = $group['start_time'] ?? '';
                                    if ($t && !in_array($t, $times)) {
                                        $times[] = $t;
                                    }
                                }
                                sort($times);
                                foreach ($times as $t): ?>
                                    <option value="<?= $t ?>">
                                        <?= substr($t, 0, 5) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Vigilante -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">👤 Vigilante</label>
                            <select id="filter-vigilante"
                                class="w-full text-sm border rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <?php foreach ($vigilantes ?? [] as $v): ?>
                                    <option value="<?= $v['id'] ?>">
                                        <?= htmlspecialchars($v['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Supervisor -->
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">👔 Supervisor</label>
                            <select id="filter-supervisor"
                                class="w-full text-sm border rounded px-2 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Todos</option>
                                <?php foreach ($supervisors ?? [] as $s): ?>
                                    <option value="<?= $s['id'] ?>">
                                        <?= htmlspecialchars($s['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Botão Limpar Filtros -->
                    <div class="mt-3 flex items-center justify-between">
                        <button type="button" id="clear-filters"
                            class="text-sm text-gray-600 hover:text-red-600 flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Limpar todos os filtros
                        </button>
                    </div>
                </div>

                <!-- Indicador de resultados -->
                <div class="text-right text-sm text-gray-500 mt-2">
                    Mostrando <strong id="visible-count">0</strong> de <strong id="total-count">0</strong> júris
                </div>
            </div>
        <?php endif; ?>

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
                                        <svg class="w-16 h-16 text-gray-300" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                        </svg>
                                        <p class="text-lg font-medium">Nenhum júri criado ainda</p>
                                        <?php if (!empty($vacancyId)): ?>
                                            <a href="<?= url('/juries/vacancy/' . $vacancyId . '/manage') ?>"
                                                class="btn-allocate btn-manual inline-block">
                                                ➕ Criar Júris para Esta Vaga
                                            </a>
                                        <?php else: ?>
                                            <a href="<?= url('/juries/planning-by-vacancy') ?>"
                                                class="btn-allocate btn-manual inline-block">
                                                ➕ Criar Júris Agora
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php else:
                            // CÁLCULO PRÉVIO: Totais por Disciplina
                            $disciplineTotals = [];
                            foreach ($groupedJuries as $g) {
                                $subj = $g['subject'];
                                if (!isset($disciplineTotals[$subj])) {
                                    $disciplineTotals[$subj] = ['candidates' => 0, 'vigilantes' => 0, 'juries' => 0];
                                }

                                $juriesList = [];
                                if (isset($g['locations'])) {
                                    foreach ($g['locations'] as $loc) {
                                        foreach ($loc['juries'] as $j)
                                            $juriesList[] = $j;
                                    }
                                } elseif (isset($g['juries'])) {
                                    $juriesList = $g['juries'];
                                }

                                foreach ($juriesList as $j) {
                                    $disciplineTotals[$subj]['candidates'] += (int) $j['candidates_quota'];
                                    $disciplineTotals[$subj]['vigilantes'] += count($j['vigilantes'] ?? []);
                                    $disciplineTotals[$subj]['juries']++;
                                }
                            }

                            // Agrupar júris por local dentro de cada exame
                            $lastSupervisor = null;
                            $totalCandidatos = 0;
                            $totalVigilantes = 0;

                            foreach ($groupedJuries as $groupIndex => $group):
                                $lastSupervisor = null;

                                // Normalizar dados de locais
                                $juriesByLocation = [];
                                if (isset($group['locations'])) {
                                    foreach ($group['locations'] as $locName => $locData) {
                                        $juriesByLocation[$locName] = $locData['juries'];
                                    }
                                } elseif (isset($group['juries'])) {
                                    foreach ($group['juries'] as $jury) {
                                        $location = !empty($jury['location']) ? $jury['location'] : 'Local não especificado';
                                        if (!isset($juriesByLocation[$location])) {
                                            $juriesByLocation[$location] = [];
                                        }
                                        $juriesByLocation[$location][] = $jury;
                                    }
                                }

                                // 1. PRÉ-CÁLCULO DE LINHAS (GRAND TOTAL ROWSPAN)
                                // Necessário para saber o rowspan das colunas principais (Data, Hora, Exame)
                                // que devem abranger TODOS os locais deste grupo.
                                $grandTotalRows = 0;

                                // Estrutura temporária para guardar os grupos de supervisor pré-processados
                                // para evitar re-processar no loop de exibição
                                $processedLocations = [];

                                foreach ($juriesByLocation as $location => $juries) {
                                    // Agrupar por Supervisor
                                    $supervisorsGroups = [];
                                    foreach ($juries as $jury) {
                                        $supId = $jury['supervisor_id'] ?? 0;
                                        if (!isset($supervisorsGroups[$supId])) {
                                            $supervisorsGroups[$supId] = [
                                                'supervisor_name' => $jury['supervisor_name'] ?? null,
                                                'supervisor_id' => $jury['supervisor_id'] ?? null,
                                                'juries' => []
                                            ];
                                        }
                                        $supervisorsGroups[$supId]['juries'][] = $jury;
                                    }

                                    // Contar linhas para este local
                                    $locRows = 1; // 1 linha para o Cabeçalho do Local
                                    $locRows += count($juries); // Todas as linhas de júri deste local
                                    $locRows += 1; // 1 linha para o Subtotal ÚNICO do Local
                        
                                    $grandTotalRows += $locRows;

                                    // Salvar processamento
                                    $processedLocations[$location] = $supervisorsGroups;
                                }

                                // Adicionar linha de Total Geral do Grupo
                                $grandTotalRows += 1;

                                // Variáveis de controle de exibição
                                $isFirstLocationOfGroup = true;
                                $examCandidates = 0; // Totais do Grupo Inteiro
                                $examVigilantes = 0;
                                ?>

                                <?php foreach ($processedLocations as $location => $supervisorsGroups): ?>

                                    <!-- CABEÇALHO DO LOCAL -->
                                    <tr class="location-header" data-location="<?= htmlspecialchars($location) ?>">
                                        <!-- Colunas Principais (Repetidas mas controladas via JS/CSS) -->
                                        <?php
                                        // Dados para as colunas principais
                                        $firstJury = reset($supervisorsGroups)['juries'][0] ?? null;
                                        $dateStr = $firstJury['exam_date'] ?? $group['exam_date'];
                                        $timeStr = $firstJury['start_time'] ?? $group['start_time'];
                                        $endTimeStr = $firstJury['end_time'] ?? $group['end_time'];

                                        $diasSemana = [
                                            'Monday' => 'Segunda-feira',
                                            'Tuesday' => 'Terça-feira',
                                            'Wednesday' => 'Quarta-feira',
                                            'Thursday' => 'Quinta-feira',
                                            'Friday' => 'Sexta-feira',
                                            'Saturday' => 'Sábado',
                                            'Sunday' => 'Domingo'
                                        ];
                                        $diaIngles = date('l', strtotime($dateStr));
                                        $diaPortugues = $diasSemana[$diaIngles] ?? $diaIngles;
                                        ?>

                                        <td class="merged-cell align-middle text-center font-semibold border-r-2 border-gray-300 bg-white"
                                            data-col="date">
                                            <div class="cell-content">
                                                <?= date('d/m/Y', strtotime($dateStr)) ?><br>
                                                <span class="text-xs text-gray-500">(<?= $diaPortugues ?>)
                                                </span>
                                            </div>
                                        </td>
                                        <td class="merged-cell align-middle text-center font-semibold border-r-2 border-gray-300 bg-white"
                                            data-col="time">
                                            <div class="cell-content">
                                                <?= date('H:i', strtotime($timeStr)) ?> -
                                                <?= date('H:i', strtotime($endTimeStr)) ?>
                                            </div>
                                        </td>
                                        <td class="merged-cell align-middle text-center font-semibold bg-gray-50 border-r-2 border-gray-300"
                                            data-col="subject">
                                            <div class="cell-content">
                                                <?= e($group['subject']) ?>
                                            </div>
                                        </td>

                                        <!-- Coluna Local (Ocupa o resto da largura) -->
                                        <td colspan="5" class="group-header"
                                            style="background: linear-gradient(90deg, #f1f5f9 0%, #e2e8f0 100%); color: #475569; border-bottom: 2px solid #94a3b8;">
                                            📍
                                            <?= strtoupper(htmlspecialchars($location)) ?>
                                        </td>
                                    </tr>

                                    <?php
                                    $isFirstLocationOfGroup = false; // Já imprimimos as colunas principais
                        
                                    // ORDENAÇÃO DOS GRUPOS DE SUPERVISOR (Sem Supervisor ID 0 primeiro ou último?)
                                    // Manter ordem de inserção mas garantir consistência
                                    ksort($supervisorsGroups);

                                    // LOOP: ITERAR CADA GRUPO DE SUPERVISOR DENTRO DO LOCAL (SEPARAR POR SUPERVISOR)
                                    foreach ($supervisorsGroups as $supId => $supGroup):
                                        $juriesInSupGroup = $supGroup['juries'];

                                        // Sort juries by Room Name
                                        usort($juriesInSupGroup, function ($a, $b) {
                                            return strnatcmp($a['room'], $b['room']);
                                        });

                                        $subCandidates = 0;
                                        $subVigilantes = 0;
                                        $isFirstRowOfSup = true;

                                        // RENDERIZAR JÚRIS DESTE SUPERVISOR
                                        foreach ($juriesInSupGroup as $jury):
                                            // Calculate totals
                                            $vigilantesCount = count($jury['vigilantes'] ?? []);
                                            $examCandidates += $jury['candidates_quota'];
                                            $examVigilantes += $vigilantesCount;

                                            // Subtotal do Supervisor
                                            $subCandidates += $jury['candidates_quota'];
                                            $subVigilantes += $vigilantesCount;

                                            // Totais Gerais
                                            $totalCandidatos += $jury['candidates_quota'];
                                            $totalVigilantes += $vigilantesCount;

                                            // UI Vars
                                            $minVigilantes = max(1, ceil(($jury['candidates_quota'] ?? 0) / 30));
                                            $hasSupervisor = !empty($jury['supervisor_id']);
                                            $vigilanteIds = array_column($jury['vigilantes'] ?? [], 'id');
                                            ?>
                                            <tr data-jury-id="<?= $jury['id'] ?>" class="<?= $isFirstRowOfSup ? 'sup-group-start' : '' ?>"
                                                data-location="<?= htmlspecialchars($location) ?>"
                                                data-exam-date="<?= $jury['exam_date'] ?>" data-start-time="<?= $jury['start_time'] ?>"
                                                data-subject="<?= htmlspecialchars($group['subject'] ?? '') ?>"
                                                data-vigilantes-count="<?= $vigilantesCount ?>" data-min-vigilantes="<?= $minVigilantes ?>"
                                                data-has-supervisor="<?= $hasSupervisor ? 'true' : 'false' ?>"
                                                data-vigilante-ids="<?= implode(',', $vigilanteIds) ?>"
                                                data-supervisor-id="<?= $jury['supervisor_id'] ?? '' ?>"
                                                style="<?= $isFirstRowOfSup && $supId > 0 ? 'border-top: 2px solid #e2e8f0;' : '' ?>">

                                                <!-- Colunas Mescladas (Repetidas) -->
                                                <td class="merged-cell align-middle text-center font-semibold border-r-2 border-gray-300 bg-white"
                                                    data-col="date">
                                                    <div class="cell-content">
                                                        <?= date('d/m/Y', strtotime($jury['exam_date'])) ?><br>
                                                        <span class="text-xs text-gray-500">(
                                                            <?= $diasSemana[date('l', strtotime($jury['exam_date']))] ?? '' ?>)
                                                        </span>
                                                    </div>
                                                </td>
                                                <td class="merged-cell align-middle text-center font-semibold border-r-2 border-gray-300 bg-white"
                                                    data-col="time">
                                                    <div class="cell-content">
                                                        <?= date('H:i', strtotime($jury['start_time'])) ?> -
                                                        <?= date('H:i', strtotime($jury['end_time'])) ?>
                                                    </div>
                                                </td>
                                                <td class="merged-cell align-middle text-center font-semibold bg-gray-50 border-r-2 border-gray-300"
                                                    data-col="subject">
                                                    <div class="cell-content">
                                                        <?= e($group['subject']) ?>
                                                    </div>
                                                </td>

                                                <!-- SALA -->
                                                <td class="font-bold text-gray-700 bg-white">
                                                    <div class="flex items-center gap-2">
                                                        <span><?= htmlspecialchars($jury['room']) ?></span>
                                                        <?php if ($jury['has_room_conflict'] ?? false): ?>
                                                            <span class="text-red-500 text-xs"
                                                                title="Conflito de sala detectado! Esta sala está sendo usada em outro exame no mesmo horário.">
                                                                ⚠️
                                                            </span>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>

                                                <!-- QUOTA CANDIDATOS -->
                                                <td class="text-center font-bold text-blue-800 bg-blue-50/50">
                                                    <?= $jury['candidates_quota'] ?>
                                                    <div class="text-[9px] text-gray-400 font-normal">
                                                        Mín: <?= $minVigilantes ?>
                                                    </div>
                                                </td>

                                                <!-- VIGILANTES -->
                                                <td class="bg-white">
                                                    <div class="flex flex-col gap-1">
                                                        <?php if (empty($jury['vigilantes'])): ?>
                                                            <div class="empty-slot" onclick="openManualAllocation(<?= $jury['id'] ?>)">
                                                                <span class="cursor-pointer hover:text-blue-600 hover:underline">
                                                                    + Adicionar vigilante
                                                                </span>
                                                            </div>
                                                        <?php else: ?>
                                                            <?php foreach ($jury['vigilantes'] as $v): ?>
                                                                <div
                                                                    class="flex items-center justify-between text-xs bg-gray-50 px-2 py-1 rounded border border-gray-100 hover:border-blue-200 group">
                                                                    <div class="flex items-center gap-1 overflow-hidden">
                                                                        <?php if (($v['supervisor_eligible'] ?? 0) == 1): ?>
                                                                            <span title="Elegível para Supervisor">👑</span>
                                                                        <?php else: ?>
                                                                            <span class="text-gray-400">👤</span>
                                                                        <?php endif; ?>
                                                                        <span class="truncate" title="<?= htmlspecialchars($v['name']) ?>">
                                                                            <?= htmlspecialchars($v['name']) ?>
                                                                        </span>
                                                                        <?php if (!empty($v['phone'])): ?>
                                                                            <span class="text-gray-400 text-[10px]">
                                                                                (<?= htmlspecialchars($v['phone']) ?>)
                                                                            </span>
                                                                        <?php endif; ?>
                                                                    </div>
                                                                    <button type="button"
                                                                        onclick="removeVigilante(<?= $jury['id'] ?>, <?= $v['id'] ?>)"
                                                                        class="text-gray-400 hover:text-red-500 transition-colors opacity-0 group-hover:opacity-100 px-1 font-bold">
                                                                        ×
                                                                    </button>
                                                                </div>
                                                            <?php endforeach; ?>
                                                            <!-- Botão (+) pequeno se não atingiu quota -->
                                                            <?php if ($vigilantesCount < $minVigilantes): ?>
                                                                <div class="text-center mt-1">
                                                                    <button onclick="openManualAllocation(<?= $jury['id'] ?>)"
                                                                        class="text-[10px] text-blue-600 hover:underline bg-blue-50 px-2 py-0.5 rounded-full border border-blue-100">
                                                                        + Adicionar
                                                                    </button>
                                                                </div>
                                                            <?php endif; ?>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>

                                                <!-- CONTADOR VIGILANTES -->
                                                <td class="text-center font-bold font-mono text-gray-700 bg-gray-50/50">
                                                    <span
                                                        class="<?= $vigilantesCount < $minVigilantes ? 'text-red-600' : 'text-green-600' ?>">
                                                        <?= $vigilantesCount ?>
                                                    </span>
                                                </td>

                                                <!-- AÇÕES -->
                                                <td class="text-center no-print">
                                                    <div class="flex items-center justify-center gap-1">
                                                        <button onclick="editJuryInVacancy(<?= $jury['id'] ?>)"
                                                            class="p-1 text-blue-600 hover:bg-blue-100 rounded" title="Editar Sala/Júri">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                                            </svg>
                                                        </button>
                                                        <button onclick="deleteJury(<?= $jury['id'] ?>)"
                                                            class="p-1 text-red-600 hover:bg-red-100 rounded" title="Remover Sala">
                                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                            </svg>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php
                                            $isFirstRowOfSup = false;
                                        endforeach; // Fim Júris Do Supervisor
                                        ?>

                                        <!-- LINHA DE SUBTOTAL DO SUPERVISOR -->
                                        <tr class="subtotal-row" data-location="<?= htmlspecialchars($location) ?>"
                                            style="background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%); border-top: 2px solid #cbd5e1; border-bottom: 2px solid #cbd5e1;">
                                            <!-- Colunas de Alinhamento (3) -->
                                            <td class="bg-white border-r-2 border-gray-300"></td>
                                            <td class="bg-white border-r-2 border-gray-300"></td>
                                            <td class="bg-gray-50 border-r-2 border-gray-300"></td>

                                            <td colspan="1" class="text-right font-bold text-slate-600 px-4 py-2 text-sm bg-slate-50">
                                                Subtotal
                                            </td>
                                            <td class="text-center font-bold text-blue-800 bg-slate-50">
                                                <?= $subCandidates ?>
                                            </td>
                                            <td colspan="1" class="px-3 py-2 bg-slate-50">
                                                <div class="flex items-center justify-between">
                                                    <!-- Info do Supervisor DO GRUPO -->
                                                    <div class="flex flex-col text-xs text-gray-600">
                                                        <?php
                                                        if (empty($supGroup['supervisor_name'])) {
                                                            echo '<span class="text-red-500 font-bold">⚠️ Sem supervisor</span>';
                                                        } else {
                                                            $sname = $supGroup['supervisor_name'];
                                                            // Tentar pegar telefone do primeiro júri
                                                            $firstJuryHere = $juriesInSupGroup[0] ?? [];
                                                            $sphone = $firstJuryHere['supervisor_phone'] ?? '';
                                                            $phoneDisplay = !empty($sphone) ? " <span class='text-gray-500'>({$sphone})</span>" : '';

                                                            echo "<div class='flex items-center gap-1'><span class='font-medium'>👑 $sname$phoneDisplay</span>";

                                                            // Botão para remover supervisor
                                                            if (!empty($firstJuryHere['id'])) {
                                                                echo "<button onclick='removeSupervisorFromAllJuries({$firstJuryHere['id']}, \"" . addslashes($sname) . "\")' class='text-red-500 hover:text-red-700 text-xs ml-1 p-0.5 rounded hover:bg-red-50 transition-colors' title='Remover Supervisor deste Grupo'>
                                                                    <svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'></path></svg>
                                                                </button>";
                                                            }
                                                            echo "</div>";
                                                        }
                                                        ?>
                                                    </div>

                                                    <!-- Botão Para Definir Supervisor (específico para este subgrupo) -->
                                                    <?php
                                                    $firstJuryHere = $juriesInSupGroup[0] ?? [];
                                                    $roomContext = count($juriesInSupGroup) . " salas - " . ($supGroup['supervisor_name'] ?? 'Sem Sup.');
                                                    $locJuryIds = array_column($juriesInSupGroup, 'id');
                                                    ?>
                                                    <?php if (!empty($firstJuryHere)): ?>
                                                        <button
                                                            onclick="openSupervisorSelectModalBulk([<?= implode(',', $locJuryIds) ?>], '<?= addslashes($roomContext) ?>', <?= $firstJuryHere['vacancy_id'] ?>, '<?= $firstJuryHere['exam_date'] ?>', '<?= $firstJuryHere['start_time'] ?>', '<?= $firstJuryHere['end_time'] ?>')"
                                                            class="px-2 py-1 bg-indigo-600 text-white text-xs rounded hover:bg-indigo-700 transition shadow-sm font-medium">
                                                            Definir
                                                        </button>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                            <td class="text-center font-bold text-gray-800 bg-slate-50">
                                                <?= $subVigilantes ?>
                                            </td>
                                            <td class="bg-slate-50"></td>
                                        </tr>

                                    <?php endforeach; // FIM LOOP SUPERVISORES ?>


                                <?php endforeach; // Fim loop locais (PROCESSADOS) 
                                        ?>

                                <?php // Nota: O Total do Grupo foi removido pois já é exibido na linha discipline-total-row abaixo ?>

                                <?php
                                // VERIFICAR SE É O ÚLTIMO GRUPO DESTA DISCIPLINA
                                // Se o próximo grupo for de outra disciplina (ou não existir), imprime o total agora.
                                $currentSubject = $group['subject'];
                                $nextGroup = $groupedJuries[$groupIndex + 1] ?? null;
                                $nextSubject = $nextGroup['subject'] ?? null;

                                if ($currentSubject !== $nextSubject && isset($disciplineTotals[$currentSubject])):
                                    $totals = $disciplineTotals[$currentSubject];
                                    ?>
                                    <tr class="discipline-total-row"
                                        style="background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 50%, #f0f9ff 100%); border-top: 2px solid #7dd3fc; border-bottom: 2px solid #7dd3fc;">
                                        <td colspan="8" class="py-3 px-6">
                                            <div class="flex items-center justify-between">
                                                <span class="font-extrabold text-sky-800 text-base uppercase tracking-wide">
                                                    📚 TOTAL <?= strtoupper($currentSubject) ?>
                                                </span>
                                                <div class="flex gap-8 font-bold text-sky-700">
                                                    <span class="flex items-center gap-2">
                                                        <span class="text-sm opacity-75">Candidatos:</span>
                                                        <span class="text-lg"><?= number_format($totals['candidates'], 0) ?></span>
                                                    </span>
                                                    <span class="flex items-center gap-2">
                                                        <span class="text-sm opacity-75">Júris:</span>
                                                        <span class="text-lg"><?= $totals['juries'] ?></span>
                                                    </span>
                                                    <span class="flex items-center gap-2">
                                                        <span class="text-sm opacity-75">Vigilantes:</span>
                                                        <span class="text-lg"><?= $totals['vigilantes'] ?></span>
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>

                            <?php endforeach; // Fim loop grupos de exame ?>

                            <!-- LINHA DE TOTALIZAÇÃO GERAL -->
                            <tr class="grand-total-row"
                                style="background: linear-gradient(135deg, #ea580c 0%, #f97316 50%, #ea580c 100%); color: white; border-top: 4px solid #c2410c;">
                                <td colspan="4"
                                    style="text-align: right; padding: 12px 20px; font-weight: 800; font-size: 1.1rem; text-transform: uppercase; letter-spacing: 0.05em;">
                                    🏆 TOTAL GERAL
                                </td>
                                <td style="text-align: center; font-weight: 800; font-size: 1.3rem; padding: 12px 8px;">
                                    <?= number_format($totalCandidatos, 0) ?>
                                </td>
                                <td style="padding: 12px 8px;"></td>
                                <td style="text-align: center; font-weight: 800; font-size: 1.3rem; padding: 12px 8px;">
                                    <?= $totalVigilantes ?>
                                </td>
                                <td style="padding: 12px 8px;"></td>
                            </tr>
                        <?php endif; // Fim if/else empty check
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
                    <p><strong>🎲 Auto:</strong> Distribuir vigilantes automaticamente</p>
                </div>
                <div>
                    <p class="font-semibold mb-2 text-blue-900">Júris/Salas:</p>
                    <p><strong>✏️ Editar:</strong> Editar disciplina, data, horário, sala e candidatos</p>
                    <p><strong>🗑️ Remover:</strong> Excluir sala/júri do planeamento</p>
                </div>
                <div>
                    <p class="font-semibold mb-2 text-blue-900">Totalizadores:</p>
                    <p><strong>Subtotal:</strong> Total de candidatos por exame (com supervisor)</p>
                    <p><strong>Total (Laranja):</strong> Total geral do bloco de exame</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Alocação Manual -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-manual-allocation"
    style="z-index: 50;">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50" style="z-index: 0;"></div>
    <div class="modal-content relative bg-white w-full max-w-2xl mx-4 rounded-lg shadow-lg p-6 z-10"
        style="z-index: 50; position: relative;">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">✋ Selecionar Vigilante</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <div id="manual-allocation-content">Carregando...</div>
    </div>
</div>

<!-- Modal: Seleccionar Supervisor para Júri Individual -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-supervisor-select"
    style="z-index: 50;">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50" onclick="closeSupervisorSelectModal()"
        style="z-index: 0;"></div>
    <div class="modal-content relative bg-white w-full max-w-lg mx-4 rounded-lg shadow-xl z-10"
        style="z-index: 50; position: relative;">
        <div class="flex justify-between items-center p-4 border-b bg-purple-600 text-white rounded-t-lg">
            <h2 class="text-lg font-semibold flex items-center gap-2">
                👔 Definir Supervisor
            </h2>
            <button type="button" onclick="closeSupervisorSelectModal()"
                class="text-white hover:text-purple-200 text-2xl">&times;</button>
        </div>
        <div class="p-4">
            <div id="supervisor-select-room" class="mb-4 p-3 bg-gray-50 rounded-lg">
                <span class="text-sm text-gray-600">Sala:</span>
                <span class="font-semibold text-gray-900" id="supervisor-select-room-name"></span>
            </div>

            <div id="supervisor-select-content" class="max-h-96 overflow-y-auto">
                <div class="flex items-center justify-center py-8">
                    <svg class="animate-spin w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                        </circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z">
                        </path>
                    </svg>
                    <span class="ml-2 text-gray-600">Carregando supervisores disponíveis...</span>
                </div>
            </div>
        </div>
        <div class="flex justify-end gap-2 p-4 border-t bg-gray-50 rounded-b-lg">
            <button type="button" onclick="closeSupervisorSelectModal()"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-100">
                Cancelar
            </button>
        </div>
    </div>
</div>


<!-- Modal: Editar Júri -->
<div id="modal-edit-jury" class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white z-10">
            <h2 class="text-xl font-bold text-gray-900">✏️ Editar Júri</h2>
            <button type="button" onclick="closeEditJuryModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
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
                        <select id="edit_room_id" name="room_id" class="w-full rounded border border-gray-300 px-3 py-2"
                            required>
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
                    <input type="number" id="edit_candidates_quota" name="candidates_quota" min="1" max="300"
                        class="w-full rounded border border-gray-300 px-3 py-2" required>
                    <p id="edit-vigilantes-needed" class="text-xs text-blue-600 mt-1 font-medium"></p>
                </div>
            </div>

            <!-- Observações -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                <textarea id="edit_notes" name="notes" rows="3" class="w-full rounded border border-gray-300 px-3 py-2"
                    placeholder="Observações adicionais sobre este júri..."></textarea>
            </div>

            <!-- Aviso de Impacto -->
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
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
                <button type="button" onclick="closeEditJuryModal()"
                    class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Confirmação de Exclusão -->
<div id="modal-confirm-delete"
    class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-[60] items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
        <div class="p-6">
            <div class="flex items-center justify-center w-12 h-12 mx-auto bg-red-100 rounded-full mb-4">
                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 id="confirm-title" class="text-lg font-semibold text-gray-900 text-center mb-2">Confirmar Exclusão</h3>
            <p id="confirm-message" class="text-sm text-gray-600 text-center mb-6">Tem certeza que deseja realizar esta
                ação?</p>
            <div class="flex gap-3">
                <button type="button" id="btn-confirm-cancel"
                    class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="button" id="btn-confirm-ok"
                    class="flex-1 px-4 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>const csrfToken = '<?= csrf_token() ?>';
    const baseUrl = '<?= rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') ?>';

    /**
    * Escape HTML para prevenir XSS em JavaScript
    */
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }


    /**
     * Exibir modal de confirmação personalizado
     */
    function showConfirmModal(title, message, onConfirm) {
        const modal = document.getElementById('modal-confirm-delete');
        const titleEl = document.getElementById('confirm-title');
        const messageEl = document.getElementById('confirm-message');
        const btnOk = document.getElementById('btn-confirm-ok');
        const btnCancel = document.getElementById('btn-confirm-cancel');

        titleEl.textContent = title;
        messageEl.innerHTML = message;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Remover listeners antigos
        const newBtnOk = btnOk.cloneNode(true);
        const newBtnCancel = btnCancel.cloneNode(true);
        btnOk.parentNode.replaceChild(newBtnOk, btnOk);
        btnCancel.parentNode.replaceChild(newBtnCancel, btnCancel);

        // Novo listener para confirmar
        newBtnOk.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            onConfirm();
        });

        // Listener para cancelar
        newBtnCancel.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        // Fechar ao clicar fora
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    }

    function editJury(juryId) {
        // Redirecionar para a página de gestão de júris onde a edição está implementada
        const currentUrl = new URL(window.location.href);
        const vacancyId = currentUrl.searchParams.get('vacancy_id');

        if (vacancyId) {
            window.location.href = `<?= url('/juries/vacancy/') ?>${vacancyId}/manage`;
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
                const masterResponse = await fetch(`${baseUrl}/api/master-data/locations-rooms`);
                const masterResult = await masterResponse.json();

                if (masterResult.success) {
                    editMasterData.locations = masterResult.locations;
                    editMasterData.rooms = masterResult.rooms;
                }
            }

            // Carregar dados do júri
            const juryResponse = await fetch(`${baseUrl}/juries/${juryId}/details`);
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
            const response = await fetch(`${baseUrl}/juries/${juryId}/update`, {
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
        showConfirmModal(
            '🗑️ Eliminar Júri',
            `<strong>${escapeHtml(roomName)}</strong><br><br>
            <span class="text-red-600">⚠️ Esta ação irá:</span>
            <ul class="text-left mt-2 space-y-1 text-sm">
                <li>• Remover o júri permanentemente</li>
                <li>• Desalocar todos os vigilantes associados</li>
                <li>• Esta ação <strong>NÃO PODE</strong> ser desfeita!</li>
            </ul>`,
            () => executeDeleteJury(juryId)
        );
    }

    async function executeDeleteJury(juryId) {
        try {
            const response = await fetch(`${baseUrl}/juries/${juryId}/delete`, {
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

    async function removeVigilante(juryId, vigilanteId, vigilanteName = 'este vigilante') {
        showConfirmModal(
            '👤 Remover Vigilante',
            `<strong>${escapeHtml(vigilanteName)}</strong><br><br>
            <span class="text-amber-600">⚠️ O vigilante será desalocado deste júri.</span><br>
            <span class="text-sm text-gray-500">Esta ação pode ser revertida alocando novamente.</span>`,
            () => executeRemoveVigilante(juryId, vigilanteId)
        );
    }

    async function executeRemoveVigilante(juryId, vigilanteId) {
        try {
            const response = await fetch(`${baseUrl}/juries/${juryId}/unassign`, {
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
            const response = await fetch(`${baseUrl}/api/allocation/eligible-vigilantes/${juryId}`);

            // Verificar se a resposta é JSON antes de tentar parsear
            const contentType = response.headers.get('content-type');
            const textResponse = await response.text();

            console.log('Response status:', response.status);
            console.log('Content-Type:', contentType);

            if (!contentType || !contentType.includes('application/json')) {
                console.error('Resposta não é JSON:', textResponse.substring(0, 500));
                throw new Error('Servidor retornou resposta inválida. Verifique os logs do servidor.');
            }

            let data;
            try {
                data = JSON.parse(textResponse);
            } catch (e) {
                console.error('Erro ao parsear JSON:', e);
                console.error('Resposta:', textResponse.substring(0, 500));
                throw new Error('Resposta do servidor não é JSON válido.');
            }

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
            const response = await fetch(`${baseUrl}/juries/${juryId}/set-supervisor`, {
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
        showConfirmModal(
            '⚠️ Remover Supervisor',
            `<span class="text-red-600 font-semibold">Esta ação removerá o supervisor de TODOS os júris deste exame.</span><br><br>
            <span class="text-gray-600">O supervisor deixará de estar associado às salas deste horário.</span>`,
            () => executeRemoveSupervisor(juryId),
            'danger'
        );
    }

    async function executeRemoveSupervisor(juryId) {
        try {
            const response = await fetch(`${baseUrl}/juries/${juryId}/set-supervisor`, {
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
                if (typeof toastr !== 'undefined') {
                    toastr.success(result.message, '✅ Sucesso');
                } else {
                    alert('✅ ' + result.message);
                }
                setTimeout(() => location.reload(), 1000);
            } else {
                if (typeof toastr !== 'undefined') {
                    toastr.error(result.message || 'Erro ao remover supervisor.', '❌ Erro');
                } else {
                    alert('❌ ' + (result.message || 'Erro ao remover supervisor.'));
                }
            }
        } catch (error) {
            console.error('Erro ao remover supervisor:', error);
            if (typeof toastr !== 'undefined') {
                toastr.error('Erro de conexão ao remover supervisor.', '❌ Erro');
            } else {
                alert('❌ Erro de conexão ao remover supervisor.');
            }
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
            const response = await fetch(`${baseUrl}/juries/${juryId}/eligible-vigilantes`);

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
            const response = await fetch(`${baseUrl}/juries/${juryId}/assign`, {
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

    /**
     * Auto-distribuir supervisores equilibradamente por um bloco de exame
     */
    async function autoDistributeSupervisors(subject, examDate, startTime, endTime, vacancyId) {
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;

        try {
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Distribuindo...';

            const params = new URLSearchParams({
                csrf: csrfToken,
                subject: subject,
                exam_date: examDate,
                start_time: startTime,
                end_time: endTime
            });

            if (vacancyId) {
                params.append('vacancy_id', vacancyId);
            }

            const response = await fetch(`${baseUrl}/juries/supervisors/auto-allocate`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params
            });

            const result = await response.json();

            if (result.success) {
                // Mostrar resumo da distribuição
                let distributionHTML = '<div class="text-left mt-2 space-y-1">';
                if (result.distribution && result.distribution.length > 0) {
                    result.distribution.forEach(d => {
                        distributionHTML += `<div class="flex items-center gap-2">
                            <span class="font-medium">👔 ${escapeHtml(d.supervisor_name)}</span>
                            <span class="px-2 py-0.5 text-xs bg-green-100 text-green-700 rounded-full">${d.jury_count} júris</span>
                        </div>`;
                    });
                }
                distributionHTML += '</div>';

                // Toast de sucesso
                const successDiv = document.createElement('div');
                successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-50 max-w-md';
                successDiv.innerHTML = `
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <div>
                            <div class="font-bold mb-1">✅ Supervisores Distribuídos!</div>
                            <div class="text-sm">${result.message}</div>
                            ${distributionHTML}
                        </div>
                    </div>
                `;
                document.body.appendChild(successDiv);

                // Recarregar página após 2s para ver os resultados
                setTimeout(() => {
                    location.reload();
                }, 2000);
            } else {
                alert('❌ ' + (result.message || 'Erro ao distribuir supervisores'));
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Erro ao auto-distribuir supervisores:', error);
            alert('❌ Erro de conexão ao distribuir supervisores');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    /**
     * Auto-distribuir vigilantes equilibradamente por um bloco de exame
     */
    async function autoDistributeVigilantes(subject, examDate, startTime, endTime, vacancyId) {
        const btn = event.target.closest('button');
        const originalText = btn.innerHTML;

        try {
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin w-4 h-4 inline-block" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg> Distribuindo...';

            const params = new URLSearchParams({
                csrf: csrfToken,
                subject: subject,
                exam_date: examDate,
                start_time: startTime,
                end_time: endTime
            });

            if (vacancyId) {
                params.append('vacancy_id', vacancyId);
            }

            const response = await fetch(`${baseUrl}/juries/vigilantes/auto-distribute`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: params
            });

            const result = await response.json();

            if (result.success) {
                const successDiv = document.createElement('div');
                successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                successDiv.innerHTML = `✅ ${result.message || 'Vigilantes distribuídos com sucesso!'}`;
                document.body.appendChild(successDiv);

                setTimeout(() => location.reload(), 1500);
            } else {
                alert('❌ ' + (result.message || 'Erro ao distribuir vigilantes'));
                btn.disabled = false;
                btn.innerHTML = originalText;
            }
        } catch (error) {
            console.error('Erro ao auto-distribuir vigilantes:', error);
            alert('❌ Erro de conexão ao distribuir vigilantes');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    }

    // Variáveis globais para o modal de selecção de supervisor
    let currentSupervisorSelectJuryId = null;
    let currentSupervisorSelectJuryIds = []; // Array para atribuição em lote
    let currentSupervisorSelectVacancyId = null;

    /**
     * Abrir modal para seleccionar supervisor de múltiplos júris (em lote)
     */
    async function openSupervisorSelectModalBulk(juryIds, roomName, vacancyId, examDate, startTime, endTime) {
        const modal = document.getElementById('modal-supervisor-select');
        const roomNameEl = document.getElementById('supervisor-select-room-name');
        const contentEl = document.getElementById('supervisor-select-content');

        currentSupervisorSelectJuryId = juryIds[0]; // Manter compatibilidade
        currentSupervisorSelectJuryIds = juryIds; // Guardar todos os IDs
        currentSupervisorSelectVacancyId = vacancyId;

        roomNameEl.textContent = roomName;

        // Mostrar modal com loading
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        contentEl.innerHTML = `
            <div class="flex items-center justify-center py-8">
                <svg class="animate-spin w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="ml-2 text-gray-600">Carregando supervisores disponíveis...</span>
            </div>
        `;

        // Carregar supervisores disponíveis
        await loadAvailableSupervisors(examDate, startTime, endTime, vacancyId);
    }

    /**
     * Abrir modal para seleccionar supervisor de um júri individual
     */
    async function openSupervisorSelectModal(juryId, roomName, vacancyId, examDate, startTime, endTime) {
        const modal = document.getElementById('modal-supervisor-select');
        const roomNameEl = document.getElementById('supervisor-select-room-name');
        const contentEl = document.getElementById('supervisor-select-content');

        currentSupervisorSelectJuryId = juryId;
        currentSupervisorSelectVacancyId = vacancyId;

        roomNameEl.textContent = roomName;

        // Mostrar modal com loading
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        contentEl.innerHTML = `
            <div class="flex items-center justify-center py-8">
                <svg class="animate-spin w-8 h-8 text-purple-600" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <span class="ml-2 text-gray-600">Carregando supervisores disponíveis...</span>
            </div>
        `;

        // Carregar supervisores disponíveis
        await loadAvailableSupervisors(examDate, startTime, endTime, vacancyId);
    }

    /**
     * Fechar modal de selecção de supervisor
     */
    function closeSupervisorSelectModal() {
        const modal = document.getElementById('modal-supervisor-select');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        currentSupervisorSelectJuryId = null;
        currentSupervisorSelectVacancyId = null;
    }

    /**
     * Carregar supervisores disponíveis do servidor
     */
    async function loadAvailableSupervisors(examDate, startTime, endTime, vacancyId) {
        const contentEl = document.getElementById('supervisor-select-content');

        try {
            const params = new URLSearchParams();
            if (examDate) params.append('exam_date', examDate);
            if (startTime) params.append('start_time', startTime);
            if (endTime) params.append('end_time', endTime);

            const response = await fetch(`${baseUrl}/api/users/supervisors?${params.toString()}`, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!response.ok) throw new Error('Erro ao carregar supervisores');

            const result = await response.json();
            const supervisors = result.supervisors || [];

            if (supervisors.length === 0) {
                contentEl.innerHTML = `
                    <div class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        <p class="font-medium">Nenhum supervisor elegível encontrado</p>
                        <p class="text-sm mt-1">Verifique se existem vigilantes com a opção "Elegível para supervisão" activa.</p>
                    </div>
                `;
                return;
            }

            // Renderizar lista de supervisores
            let html = '<div class="space-y-2">';

            for (const sup of supervisors) {
                const loadClass = sup.load >= 10 ? 'bg-red-100 text-red-700' :
                    (sup.load >= 8 ? 'bg-yellow-100 text-yellow-700' : 'bg-green-100 text-green-700');
                const loadText = sup.load !== undefined ? `${sup.load}/10 júris` : 'Disponível';

                html += `
                    <div class="flex items-center justify-between p-3 border rounded-lg hover:bg-purple-50 transition cursor-pointer"
                         onclick="assignSupervisorToJury(${sup.id}, '${escapeHtml(sup.name || sup.nome)}')">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-purple-100 flex items-center justify-center text-purple-600 font-bold text-sm">
                                ${(sup.name || sup.nome || '?').charAt(0).toUpperCase()}
                            </div>
                            <div>
                                <div class="font-medium text-gray-900">${escapeHtml(sup.name || sup.nome)}</div>
                                <div class="text-xs text-gray-500">${escapeHtml(sup.phone || sup.telefone || 'Sem telefone')}</div>
                            </div>
                        </div>
                        <span class="px-3 py-1 text-xs rounded-full font-medium ${loadClass}">
                            ${loadText}
                        </span>
                    </div>
                `;
            }

            html += '</div>';

            // Opção para remover supervisor
            html += `
                <div class="mt-4 pt-4 border-t">
                    <button onclick="assignSupervisorToJury(0, 'Nenhum')" 
                        class="w-full p-3 text-center text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition">
                        ✕ Remover Supervisor
                    </button>
                </div>
            `;

            contentEl.innerHTML = html;

        } catch (error) {
            console.error('Erro ao carregar supervisores:', error);
            contentEl.innerHTML = `
                <div class="text-center py-8 text-red-500">
                    <p class="font-medium">❌ Erro ao carregar supervisores</p>
                    <p class="text-sm mt-1">${error.message}</p>
                </div>
            `;
        }
    }

    /**
     * Atribuir supervisor a um ou mais júris
     */
    async function assignSupervisorToJury(supervisorId, supervisorName) {
        // Verificar se temos IDs para atribuir
        const juryIds = currentSupervisorSelectJuryIds.length > 0
            ? currentSupervisorSelectJuryIds
            : (currentSupervisorSelectJuryId ? [currentSupervisorSelectJuryId] : []);

        if (juryIds.length === 0) return;

        try {
            // Usar bulk assign para atribuir a todos os júris do local
            const response = await fetch(`${baseUrl}/juries/bulk-assign-supervisor`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    csrf: csrfToken,
                    jury_ids: juryIds,
                    supervisor_id: supervisorId
                })
            });

            const result = await response.json();

            if (result.success) {
                closeSupervisorSelectModal();

                const successDiv = document.createElement('div');
                successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                successDiv.innerHTML = supervisorId ?
                    `✅ Supervisor "${escapeHtml(supervisorName)}" atribuído a ${juryIds.length} júri(s)!` :
                    '✅ Supervisor removido!';
                document.body.appendChild(successDiv);

                setTimeout(() => location.reload(), 1000);
            } else {
                alert('❌ ' + (result.message || 'Erro ao atribuir supervisor'));
            }
        } catch (error) {
            console.error('Erro ao atribuir supervisor:', error);
            alert('❌ Erro de conexão ao atribuir supervisor');
        }
    }

    /**
     * Remover supervisor de um júri individual (chamado pelo botão X na célula)
     */
    async function removeSupervisorFromJury(juryId) {
        if (!confirm('Remover supervisor deste júri?')) return;

        try {
            const response = await fetch(`${baseUrl}/juries/${juryId}/supervisor/single`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    csrf: csrfToken,
                    supervisor_id: 0
                })
            });

            const result = await response.json();

            if (result.success) {
                const successDiv = document.createElement('div');
                successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                successDiv.innerHTML = '✅ Supervisor removido!';
                document.body.appendChild(successDiv);

                setTimeout(() => location.reload(), 1000);
            } else {
                alert('❌ ' + (result.message || 'Erro ao remover supervisor'));
            }
        } catch (error) {
            console.error('Erro ao remover supervisor:', error);
            alert('❌ Erro de conexão ao remover supervisor');
        }
    }


    /**
     * Remover supervisor de todos os júris de um bloco de exame
     */
    async function removeSupervisorFromAllJuries(juryId, supervisorName) {
        showConfirmModal(
            '🚫 Remover Supervisor',
            `<strong>${escapeHtml(supervisorName)}</strong><br><br>
            <span class="text-amber-600">⚠️ Esta ação irá:</span>
            <ul class="text-left mt-2 space-y-1 text-sm">
                <li>• Remover o supervisor de todos os júris deste exame</li>
                <li>• Os júris ficarão sem supervisor atribuído</li>
            </ul>`,
            async () => {
                try {
                    const response = await fetch(`${baseUrl}/juries/${juryId}/supervisor/remove`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: new URLSearchParams({ csrf: csrfToken })
                    });

                    const result = await response.json();

                    if (result.success) {
                        const successDiv = document.createElement('div');
                        successDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg z-50';
                        successDiv.innerHTML = '✅ Supervisor removido com sucesso!';
                        document.body.appendChild(successDiv);

                        setTimeout(() => location.reload(), 1000);
                    } else {
                        alert('❌ ' + (result.message || 'Erro ao remover supervisor'));
                    }
                } catch (error) {
                    console.error('Erro ao remover supervisor:', error);
                    alert('❌ Erro de conexão ao remover supervisor');
                }
            }
        );
    }

    // Event listeners para fechar modal
    document.addEventListener('DOMContentLoaded', function () {
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

    // ========== BOTÃO VALIDAR PLANEAMENTO ==========
    document.getElementById('btn-validate-planning')?.addEventListener('click', async function () {
        const confirmed = confirm(
            '✅ VALIDAR PLANEAMENTO\n\n' +
            'Ao validar:\n' +
            '• O planeamento será marcado como validado\n' +
            '• Alterações posteriores pedirão confirmação\n' +
            '• Mapas de vigilância estarão prontos para impressão\n\n' +
            'Deseja continuar?'
        );

        if (!confirmed) return;

        this.disabled = true;
        this.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span> Validando...';

        try {
            const vacancyId = <?= $vacancyId ?? 0 ?>;
            const response = await fetch('<?= url('/juries/vacancy/') ?>' + vacancyId + '/validate', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ csrf: csrfToken })
            });

            const result = await response.json();

            if (result.success) {
                alert('✅ Planeamento validado com sucesso!\n\nOs mapas estão prontos para impressão.');
                location.reload();
            } else {
                alert('❌ Erro: ' + (result.message || 'Não foi possível validar'));
                this.disabled = false;
                this.innerHTML = '✓ Validar Planeamento';
            }
        } catch (error) {
            console.error('Erro ao validar:', error);
            alert('❌ Erro de conexão ao validar planeamento');
            this.disabled = false;
            this.innerHTML = '✓ Validar Planeamento';
        }
    });

    // ========== MÓDULO DE FILTROS ==========
    (function initFilterModule() {
        const filterModule = document.getElementById('filter-module');
        if (!filterModule) return;

        // Elementos
        const toggleBtn = document.getElementById('toggle-filters');
        const advancedFilters = document.getElementById('advanced-filters');
        const filterArrow = document.getElementById('filter-arrow');
        const clearBtn = document.getElementById('clear-filters');
        const visibleCountEl = document.getElementById('visible-count');
        const totalCountEl = document.getElementById('total-count');

        // Dropdowns
        const filterLocal = document.getElementById('filter-local');
        const filterDate = document.getElementById('filter-date');
        const filterSubject = document.getElementById('filter-subject');
        const filterTime = document.getElementById('filter-time');
        const filterVigilante = document.getElementById('filter-vigilante');
        const filterSupervisor = document.getElementById('filter-supervisor');

        // State chips
        const filterChips = document.querySelectorAll('.filter-chip');

        // Todas as linhas de júri na tabela
        const allJuryRows = document.querySelectorAll('.allocation-table tbody tr[data-jury-id]');
        const locationRows = document.querySelectorAll('.allocation-table tbody tr.location-header');
        const subtotalRows = document.querySelectorAll('.allocation-table tbody tr.subtotal-row');

        // Estado dos filtros
        let activeFilters = {
            state: null, // no-vigilante, no-supervisor, incomplete, complete
            local: '',
            date: '',
            subject: '',
            time: '',
            vigilante: '',
            supervisor: ''
        };

        // Toggle painel avançado
        toggleBtn?.addEventListener('click', () => {
            advancedFilters.classList.toggle('hidden');
            filterArrow.style.transform = advancedFilters.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
        });

        // Chip clicks
        filterChips.forEach(chip => {
            chip.addEventListener('click', () => {
                const filter = chip.dataset.filter;
                const isActive = chip.dataset.active === 'true';

                // Reset all chips
                filterChips.forEach(c => {
                    c.dataset.active = 'false';
                    c.classList.remove('bg-blue-100', 'border-blue-500');
                    c.classList.add('border-gray-300');
                });

                if (!isActive) {
                    chip.dataset.active = 'true';
                    chip.classList.add('bg-blue-100', 'border-blue-500');
                    chip.classList.remove('border-gray-300');
                    activeFilters.state = filter;
                } else {
                    activeFilters.state = null;
                }

                applyFilters();
            });
        });

        // Dropdown listeners
        [filterLocal, filterDate, filterSubject, filterTime, filterVigilante, filterSupervisor].forEach(select => {
            select?.addEventListener('change', () => {
                activeFilters.local = filterLocal?.value || '';
                activeFilters.date = filterDate?.value || '';
                activeFilters.subject = filterSubject?.value || '';
                activeFilters.time = filterTime?.value || '';
                activeFilters.vigilante = filterVigilante?.value || '';
                activeFilters.supervisor = filterSupervisor?.value || '';
                applyFilters();
            });
        });

        // Clear filters
        clearBtn?.addEventListener('click', () => {
            activeFilters = { state: null, local: '', date: '', subject: '', time: '', vigilante: '', supervisor: '' };
            filterChips.forEach(c => {
                c.dataset.active = 'false';
                c.classList.remove('bg-blue-100', 'border-blue-500');
                c.classList.add('border-gray-300');
            });
            [filterLocal, filterDate, filterSubject, filterTime, filterVigilante, filterSupervisor].forEach(s => {
                if (s) s.value = '';
            });
            applyFilters();
        });

        // Função para verificar estado do júri
        function getJuryState(row) {
            const vigilantesCount = parseInt(row.dataset.vigilantesCount || '0', 10);
            const minVigilantes = parseInt(row.dataset.minVigilantes || '1', 10);
            const hasSupervisor = row.dataset.hasSupervisor === 'true';

            if (vigilantesCount === 0) return 'no-vigilante';
            if (!hasSupervisor) return 'no-supervisor';
            if (vigilantesCount < minVigilantes) return 'incomplete';
            return 'complete';
        }

        // Aplicar filtros
        function applyFilters() {
            let visibleCount = 0;
            let totalCount = 0;
            const visibleLocations = new Set();

            allJuryRows.forEach(row => {
                totalCount++;
                let visible = true;

                // Filter by state
                if (activeFilters.state) {
                    const juryState = getJuryState(row);
                    if (activeFilters.state !== juryState) {
                        visible = false;
                    }
                }

                // Filter by location
                if (visible && activeFilters.local) {
                    const rowLocal = row.dataset.location || '';
                    if (rowLocal !== activeFilters.local) visible = false;
                }

                // Filter by date
                if (visible && activeFilters.date) {
                    const rowDate = row.dataset.examDate || '';
                    if (rowDate !== activeFilters.date) visible = false;
                }

                // Filter by subject
                if (visible && activeFilters.subject) {
                    const rowSubject = row.dataset.subject || '';
                    if (rowSubject !== activeFilters.subject) visible = false;
                }

                // Filter by time
                if (visible && activeFilters.time) {
                    const rowTime = row.dataset.startTime || '';
                    if (rowTime !== activeFilters.time) visible = false;
                }

                // Filter by vigilante
                if (visible && activeFilters.vigilante) {
                    const vigilantes = (row.dataset.vigilanteIds || '').split(',');
                    if (!vigilantes.includes(activeFilters.vigilante)) visible = false;
                }

                // Filter by supervisor
                if (visible && activeFilters.supervisor) {
                    const supervisor = row.dataset.supervisorId || '';
                    if (supervisor !== activeFilters.supervisor) visible = false;
                }

                // Apply visibility
                row.style.display = visible ? '' : 'none';
                if (visible) {
                    visibleCount++;
                    visibleLocations.add(row.dataset.location);
                }
            });

            // Location header visibility
            locationRows.forEach(row => {
                const loc = row.dataset.location || '';
                row.style.display = visibleLocations.has(loc) ? '' : 'none';
            });

            // Subtotal visibility
            subtotalRows.forEach(row => {
                const loc = row.dataset.location || '';
                row.style.display = visibleLocations.has(loc) ? '' : 'none';
            });

            // Update counter
            if (visibleCountEl) visibleCountEl.textContent = visibleCount;
            if (totalCountEl) totalCountEl.textContent = totalCount;
        }

        // Contar estados para badges
        function updateStateCounts() {
            let noVigilante = 0, noSupervisor = 0, incomplete = 0, complete = 0;

            allJuryRows.forEach(row => {
                const state = getJuryState(row);
                switch (state) {
                    case 'no-vigilante': noVigilante++; break;
                    case 'no-supervisor': noSupervisor++; break;
                    case 'incomplete': incomplete++; break;
                    case 'complete': complete++; break;
                }
            });

            document.getElementById('count-no-vigilante').textContent = noVigilante;
            document.getElementById('count-no-supervisor').textContent = noSupervisor;
            document.getElementById('count-incomplete').textContent = incomplete;
            document.getElementById('count-complete').textContent = complete;
        }

        // Inicializar
        updateStateCounts();
        applyFilters();
        console.log('✅ Módulo de filtros inicializado');
        // ========== EVENT LISTENERS ADICIONAIS ==========
        document.addEventListener('click', function (e) {
            // Remover Vigilante
            const removeVigBtn = e.target.closest('.remove-vigilante');
            if (removeVigBtn) {
                const juryId = removeVigBtn.dataset.juryId;
                const vigId = removeVigBtn.dataset.vigilanteId;
                removeVigilante(juryId, vigId);
            }

            // Eliminar Júri
            const deleteJuryBtn = e.target.closest('.delete-jury-btn');
            if (deleteJuryBtn) {
                const juryId = deleteJuryBtn.dataset.juryId;
                // Tentar obter nome da sala para confirmação
                const row = deleteJuryBtn.closest('tr');
                let roomName = 'Sala';
                if (row) {
                    const roomCell = row.querySelector('td:nth-child(4)'); // Index 4 é Sala nas colunas específicas
                    if (roomCell) roomName = roomCell.textContent.trim();
                }
                deleteJury(juryId, roomName);
            }

            // Editar Júri
            const editJuryBtn = e.target.closest('.edit-jury-btn');
            if (editJuryBtn) {
                const juryId = editJuryBtn.dataset.juryId;
                openEditJuryModal(juryId);
            }
        });

    })();
</script>