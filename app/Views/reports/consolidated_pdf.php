<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <title>Relatório Consolidado de Exames</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            color: #333;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            font-size: 18px;
            margin-bottom: 5px;
        }

        .header p {
            color: #666;
            font-size: 12px;
        }

        .header .processo {
            font-size: 14px;
            font-weight: bold;
            color: #2563eb;
            margin-top: 10px;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            font-size: 13px;
            font-weight: bold;
            color: #1f2937;
            background: #f3f4f6;
            padding: 8px 12px;
            border-left: 4px solid #2563eb;
            margin-bottom: 10px;
        }

        .cards {
            display: table;
            width: 100%;
            margin-bottom: 15px;
        }

        .card {
            display: table-cell;
            width: 16.66%;
            text-align: center;
            padding: 10px;
            border: 1px solid #e5e7eb;
        }

        .card-value {
            font-size: 20px;
            font-weight: bold;
        }

        .card-label {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
        }

        .green {
            color: #059669;
        }

        .orange {
            color: #d97706;
        }

        .red {
            color: #dc2626;
        }

        .blue {
            color: #2563eb;
        }

        .purple {
            color: #7c3aed;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #d1d5db;
            padding: 8px 10px;
            text-align: left;
        }

        th {
            background: #f3f4f6;
            font-weight: 600;
            font-size: 10px;
        }

        td {
            font-size: 10px;
        }

        .text-center {
            text-align: center;
        }

        .text-right {
            text-align: right;
        }

        .bg-green {
            background-color: #dcfce7;
        }

        .bg-orange {
            background-color: #fef3c7;
        }

        .bg-red {
            background-color: #fee2e2;
        }

        .occurrence {
            border-left: 3px solid #dc2626;
            background: #fef2f2;
            padding: 8px 12px;
            margin-bottom: 8px;
        }

        .occurrence-title {
            font-weight: bold;
            font-size: 11px;
        }

        .occurrence-detail {
            font-size: 10px;
            color: #666;
        }

        .footer {
            margin-top: 40px;
            border-top: 1px solid #d1d5db;
            padding-top: 20px;
        }

        .footer-text {
            text-align: center;
            font-style: italic;
            color: #6b7280;
            margin-bottom: 30px;
        }

        .signatures {
            display: table;
            width: 100%;
        }

        .signature {
            display: table-cell;
            width: 50%;
            text-align: center;
            padding: 20px;
        }

        .signature-line {
            border-bottom: 1px solid #333;
            width: 200px;
            margin: 0 auto 5px;
        }

        .signature-label {
            font-size: 10px;
            color: #666;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <!-- Cabeçalho -->
    <div class="header">
        <h1>UNIVERSIDADE LICUNGO</h1>
        <p>Comissão de Exames de Admissão</p>
        <p class="processo">
            <?= htmlspecialchars($data['identification']['ano_processo']) ?>
        </p>
        <p style="margin-top: 10px; font-size: 10px;">
            Relatório gerado em:
            <?= $data['generated_at'] ?><br>
            Coordenador:
            <?= htmlspecialchars($data['identification']['coordenador']) ?>
        </p>
    </div>

    <!-- Secção 2: Resumo Geral -->
    <div class="section">
        <div class="section-title">RESUMO GERAL</div>
        <div class="cards">
            <div class="card">
                <div class="card-value blue">
                    <?= $data['summary']['total_exames'] ?>
                </div>
                <div class="card-label">Exames</div>
            </div>
            <div class="card">
                <div class="card-value purple">
                    <?= $data['summary']['total_juris'] ?>
                </div>
                <div class="card-label">Júris/Salas</div>
            </div>
            <div class="card">
                <div class="card-value">
                    <?= $data['summary']['total_esperados'] ?>
                </div>
                <div class="card-label">Esperados</div>
            </div>
            <div class="card">
                <div class="card-value green">
                    <?= $data['summary']['total_presentes'] ?>
                </div>
                <div class="card-label">Presentes</div>
            </div>
            <div class="card">
                <div class="card-value orange">
                    <?= $data['summary']['total_ausentes'] ?>
                </div>
                <div class="card-label">Ausentes</div>
            </div>
            <div class="card">
                <div class="card-value red">
                    <?= $data['summary']['total_fraudes'] ?>
                </div>
                <div class="card-label">Fraudes</div>
            </div>
        </div>
    </div>

    <!-- Secção 3: Estatísticas por Género -->
    <div class="section">
        <div class="section-title">ESTATÍSTICAS CONSOLIDADAS</div>
        <table>
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th class="text-center">Masculino</th>
                    <th class="text-center">Feminino</th>
                    <th class="text-center">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="green"><strong>✓ Presentes</strong></td>
                    <td class="text-center">
                        <?= $data['statistics']['presentes']['masculino'] ?>
                    </td>
                    <td class="text-center">
                        <?= $data['statistics']['presentes']['feminino'] ?>
                    </td>
                    <td class="text-center bg-green"><strong>
                            <?= $data['statistics']['presentes']['total'] ?>
                        </strong></td>
                </tr>
                <tr>
                    <td class="orange"><strong>✗ Ausentes</strong></td>
                    <td class="text-center">
                        <?= $data['statistics']['ausentes']['masculino'] ?>
                    </td>
                    <td class="text-center">
                        <?= $data['statistics']['ausentes']['feminino'] ?>
                    </td>
                    <td class="text-center bg-orange"><strong>
                            <?= $data['statistics']['ausentes']['total'] ?>
                        </strong></td>
                </tr>
                <tr>
                    <td class="red"><strong>⚠ Fraudes</strong></td>
                    <td class="text-center">
                        <?= $data['statistics']['fraudes']['masculino'] ?>
                    </td>
                    <td class="text-center">
                        <?= $data['statistics']['fraudes']['feminino'] ?>
                    </td>
                    <td class="text-center bg-red"><strong>
                            <?= $data['statistics']['fraudes']['total'] ?>
                        </strong></td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Secção 5: Detalhamento por Disciplina -->
    <div class="section">
        <div class="section-title">DETALHAMENTO POR DISCIPLINA</div>
        <table>
            <thead>
                <tr>
                    <th>Disciplina</th>
                    <th class="text-center">Salas</th>
                    <th class="text-center">Esperados</th>
                    <th class="text-center">Presentes</th>
                    <th class="text-center">Ausentes</th>
                    <th class="text-center">Fraudes</th>
                    <th class="text-center">Taxa Presença</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($data['by_discipline'] as $disc): ?>
                    <tr>
                        <td>
                            <?= htmlspecialchars($disc['disciplina']) ?>
                        </td>
                        <td class="text-center">
                            <?= $disc['salas'] ?>
                        </td>
                        <td class="text-center">
                            <?= $disc['esperados'] ?>
                        </td>
                        <td class="text-center green">
                            <?= $disc['presentes'] ?>
                        </td>
                        <td class="text-center orange">
                            <?= $disc['ausentes'] ?>
                        </td>
                        <td class="text-center red">
                            <?= $disc['fraudes'] ?>
                        </td>
                        <td class="text-center"><strong>
                                <?= $disc['taxa_presenca'] ?>%
                            </strong></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <!-- Secção 6: Ocorrências -->
    <?php if (!empty($data['occurrences'])): ?>
        <div class="section page-break">
            <div class="section-title">OCORRÊNCIAS RELEVANTES</div>
            <?php foreach ($data['occurrences'] as $occ): ?>
                <div class="occurrence">
                    <div class="occurrence-title">
                        <?= htmlspecialchars($occ['disciplina']) ?> -
                        <?= htmlspecialchars($occ['sala']) ?>
                        <?php if ($occ['total_fraudes'] > 0): ?>
                            <span style="color: #dc2626;">(
                                <?= $occ['total_fraudes'] ?> fraude(s))
                            </span>
                        <?php endif; ?>
                    </div>
                    <div class="occurrence-detail">
                        <?= htmlspecialchars($occ['local']) ?> |
                        <?= $occ['data'] ?>
                        <?php if (!empty($occ['observacoes'])): ?>
                            <br><em>"
                                <?= htmlspecialchars($occ['observacoes']) ?>"
                            </em>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Secção 7: Validação Institucional -->
    <div class="footer">
        <p class="footer-text">
            O presente relatório consolida os dados oficiais dos exames de admissão
            realizados pela Comissão de Exames de Admissão da Universidade Licungo.
        </p>
        <div class="signatures">
            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-label">Coordenador</div>
                <div style="font-size: 10px;">
                    <?= htmlspecialchars($data['identification']['coordenador']) ?>
                </div>
            </div>
            <div class="signature">
                <div class="signature-line"></div>
                <div class="signature-label">Data</div>
                <div style="font-size: 10px;">
                    <?= date('d/m/Y') ?>
                </div>
            </div>
        </div>
    </div>
</body>

</html>