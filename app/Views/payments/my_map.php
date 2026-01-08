<?php
$title = 'Meu Mapa de Pagamento';
$breadcrumbs = [
    ['label' => 'Dashboard', 'url' => url('/dashboard')],
    ['label' => 'Meu Mapa de Pagamento']
];
?>

<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <!-- Cabeçalho -->
    <div class="bg-gradient-to-r from-indigo-800 to-purple-700 text-white rounded-lg shadow-lg p-6">
        <h1 class="text-2xl font-bold mb-1">💰 Meu Mapa de Pagamento</h1>
        <p class="text-indigo-200 text-sm">
            Exames de Admissão –
            <?= $vacancy ? date('Y', strtotime($vacancy['deadline_at'] ?? 'now')) : date('Y') ?>
        </p>
    </div>

    <!-- Seletor de Vaga -->
    <?php if (!empty($vacancies)): ?>
        <div class="bg-white rounded-lg shadow p-4">
            <form method="GET" action="<?= url('/payments/my-map') ?>" class="flex items-center gap-4">
                <label class="text-sm font-medium text-gray-700">Selecionar Vaga:</label>
                <select name="vacancy_id" onchange="this.form.submit()"
                    class="rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <?php foreach ($vacancies as $v): ?>
                        <option value="<?= $v['id'] ?>" <?= ($selectedVacancy == $v['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($v['title']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    <?php endif; ?>

    <?php if ($payment): ?>
        <!-- Bloco 1: Resumo Financeiro -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="text-2xl">📊</span> Resumo Financeiro
            </h2>
            <div class="grid md:grid-cols-2 gap-6">
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <div class="text-sm text-blue-600 font-medium mb-1">Vigias Realizadas</div>
                    <div class="text-2xl font-bold text-blue-800">
                        <?= (int) $payment['nr_vigias'] ?>
                    </div>
                    <div class="text-sm text-blue-600 mt-2">
                        <?= (int) $payment['nr_vigias'] ?> ×
                        <?= number_format($rates['valor_por_vigia'] ?? 0, 2, ',', '.') ?> MT =
                        <span class="font-bold">
                            <?= number_format($payment['valor_vigias'] ?? 0, 2, ',', '.') ?> MT
                        </span>
                    </div>
                </div>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <div class="text-sm text-purple-600 font-medium mb-1">Supervisões Realizadas</div>
                    <div class="text-2xl font-bold text-purple-800">
                        <?= (int) $payment['nr_supervisoes'] ?>
                    </div>
                    <div class="text-sm text-purple-600 mt-2">
                        <?= (int) $payment['nr_supervisoes'] ?> ×
                        <?= number_format($rates['valor_por_supervisao'] ?? 0, 2, ',', '.') ?> MT =
                        <span class="font-bold">
                            <?= number_format($payment['valor_supervisoes'] ?? 0, 2, ',', '.') ?> MT
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bloco 2: Total a Receber -->
        <div class="bg-gradient-to-r from-green-600 to-emerald-600 rounded-lg shadow-lg p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <div class="text-green-100 text-sm font-medium mb-1">TOTAL A RECEBER</div>
                    <div class="text-4xl font-bold">
                        <?= number_format($payment['total'] ?? 0, 2, ',', '.') ?> MT
                    </div>
                </div>
                <div class="text-6xl opacity-30">💵</div>
            </div>
        </div>

        <!-- Bloco 3: Estado do Pagamento -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="text-2xl">📋</span> Estado do Pagamento
            </h2>
            <?php
            $estado = $payment['estado'] ?? 'previsto';
            $estadoConfig = [
                'previsto' => [
                    'icon' => '🟡',
                    'label' => 'Previsto',
                    'bg' => 'bg-yellow-50',
                    'border' => 'border-yellow-300',
                    'text' => 'text-yellow-800',
                    'message' => 'Valores sujeitos a validação pela Comissão de Exames.'
                ],
                'validado' => [
                    'icon' => '🔵',
                    'label' => 'Validado',
                    'bg' => 'bg-blue-50',
                    'border' => 'border-blue-300',
                    'text' => 'text-blue-800',
                    'message' => 'Este valor foi validado pela Comissão de Exames e encontra-se em processamento.'
                ],
                'pago' => [
                    'icon' => '🟢',
                    'label' => 'Pago',
                    'bg' => 'bg-green-50',
                    'border' => 'border-green-300',
                    'text' => 'text-green-800',
                    'message' => 'Pagamento efetuado com sucesso.'
                ]
            ];
            $config = $estadoConfig[$estado] ?? $estadoConfig['previsto'];
            ?>
            <div class="<?= $config['bg'] ?> <?= $config['border'] ?> border-2 rounded-lg p-4">
                <div class="flex items-center gap-3">
                    <span class="text-3xl">
                        <?= $config['icon'] ?>
                    </span>
                    <div>
                        <div class="font-bold <?= $config['text'] ?> text-lg">
                            <?= $config['label'] ?>
                        </div>
                        <div class="<?= $config['text'] ?> text-sm mt-1">
                            <?= $config['message'] ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (!empty($payment['validated_at'])): ?>
                <div class="mt-3 text-sm text-gray-500">
                    Validado em:
                    <?= date('d/m/Y H:i', strtotime($payment['validated_at'])) ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bloco 4: Detalhe das Participações -->
        <?php if (!empty($participations)): ?>
            <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                <div class="p-4 bg-gray-50 border-b">
                    <h2 class="text-lg font-semibold text-gray-800 flex items-center gap-2">
                        <span class="text-2xl">📅</span> Detalhe das Participações
                    </h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disciplina</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Local</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Data</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sala</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Papel</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-100">
                            <?php foreach ($participations as $p): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm font-medium text-gray-700">
                                        <?= htmlspecialchars($p['subject']) ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?= htmlspecialchars($p['location'] ?? '-') ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?= date('d/m/Y', strtotime($p['exam_date'])) ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-gray-600">
                                        <?= htmlspecialchars($p['room']) ?>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <?php if ($p['papel'] === 'Supervisor'): ?>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                👔 Supervisor
                                            </span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                👁️ Vigilante
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div class="p-4 bg-gray-50 border-t text-sm text-gray-500">
                    ⚠️ Este é um mapa apenas para consulta. Qualquer correção deve ser solicitada à Comissão de Exames.
                </div>
            </div>
        <?php endif; ?>

    <?php elseif (!empty($vacancies)): ?>
        <!-- Sem pagamentos para a vaga selecionada -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
            <div class="text-yellow-600 text-4xl mb-3">📭</div>
            <h3 class="text-lg font-semibold text-yellow-800">Sem Mapa de Pagamento</h3>
            <p class="text-yellow-700 mt-2">
                O mapa de pagamentos para esta vaga ainda não foi gerado pela Comissão de Exames.
            </p>
        </div>
    <?php else: ?>
        <!-- Sem participações -->
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <div class="text-gray-400 text-5xl mb-4">📋</div>
            <h3 class="text-lg font-semibold text-gray-700">Sem Participações</h3>
            <p class="text-gray-500 mt-2">
                Não existem mapas de pagamento associados ao seu perfil.
            </p>
        </div>
    <?php endif; ?>
</div>