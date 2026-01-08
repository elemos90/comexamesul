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
            font-family: 'Times New Roman', serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #000;
            background: white;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 15mm 20mm;
            margin: 0 auto;
            background: white;
        }

        @media print {
            body {
                background: white;
            }

            .page {
                width: 100%;
                padding: 10mm;
                page-break-after: always;
            }

            .no-print {
                display: none !important;
            }
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #1e3a8a;
            padding-bottom: 15px;
        }

        .header img {
            height: 60px;
            margin: 0 auto 10px auto;
            display: block;
        }

        .header h1 {
            font-size: 14pt;
            color: #1e3a8a;
            margin-bottom: 5px;
        }

        .header h2 {
            font-size: 12pt;
            font-weight: normal;
            color: #333;
        }

        .subtitle {
            font-size: 10pt;
            color: #666;
            margin-top: 5px;
        }

        .section-title {
            background: #1e3a8a;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            margin: 15px 0 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #333;
            padding: 6px 8px;
            text-align: left;
            font-size: 9pt;
        }

        th {
            background: #1e3a8a;
            color: white;
            font-weight: bold;
        }

        tbody tr:nth-child(even) {
            background: #f5f5f5;
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

        .totals-row {
            background: #1e3a8a !important;
            color: white;
            font-weight: bold;
        }

        .totals-row td {
            border-color: #1e3a8a;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #ccc;
        }

        .signatures {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }

        .signature-block {
            text-align: center;
            width: 45%;
        }

        .signature-line {
            border-top: 1px solid #000;
            margin-top: 40px;
            padding-top: 5px;
        }

        .print-btn {
            position: fixed;
            top: 20px;
            right: 20px;
            background: #1e3a8a;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }

        .print-btn:hover {
            background: #1e40af;
        }
    </style>
</head>

<body>
    <button class="print-btn no-print" onclick="window.print()">🖨️ Imprimir</button>

    <div class="page">
        <!-- Cabeçalho Institucional -->
        <div class="header">
            <img src="<?= url('/assets/images/logo_unilicungo.png') ?>" alt="Logo da Instituição">
            <h1>COMISSÃO DE EXAMES DE ADMISSÃO</h1>
            <h2>Mapa de Pagamentos - Vigilantes e Supervisores</h2>
            <div class="subtitle">
                <?= htmlspecialchars($vacancy['title'] ?? 'Exames de Admissão') ?> -
                <?= $vacancy['year'] ?? date('Y') ?>
            </div>
        </div>

        <!-- Informações das Taxas -->
        <?php if (!empty($rates)): ?>
            <div style="background: #f5f5f5; padding: 10px; margin-bottom: 15px; border: 1px solid #ddd;">
                <strong>Taxas Aplicadas:</strong>
                Valor por Vigia: <strong><?= number_format($rates['valor_por_vigia'], 2, ',', '.') ?>
                    <?= $rates['moeda'] ?></strong> |
                Valor por Supervisão: <strong><?= number_format($rates['valor_por_supervisao'], 2, ',', '.') ?>
                    <?= $rates['moeda'] ?></strong>
            </div>
        <?php endif; ?>

        <!-- Tabela Principal -->
        <table>
            <thead>
                <tr>
                    <th style="width: 35px;" class="text-center">Ord.</th>
                    <th>Nome Completo</th>
                    <th style="width: 70px;" class="text-center">Nº de Vigias</th>
                    <th style="width: 90px;" class="text-center">Nº de Supervisões</th>
                    <th style="width: 110px;" class="text-right">Valor a Receber (MT)</th>
                    <th style="width: 80px;">NUIT</th>
                    <th style="width: 80px;">Nome do Banco</th>
                    <th style="width: 110px;">Número da Conta/NIB</th>
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
                        <td class="text-center"><?= $p['nr_vigias'] ?></td>
                        <td class="text-center"><?= $p['nr_supervisoes'] ?></td>
                        <td class="text-right font-bold">MZN <?= number_format($p['total'], 2, ',', '.') ?></td>
                        <td><?= htmlspecialchars($p['nuit'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($p['banco'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($p['numero_conta'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="totals-row">
                    <td colspan="2" class="text-right">TOTAL GERAL:</td>
                    <td class="text-center"><?= $totalVigias ?></td>
                    <td class="text-center"><?= $totalSupervisoes ?></td>
                    <td class="text-right">MZN <?= number_format($totalValor, 2, ',', '.') ?></td>
                    <td colspan="3"></td>
                </tr>
            </tfoot>
        </table>

        <!-- Resumo -->
        <div style="margin-top: 20px; font-size: 10pt;">
            <p><strong>Total de Beneficiários:</strong> <?= count($payments) ?></p>
            <p><strong>Data de Geração:</strong> <?= date('d/m/Y H:i') ?></p>
            <?php if (!empty($payments[0]['validated_at'])): ?>
                <p><strong>Validado em:</strong> <?= date('d/m/Y H:i', strtotime($payments[0]['validated_at'])) ?></p>
            <?php endif; ?>
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
        <div class="footer" style="text-align: center; font-size: 9pt; color: #666;">
            Documento gerado automaticamente pelo Sistema de Gestão de Exames de Admissão<br>
            <?= date('d/m/Y H:i:s') ?>
        </div>
    </div>
</body>

</html>