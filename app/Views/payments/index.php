<?php
$title = $title ?? 'Mapa de Pagamentos';
$breadcrumbs = $breadcrumbs ?? [];
$selectedVacancy = $selectedVacancy ?? null;
$payments = $payments ?? [];
$stats = $stats ?? null;
$rates = $rates ?? null;
?>

<style>
    .payment-container {
        max-width: 1100px;
        margin: 0 auto;
    }

    .payment-card {
        background: white;
        border-radius: 8px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        padding: 1rem;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .stat-card {
        background: white;
        border-radius: 8px;
        padding: 0.75rem 1rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border-left: 3px solid;
    }

    .stat-card.purple {
        border-color: #8b5cf6;
    }

    .stat-card.green {
        border-color: #10b981;
    }

    .stat-card.orange {
        border-color: #f59e0b;
    }

    .stat-card.blue {
        border-color: #3b82f6;
    }

    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1;
    }

    .stat-label {
        font-size: 0.7rem;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.3px;
    }

    .payment-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 0.75rem;
    }

    .payment-table th {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
        color: white;
        padding: 8px 6px;
        text-align: left;
        font-weight: 500;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.3px;
        white-space: nowrap;
    }

    .payment-table th:first-child {
        border-radius: 6px 0 0 0;
    }

    .payment-table th:last-child {
        border-radius: 0 6px 0 0;
    }

    .payment-table td {
        padding: 6px;
        border-bottom: 1px solid #f1f5f9;
        font-size: 0.73rem;
    }

    .payment-table tbody tr:nth-child(even) {
        background: #fafbfc;
    }

    .payment-table tbody tr:hover {
        background: #eff6ff;
    }

    .total-row {
        background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%) !important;
        color: white;
        font-weight: 600;
    }

    .total-row td {
        padding: 8px 6px;
        border: none;
    }

    .total-row td:first-child {
        border-radius: 0 0 0 6px;
    }

    .total-row td:last-child {
        border-radius: 0 0 6px 0;
    }

    .badge {
        padding: 2px 8px;
        border-radius: 10px;
        font-size: 0.6rem;
        font-weight: 500;
        white-space: nowrap;
        display: inline-block;
    }

    .badge-success {
        background: #dcfce7;
        color: #166534;
    }

    .badge-pending {
        background: #e0e7ff;
        color: #4338ca;
    }

    .badge-warning {
        background: #fef3c7;
        color: #b45309;
        font-size: 0.55rem;
    }

    .rates-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        background: #ecfdf5;
        color: #065f46;
        padding: 3px 10px;
        border-radius: 15px;
        font-size: 0.7rem;
        font-weight: 500;
        white-space: nowrap;
    }

    .btn-sm {
        padding: 5px 12px;
        font-size: 0.7rem;
        border-radius: 5px;
        font-weight: 500;
        transition: all 0.15s;
    }

    .compact-text {
        max-width: 120px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .value-cell {
        font-family: 'Consolas', monospace;
        font-weight: 600;
    }

    @media (max-width: 768px) {
        .stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="payment-container px-3 py-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-4">
        <div>
            <h1 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                Mapa de Pagamentos
            </h1>
            <p class="text-gray-500 text-xs">Vigilantes e Supervisores de Exames</p>
        </div>
        <a href="<?= url('/payments/rates') ?>"
            class="btn-sm bg-indigo-600 text-white hover:bg-indigo-700 flex items-center gap-1">
            ⚙️ Taxas
        </a>
    </div>

    <!-- Seletor de Vaga -->
    <div class="payment-card mb-3">
        <form method="GET" action="<?= url('/payments') ?>" class="flex items-center gap-3">
            <label class="font-medium text-gray-600 text-sm">Vaga:</label>
            <select name="vacancy_id"
                class="flex-1 border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-indigo-500"
                onchange="this.form.submit()">
                <option value="">-- Selecione --</option>
                <?php foreach ($vacancies as $v): ?>
                    <option value="<?= $v['id'] ?>" <?= $selectedVacancy == $v['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['title']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </form>
    </div>

    <?php if ($selectedVacancy): ?>
        <!-- Stats -->
        <div class="stats-grid">
            <div class="stat-card purple">
                <div class="stat-value text-purple-600"><?= $stats['total_beneficiarios'] ?? count($payments) ?></div>
                <div class="stat-label">Beneficiários</div>
            </div>
            <div class="stat-card green">
                <div class="stat-value text-green-600"><?= $stats['total_vigias'] ?? 0 ?></div>
                <div class="stat-label">Vigias</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-value text-orange-500"><?= $stats['total_supervisoes'] ?? 0 ?></div>
                <div class="stat-label">Supervisões</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-value text-blue-600" style="font-size: 1.1rem;">
                    <?= number_format($stats['valor_total'] ?? 0, 2, ',', '.') ?></div>
                <div class="stat-label">Total (MZN)</div>
            </div>
        </div>

        <!-- Info Bar -->
        <div class="flex flex-wrap items-center gap-3 mb-3">
            <?php if ($rates): ?>
                <span class="rates-badge">💰 Vigia: <strong><?= number_format($rates['valor_por_vigia'], 2, ',', '.') ?>
                        MZN</strong></span>
                <span class="rates-badge">👔 Supervisão:
                    <strong><?= number_format($rates['valor_por_supervisao'], 2, ',', '.') ?> MZN</strong></span>
            <?php else: ?>
                <span class="rates-badge" style="background: #fef3c7; color: #92400e;">
                    ⚠️ Sem taxa definida - <a href="<?= url('/payments/rates') ?>" class="underline">Definir</a>
                </span>
            <?php endif; ?>
        </div>

        <!-- Ações -->
        <div class="flex flex-wrap gap-2 mb-3">
            <a href="<?= url('/payments/preview/' . $selectedVacancy) ?>"
                class="btn-sm bg-blue-600 text-white hover:bg-blue-700">
                👁️ Preview
            </a>
            <form method="POST" action="<?= url('/payments/generate/' . $selectedVacancy) ?>" class="inline"
                onsubmit="return confirm('Gerar/atualizar mapa de pagamentos?')">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <button type="submit" class="btn-sm bg-green-600 text-white hover:bg-green-700">
                    📊 Gerar
                </button>
            </form>
            <?php if (!empty($payments) && ($payments[0]['estado'] ?? '') === 'previsto'): ?>
                <form method="POST" action="<?= url('/payments/validate/' . $selectedVacancy) ?>" class="inline"
                    onsubmit="return confirm('Validar mapa? Os valores ficarão congelados.')">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <button type="submit" class="btn-sm bg-purple-600 text-white hover:bg-purple-700">
                        ✓ Validar
                    </button>
                </form>
            <?php endif; ?>
            <?php if (!empty($payments)): ?>
                <div class="relative inline-block" x-data="{ open: false }">
                    <button @click="open = !open" class="btn-sm bg-gray-600 text-white hover:bg-gray-700">
                        📥 Exportar ▼
                    </button>
                    <div x-show="open" @click.away="open = false"
                        class="absolute right-0 mt-1 bg-white shadow-lg rounded-lg py-1 min-w-[100px] z-10 border text-sm">
                        <a href="<?= url('/payments/export/' . $selectedVacancy . '?format=pdf') ?>"
                            class="block px-3 py-1.5 hover:bg-gray-100">📄 PDF</a>
                        <a href="<?= url('/payments/export/' . $selectedVacancy . '?format=excel') ?>"
                            class="block px-3 py-1.5 hover:bg-gray-100">📊 Excel</a>
                        <a href="<?= url('/payments/export/' . $selectedVacancy . '?format=csv') ?>"
                            class="block px-3 py-1.5 hover:bg-gray-100">📋 CSV</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabela -->
        <?php if (!empty($payments)): ?>
            <div class="payment-card overflow-x-auto p-0">
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th style="width: 35px;" class="text-center">#</th>
                            <th>Nome Completo</th>
                            <th style="width: 50px;" class="text-center">Vigias</th>
                            <th style="width: 50px;" class="text-center">Superv.</th>
                            <th style="width: 85px;" class="text-right">Valor (MZN)</th>
                            <th style="width: 75px;">NUIT</th>
                            <th style="width: 90px;">Banco</th>
                            <th style="width: 120px;">Conta/NIB</th>
                            <th style="width: 70px;" class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $i => $p): ?>
                            <tr>
                                <td class="text-center text-gray-500"><?= $i + 1 ?></td>
                                <td>
                                    <?= htmlspecialchars($p['nome_completo']) ?>
                                    <?php if (empty($p['nuit']) || empty($p['banco']) || empty($p['numero_conta'])): ?>
                                        <span class="badge badge-warning ml-1">⚠</span>
                                    <?php endif; ?>
                                </td>
                                <td
                                    class="text-center font-semibold <?= $p['nr_vigias'] > 0 ? 'text-blue-600' : 'text-gray-400' ?>">
                                    <?= $p['nr_vigias'] ?>
                                </td>
                                <td
                                    class="text-center font-semibold <?= $p['nr_supervisoes'] > 0 ? 'text-purple-600' : 'text-gray-400' ?>">
                                    <?= $p['nr_supervisoes'] ?>
                                </td>
                                <td class="text-right value-cell">
                                    <?= number_format($p['total'], 2, ',', '.') ?>
                                </td>
                                <td class="compact-text text-gray-600"><?= htmlspecialchars($p['nuit'] ?? '-') ?></td>
                                <td class="compact-text text-gray-600" title="<?= htmlspecialchars($p['banco'] ?? '') ?>">
                                    <?= htmlspecialchars($p['banco'] ?? '-') ?>
                                </td>
                                <td class="compact-text text-gray-600" style="font-family: monospace; font-size: 0.65rem;">
                                    <?= htmlspecialchars($p['numero_conta'] ?? '-') ?>
                                </td>
                                <td class="text-center">
                                    <?php if ($p['estado'] === 'validado'): ?>
                                        <span class="badge badge-success">✓ Validado</span>
                                    <?php else: ?>
                                        <span class="badge badge-pending">Previsto</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="total-row">
                            <td colspan="2" class="text-right font-semibold">TOTAL</td>
                            <td class="text-center"><?= array_sum(array_column($payments, 'nr_vigias')) ?></td>
                            <td class="text-center"><?= array_sum(array_column($payments, 'nr_supervisoes')) ?></td>
                            <td class="text-right value-cell">
                                <?= number_format(array_sum(array_column($payments, 'total')), 2, ',', '.') ?></td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="payment-card text-center py-8">
                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-500 text-sm">Nenhum mapa gerado para esta vaga.</p>
                <p class="text-gray-400 text-xs mt-1">Clique em "Gerar" para criar o mapa.</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="payment-card text-center py-8">
            <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-gray-500 text-sm">Selecione uma vaga acima para ver o mapa.</p>
        </div>
    <?php endif; ?>
</div>