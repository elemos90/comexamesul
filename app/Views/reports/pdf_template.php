<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Relatório Estatístico</title>
    <style>
        body {
            font-family: 'Helvetica', sans-serif;
            font-size: 12px;
            color: #333;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #000;
            pb-4;
        }

        .logo {
            width: 80px;
            height: auto;
            margin-bottom: 10px;
        }

        .university-name {
            font-size: 18px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .commission-name {
            font-size: 14px;
            font-weight: bold;
            margin-top: 5px;
        }

        .report-title {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
            text-decoration: underline;
        }

        .meta-info {
            margin-top: 10px;
            font-size: 11px;
        }

        .table-container {
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
        }

        th {
            background-color: #f0f0f0;
            font-weight: bold;
        }

        .total-row {
            font-weight: bold;
            background-color: #e0e0e0;
        }

        .indicators {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .indicator-box {
            display: table-cell;
            text-align: center;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
        }

        .indicator-value {
            font-size: 16px;
            font-weight: bold;
            color: #000;
        }

        .indicator-label {
            font-size: 10px;
            text-transform: uppercase;
            color: #666;
        }

        .charts {
            margin-top: 30px;
            text-align: center;
        }

        .chart-container {
            display: inline-block;
            width: 48%;
            vertical-align: top;
        }

        .chart-img {
            width: 100%;
            height: auto;
        }

        .observations {
            margin-top: 30px;
            border: 1px solid #000;
            padding: 10px;
            min-height: 50px;
        }

        .observations h4 {
            margin: 0 0 5px 0;
            font-size: 12px;
        }

        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 10px;
            text-align: center;
            border-top: 1px solid #ccc;
            padding-top: 5px;
        }

        .page-number:after {
            content: counter(page);
        }
    </style>
</head>

<body>
    <div class="header">
        <!-- Placeholder for Logo -->
        <!-- <img src="path/to/logo.png" class="logo"> -->
        <div class="university-name">Universidade Licungo</div>
        <div class="commission-name">Comissão de Exames de Admissão</div>
        <div class="report-title">Relatório Estatístico de Presenças, Ausências e Fraudes por Género</div>
        <div class="meta-info">
            Ano/Edição:
            <?= date('Y') ?> | Emissão:
            <?= date('d/m/Y H:i') ?>
        </div>
    </div>

    <div class="indicators">
        <div class="indicator-box">
            <div class="indicator-value">
                <?= $stats['grand_total'] ?>
            </div>
            <div class="indicator-label">Total Candidatos</div>
        </div>
        <div class="indicator-box">
            <div class="indicator-value">
                <?= $stats['grand_total'] > 0 ? number_format(($stats['present_total'] / $stats['grand_total']) * 100, 1, ',', '.') : 0 ?>%
            </div>
            <div class="indicator-label">Taxa de Presença</div>
        </div>
        <div class="indicator-box">
            <div class="indicator-value">
                <?= $stats['grand_total'] > 0 ? number_format(($stats['absent_total'] / $stats['grand_total']) * 100, 1, ',', '.') : 0 ?>%
            </div>
            <div class="indicator-label">Taxa de Ausência</div>
        </div>
        <div class="indicator-box">
            <div class="indicator-value">
                <?= $stats['present_total'] > 0 ? number_format(($stats['fraudes_total'] / $stats['present_total']) * 100, 1, ',', '.') : 0 ?>%
            </div>
            <div class="indicator-label">Taxa de Fraude</div>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Estado</th>
                    <th>Masculino</th>
                    <th>Feminino</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: left;">Presentes</td>
                    <td>
                        <?= $stats['present_m'] ?>
                    </td>
                    <td>
                        <?= $stats['present_f'] ?>
                    </td>
                    <td>
                        <?= $stats['present_total'] ?>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left;">Ausentes</td>
                    <td>
                        <?= $stats['absent_m'] ?>
                    </td>
                    <td>
                        <?= $stats['absent_f'] ?>
                    </td>
                    <td>
                        <?= $stats['absent_total'] ?>
                    </td>
                </tr>
                <tr>
                    <td style="text-align: left;">Fraudes</td>
                    <td>
                        <?= $stats['fraudes_m'] ?>
                    </td>
                    <td>
                        <?= $stats['fraudes_f'] ?>
                    </td>
                    <td>
                        <?= $stats['fraudes_total'] ?>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td style="text-align: left;">Total Geral</td>
                    <td colspan="2"></td>
                    <td>
                        <?= $stats['grand_total'] ?>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="charts">
        <?php
        // QuickChart URL for Bar Chart
        $barChartConfig = [
            'type' => 'bar',
            'data' => [
                'labels' => ['Presentes', 'Ausentes', 'Fraudes'],
                'datasets' => [
                    [
                        'label' => 'Masculino',
                        'backgroundColor' => 'rgba(54, 162, 235, 0.5)',
                        'data' => [$stats['present_m'], $stats['absent_m'], $stats['fraudes_m']]
                    ],
                    [
                        'label' => 'Feminino',
                        'backgroundColor' => 'rgba(255, 99, 132, 0.5)',
                        'data' => [$stats['present_f'], $stats['absent_f'], $stats['fraudes_f']]
                    ]
                ]
            ],
            'options' => [
                'title' => ['display' => true, 'text' => 'Distribuição por Género'],
                'scales' => ['yAxes' => [['ticks' => ['beginAtZero' => true]]]]
            ]
        ];
        $barChartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($barChartConfig));

        // QuickChart URL for Pie Chart
        $pieChartConfig = [
            'type' => 'pie',
            'data' => [
                'labels' => ['Presentes', 'Ausentes', 'Fraudes'],
                'datasets' => [
                    [
                        'data' => [$stats['present_total'], $stats['absent_total'], $stats['fraudes_total']],
                        'backgroundColor' => ['#4caf50', '#f44336', '#ff9800']
                    ]
                ]
            ],
            'options' => [
                'title' => ['display' => true, 'text' => 'Distribuição Geral']
            ]
        ];
        $pieChartUrl = "https://quickchart.io/chart?c=" . urlencode(json_encode($pieChartConfig));
        ?>

        <div class="chart-container">
            <img src="<?= $barChartUrl ?>" class="chart-img" alt="Gráfico de Barras">
        </div>
        <div class="chart-container">
            <img src="<?= $pieChartUrl ?>" class="chart-img" alt="Gráfico Circular">
        </div>
    </div>

    <div class="observations">
        <h4>Observações Institucionais:</h4>
        <p>Os dados apresentados resultam dos relatórios submetidos pelos vigilantes e supervisores no final dos exames.
        </p>
    </div>

    <div class="footer">
        Documento gerado automaticamente pelo Sistema de Gestão de Exames de Admissão<br>
        Página <span class="page-number"></span>
    </div>
</body>

</html>