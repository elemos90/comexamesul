<?php
$title = $title ?? 'Taxas de Pagamento';
$breadcrumbs = $breadcrumbs ?? [];
$rates = $rates ?? [];
$vacancies = $vacancies ?? [];

use App\Utils\Auth;
$currentUser = Auth::user();
$isCoordinator = ($currentUser['role'] ?? '') === 'coordenador';
?>

<style>
    .rates-card {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        padding: 1.5rem;
    }

    .rates-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
    }

    .rates-table th {
        background: #f8fafc;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        border-bottom: 2px solid #e2e8f0;
    }

    .rates-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
    }

    .rates-table tr:hover {
        background: #f8fafc;
    }

    .badge-active {
        background: #d1fae5;
        color: #059669;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
    }

    .badge-inactive {
        background: #f3f4f6;
        color: #6b7280;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
    }
</style>

<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Taxas de Pagamento</h1>
            <p class="text-gray-600">Defina os valores por vigia e supervisão para cada vaga</p>
        </div>
        <a href="<?= url('/payments') ?>" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">
            ← Voltar aos Pagamentos
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <?php if ($isCoordinator): ?>
            <!-- Formulário de Nova Taxa (apenas para coordenadores) -->
            <div class="rates-card">
                <h2 class="text-lg font-semibold mb-4">Nova Taxa</h2>
                <form method="POST" action="<?= url('/payments/rates') ?>">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Vaga</label>
                        <select name="vacancy_id" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Selecione --</option>
                                <?php foreach ($vacancies as $v): ?>
                                <option value="<?= $v['id'] ?>">
                                   <?= htmlspecialchars($v['title']) ?> (
                                            <?= $v['year'] ?? date('Y') ?>)
                                </option>
                                <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor por Vigia (MZN)</label>
                        <input type="number" name="valor_por_vigia" step="0.01" min="0" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            placeholder="Ex: 750.00">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Valor por Supervisão (MZN)</label>
                        <input type="number" name="valor_por_supervisao" step="0.01" min="0" required
                            class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-500"
                            placeholder="Ex: 1500.00">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Moeda</label>
                        <select name="moeda" class="w-full border rounded-lg px-3 py-2">
                            <option value="MZN" selected>MZN - Metical</option>
                            <option value="USD">USD - Dólar</option>
                            <option value="EUR">EUR - Euro</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700">
                        Criar Taxa
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Lista de Taxas -->
        <div class="rates-card <?= $isCoordinator ? 'lg:col-span-2' : 'lg:col-span-3' ?>">
            <h2 class="text-lg font-semibold mb-4">Taxas Existentes</h2>

            <?php if (!empty($rates)): ?>
                <table class="rates-table">
                    <thead>
                        <tr>
                            <th>Vaga</th>
                            <th class="text-right">Por Vigia</th>
                            <th class="text-right">Por Supervisão</th>
                            <th>Moeda</th>
                            <th>Estado</th>
                            <th>Criado em</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rates as $r): ?>
                            <tr>
                                <td>
                                    <strong>
                                        <?= htmlspecialchars($r['vacancy_title'] ?? 'N/A') ?>
                                    </strong>
                                    <span class="text-gray-500 text-sm">(
                                        <?= $r['vacancy_year'] ?? '-' ?>)
                                    </span>
                                </td>
                                <td class="text-right font-mono">
                                    <?= number_format($r['valor_por_vigia'], 2, ',', '.') ?>
                                </td>
                                <td class="text-right font-mono">
                                    <?= number_format($r['valor_por_supervisao'], 2, ',', '.') ?>
                                </td>
                                <td>
                                    <?= $r['moeda'] ?>
                                </td>
                                <td>
                                    <?php if ($r['ativo']): ?>
                                        <span class="badge-active">✓ Ativa</span>
                                    <?php else: ?>
                                        <span class="badge-inactive">Inativa</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-gray-500 text-sm">
                                    <?= date('d/m/Y', strtotime($r['created_at'])) ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="text-center py-8">
                    <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="text-gray-500">Nenhuma taxa definida.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Informação -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <div class="flex items-start gap-3">
            <svg class="w-6 h-6 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div>
                <h3 class="font-semibold text-blue-800">Como funcionam as taxas?</h3>
                <ul class="text-blue-700 text-sm mt-2 space-y-1">
                    <li>• Cada vaga pode ter uma taxa ativa de cada vez</li>
                    <li>• Ao criar uma nova taxa, ela automaticamente se torna ativa</li>
                    <li>• Os valores são aplicados automaticamente ao gerar o mapa de pagamentos</li>
                    <li>• Fórmula: <code
                            class="bg-blue-100 px-1 rounded">Total = (Nº Vigias × Valor/Vigia) + (Nº Supervisões × Valor/Supervisão)</code>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>