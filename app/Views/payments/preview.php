<?php
$title = $title ?? 'Pré-visualização de Pagamentos';
$breadcrumbs = $breadcrumbs ?? [];
$vacancy = $vacancy ?? null;
$preview = $preview ?? ['success' => false, 'data' => []];
?>

<style>
    .preview-container {
        max-width: 1100px;
        margin: 0 auto;
    }

    .preview-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.08);
        padding: 1rem;
    }

    .preview-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.8rem;
    }

    .preview-table th {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%);
        color: white;
        padding: 8px 6px;
        text-align: left;
        font-weight: 500;
        font-size: 0.7rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
    }

    .preview-table th:first-child {
        border-radius: 6px 0 0 0;
    }

    .preview-table th:last-child {
        border-radius: 0 6px 0 0;
    }

    .preview-table td {
        padding: 6px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.75rem;
    }

    .preview-table tbody tr:nth-child(even) {
        background: #fafbfc;
    }

    .preview-table tbody tr:hover {
        background: #eff6ff;
    }

    .totals-row {
        background: linear-gradient(135deg, #1e3a8a 0%, #1e40af 100%) !important;
        color: white;
        font-weight: 600;
    }

    .totals-row td {
        padding: 10px 6px;
        border: none;
    }

    .totals-row td:first-child {
        border-radius: 0 0 0 6px;
    }

    .totals-row td:last-child {
        border-radius: 0 0 6px 0;
    }

    .badge-warning {
        background: #fef3c7;
        color: #d97706;
        padding: 1px 4px;
        border-radius: 3px;
        font-size: 0.6rem;
    }

    .value-cell {
        font-family: 'Consolas', 'Monaco', monospace;
        font-weight: 600;
    }

    .currency {
        color: #64748b;
        font-size: 0.65rem;
    }

    .compact-input {
        font-size: 0.75rem;
        max-width: 140px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .header-info {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e2e8f0;
    }

    .rates-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ecfdf5;
        color: #065f46;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 500;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .preview-table {
            font-size: 9pt;
        }

        .preview-card {
            box-shadow: none;
            padding: 0;
        }
    }
</style>

<div class="preview-container px-4 py-4">
    <!-- Header Compacto -->
    <div class="header-info no-print">
        <div>
            <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Mapa de Pagamentos - Pré-visualização
            </h1>
            <p class="text-gray-500 text-xs mt-1">
                <?= htmlspecialchars($vacancy['title'] ?? 'Vaga') ?> (<?= $vacancy['year'] ?? date('Y') ?>)
            </p>
        </div>
        <a href="<?= url('/payments?vacancy_id=' . ($vacancy['id'] ?? '')) ?>"
            class="text-gray-600 hover:text-gray-800 text-sm flex items-center gap-1">
            ← Voltar
        </a>
    </div>

    <?php if (!$preview['success']): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-3 mb-4 rounded-r">
            <p class="text-red-700 text-sm">
                <strong>Erro:</strong> <?= htmlspecialchars($preview['error'] ?? 'Erro desconhecido') ?>
            </p>
        </div>
    <?php else: ?>

        <!-- Info Bar Compacta -->
        <div class="flex flex-wrap gap-3 mb-4 items-center">
            <?php if (!empty($preview['rates'])): ?>
                <span class="rates-badge">
                    💰 Vigia: <strong><?= number_format($preview['rates']['valor_por_vigia'], 2, ',', '.') ?> MZN</strong>
                </span>
                <span class="rates-badge">
                    👔 Supervisão: <strong><?= number_format($preview['rates']['valor_por_supervisao'], 2, ',', '.') ?>
                        MZN</strong>
                </span>
            <?php endif; ?>

            <?php if (!empty($preview['totals']['dados_incompletos'])): ?>
                <span
                    class="inline-flex items-center gap-1 bg-yellow-50 text-yellow-700 px-3 py-1 rounded-full text-xs font-medium">
                    ⚠️ <?= $preview['totals']['dados_incompletos'] ?> com dados incompletos
                </span>
            <?php endif; ?>
        </div>

        <!-- Tabela Preview Compacta -->
        <div class="preview-card overflow-x-auto">
            <table class="preview-table">
                <thead>
                    <tr>
                        <th style="width: 35px; text-align: center;">#</th>
                        <th>Nome Completo</th>
                        <th style="width: 55px; text-align: center;">Vigias</th>
                        <th style="width: 55px; text-align: center;">Superv.</th>
                        <th style="width: 90px; text-align: right;">Valor (MZN)</th>
                        <th style="width: 80px;">NUIT</th>
                        <th style="width: 100px;">Banco</th>
                        <th style="width: 130px;">Conta/NIB</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($preview['data'] as $i => $p): ?>
                        <tr>
                            <td class="text-center text-gray-500"><?= $i + 1 ?></td>
                            <td>
                                <?= htmlspecialchars($p['nome_completo']) ?>
                                <?php if ($p['dados_incompletos']): ?>
                                    <span class="badge-warning">⚠</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center font-medium <?= $p['nr_vigias'] > 0 ? 'text-blue-600' : 'text-gray-400' ?>">
                                <?= $p['nr_vigias'] ?>
                            </td>
                            <td
                                class="text-center font-medium <?= $p['nr_supervisoes'] > 0 ? 'text-purple-600' : 'text-gray-400' ?>">
                                <?= $p['nr_supervisoes'] ?>
                            </td>
                            <td class="text-right value-cell">
                                <span class="currency">MZN</span>
                                <?= number_format($p['total'], 2, ',', '.') ?>
                            </td>
                            <td class="compact-input text-gray-600"><?= htmlspecialchars($p['nuit'] ?? '-') ?></td>
                            <td class="compact-input text-gray-600" title="<?= htmlspecialchars($p['banco'] ?? '') ?>">
                                <?= htmlspecialchars($p['banco'] ?? '-') ?>
                            </td>
                            <td class="compact-input text-gray-600 font-mono text-xs">
                                <?= htmlspecialchars($p['numero_conta'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="totals-row">
                        <td colspan="2" class="text-right font-semibold">TOTAL GERAL:</td>
                        <td class="text-center"><?= $preview['totals']['total_vigias'] ?? 0 ?></td>
                        <td class="text-center"><?= $preview['totals']['total_supervisoes'] ?? 0 ?></td>
                        <td class="text-right value-cell">
                            <span class="currency">MZN</span>
                            <?= number_format($preview['totals']['valor_total'] ?? 0, 2, ',', '.') ?>
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Ações Compactas -->
        <div class="flex justify-center gap-3 mt-4 no-print">
            <form method="POST" action="<?= url('/payments/generate/' . ($vacancy['id'] ?? '')) ?>"
                onsubmit="return confirm('Deseja gerar o mapa de pagamentos com estes valores?')">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <button type="submit"
                    class="bg-green-600 text-white px-5 py-2 rounded-lg hover:bg-green-700 text-sm font-medium flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Confirmar e Gerar
                </button>
            </form>
            <button onclick="window.print()"
                class="bg-gray-100 text-gray-700 px-5 py-2 rounded-lg hover:bg-gray-200 text-sm font-medium flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Imprimir
            </button>
            <a href="<?= url('/payments?vacancy_id=' . ($vacancy['id'] ?? '')) ?>"
                class="bg-gray-100 text-gray-600 px-5 py-2 rounded-lg hover:bg-gray-200 text-sm">
                Cancelar
            </a>
        </div>
    <?php endif; ?>
</div>