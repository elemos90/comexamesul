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
            <a href="url('/juries/export/excel')"
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
    <!-- Cabeçalho Institucional (apenas na impressão) -->
    <div class="print-header">
        <div class="text-center mb-6">
            <div class="flex justify-center mb-3">
                <!-- Logo da instituição -->
                <?php
                $logoPath = '/uploads/institution-logo.png';
                $logoExists = file_exists(public_path($logoPath));
                ?>

                <div class="relative group">
                    <div id="logo-container"
                        class="w-32 h-32 flex items-center justify-center <?= $canManage ? 'cursor-pointer hover:opacity-80 transition-opacity' : '' ?>"
                        <?= $canManage ? 'onclick="document.getElementById(\'logo-upload\').click()" title="Clique para carregar o logo da instituição"' : '' ?>>
                        <?php if ($logoExists): ?>
                            <img src="<?= url($logoPath) ?>?v=<?= time() ?>" alt="Logo"
                                class="w-full h-full object-contain">
                        <?php else: ?>
                            <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center">
                                <span class="text-3xl font-bold text-primary-600">UL</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($canManage): ?>
                        <!-- Input file escondido -->
                        <input type="file" id="logo-upload" accept="image/png,image/jpeg,image/jpg" class="hidden"
                            onchange="uploadInstitutionLogo(this)">

                        <!-- Tooltip na tela -->
                        <div
                            class="no-print absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity whitespace-nowrap">
                            📸 Clique para carregar logo
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <h2 class="text-lg font-bold text-gray-900">UNIVERSIDADE LICUNGO</h2>
            <p class="text-sm text-gray-700 font-medium">VICE REITORIA ACADÉMICA</p>
            <p class="text-sm text-gray-700">DIRECÇÃO ACADÉMICA</p>
            <p class="text-xs text-gray-600 mt-1">Comissão de Coordenação dos Exames de Admissão - <?= date('Y') ?></p>
        </div>

        <div class="bg-primary-800 text-white py-3 px-4 mb-6">
            <?php
            // Título dinâmico baseado na vaga selecionada
            if (!empty($vacancy)) {
                $documentTitle = strtoupper(htmlspecialchars($vacancy['title']));
            } else {
                $documentTitle = 'CALENDÁRIO DE VIGILÂNCIA AOS EXAMES DE ADMISSÃO ' . date('Y');
            }
            ?>
            <h1 class="text-center text-base font-bold uppercase"><?= $documentTitle ?></h1>
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
                $totalGeralCandidatos = 0;
                $totalGeralVigilantes = 0;

                foreach ($groupedJuries as $group):
                    // Pegar o local do grupo ou do primeiro júri
                    $location = $group['location'] ?? $group['juries'][0]['location'] ?? null;

                    // Pular se não houver local definido
                    if (empty($location)) {
                        $location = 'Local não especificado';
                    }

                    $examCandidatos = 0;
                    $examVigilantes = 0;
                    $rowspan = count($group['juries']);
                    ?>
                    <!-- Cabeçalho do Local -->
                    <tr class="location-header">
                        <td colspan="8"><?= strtoupper(htmlspecialchars($location)) ?></td>
                    </tr>

                    <?php
                    $firstRow = true;

                    foreach ($group['juries'] as $index => $jury):
                        $vigilantesCount = count($jury['vigilantes'] ?? []);
                        $examCandidatos += $jury['candidates_quota'];
                        $examVigilantes += $vigilantesCount;
                        ?>
                        <tr>
                            <?php if ($firstRow):
                                // Dias da semana em Português de Portugal
                                $diasSemana = [
                                    'Monday' => 'Segunda',
                                    'Tuesday' => 'Terça',
                                    'Wednesday' => 'Quarta',
                                    'Thursday' => 'Quinta',
                                    'Friday' => 'Sexta',
                                    'Saturday' => 'Sábado',
                                    'Sunday' => 'Domingo'
                                ];
                                $diaIngles = date('l', strtotime($jury['exam_date']));
                                $diaPortugues = $diasSemana[$diaIngles] ?? $diaIngles;
                                ?>
                                <td rowspan="<?= $rowspan + 1 ?>" class="text-center font-semibold">
                                    <?= date('d/m/Y', strtotime($jury['exam_date'])) ?><br>
                                    <span class="text-xs">(<?= $diaPortugues ?>)</span>
                                </td>
                                <td rowspan="<?= $rowspan + 1 ?>" class="text-center font-semibold">
                                    <?= date('H:i', strtotime($jury['start_time'])) ?>
                                </td>
                                <td rowspan="<?= $rowspan + 1 ?>" class="text-center font-bold bg-gray-50">
                                    <?= htmlspecialchars(strtoupper($group['subject'])) ?>
                                </td>
                                <?php $firstRow = false; endif; ?>

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

                    <!-- Subtotal do Exame -->
                    <tr class="subtotal-row">
                        <td class="text-center font-bold">Subtotal</td>
                        <td class="text-center font-bold"><?= number_format($examCandidatos, 0) ?></td>
                        <td class="text-center" style="font-size: 0.75rem;">
                            <?php
                            // Coletar supervisores únicos deste bloco
                            $supervisors = [];
                            foreach ($group['juries'] as $j) {
                                if (!empty($j['supervisor_name'])) {
                                    $id = $j['supervisor_id'];
                                    if (!isset($supervisors[$id])) {
                                        $supervisors[$id] = [
                                            'name' => $j['supervisor_name'],
                                            'phone' => $j['supervisor_phone'] ?? null,
                                            'count' => 0
                                        ];
                                    }
                                    $supervisors[$id]['count']++;
                                }
                            }
                            ?>

                            <?php if (!empty($supervisors)): ?>
                                <div class="space-y-1">
                                    <?php foreach ($supervisors as $sup): ?>
                                        <div class="text-blue-900">
                                            <strong>Supervisor:</strong> <?= htmlspecialchars($sup['name']) ?>
                                            <?php if ($sup['phone']): ?>
                                                <span class="text-xs text-gray-600"> • <?= htmlspecialchars($sup['phone']) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <span class="text-gray-400 italic text-xs">Sem supervisor</span>
                            <?php endif; ?>


                        </td>
                        <td class="text-center font-bold"><?= $examVigilantes ?></td>
                        <td class="no-print"></td>
                    </tr>

                    <?php
                    $totalGeralCandidatos += $examCandidatos;
                    $totalGeralVigilantes += $examVigilantes;
                    ?>
                <?php endforeach; ?>

                <!-- Total Geral -->
                <tr class="total-row">
                    <td colspan="3" style="text-align: right; font-weight: bold; padding-right: 0.5rem;">TOTAL GERAL
                    </td>
                    <td style="text-align: center;"></td>
                    <td style="text-align: center; font-weight: bold;"><?= number_format($totalGeralCandidatos, 0) ?>
                    </td>
                    <td></td>
                    <td style="text-align: center; font-weight: bold;"><?= $totalGeralVigilantes ?></td>
                    <td class="no-print"></td>
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
    /* Estilos para tela */
    .no-print {
        margin-bottom: 2rem;
    }

    .print-document {
        background: white;
        padding: 2rem;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .print-header {
        margin-bottom: 2rem;
    }

    .jury-table {
        width: 100%;
        max-width: 100%;
        border-collapse: collapse;
        margin-bottom: 1.5rem;
        font-size: 0.813rem;
        table-layout: fixed;
    }

    .jury-table th {
        background-color: #1e3a8a;
        color: white;
        padding: 0.6rem 0.3rem;
        text-align: center;
        font-weight: 600;
        font-size: 0.75rem;
        border: 1px solid #1e40af;
        word-wrap: break-word;
    }

    .jury-table td {
        padding: 0.4rem 0.3rem;
        border: 1px solid #d1d5db;
        word-wrap: break-word;
        overflow-wrap: break-word;
        vertical-align: middle;
    }

    .location-header td {
        background-color: #fbbf24;
        font-weight: 700;
        text-align: center;
        padding: 0.5rem 1rem;
        font-size: 0.813rem;
    }

    .subtotal-row td {
        background-color: #fef3c7;
        font-weight: 600;
        padding: 0.5rem 0.3rem;
        vertical-align: middle;
    }

    .subtotal-row td:first-child {
        text-align: center;
    }

    .subtotal-row td:nth-child(2) {
        text-align: center;
    }

    .total-row td {
        background-color: #fed7aa;
        font-weight: 700;
        padding: 0.6rem 0.3rem;
    }

    /* Estilos para impressão */
    @media print {
        @page {
            size: A4;
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
            /* Remove cabeçalho e rodapé do navegador */
            marks: none;
        }

        @page :first {
            margin-top: 1.5cm;
        }

        html {
            margin: 0;
            padding: 0;
        }

        body {
            margin: 0 !important;
            padding: 0 !important;
            overflow: hidden;
            position: relative;
        }

        /* Ocultar elementos indesejados na impressão */
        .no-print,
        nav,
        header,
        .sidebar,
        .scrollbar,
        ::-webkit-scrollbar,
        aside,
        [class*="sidebar"],
        [class*="nav"],
        [id*="sidebar"],
        [id*="nav"] {
            display: none !important;
        }

        /* Ocultar cabeçalho do navegador/portal */
        body>header,
        body>nav,
        body>aside,
        #header,
        #nav,
        #sidebar,
        .header,
        .navbar,
        .top-bar,
        .breadcrumb,
        .breadcrumbs {
            display: none !important;
        }

        /* Ocultar scrollbars completamente */
        ::-webkit-scrollbar {
            display: none !important;
            width: 0 !important;
            height: 0 !important;
        }

        * {
            -ms-overflow-style: none !important;
            scrollbar-width: none !important;
        }

        /* Garantir que apenas o conteúdo principal seja visível */
        html,
        body {
            width: 100%;
            height: auto;
            overflow: visible;
        }

        * {
            overflow: visible !important;
        }

        /* Container principal */
        main,
        .container,
        .content {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: none !important;
        }

        .print-document {
            padding: 0;
            box-shadow: none;
            width: 100%;
            max-width: none;
            margin: 0;
        }

        .jury-table {
            page-break-inside: auto;
            font-size: 8.5pt;
            table-layout: fixed;
        }

        .jury-table thead {
            display: table-header-group;
        }

        .jury-table tbody {
            display: table-row-group;
        }

        .jury-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .jury-table th {
            background-color: #1e3a8a !important;
            color: white !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            padding: 0.4rem 0.2rem;
            font-size: 7.5pt;
        }

        .jury-table td {
            padding: 0.3rem 0.2rem;
            font-size: 8pt;
        }

        .location-header {
            page-break-after: avoid;
        }

        .location-header td {
            background-color: #fbbf24 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .subtotal-row {
            page-break-before: avoid;
            page-break-after: auto;
        }

        .subtotal-row td {
            background-color: #fef3c7 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            padding: 0.3rem 0.2rem;
            font-size: 8pt;
        }

        .total-row {
            page-break-before: avoid;
        }

        .total-row td {
            background-color: #fed7aa !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            padding: 0.4rem 0.2rem;
            font-size: 8.5pt;
        }

        .print-footer {
            page-break-inside: avoid;
            page-break-before: auto;
            margin-top: 2rem;
        }

        .print-header {
            page-break-after: avoid;
        }
    }
</style>

<script>
    async function uploadInstitutionLogo(input) {
        const file = input.files[0];

        if (!file) return;

        // Validar tipo de arquivo
        if (!['image/png', 'image/jpeg', 'image/jpg'].includes(file.type)) {
            alert('❌ Por favor, selecione apenas imagens PNG ou JPG');
            input.value = '';
            return;
        }

        // Validar tamanho (máx 2MB)
        if (file.size > 2 * 1024 * 1024) {
            alert('❌ A imagem deve ter no máximo 2MB');
            input.value = '';
            return;
        }

        const formData = new FormData();
        formData.append('logo', file);
        formData.append('csrf', '<?= \App\Utils\Csrf::token() ?>');

        // Mostrar loading no logo
        const container = document.getElementById('logo-container');
        const originalContent = container.innerHTML;
        container.innerHTML = `
        <svg class="animate-spin h-8 w-8 text-primary-600" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
    `;

        try {
            const response = await fetch('/settings/upload-logo', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                // Atualizar logo
                container.innerHTML = `<img src="${result.logoUrl}?v=${Date.now()}" alt="Logo" class="w-full h-full object-cover">`;

                // Toast de sucesso
                if (typeof toastr !== 'undefined') {
                    toastr.success('Logo carregado com sucesso!', 'Sucesso');
                } else {
                    alert('✅ Logo carregado com sucesso!');
                }
            } else {
                container.innerHTML = originalContent;
                alert('❌ Erro: ' + result.message);
            }
        } catch (error) {
            console.error('Erro ao carregar logo:', error);
            container.innerHTML = originalContent;
            alert('❌ Erro ao carregar logo. Tente novamente.');
        }

        // Limpar input
        input.value = '';
    }
</script>