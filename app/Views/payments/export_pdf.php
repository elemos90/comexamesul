<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Mapa de Pagamentos - <?= htmlspecialchars($vacancy['title'] ?? 'Exames') ?></title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', 'Arial', sans-serif;
            font-size: 9pt;
            line-height: 1.3;
            color: #1f2937;
            background: white;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 12mm 15mm;
            margin: 0 auto;
            background: white;
        }

        @media print {
            body {
                background: white;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .page {
                width: 100%;
                padding: 8mm;
                page-break-after: always;
            }

            .no-print {
                display: none !important;
            }
        }

        /* Header Compacto */
        .header {
            text-align: center;
            margin-bottom: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid #1e3a8a;
        }

        .header img {
            height: 45px;
            margin: 0 auto 6px auto;
            display: block;
        }

        .header h1 {
            font-size: 12pt;
            font-weight: 600;
            color: #1e3a8a;
            margin-bottom: 3px;
            letter-spacing: 0.5px;
        }

        .header h2 {
            font-size: 10pt;
            font-weight: 500;
            color: #4b5563;
        }

        .subtitle {
            font-size: 8pt;
            color: #6b7280;
            margin-top: 4px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        /* Taxas Badge */
        .rates-bar {
            background: linear-gradient(135deg, #f0fdf4 0%, #ecfdf5 100%);
            border: 1px solid #86efac;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 12px;
            display: flex;
            justify-content: center;
            gap: 20px;
            font-size: 8pt;
        }

        .rates-bar strong {
            color: #166534;
        }

        /* Tabela Compacta */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
            font-size: 8pt;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 5px 6px;
            text-align: left;
        }

        th {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            font-weight: 500;
            font-size: 7pt;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        tbody tr:nth-child(even) {
            background: #f9fafb;
        }

        tbody tr:hover {
            background: #eff6ff;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .font-bold {
            font-weight: 600;
        }

        .value-cell {
            font-family: 'Consolas', 'Monaco', monospace;
            font-size: 8pt;
        }

        .currency {
            color: #6b7280;
            font-size: 7pt;
        }

        /* Row Total */
        .totals-row {
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important;
            color: white;
            font-weight: 600;
        }

        .totals-row td {
            border-color: #1e3a8a;
            padding: 7px 6px;
        }

        /* Resumo */
        .summary {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            padding: 10px 15px;
            background: #f8fafc;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            font-size: 8pt;
        }

        .summary-item {
            text-align: center;
        }

        .summary-item .label {
            color: #6b7280;
            font-size: 7pt;
            text-transform: uppercase;
        }

        .summary-item .value {
            font-size: 11pt;
            font-weight: 600;
            color: #1e3a8a;
        }

        /* Assinaturas */
        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
            padding-top: 15px;
        }

        .signature-block {
            text-align: center;
            width: 40%;
        }

        .signature-line {
            border-top: 1px solid #374151;
            margin-top: 35px;
            padding-top: 6px;
            font-size: 8pt;
            color: #4b5563;
        }

        /* Footer */
        .footer {
            margin-top: 20px;
            padding-top: 10px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            font-size: 7pt;
            color: #9ca3af;
        }

        /* Print Button */
        .print-btn {
            position: fixed;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            color: white;
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .print-btn:hover {
            opacity: 0.9;
        }

        /* Dados compactos */
        .compact-text {
            max-width: 100px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            font-size: 7pt;
        }
    </style>
</head>

<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ Imprimir</button>

    <div class="page">
        <!-- Header Institucional Compacto -->
        <div class="header">
            <img src="<?= url('/assets/images/logo_unilicungo.png') ?>" alt="Logo">
            <h1>COMISSÃO DE EXAMES DE ADMISSÃO</h1>
            <h2>Mapa de Pagamentos - Vigilantes e Supervisores</h2>
            <div class="subtitle">
                <?= htmlspecialchars($vacancy['title'] ?? 'Exames de Admissão') ?> -
                <?= $vacancy['year'] ?? date('Y') ?>
            </div>
        </div>

        <!-- Taxas Aplicadas -->
        <?php if (!empty($rates)): ?>
            <div class="rates-bar">
                <span><strong>💰 Vigia:</strong> <?= number_format($rates['valor_por_vigia'], 2, ',', '.') ?>
                    <?= $rates['moeda'] ?></span>
                <span><strong>👔 Supervisão:</strong> <?= number_format($rates['valor_por_supervisao'], 2, ',', '.') ?>
                    <?= $rates['moeda'] ?></span>
            </div>
        <?php endif; ?>

        <!-- Tabela Principal Compacta -->
        <table>
            <thead>
                <tr>
                    <th style="width: 30px;" class="text-center">#</th>
                    <th>Nome Completo</th>
                    <th style="width: 50px;" class="text-center">Vigias</th>
                    <th style="width: 50px;" class="text-center">Superv.</th>
                    <th style="width: 80px;" class="text-right">Valor (MZN)</th>
                    <th style="width: 70px;">NUIT</th>
                    <th style="width: 80px;">Banco</th>
                    <th style="width: 100px;">Conta/NIB</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $totalVigias = 0;
                $totalSupervisoes = 0;
                $totalValor = 0;
                foreach ($payments as $i => $p):
                    $totalVigias += $p['nr_vigias'];
                    $totalSupervisoes += $p['nr_supervisoes'];
                    $totalValor += $p['total'];
                    ?>
                    <tr>
                        <td class="text-center"><?= $i + 1 ?></td>
                        <td><?= htmlspecialchars($p['nome_completo']) ?></td>
                        <td class="text-center font-bold" style="color: <?= $p['nr_vigias'] > 0 ? '#2563eb' : '#9ca3af' ?>">
                            <?= $p['nr_vigias'] ?>
                        </td>
                        <td class="text-center font-bold"
                            style="color: <?= $p['nr_supervisoes'] > 0 ? '#7c3aed' : '#9ca3af' ?>">
                            <?= $p['nr_supervisoes'] ?>
                        </td>
                        <td class="text-right value-cell">
                            <span class="currency">MZN</span> <?= number_format($p['total'], 2, ',', '.') ?>
                        </td>
                        <td class="compact-text"><?= htmlspecialchars($p['nuit'] ?? '-') ?></td>
                        <td class="compact-text"><?= htmlspecialchars($p['banco'] ?? '-') ?></td>
                        <td class="compact-text" style="font-family: monospace; font-size: 7pt;">
                            <?= htmlspecialchars($p['numero_conta'] ?? '-') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="2" class="text-right">TOTAL GERAL:</td>
                    <td class="text-center"><?= $totalVigias ?></td>
                    <td class="text-center"><?= $totalSupervisoes ?></td>
                    <td class="text-right value-cell">
                        <span class="currency">MZN</span> <?= number_format($totalValor, 2, ',', '.') ?>
                    </td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>

        <!-- Resumo Compacto -->
        <div class="summary">
            <div class="summary-item">
                <div class="label">Beneficiários</div>
                <div class="value"><?= count($payments) ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Total Vigias</div>
                <div class="value"><?= $totalVigias ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Total Supervisões</div>
                <div class="value"><?= $totalSupervisoes ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Valor Total</div>
                <div class="value" style="color: #166534;">MZN <?= number_format($totalValor, 2, ',', '.') ?></div>
            </div>
            <div class="summary-item">
                <div class="label">Gerado em</div>
                <div class="value" style="font-size: 9pt;"><?= date('d/m/Y H:i') ?></div>
            </div>
        </div>

        <!-- Assinaturas -->
        <div class="signatures">
            <div class="signature-block">
                <div class="signature-line">
                    Responsável pela Comissão
                </div>
            </div>
            <div class="signature-block">
                <div class="signature-line">
                    Director Financeiro
                </div>
            </div>
        </div>

        <!-- Rodapé -->
        <div class="footer">
            Documento gerado automaticamente pelo Sistema de Gestão de Exames de Admissão<br>
            <?= date('d/m/Y H:i:s') ?>
        </div>
    </div>
</body>

</html>