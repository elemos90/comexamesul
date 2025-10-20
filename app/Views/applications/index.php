<?php
$title = 'Revisar Candidaturas';
$breadcrumbs = [
    ['label' => 'Candidaturas']
];
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">⚡ Candidaturas de Vigilantes</h1>
            <p class="mt-1 text-sm text-gray-600">Revise e aprove candidaturas para vagas publicadas</p>
        </div>
        <div class="flex gap-2">
            <?php if (isset($selectedVacancy)): ?>
                <!-- Botões Relâmpago Seletivos (com checkboxes) -->
                <button type="button" 
                        id="btn-approve-selected" 
                        data-disabled="true"
                        class="group px-4 py-2 bg-gradient-to-r from-green-600 to-green-700 text-white text-sm font-bold rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 flex items-center gap-2 opacity-50 cursor-not-allowed"
                        title="Selecione candidaturas para aprovar">
                    <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span id="approve-count">Aprovar (0)</span>
                </button>
                
                <button type="button" 
                        id="btn-reject-selected" 
                        data-disabled="true"
                        class="group px-4 py-2 bg-gradient-to-r from-red-600 to-red-700 text-white text-sm font-bold rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200 shadow-md hover:shadow-lg transform hover:scale-105 flex items-center gap-2 opacity-50 cursor-not-allowed"
                        title="Selecione candidaturas para rejeitar">
                    <svg class="w-4 h-4 group-hover:rotate-12 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span id="reject-count">Rejeitar (0)</span>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Seletor de Vaga e Filtros -->
    <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6 space-y-4">
        <!-- Seletor de Vaga -->
        <div>
            <label class="block text-sm font-semibold text-gray-700 mb-3">Selecione uma Vaga:</label>
            <form method="GET" action="/applications" class="flex gap-3">
                <select 
                    name="vacancy" 
                    onchange="this.form.submit()" 
                    class="flex-1 rounded border border-gray-300 px-4 py-2 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                >
                    <option value="">-- Selecione uma vaga --</option>
                    <?php foreach ($vacancies as $vacancy): ?>
                        <option 
                            value="<?= $vacancy['id'] ?>" 
                            <?= $selectedVacancy && $selectedVacancy['id'] == $vacancy['id'] ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($vacancy['title']) ?> 
                            (<?= ucfirst($vacancy['status']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>

        <?php if ($selectedVacancy): ?>
        <!-- Filtros Avançados -->
        <div class="border-t pt-4">
            <div class="flex items-center justify-between mb-3">
                <label class="block text-sm font-semibold text-gray-700">Filtros Avançados:</label>
                <button type="button" onclick="clearFilters()" class="text-xs text-gray-500 hover:text-gray-700 underline">
                    Limpar Filtros
                </button>
            </div>
            
            <div class="grid md:grid-cols-3 gap-4">
                <!-- Pesquisa por Nome/Email -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Pesquisar Candidato:</label>
                    <input 
                        type="text" 
                        id="filter-search"
                        placeholder="Nome ou email..."
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        oninput="applyFilters()"
                    >
                </div>

                <!-- Filtro por Status -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Estado da Candidatura:</label>
                    <select 
                        id="filter-status"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        onchange="applyFilters()"
                    >
                        <option value="">Todos os estados</option>
                        <option value="pendente">⏳ Pendente</option>
                        <option value="aprovada">✓ Aprovado</option>
                        <option value="rejeitada">✗ Rejeitado</option>
                    </select>
                </div>

                <!-- Filtro por Elegibilidade -->
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Elegibilidade a Supervisor:</label>
                    <select 
                        id="filter-eligible"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        onchange="applyFilters()"
                    >
                        <option value="">Todos</option>
                        <option value="yes">👔 Elegíveis</option>
                        <option value="no">Não elegíveis</option>
                    </select>
                </div>
            </div>

            <!-- Contador de Resultados -->
            <div class="mt-3 text-xs text-gray-600">
                Mostrando <span id="filtered-count" class="font-semibold">0</span> de <span id="total-count" class="font-semibold">0</span> candidaturas
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($selectedVacancy): ?>
        <!-- Estatísticas -->
        <div class="grid md:grid-cols-4 gap-4">
            <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Total</p>
                        <p class="text-3xl font-bold text-gray-800 mt-1" data-stat="total">
                            <?= array_sum($statistics) ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Pendentes</p>
                        <p class="text-3xl font-bold text-yellow-600 mt-1" data-stat="pending">
                            <?= $statistics['pendente'] ?? 0 ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Aprovadas</p>
                        <p class="text-3xl font-bold text-green-600 mt-1" data-stat="approved">
                            <?= $statistics['aprovada'] ?? 0 ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-gray-600">Rejeitadas</p>
                        <p class="text-3xl font-bold text-red-600 mt-1" data-stat="rejected">
                            <?= $statistics['rejeitada'] ?? 0 ?>
                        </p>
                    </div>
                    <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lista de Candidaturas -->
        <div class="bg-white border border-gray-100 rounded-lg shadow-sm">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800">
                    Candidaturas para: <?= htmlspecialchars($selectedVacancy['title']) ?>
                </h2>
            </div>

            <?php if (empty($applications)): ?>
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <h3 class="text-lg font-semibold text-gray-700 mb-2">Nenhuma candidatura ainda</h3>
                    <p class="text-gray-500">Vigilantes ainda não se candidataram a esta vaga.</p>
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-100">
                    <?php foreach ($applications as $app): ?>
                        <?php
                        $statusColors = [
                            'pendente' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                            'aprovada' => 'bg-green-100 text-green-700 border-green-200',
                            'rejeitada' => 'bg-red-100 text-red-700 border-red-200',
                            'cancelada' => 'bg-gray-100 text-gray-600 border-gray-200',
                        ];
                        $statusClass = $statusColors[$app['status']] ?? 'bg-gray-100 text-gray-600';
                        ?>
                        <div data-application-row
                             data-id="<?= $app['id'] ?>"
                             data-status="<?= $app['status'] ?>"
                             data-name="<?= htmlspecialchars(strtolower($app['vigilante_name'])) ?>"
                             data-email="<?= htmlspecialchars(strtolower($app['vigilante_email'])) ?>"
                             data-eligible="<?= $app['supervisor_eligible'] ? 'yes' : 'no' ?>"
                             class="px-6 py-4 hover:bg-gray-50 transition-colors <?= $app['status'] === 'pendente' ? 'bg-yellow-50/30' : '' ?>">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1">
                                    <div class="flex items-start gap-3">
                                        <!-- Checkbox de Seleção -->
                                        <div class="pt-1">
                                            <input type="checkbox" 
                                                   class="application-checkbox w-5 h-5 text-primary-600 border-gray-300 rounded focus:ring-2 focus:ring-primary-500 cursor-pointer"
                                                   data-app-id="<?= $app['id'] ?>"
                                                   data-app-name="<?= htmlspecialchars($app['vigilante_name']) ?>"
                                                   data-app-status="<?= $app['status'] ?>"
                                                   onchange="updateBulkButtons()"
                                                   title="Selecionar esta candidatura">
                                        </div>
                                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <span class="text-primary-700 font-bold text-sm">
                                                <?= strtoupper(substr($app['vigilante_name'], 0, 2)) ?>
                                            </span>
                                        </div>
                                        <div class="flex-1">
                                            <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($app['vigilante_name']) ?></h3>
                                            <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-gray-600">
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                                    </svg>
                                                    <?= htmlspecialchars($app['vigilante_email']) ?>
                                                </span>
                                                <?php if ($app['vigilante_phone']): ?>
                                                    <span class="flex items-center gap-1">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                                        </svg>
                                                        <?= htmlspecialchars($app['vigilante_phone']) ?>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="flex items-center gap-1">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                    Candidatou-se: <?= htmlspecialchars(date('d/m/Y H:i', strtotime($app['applied_at']))) ?>
                                                </span>
                                                <?php if ($app['supervisor_eligible']): ?>
                                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-semibold rounded">
                                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                        </svg>
                                                        Elegível Supervisor
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <?php if ($app['reviewed_at']): ?>
                                                <p class="mt-2 text-xs text-gray-500">
                                                    Revisado por <?= htmlspecialchars($app['reviewed_by_name'] ?? 'N/A') ?> 
                                                    em <?= htmlspecialchars(date('d/m/Y H:i', strtotime($app['reviewed_at']))) ?>
                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1.5 rounded-full text-xs font-semibold border <?= $statusClass ?>">
                                        <?= htmlspecialchars(ucfirst($app['status'])) ?>
                                    </span>
                                    
                                    <!-- Botões para PENDENTE -->
                                    <?php if ($app['status'] === 'pendente'): ?>
                                        <button type="button" 
                                                onclick="approveApplication(<?= $app['id'] ?>, '<?= htmlspecialchars($app['vigilante_name'], ENT_QUOTES) ?>')"
                                                class="px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 transition-colors"
                                                title="Aprovar">
                                            ✓ Aprovar
                                        </button>
                                        <button type="button"
                                                onclick="showRejectModal(<?= $app['id'] ?>, '<?= htmlspecialchars($app['vigilante_name'], ENT_QUOTES) ?>')"
                                                class="px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded hover:bg-red-700 transition-colors"
                                                title="Rejeitar">
                                            ✗ Rejeitar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Botões para APROVADA -->
                                    <?php if ($app['status'] === 'aprovada'): ?>
                                        <?php if ($app['supervisor_eligible']): ?>
                                            <button type="button"
                                                    onclick="toggleSupervisorEligibility(<?= $app['id'] ?>, false, '<?= htmlspecialchars($app['vigilante_name'], ENT_QUOTES) ?>')"
                                                    class="px-3 py-1.5 bg-orange-600 text-white text-xs font-semibold rounded hover:bg-orange-700 transition-colors flex items-center gap-1"
                                                    title="Remover elegibilidade a supervisor">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                </svg>
                                                Remover Elegível
                                            </button>
                                        <?php else: ?>
                                            <button type="button"
                                                    onclick="toggleSupervisorEligibility(<?= $app['id'] ?>, true, '<?= htmlspecialchars($app['vigilante_name'], ENT_QUOTES) ?>')"
                                                    class="px-3 py-1.5 bg-purple-600 text-white text-xs font-semibold rounded hover:bg-purple-700 transition-colors flex items-center gap-1 shadow-md"
                                                    title="Marcar como elegível a supervisor">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                                👔 Elegível Supervisor
                                            </button>
                                        <?php endif; ?>
                                        <button type="button"
                                                onclick="revertToPending(<?= $app['id'] ?>, '<?= htmlspecialchars($app['vigilante_name'], ENT_QUOTES) ?>')"
                                                class="px-3 py-1.5 bg-gray-600 text-white text-xs font-semibold rounded hover:bg-gray-700 transition-colors"
                                                title="Reverter para Pendente">
                                            ↶ Pendente
                                        </button>
                                        <button type="button"
                                                onclick="showRejectModal(<?= $app['id'] ?>, '<?= htmlspecialchars($app['vigilante_name'], ENT_QUOTES) ?>')"
                                                class="px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded hover:bg-red-700 transition-colors"
                                                title="Rejeitar">
                                            ✗ Rejeitar
                                        </button>
                                    <?php endif; ?>
                                    
                                    <!-- Botões para REJEITADA -->
                                    <?php if ($app['status'] === 'rejeitada'): ?>
                                        <button type="button"
                                                onclick="revertToPending(<?= $app['id'] ?>, '<?= htmlspecialchars($app['vigilante_name'], ENT_QUOTES) ?>')"
                                                class="px-3 py-1.5 bg-gray-600 text-white text-xs font-semibold rounded hover:bg-gray-700 transition-colors"
                                                title="Reverter para Pendente">
                                            ↶ Pendente
                                        </button>
                                        <button type="button" 
                                                onclick="approveApplication(<?= $app['id'] ?>, '<?= htmlspecialchars($app['vigilante_name'], ENT_QUOTES) ?>')"
                                                class="px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 transition-colors"
                                                title="Aprovar">
                                            ✓ Aprovar
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <h3 class="text-lg font-semibold text-gray-700 mb-2">Selecione uma Vaga</h3>
            <p class="text-gray-500">Escolha uma vaga acima para revisar as candidaturas</p>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Rejeitar Candidatura -->
<div id="modal-reject" class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="flex items-center justify-between p-6 border-b">
            <h2 class="text-xl font-bold text-gray-900">🚫 Rejeitar Candidatura</h2>
            <button type="button" 
                    onclick="closeRejectModal()" 
                    class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
        
        <form id="form-reject" class="p-6">
            <input type="hidden" id="reject_app_id" name="application_id">
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Vigilante:</label>
                <p class="font-semibold text-gray-900" id="reject_vigilante_name"></p>
            </div>
            
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Motivo da Rejeição <span class="text-red-500">*</span>
                </label>
                <select name="rejection_reason" 
                        id="rejection_reason"
                        class="w-full border border-gray-300 rounded px-3 py-2 focus:border-primary-500 focus:ring-1 focus:ring-primary-500" 
                        required>
                    <option value="">-- Selecione um motivo --</option>
                    <option value="Perfil Incompleto">Perfil Incompleto</option>
                    <option value="Documentos Pendentes">Documentos Pendentes</option>
                    <option value="Experiência Insuficiente">Experiência Insuficiente</option>
                    <option value="Conflito de Horário">Conflito de Horário</option>
                    <option value="Não Atende Requisitos">Não Atende Requisitos</option>
                    <option value="Outro">Outro Motivo</option>
                </select>
            </div>
            
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Detalhes Adicionais (opcional)
                </label>
                <textarea name="rejection_details" 
                          id="rejection_details"
                          rows="3" 
                          class="w-full border border-gray-300 rounded px-3 py-2 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                          placeholder="Explique o motivo com mais detalhes..."></textarea>
            </div>
            
            <div class="flex gap-3 justify-end">
                <button type="button" 
                        onclick="closeRejectModal()" 
                        class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="submit" 
                        id="btn-submit-reject"
                        class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition-colors">
                    Rejeitar Candidatura
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// CSRF Token
const csrfToken = '<?= csrf_token() ?>';

// ===== CONFIGURAR TOASTR =====
if (typeof toastr !== 'undefined') {
    toastr.options = {
        closeButton: true,
        progressBar: true,
        positionClass: 'toast-top-right',
        timeOut: 5000,
        extendedTimeOut: 2000,
        showEasing: 'swing',
        hideEasing: 'linear',
        showMethod: 'fadeIn',
        hideMethod: 'fadeOut'
    };
}

// ===== HELPERS DE TOAST =====
function showSuccessToast(message, title = '✅ Sucesso') {
    if (typeof toastr !== 'undefined') {
        toastr.success(message, title);
    } else {
        alert(title + ': ' + message);
    }
}

function showErrorToast(message, title = '❌ Erro') {
    if (typeof toastr !== 'undefined') {
        toastr.error(message, title);
    } else {
        alert(title + ': ' + message);
    }
}

function showInfoToast(message, title = 'ℹ️ Info') {
    if (typeof toastr !== 'undefined') {
        toastr.info(message, title);
    } else {
        alert(title + ': ' + message);
    }
}

// ===== APROVAR CANDIDATURA =====
async function approveApplication(appId, vigilanteName) {
    if (!confirm(`Aprovar candidatura de ${vigilanteName}?`)) {
        return;
    }

    try {
        const response = await fetch(`/applications/${appId}/approve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ csrf: csrfToken })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccessToast(`Candidatura de <strong>${vigilanteName}</strong> foi aprovada!`);
            // Recarregar página para atualizar botões (incluindo botão de elegibilidade)
            setTimeout(() => location.reload(), 1000);
        } else {
            showErrorToast(result.message || 'Erro ao aprovar candidatura');
        }
    } catch (error) {
        console.error('Erro:', error);
        showErrorToast('Erro de conexão ao aprovar candidatura');
    }
}

// ===== ALTERNAR ELEGIBILIDADE A SUPERVISOR =====
async function toggleSupervisorEligibility(appId, isEligible, vigilanteName) {
    const action = isEligible ? 'marcar como ELEGÍVEL A SUPERVISOR' : 'REMOVER elegibilidade a supervisor';
    const message = `${action} para ${vigilanteName}?\n\n${isEligible ? '✓ Este vigilante terá PRIORIDADE na lista de supervisores.' : '✗ Este vigilante perderá a prioridade para supervisor.'}`;
    
    if (!confirm(message)) {
        return;
    }

    try {
        const response = await fetch(`/applications/${appId}/toggle-supervisor-eligible`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ 
                csrf: csrfToken,
                supervisor_eligible: isEligible
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const successMsg = isEligible 
                ? `👔 <strong>${vigilanteName}</strong> agora é elegível a supervisor!`
                : `<strong>${vigilanteName}</strong> não é mais elegível a supervisor.`;
            showSuccessToast(successMsg);
            
            // Recarregar página para atualizar badge e botão
            setTimeout(() => location.reload(), 1000);
        } else {
            showErrorToast(result.message || 'Erro ao atualizar elegibilidade');
        }
    } catch (error) {
        console.error('Erro:', error);
        showErrorToast('Erro de conexão ao atualizar elegibilidade');
    }
}

// ===== REVERTER PARA PENDENTE =====
async function revertToPending(appId, vigilanteName) {
    if (!confirm(`Reverter candidatura de ${vigilanteName} para PENDENTE?\n\nIsso irá desfazer a aprovação ou rejeição anterior.`)) {
        return;
    }

    try {
        const response = await fetch(`/applications/${appId}/revert`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ csrf: csrfToken })
        });
        
        const result = await response.json();
        
        if (result.success) {
            showSuccessToast(`Candidatura de <strong>${vigilanteName}</strong> foi revertida para PENDENTE!`);
            // Recarregar página para atualizar botões corretamente
            setTimeout(() => location.reload(), 1000);
        } else {
            showErrorToast(result.message || 'Erro ao reverter candidatura');
        }
    } catch (error) {
        console.error('Erro:', error);
        showErrorToast('Erro de conexão ao reverter candidatura');
    }
}

// ===== MODAL DE REJEIÇÃO =====
function showRejectModal(appId, vigilanteName) {
    document.getElementById('reject_app_id').value = appId;
    document.getElementById('reject_vigilante_name').textContent = vigilanteName;
    document.getElementById('modal-reject').classList.remove('hidden');
}

function closeRejectModal() {
    document.getElementById('modal-reject').classList.add('hidden');
    document.getElementById('form-reject').reset();
    
    // Resetar botão se estava desabilitado
    const btn = document.getElementById('btn-submit-reject');
    if (btn) {
        btn.disabled = false;
        btn.innerHTML = 'Rejeitar Candidatura';
    }
    
    // Limpar seleção em massa
    window.bulkRejectCheckboxes = null;
}

// ===== SUBMIT REJEIÇÃO =====
document.getElementById('form-reject')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const appId = formData.get('application_id');
    const rejectionReason = formData.get('rejection_reason');
    const rejectionDetails = formData.get('rejection_details');
    
    // Validação client-side
    if (!rejectionReason) {
        showErrorToast('Por favor, selecione um motivo de rejeição');
        document.getElementById('rejection_reason').focus();
        return;
    }
    
    // Verificar se é rejeição em massa
    if (window.bulkRejectCheckboxes && window.bulkRejectCheckboxes.length > 0) {
        // Processar rejeição em massa
        await processBulkReject(rejectionReason, rejectionDetails);
        return;
    }
    
    // Rejeição individual
    const btn = document.getElementById('btn-submit-reject');
    const originalText = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> Rejeitando...';
    
    try {
        const response = await fetch(`/applications/${appId}/reject`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                csrf: csrfToken,
                rejection_reason: rejectionReason,
                rejection_details: rejectionDetails
            })
        });
        
        const contentType = response.headers.get('content-type');
        
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            console.error('Resposta não-JSON:', text.substring(0, 500));
            showErrorToast('Erro: Resposta inválida do servidor');
            btn.disabled = false;
            btn.innerHTML = originalText;
            return;
        }
        
        const result = await response.json();
        
        if (response.ok && result.success) {
            showSuccessToast('Candidatura rejeitada. Vigilante foi notificado por email.');
            closeRejectModal();
            // Recarregar página para atualizar botões corretamente
            setTimeout(() => location.reload(), 1000);
        } else {
            showErrorToast(result.message || 'Erro ao rejeitar candidatura');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Erro completo:', error);
        showErrorToast('Erro de conexão ao rejeitar candidatura: ' + error.message);
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
});

// Fechar modal ao clicar fora
document.getElementById('modal-reject')?.addEventListener('click', function(e) {
    if (e.target === this) {
        closeRejectModal();
    }
});

// Fechar modal com ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeRejectModal();
    }
});

// ===== ATUALIZAR UI DINAMICAMENTE =====
function updateApplicationRow(appId, newStatus) {
    const row = document.querySelector(`[data-application-row][data-id="${appId}"]`);
    if (!row) return;
    
    // Atualizar atributo de status
    row.setAttribute('data-status', newStatus);
    
    // Cores por status
    const statusColors = {
        'pendente': 'bg-yellow-100 text-yellow-700 border-yellow-200',
        'aprovada': 'bg-green-100 text-green-700 border-green-200',
        'rejeitada': 'bg-red-100 text-red-700 border-red-200'
    };
    
    // Atualizar badge de status
    const statusBadge = row.querySelector('.rounded-full.border');
    if (statusBadge) {
        statusBadge.className = `px-3 py-1.5 rounded-full text-xs font-semibold border ${statusColors[newStatus]}`;
        statusBadge.textContent = newStatus.charAt(0).toUpperCase() + newStatus.slice(1);
    }
    
    // Atualizar cor de fundo da linha
    if (newStatus === 'pendente') {
        row.classList.add('bg-yellow-50/30');
        row.classList.remove('bg-gray-50');
    } else {
        row.classList.remove('bg-yellow-50/30');
    }
    
    // Buscar nome do vigilante
    const vigilanteName = row.querySelector('h3.font-semibold')?.textContent || '';
    
    // Reconstruir botões de ação
    const actionContainer = row.querySelector('.flex.items-center.gap-2');
    if (actionContainer) {
        // Guardar o HTML do badge atual
        const badge = actionContainer.querySelector('.rounded-full.border');
        const badgeHTML = badge ? badge.outerHTML : '';
        
        // Limpar e reconstruir
        actionContainer.innerHTML = badgeHTML;
        
        // Criar botões baseados no novo status
        if (newStatus === 'pendente') {
            actionContainer.innerHTML += `
                <button type="button" 
                        onclick="approveApplication(${appId}, '${vigilanteName.replace(/'/g, "\\'")}')"
                        class="px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 transition-colors"
                        title="Aprovar">
                    ✓ Aprovar
                </button>
                <button type="button"
                        onclick="showRejectModal(${appId}, '${vigilanteName.replace(/'/g, "\\'")}')"
                        class="px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded hover:bg-red-700 transition-colors"
                        title="Rejeitar">
                    ✗ Rejeitar
                </button>
            `;
        } else if (newStatus === 'aprovada') {
            actionContainer.innerHTML += `
                <button type="button"
                        onclick="revertToPending(${appId}, '${vigilanteName.replace(/'/g, "\\'")}')"
                        class="px-3 py-1.5 bg-gray-600 text-white text-xs font-semibold rounded hover:bg-gray-700 transition-colors"
                        title="Reverter para Pendente">
                    ↶ Pendente
                </button>
                <button type="button"
                        onclick="showRejectModal(${appId}, '${vigilanteName.replace(/'/g, "\\'")}')"
                        class="px-3 py-1.5 bg-red-600 text-white text-xs font-semibold rounded hover:bg-red-700 transition-colors"
                        title="Rejeitar">
                    ✗ Rejeitar
                </button>
            `;
        } else if (newStatus === 'rejeitada') {
            actionContainer.innerHTML += `
                <button type="button"
                        onclick="revertToPending(${appId}, '${vigilanteName.replace(/'/g, "\\'")}')"
                        class="px-3 py-1.5 bg-gray-600 text-white text-xs font-semibold rounded hover:bg-gray-700 transition-colors"
                        title="Reverter para Pendente">
                    ↶ Pendente
                </button>
                <button type="button" 
                        onclick="approveApplication(${appId}, '${vigilanteName.replace(/'/g, "\\'")}')"
                        class="px-3 py-1.5 bg-green-600 text-white text-xs font-semibold rounded hover:bg-green-700 transition-colors"
                        title="Aprovar">
                    ✓ Aprovar
                </button>
            `;
        }
    }
}

function updateStatistics() {
    // Contar candidaturas por status
    const rows = document.querySelectorAll('[data-application-row]');
    const stats = {
        pendente: 0,
        aprovada: 0,
        rejeitada: 0
    };
    
    rows.forEach(row => {
        const status = row.getAttribute('data-status');
        if (stats.hasOwnProperty(status)) {
            stats[status]++;
        }
    });
    
    // Atualizar estatísticas na UI
    const pendingEl = document.querySelector('[data-stat="pending"]');
    const approvedEl = document.querySelector('[data-stat="approved"]');
    const rejectedEl = document.querySelector('[data-stat="rejected"]');
    const totalEl = document.querySelector('[data-stat="total"]');
    
    if (pendingEl) pendingEl.textContent = stats.pendente;
    if (approvedEl) approvedEl.textContent = stats.aprovada;
    if (rejectedEl) rejectedEl.textContent = stats.rejeitada;
    if (totalEl) totalEl.textContent = stats.pendente + stats.aprovada + stats.rejeitada;
}

// ===== GERENCIAMENTO DE SELEÇÃO MÚLTIPLA =====
function updateBulkButtons() {
    const checkboxes = document.querySelectorAll('.application-checkbox:checked');
    const count = checkboxes.length;
    
    const btnApprove = document.getElementById('btn-approve-selected');
    const btnReject = document.getElementById('btn-reject-selected');
    const approveCount = document.getElementById('approve-count');
    const rejectCount = document.getElementById('reject-count');
    
    // Atualizar contadores
    if (approveCount) approveCount.textContent = `Aprovar (${count})`;
    if (rejectCount) rejectCount.textContent = `Rejeitar (${count})`;
    
    // Ativar/desativar botões
    if (count > 0) {
        // Ativar botões
        if (btnApprove) {
            btnApprove.dataset.disabled = 'false';
            btnApprove.classList.remove('opacity-50', 'cursor-not-allowed');
            btnApprove.classList.add('cursor-pointer');
            btnApprove.title = `Aprovar ${count} candidatura(s) selecionada(s)`;
        }
        if (btnReject) {
            btnReject.dataset.disabled = 'false';
            btnReject.classList.remove('opacity-50', 'cursor-not-allowed');
            btnReject.classList.add('cursor-pointer');
            btnReject.title = `Rejeitar ${count} candidatura(s) selecionada(s)`;
        }
    } else {
        // Desativar botões
        if (btnApprove) {
            btnApprove.dataset.disabled = 'true';
            btnApprove.classList.add('opacity-50', 'cursor-not-allowed');
            btnApprove.classList.remove('cursor-pointer');
            btnApprove.title = 'Selecione candidaturas para aprovar';
        }
        if (btnReject) {
            btnReject.dataset.disabled = 'true';
            btnReject.classList.add('opacity-50', 'cursor-not-allowed');
            btnReject.classList.remove('cursor-pointer');
            btnReject.title = 'Selecione candidaturas para rejeitar';
        }
    }
}

async function approveSelected() {
    const checkboxes = document.querySelectorAll('.application-checkbox:checked');
    if (checkboxes.length === 0) return;
    
    // Filtrar apenas candidaturas PENDENTES (case-insensitive e trim)
    const pendingCheckboxes = Array.from(checkboxes).filter(cb => {
        const status = (cb.dataset.appStatus || '').toLowerCase().trim();
        return status === 'pendente';
    });
    const nonPendingCount = checkboxes.length - pendingCheckboxes.length;
    
    if (pendingCheckboxes.length === 0) {
        showErrorToast('Nenhuma candidatura PENDENTE selecionada. Apenas candidaturas pendentes podem ser aprovadas.');
        return;
    }
    
    // Avisar sobre não-pendentes
    let warningMessage = '';
    if (nonPendingCount > 0) {
        warningMessage = `\n\n⚠️ ${nonPendingCount} candidatura(s) não-pendente(s) será(ão) ignorada(s).`;
    }
    
    const names = pendingCheckboxes.map(cb => cb.dataset.appName);
    const displayNames = names.length <= 5 ? names.join('\n• ') : `${names.slice(0, 5).join('\n• ')}\n... e mais ${names.length - 5}`;
    
    if (!confirm(`⚡ APROVAR ${pendingCheckboxes.length} CANDIDATURA(S) PENDENTE(S)?\n\n• ${displayNames}${warningMessage}\n\nTodas receberão email de aprovação!\n\nConfirmar?`)) {
        return;
    }
    
    // Usar apenas as pendentes
    const checkboxesToProcess = pendingCheckboxes;
    
    // Desabilitar botões durante processamento
    const btnApprove = document.getElementById('btn-approve-selected');
    const btnReject = document.getElementById('btn-reject-selected');
    const originalApproveText = document.getElementById('approve-count').innerHTML;
    
    btnApprove.dataset.disabled = 'true';
    btnReject.dataset.disabled = 'true';
    btnApprove.classList.add('opacity-50', 'cursor-not-allowed');
    btnReject.classList.add('opacity-50', 'cursor-not-allowed');
    document.getElementById('approve-count').innerHTML = '<span class="inline-block animate-spin">⏳</span> Processando...';
    
    let successCount = 0;
    let errorCount = 0;
    const errorMessages = [];
    const total = checkboxesToProcess.length;
    
    for (let i = 0; i < checkboxesToProcess.length; i++) {
        const checkbox = checkboxesToProcess[i];
        const appId = checkbox.dataset.appId;
        const appName = checkbox.dataset.appName;
        
        // Atualizar progresso
        document.getElementById('approve-count').innerHTML = `<span class="inline-block animate-spin">⏳</span> ${i + 1}/${total}`;
        
        try {
            const response = await fetch(`/applications/${appId}/approve`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ csrf: csrfToken })
            });
            
            const result = await response.json();
            
            if (result.success) {
                successCount++;
                checkbox.checked = false;
            } else {
                errorCount++;
                errorMessages.push(`${appName}: ${result.message || 'Erro desconhecido'}`);
                console.error(`Erro ao aprovar ${appName}:`, result.message);
            }
        } catch (error) {
            errorCount++;
            errorMessages.push(`${appName}: ${error.message || 'Erro de conexão'}`);
            console.error(`Erro ao aprovar ${appName}:`, error);
        }
    }
    
    // Desmarcar também as não-pendentes ignoradas
    checkboxes.forEach(cb => {
        if (cb.dataset.appStatus !== 'pendente') {
            cb.checked = false;
        }
    });
    
    // Restaurar botões
    document.getElementById('approve-count').innerHTML = originalApproveText;
    updateBulkButtons();
    
    // Feedback final
    if (successCount > 0) {
        let message = `✅ ${successCount} de ${total} candidatura(s) aprovada(s)!`;
        if (nonPendingCount > 0) {
            message += ` (${nonPendingCount} ignorada(s) por não serem pendentes)`;
        }
        showSuccessToast(message);
        // Recarregar página para atualizar botões (incluindo elegibilidade)
        setTimeout(() => location.reload(), 1500);
    }
    if (errorCount > 0) {
        if (successCount === 0) {
            showErrorToast(`❌ Erro ao aprovar todas as candidaturas.\n\n${errorMessages.slice(0, 3).join('\n')}`);
        } else {
            console.error('Erros detalhados:', errorMessages);
        }
    }
}

async function rejectSelected() {
    const checkboxes = document.querySelectorAll('.application-checkbox:checked');
    if (checkboxes.length === 0) return;
    
    // Filtrar apenas candidaturas PENDENTES (case-insensitive e trim)
    const pendingCheckboxes = Array.from(checkboxes).filter(cb => {
        const status = (cb.dataset.appStatus || '').toLowerCase().trim();
        return status === 'pendente';
    });
    const nonPendingCount = checkboxes.length - pendingCheckboxes.length;
    
    if (pendingCheckboxes.length === 0) {
        showErrorToast('Nenhuma candidatura PENDENTE selecionada. Apenas candidaturas pendentes podem ser rejeitadas.');
        return;
    }
    
    // Avisar sobre não-pendentes
    if (nonPendingCount > 0) {
        showInfoToast(`${nonPendingCount} candidatura(s) não-pendente(s) será(ão) ignorada(s).`);
    }
    
    // Abrir modal de rejeição em massa (apenas com pendentes)
    showBulkRejectModal(pendingCheckboxes);
}

function showBulkRejectModal(checkboxes) {
    const count = checkboxes.length;
    const names = Array.from(checkboxes).map(cb => cb.dataset.appName);
    const displayNames = names.length <= 3 ? names.join(', ') : `${names.slice(0, 3).join(', ')} e mais ${names.length - 3}`;
    
    // Preencher modal
    document.getElementById('reject-vigilante-name').textContent = `${count} Candidatura(s): ${displayNames}`;
    document.getElementById('modal-reject').classList.remove('hidden');
    
    // Armazenar checkboxes para processar depois
    window.bulkRejectCheckboxes = checkboxes;
}

async function processBulkReject(rejectionReason, rejectionDetails) {
    const checkboxes = window.bulkRejectCheckboxes;
    if (!checkboxes || checkboxes.length === 0) return;
    
    const btnSubmit = document.getElementById('btn-submit-reject');
    const originalText = btnSubmit.innerHTML;
    
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = '<span class="inline-block animate-spin">⏳</span> Processando...';
    
    let successCount = 0;
    let errorCount = 0;
    const errorMessages = [];
    const total = checkboxes.length;
    
    for (let i = 0; i < checkboxes.length; i++) {
        const checkbox = checkboxes[i];
        const appId = checkbox.dataset.appId;
        const appName = checkbox.dataset.appName;
        
        // Atualizar progresso
        btnSubmit.innerHTML = `<span class="inline-block animate-spin">⏳</span> Rejeitando ${i + 1}/${total}...`;
        
        try {
            const response = await fetch(`/applications/${appId}/reject`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    csrf: csrfToken,
                    rejection_reason: rejectionReason,
                    rejection_details: rejectionDetails
                })
            });
            
            const contentType = response.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Resposta não-JSON:', text.substring(0, 500));
                errorCount++;
                errorMessages.push(`${appName}: Resposta inválida do servidor`);
                continue;
            }
            
            const result = await response.json();
            
            if (response.ok && result.success) {
                successCount++;
                checkbox.checked = false;
            } else {
                errorCount++;
                errorMessages.push(`${appName}: ${result.message || 'Erro desconhecido'}`);
                console.error(`Erro ao rejeitar ${appName}:`, result.message);
            }
        } catch (error) {
            errorCount++;
            errorMessages.push(`${appName}: ${error.message || 'Erro de conexão'}`);
            console.error(`Erro ao rejeitar ${appName}:`, error);
        }
    }
    
    // Fechar modal e restaurar
    btnSubmit.innerHTML = originalText;
    closeRejectModal();
    window.bulkRejectCheckboxes = null;
    
    // Feedback final
    if (successCount > 0) {
        showSuccessToast(`✅ ${successCount} de ${total} candidatura(s) rejeitada(s)!` + (errorCount > 0 ? ` (${errorCount} com erro)` : ''));
        // Recarregar página para atualizar botões corretamente
        setTimeout(() => location.reload(), 1500);
    }
    if (errorCount > 0) {
        if (successCount === 0) {
            showErrorToast(`❌ Erro ao rejeitar todas as candidaturas.\n\n${errorMessages.slice(0, 3).join('\n')}`);
        } else {
            console.error('Erros detalhados:', errorMessages);
        }
    }
}

// ===== FILTROS AVANÇADOS =====
function applyFilters() {
    const searchTerm = document.getElementById('filter-search')?.value.toLowerCase().trim() || '';
    const statusFilter = document.getElementById('filter-status')?.value || '';
    const eligibleFilter = document.getElementById('filter-eligible')?.value || '';
    
    const rows = document.querySelectorAll('[data-application-row]');
    let visibleCount = 0;
    const totalCount = rows.length;
    
    rows.forEach(row => {
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        const status = row.dataset.status || '';
        const eligible = row.dataset.eligible || '';
        
        // Verificar todos os filtros
        const matchesSearch = !searchTerm || name.includes(searchTerm) || email.includes(searchTerm);
        const matchesStatus = !statusFilter || status === statusFilter;
        const matchesEligible = !eligibleFilter || eligible === eligibleFilter;
        
        // Mostrar/ocultar linha
        if (matchesSearch && matchesStatus && matchesEligible) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });
    
    // Atualizar contador
    document.getElementById('filtered-count').textContent = visibleCount;
    document.getElementById('total-count').textContent = totalCount;
    
    // Desmarcar checkboxes ocultos
    const hiddenCheckboxes = document.querySelectorAll('[data-application-row][style*="display: none"] .application-checkbox');
    hiddenCheckboxes.forEach(cb => cb.checked = false);
    
    // Atualizar botões de ação em lote
    updateBulkButtons();
}

function clearFilters() {
    document.getElementById('filter-search').value = '';
    document.getElementById('filter-status').value = '';
    document.getElementById('filter-eligible').value = '';
    applyFilters();
}

// ===== INICIALIZAÇÃO =====
document.addEventListener('DOMContentLoaded', function() {
    // Adicionar event listeners aos botões
    const btnApprove = document.getElementById('btn-approve-selected');
    const btnReject = document.getElementById('btn-reject-selected');
    
    if (btnApprove) {
        btnApprove.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.dataset.disabled === 'true') {
                return;
            }
            approveSelected();
        });
    }
    
    if (btnReject) {
        btnReject.addEventListener('click', function(e) {
            e.preventDefault();
            if (this.dataset.disabled === 'true') {
                return;
            }
            rejectSelected();
        });
    }
    
    // Inicializar estado dos botões e contadores de filtro
    updateBulkButtons();
    
    // Inicializar contador de filtros
    const rows = document.querySelectorAll('[data-application-row]');
    if (rows.length > 0) {
        document.getElementById('filtered-count').textContent = rows.length;
        document.getElementById('total-count').textContent = rows.length;
    }
});
</script>
