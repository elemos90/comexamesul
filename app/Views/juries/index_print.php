<?php
$title = 'Calendário de Vigilância - Universidade Licungo';
$breadcrumbs = [
    ['label' => 'Júris'],
    ['label' => 'Calendário']
];
$canManage = in_array($user['role'], ['coordenador', 'membro'], true);
$isVigilante = $user['role'] === 'vigilante';

// Agrupar júris por data para o calendário
$juriesByDate = [];
foreach ($groupedJuries as $group) {
    $date = $group['exam_date'];
    if (!isset($juriesByDate[$date])) {
        $juriesByDate[$date] = [];
    }
    $juriesByDate[$date][] = $group;
}
ksort($juriesByDate);
?>

<!-- Cabeçalho e Botões (ocultos na impressão) -->
<div class="no-print space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <!-- NOVO: Banner de Filtro por Vaga com Dropdown -->
    <?php if (!empty($vacancy)): ?>
        <div class="bg-blue-50 border-l-4 border-blue-600 p-4 rounded-lg shadow-sm">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                    </svg>
                    <div>
                        <p class="text-sm font-semibold text-blue-900">📋 Mostrando júris de:</p>
                        <p class="text-sm text-blue-800 font-medium"><?= htmlspecialchars($vacancy['title']) ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <select onchange="window.location.href='<?= url('/juries?vacancy_id=') ?>'+this.value"
                        class="px-3 py-2 text-sm border border-blue-300 rounded-lg bg-white text-gray-700 hover:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="current" <?= ($vacancyId ?? 'current') === $vacancyId ? 'selected' : '' ?>>📌 Vaga Atual
                        </option>
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
        <div class="bg-yellow-50 border-l-4 border-yellow-500 p-4 rounded-lg shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-yellow-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <p class="text-sm font-semibold text-yellow-900">⚠️ Nenhuma vaga aberta no momento</p>
                    <p class="text-xs text-yellow-800 mt-1">Não há vagas abertas. Crie uma nova vaga ou selecione do
                        histórico.</p>
                </div>
                <?php if (!empty($allVacancies)): ?>
                    <select onchange="window.location.href='<?= url('/juries?vacancy_id=') ?>'+this.value"
                        class="px-3 py-2 text-sm border border-yellow-300 rounded-lg bg-white text-gray-700">
                        <option value="">Ver Histórico</option>
                        <?php foreach ($allVacancies as $v): ?>
                            <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['title']) ?> (<?= ucfirst($v['status']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <div class="flex items-center justify-between">
        <div>
            <?php
            // Título dinâmico da tela baseado na vaga selecionada
            if (!empty($vacancy)) {
                $screenTitle = '📋 ' . htmlspecialchars($vacancy['title']);
            } else {
                $screenTitle = '📋 Calendário de Vigilância aos Exames ' . date('Y');
            }
            ?>
            <h1 class="text-2xl font-semibold text-gray-800"><?= $screenTitle ?></h1>
            <p class="text-sm text-gray-500">Documento oficial para impressão e assinaturas</p>
            <p class="text-xs text-amber-600 mt-1">💡 Dica: Ao imprimir, desative "Cabeçalhos e rodapés" nas opções do
                navegador</p>
        </div>
        <div class="flex gap-2">
            <button onclick="window.print()"
                class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-700 flex items-center gap-2 shadow-md"
                title="Nas opções de impressão, desative 'Cabeçalhos e rodapés' para melhor resultado">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir PDF
            </button>
            <a href="<?= url('/juries/export/excel') ?>"
                class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-700 flex items-center gap-2 shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Exportar Excel
            </a>
        </div>
    </div>
</div>

<!-- Documento para Impressão -->
<div class="print-document">
    <!-- Cabeçalho Institucional Compacto (apenas na impressão) -->
    <div class="print-header">
        <div class="text-center mb-2 border-b pb-2">
            <!-- Logo da instituição -->
            <?php
            $logoPath = '/uploads/institution-logo.png';
            $logoExists = file_exists(public_path($logoPath));
            ?>

            <div class="flex justify-center mb-1">
                <div class="relative group">
                    <div id="logo-container"
                        class="w-16 h-16 flex items-center justify-center <?= $canManage ? 'cursor-pointer hover:opacity-80 transition-opacity' : '' ?>"
                        <?= $canManage ? 'onclick="document.getElementById(\'logo-upload\').click()" title="Clique para carregar o logo da instituição"' : '' ?>>
                        <?php if ($logoExists): ?>
                            <img src="<?= url($logoPath) ?>?v=<?= time() ?>" alt="Logo"
                                class="w-full h-full object-contain">
                        <?php else: ?>
                            <div class="w-14 h-14 bg-primary-100 rounded-full flex items-center justify-center">
                                <span class="text-xl font-bold text-primary-600">UL</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($canManage): ?>
                        <!-- Input file escondido -->
                        <input type="file" id="logo-upload" accept="image/png,image/jpeg,image/jpg" class="hidden"
                            onchange="uploadInstitutionLogo(this)">
                    <?php endif; ?>
                </div>
            </div>

            <h2 class="text-sm font-bold text-gray-900 leading-tight">UNIVERSIDADE LICUNGO</h2>
            <p class="text-xs text-gray-700">Vice Reitoria Académica • Direcção Académica</p>
            <p class="text-xs text-gray-600">Comissão de Coordenação dos Exames de Admissão - <?= date('Y') ?></p>
        </div>

        <div class="bg-primary-800 text-white py-2 px-4 mb-3 text-center">
            <?php
            // Título dinâmico baseado na vaga selecionada
            if (!empty($vacancy)) {
                $documentTitle = strtoupper(htmlspecialchars($vacancy['title']));
            } else {
                $documentTitle = 'CALENDÁRIO DE VIGILÂNCIA AOS EXAMES DE ADMISSÃO ' . date('Y');
            }
            ?>
            <h1 class="text-sm font-bold uppercase"><?= $documentTitle ?></h1>
        </div>
    </div>

    <!-- Tabela Única com Cabeçalho Geral -->
    <div>
        <table class="jury-table">
            <thead>
                <tr>
                    <th style="width: 10%">DIA</th>
                    <th style="width: 7%">HORA</th>
                    <th style="width: 18%">EXAME</th>
                    <th style="width: 20%">SALAS</th>
                    <th style="width: 8%">Nº Cand</th>
                    <th style="width: 30%">VIGILANTE</th>
                    <th style="width: 7%">Nº Vigias</th>
                    <th style="width: 10%" class="no-print">AÇÕES</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // CÁLCULO PRÉVIO: Totais por Disciplina
                $disciplineTotals = [];
                if (!empty($groupedJuries)) {
                    foreach ($groupedJuries as $g) {
                        $subj = $g['subject'];
                        if (!isset($disciplineTotals[$subj])) {
                            $disciplineTotals[$subj] = ['candidates' => 0, 'vigilantes' => 0, 'juries' => 0];
                        }

                        foreach ($g['locations'] as $loc) {
                            foreach ($loc['juries'] as $j) {
                                $disciplineTotals[$subj]['candidates'] += (int) $j['candidates_quota'];
                                $disciplineTotals[$subj]['vigilantes'] += count($j['vigilantes'] ?? []);
                                $disciplineTotals[$subj]['juries']++;
                            }
                        }
                    }
                }

                $totalGeralCandidatos = 0;
                $totalGeralVigilantes = 0;

                // Re-indexar array para garantir consistência nas chaves numéricas
                $groupedJuries = array_values($groupedJuries);

                foreach ($groupedJuries as $groupIndex => $group):
                    // Calcular rowspan total do grupo principal (Exam/Subject/Time)
                    // Rowspan = Para cada local -> (1 header + N juries + 1 subtotal)
                    $totalRowspan = 0;
                    foreach ($group['locations'] as $loc) {
                        $totalRowspan += 1; // Header do local
                        $totalRowspan += count($loc['juries']); // Salas
                        $totalRowspan += 1; // Subtotal do local
                    }

                    // Variável para controlar a impressão das colunas principais (Date/Time/Exam)
                    $printMainColumns = true;

                    // Iterar por locais
                    foreach ($group['locations'] as $locationName => $locData):
                        // Header do Local (Amarelo)
                        ?>
                        <tr class="location-header">
                            <?php if ($printMainColumns):
                                // Dias da semana em Português
                                $diasSemana = [
                                    'Monday' => 'Segunda',
                                    'Tuesday' => 'Terça',
                                    'Wednesday' => 'Quarta',
                                    'Thursday' => 'Quinta',
                                    'Friday' => 'Sexta',
                                    'Saturday' => 'Sábado',
                                    'Sunday' => 'Domingo'
                                ];
                                $diaIngles = date('l', strtotime($group['exam_date']));
                                $diaPortugues = $diasSemana[$diaIngles] ?? $diaIngles;
                                ?>
                                <td rowspan="<?= $totalRowspan ?>" class="text-center font-semibold"
                                    style="vertical-align: middle; background-color: white;">
                                    <?= date('d/m/Y', strtotime($group['exam_date'])) ?><br>
                                    <span class="text-xs">(<?= $diaPortugues ?>)</span>
                                </td>
                                <td rowspan="<?= $totalRowspan ?>" class="text-center font-semibold"
                                    style="vertical-align: middle; background-color: white;">
                                    <?= date('H:i', strtotime($group['start_time'])) ?> -
                                    <?= date('H:i', strtotime($group['end_time'])) ?>
                                </td>
                                <td rowspan="<?= $totalRowspan ?>" class="text-center font-bold bg-gray-50"
                                    style="vertical-align: middle; background-color: white;">
                                    <?= htmlspecialchars(strtoupper($group['subject'])) ?>
                                </td>
                                <?php $printMainColumns = false; endif; ?>

                            <!-- Coluna de Salas (agora ocupada pelo Header do Local) -->
                            <td colspan="5"
                                style="background-color: #f9fafb; font-weight: 600; text-align: center; color: #374151; border-top: 1px solid #e5e7eb;">
                                <?= strtoupper(htmlspecialchars($locationName)) ?>
                            </td>
                        </tr>

                        <?php
                        // Dados do subtotal DESTE local
                        $locCandidatos = 0;
                        $locVigilantes = 0;
                        $locSupervisors = [];

                        // Iterar salas deste local
                        foreach ($locData['juries'] as $jury):
                            $vigilantesCount = count($jury['vigilantes'] ?? []);
                            $locCandidatos += $jury['candidates_quota'];
                            $locVigilantes += $vigilantesCount;

                            // Coletar supervisor para subtotal do local
                            if (!empty($jury['supervisor_name'])) {
                                $sid = $jury['supervisor_id'];
                                if (!isset($locSupervisors[$sid])) {
                                    $locSupervisors[$sid] = [
                                        'name' => $jury['supervisor_name'],
                                        'phone' => $jury['supervisor_phone'] ?? null
                                    ];
                                }
                            }
                            ?>
                            <tr>
                                <td class="text-center"><?= htmlspecialchars($jury['room']) ?></td>
                                <td class="text-center font-semibold"><?= number_format($jury['candidates_quota'], 0) ?></td>
                                <td style="font-size: 0.75rem; line-height: 1.3;">
                                    <?php if (!empty($jury['vigilantes'])): ?>
                                        <?php foreach ($jury['vigilantes'] as $v): ?>
                                            <?= htmlspecialchars($v['name']) ?><br>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center"><?= $vigilantesCount ?></td>
                                <td class="text-center no-print">
                                    <a href="<?= url('/juries/' . $jury['id'] . '/report') ?>"
                                        class="text-blue-600 hover:text-blue-800 text-xs font-bold uppercase">
                                        Relatório
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                        <!-- Subtotal do Local -->
                        <tr class="subtotal-row">
                            <td class="text-center font-bold" style="background-color: #fafafa; border-top: 1px solid #e5e7eb;">
                                Subtotal</td>
                            <td class="text-center font-bold" style="background-color: #fafafa; border-top: 1px solid #e5e7eb;">
                                <?= number_format($locCandidatos, 0) ?>
                            </td>
                            <td class="text-center"
                                style="font-size: 0.75rem; background-color: #fafafa; border-top: 1px solid #e5e7eb;">
                                <?php if (!empty($locSupervisors)): ?>
                                    <div class="space-y-1">
                                        <?php foreach ($locSupervisors as $sup): ?>
                                            <div>
                                                <strong>Supervisor:</strong> <?= htmlspecialchars($sup['name']) ?>
                                                <?php if ($sup['phone']): ?>
                                                    <span class="text-xs"> • <?= htmlspecialchars($sup['phone']) ?></span>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="text-gray-500 italic text-xs">Sem supervisor</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center font-bold" style="background-color: #fafafa; border-top: 1px solid #e5e7eb;">
                                <?= $locVigilantes ?></td>
                            <td class="no-print" style="background-color: #fafafa; border-top: 1px solid #e5e7eb;"></td>
                        </tr>

                        <?php
                        $totalGeralCandidatos += $locCandidatos;
                        $totalGeralVigilantes += $locVigilantes;
                        ?>

                    <?php endforeach; // Fim loop locais ?>

                    <?php
                    // LÓGICA DE EXIBIÇÃO DO TOTAL DA DISCIPLINA
                    $currentSubject = $group['subject'];
                    $nextGroup = $groupedJuries[$groupIndex + 1] ?? null;
                    $nextSubject = $nextGroup['subject'] ?? null;

                    if ($currentSubject !== $nextSubject && isset($disciplineTotals[$currentSubject])):
                        $totals = $disciplineTotals[$currentSubject];
                        ?>
                        <tr class="discipline-total-row"
                            style="background-color: #f3f4f6; -webkit-print-color-adjust: exact; print-color-adjust: exact;">
                            <td colspan="8"
                                style="padding: 6px 12px; border-top: 1px solid #d1d5db; border-bottom: 1px solid #d1d5db;">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <span style="font-weight: 600; color: #1f2937; font-size: 10pt; text-transform: uppercase;">
                                        TOTAL <?= strtoupper($currentSubject) ?>
                                    </span>
                                    <div style="display: flex; gap: 24px; font-weight: 600; color: #374151;">
                                        <span>Candidatos: <?= number_format($totals['candidates'], 0) ?></span>
                                        <span>Júris: <?= $totals['juries'] ?></span>
                                        <span>Vigilantes: <?= $totals['vigilantes'] ?></span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>

                <?php endforeach; // Fim loop grupos principais ?>

                <!-- Total Geral -->
                <tr class="total-row">
                    <td colspan="3"
                        style="text-align: right; font-weight: 600; padding-right: 0.5rem; background-color: #e5e7eb; color: #111827; border-top: 1px solid #d1d5db;">
                        TOTAL GERAL</td>
                    <td style="text-align: center; background-color: #e5e7eb; border-top: 1px solid #d1d5db;"></td>
                    <td
                        style="text-align: center; font-weight: 600; background-color: #e5e7eb; color: #111827; border-top: 1px solid #d1d5db;">
                        <?= number_format($totalGeralCandidatos, 0) ?>
                    </td>
                    <td style="background-color: #e5e7eb; border-top: 1px solid #d1d5db;"></td>
                    <td
                        style="text-align: center; font-weight: 600; background-color: #e5e7eb; color: #111827; border-top: 1px solid #d1d5db;">
                        <?= $totalGeralVigilantes ?>
                    </td>
                    <td class="no-print" style="background-color: #e5e7eb; border-top: 1px solid #d1d5db;"></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Rodapé com Assinaturas (apenas na impressão) -->
    <div class="print-footer mt-12">
        <div class="grid grid-cols-2 gap-8">
            <div>
                <p class="text-sm font-semibold mb-8">O/A Coordenador/a,</p>
                <div class="border-t border-gray-800 pt-1">
                    <p class="text-xs text-center">(<?= htmlspecialchars($user['name']) ?>)</p>
                </div>
            </div>
            <div>
                <p class="text-sm mb-2">Data: ____ / ____ / <?= date('Y') ?></p>
                <p class="text-sm mb-2">Local: _________________________________</p>
            </div>
        </div>
    </div>
</div>

<style>
    /* Reset & Base */
    @media print {
        @page {
            size: A4 portrait;
            margin: 1.5cm 1cm;
        }

        /* HIDE EVERYTHING ELSE */
        body,
        html {
            height: auto;
            overflow: visible;
        }

        body * {
            visibility: hidden;
        }

        /* SHOW ONLY THE PRINT DOCUMENT */
        .print-document,
        .print-document * {
            visibility: visible;
        }

        .print-document {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            margin: 0;
            padding: 0;
            background: white;
            z-index: 9999;
        }

        /* Force Hide Specific Elements */
        nav,
        aside,
        .sidebar,
        .navbar,
        header,
        footer,
        .modal,
        .no-print,
        button {
            display: none !important;
        }

        /* Ensure tables break correctly */
        .official-table {
            page-break-inside: auto;
        }

        .official-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .official-table thead {
            display: table-header-group;
        }

        .official-table tfoot {
            display: table-footer-group;
        }

        /* Ensure colors print */
        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }
    }

    /* Screen-only styles */
    .no-print {
        margin-bottom: 2rem;
    }

    /* Document Container on Screen */
    .print-document {
        background: white;
        padding: 0;
        max-width: 100%;
    }

    /* Institutional Header */
    .inst-header {
        text-align: center;
        margin-bottom: 1.5rem;
        font-family: Arial, Helvetica, sans-serif;
    }

    .inst-logo {
        height: 80px;
        margin-bottom: 10px;
        object-fit: contain;
    }

    /* Official Table Styles */
    .official-table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2rem;
        font-size: 10pt;
    }

    .official-table th,
    .official-table td {
        border: 1px solid #000;
        padding: 4px 6px;
        vertical-align: middle;
    }

    .official-table thead th {
        background-color: #d9d9d9 !important;
        color: #000;
        font-weight: bold;
        text-transform: uppercase;
        text-align: center;
    }

    /* Hierarchy Styles - Formal Ultra-Suave */
    .location-header td {
        background-color: #f9fafb !important;
        font-weight: 600;
        text-transform: uppercase;
        color: #374151;
    }

    .subtotal-row td {
        background-color: #fafafa !important;
        font-weight: 600;
    }

    .total-row td {
        background-color: #e5e7eb !important;
        font-weight: 600;
        text-transform: uppercase;
        color: #111827;
    }

    .text-center {
        text-align: center;
    }

    .text-right {
        text-align: right;
    }

    .font-bold {
        font-weight: bold;
    }
</style>