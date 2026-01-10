<?php
/** @var array $vacancies */
/** @var array $locations */
/** @var array $disciplines */
/** @var array $user */
/** @var bool $canValidate */
?>
<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório Consolidado de Exames</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            .print-break {
                page-break-before: always;
            }

            body {
                font-size: 12px;
            }

            .container {
                max-width: 100% !important;
                padding: 0 !important;
            }
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.9);
            display: flex; align-items: center; justify-content: center;
            z-index: 1000;
        }
    </style>
</head>

<body class="bg-gray-100">
    <?php include __DIR__ . '/../partials/sidebar.php'; ?>

    <main class="ml-64 p-6">
        <div class="container mx-auto max-w-7xl">

            <!-- Cabeçalho -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 flex items-center gap-3">
                            <i class="fas fa-file-alt text-blue-600"></i>
                            Relatório Consolidado de Exames
                        </h1>
                        <p class="text-gray-500 mt-1">Agregação estatística de todos os exames realizados</p>
                    </div>
                    <div class="flex gap-2 no-print">
                        <button onclick="window.print()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition">
                            <i class="fas fa-print mr-2"></i>Imprimir
                        </button>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-white rounded-lg shadow-md p-6 mb-6 no-print">
                <h2 class="text-lg font-semibold text-gray-700 mb-4">
                    <i class="fas fa-filter text-blue-500 mr-2"></i>Filtros
                </h2>
                <form id="filters-form" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Vaga/Processo</label>
                        <select name="vacancy_id" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas</option>
                            <?php foreach ($vacancies as $v): ?>
                                    <option value="<?= $v['id'] ?>"><?= htmlspecialchars($v['title']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Local</label>
                        <select name="location" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="">Todos</option>
                            <?php foreach ($locations as $loc): ?>
                                    <option value="<?= htmlspecialchars($loc) ?>"><?= htmlspecialchars($loc) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Disciplina</label>
                        <select name="discipline" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                            <option value="">Todas</option>
                            <?php foreach ($disciplines as $disc): ?>
                                    <option value="<?= htmlspecialchars($disc) ?>"><?= htmlspecialchars($disc) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Data Início</label>
                        <input type="date" name="date_from" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Data Fim</label>
                        <input type="date" name="date_to" class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="w-full bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700 transition font-medium">
                            <i class="fas fa-chart-bar mr-2"></i>Gerar Relatório
                        </button>
                    </div>
                </form>
            </div>

            <!-- Conteúdo do Relatório -->
            <div id="report-content" class="hidden">
                <!-- Cards de Resumo -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-3xl font-bold text-blue-600" id="card-exames">0</div>
                        <div class="text-sm text-gray-500 uppercase">Exames</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-3xl font-bold text-purple-600" id="card-juris">0</div>
                        <div class="text-sm text-gray-500 uppercase">Júris/Salas</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-3xl font-bold text-gray-600" id="card-esperados">0</div>
                        <div class="text-sm text-gray-500 uppercase">Esperados</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-3xl font-bold text-green-600" id="card-presentes">0</div>
                        <div class="text-sm text-gray-500 uppercase">Presentes</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-3xl font-bold text-orange-500" id="card-ausentes">0</div>
                        <div class="text-sm text-gray-500 uppercase">Ausentes</div>
                    </div>
                    <div class="bg-white rounded-lg shadow p-4 text-center">
                        <div class="text-3xl font-bold text-red-600" id="card-fraudes">0</div>
                        <div class="text-sm text-gray-500 uppercase">Fraudes</div>
                    </div>
                </div>

                <!-- Estatísticas por Género -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">
                        <i class="fas fa-table text-blue-500 mr-2"></i>Estatísticas Consolidadas
                    </h3>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border px-4 py-3 text-left">Indicador</th>
                                <th class="border px-4 py-3 text-center">Masculino</th>
                                <th class="border px-4 py-3 text-center">Feminino</th>
                                <th class="border px-4 py-3 text-center bg-blue-50">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border px-4 py-3 font-medium text-green-700"><i class="fas fa-check-circle mr-2"></i>Presentes</td>
                                <td class="border px-4 py-3 text-center" id="stat-presentes-m">0</td>
                                <td class="border px-4 py-3 text-center" id="stat-presentes-f">0</td>
                                <td class="border px-4 py-3 text-center font-bold bg-green-50" id="stat-presentes-total">0</td>
                            </tr>
                            <tr>
                                <td class="border px-4 py-3 font-medium text-orange-700"><i class="fas fa-times-circle mr-2"></i>Ausentes</td>
                                <td class="border px-4 py-3 text-center" id="stat-ausentes-m">0</td>
                                <td class="border px-4 py-3 text-center" id="stat-ausentes-f">0</td>
                                <td class="border px-4 py-3 text-center font-bold bg-orange-50" id="stat-ausentes-total">0</td>
                            </tr>
                            <tr>
                                <td class="border px-4 py-3 font-medium text-red-700"><i class="fas fa-exclamation-triangle mr-2"></i>Fraudes</td>
                                <td class="border px-4 py-3 text-center" id="stat-fraudes-m">0</td>
                                <td class="border px-4 py-3 text-center" id="stat-fraudes-f">0</td>
                                <td class="border px-4 py-3 text-center font-bold bg-red-50" id="stat-fraudes-total">0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Gráficos -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            <i class="fas fa-chart-bar text-blue-500 mr-2"></i>Distribuição Geral
                        </h3>
                        <canvas id="chart-presence" height="200"></canvas>
                    </div>
                    <div class="bg-white rounded-lg shadow-md p-6">
                        <h3 class="text-lg font-semibold text-gray-700 mb-4">
                            <i class="fas fa-venus-mars text-pink-500 mr-2"></i>Por Género
                        </h3>
                        <canvas id="chart-gender" height="200"></canvas>
                    </div>
                </div>

                <!-- Por Disciplina -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">
                        <i class="fas fa-book text-purple-500 mr-2"></i>Detalhamento por Disciplina
                    </h3>
                    <table class="w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border px-4 py-3 text-left">Disciplina</th>
                                <th class="border px-4 py-3 text-center">Salas</th>
                                <th class="border px-4 py-3 text-center">Esperados</th>
                                <th class="border px-4 py-3 text-center">Presentes</th>
                                <th class="border px-4 py-3 text-center">Ausentes</th>
                                <th class="border px-4 py-3 text-center">Fraudes</th>
                                <th class="border px-4 py-3 text-center">Taxa Presença</th>
                            </tr>
                        </thead>
                        <tbody id="tbody-discipline"></tbody>
                    </table>
                </div>

                <!-- Exportações -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6 no-print">
                    <h3 class="text-lg font-semibold text-gray-700 mb-4">
                        <i class="fas fa-download text-green-500 mr-2"></i>Exportar Relatório
                    </h3>
                    <div class="flex flex-wrap gap-4">
                        <a id="btn-export-pdf" href="#" class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center gap-2">
                            <i class="fas fa-file-pdf"></i>Exportar PDF
                        </a>
                        <a id="btn-export-excel" href="#" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                            <i class="fas fa-file-excel"></i>Exportar Excel
                        </a>
                        <a id="btn-export-csv" href="#" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                            <i class="fas fa-file-csv"></i>Exportar CSV
                        </a>
                    </div>
                </div>
            </div>

            <!-- Mensagem inicial -->
            <div id="initial-message" class="bg-white rounded-lg shadow-md p-12 text-center">
                <i class="fas fa-chart-pie text-6xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">Selecione os Filtros</h3>
                <p class="text-gray-500">Utilize os filtros acima e clique em "Gerar Relatório" para visualizar os dados consolidados.</p>
            </div>

        </div>
    </main>

    <!-- Loading Overlay -->
    <div id="loading-overlay" class="loading-overlay hidden">
        <div class="text-center">
            <i class="fas fa-spinner fa-spin text-4xl text-blue-600 mb-4"></i>
            <p class="text-gray-600">A gerar relatório...</p>
        </div>
    </div>

    <script>
        let chartPresence = null;
        let chartGender = null;
        let currentFilters = {};

        document.getElementById('filters-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            console.log('[DEBUG] Form submitted');

            const formData = new FormData(this);
            currentFilters = Object.fromEntries(formData.entries());
            console.log('[DEBUG] Filters:', currentFilters);

            document.getElementById('loading-overlay').classList.remove('hidden');

            const controller = new AbortController();
            const timeoutId = setTimeout(() => {
                console.log('[DEBUG] Request timeout after 30s');
                controller.abort();
            }, 30000);

            try {
                const url = '<?= url('/reports/consolidated/generate') ?>';
                console.log('[DEBUG] Fetching URL:', url);

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
                console.log('[DEBUG] Response status:', response.status);

                const text = await response.text();
                console.log('[DEBUG] Response text (first 500 chars):', text.substring(0, 500));

                let result;
                try {
                    result = JSON.parse(text);
                } catch (parseError) {
                    console.error('[DEBUG] JSON parse error:', parseError);
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
                console.error('[DEBUG] Fetch error:', error);
                if (error.name === 'AbortError') {
                    alert('Tempo limite excedido (30s). Servidor não respondeu.');
                } else {
                    alert('Erro ao gerar relatório: ' + error.message);
                }
            } finally {
                document.getElementById('loading-overlay').classList.add('hidden');
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
                    datasets: [{ label: 'Total', data: charts.presence_chart.data, backgroundColor: charts.presence_chart.colors, borderRadius: 8 }]
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
                    <td class="border px-4 py-2 font-medium">${disc.disciplina}</td>
                    <td class="border px-4 py-2 text-center">${disc.salas}</td>
                    <td class="border px-4 py-2 text-center">${disc.esperados}</td>
                    <td class="border px-4 py-2 text-center text-green-600">${disc.presentes}</td>
                    <td class="border px-4 py-2 text-center text-orange-600">${disc.ausentes}</td>
                    <td class="border px-4 py-2 text-center text-red-600">${disc.fraudes}</td>
                    <td class="border px-4 py-2 text-center">
                        <span class="px-2 py-1 rounded ${disc.taxa_presenca >= 80 ? 'bg-green-100 text-green-800' : disc.taxa_presenca >= 50 ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'}">
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
</body>
</html>