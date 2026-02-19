<?php
$title = 'Planeamento de Júris por Vaga';
$breadcrumbs = [
    ['label' => 'Júris', 'url' => url('/juries')],
    ['label' => 'Planeamento por Vaga']
];
?>

<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Planeamento por Vaga</h1>
            <p class="text-gray-600 mt-1">Crie júris vinculados a uma vaga e aloque vigilantes automaticamente</p>
        </div>
    </div>

    <?php if (empty($vacancies)): ?>
        <!-- Nenhuma vaga aberta -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-yellow-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>
            <h3 class="text-lg font-medium text-yellow-900 mb-2">Nenhuma vaga aberta</h3>
            <p class="text-yellow-700 mb-4">Crie uma vaga primeiro para poder planejar os júris</p>
            <a href="<?= url('/vacancies') ?>"
                class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-500">
                Ir para Vagas
            </a>
        </div>
    <?php else: ?>
        <!-- Lista de Vagas -->
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($vacancies as $vacancy): ?>
                <!-- CORREÇÃO #1: Dados já vêm do controller, sem instanciar models na view -->
                <div class="bg-white border border-gray-200 rounded-lg shadow-sm hover:shadow-md transition-shadow">
                    <div class="p-6">
                        <!-- Header -->
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                    <?= htmlspecialchars($vacancy['title']) ?>
                                </h3>
                                <p class="text-sm text-gray-500">
                                    Prazo: <?= date('d/m/Y H:i', strtotime($vacancy['deadline_at'])) ?>
                                </p>
                            </div>
                            <span
                                class="px-2 py-1 text-xs font-semibold rounded-full <?= $vacancy['status'] === 'aberta' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                <?= ucfirst($vacancy['status']) ?>
                            </span>
                        </div>

                        <!-- Estatísticas se houver júris -->
                        <?php if ($vacancy['has_juries'] && $vacancy['stats']): ?>
                            <?php $stats = $vacancy['stats']; ?>
                            <div class="border-t border-gray-100 pt-4 mb-4">
                                <div class="grid grid-cols-2 gap-4 text-center">
                                    <div>
                                        <div class="text-2xl font-bold text-primary-600"><?= $stats['total_juries'] ?></div>
                                        <div class="text-xs text-gray-500">Júris</div>
                                    </div>
                                    <div>
                                        <div
                                            class="text-2xl font-bold <?= $stats['occupancy_rate'] >= 80 ? 'text-green-600' : 'text-orange-600' ?>">
                                            <?= $stats['occupancy_rate'] ?>%
                                        </div>
                                        <div class="text-xs text-gray-500">Ocupação</div>
                                    </div>
                                </div>

                                <div class="mt-3 flex gap-2 text-xs">
                                    <span class="flex items-center text-green-600">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd" />
                                        </svg>
                                        <?= $stats['juries_complete'] ?> completos
                                    </span>
                                    <?php if ($stats['juries_incomplete'] > 0): ?>
                                        <span class="flex items-center text-orange-600">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            <?= $stats['juries_incomplete'] ?> incompletos
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="border-t border-gray-100 pt-4 mb-4">
                                <p class="text-sm text-gray-500 text-center py-2">
                                    ℹ️ Nenhum júri criado ainda
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Ações -->
                        <div class="flex gap-2">
                            <?php if ($vacancy['has_juries']): ?>
                                <a href="<?= url('/juries/vacancy/' . $vacancy['id'] . '/manage') ?>"
                                    class="flex-1 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500 text-center">
                                    Gerir Júris
                                </a>
                            <?php else: ?>
                                <button type="button"
                                    onclick="openCreateWizard(<?= $vacancy['id'] ?>, '<?= htmlspecialchars($vacancy['title'], ENT_QUOTES) ?>')"
                                    class="flex-1 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500 text-center">
                                    Criar Júris
                                </button>
                            <?php endif; ?>

                            <a href="<?= url('/vacancies/' . $vacancy['id']) ?>"
                                class="px-4 py-2 border border-gray-300 text-gray-700 text-sm font-medium rounded hover:bg-gray-50 text-center">
                                Ver Vaga
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Criar Júris (Wizard 5 Etapas) -->
<div id="modal-create-juries"
    class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full mx-4 max-h-[95vh] overflow-hidden flex flex-col">
        <!-- Header com Indicador de Passos -->
        <div class="p-6 border-b bg-gradient-to-r from-primary-600 to-primary-700">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Criar Júris Completos</h2>
                <button type="button" class="modal-close text-white/80 hover:text-white">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Step Indicator -->
            <div class="flex items-center justify-between">
                <div class="step-indicator flex items-center gap-2" data-step="1">
                    <div
                        class="step-circle w-8 h-8 rounded-full bg-white text-primary-600 font-bold flex items-center justify-center text-sm">
                        1</div>
                    <span class="text-white text-sm font-medium hidden md:block">Dados Gerais</span>
                </div>
                <div class="flex-1 h-0.5 bg-white/30 mx-2"></div>
                <div class="step-indicator flex items-center gap-2 opacity-50" data-step="2">
                    <div
                        class="step-circle w-8 h-8 rounded-full bg-white/30 text-white font-bold flex items-center justify-center text-sm">
                        2</div>
                    <span class="text-white/70 text-sm font-medium hidden md:block">Salas</span>
                </div>
                <div class="flex-1 h-0.5 bg-white/30 mx-2"></div>
                <div class="step-indicator flex items-center gap-2 opacity-50" data-step="3">
                    <div
                        class="step-circle w-8 h-8 rounded-full bg-white/30 text-white font-bold flex items-center justify-center text-sm">
                        3</div>
                    <span class="text-white/70 text-sm font-medium hidden md:block">Vigilantes</span>
                </div>
                <div class="flex-1 h-0.5 bg-white/30 mx-2"></div>
                <div class="step-indicator flex items-center gap-2 opacity-50" data-step="4">
                    <div
                        class="step-circle w-8 h-8 rounded-full bg-white/30 text-white font-bold flex items-center justify-center text-sm">
                        4</div>
                    <span class="text-white/70 text-sm font-medium hidden md:block">Supervisores</span>
                </div>
                <div class="flex-1 h-0.5 bg-white/30 mx-2"></div>
                <div class="step-indicator flex items-center gap-2 opacity-50" data-step="5">
                    <div
                        class="step-circle w-8 h-8 rounded-full bg-white/30 text-white font-bold flex items-center justify-center text-sm">
                        5</div>
                    <span class="text-white/70 text-sm font-medium hidden md:block">Revisão</span>
                </div>
            </div>
        </div>

        <!-- Wizard Content -->
        <div class="flex-1 overflow-y-auto p-6">
            <form id="form-create-juries">
                <input type="hidden" id="create_vacancy_id" name="vacancy_id">
                <input type="hidden" name="csrf" value="<?= \App\Utils\Csrf::token() ?>">

                <!-- STEP 1: Dados Gerais -->
                <div class="wizard-step" data-step="1">
                    <!-- Vaga Selecionada -->
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <div class="font-semibold text-blue-900" id="selected_vacancy_title"></div>
                                <div class="text-sm text-blue-700">Júris serão vinculados a esta vaga</div>
                            </div>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📍 Local e Data</h3>
                    <div class="grid md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Local *</label>
                            <select name="location" id="select-location"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-primary-500"
                                required>
                                <option value="">Selecione o local...</option>
                                <?php foreach ($locations as $loc): ?>
                                    <option value="<?= htmlspecialchars($loc['name']) ?>"
                                        data-location-id="<?= $loc['id'] ?>">
                                        <?= htmlspecialchars($loc['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Data do Exame *</label>
                            <input type="date" name="exam_date"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-primary-500"
                                required>
                        </div>
                    </div>

                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📚 Disciplina e Horário</h3>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Disciplina *</label>
                            <select name="subject"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-primary-500"
                                required>
                                <option value="">Selecione...</option>
                                <?php foreach ($disciplines as $disc): ?>
                                    <option value="<?= htmlspecialchars($disc['name']) ?>">
                                        <?= htmlspecialchars($disc['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Horário Início *</label>
                            <input type="time" name="start_time" id="start_time"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-primary-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Horário Fim *</label>
                            <input type="time" name="end_time" id="end_time"
                                class="w-full rounded-lg border border-gray-300 px-3 py-2.5 focus:ring-2 focus:ring-primary-500"
                                required>
                            <p id="time-validation-error" class="mt-1 text-xs text-red-600 hidden"></p>
                            <p id="time-validation-success" class="mt-1 text-xs text-green-600 hidden"></p>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Salas -->
                <div class="wizard-step hidden" data-step="2">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">🏫 Salas e Candidatos</h3>
                        <button type="button" id="btn-add-room"
                            class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-500 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Adicionar Sala
                        </button>
                    </div>

                    <div class="bg-amber-50 border border-amber-200 rounded-lg p-3 mb-4 text-sm text-amber-800">
                        <strong>💡 Dica:</strong> Para cada 30 candidatos, será necessário 1 vigilante.
                    </div>

                    <div id="rooms-container" class="space-y-3">
                        <!-- Primeira sala -->
                        <div class="flex gap-3 items-start room-row bg-gray-50 p-4 rounded-lg border">
                            <div class="flex-1">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Sala *</label>
                                <select name="rooms[0][room]"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 room-select"
                                    onchange="updateRoomCapacity(this)" required>
                                    <option value="">Selecione a sala...</option>
                                </select>
                            </div>
                            <div class="w-32">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Candidatos *</label>
                                <input type="number" name="rooms[0][candidates_quota]" min="1"
                                    class="w-full rounded-lg border border-gray-300 px-3 py-2 room-capacity"
                                    oninput="updateVigilantesPreview()" required>
                            </div>
                            <div class="w-24 text-center">
                                <label class="block text-xs font-medium text-gray-700 mb-1">Mín. Vig.</label>
                                <div class="room-min-vigilantes text-lg font-bold text-primary-600 py-1">-</div>
                            </div>
                            <div class="w-10 pt-6">
                                <!-- Primeira sala não tem botão remover -->
                            </div>
                        </div>
                    </div>

                    <!-- Resumo de Salas -->
                    <div class="mt-4 p-4 bg-gray-100 rounded-lg">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Total de Salas:</span>
                            <span class="font-bold" id="total-rooms">1</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Total de Candidatos:</span>
                            <span class="font-bold" id="total-candidates">0</span>
                        </div>
                        <div class="flex justify-between text-sm mt-1">
                            <span class="text-gray-600">Vigilantes Necessários:</span>
                            <span class="font-bold text-primary-600" id="total-vigilantes-needed">0</span>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Vigilantes -->
                <div class="wizard-step hidden" data-step="3">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">👁️ Alocação de Vigilantes</h3>
                        <button type="button" id="btn-auto-vigilantes"
                            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-500 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Auto-Alocar Todos
                        </button>
                    </div>

                    <div id="vigilantes-allocation-container" class="space-y-4">
                        <!-- Será preenchido dinamicamente -->
                        <div class="text-center py-8 text-gray-500">
                            <p>Carregando informações das salas...</p>
                        </div>
                    </div>

                    <!-- Status Geral -->
                    <div class="mt-6 p-4 rounded-lg border-2" id="vigilantes-status">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div id="vigilantes-status-icon"
                                    class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900" id="vigilantes-status-title">Alocação
                                        Incompleta</div>
                                    <div class="text-sm text-gray-600" id="vigilantes-status-desc">Aloque vigilantes
                                        para todas as salas</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold" id="vigilantes-allocated-count">0</div>
                                <div class="text-xs text-gray-500">de <span id="vigilantes-needed-count">0</span>
                                    necessários</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 4: Supervisores -->
                <div class="wizard-step hidden" data-step="4">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">👔 Alocação de Supervisores</h3>
                        <button type="button" id="btn-auto-supervisors"
                            class="px-4 py-2 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-500 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 10V3L4 14h7v7l9-11h-7z" />
                            </svg>
                            Auto-Distribuir
                        </button>
                    </div>

                    <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 mb-4 text-sm text-purple-800">
                        <strong>ℹ️ Regra:</strong> Cada supervisor pode supervisionar até 10 júris. Salas: <span
                            id="supervisor-juries-count">0</span> → Supervisores necessários: <span
                            id="supervisors-needed" class="font-bold">0</span>
                    </div>

                    <div id="supervisors-allocation-container" class="space-y-4">
                        <!-- Será preenchido dinamicamente -->
                        <div class="text-center py-8 text-gray-500">
                            <p>Carregando supervisores elegíveis...</p>
                        </div>
                    </div>

                    <!-- Status Geral -->
                    <div class="mt-6 p-4 rounded-lg border-2" id="supervisors-status">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div id="supervisors-status-icon"
                                    class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-semibold text-gray-900" id="supervisors-status-title">Sem
                                        Supervisor</div>
                                    <div class="text-sm text-gray-600" id="supervisors-status-desc">Selecione
                                        supervisores para os júris</div>
                                </div>
                            </div>
                            <div class="text-right">
                                <div class="text-2xl font-bold" id="supervisors-allocated-count">0</div>
                                <div class="text-xs text-gray-500">de <span id="supervisors-min-count">0</span> mínimo
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 5: Revisão Final -->
                <div class="wizard-step hidden" data-step="5">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Revisão Final</h3>

                    <!-- Alertas -->
                    <div id="review-alerts" class="mb-6 space-y-2">
                        <!-- Alertas serão inseridos aqui -->
                    </div>

                    <!-- Resumo Geral -->
                    <div class="grid md:grid-cols-2 gap-6">
                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-3">📍 Informações do Exame</h4>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Local:</dt>
                                    <dd class="font-medium" id="review-location">-</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Data:</dt>
                                    <dd class="font-medium" id="review-date">-</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Disciplina:</dt>
                                    <dd class="font-medium" id="review-subject">-</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Horário:</dt>
                                    <dd class="font-medium" id="review-time">-</dd>
                                </div>
                            </dl>
                        </div>

                        <div class="bg-gray-50 rounded-lg p-4">
                            <h4 class="font-semibold text-gray-800 mb-3">📊 Totais</h4>
                            <dl class="space-y-2 text-sm">
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Salas/Júris:</dt>
                                    <dd class="font-medium" id="review-rooms">-</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Candidatos:</dt>
                                    <dd class="font-medium" id="review-candidates">-</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Vigilantes:</dt>
                                    <dd class="font-medium text-primary-600" id="review-vigilantes">-</dd>
                                </div>
                                <div class="flex justify-between">
                                    <dt class="text-gray-600">Supervisores:</dt>
                                    <dd class="font-medium text-purple-600" id="review-supervisors">-</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Detalhes das Salas -->
                    <div class="mt-6">
                        <h4 class="font-semibold text-gray-800 mb-3">🏫 Detalhes por Sala</h4>
                        <div id="review-rooms-detail" class="space-y-2">
                            <!-- Será preenchido dinamicamente -->
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Footer com Navegação -->
        <div class="p-4 border-t bg-gray-50 flex items-center justify-between">
            <button type="button" id="btn-wizard-back"
                class="px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100 flex items-center gap-2 hidden">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Voltar
            </button>
            <div class="flex-1"></div>
            <div class="flex gap-3">
                <button type="button"
                    class="modal-close px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-100">
                    Cancelar
                </button>
                <button type="button" id="btn-wizard-next"
                    class="px-6 py-2.5 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-500 flex items-center gap-2">
                    Próximo
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
                <button type="button" id="btn-wizard-create"
                    class="hidden px-6 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-500 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Criar Júris
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Selecionar Vigilante -->
<div id="vigilante-selector-modal"
    class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-[60] items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 overflow-hidden">
        <div class="p-4 border-b bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Selecionar Vigilante</h3>
            <button type="button" onclick="closeVigilanteSelector()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div class="p-4">
            <div class="relative mb-4">
                <input type="text" id="vigilante-search" placeholder="Pesquisar por nome ou email..."
                    class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                <svg class="w-5 h-5 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                    stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                </svg>
            </div>
            <div id="vigilante-list" class="max-h-64 overflow-y-auto space-y-1">
                <!-- Lista de vigilantes será injetada aqui -->
            </div>
        </div>
        <div class="p-3 border-t bg-gray-50">
            <button type="button" onclick="closeVigilanteSelector()"
                class="w-full px-4 py-2 text-gray-700 bg-gray-200 hover:bg-gray-300 rounded-lg transition-colors">
                Cancelar
            </button>
        </div>
    </div>
</div>

<script>
    const baseUrl = '<?= rtrim(url(''), '/') ?>';
    // Dados mestre do PHP
    const masterRooms = <?= json_encode($rooms) ?>;

    let selectedLocationId = null;
    let roomCount = 1; // Começamos com 1 sala

    // CORREÇÃO #5: Validação de horários em tempo real
    function validateTimeRange() {
        const startTime = document.getElementById('start_time').value;
        const endTime = document.getElementById('end_time').value;
        const errorEl = document.getElementById('time-validation-error');
        const successEl = document.getElementById('time-validation-success');
        const endTimeInput = document.getElementById('end_time');

        errorEl.classList.add('hidden');
        successEl.classList.add('hidden');
        endTimeInput.classList.remove('border-red-500', 'border-green-500');
        endTimeInput.setCustomValidity('');

        if (!startTime || !endTime) {
            return true;
        }

        const start = new Date(`2000-01-01T${startTime}`);
        const end = new Date(`2000-01-01T${endTime}`);
        const diffMinutes = (end - start) / 60000;

        if (end <= start) {
            errorEl.textContent = '❌ Horário de término deve ser maior que o de início';
            errorEl.classList.remove('hidden');
            endTimeInput.classList.add('border-red-500');
            endTimeInput.setCustomValidity('Horário inválido');
            return false;
        }

        if (diffMinutes < 30) {
            errorEl.textContent = '⚠️ Duração mínima recomendada: 30 minutos';
            errorEl.classList.remove('hidden');
            endTimeInput.classList.add('border-red-500');
            return false;
        }

        if (diffMinutes > 240) {
            errorEl.textContent = '⚠️ Duração muito longa (>4h). Verifique se está correto.';
            errorEl.classList.remove('hidden');
            endTimeInput.classList.add('border-red-500');
            return false;
        }

        successEl.textContent = `✓ Duração: ${diffMinutes} minutos`;
        successEl.classList.remove('hidden');
        endTimeInput.classList.add('border-green-500');
        return true;
    }

    // Adicionar listeners de validação
    document.getElementById('start_time')?.addEventListener('change', validateTimeRange);
    document.getElementById('end_time')?.addEventListener('change', validateTimeRange);
    document.getElementById('end_time')?.addEventListener('input', validateTimeRange);

    // Atualizar salas disponíveis quando o local mudar
    document.getElementById('select-location').addEventListener('change', function () {
        selectedLocationId = this.options[this.selectedIndex]?.dataset?.locationId || null;
        updateAllRoomSelects();
    });

    // Atualizar salas quando data/hora mudar
    const updateRoomsListener = () => {
        // Apenas atualizar se tivermos um local selecionado
        if (selectedLocationId) {
            updateAllRoomSelects();
        }
    };

    document.querySelector('input[name="exam_date"]')?.addEventListener('change', updateRoomsListener);
    document.querySelector('input[name="start_time"]')?.addEventListener('change', updateRoomsListener);
    // Para end_time usamos change pois o input já tem validação validateTimeRange
    document.querySelector('input[name="end_time"]')?.addEventListener('change', updateRoomsListener);

    function openCreateWizard(vacancyId, vacancyTitle) {
        document.getElementById('create_vacancy_id').value = vacancyId;
        document.getElementById('selected_vacancy_title').textContent = vacancyTitle;

        // Resetar contador de salas
        roomCount = 1;

        // Limpar salas extras (manter apenas a primeira)
        const roomsContainer = document.getElementById('rooms-container');
        const rooms = roomsContainer.querySelectorAll('.room-row');
        rooms.forEach((room, index) => {
            if (index > 0) room.remove();
        });

        // Limpar campos
        document.querySelector('select[name="location"]').value = '';
        document.querySelector('input[name="exam_date"]').value = '';
        document.querySelector('select[name="subject"]').value = '';
        document.querySelector('input[name="start_time"]').value = '';
        document.querySelector('input[name="end_time"]').value = '';

        // Limpar primeira sala
        const firstRoomSelect = document.querySelector('select[name="rooms[0][room]"]');
        const firstCapacityInput = document.querySelector('input[name="rooms[0][candidates_quota]"]');
        firstRoomSelect.innerHTML = '<option value="">Selecione o local primeiro</option>';
        firstCapacityInput.value = '';

        selectedLocationId = null;

        // Abrir modal
        document.getElementById('modal-create-juries').classList.remove('hidden');
        document.getElementById('modal-create-juries').classList.add('flex');
    }

    function getRoomOptions(locationId, excludeRoomCode = null) {
        let html = '<option value="">Selecione a sala...</option>';

        if (!locationId) {
            return '<option value="">Selecione um local primeiro</option>';
        }

        // Get all currently selected room codes (except the one we're editing)
        const selectedRoomCodes = Array.from(document.querySelectorAll('.room-select'))
            .map(select => select.value)
            .filter(code => code && code !== excludeRoomCode);

        const filteredRooms = (window.availableRoomsCache || masterRooms).filter(room =>
            room.location_id == locationId && !selectedRoomCodes.includes(room.code)
        );

        if (filteredRooms.length === 0 && selectedRoomCodes.length > 0) {
            return '<option value="">Todas as salas já selecionadas</option>';
        }

        if (filteredRooms.length === 0) {
            return '<option value="">Nenhuma sala disponível para este horário</option>';
        }

        filteredRooms.forEach(room => {
            html += `<option value="${room.code}" data-capacity="${room.capacity}" data-room-id="${room.id}">${room.code} - ${room.name} (Cap: ${room.capacity})</option>`;
        });

        return html;
    }

    // Cache para salas disponíveis
    window.availableRoomsCache = null;

    async function fetchAvailableRooms() {
        const examDate = document.querySelector('input[name="exam_date"]')?.value;
        const startTime = document.querySelector('input[name="start_time"]')?.value;
        const endTime = document.querySelector('input[name="end_time"]')?.value;

        // Limpar cache antes de nova busca para evitar uso de dados obsoletos
        window.availableRoomsCache = null;

        // Se não tem data/hora preenchida, não buscamos nada (deixamos o cache nulo para usar fallback se necessário, ou vazio)
        if (!selectedLocationId || !examDate || !startTime || !endTime) {
            // Se faltar dados, talvez devêssemos mostrar todas as salas?
            // Melhor não. Se tem local selecionado mas não tem hora, mostra todas do local.
            if (selectedLocationId) {
                window.availableRoomsCache = masterRooms.filter(r => r.location_id == selectedLocationId);
            }
            return;
        }

        try {
            const params = new URLSearchParams({
                location_id: selectedLocationId,
                exam_date: examDate,
                start_time: startTime,
                end_time: endTime
            });

            const response = await fetch(`${baseUrl}/api/rooms/available?${params}`);
            const data = await response.json();

            if (data.success) {
                window.availableRoomsCache = data.available_rooms.map(room => ({
                    ...room,
                    location_id: selectedLocationId
                }));

                // Mostrar feedback se houver salas ocupadas
                if (data.total_occupied > 0) {
                    console.log(`⚠️ ${data.total_occupied} sala(s) ocupada(s) neste horário:`, data.occupied_rooms);
                }
            } else {
                console.error('Erro ao buscar salas disponíveis:', data.message);
                // Em caso de erro, NÃO fallback para todas as salas, pois pode gerar conflito.
                // Deixar cache vazio para forçar erro ou mostrar vazio.
                window.availableRoomsCache = [];
                alert('Erro ao verificar disponibilidade de salas: ' + data.message);
            }
        } catch (error) {
            console.error('Erro ao buscar salas disponíveis:', error);
            window.availableRoomsCache = [];
            alert('Erro de conexão ao verificar salas.');
        }
    }

    async function updateAllRoomSelects() {
        const roomSelects = document.querySelectorAll('.room-select');

        // 1. Mostrar estado de carregando
        roomSelects.forEach(select => {
            if (select.value) {
                // Se já tinha valor, manter, mas indicar loading? Não, melhor limpar para garantir.
                // Mas se o usuário só mudou 1 minuto, perder a seleção é chato.
                // Vamos tentar manter o valor SE ele ainda for válido depois.
                select.dataset.oldValue = select.value;
            }
            select.innerHTML = '<option value="">⏳ Verificando disponibilidade...</option>';
            select.disabled = true;
        });

        // 2. Buscar novas salas
        await fetchAvailableRooms();

        // 3. Atualizar dropdowns
        roomSelects.forEach(select => {
            select.disabled = false;
            const oldValue = select.dataset.oldValue;

            // Recriar opções
            select.innerHTML = getRoomOptions(selectedLocationId, oldValue); // Passar oldValue como excludeCode se necessário, mas getRoomOptions usa exclude para outras salas.
            // A lógica de getRoomOptions(locationId, excludeRoomCode) usa excludeRoomCode para filtrar salas JÁ selecionadas em OUTROS selects.
            // Aqui queremos repopular. O segundo param é "excludeRoomCode". Não devemos passar o próprio valor.

            // Re-selecionar valor antigo SE ele ainda existir na lista
            if (oldValue) {
                // Verificar se old value está nas options
                const optionExists = select.querySelector(`option[value="${oldValue}"]`);
                if (optionExists) {
                    select.value = oldValue;
                } else {
                    // Valor antigo não está mais disponível!
                    // Resetar para vazio
                    select.value = "";
                }
            }
        });
    }

    function updateRoomCapacity(selectElement) {
        const selectedOption = selectElement.options[selectElement.selectedIndex];
        const capacity = selectedOption.dataset.capacity;
        const capacityInput = selectElement.closest('.room-row').querySelector('.room-capacity');

        if (capacity) {
            capacityInput.value = capacity;
            capacityInput.placeholder = `Sugestão: ${capacity}`;
        }

        // Update other room selects to remove this room from options
        updateAllRoomSelects();
        updateVigilantesPreview();
    }

    function addRoom() {
        if (!selectedLocationId) {
            alert('Por favor, selecione um local primeiro');
            return;
        }

        const roomsContainer = document.getElementById('rooms-container');
        const roomDiv = document.createElement('div');
        roomDiv.className = 'flex gap-3 items-start room-row bg-gray-50 p-4 rounded-lg border';
        roomDiv.innerHTML = `
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-700 mb-1">Sala *</label>
            <select name="rooms[${roomCount}][room]" class="w-full rounded-lg border border-gray-300 px-3 py-2 room-select" onchange="updateRoomCapacity(this)" required>
                ${getRoomOptions(selectedLocationId)}
            </select>
        </div>
        <div class="w-32">
            <label class="block text-xs font-medium text-gray-700 mb-1">Candidatos *</label>
            <input type="number" name="rooms[${roomCount}][candidates_quota]" min="1" class="w-full rounded-lg border border-gray-300 px-3 py-2 room-capacity" oninput="updateVigilantesPreview()" required>
        </div>
        <div class="w-24 text-center">
            <label class="block text-xs font-medium text-gray-700 mb-1">Mín. Vig.</label>
            <div class="room-min-vigilantes text-lg font-bold text-primary-600 py-1">-</div>
        </div>
        <div class="w-10 pt-6">
            <button type="button" onclick="removeRoom(this)" class="text-red-600 hover:text-red-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;

        roomsContainer.appendChild(roomDiv);
        roomCount++;
        updateVigilantesPreview();
    }

    function removeRoom(btn) {
        btn.closest('.room-row').remove();
        updateAllRoomSelects();
        updateVigilantesPreview();
    }

    document.getElementById('btn-add-room').addEventListener('click', addRoom);

    // =========================================
    // WIZARD NAVIGATION
    // =========================================
    let currentStep = 1;
    const totalSteps = 5;

    // Dados do wizard
    let wizardData = {
        rooms: [],
        vigilantes: {},  // roomIndex -> [vigilante_ids]
        supervisors: []  // [user_ids]
    };

    // Elegíveis carregados do servidor
    let eligibleVigilantes = [];
    let eligibleSupervisors = [];

    function goToStep(step) {
        if (step < 1 || step > totalSteps) return;

        // Validar antes de avançar
        if (step > currentStep && !validateCurrentStep()) {
            return;
        }

        // Esconder step atual
        document.querySelector(`.wizard-step[data-step="${currentStep}"]`)?.classList.add('hidden');

        // Mostrar novo step
        document.querySelector(`.wizard-step[data-step="${step}"]`)?.classList.remove('hidden');

        // Atualizar indicadores
        updateStepIndicators(step);

        // Atualizar botões de navegação
        updateNavigationButtons(step);

        // Ações específicas ao entrar no step
        onStepEnter(step);

        currentStep = step;
    }

    function updateStepIndicators(activeStep) {
        document.querySelectorAll('.step-indicator').forEach(indicator => {
            const step = parseInt(indicator.dataset.step);
            const circle = indicator.querySelector('.step-circle');

            if (step < activeStep) {
                // Completed
                indicator.classList.remove('opacity-50');
                circle.classList.remove('bg-white/30', 'text-white');
                circle.classList.add('bg-green-500', 'text-white');
                circle.innerHTML = '✓';
            } else if (step === activeStep) {
                // Active
                indicator.classList.remove('opacity-50');
                circle.classList.remove('bg-white/30', 'text-white', 'bg-green-500');
                circle.classList.add('bg-white', 'text-primary-600');
                circle.innerHTML = step;
            } else {
                // Future
                indicator.classList.add('opacity-50');
                circle.classList.remove('bg-white', 'text-primary-600', 'bg-green-500');
                circle.classList.add('bg-white/30', 'text-white');
                circle.innerHTML = step;
            }
        });
    }

    function updateNavigationButtons(step) {
        const btnBack = document.getElementById('btn-wizard-back');
        const btnNext = document.getElementById('btn-wizard-next');
        const btnCreate = document.getElementById('btn-wizard-create');

        // Botão Voltar
        if (step === 1) {
            btnBack.classList.add('hidden');
        } else {
            btnBack.classList.remove('hidden');
        }

        // Botões Próximo / Criar
        if (step === totalSteps) {
            btnNext.classList.add('hidden');
            btnCreate.classList.remove('hidden');
        } else {
            btnNext.classList.remove('hidden');
            btnCreate.classList.add('hidden');
        }
    }

    function showValidationMessage(message) {
        if (typeof toastr !== 'undefined') {
            toastr.error(message);
        } else {
            alert(message);
        }
    }

    function highlightField(element) {
        if (!element) return;
        element.classList.add('border-red-500', 'ring-1', 'ring-red-500', 'bg-red-50');

        const removeHighlight = function () {
            element.classList.remove('border-red-500', 'ring-1', 'ring-red-500', 'bg-red-50');
        };

        element.addEventListener('input', removeHighlight, { once: true });
        element.addEventListener('change', removeHighlight, { once: true });
    }

    function validateCurrentStep() {
        switch (currentStep) {
            case 1:
                return validateStep1();
            case 2:
                return validateStep2();
            case 3:
                return validateStep3();
            case 4:
                return validateStep4();
            default:
                return true;
        }
    }

    function validateStep1() {
        const form = document.getElementById('form-create-juries');
        const locationEl = form.querySelector('select[name="location"]');
        const examDateEl = form.querySelector('input[name="exam_date"]');
        const subjectEl = form.querySelector('select[name="subject"]');
        const startTimeEl = form.querySelector('input[name="start_time"]');
        const endTimeEl = form.querySelector('input[name="end_time"]');

        let hasError = false;
        const requiredFields = [
            { el: locationEl, name: 'Local' },
            { el: examDateEl, name: 'Data do Exame' },
            { el: subjectEl, name: 'Disciplina' },
            { el: startTimeEl, name: 'Horário Início' },
            { el: endTimeEl, name: 'Horário Fim' }
        ];

        requiredFields.forEach(field => {
            if (!field.el.value) {
                highlightField(field.el);
                hasError = true;
            }
        });

        if (hasError) {
            showValidationMessage('❌ Por favor, preencha todos os campos obrigatórios marcados em vermelho.');
            return false;
        }

        if (!validateTimeRange()) {
            return false;
        }

        return true;
    }

    function validateStep2() {
        const roomRows = document.querySelectorAll('.room-row');
        let valid = true;
        let totalCandidates = 0;
        let hasEmpty = false;

        roomRows.forEach(row => {
            const roomSelect = row.querySelector('.room-select');
            const capacityInput = row.querySelector('.room-capacity');
            const room = roomSelect.value;
            const candidates = parseInt(capacityInput.value) || 0;

            if (!room) {
                highlightField(roomSelect);
                hasEmpty = true;
                valid = false;
            }
            if (candidates < 1) {
                highlightField(capacityInput);
                hasEmpty = true;
                valid = false;
            }
            totalCandidates += candidates;
        });

        if (hasEmpty) {
            showValidationMessage('❌ Por favor, preencha todas as salas com dados válidos.');
            return false;
        }

        if (!valid) {
            showValidationMessage('❌ Verifique os dados das salas.');
            return false;
        }

        if (roomRows.length === 0) {
            showValidationMessage('❌ Adicione pelo menos uma sala.');
            return false;
        }

        // Salvar dados das salas
        collectRoomsData();

        return true;
    }

    function validateStep3() {
        // Verificar se todas as salas têm vigilantes suficientes
        let allComplete = true;
        let incompleteRooms = [];

        wizardData.rooms.forEach((room, index) => {
            const allocated = (wizardData.vigilantes[index] || []).length;
            const required = calculateMinVigilantes(room.candidates);
            if (allocated < required) {
                allComplete = false;
                incompleteRooms.push(`${room.room}: ${allocated}/${required} vigilantes`);
            }
        });

        if (!allComplete) {
            showValidationMessage(`❌ Não é possível avançar. Salas incompletas:\n\n${incompleteRooms.join('\n')}\n\nAloque vigilantes suficientes.`);
            return false;
        }

        return true;
    }

    function validateStep4() {
        const totalRooms = wizardData.rooms.length;
        const MAX_JURIS_POR_SUPERVISOR = 10;
        const numBlocks = Math.ceil(totalRooms / MAX_JURIS_POR_SUPERVISOR);

        // Initialize blockSupervisors if needed
        if (!wizardData.blockSupervisors) {
            wizardData.blockSupervisors = new Array(numBlocks).fill(null);
        }

        // Check which blocks are missing supervisors
        const missingBlocks = [];
        for (let i = 0; i < numBlocks; i++) {
            if (!wizardData.blockSupervisors[i]) {
                const startRoom = i * MAX_JURIS_POR_SUPERVISOR;
                const endRoom = Math.min(startRoom + MAX_JURIS_POR_SUPERVISOR, totalRooms);
                const blockRooms = wizardData.rooms.slice(startRoom, endRoom).map(r => r.room);
                missingBlocks.push(`Bloco ${i + 1} (${blockRooms.join(', ')})`);
            }
        }

        if (missingBlocks.length > 0) {
            showValidationMessage(`❌ Não é possível avançar. Blocos sem supervisor:\n\n${missingBlocks.join('\n')}\n\nAtribua um supervisor a cada bloco.`);
            return false;
        }

        return true;
    }

    function onStepEnter(step) {
        switch (step) {
            case 3:
                buildVigilantesStep();
                break;
            case 4:
                buildSupervisorsStep();
                break;
            case 5:
                buildReviewStep();
                break;
        }
    }

    // =========================================
    // CÁLCULOS E COLETA DE DADOS
    // =========================================

    function calculateMinVigilantes(candidates) {
        return Math.ceil(candidates / 30);
    }

    function updateVigilantesPreview() {
        let totalRooms = 0;
        let totalCandidates = 0;
        let totalVigilantes = 0;

        document.querySelectorAll('.room-row').forEach(row => {
            const candidates = parseInt(row.querySelector('.room-capacity')?.value) || 0;
            const minVigDisplay = row.querySelector('.room-min-vigilantes');

            if (candidates > 0) {
                const minVig = calculateMinVigilantes(candidates);
                if (minVigDisplay) minVigDisplay.textContent = minVig;
                totalVigilantes += minVig;
                totalCandidates += candidates;
            } else {
                if (minVigDisplay) minVigDisplay.textContent = '-';
            }
            totalRooms++;
        });

        // Atualizar resumo
        document.getElementById('total-rooms').textContent = totalRooms;
        document.getElementById('total-candidates').textContent = totalCandidates;
        document.getElementById('total-vigilantes-needed').textContent = totalVigilantes;
    }

    function collectRoomsData() {
        wizardData.rooms = [];
        document.querySelectorAll('.room-row').forEach((row, index) => {
            const room = row.querySelector('.room-select').value;
            const candidates = parseInt(row.querySelector('.room-capacity').value) || 0;

            if (room && candidates > 0) {
                wizardData.rooms.push({
                    room: room,
                    candidates: candidates,
                    minVigilantes: calculateMinVigilantes(candidates)
                });
            }
        });
    }

    // =========================================
    // HELPER: Get all currently selected supervisors (flattened)
    // =========================================
    function getAllSelectedSupervisors() {
        if (wizardData.blockSupervisors && wizardData.blockSupervisors.length > 0) {
            return wizardData.blockSupervisors.filter(id => id !== null);
        }
        return wizardData.supervisors || [];
    }

    // =========================================
    // HELPER: Get all currently selected vigilantes (flattened)
    // =========================================
    function getAllSelectedVigilantes() {
        return Object.values(wizardData.vigilantes).flat();
    }

    // =========================================
    // STEP 3: VIGILANTES
    // =========================================

    async function buildVigilantesStep() {
        const container = document.getElementById('vigilantes-allocation-container');
        container.innerHTML = '<div class="text-center py-4"><span class="animate-spin inline-block w-6 h-6 border-2 border-primary-600 border-t-transparent rounded-full"></span> Carregando vigilantes elegíveis...</div>';

        // Carregar vigilantes elegíveis (apenas se ainda não carregou)
        if (eligibleVigilantes.length === 0) {
            try {
                const vacancyId = document.getElementById('create_vacancy_id').value;
                const url = `${baseUrl}/api/vigilantes/eligible?vacancy_id=${vacancyId}`;
                const response = await fetch(url);
                const result = await response.json();

                if (result.success) {
                    eligibleVigilantes = result.vigilantes || [];
                } else {
                    eligibleVigilantes = [];
                }
            } catch (error) {
                console.error('Erro ao carregar vigilantes:', error);
                eligibleVigilantes = [];
            }
        }

        // Construir UI para cada sala
        let html = '';
        wizardData.rooms.forEach((room, index) => {
            const allocated = wizardData.vigilantes[index] || [];
            const required = room.minVigilantes;
            const isComplete = allocated.length >= required;
            const isOverAllocated = allocated.length > required;

            let cardClass = 'border-red-300 bg-red-50';
            let iconClass = 'bg-red-100 text-red-700';
            let badgeClass = 'bg-red-100 text-red-700';

            if (isOverAllocated) {
                cardClass = 'border-yellow-400 bg-yellow-50';
                iconClass = 'bg-yellow-100 text-yellow-700';
                badgeClass = 'bg-yellow-100 text-yellow-800';
            } else if (isComplete) {
                cardClass = 'border-green-300 bg-green-50';
                iconClass = 'bg-green-100 text-green-700';
                badgeClass = 'bg-green-100 text-green-700';
            }

            html += `
                <div class="p-4 border-2 rounded-lg ${cardClass} transition-colors">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg ${iconClass} flex items-center justify-center">
                                <span class="font-bold text-lg">${room.room}</span>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900 flex items-center gap-2">
                                    ${room.room}
                                    ${isOverAllocated ?
                    `<span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-xs font-medium bg-yellow-200 text-yellow-800">
                                            ⚠️ ${allocated.length - required} extra(s)
                                        </span>`
                    : ''}
                                </div>
                                <div class="text-sm text-gray-600">${room.candidates} candidatos · Mín. ${required} vigilante(s)</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-sm font-medium ${badgeClass}">
                                ${allocated.length}/${required}
                            </span>
                            <button type="button" onclick="autoAllocateRoom(${index})" class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-500 shadow-sm">
                                ⚡ Auto
                            </button>
                            <button type="button" onclick="openVigilanteSelector(${index})" class="px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded hover:bg-blue-500 shadow-sm">
                                ✋ Manual
                            </button>
                        </div>
                    </div>
                    
                    ${isOverAllocated ?
                    `<div class="mb-3 px-3 py-2 bg-yellow-100 border border-yellow-200 rounded text-xs text-yellow-800 flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <span><strong>Atenção:</strong> Você alocou mais vigilantes do que o mínimo exigido (${required}). Certifique-se de que isso é intencional.</span>
                        </div>`
                    : ''}

                    <div id="room-${index}-vigilantes" class="flex flex-wrap gap-2">
                        ${allocated.map(v => `
                            <span class="inline-flex items-center gap-1 px-2 py-1 bg-white border border-gray-200 rounded-md text-sm shadow-sm">
                                ${getVigilanteName(v)}
                                <button type="button" onclick="removeVigilante(${index}, ${v})" class="ml-1 text-gray-400 hover:text-red-500 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </span>
                        `).join('')}
                        ${allocated.length === 0 ? '<span class="text-sm text-gray-500 italic py-1">Nenhum vigilante alocado</span>' : ''}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html || '<p class="text-gray-500 text-center py-4">Nenhuma sala configurada</p>';
        updateVigilantesStatus();
    }

    function getVigilanteName(id) {
        const v = eligibleVigilantes.find(vig => vig.id == id);
        return v ? v.name : `Vigilante #${id}`;
    }

    function autoAllocateRoom(roomIndex) {
        const room = wizardData.rooms[roomIndex];
        if (!room) return;

        const required = room.minVigilantes;
        const currentlyAllocated = wizardData.vigilantes[roomIndex] || [];
        const allAllocatedVigilantes = getAllSelectedVigilantes();
        const allAllocatedSupervisors = getAllSelectedSupervisors();

        // Encontrar vigilantes disponíveis (não alocados como vigilantes E não alocados como supervisores)
        const available = eligibleVigilantes.filter(v =>
            !allAllocatedVigilantes.includes(v.id) &&
            !allAllocatedSupervisors.includes(v.id)
        );

        if (available.length === 0) {
            toastr.warning('Não há vigilantes disponíveis para alocação automática.');
            return;
        }

        // Alocar até atingir o mínimo
        const needed = required - currentlyAllocated.length;
        if (needed <= 0) return;

        const toAllocate = available.slice(0, needed).map(v => v.id);

        wizardData.vigilantes[roomIndex] = [...currentlyAllocated, ...toAllocate];

        buildVigilantesStep();
    }

    function removeVigilante(roomIndex, vigilanteId) {
        if (!wizardData.vigilantes[roomIndex]) return;
        wizardData.vigilantes[roomIndex] = wizardData.vigilantes[roomIndex].filter(id => id !== vigilanteId);
        buildVigilantesStep();
    }

    // Modal-based vigilante selector with search filter
    let currentSelectorRoomIndex = null;

    function openVigilanteSelector(roomIndex) {
        const allAllocatedVigilantes = getAllSelectedVigilantes();
        const allAllocatedSupervisors = getAllSelectedSupervisors();

        // Filtro: Não mostrar quem já é vigilante E quem já é supervisor
        const available = eligibleVigilantes.filter(v =>
            !allAllocatedVigilantes.includes(v.id) &&
            !allAllocatedSupervisors.includes(v.id)
        );

        if (available.length === 0) {
            alert('❌ Não há vigilantes disponíveis para alocar');
            return;
        }

        currentSelectorRoomIndex = roomIndex;

        // Build and show modal
        const modal = document.getElementById('vigilante-selector-modal');
        const searchInput = document.getElementById('vigilante-search');
        const listContainer = document.getElementById('vigilante-list');

        // Render available vigilantes
        function renderList(filter = '') {
            const filtered = available.filter(v =>
                v.name.toLowerCase().includes(filter.toLowerCase()) ||
                (v.email && v.email.toLowerCase().includes(filter.toLowerCase()))
            );

            if (filtered.length === 0) {
                listContainer.innerHTML = '<div class="text-center py-4 text-gray-500">Nenhum vigilante encontrado</div>';
                return;
            }

            listContainer.innerHTML = filtered.map(v => `
                <button type="button" 
                    onclick="selectVigilante(${v.id})"
                    class="w-full flex items-center gap-3 p-3 hover:bg-primary-50 rounded-lg transition-colors text-left border border-transparent hover:border-primary-200">
                    <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-700 flex items-center justify-center font-semibold">
                        ${v.name.charAt(0).toUpperCase()}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-gray-900 truncate">${v.name}</div>
                        <div class="text-sm text-gray-500 truncate">${v.email || ''}</div>
                    </div>
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
            `).join('');
        }

        renderList();
        searchInput.value = '';
        searchInput.oninput = (e) => renderList(e.target.value);

        modal.classList.remove('hidden');
        modal.classList.add('flex');
        searchInput.focus();
    }

    function selectVigilante(vigilanteId) {
        if (currentSelectorRoomIndex !== null) {
            if (!wizardData.vigilantes[currentSelectorRoomIndex]) {
                wizardData.vigilantes[currentSelectorRoomIndex] = [];
            }
            wizardData.vigilantes[currentSelectorRoomIndex].push(vigilanteId);
            closeVigilanteSelector();
            buildVigilantesStep();
        }
    }

    function closeVigilanteSelector() {
        const modal = document.getElementById('vigilante-selector-modal');
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        currentSelectorRoomIndex = null;
    }

    function updateVigilantesStatus() {
        let totalAllocated = 0;
        let totalNeeded = 0;
        let allComplete = true;

        wizardData.rooms.forEach((room, index) => {
            const allocated = (wizardData.vigilantes[index] || []).length;
            const required = room.minVigilantes;
            totalAllocated += allocated;
            totalNeeded += required;
            if (allocated < required) allComplete = false;
        });

        const statusDiv = document.getElementById('vigilantes-status');
        const iconDiv = document.getElementById('vigilantes-status-icon');
        const titleEl = document.getElementById('vigilantes-status-title');
        const descEl = document.getElementById('vigilantes-status-desc');

        document.getElementById('vigilantes-allocated-count').textContent = totalAllocated;
        document.getElementById('vigilantes-needed-count').textContent = totalNeeded;

        if (allComplete) {
            statusDiv.classList.remove('border-red-300');
            statusDiv.classList.add('border-green-300');
            iconDiv.classList.remove('bg-red-100');
            iconDiv.classList.add('bg-green-100');
            iconDiv.innerHTML = '<svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
            titleEl.textContent = 'Alocação Completa!';
            descEl.textContent = 'Todos os vigilantes foram alocados';
        } else {
            statusDiv.classList.remove('border-green-300');
            statusDiv.classList.add('border-red-300');
            iconDiv.classList.remove('bg-green-100');
            iconDiv.classList.add('bg-red-100');
            iconDiv.innerHTML = '<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>';
            titleEl.textContent = 'Alocação Incompleta';
            descEl.textContent = 'Aloque vigilantes para todas as salas';
        }
    }

    // Auto-alocar todos os vigilantes
    document.getElementById('btn-auto-vigilantes')?.addEventListener('click', function () {
        wizardData.rooms.forEach((room, index) => {
            autoAllocateRoom(index);
        });
    });

    // =========================================
    // STEP 4: SUPERVISORES
    // =========================================

    async function buildSupervisorsStep() {
        const container = document.getElementById('supervisors-allocation-container');
        const numJuries = wizardData.rooms.length;
        const MAX_JURIS_POR_SUPERVISOR = 10;
        const supervisorsNeeded = Math.ceil(numJuries / MAX_JURIS_POR_SUPERVISOR);

        document.getElementById('supervisor-juries-count').textContent = numJuries;
        document.getElementById('supervisors-needed').textContent = supervisorsNeeded;
        document.getElementById('supervisors-min-count').textContent = supervisorsNeeded;

        // Carregar supervisores elegíveis (apenas se não carregou)
        if (eligibleSupervisors.length === 0) {
            container.innerHTML = '<div class="text-center py-4"><span class="animate-spin inline-block w-6 h-6 border-2 border-purple-600 border-t-transparent rounded-full"></span> Carregando supervisores elegíveis...</div>';
            try {
                const vacancyId = document.getElementById('create_vacancy_id').value;
                const response = await fetch(`${baseUrl}/api/supervisors/eligible?vacancy_id=${vacancyId}`);
                const result = await response.json();

                if (result.success) {
                    eligibleSupervisors = result.supervisors || [];
                } else {
                    eligibleSupervisors = [];
                }
            } catch (error) {
                console.error('Erro ao carregar supervisores:', error);
                eligibleSupervisors = [];
            }
        }

        // Criar blocos de salas (máximo 10 salas por bloco)
        const blocks = [];
        for (let i = 0; i < numJuries; i += MAX_JURIS_POR_SUPERVISOR) {
            const blockRooms = wizardData.rooms.slice(i, i + MAX_JURIS_POR_SUPERVISOR);
            blocks.push({
                id: blocks.length,
                rooms: blockRooms,
                totalCandidates: blockRooms.reduce((sum, r) => sum + r.candidates, 0),
                supervisorId: wizardData.blockSupervisors ? wizardData.blockSupervisors[blocks.length] : null
            });
        }

        // Initialize block supervisors array if needed
        if (!wizardData.blockSupervisors) {
            wizardData.blockSupervisors = new Array(blocks.length).fill(null);
        }

        // Identificar vigilantes já alocados para excluí-los da lista de supervisores
        const allAllocatedVigilantes = getAllSelectedVigilantes();

        // Construir UI por blocos
        let html = '<div class="space-y-4">';

        blocks.forEach((block, blockIndex) => {
            const supervisor = wizardData.blockSupervisors[blockIndex];
            const supervisorData = supervisor ? eligibleSupervisors.find(s => s.id === supervisor) : null;
            const isComplete = supervisor !== null;

            html += `
                <div class="p-4 border-2 rounded-lg ${isComplete ? 'border-green-300 bg-green-50' : 'border-purple-300 bg-purple-50'}">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-lg ${isComplete ? 'bg-green-100' : 'bg-purple-100'} flex items-center justify-center">
                                <span class="font-bold ${isComplete ? 'text-green-700' : 'text-purple-700'} text-lg">B${blockIndex + 1}</span>
                            </div>
                            <div>
                                <div class="font-semibold text-gray-900">Bloco ${blockIndex + 1}</div>
                                <div class="text-sm text-gray-600">
                                    ${block.rooms.length} sala(s): ${block.rooms.map(r => r.room).join(', ')} 
                                    · ${block.totalCandidates} candidatos
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-3 py-1 rounded-full text-sm font-medium ${isComplete ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700'}">
                                ${isComplete ? '✓ Atribuído' : 'Pendente'}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <select id="block-supervisor-${blockIndex}" 
                            onchange="assignBlockSupervisor(${blockIndex}, this.value)"
                            class="flex-1 rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-purple-500 focus:border-purple-500">
                            <option value="">-- Selecionar Supervisor --</option>
                            ${eligibleSupervisors.map(s => {
                // Excluir se for vigilante
                if (allAllocatedVigilantes.includes(s.id)) return '';
                // Excluir se já for supervisor de OUTRO bloco (opcional, mas bom pra evitar duplicidade)
                // Mas user pode supervisionar +1 bloco se quiser? User disse "não pode assumir os dois papeis". Nada disse sobre múltiplos blocos.
                return `
                                    <option value="${s.id}" ${supervisor === s.id ? 'selected' : ''}>
                                        ${s.name} (${s.role_label || s.role || 'Supervisor'})
                                    </option>
                                `;
            }).join('')}
                        </select>
                        ${supervisorData ? `
                            <div class="flex items-center gap-2 text-sm text-green-700">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                ${supervisorData.name}
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        });

        html += '</div>';

        if (eligibleSupervisors.length === 0) {
            html = '<p class="text-gray-500 text-center py-4">Nenhum supervisor elegível encontrado</p>';
        }

        container.innerHTML = html;
        updateSupervisorsStatus();
    }

    function assignBlockSupervisor(blockIndex, supervisorId) {
        if (!wizardData.blockSupervisors) {
            wizardData.blockSupervisors = [];
        }
        wizardData.blockSupervisors[blockIndex] = supervisorId ? parseInt(supervisorId) : null;

        // Update legacy supervisors array for compatibility
        wizardData.supervisors = wizardData.blockSupervisors.filter(id => id !== null);

        buildSupervisorsStep();
    }

    function toggleSupervisor(id) {
        const index = wizardData.supervisors.indexOf(id);
        if (index > -1) {
            wizardData.supervisors.splice(index, 1);
        } else {
            wizardData.supervisors.push(id);
        }
        buildSupervisorsStep();
    }

    function updateSupervisorsStatus() {
        const needed = Math.ceil(wizardData.rooms.length / 10);
        const allocated = wizardData.supervisors.length;

        document.getElementById('supervisors-allocated-count').textContent = allocated;

        const statusDiv = document.getElementById('supervisors-status');
        const iconDiv = document.getElementById('supervisors-status-icon');
        const titleEl = document.getElementById('supervisors-status-title');
        const descEl = document.getElementById('supervisors-status-desc');

        if (allocated >= needed) {
            statusDiv.classList.remove('border-red-300');
            statusDiv.classList.add('border-green-300');
            iconDiv.classList.remove('bg-red-100');
            iconDiv.classList.add('bg-green-100');
            iconDiv.innerHTML = '<svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>';
            titleEl.textContent = 'Supervisores Alocados!';
            descEl.textContent = `${allocated} supervisor(es) selecionado(s)`;
        } else {
            statusDiv.classList.remove('border-green-300');
            statusDiv.classList.add('border-red-300');
            iconDiv.classList.remove('bg-green-100');
            iconDiv.classList.add('bg-red-100');
            iconDiv.innerHTML = '<svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>';
            titleEl.textContent = 'Faltam Supervisores';
            descEl.textContent = `Selecione pelo menos ${needed} supervisor(es)`;
        }
    }

    // Auto-distribuir supervisores por blocos
    document.getElementById('btn-auto-supervisors')?.addEventListener('click', function () {
        const totalRooms = wizardData.rooms.length;
        const MAX_JURIS_POR_SUPERVISOR = 10;
        const numBlocks = Math.ceil(totalRooms / MAX_JURIS_POR_SUPERVISOR);

        // Initialize block supervisors array
        wizardData.blockSupervisors = [];

        // Identificar quem já é vigilante para excluir da auto-distribuição
        const allAllocatedVigilantes = getAllSelectedVigilantes();
        const availableSupervisors = eligibleSupervisors.filter(s => !allAllocatedVigilantes.includes(s.id));

        if (availableSupervisors.length === 0) {
            toastr.warning('Não há supervisores disponíveis para distribuição automática (todos podem estar alocados como vigilantes).');
            return;
        }

        // Assign one supervisor to each block using round-robin
        for (let i = 0; i < numBlocks; i++) {
            if (availableSupervisors[i]) {
                wizardData.blockSupervisors[i] = availableSupervisors[i].id;
            } else if (availableSupervisors.length > 0) {
                // If not enough supervisors, reuse from beginning
                wizardData.blockSupervisors[i] = availableSupervisors[i % availableSupervisors.length].id;
            }
        }

        // Update legacy array for compatibility
        wizardData.supervisors = wizardData.blockSupervisors.filter(id => id !== null);

        buildSupervisorsStep();
    });

    // =========================================
    // STEP 5: REVISÃO
    // =========================================

    function buildReviewStep() {
        const formData = new FormData(document.getElementById('form-create-juries'));

        // Informações gerais
        document.getElementById('review-location').textContent = formData.get('location');
        document.getElementById('review-date').textContent = formatDate(formData.get('exam_date'));
        document.getElementById('review-subject').textContent = formData.get('subject');
        document.getElementById('review-time').textContent = `${formData.get('start_time')} - ${formData.get('end_time')}`;

        // Totais
        const totalVigilantes = Object.values(wizardData.vigilantes).flat().length;
        document.getElementById('review-rooms').textContent = wizardData.rooms.length;
        document.getElementById('review-candidates').textContent = wizardData.rooms.reduce((sum, r) => sum + r.candidates, 0);
        document.getElementById('review-vigilantes').textContent = totalVigilantes;
        document.getElementById('review-supervisors').textContent = wizardData.supervisors.length;

        // Detalhes por sala
        let detailsHtml = '';
        wizardData.rooms.forEach((room, index) => {
            const vigilantes = (wizardData.vigilantes[index] || []).map(id => getVigilanteName(id)).join(', ') || 'Nenhum';
            const isComplete = (wizardData.vigilantes[index] || []).length >= room.minVigilantes;

            detailsHtml += `
                <div class="flex items-center justify-between p-3 bg-white rounded border ${isComplete ? 'border-gray-200' : 'border-red-300'}">
                    <div class="flex items-center gap-3">
                        <span class="font-semibold">${room.room}</span>
                        <span class="text-sm text-gray-500">${room.candidates} candidatos</span>
                    </div>
                    <div class="text-sm">
                        <span class="${isComplete ? 'text-green-600' : 'text-red-600'}">${(wizardData.vigilantes[index] || []).length}/${room.minVigilantes} vig.</span>
                    </div>
                </div>
            `;
        });
        document.getElementById('review-rooms-detail').innerHTML = detailsHtml;

        // Alertas
        buildReviewAlerts();
    }

    function buildReviewAlerts() {
        const alerts = [];

        // Verificar vigilantes
        wizardData.rooms.forEach((room, index) => {
            const allocated = (wizardData.vigilantes[index] || []).length;
            if (allocated < room.minVigilantes) {
                alerts.push({
                    type: 'warning',
                    message: `Sala ${room.room}: ${allocated}/${room.minVigilantes} vigilantes (abaixo do mínimo)`
                });
            }
        });

        // Verificar supervisores
        const supervisorsNeeded = Math.ceil(wizardData.rooms.length / 10);
        if (wizardData.supervisors.length < supervisorsNeeded) {
            alerts.push({
                type: 'warning',
                message: `Supervisores: ${wizardData.supervisors.length}/${supervisorsNeeded} (abaixo do recomendado)`
            });
        }

        const alertsContainer = document.getElementById('review-alerts');
        if (alerts.length > 0) {
            alertsContainer.innerHTML = alerts.map(a => `
                <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg flex items-start gap-2">
                    <svg class="w-5 h-5 text-amber-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="text-sm text-amber-800">${a.message}</span>
                </div>
            `).join('');
        } else {
            alertsContainer.innerHTML = `
                <div class="p-3 bg-green-50 border border-green-200 rounded-lg flex items-start gap-2">
                    <svg class="w-5 h-5 text-green-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-sm text-green-800">✅ Tudo pronto! A configuração está completa.</span>
                </div>
            `;
        }
    }

    function formatDate(dateStr) {
        if (!dateStr) return '-';
        const parts = dateStr.split('-');
        return `${parts[2]}/${parts[1]}/${parts[0]}`;
    }

    // =========================================
    // NAVEGAÇÃO E CRIAÇÃO
    // =========================================

    document.getElementById('btn-wizard-next')?.addEventListener('click', function () {
        goToStep(currentStep + 1);
    });

    document.getElementById('btn-wizard-back')?.addEventListener('click', function () {
        goToStep(currentStep - 1);
    });

    document.getElementById('btn-wizard-create')?.addEventListener('click', async function () {
        const formData = new FormData(document.getElementById('form-create-juries'));

        // Preparar dados com alocações
        const rooms = wizardData.rooms.map((room, index) => ({
            room: room.room,
            candidates_quota: room.candidates,
            vigilantes: wizardData.vigilantes[index] || []
        }));

        const data = {
            vacancy_id: parseInt(formData.get('vacancy_id')),
            location: formData.get('location'),
            exam_date: formData.get('exam_date'),
            csrf: formData.get('csrf'),
            supervisors: wizardData.supervisors,
            blockSupervisors: wizardData.blockSupervisors || [],
            disciplines: [{
                subject: formData.get('subject'),
                start_time: formData.get('start_time'),
                end_time: formData.get('end_time'),
                rooms: rooms
            }]
        };

        this.disabled = true;
        this.innerHTML = '<span class="animate-spin inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span> Criando...';

        try {
            const response = await fetch(`${baseUrl}/juries/create-for-vacancy`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            if (response.status === 401) {
                alert('⚠️ Sessão expirada. Redirecionando para login...');
                window.location.href = '<?= url('/login') ?>';
                return;
            }

            const result = await response.json();

            if (result.success) {
                alert(`✅ ${result.message}\n\n${result.total || wizardData.rooms.length} júri(s) criado(s) com alocações!\n\nRedirecionando para gestão...`);
                window.location.href = `<?= url('/juries/vacancy/') ?>${data.vacancy_id}/manage`;
            } else {
                alert(`❌ Erro: ${result.message}`);
                this.disabled = false;
                this.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Criar Júris';
            }
        } catch (error) {
            console.error('Erro ao criar júris:', error);
            alert('❌ Erro ao criar júris. Verifique o console.');
            this.disabled = false;
            this.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Criar Júris';
        }
    });

    // =========================================
    // ABERTURA DO WIZARD
    // =========================================

    function openCreateWizard(vacancyId, vacancyTitle) {
        document.getElementById('create_vacancy_id').value = vacancyId;
        document.getElementById('selected_vacancy_title').textContent = vacancyTitle;

        // Resetar wizard
        currentStep = 1;
        roomCount = 1;
        wizardData = { rooms: [], vigilantes: {}, supervisors: [] };

        // Limpar salas extras
        const roomsContainer = document.getElementById('rooms-container');
        const rooms = roomsContainer.querySelectorAll('.room-row');
        rooms.forEach((room, index) => {
            if (index > 0) room.remove();
        });

        // Limpar campos
        document.querySelector('select[name="location"]').value = '';
        document.querySelector('input[name="exam_date"]').value = '';
        document.querySelector('select[name="subject"]').value = '';
        document.querySelector('input[name="start_time"]').value = '';
        document.querySelector('input[name="end_time"]').value = '';

        // Limpar primeira sala
        const firstRoomSelect = document.querySelector('select[name="rooms[0][room]"]');
        const firstCapacityInput = document.querySelector('input[name="rooms[0][candidates_quota]"]');
        firstRoomSelect.innerHTML = '<option value="">Selecione o local primeiro</option>';
        firstCapacityInput.value = '';

        selectedLocationId = null;

        // Resetar UI do wizard
        goToStep(1);
        updateVigilantesPreview();

        // Abrir modal
        document.getElementById('modal-create-juries').classList.remove('hidden');
        document.getElementById('modal-create-juries').classList.add('flex');
    }

    // Fechar modais
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('.modal').classList.add('hidden');
            this.closest('.modal').classList.remove('flex');
        });
    });

    // Auto-abrir wizard se vacancy_id vier na URL
    (function () {
        const urlParams = new URLSearchParams(window.location.search);
        const vacancyIdParam = urlParams.get('vacancy_id');

        if (vacancyIdParam) {
            // Encontrar a vaga na lista e abrir o wizard
            const vacancyId = parseInt(vacancyIdParam);
            const vacancies = <?= json_encode($vacancies ?? []) ?>;
            const vacancy = vacancies.find(v => v.id === vacancyId);

            if (vacancy) {
                // Pequeno atraso para garantir que a página carregou
                setTimeout(() => {
                    openCreateWizard(vacancy.id, vacancy.title || `Vaga #${vacancy.id}`);
                }, 100);
            }

            // Limpar URL para não reabrir se o utilizador recarregar
            const newUrl = window.location.pathname;
            window.history.replaceState({}, document.title, newUrl);
        }
    })();
</script>