<?php
$title = $title ?? 'Pré-visualização de Pagamentos';
$breadcrumbs = $breadcrumbs ?? [];
$vacancy = $vacancy ?? null;
$preview = $preview ?? ['success' => false, 'data' => []];
?>

<style>
    .preview-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
    }

    .preview-table {
        width: 100%;
        border-collapse: collapse;
    }

    .preview-table th {
        background: #1e3a8a;
        color: white;
        padding: 12px;
        text-align: left;
        font-weight: 600;
    }

    .preview-table td {
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .preview-table tbody tr:nth-child(odd) {
        background: #f8fafc;
    }

    .preview-table tbody tr:hover {
        background: #e0e7ff;
    }

    .totals-row {
        background: #1e3a8a !important;
        color: white;
        font-weight: bold;
    }

    .badge-warning {
        background: #fef3c7;
        color: #d97706;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 0.7rem;
    }
</style>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Pré-visualização de Pagamentos</h1>
            <p class="text-gray-600">
                <?= htmlspecialchars($vacancy['title'] ?? 'Vaga') ?> (<?= $vacancy['year'] ?? date('Y') ?>)
            </p>
        </div>
        <a href="<?= url('/payments?vacancy_id=' . ($vacancy['id'] ?? '')) ?>"
            class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            ← Voltar
        </a>
    </div>

    <?php if (!$preview['success']): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
            <div class="flex items-center gap-2 text-red-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <strong>Erro:</strong>
                <?= htmlspecialchars($preview['error'] ?? 'Erro desconhecido') ?>
            </div>
        </div>
    <?php else: ?>
        <!-- Taxas Aplicadas -->
        <?php if (!empty($preview['rates'])): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-4 text-green-800">
                    <strong>Taxas Aplicadas:</strong>
                    <span>Vigia: <strong>
                            <?= number_format($preview['rates']['valor_por_vigia'], 2, ',', '.') ?> MZN
                        </strong></span>
                    <span>|</span>
                    <span>Supervisão: <strong>
                            <?= number_format($preview['rates']['valor_por_supervisao'], 2, ',', '.') ?> MZN
                        </strong></span>
                </div>
            </div>
        <?php endif; ?>

        <!-- Alertas -->
        <?php if (!empty($preview['totals']['dados_incompletos'])): ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-2 text-yellow-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <strong>Atenção:</strong>
                    <?= $preview['totals']['dados_incompletos'] ?> beneficiário(s) com dados bancários incompletos.
                </div>
            </div>
        <?php endif; ?>

        <!-- Tabela Preview -->
        <div class="preview-card overflow-x-auto">
            <h2 class="text-lg font-bold text-center text-gray-800 mb-4">
                Vigilantes e supervisores de exames de admissão (<?= $vacancy['year'] ?? date('Y') ?>)
            </h2>

            <table class="preview-table">
                <thead>
                    <tr>
                        <th style="width: 50px;">Ord.</th>
                        <th>Nome Completo</th>
                        <th class="text-center" style="width: 100px;">Número de Vigias</th>
                        <th class="text-center" style="width: 120px;">Número de Supervisões</th>
                        <th class="text-right" style="width: 150px;">Valor a Receber (MT)</th>
                        <th style="width: 100px;">NUIT</th>
                        <th style="width: 120px;">Nome do Banco</th>
                        <th style="width: 150px;">Número da Conta/NIB</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($preview['data'] as $i => $p): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td>
                                <?= htmlspecialchars($p['nome_completo']) ?>
                                <?php if ($p['dados_incompletos']): ?>
                                    <span class="badge-warning">⚠️</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><?= $p['nr_vigias'] ?></td>
                            <td class="text-center"><?= $p['nr_supervisoes'] ?></td>
                            <td class="text-right font-mono font-bold">
                                <span class="text-gray-500">MZN</span>
                                <?= number_format($p['total'], 2, ',', '.') ?>
                            </td>
                            <td><?= htmlspecialchars($p['nuit'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($p['banco'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($p['numero_conta'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="totals-row">
                        <td colspan="2" class="text-right">TOTAL GERAL:</td>
                        <td class="text-center"><?= $preview['totals']['total_vigias'] ?? 0 ?></td>
                        <td class="text-center"><?= $preview['totals']['total_supervisoes'] ?? 0 ?></td>
                        <td class="text-right font-mono">
                            MZN <?= number_format($preview['totals']['valor_total'] ?? 0, 2, ',', '.') ?>
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Ações -->
        <div class="flex justify-center gap-4 mt-6">
            <form method="POST" action="<?= url('/payments/generate/' . ($vacancy['id'] ?? '')) ?>"
                onsubmit="return confirm('Deseja gerar o mapa de pagamentos com estes valores?')">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <button type="submit" class="bg-green-600 text-white px-6 py-3 rounded-lg hover:bg-green-700 font-medium">
                    ✓ Confirmar e Gerar Mapa
                </button>
            </form>
            <a href="<?= url('/payments?vacancy_id=' . ($vacancy['id'] ?? '')) ?>"
                class="bg-gray-300 text-gray-700 px-6 py-3 rounded-lg hover:bg-gray-400">
                Cancelar
            </a>
        </div>
    <?php endif; ?>
</div>