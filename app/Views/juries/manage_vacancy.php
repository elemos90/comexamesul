<?php
$title = 'Gestão de Júris - ' . htmlspecialchars($vacancy['title']);
$breadcrumbs = [
    ['label' => 'Júris', 'url' => url('/juries')],
    ['label' => 'Planeamento por Vaga', 'url' => url('/juries/planning-by-vacancy')],
    ['label' => 'Gestão de Alocações']
];

$juryModel = new \App\Models\Jury();
?>

<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <!-- Header -->
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-3xl font-bold text-gray-900"><?= htmlspecialchars($vacancy['title']) ?></h1>
            <p class="text-gray-600 mt-1">Criar e gerir estrutura de júris (locais, salas, horários)</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= url('/juries/planning-by-vacancy?vacancy_id=' . $vacancy['id']) ?>"
                class="px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Criar Novo Júri
            </a>
            <a href="<?= url('/juries/planning-by-vacancy') ?>"
                class="px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                ← Voltar
            </a>
        </div>
    </div>

    <!-- Barra de Estatísticas -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="text-xs text-gray-500 uppercase">Júris Criados</div>
            <div class="text-2xl font-bold text-primary-600"><?= $stats['total_juries'] ?></div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="text-xs text-gray-500 uppercase">Vigilantes Necessários</div>
            <div class="text-2xl font-bold text-blue-600"><?= $stats['total_juries'] * 2 ?></div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="text-xs text-gray-500 uppercase">Vigilantes Alocados</div>
            <?php
            $juryModel = new \App\Models\Jury();
            $vigilantesAlocados = $juryModel->statement(
                "SELECT COUNT(DISTINCT jv.vigilante_id) as total 
                     FROM jury_vigilantes jv 
                     INNER JOIN juries j ON j.id = jv.jury_id 
                     WHERE j.vacancy_id = :vacancy_id",
                ['vacancy_id' => $vacancy['id']]
            )[0]['total'] ?? 0;
            ?>
            <div
                class="text-2xl font-bold <?= $vigilantesAlocados >= ($stats['total_juries'] * 2) ? 'text-green-600' : 'text-orange-600' ?>">
                <?= $vigilantesAlocados ?>
            </div>
        </div>
        <div class="bg-white p-4 rounded-lg shadow-sm border">
            <div class="text-xs text-gray-500 uppercase">Total de Candidatos</div>
            <div class="text-2xl font-bold text-purple-600"><?= $stats['approved_candidates'] ?? 0 ?></div>
        </div>
    </div>

    <?php
    // Calcular status de alocação
    $vigilantesNecessarios = $stats['total_juries'] * 2;
    $percentualAlocado = $vigilantesNecessarios > 0 ? round(($vigilantesAlocados / $vigilantesNecessarios) * 100) : 0;
    $jurisSemVigilantes = $juryModel->statement(
        "SELECT COUNT(*) as total FROM juries j 
             WHERE j.vacancy_id = :vacancy_id 
             AND j.id NOT IN (SELECT DISTINCT jury_id FROM jury_vigilantes)",
        ['vacancy_id' => $vacancy['id']]
    )[0]['total'] ?? 0;
    ?>

    <!-- Status de Alocação -->
    <?php if ($stats['total_juries'] > 0 && $percentualAlocado < 100): ?>
        <div class="bg-orange-50 border-l-4 border-orange-500 p-4 rounded">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-orange-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <h4 class="font-semibold text-orange-900">⚠️ Alocação de Vigilantes Incompleta</h4>
                    <p class="text-sm text-orange-800 mt-1">
                        <strong><?= $vigilantesAlocados ?>/<?= $vigilantesNecessarios ?> vigilantes</strong> alocados
                        (<?= $percentualAlocado ?>% completo).<br>
                        <?php if ($jurisSemVigilantes > 0): ?>
                            <strong><?= $jurisSemVigilantes ?> júri(s)</strong> ainda sem nenhum vigilante alocado.
                        <?php endif; ?>
                    </p>
                    <div class="flex gap-2 mt-3">
                        <a href="<?= url('/juries/planning?vacancy_id=' . $vacancy['id']) ?>"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-orange-600 text-white text-sm font-medium rounded-lg hover:bg-orange-700 shadow-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                            Alocar Vigilantes Agora
                        </a>
                        <a href="<?= url('/juries/planning') ?>"
                            class="inline-flex items-center gap-2 px-4 py-2 border border-orange-600 text-orange-700 text-sm font-medium rounded-lg hover:bg-orange-50">
                            Ver Planeamento Avançado
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php elseif ($stats['total_juries'] > 0 && $percentualAlocado >= 100): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-green-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h4 class="font-semibold text-green-900">✅ Alocação Completa</h4>
                    <p class="text-sm text-green-800 mt-1">
                        Todos os <?= $vigilantesNecessarios ?> vigilantes necessários foram alocados com sucesso!
                    </p>
                    <a href="<?= url('/juries/planning?vacancy_id=' . $vacancy['id']) ?>"
                        class="inline-flex items-center gap-2 mt-2 px-3 py-1.5 text-green-700 text-sm font-medium hover:underline">
                        Ver detalhes no Planeamento Avançado →
                    </a>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h4 class="font-semibold text-blue-900">ℹ️ Próximo Passo: Criar Júris</h4>
                    <p class="text-sm text-blue-800 mt-1">
                        Comece criando a <strong>estrutura dos júris</strong> (local, sala, data, horário).<br>
                        Depois, utilize o <strong>Planeamento Avançado</strong> para alocar vigilantes e supervisores.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Alerta de Conflitos Globais -->
    <?php
    $totalConflicts = 0;
    foreach ($groupedJuries as $lg) {
        foreach ($lg['disciplines'] as $d) {
            foreach ($d['juries'] as $j) {
                if ($j['has_room_conflict'])
                    $totalConflicts++;
            }
        }
    }
    ?>
    <?php if ($totalConflicts > 0): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded">
            <div class="flex items-start">
                <svg class="w-6 h-6 text-red-600 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-bold text-red-900">⚠️ ATENÇÃO: Conflitos de Sala Detectados</h3>
                    <p class="text-sm text-red-800 mt-1">
                        <strong><?= $totalConflicts ?> júri(s)</strong> estão usando a mesma sala em horários sobrepostos.
                        Por favor, reveja as alocações de salas para resolver os conflitos.
                    </p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Lista de Júris Agrupados -->
    <?php if (empty($groupedJuries)): ?>
        <div class="bg-gray-50 border border-gray-200 rounded-lg p-8 text-center">
            <p class="text-gray-600">Nenhum júri criado ainda para esta vaga</p>
        </div>
    <?php else: ?>
        <?php foreach ($groupedJuries as $locationGroup): ?>
            <!-- Grupo por Local/Data -->
            <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 border-b px-6 py-4">
                    <h2 class="text-xl font-bold text-gray-900">
                        📍 <?= htmlspecialchars($locationGroup['location']) ?> -
                        <?= date('d/m/Y', strtotime($locationGroup['exam_date'])) ?>
                    </h2>
                </div>

                <div class="p-6 space-y-6">
                    <?php foreach ($locationGroup['disciplines'] as $discipline): ?>
                        <!-- Disciplina -->
                        <div class="border-l-4 border-primary-500 pl-4">
                            <div class="flex items-center justify-between mb-4">
                                <div>
                                    <h3 class="text-lg font-semibold text-gray-900">
                                        📚 <?= htmlspecialchars($discipline['subject']) ?>
                                    </h3>
                                    <p class="text-sm text-gray-600">
                                        🕐 <?= substr($discipline['start_time'], 0, 5) ?> -
                                        <?= substr($discipline['end_time'], 0, 5) ?>
                                    </p>
                                </div>
                                <div class="flex gap-2">
                                    <button type="button"
                                        class="btn-edit-discipline px-3 py-1.5 bg-primary-600 text-white text-sm rounded hover:bg-primary-700 flex items-center gap-1.5"
                                        data-discipline='<?= json_encode($discipline) ?>' title="Editar Disciplina e Salas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Editar Disciplina
                                    </button>
                                </div>
                            </div>

                            <!-- Júris (Salas) -->
                            <div class="space-y-4">
                                <?php foreach ($discipline['juries'] as $jury): ?>
                                    <div
                                        class="border <?= $jury['has_room_conflict'] ? 'border-red-300 bg-red-50' : 'border-gray-200 bg-white' ?> rounded-lg p-4 shadow-sm">
                                        <!-- Alerta de Conflito de Sala -->
                                        <?php if ($jury['has_room_conflict']): ?>
                                            <div class="mb-3 p-3 bg-red-100 border border-red-300 rounded-lg">
                                                <div class="flex items-start gap-2">
                                                    <svg class="w-5 h-5 text-red-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                                    </svg>
                                                    <div class="flex-1">
                                                        <h5 class="text-sm font-semibold text-red-900">⚠️ CONFLITO DE SALA</h5>
                                                        <p class="text-xs text-red-800 mt-1">
                                                            Esta sala está alocada para outro júri no mesmo horário:
                                                            <?php foreach ($jury['room_conflicts'] as $conflict): ?>
                                                                <br>• <strong><?= htmlspecialchars($conflict['subject']) ?></strong>
                                                                (<?= substr($conflict['start_time'], 0, 5) ?>-<?= substr($conflict['end_time'], 0, 5) ?>)
                                                            <?php endforeach; ?>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Header do Júri -->
                                        <div class="flex items-start justify-between">
                                            <div class="flex-1">
                                                <div class="flex items-center gap-2 mb-2">
                                                    <h4 class="font-semibold text-gray-900">
                                                        🏫 <?= htmlspecialchars($jury['room']) ?>
                                                    </h4>
                                                    <?php if ($jury['has_room_conflict']): ?>
                                                        <span class="px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">
                                                            ⚠️ Conflito
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="text-sm text-gray-600 space-y-1">
                                                    <p>
                                                        👥 <?= $jury['candidates_quota'] ?> candidatos |
                                                        2 vigilantes necessários
                                                        <?php if (!empty($jury['room_capacity'])): ?>
                                                            | Capacidade da sala: <?= $jury['room_capacity'] ?>
                                                        <?php endif; ?>
                                                    </p>
                                                    <?php if (!empty($jury['notes'])): ?>
                                                        <p class="text-blue-600 italic">
                                                            ℹ️ <?= htmlspecialchars($jury['notes']) ?>
                                                        </p>
                                                    <?php endif; ?>
                                                </div>
                                            </div>

                                            <div class="flex gap-2">
                                                <button type="button" onclick="showEditJuryModal(<?= $jury['id'] ?>)"
                                                    class="px-3 py-1 bg-gray-600 text-white text-sm rounded hover:bg-gray-500 flex items-center gap-1"
                                                    title="Editar Júri">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                    Editar
                                                </button>

                                                <button type="button"
                                                    class="btn-delete-jury px-3 py-1 bg-red-600 text-white text-sm rounded hover:bg-red-500 flex items-center gap-1"
                                                    data-id="<?= $jury['id'] ?>"
                                                    data-subject="<?= htmlspecialchars($jury['subject'], ENT_QUOTES) ?>"
                                                    data-room="<?= htmlspecialchars($jury['room'], ENT_QUOTES) ?>"
                                                    title="Eliminar Júri">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                    Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<datalist id="subjects-list"></datalist>
<!-- Modal: Criar Júris -->
<div id="modal-create-jury"
    class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white z-10">
            <div>
                <h2 class="text-xl font-bold text-gray-900">➕ Criar Júris para Exame</h2>
                <p class="text-sm text-gray-600 mt-1">Adicione múltiplas salas para o mesmo exame/horário</p>
            </div>
            <button type="button" class="modal-close text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="form-create-jury" class="p-6">
            <!-- PASSO 1: Disciplina/Exame -->
            <div class="mb-6 pb-6 border-b">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase flex items-center gap-2">
                    <span
                        class="flex items-center justify-center w-6 h-6 bg-primary-600 text-white rounded-full text-xs">1</span>
                    📚 Disciplina/Exame
                </h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selecione ou digite a disciplina
                        *</label>
                    <input type="text" id="create_subject" name="subject" list="subjects-list"
                        class="w-full rounded border border-gray-300 px-3 py-2"
                        placeholder="Ex: INGLÊS, MATEMÁTICA, FÍSICA..." required>

                    <p class="text-xs text-gray-500 mt-1">Digite uma nova disciplina ou selecione uma existente</p>
                </div>
            </div>

            <!-- PASSO 2: Data e Horário -->
            <div class="mb-6 pb-6 border-b">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase flex items-center gap-2">
                    <span
                        class="flex items-center justify-center w-6 h-6 bg-primary-600 text-white rounded-full text-xs">2</span>
                    📅 Data e Horário (único para todas as salas)
                </h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data *</label>
                        <input type="date" id="create_exam_date" name="exam_date"
                            class="w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hora Início *</label>
                        <input type="time" id="create_start_time" name="start_time"
                            class="w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hora Fim *</label>
                        <input type="time" id="create_end_time" name="end_time"
                            class="w-full rounded border border-gray-300 px-3 py-2" required>
                        <p id="create-time-validation" class="text-xs mt-1 hidden"></p>
                    </div>
                </div>
            </div>

            <!-- PASSO 3: Local -->
            <div class="mb-6 pb-6 border-b">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase flex items-center gap-2">
                    <span
                        class="flex items-center justify-center w-6 h-6 bg-primary-600 text-white rounded-full text-xs">3</span>
                    📍 Local do Exame
                </h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selecione o local *</label>
                    <select id="create_location_id" name="location_id"
                        class="w-full rounded border border-gray-300 px-3 py-2" required>
                        <option value="">Carregando locais...</option>
                    </select>
                </div>
            </div>

            <!-- PASSO 4: Salas -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase flex items-center gap-2">
                        <span
                            class="flex items-center justify-center w-6 h-6 bg-primary-600 text-white rounded-full text-xs">4</span>
                        🏫 Salas e Candidatos
                    </h3>
                    <button type="button" onclick="addRoomRow()"
                        class="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Adicionar Sala
                    </button>
                </div>

                <div class="bg-gray-50 rounded-lg p-4">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-sm font-semibold text-gray-700">
                                <th class="pb-2" style="width: 60%">Sala</th>
                                <th class="pb-2" style="width: 30%">Nº Candidatos</th>
                                <th class="pb-2" style="width: 10%"></th>
                            </tr>
                        </thead>
                        <tbody id="rooms-table-body">
                            <!-- Linhas de salas serão adicionadas aqui via JS -->
                        </tbody>
                    </table>

                    <div id="rooms-empty-state" class="text-center py-8 text-gray-500">
                        <svg class="w-12 h-12 mx-auto mb-2 text-gray-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <p class="text-sm">Clique em "Adicionar Sala" para começar</p>
                    </div>

                    <!-- Resumo -->
                    <div id="rooms-summary" class="hidden mt-4 pt-4 border-t border-gray-300">
                        <div class="flex justify-between text-sm">
                            <span class="font-semibold text-gray-700">Total:</span>
                            <div class="flex gap-4">
                                <span><strong id="total-rooms">0</strong> sala(s)</span>
                                <span><strong id="total-candidates">0</strong> candidatos</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões -->
            <div class="flex justify-end gap-3 pt-4 border-t">
                <button type="button"
                    class="modal-close px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-700 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Criar Júris
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Júri -->
<div id="modal-edit-jury" class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white z-10">
            <h2 class="text-xl font-bold text-gray-900">✏️ Editar Júri</h2>
            <button type="button" class="modal-close text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="form-edit-jury" class="p-6">
            <input type="hidden" id="edit_jury_id" name="jury_id">

            <!-- Informações Básicas -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase">📚 Disciplina</h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Disciplina *</label>
                    <input type="text" id="edit_subject" name="subject" list="subjects-list"
                        class="w-full rounded border border-gray-300 px-3 py-2" required>
                </div>
            </div>

            <!-- Data e Horário -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase">📅 Data e Horário</h3>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data *</label>
                        <input type="date" id="edit_exam_date" name="exam_date"
                            class="w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Início *</label>
                        <input type="time" id="edit_start_time" name="start_time"
                            class="w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Fim *</label>
                        <input type="time" id="edit_end_time" name="end_time"
                            class="w-full rounded border border-gray-300 px-3 py-2" required>
                        <p id="edit-time-validation" class="mt-1 text-xs hidden"></p>
                    </div>
                </div>
            </div>

            <!-- Local e Sala -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase">📍 Local e Sala</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Local *</label>
                        <select id="edit_location_id" name="location_id"
                            class="w-full rounded border border-gray-300 px-3 py-2" required>
                            <option value="">Carregando locais...</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Sala *</label>
                        <select id="edit_room_id" name="room_id" class="w-full rounded border border-gray-300 px-3 py-2"
                            required>
                            <option value="">Selecione um local primeiro</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Candidatos -->
            <div class="mb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase">👥 Candidatos</h3>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nº Candidatos *</label>
                    <input type="number" id="edit_candidates_quota" name="candidates_quota" min="1" max="300"
                        class="w-full rounded border border-gray-300 px-3 py-2" required>
                    <p id="edit-vigilantes-needed" class="text-xs text-blue-600 mt-1 font-medium"></p>
                </div>
            </div>

            <!-- Observações -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">Observações</label>
                <textarea id="edit_notes" name="notes" rows="3" class="w-full rounded border border-gray-300 px-3 py-2"
                    placeholder="Observações adicionais sobre este júri..."></textarea>
            </div>

            <!-- Aviso de Impacto -->
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1">
                        <h4 class="text-sm font-semibold text-yellow-900">⚠️ Atenção ao Editar</h4>
                        <ul class="text-xs text-yellow-800 mt-1 space-y-1">
                            <li>• Alterar data/horário pode criar conflitos com outros júris</li>
                            <li>• Alterar nº de candidatos recalcula vigilantes necessários</li>
                            <li>• Vigilantes já alocados serão mantidos (se possível)</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <button type="button"
                    class="modal-close px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-500">
                    💾 Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Adicionar Vigilante Manualmente -->
<div id="modal-add-vigilante"
    class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
        <div class="flex items-center justify-between p-6 border-b">
            <h2 class="text-xl font-bold text-gray-900">Adicionar Vigilante</h2>
            <button type="button" class="modal-close text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="form-add-vigilante" class="p-6">
            <input type="hidden" id="add_jury_id" name="jury_id">

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Vigilante Elegível</label>
                <select id="add_vigilante_id" name="vigilante_id"
                    class="w-full rounded border border-gray-300 px-3 py-2" required>
                    <option value="">Carregando...</option>
                </select>
                <p class="text-xs text-gray-500 mt-1">Apenas candidatos aprovados desta vaga, sem conflitos</p>
            </div>

            <div class="flex items-center justify-end gap-3">
                <button type="button"
                    class="modal-close px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-500">
                    Adicionar
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Disciplina/Exame (com múltiplas salas) -->
<div id="modal-edit-discipline"
    class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-5xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b sticky top-0 bg-white z-10">
            <div>
                <h2 class="text-xl font-bold text-gray-900">✏️ Editar Disciplina/Exame</h2>
                <p class="text-sm text-gray-600 mt-1">Altere informações comuns e gerencie salas</p>
            </div>
            <button type="button" class="modal-close text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <form id="form-edit-discipline" class="p-6 space-y-6">
            <!-- PASSO 1: Informações Comuns -->
            <div class="border-b pb-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-4 uppercase flex items-center gap-2">
                    <span
                        class="flex items-center justify-center w-6 h-6 bg-primary-600 text-white rounded-full text-xs">1</span>
                    📚 Informações Comuns (aplicadas a todas as salas)
                </h3>

                <div class="grid md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Disciplina *</label>
                        <input type="text" id="disc_subject" name="subject" list="subjects-list"
                            class="w-full rounded border border-gray-300 px-3 py-2"
                            placeholder="Ex: INGLÊS, MATEMÁTICA..." required>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Data do Exame *</label>
                        <input type="date" id="disc_exam_date" name="exam_date"
                            class="w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Início *</label>
                            <input type="time" id="disc_start_time" name="start_time"
                                class="w-full rounded border border-gray-300 px-3 py-2" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Fim *</label>
                            <input type="time" id="disc_end_time" name="end_time"
                                class="w-full rounded border border-gray-300 px-3 py-2" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PASSO 2: Salas -->
            <div>
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase flex items-center gap-2">
                        <span
                            class="flex items-center justify-center w-6 h-6 bg-primary-600 text-white rounded-full text-xs">2</span>
                        🏫 Salas do Exame
                    </h3>
                    <button type="button" id="btn-add-disc-room"
                        class="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Adicionar Sala
                    </button>
                </div>

                <div id="disc-rooms-container" class="space-y-3">
                    <!-- Salas serão injetadas aqui via JavaScript -->
                </div>
            </div>

            <!-- PASSO 3: Vigilantes -->
            <div class="border-t pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase flex items-center gap-2">
                        <span
                            class="flex items-center justify-center w-6 h-6 bg-green-600 text-white rounded-full text-xs">3</span>
                        👁️ Alocação de Vigilantes
                    </h3>
                    <button type="button" id="btn-edit-auto-vigilantes"
                        class="px-3 py-1.5 bg-green-600 text-white text-sm rounded hover:bg-green-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Auto-Alocar Todos
                    </button>
                </div>

                <div class="bg-green-50 border border-green-200 rounded-lg p-3 mb-4 text-sm text-green-800">
                    <strong>💡 Regra:</strong> 1 vigilante por cada 30 candidatos. Clique em "Auto" ou "Manual" para
                    alocar.
                </div>

                <div id="disc-vigilantes-container" class="space-y-3">
                    <!-- Vigilantes por sala serão injetados aqui via JavaScript -->
                </div>
            </div>

            <!-- PASSO 4: Supervisores -->
            <div class="border-t pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-700 uppercase flex items-center gap-2">
                        <span
                            class="flex items-center justify-center w-6 h-6 bg-purple-600 text-white rounded-full text-xs">4</span>
                        👔 Supervisores por Bloco
                    </h3>
                    <button type="button" id="btn-edit-auto-supervisors"
                        class="px-3 py-1.5 bg-purple-600 text-white text-sm rounded hover:bg-purple-700 flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        Auto-Distribuir
                    </button>
                </div>

                <div class="bg-purple-50 border border-purple-200 rounded-lg p-3 mb-4 text-sm text-purple-800">
                    <strong>ℹ️ Regra:</strong> Cada supervisor pode supervisionar até 10 salas.
                    Total de salas: <span id="disc-total-rooms">0</span> →
                    Supervisores necessários: <span id="disc-supervisors-needed" class="font-bold">0</span>
                </div>

                <div id="disc-supervisors-container" class="space-y-3">
                    <!-- Blocos de supervisores serão injetados aqui via JavaScript -->
                </div>
            </div>

            <!-- Aviso -->
            <div class="p-4 bg-yellow-50 border border-yellow-200 rounded-lg">
                <div class="flex items-start gap-2">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1">
                        <p class="text-sm text-yellow-900 font-medium">⚠️ Atenção:</p>
                        <p class="text-xs text-yellow-800 mt-1">
                            • Alterar data/horário afetará <strong>todas as salas</strong> deste exame<br>
                            • Vigilantes já alocados serão mantidos, mas podem ter conflitos de horário<br>
                            • Remover uma sala eliminará os vigilantes alocados a ela
                        </p>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <button type="button"
                    class="modal-close px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-500 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Salvar Alterações
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const vacancyId = <?= $vacancy['id'] ?>;
    const csrfToken = '<?= \App\Utils\Csrf::token() ?>';

    // CORREÇÃO #2: Utility Functions para Loading States
    function showLoading(button, message = 'Processando...') {
        const originalContent = button.innerHTML;
        button.dataset.originalContent = originalContent;
        button.disabled = true;
        button.classList.add('opacity-75', 'cursor-not-allowed');
        button.innerHTML = `
        <svg class="animate-spin inline-block w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        ${message}
    `;
        return originalContent;
    }

    function hideLoading(button) {
        if (button.dataset.originalContent) {
            button.innerHTML = button.dataset.originalContent;
            delete button.dataset.originalContent;
        }
        button.disabled = false;
        button.classList.remove('opacity-75', 'cursor-not-allowed');
    }

    // CORREÇÃO #3: Toast Notifications Helper
    function showToast(type, title, message, options = {}) {
        toastr.options = {
            closeButton: true,
            progressBar: true,
            positionClass: 'toast-top-right',
            timeOut: options.timeOut || (type === 'error' ? 10000 : 5000),
            extendedTimeOut: 2000,
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut'
        };

        toastr[type](message, title);
    }

    function showSuccessToast(message, title = '✅ Sucesso') {
        showToast('success', title, message);
    }

    function showErrorToast(message, title = '❌ Erro') {
        showToast('error', title, message);
    }

    function showWarningToast(message, title = '⚠️ Atenção') {
        showToast('warning', title, message);
    }

    function showInfoToast(message, title = 'ℹ️ Informação') {
        showToast('info', title, message);
    }

    // CORREÇÃO #4: Atualização Dinâmica da UI (sem reload)
    async function refreshStats() {
        try {
            const response = await fetch(appUrl(`/juries/vacancy/${vacancyId}/stats`));
            const result = await response.json();

            if (result.success && result.stats) {
                updateStatsUI(result.stats);
            }
        } catch (error) {
            console.error('Erro ao atualizar estatísticas:', error);
        }
    }

    function updateStatsUI(stats) {
        // Atualizar números nas estatísticas globais
        const statCards = document.querySelectorAll('[data-stat]');
        statCards.forEach(card => {
            const statType = card.dataset.stat;
            if (stats[statType] !== undefined) {
                const valueEl = card.querySelector('.text-2xl');
                if (valueEl) {
                    // Animação de mudança
                    valueEl.classList.add('animate-pulse');
                    valueEl.textContent = stats[statType];
                    setTimeout(() => valueEl.classList.remove('animate-pulse'), 1000);
                }
            }
        });
    }

    // ========== CRIAÇÃO DE JÚRIS ==========

    // Variáveis globais para gerenciar o modal de criação
    let availableLocations = [];
    let availableRooms = [];
    let roomRowCounter = 0;

    // Objeto global com dados mestre (para uso em edição e criação)
    let masterData = {
        locations: [],
        rooms: []
    };

    // Abrir modal de criação
    async function showCreateJuryModal() {
        // Limpar formulário
        document.getElementById('form-create-jury').reset();
        document.getElementById('rooms-table-body').innerHTML = '';
        document.getElementById('rooms-empty-state').classList.remove('hidden');
        document.getElementById('rooms-summary').classList.add('hidden');
        roomRowCounter = 0;

        // Carregar dados mestre
        await loadMasterData();

        // Abrir modal
        document.getElementById('modal-create-jury').classList.remove('hidden');
        document.getElementById('modal-create-jury').classList.add('flex');
    }

    // Carregar dados mestre (locais, salas, disciplinas)
    async function loadMasterData() {
        try {
            // Carregar locais e salas
            const locationResponse = await fetch(appUrl('/api/master-data/locations-rooms'), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (!locationResponse.ok) {
                throw new Error(`HTTP ${locationResponse.status}: ${locationResponse.statusText}`);
            }

            const contentType = locationResponse.headers.get('content-type');
            if (!contentType || !contentType.includes('application/json')) {
                const text = await locationResponse.text();
                console.error('Resposta não é JSON:', text.substring(0, 200));
                throw new Error('Servidor retornou HTML em vez de JSON');
            }

            const locationData = await locationResponse.json();

            if (locationData.success) {
                availableLocations = locationData.locations || [];
                availableRooms = locationData.rooms || [];

                // Atualizar masterData global
                masterData.locations = availableLocations;
                masterData.rooms = availableRooms;

                // Preencher dropdown de locais (se existir)
                const locationSelect = document.getElementById('create_location_id');
                if (locationSelect) {
                    locationSelect.innerHTML = '<option value="">Selecione um local...</option>';

                    if (availableLocations.length === 0) {
                        locationSelect.innerHTML += '<option value="" disabled>Nenhum local cadastrado</option>';
                    } else {
                        availableLocations.forEach(loc => {
                            const option = document.createElement('option');
                            option.value = loc.id;
                            option.textContent = loc.name;
                            locationSelect.appendChild(option);
                        });
                    }
                }
            } else {
                throw new Error(locationData.message || 'Erro ao carregar locais');
            }

            // Carregar disciplinas usadas
            const subjectResponse = await fetch(appUrl(`/api/vacancies/${vacancyId}/subjects`), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (subjectResponse.ok) {
                const subjectData = await subjectResponse.json();

                if (subjectData.success) {
                    const datalist = document.getElementById('subjects-list');
                    if (datalist) {
                        datalist.innerHTML = '';
                        (subjectData.subjects || []).forEach(subject => {
                            const option = document.createElement('option');
                            option.value = subject;
                            datalist.appendChild(option);
                        });
                    }
                }
            }
        } catch (error) {
            console.error('Erro ao carregar dados mestre:', error);
            showErrorToast(
                'Erro ao carregar dados: ' + error.message + '. Verifique o console para mais detalhes.',
                'Erro de Carregamento'
            );
        }
    }

    // Adicionar linha de sala
    function addRoomRow() {
        const locationId = document.getElementById('create_location_id').value;

        if (!locationId) {
            showWarningToast('Selecione um local primeiro!', 'Atenção');
            return;
        }

        // Filtrar salas do local selecionado
        const roomsForLocation = availableRooms.filter(room => room.location_id == locationId);

        if (roomsForLocation.length === 0) {
            showWarningToast('Este local não possui salas cadastradas.', 'Atenção');
            return;
        }

        roomRowCounter++;
        const rowId = `room-row-${roomRowCounter}`;

        const tableBody = document.getElementById('rooms-table-body');
        const row = document.createElement('tr');
        row.id = rowId;
        row.className = 'border-b border-gray-200';
        row.innerHTML = `
        <td class="py-2 pr-2">
            <select name="rooms[${roomRowCounter}][room_id]" class="room-select w-full rounded border border-gray-300 px-2 py-1 text-sm" required>
                <option value="">Selecione uma sala...</option>
                ${roomsForLocation.map(room => `<option value="${room.id}" data-capacity="${room.capacity}">${room.code} - ${room.name} (cap: ${room.capacity})</option>`).join('')}
            </select>
        </td>
        <td class="py-2 pr-2">
            <input type="number" name="rooms[${roomRowCounter}][candidates]" 
                   min="1" max="100" 
                   class="candidates-input w-full rounded border border-gray-300 px-2 py-1 text-sm" 
                   placeholder="Ex: 30" required>
        </td>
        <td class="py-2">
            <button type="button" onclick="removeRoomRow('${rowId}')" 
                    class="text-red-600 hover:text-red-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </td>
    `;

        tableBody.appendChild(row);

        // Adicionar listener para atualizar resumo
        row.querySelector('.candidates-input').addEventListener('input', updateRoomsSummary);
        row.querySelector('.room-select').addEventListener('change', updateRoomsSummary);

        // Esconder estado vazio e mostrar resumo
        document.getElementById('rooms-empty-state').classList.add('hidden');
        updateRoomsSummary();
    }

    // Remover linha de sala
    function removeRoomRow(rowId) {
        document.getElementById(rowId).remove();
        updateRoomsSummary();

        // Se não houver mais linhas, mostrar estado vazio
        const tableBody = document.getElementById('rooms-table-body');
        if (tableBody.children.length === 0) {
            document.getElementById('rooms-empty-state').classList.remove('hidden');
            document.getElementById('rooms-summary').classList.add('hidden');
        }
    }

    // Atualizar resumo de salas
    function updateRoomsSummary() {
        const rows = document.querySelectorAll('#rooms-table-body tr');
        let totalRooms = 0;
        let totalCandidates = 0;

        rows.forEach(row => {
            const roomSelect = row.querySelector('.room-select');
            const candidatesInput = row.querySelector('.candidates-input');

            if (roomSelect.value) {
                totalRooms++;
            }

            const candidates = parseInt(candidatesInput.value) || 0;
            totalCandidates += candidates;
        });

        document.getElementById('total-rooms').textContent = totalRooms;
        document.getElementById('total-candidates').textContent = totalCandidates;

        if (totalRooms > 0) {
            document.getElementById('rooms-summary').classList.remove('hidden');
        } else {
            document.getElementById('rooms-summary').classList.add('hidden');
        }
    }

    // Atualizar salas disponíveis quando local mudar
    document.getElementById('create_location_id')?.addEventListener('change', function () {
        // Limpar salas existentes quando mudar de local
        const tableBody = document.getElementById('rooms-table-body');
        if (tableBody.children.length > 0) {
            if (confirm('Trocar de local vai limpar todas as salas adicionadas. Continuar?')) {
                tableBody.innerHTML = '';
                document.getElementById('rooms-empty-state').classList.remove('hidden');
                document.getElementById('rooms-summary').classList.add('hidden');
            } else {
                // Reverter seleção
                this.selectedIndex = 0;
            }
        }
    });

    // Validar horários no modal de criação
    function validateCreateTimeRange() {
        const startTime = document.getElementById('create_start_time').value;
        const endTime = document.getElementById('create_end_time').value;
        const validationEl = document.getElementById('create-time-validation');
        const endTimeInput = document.getElementById('create_end_time');

        validationEl.classList.add('hidden');
        endTimeInput.classList.remove('border-red-500', 'border-green-500');

        if (!startTime || !endTime) return true;

        const start = new Date(`2000-01-01T${startTime}`);
        const end = new Date(`2000-01-01T${endTime}`);
        const diffMinutes = (end - start) / 60000;

        if (end <= start) {
            validationEl.textContent = '❌ Horário inválido';
            validationEl.classList.remove('hidden', 'text-green-600');
            validationEl.classList.add('text-red-600');
            endTimeInput.classList.add('border-red-500');
            return false;
        }

        validationEl.textContent = `✓ Duração: ${diffMinutes} min`;
        validationEl.classList.remove('hidden', 'text-red-600');
        validationEl.classList.add('text-green-600');
        endTimeInput.classList.add('border-green-500');
        return true;
    }

    // Listeners para validação em tempo real
    document.getElementById('create_start_time')?.addEventListener('change', validateCreateTimeRange);
    document.getElementById('create_end_time')?.addEventListener('change', validateCreateTimeRange);

    // Submit do formulário de criação
    document.getElementById('form-create-jury')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        // Validar se há pelo menos uma sala
        const rows = document.querySelectorAll('#rooms-table-body tr');
        if (rows.length === 0) {
            showWarningToast('Adicione pelo menos uma sala!', 'Atenção');
            return;
        }

        // Coletar dados do formulário
        const subject = document.getElementById('create_subject').value;
        const examDate = document.getElementById('create_exam_date').value;
        const startTime = document.getElementById('create_start_time').value;
        const endTime = document.getElementById('create_end_time').value;
        const locationId = document.getElementById('create_location_id').value;

        // Coletar salas e candidatos
        const rooms = [];
        rows.forEach(row => {
            const roomId = row.querySelector('.room-select').value;
            const candidates = row.querySelector('.candidates-input').value;

            if (roomId && candidates) {
                rooms.push({
                    room_id: parseInt(roomId),
                    candidates_quota: parseInt(candidates)
                });
            }
        });

        if (rooms.length === 0) {
            showWarningToast('Preencha todas as salas corretamente!', 'Atenção');
            return;
        }

        const button = e.submitter;
        showLoading(button, 'Criando júris...');

        const requestData = {
            vacancy_id: vacancyId,
            subject: subject,
            exam_date: examDate,
            start_time: startTime,
            end_time: endTime,
            location_id: locationId,
            rooms: rooms,
            csrf: csrfToken
        };

        console.log('📤 Enviando requisição para /juries/create-bulk:', requestData);

        try {
            const response = await fetch(appUrl('/juries/create-bulk'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(requestData)
            });

            console.log('📥 Resposta recebida:', {
                status: response.status,
                statusText: response.statusText,
                headers: Object.fromEntries(response.headers.entries())
            });

            const responseText = await response.text();
            console.log('📄 Corpo da resposta (texto):', responseText);

            let result;
            try {
                result = JSON.parse(responseText);
                console.log('✅ JSON parseado com sucesso:', result);
            } catch (jsonError) {
                console.error('❌ ERRO ao parsear JSON:', jsonError);
                console.error('Texto recebido:', responseText.substring(0, 500));
                throw new Error('Resposta inválida do servidor');
            }

            if (result.success) {
                showSuccessToast(result.message || `${rooms.length} júri(s) criado(s) com sucesso!`, 'Sucesso!');

                // Fechar modal
                document.getElementById('modal-create-jury').classList.add('hidden');
                document.getElementById('modal-create-jury').classList.remove('flex');

                // Recarregar página após 1.5s
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorToast(result.message || 'Erro ao criar júris', 'Falha na Criação');
                hideLoading(button);
            }
        } catch (error) {
            console.error('Erro:', error);
            showErrorToast(error.message || 'Erro de conexão', 'Erro de Conexão');
            hideLoading(button);
        }
    });

    // ========== EDIÇÃO E REMOÇÃO DE JÚRIS ==========

    // Abrir modal de edição e carregar dados do júri
    async function showEditJuryModal(juryId) {
        try {
            // Buscar dados do júri
            const response = await fetch(appUrl(`/juries/${juryId}/details`));
            const result = await response.json();

            if (!result.success) {
                showErrorToast(result.message || 'Júri não encontrado', 'Erro');
                return;
            }

            const jury = result.jury;

            // Preencher campos básicos
            document.getElementById('edit_jury_id').value = jury.id;
            document.getElementById('edit_subject').value = jury.subject;
            document.getElementById('edit_exam_date').value = jury.exam_date;
            document.getElementById('edit_start_time').value = jury.start_time;
            document.getElementById('edit_end_time').value = jury.end_time;
            document.getElementById('edit_candidates_quota').value = jury.candidates_quota;
            document.getElementById('edit_notes').value = jury.notes || '';

            // Carregar locais e salas no modal de edição
            const editLocationSelect = document.getElementById('edit_location_id');
            const editRoomSelect = document.getElementById('edit_room_id');

            // Popular locais
            editLocationSelect.innerHTML = '<option value="">Selecione um local...</option>';
            masterData.locations.forEach(loc => {
                const opt = document.createElement('option');
                opt.value = loc.id;
                opt.textContent = loc.name;
                if (loc.id == jury.location_id) {
                    opt.selected = true;
                }
                editLocationSelect.appendChild(opt);
            });

            // Popular salas do local selecionado
            if (jury.location_id) {
                const locationRooms = masterData.rooms.filter(r => r.location_id == jury.location_id);
                editRoomSelect.innerHTML = '<option value="">Selecione uma sala...</option>';
                locationRooms.forEach(room => {
                    const opt = document.createElement('option');
                    opt.value = room.id;
                    opt.textContent = `${room.code} (cap: ${room.capacity})`;
                    if (room.id == jury.room_id) {
                        opt.selected = true;
                    }
                    editRoomSelect.appendChild(opt);
                });
            }

            // Adicionar listener para mudar salas quando mudar local
            editLocationSelect.onchange = function () {
                const locationId = this.value;
                const locationRooms = masterData.rooms.filter(r => r.location_id == locationId);

                editRoomSelect.innerHTML = '<option value="">Selecione uma sala...</option>';
                locationRooms.forEach(room => {
                    const opt = document.createElement('option');
                    opt.value = room.id;
                    opt.textContent = `${room.code} (cap: ${room.capacity})`;
                    editRoomSelect.appendChild(opt);
                });
            };

            // Calcular vigilantes necessários
            updateEditVigilantesNeeded();

            // Abrir modal
            document.getElementById('modal-edit-jury').classList.remove('hidden');
            document.getElementById('modal-edit-jury').classList.add('flex');

        } catch (error) {
            console.error('Erro ao carregar júri:', error);
            showErrorToast(error.message, 'Erro ao Carregar');
        }
    }

    // Calcular vigilantes necessários (edição)
    function updateEditVigilantesNeeded() {
        const candidates = parseInt(document.getElementById('edit_candidates_quota').value) || 0;
        const needed = Math.ceil(candidates / 30);
        const el = document.getElementById('edit-vigilantes-needed');
        el.textContent = `🔢 Vigilantes necessários: ${needed}`;
    }

    // Validar horários no modal de edição
    function validateEditTimeRange() {
        const startTime = document.getElementById('edit_start_time').value;
        const endTime = document.getElementById('edit_end_time').value;
        const validationEl = document.getElementById('edit-time-validation');
        const endTimeInput = document.getElementById('edit_end_time');

        validationEl.classList.add('hidden');
        endTimeInput.classList.remove('border-red-500', 'border-green-500');

        if (!startTime || !endTime) return true;

        const start = new Date(`2000-01-01T${startTime}`);
        const end = new Date(`2000-01-01T${endTime}`);
        const diffMinutes = (end - start) / 60000;

        if (end <= start) {
            validationEl.textContent = '❌ Horário inválido';
            validationEl.classList.remove('hidden', 'text-green-600');
            validationEl.classList.add('text-red-600');
            endTimeInput.classList.add('border-red-500');
            return false;
        }

        validationEl.textContent = `✓ Duração: ${diffMinutes} min`;
        validationEl.classList.remove('hidden', 'text-red-600');
        validationEl.classList.add('text-green-600');
        endTimeInput.classList.add('border-green-500');
        return true;
    }

    // Listeners para validação em tempo real
    document.getElementById('edit_candidates_quota')?.addEventListener('input', updateEditVigilantesNeeded);
    document.getElementById('edit_start_time')?.addEventListener('change', validateEditTimeRange);
    document.getElementById('edit_end_time')?.addEventListener('change', validateEditTimeRange);

    // Submit do formulário de edição
    document.getElementById('form-edit-jury')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const juryId = document.getElementById('edit_jury_id').value;
        const formData = new FormData(this);
        const data = Object.fromEntries(formData);

        const button = e.submitter;
        showLoading(button, 'Salvando...');

        try {
            const response = await fetch(appUrl(`/juries/${juryId}/update`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    ...data,
                    csrf: csrfToken
                })
            });

            const result = await response.json();

            if (result.success) {
                showSuccessToast(result.message, 'Júri Atualizado');

                // Fechar modal
                document.getElementById('modal-edit-jury').classList.add('hidden');
                document.getElementById('modal-edit-jury').classList.remove('flex');

                // Recarregar página após 1.5s
                setTimeout(() => location.reload(), 1500);
            } else {
                showErrorToast(result.message || 'Erro ao atualizar', 'Falha na Atualização');
                hideLoading(button);
            }
        } catch (error) {
            showErrorToast(error.message, 'Erro de Conexão');
            hideLoading(button);
        }
    });

    // Eliminar júri
    async function deleteJury(juryId, subject, room) {
        // Confirmação dupla
        const confirmed = confirm(
            `⚠️ ATENÇÃO - Esta ação é IRREVERSÍVEL!\n\n` +
            `Eliminar júri:\n` +
            `📚 ${subject}\n` +
            `🏫 Sala ${room}\n\n` +
            `Isto irá:\n` +
            `• Remover o júri permanentemente\n` +
            `• Desalocar todos os vigilantes\n` +
            `• Apagar todos os registros relacionados\n\n` +
            `Tem ABSOLUTA CERTEZA?`
        );

        if (!confirmed) return;

        // Segunda confirmação simplificada
        const doubleConfirm = confirm(
            `ÚLTIMA CONFIRMAÇÃO!\n\n` +
            `Tem certeza absoluta que deseja eliminar este júri?\n` +
            `Esta ação não pode ser desfeita.`
        );

        if (!doubleConfirm) return;

        // Remover a exigência de digitar "ELIMINAR"
        // const userInput = prompt('Digite "ELIMINAR" (em maiúsculas) para confirmar:');
        // if (userInput !== 'ELIMINAR') { ... }

        try {
            const response = await fetch(appUrl(`/juries/${juryId}/delete`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ csrf: csrfToken })
            });

            const result = await response.json();

            if (result.success) {
                showSuccessToast(
                    `Júri "${subject}" (Sala ${room}) foi eliminado permanentemente.`,
                    '🗑️ Júri Eliminado'
                );
                setTimeout(() => location.reload(), 2000);
            } else {
                showErrorToast(result.message || 'Erro ao eliminar', 'Falha na Eliminação');
            }
        } catch (error) {
            showErrorToast(error.message, 'Erro de Conexão');
        }
    }

    // ========== EDIÇÃO DE DISCIPLINA/EXAME ==========

    let discRoomCounter = 0;
    let currentDisciplineData = null;

    function showEditDisciplineModal(disciplineData) {
        currentDisciplineData = disciplineData;

        // Preencher campos comuns
        document.getElementById('disc_subject').value = disciplineData.subject;
        document.getElementById('disc_exam_date').value = disciplineData.juries[0].exam_date;
        document.getElementById('disc_start_time').value = disciplineData.start_time;
        document.getElementById('disc_end_time').value = disciplineData.end_time;

        // Limpar e preencher salas
        const container = document.getElementById('disc-rooms-container');
        container.innerHTML = '';
        discRoomCounter = 0;

        disciplineData.juries.forEach((jury, index) => {
            addDiscRoomRow(jury);
        });

        // Abrir modal
        document.getElementById('modal-edit-discipline').classList.remove('hidden');
        document.getElementById('modal-edit-discipline').classList.add('flex');
    }

    function addDiscRoomRow(juryData = null) {
        const container = document.getElementById('disc-rooms-container');
        const index = discRoomCounter++;

        const roomDiv = document.createElement('div');
        roomDiv.className = 'disc-room-row border border-gray-200 rounded-lg p-4 bg-gray-50';
        roomDiv.dataset.index = index;

        if (juryData) {
            roomDiv.dataset.juryId = juryData.id;
        }

        roomDiv.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex-1 grid grid-cols-3 gap-3">
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Sala *</label>
                    <input type="text" name="rooms[${index}][room]" 
                           value="${juryData ? juryData.room : ''}"
                           class="w-full rounded border border-gray-300 px-3 py-2 text-sm" 
                           placeholder="Ex: Sala 101" required>
                    ${juryData ? `<input type="hidden" name="rooms[${index}][jury_id]" value="${juryData.id}">` : ''}
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Nº Candidatos *</label>
                    <input type="number" name="rooms[${index}][candidates_quota]" 
                           value="${juryData ? juryData.candidates_quota : ''}"
                           min="1" class="w-full rounded border border-gray-300 px-3 py-2 text-sm" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Vigilantes Alocados</label>
                    <div class="px-3 py-2 bg-gray-100 rounded text-sm text-center font-medium">
                        ${juryData && juryData.vigilantes_count ? juryData.vigilantes_count : 0}
                    </div>
                </div>
            </div>
            <div class="pt-6">
                <button type="button" onclick="removeDiscRoom(${index})" 
                        class="p-2 text-red-600 hover:bg-red-50 rounded transition-colors"
                        title="Remover sala">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </div>
        ${juryData && juryData.vigilantes_count > 0 ? `
        <div class="mt-2 p-2 bg-yellow-50 border border-yellow-200 rounded text-xs text-yellow-800">
            ⚠️ Esta sala tem ${juryData.vigilantes_count} vigilante(s) alocado(s). Removê-la eliminará essas alocações.
        </div>
        ` : ''}
    `;

        container.appendChild(roomDiv);
    }

    function removeDiscRoom(index) {
        const roomRow = document.querySelector(`.disc-room-row[data-index="${index}"]`);
        if (roomRow) {
            const juryId = roomRow.dataset.juryId;
            if (juryId) {
                if (!confirm('Tem certeza que deseja remover esta sala?\n\nOs vigilantes alocados a ela serão removidos.')) {
                    return;
                }
            }
            roomRow.remove();
        }
    }

    // Listener para adicionar nova sala
    document.getElementById('btn-add-disc-room')?.addEventListener('click', () => addDiscRoomRow());

    // Listeners para botões de editar disciplina
    document.addEventListener('click', function (e) {
        if (e.target.closest('.btn-edit-discipline')) {
            try {
                const button = e.target.closest('.btn-edit-discipline');
                console.log('🔘 Botão clicado, JSON bruto:', button.dataset.discipline);
                const disciplineData = JSON.parse(button.dataset.discipline);
                console.log('✅ JSON parseado com sucesso:', disciplineData);
                showEditDisciplineModal(disciplineData);
            } catch (error) {
                console.error('❌ Erro ao parsear JSON da disciplina:', error);
                console.error('JSON bruto:', e.target.closest('.btn-edit-discipline').dataset.discipline);
                alert('Erro ao carregar dados da disciplina. Verifique o console.');
            }
        }
    });

    // Submit do formulário de edição de disciplina
    document.getElementById('form-edit-discipline')?.addEventListener('submit', async function (e) {
        e.preventDefault();

        const formData = new FormData(this);

        // Coletar dados
        const data = {
            subject: formData.get('subject'),
            exam_date: formData.get('exam_date'),
            start_time: formData.get('start_time'),
            end_time: formData.get('end_time'),
            rooms: [],
            csrf: csrfToken
        };

        // Coletar salas
        let roomIndex = 0;
        while (formData.has(`rooms[${roomIndex}][room]`)) {
            const room = {
                room: formData.get(`rooms[${roomIndex}][room]`),
                candidates_quota: parseInt(formData.get(`rooms[${roomIndex}][candidates_quota]`))
            };

            // Se tem jury_id, é uma sala existente
            if (formData.has(`rooms[${roomIndex}][jury_id]`)) {
                room.jury_id = parseInt(formData.get(`rooms[${roomIndex}][jury_id]`));
            }

            data.rooms.push(room);
            roomIndex++;
        }

        if (data.rooms.length === 0) {
            alert('❌ Adicione pelo menos uma sala');
            return;
        }

        const button = e.submitter;
        showLoading(button, 'Salvando...');

        try {
            const response = await fetch(appUrl(`/juries/vacancy/${vacancyId}/update-discipline`), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(data)
            });

            const result = await response.json();

            if (result.success) {
                showSuccessToast(result.message, 'Disciplina Atualizada');

                // Fechar modal
                document.getElementById('modal-edit-discipline').classList.add('hidden');
                document.getElementById('modal-edit-discipline').classList.remove('flex');

                // Recarregar página
                setTimeout(() => location.reload(), 1500);
            } else {
                alert(`❌ Erro: ${result.message}`);
                hideLoading(button);
            }
        } catch (error) {
            console.error('Erro ao atualizar disciplina:', error);
            alert('❌ Erro ao atualizar disciplina. Tente novamente.');
            hideLoading(button);
        }
    });

    // Fechar modais
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function () {
            this.closest('.modal').classList.add('hidden');
            this.closest('.modal').classList.remove('flex');
        });
    });

    // Event Listener Global para botões de eliminar
    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-delete-jury');
        if (btn) {
            e.preventDefault();
            console.log('Botão eliminar clicado:', btn.dataset);
            const { id, subject, room } = btn.dataset;

            if (id) {
                // Passar valores padrão se subject ou room estiverem vazios
                deleteJury(id, subject || 'Sem disciplina', room || 'Sem sala');
            } else {
                console.error('ID do júri não encontrado no botão');
                alert('Erro: ID do júri não encontrado.');
            }
        }
    });

    // Inicialização
    document.addEventListener('DOMContentLoaded', async function () {
        try {
            await loadMasterData();

            // Verificar se deve abrir modal de edição automaticamente
            const urlParams = new URLSearchParams(window.location.search);
            const editJuryId = urlParams.get('edit_jury');

            if (editJuryId) {
                setTimeout(() => {
                    if (typeof showEditJuryModal === 'function') {
                        showEditJuryModal(parseInt(editJuryId));
                    }
                    // Limpar URL
                    const newUrl = window.location.pathname;
                    window.history.replaceState({}, document.title, newUrl);
                }, 500);
            }
        } catch (error) {
            console.error('Erro ao carregar dados mestre:', error);
        }
    });

    // ========== VIGILANTES E SUPERVISORES NO EDIT MODAL ==========

    let editVigilantes = {}; // { roomIndex: [vigilanteIds] }
    let editBlockSupervisors = []; // [supervisorId per block]
    let editEligibleVigilantes = [];
    let editEligibleSupervisors = [];

    async function buildEditVigilantesSection() {
        const container = document.getElementById('disc-vigilantes-container');
        if (!container) return;

        container.innerHTML = '<div class="text-center py-4 text-gray-500">Carregando vigilantes...</div>';

        // Load eligible vigilantes
        try {
            const basePath = window.location.pathname.split('/public/')[0] || '';
            const response = await fetch(`${basePath}/public/api/vigilantes/eligible?vacancy_id=${vacancyId}`);
            const result = await response.json();
            editEligibleVigilantes = result.success ? result.vigilantes || [] : [];
        } catch (error) {
            console.error('Erro ao carregar vigilantes:', error);
            editEligibleVigilantes = [];
        }

        // Build UI for each room
        const roomRows = document.querySelectorAll('.disc-room-row');
        if (roomRows.length === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-4">Adicione salas primeiro</p>';
            return;
        }

        let html = '';
        roomRows.forEach((row, index) => {
            const roomName = row.querySelector('input[name*="[room]"]')?.value || `Sala ${index + 1}`;
            const candidates = parseInt(row.querySelector('input[name*="[candidates_quota]"]')?.value) || 0;
            const required = Math.ceil(candidates / 30);
            const allocated = editVigilantes[index] || [];
            const isComplete = allocated.length >= required;

            html += `
                <div class="p-4 border rounded-lg ${isComplete ? 'border-green-300 bg-green-50' : 'border-red-300 bg-red-50'}">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <span class="font-semibold">${roomName}</span>
                            <span class="text-sm text-gray-500">${candidates} candidatos</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-1 rounded text-sm font-medium ${isComplete ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'}">
                                ${allocated.length}/${required} vig.
                            </span>
                            <button type="button" onclick="editAutoAllocateRoom(${index})" 
                                class="px-2 py-1 bg-green-600 text-white text-xs rounded hover:bg-green-500">Auto</button>
                            <button type="button" onclick="editOpenVigilanteSelector(${index})" 
                                class="px-2 py-1 bg-blue-600 text-white text-xs rounded hover:bg-blue-500">Manual</button>
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-1">
                        ${allocated.map(vid => {
                const v = editEligibleVigilantes.find(x => x.id === vid);
                return `<span class="inline-flex items-center gap-1 px-2 py-1 bg-white border rounded text-xs">
                                ${v ? v.name : 'Vigilante ' + vid}
                                <button type="button" onclick="editRemoveVigilante(${index}, ${vid})" class="text-red-500 hover:text-red-700">×</button>
                            </span>`;
            }).join('')}
                        ${allocated.length === 0 ? '<span class="text-sm text-gray-500 italic">Nenhum vigilante</span>' : ''}
                    </div>
                </div>
            `;
        });

        container.innerHTML = html;
    }

    function editAutoAllocateRoom(roomIndex) {
        const roomRows = document.querySelectorAll('.disc-room-row');
        const row = roomRows[roomIndex];
        if (!row) return;

        const candidates = parseInt(row.querySelector('input[name*="[candidates_quota]"]')?.value) || 0;
        const required = Math.ceil(candidates / 30);
        const currentlyAllocated = editVigilantes[roomIndex] || [];
        const allAllocated = Object.values(editVigilantes).flat();
        const available = editEligibleVigilantes.filter(v => !allAllocated.includes(v.id));

        const needed = required - currentlyAllocated.length;
        const toAllocate = available.slice(0, needed).map(v => v.id);

        editVigilantes[roomIndex] = [...currentlyAllocated, ...toAllocate];
        buildEditVigilantesSection();
    }

    function editRemoveVigilante(roomIndex, vigilanteId) {
        if (!editVigilantes[roomIndex]) return;
        editVigilantes[roomIndex] = editVigilantes[roomIndex].filter(id => id !== vigilanteId);
        buildEditVigilantesSection();
    }

    function editOpenVigilanteSelector(roomIndex) {
        const allAllocated = Object.values(editVigilantes).flat();
        const available = editEligibleVigilantes.filter(v => !allAllocated.includes(v.id));

        if (available.length === 0) {
            alert('❌ Não há vigilantes disponíveis');
            return;
        }

        const options = available.map((v, i) => `${i + 1}. ${v.name}`).join('\n');
        const choice = prompt(`Selecione o vigilante:\n\n${options}`);

        if (choice) {
            const index = parseInt(choice) - 1;
            if (index >= 0 && index < available.length) {
                if (!editVigilantes[roomIndex]) editVigilantes[roomIndex] = [];
                editVigilantes[roomIndex].push(available[index].id);
                buildEditVigilantesSection();
            }
        }
    }

    async function buildEditSupervisorsSection() {
        const container = document.getElementById('disc-supervisors-container');
        if (!container) return;

        container.innerHTML = '<div class="text-center py-4 text-gray-500">Carregando supervisores...</div>';

        // Load eligible supervisors
        try {
            const basePath = window.location.pathname.split('/public/')[0] || '';
            const response = await fetch(`${basePath}/public/api/supervisors/eligible?vacancy_id=${vacancyId}`);
            const result = await response.json();
            editEligibleSupervisors = result.success ? result.supervisors || [] : [];
        } catch (error) {
            console.error('Erro ao carregar supervisores:', error);
            editEligibleSupervisors = [];
        }

        const roomRows = document.querySelectorAll('.disc-room-row');
        const numRooms = roomRows.length;
        const MAX_JURIS_POR_SUPERVISOR = 10;
        const numBlocks = Math.ceil(numRooms / MAX_JURIS_POR_SUPERVISOR);

        document.getElementById('disc-total-rooms').textContent = numRooms;
        document.getElementById('disc-supervisors-needed').textContent = numBlocks;

        if (numRooms === 0) {
            container.innerHTML = '<p class="text-gray-500 text-center py-4">Adicione salas primeiro</p>';
            return;
        }

        // Initialize block supervisors if needed
        if (editBlockSupervisors.length !== numBlocks) {
            editBlockSupervisors = new Array(numBlocks).fill(null);
        }

        let html = '';
        for (let blockIndex = 0; blockIndex < numBlocks; blockIndex++) {
            const startRoom = blockIndex * MAX_JURIS_POR_SUPERVISOR;
            const endRoom = Math.min(startRoom + MAX_JURIS_POR_SUPERVISOR, numRooms);
            const blockRooms = [];
            for (let i = startRoom; i < endRoom; i++) {
                const r = roomRows[i]?.querySelector('input[name*="[room]"]')?.value || `Sala ${i + 1}`;
                blockRooms.push(r);
            }

            const supervisor = editBlockSupervisors[blockIndex];
            const isComplete = supervisor !== null;

            html += `
                <div class="p-4 border-2 rounded-lg ${isComplete ? 'border-green-300 bg-green-50' : 'border-purple-300 bg-purple-50'}">
                    <div class="flex items-center justify-between mb-2">
                        <div>
                            <span class="font-semibold">Bloco ${blockIndex + 1}</span>
                            <span class="text-sm text-gray-600 ml-2">${blockRooms.join(', ')}</span>
                        </div>
                        <span class="px-2 py-1 rounded text-sm font-medium ${isComplete ? 'bg-green-100 text-green-700' : 'bg-purple-100 text-purple-700'}">
                            ${isComplete ? '✓ Atribuído' : 'Pendente'}
                        </span>
                    </div>
                    <select onchange="editAssignBlockSupervisor(${blockIndex}, this.value)"
                        class="w-full rounded border border-gray-300 px-3 py-2 text-sm">
                        <option value="">-- Selecionar Supervisor --</option>
                        ${editEligibleSupervisors.map(s => `
                            <option value="${s.id}" ${supervisor === s.id ? 'selected' : ''}>
                                ${s.name} (${s.role_label || s.role || 'Supervisor'})
                            </option>
                        `).join('')}
                    </select>
                </div>
            `;
        }

        container.innerHTML = html;
    }

    function editAssignBlockSupervisor(blockIndex, supervisorId) {
        editBlockSupervisors[blockIndex] = supervisorId ? parseInt(supervisorId) : null;
        buildEditSupervisorsSection();
    }

    // Auto-alocar todos vigilantes
    document.getElementById('btn-edit-auto-vigilantes')?.addEventListener('click', function () {
        const roomRows = document.querySelectorAll('.disc-room-row');
        roomRows.forEach((row, index) => {
            editAutoAllocateRoom(index);
        });
    });

    // Auto-distribuir supervisores
    document.getElementById('btn-edit-auto-supervisors')?.addEventListener('click', function () {
        const roomRows = document.querySelectorAll('.disc-room-row');
        const numBlocks = Math.ceil(roomRows.length / 10);
        editBlockSupervisors = [];
        for (let i = 0; i < numBlocks; i++) {
            if (editEligibleSupervisors[i]) {
                editBlockSupervisors[i] = editEligibleSupervisors[i].id;
            } else if (editEligibleSupervisors.length > 0) {
                editBlockSupervisors[i] = editEligibleSupervisors[i % editEligibleSupervisors.length].id;
            }
        }
        buildEditSupervisorsSection();
    });

    // Refresh vigilantes/supervisors when modal opens or rooms change
    const origShowEditDisciplineModal = showEditDisciplineModal;
    showEditDisciplineModal = async function (disciplineData) {
        editVigilantes = {};
        editBlockSupervisors = [];

        origShowEditDisciplineModal(disciplineData);

        // Load existing vigilantes from juries data
        if (disciplineData.juries) {
            disciplineData.juries.forEach((jury, index) => {
                if (jury.vigilantes && Array.isArray(jury.vigilantes)) {
                    editVigilantes[index] = jury.vigilantes.map(v => v.user_id || v.id);
                }
            });
        }

        // Build sections after short delay to ensure DOM is ready
        setTimeout(async () => {
            await buildEditVigilantesSection();
            await buildEditSupervisorsSection();
        }, 100);
    };
</script>