<?php
/**
 * Relatório Consolidado de Exames
 * 
 * Esta view é renderizada dentro do layout principal (main.php)
 * que já inclui a navbar, sidebar e CSS compilado do Tailwind.
 */

$title = 'Relatório Consolidado de Exames';
$breadcrumbs = [
    ['label' => 'Relatórios', 'href' => url('/reports')],
    ['label' => 'Consolidado']
];

/** @var array $vacancies */
/** @var array $locations */
/** @var array $disciplines */
/** @var array $user */
/** @var bool $canValidate */
?>

<div class="space-y-5">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <!-- Cabeçalho -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Relatório Consolidado de Exames</h1>
            <p class="text-xs text-gray-500">Agregação estatística de todos os exames realizados</p>
        </div>
        <button onclick="window.print()" class="btn btn-secondary no-print">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
            </svg>
            Imprimir
        </button>
    </div>

    <!-- Filtros -->
    <div class="bg-white border border-gray-200 rounded-lg p-4 no-print">
        <h2 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
            </svg>
            Filtros
        </h2>
        <form id="filters-form" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Vaga/Processo</label>
                <select name="vacancy_id" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                    <option value="">Todas</option>
                    <?php foreach ($vacancies as $v): ?>
                        <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Local</label>
                <select name="location" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                    <option value="">Todos</option>
                    <?php foreach ($locations as $loc): ?>
                        <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Disciplina</label>
                <select name="discipline" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
                    <option value="">Todas</option>
                    <?php foreach ($disciplines as $disc): ?>
                        <option value="<?= htmlspecialchars($disc) ?>"><?= htmlspecialchars($disc) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Início</label>
                <input type="date" name="date_from" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Data Fim</label>
                <input type="date" name="date_to" class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm">
            </div>
            <div class="flex items-end">
                <button type="submit" class="btn btn-primary w-full">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                    Gerar
                </button>
            </div>
        </form>
    </div>

    <!-- Conteúdo do Relatório (oculto inicialmente) -->
    <div id="report-content" class="hidden space-y-4">
        <!-- Cards de Resumo -->
        <div class="grid grid-cols-3 lg:grid-cols-6 gap-3">
            <div class="bg-white border border-gray-200 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-blue-600" id="card-exames">0</div>
                <div class="text-xs text-gray-500 uppercase">Exames</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-purple-600" id="card-juris">0</div>
                <div class="text-xs text-gray-500 uppercase">Júris/Salas</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-gray-600" id="card-esperados">0</div>
                <div class="text-xs text-gray-500 uppercase">Esperados</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-green-600" id="card-presentes">0</div>
                <div class="text-xs text-gray-500 uppercase">Presentes</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-orange-500" id="card-ausentes">0</div>
                <div class="text-xs text-gray-500 uppercase">Ausentes</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-3 text-center">
                <div class="text-2xl font-bold text-red-600" id="card-fraudes">0</div>
                <div class="text-xs text-gray-500 uppercase">Fraudes</div>
            </div>
        </div>

        <!-- Estatísticas por Género -->
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Estatísticas Consolidadas</h3>
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50">
                        <th class="border px-3 py-2 text-left">Indicador</th>
                        <th class="border px-3 py-2 text-center">Masculino</th>
                        <th class="border px-3 py-2 text-center">Feminino</th>
                        <th class="border px-3 py-2 text-center bg-blue-50 font-semibold">Total</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border px-3 py-2 font-medium text-green-700">✓ Presentes</td>
                        <td class="border px-3 py-2 text-center" id="stat-presentes-m">0</td>
                        <td class="border px-3 py-2 text-center" id="stat-presentes-f">0</td>
                        <td class="border px-3 py-2 text-center font-bold bg-green-50" id="stat-presentes-total">0</td>
                    </tr>
                    <tr>
                        <td class="border px-3 py-2 font-medium text-orange-700">✗ Ausentes</td>
                        <td class="border px-3 py-2 text-center" id="stat-ausentes-m">0</td>
                        <td class="border px-3 py-2 text-center" id="stat-ausentes-f">0</td>
                        <td class="border px-3 py-2 text-center font-bold bg-orange-50" id="stat-ausentes-total">0</td>
                    </tr>
                    <tr>
                        <td class="border px-3 py-2 font-medium text-red-700">⚠ Fraudes</td>
                        <td class="border px-3 py-2 text-center" id="stat-fraudes-m">0</td>
                        <td class="border px-3 py-2 text-center" id="stat-fraudes-f">0</td>
                        <td class="border px-3 py-2 text-center font-bold bg-red-50" id="stat-fraudes-total">0</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Gráficos -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Distribuição Geral</h3>
                <canvas id="chart-presence" height="180"></canvas>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Por Género</h3>
                <canvas id="chart-gender" height="180"></canvas>
            </div>
        </div>

        <!-- Por Disciplina -->
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Detalhamento por Disciplina</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="bg-gray-50">
                            <th class="border px-3 py-2 text-left">Disciplina</th>
                            <th class="border px-3 py-2 text-center">Salas</th>
                            <th class="border px-3 py-2 text-center">Esperados</th>
                            <th class="border px-3 py-2 text-center">Presentes</th>
                            <th class="border px-3 py-2 text-center">Ausentes</th>
                            <th class="border px-3 py-2 text-center">Fraudes</th>
                            <th class="border px-3 py-2 text-center">Taxa Presença</th>
                        </tr>
                    </thead>
                    <tbody id="tbody-discipline"></tbody>
                </table>
            </div>
        </div>

        <!-- Exportações -->
        <div class="bg-white border border-gray-200 rounded-lg p-4 no-print">
            <h3 class="text-sm font-semibold text-gray-700 mb-3">Exportar Relatório</h3>
            <div class="flex flex-wrap gap-2">
                <a id="btn-export-pdf" href="#" class="btn btn-primary">
                    📄 PDF
                </a>
                <a id="btn-export-excel" href="#" class="btn btn-primary" style="background-color: #16a34a;">
                    📊 Excel
                </a>
                <a id="btn-export-csv" href="#" class="btn btn-secondary">
                    📋 CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Mensagem inicial -->
    <div id="initial-message" class="bg-white border border-gray-200 rounded-lg p-8 text-center">
        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />
        </svg>
        <h3 class="text-base font-semibold text-gray-600 mb-1">Selecione os Filtros</h3>
        <p class="text-sm text-gray-500">Utilize os filtros acima e clique em "Gerar" para visualizar os dados
            consolidados.</p>
    </div>
</div>

<!-- Loading Overlay -->
<div id="loading-overlay" class="fixed inset-0 bg-white/90 hidden items-center justify-center z-50"
    style="display: none;">
    <div class="text-center">
        <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-3" xmlns="http://www.w3.org/2000/svg" fill="none"
            viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor"
                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
            </path>
        </svg>
        <p class="text-gray-600 text-sm">A gerar relatório...</p>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
    let chartPresence = null;
    let chartGender = null;
    let currentFilters = {};

    document.getElementById('filters-form').addEventListener('submit', async function (e) {
        e.preventDefault();
        console.log('[ConsolidatedReport] Form submitted');

        const formData = new FormData(this);
        currentFilters = Object.fromEntries(formData.entries());
        console.log('[ConsolidatedReport] Filters:', currentFilters);

        // Mostrar loading
        const overlay = document.getElementById('loading-overlay');
        overlay.style.display = 'flex';
        overlay.classList.remove('hidden');

        const controller = new AbortController();
        const timeoutId = setTimeout(() => {
            console.log('[ConsolidatedReport] Request timeout after 30s');
            controller.abort();
        }, 30000);

        try {
            const url = '<?= url('/reports/consolidated/generate') ?>';
            console.log('[ConsolidatedReport] Fetching URL:', url);

            const response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(currentFilters),
                signal: controller.signal
            });

            clearTimeout(timeoutId);
            console.log('[ConsolidatedReport] Response status:', response.status);

            const text = await response.text();
            console.log('[ConsolidatedReport] Response (first 300 chars):', text.substring(0, 300));

            let result;
            try {
                result = JSON.parse(text);
            } catch (parseError) {
                console.error('[ConsolidatedReport] JSON parse error:', parseError);
                alert('Erro: Resposta inválida do servidor. Veja o console (F12).');
                return;
            }

            if (result.success) {
                renderReport(result.data, result.charts);
                document.getElementById('report-content').classList.remove('hidden');
                document.getElementById('initial-message').classList.add('hidden');
                updateExportLinks();
            } else {
                alert('Erro: ' + result.message);
            }
        } catch (error) {
            clearTimeout(timeoutId);
            console.error('[ConsolidatedReport] Fetch error:', error);
            if (error.name === 'AbortError') {
                alert('Tempo limite excedido (30s). Servidor não respondeu.');
            } else {
                alert('Erro ao gerar relatório: ' + error.message);
            }
        } finally {
            // Esconder loading
            overlay.style.display = 'none';
            overlay.classList.add('hidden');
        }
    });

    function renderReport(data, charts) {
        // Cards
        document.getElementById('card-exames').textContent = data.summary.total_exames;
        document.getElementById('card-juris').textContent = data.summary.total_juris;
        document.getElementById('card-esperados').textContent = data.summary.total_esperados;
        document.getElementById('card-presentes').textContent = data.summary.total_presentes;
        document.getElementById('card-ausentes').textContent = data.summary.total_ausentes;
        document.getElementById('card-fraudes').textContent = data.summary.total_fraudes;

        // Estatísticas
        document.getElementById('stat-presentes-m').textContent = data.statistics.presentes.masculino;
        document.getElementById('stat-presentes-f').textContent = data.statistics.presentes.feminino;
        document.getElementById('stat-presentes-total').textContent = data.statistics.presentes.total;
        document.getElementById('stat-ausentes-m').textContent = data.statistics.ausentes.masculino;
        document.getElementById('stat-ausentes-f').textContent = data.statistics.ausentes.feminino;
        document.getElementById('stat-ausentes-total').textContent = data.statistics.ausentes.total;
        document.getElementById('stat-fraudes-m').textContent = data.statistics.fraudes.masculino;
        document.getElementById('stat-fraudes-f').textContent = data.statistics.fraudes.feminino;
        document.getElementById('stat-fraudes-total').textContent = data.statistics.fraudes.total;

        // Gráficos
        if (chartPresence) chartPresence.destroy();
        if (chartGender) chartGender.destroy();

        chartPresence = new Chart(document.getElementById('chart-presence').getContext('2d'), {
            type: 'bar',
            data: {
                labels: charts.presence_chart.labels,
                datasets: [{ label: 'Total', data: charts.presence_chart.data, backgroundColor: charts.presence_chart.colors, borderRadius: 6 }]
            },
            options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
        });

        chartGender = new Chart(document.getElementById('chart-gender').getContext('2d'), {
            type: 'bar',
            data: { labels: charts.gender_chart.labels, datasets: charts.gender_chart.datasets },
            options: { responsive: true, plugins: { legend: { position: 'top' } }, scales: { y: { beginAtZero: true } } }
        });

        // Por Disciplina
        const tbody = document.getElementById('tbody-discipline');
        tbody.innerHTML = '';
        data.by_discipline.forEach(disc => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td class="border px-3 py-2 font-medium">${disc.disciplina}</td>
                <td class="border px-3 py-2 text-center">${disc.salas}</td>
                <td class="border px-3 py-2 text-center">${disc.esperados}</td>
                <td class="border px-3 py-2 text-center text-green-600">${disc.presentes}</td>
                <td class="border px-3 py-2 text-center text-orange-600">${disc.ausentes}</td>
                <td class="border px-3 py-2 text-center text-red-600">${disc.fraudes}</td>
                <td class="border px-3 py-2 text-center">
                    <span class="px-2 py-0.5 rounded text-xs ${disc.taxa_presenca >= 80 ? 'bg-green-100 text-green-800' : disc.taxa_presenca >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">
                        ${disc.taxa_presenca}%
                    </span>
                </td>
            `;
            tbody.appendChild(row);
        });
    }

    function updateExportLinks() {
        const params = new URLSearchParams(currentFilters).toString();
        document.getElementById('btn-export-pdf').href = '<?= url('/reports/consolidated/export/pdf') ?>?' + params;
        document.getElementById('btn-export-excel').href = '<?= url('/reports/consolidated/export/excel') ?>?' + params;
        document.getElementById('btn-export-csv').href = '<?= url('/reports/consolidated/export/csv') ?>?' + params;
    }
</script>

<style>
    @media print {
        .no-print {
            display: none !important;
        }

        .print-break {
            page-break-before: always;
        }
    }
</style>