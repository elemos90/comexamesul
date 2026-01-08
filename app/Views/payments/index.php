<?php
$title = $title ?? 'Mapa de Pagamentos';
$breadcrumbs = $breadcrumbs ?? [];
$selectedVacancy = $selectedVacancy ?? null;
$payments = $payments ?? [];
$stats = $stats ?? null;
$rates = $rates ?? null;
?>

<style>
    .payment-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
    }

    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 1.5rem;
        border-radius: 12px;
        text-align: center;
    }

    .stat-card.green {
        background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
    }

    .stat-card.orange {
        background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    }

    .stat-card.blue {
        background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
    }

    .stat-value {
        font-size: 2rem;
        font-weight: 700;
    }

    .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }

    .payment-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .payment-table th {
        background: #f8fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #e2e8f0;
    }

    .payment-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .payment-table tr:hover {
        background: #f8fafc;
    }

    .badge-warning {
        background: #fef3c7;
        color: #d97706;
        padding: 2px 8px;
        border-radius: 4px;
        font-size: 0.75rem;
    }

    .badge-success {
        background: #d1fae5;
        color: #059669;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
    }

    .badge-pending {
        background: #e0e7ff;
        color: #4f46e5;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
    }
</style>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Mapa de Pagamentos</h1>
            <p class="text-gray-600">Vigilantes e Supervisores de Exames de Admissão</p>
        </div>
        <a href="<?= url('/payments/rates') ?>"
            class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Gerir Taxas
        </a>
    </div>

    <!-- Seletor de Vaga -->
    <div class="payment-card mb-6">
        <form method="GET" action="<?= url('/payments') ?>" class="flex items-center gap-4">
            <label class="font-medium text-gray-700">Selecione a Vaga:</label>
            <select name="vacancy_id" class="flex-1 border rounded-lg px-4 py-2 focus:ring-2 focus:ring-indigo-500"
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
        <!-- Estatísticas -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-card">
                <div class="stat-value">
                    <?= $stats['total_beneficiarios'] ?? count($payments) ?>
                </div>
                <div class="stat-label">Beneficiários</div>
            </div>
            <div class="stat-card green">
                <div class="stat-value">
                    <?= $stats['total_vigias'] ?? 0 ?>
                </div>
                <div class="stat-label">Total de Vigias</div>
            </div>
            <div class="stat-card orange">
                <div class="stat-value">
                    <?= $stats['total_supervisoes'] ?? 0 ?>
                </div>
                <div class="stat-label">Supervisões</div>
            </div>
            <div class="stat-card blue">
                <div class="stat-value">
                    <?= number_format($stats['valor_total'] ?? 0, 2, ',', '.') ?>
                </div>
                <div class="stat-label">Valor Total (MZN)</div>
            </div>
        </div>

        <!-- Taxas Ativas -->
        <?php if ($rates): ?>
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-2 text-green-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <strong>Taxas Ativas:</strong>
                    Vigia:
                    <?= number_format($rates['valor_por_vigia'], 2, ',', '.') ?> MZN |
                    Supervisão:
                    <?= number_format($rates['valor_por_supervisao'], 2, ',', '.') ?> MZN
                </div>
            </div>
        <?php else: ?>
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <div class="flex items-center gap-2 text-yellow-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <strong>Atenção:</strong> Não há taxa de pagamento definida para esta vaga.
                    <a href="<?= url('/payments/rates') ?>" class="underline ml-2">Definir Taxas</a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Ações -->
        <div class="flex gap-3 mb-6">
            <a href="<?= url('/payments/preview/' . $selectedVacancy) ?>"
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                👁️ Pré-visualizar
            </a>
            <form method="POST" action="<?= url('/payments/generate/' . $selectedVacancy) ?>" class="inline"
                onsubmit="return confirm('Deseja gerar/atualizar o mapa de pagamentos?')">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    📊 Gerar Mapa
                </button>
            </form>
            <?php if (!empty($payments) && ($payments[0]['estado'] ?? '') === 'previsto'): ?>
                <form method="POST" action="<?= url('/payments/validate/' . $selectedVacancy) ?>" class="inline"
                    onsubmit="return confirm('ATENÇÃO: Após validação, os valores ficarão congelados. Deseja continuar?')">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">
                        ✓ Validar Mapa
                    </button>
                </form>
            <?php endif; ?>
            <?php if (!empty($payments)): ?>
                <div class="relative" id="exportDropdown">
                    <button onclick="document.getElementById('exportMenu').classList.toggle('hidden')"
                        class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
                        📥 Exportar ▼
                    </button>
                    <div id="exportMenu"
                        class="absolute hidden bg-white shadow-lg rounded-lg mt-1 py-2 min-w-[120px] z-10 border">
                        <a href="<?= url('/payments/export/' . $selectedVacancy . '?format=pdf') ?>"
                            class="block px-4 py-2 hover:bg-gray-100 text-gray-700">📄 PDF</a>
                        <a href="<?= url('/payments/export/' . $selectedVacancy . '?format=excel') ?>"
                            class="block px-4 py-2 hover:bg-gray-100 text-gray-700">📊 Excel</a>
                        <a href="<?= url('/payments/export/' . $selectedVacancy . '?format=csv') ?>"
                            class="block px-4 py-2 hover:bg-gray-100 text-gray-700">📋 CSV</a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tabela de Pagamentos -->
        <?php if (!empty($payments)): ?>
            <div class="payment-card overflow-x-auto">
                <table class="payment-table">
                    <thead>
                        <tr>
                            <th>Ord.</th>
                            <th>Nome Completo</th>
                            <th class="text-center">Nº de Vigias</th>
                            <th class="text-center">Nº de Supervisões</th>
                            <th class="text-right">Valor a Receber (MZN)</th>
                            <th>NUIT</th>
                            <th>Nome do Banco</th>
                            <th>Número da Conta/NIB</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $i => $p): ?>
                            <tr>
                                <td>
                                    <?= $i + 1 ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($p['nome_completo']) ?>
                                    <?php if (empty($p['nuit']) || empty($p['banco']) || empty($p['numero_conta'])): ?>
                                        <span class="badge-warning ml-2">⚠️ Dados incompletos</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-center">
                                    <?= $p['nr_vigias'] ?>
                                </td>
                                <td class="text-center">
                                    <?= $p['nr_supervisoes'] ?>
                                </td>
                                <td class="text-right font-bold">
                                    <?= number_format($p['total'], 2, ',', '.') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($p['nuit'] ?? '-') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($p['banco'] ?? '-') ?>
                                </td>
                                <td>
                                    <?= htmlspecialchars($p['numero_conta'] ?? '-') ?>
                                </td>
                                <td>
                                    <?php if ($p['estado'] === 'validado'): ?>
                                        <span class="badge-success">✓ Validado</span>
                                    <?php else: ?>
                                        <span class="badge-pending">Previsto</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="bg-gray-100 font-bold">
                            <td colspan="2">TOTAL</td>
                            <td class="text-center">
                                <?= array_sum(array_column($payments, 'nr_vigias')) ?>
                            </td>
                            <td class="text-center">
                                <?= array_sum(array_column($payments, 'nr_supervisoes')) ?>
                            </td>
                            <td class="text-right">
                                <?= number_format(array_sum(array_column($payments, 'total')), 2, ',', '.') ?>
                            </td>
                            <td colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        <?php else: ?>
            <div class="payment-card text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <p class="text-gray-500">Nenhum mapa de pagamentos gerado para esta vaga.</p>
                <p class="text-gray-400 text-sm mt-2">Clique em "Gerar Mapa" para criar o mapa de pagamentos.</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="payment-card text-center py-12">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <p class="text-gray-500">Selecione uma vaga para ver o mapa de pagamentos.</p>
        </div>
    <?php endif; ?>
</div>