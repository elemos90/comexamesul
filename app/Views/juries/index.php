<?php
$title = 'Júris de exames';
$breadcrumbs = [
    ['label' => 'Júris']
];
$canManage = in_array($user['role'], ['coordenador', 'membro'], true);
$isVigilante = $user['role'] === 'vigilante';
?>
<div class="space-y-4">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-lg font-semibold text-gray-900">Lista de Júris</h1>
            <p class="text-xs text-gray-500">Visualização geral dos júris organizados por data e local</p>
        </div>
        <div class="flex gap-2">
            <?php if ($canManage): ?>
            <a href="<?= url('/juries/planning') ?>" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Criar & Alocar
            </a>
            <?php endif; ?>
            <button onclick="window.print()" class="btn btn-secondary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Imprimir
            </button>
            <button type="button" data-modal-target="modal-share-email" class="btn btn-primary">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Email
            </button>
        </div>
    </div>

    <?php if ($isVigilante): ?>
        <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplina</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sala</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horário</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($juries as $jury): ?>
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-700"><?= htmlspecialchars($jury['subject']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($jury['room']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars(date('d/m/Y', strtotime($jury['exam_date']))) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars(substr($jury['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr($jury['end_time'], 0, 5)) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($jury['supervisor_name'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$juries): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-6 text-center text-sm text-gray-500">Ainda não foi alocado a júris.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="grid lg:grid-cols-5 gap-6">
            <!-- Painel lateral de vigilantes -->
            <div class="lg:col-span-1 space-y-3">
                <div class="bg-white border border-gray-200 rounded-lg p-3 sticky top-4">
                    <h2 class="text-xs font-semibold text-gray-600 uppercase mb-1">Vigilantes disponíveis</h2>
                    <p class="text-xs text-gray-400 mb-3">Arraste para alocar</p>
                    <ul id="available-vigilantes" class="space-y-2" data-pool="true">
                        <?php foreach ($vigilantes as $vigilante): ?>
                            <li class="draggable-item" data-id="<?= $vigilante['id'] ?>" tabindex="0">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-medium text-gray-700 truncate"><?= htmlspecialchars($vigilante['name']) ?></p>
                                        <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($vigilante['email']) ?></p>
                                    </div>
                                    <?php if ((int)$vigilante['jury_count'] > 0): ?>
                                        <span class="flex-shrink-0 inline-flex items-center justify-center w-6 h-6 text-xs font-semibold rounded-full <?= (int)$vigilante['jury_count'] >= 3 ? 'bg-amber-100 text-amber-700' : 'bg-blue-100 text-blue-700' ?>" title="<?= (int)$vigilante['jury_count'] ?> júri(s) alocado(s)">
                                            <?= (int)$vigilante['jury_count'] ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        <?php if (!$vigilantes): ?>
                            <li class="text-xs text-gray-500">Sem vigilantes disponíveis.</li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>

            <!-- Área principal de júris agrupados -->
            <div class="lg:col-span-4 space-y-4">
                <?php foreach ($groupedJuries as $group): ?>
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden">
                        <!-- Cabeçalho do grupo (Disciplina + Data/HoraO -->
                        <div class="px-5 py-4 bg-primary-50 border-b-2 border-primary-200">
                            <div class="flex items-center justify-between">
                                <div>
                                    <h3 class="text-lg font-bold text-primary-900"><?= htmlspecialchars($group['subject']) ?></h3>
                                    <p class="text-sm text-primary-700 mt-1">
                                        <span class="font-medium"><?= htmlspecialchars(date('d/m/Y', strtotime($group['exam_date']))) ?></span>
                                        · <?= htmlspecialchars(substr($group['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr($group['end_time'], 0, 5)) ?>
                                    </p>
                                </div>
                                <div class="flex items-center gap-2 text-xs">
                                    <?php 
                                    // Contar total de salas
                                    $totalSalas = 0;
                                    foreach($group['locations'] as $loc) { $totalSalas += count($loc['juries']); }
                                    ?>
                                    <span class="px-3 py-1.5 bg-white text-primary-700 font-semibold rounded border border-primary-300">
                                        <?= $totalSalas ?> sala(s)
                                    </span>
                                    
                                    <?php if ($canManage): ?>
                                    <?php 
                                        // Coletar todos os júris para o botão de editar batch
                                        // AVISO: A edição em batch pode precisar de ajustes para lidar com múltiplos locais de uma só vez, 
                                        // ou devemos limitar a edição por local.
                                        // Por enquanto, vamos coletar tudo flat.
                                        $allJuriesFlat = [];
                                        foreach($group['locations'] as $loc) {
                                            foreach($loc['juries'] as $j) {
                                                $allJuriesFlat[] = [
                                                    'id' => $j['id'], 'room' => $j['room'], 'candidates_quota' => $j['candidates_quota']
                                                ];
                                            }
                                        }
                                    ?>
                                    <button type="button" class="px-3 py-1.5 bg-blue-600 text-white font-medium rounded hover:bg-blue-500 flex items-center gap-1" data-action="edit-discipline-batch" data-group='<?= json_encode([
                                        'subject' => $group['subject'],
                                        'exam_date' => $group['exam_date'],
                                        'start_time' => substr($group['start_time'], 0, 5),
                                        'end_time' => substr($group['end_time'], 0, 5),
                                        'location' => implode(', ', array_keys($group['locations'])), // Listar locais
                                        'juries' => $allJuriesFlat
                                    ]) ?>'>
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                        Editar Disciplina
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Salas do grupo (Agrupadas por Local) -->
                        <div class="p-5 space-y-6">
                            <?php foreach ($group['locations'] as $locationName => $locData): ?>
                                
                                <!-- Sub-cabeçalho do Local -->
                                <div class="bg-gray-100 px-3 py-2 rounded border border-gray-200 flex items-center gap-2">
                                     <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                     </svg>
                                     <h4 class="font-bold text-gray-700 uppercase text-sm"><?= htmlspecialchars($locationName) ?></h4>
                                </div>

                                <div class="space-y-4 ml-2 pl-4 border-l-2 border-gray-200">
                                <?php foreach ($locData['juries'] as $jury): ?>
                                    <div class="border border-gray-200 rounded-lg overflow-hidden hover:border-primary-300 transition-colors bg-white">
                                        <!-- Cabeçalho da sala -->
                                        <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 flex items-center justify-between">
                                            <div class="flex items-center gap-3">
                                                <span class="inline-flex items-center justify-center w-8 h-8 bg-primary-600 text-white text-sm font-bold rounded">
                                                    <?= htmlspecialchars($jury['room']) ?>
                                                </span>
                                                <div>
                                                    <p class="text-sm font-semibold text-gray-800">Sala <?= htmlspecialchars($jury['room']) ?></p>
                                                    <p class="text-xs text-gray-500"><?= (int)$jury['candidates_quota'] ?> candidatos</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <button type="button" class="btn-edit-inline px-2 py-1.5 text-xs font-medium bg-blue-100 text-blue-700 rounded hover:bg-blue-200 flex items-center gap-1" data-jury-id="<?= $jury['id'] ?>" title="Edição rápida">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                                    </svg>
                                                    Rápido
                                                </button>
                                                <button type="button" class="px-2 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 rounded hover:bg-gray-200" data-action="open-edit-jury" data-jury='<?= json_encode([
                                                    'id' => $jury['id'],
                                                    'subject' => $jury['subject'],
                                                    'exam_date' => $jury['exam_date'],
                                                    'start_time' => substr($jury['start_time'], 0, 5),
                                                    'end_time' => substr($jury['end_time'], 0, 5),
                                                    'location' => $jury['location'],
                                                    'room' => $jury['room'],
                                                    'candidates_quota' => $jury['candidates_quota'],
                                                    'notes' => $jury['notes'],
                                                ]) ?>' title="Edição completa">Completo</button>
                                                <form method="POST" action="<?= url('/juries/' . $jury['id'] . '/delete') ?>" onsubmit="return confirm('Eliminar este júri?');" class="inline">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                                    <button type="submit" class="px-2 py-1.5 text-xs font-medium bg-red-100 text-red-600 rounded hover:bg-red-200">Eliminar</button>
                                                </form>
                                            </div>
                                        </div>
    
                                        <!-- Corpo: Vigilantes e Supervisor -->
                                        <div class="grid md:grid-cols-2 gap-4 p-4">
                                            <!-- Vigilantes -->
                                            <div>
                                                <div class="flex items-center justify-between mb-2">
                                                    <h4 class="text-sm font-semibold text-gray-700 uppercase">Vigilantes</h4>
                                                    <span class="text-xs text-gray-500"><?= count($jury['vigilantes']) ?> alocado(s)</span>
                                                </div>
                                                <ul class="dropzone min-h-[100px] border-2 border-dashed border-gray-300 rounded-lg p-3 space-y-2" data-jury="<?= $jury['id'] ?>" data-assign-url="/juries/<?= $jury['id'] ?>/assign" data-unassign-url="/juries/<?= $jury['id'] ?>/unassign">
                                                    <?php foreach ($jury['vigilantes'] as $member): ?>
                                                        <li class="draggable-item" data-id="<?= $member['id'] ?>" tabindex="0">
                                                            <div>
                                                                <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($member['name']) ?></p>
                                                                <p class="text-xs text-gray-500"><?= htmlspecialchars($member['email']) ?></p>
                                                            </div>
                                                        </li>
                                                    <?php endforeach; ?>
                                                    <?php if (!$jury['vigilantes']): ?>
                                                        <li class="text-xs text-gray-400 italic text-center py-4">Arraste vigilantes para aqui</li>
                                                    <?php endif; ?>
                                                </ul>
                                            </div>
    
                                            <!-- Supervisor -->
                                            <div>
                                                <h4 class="text-sm font-semibold text-gray-700 uppercase mb-2">Supervisor</h4>
                                                <div class="dropzone-single min-h-[100px] border-2 border-dashed border-amber-300 rounded-lg p-3 bg-amber-50" data-jury="<?= $jury['id'] ?>" data-set-url="/juries/<?= $jury['id'] ?>/set-supervisor">
                                                    <?php if (!empty($jury['supervisor_name'])): ?>
                                                        <div class="draggable-item bg-amber-100 border-amber-300" data-id="<?= $jury['supervisor_id'] ?>" tabindex="0">
                                                            <div>
                                                                <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($jury['supervisor_name']) ?></p>
                                                                <p class="text-xs text-amber-700 font-medium">Supervisor designado</p>
                                                            </div>
                                                        </div>
                                                    <?php else: ?>
                                                        <p class="text-xs text-amber-600 italic text-center py-4">Arraste um supervisor elegível</p>
                                                    <?php endif; ?>
                                                </div>
                                                
                                                <!-- Pool de supervisores (Apenas exibido na primeira sala do grupo no design original, mas aqui repetimos para simplicidade ou refatorar para ficar fixo) -->
                                                <!-- Removido daqui para reduzir ruído visual, ou manter apenas se necessário -->
                                            </div>
                                        </div>
    
                                        <?php if ($jury['has_report']): ?>
                                            <div class="px-4 pb-3">
                                                <div class="flex items-center gap-2 text-xs text-green-700 bg-green-50 border border-green-200 rounded px-3 py-2">
                                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                    <span class="font-medium">Relatório submetido</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </div>

                        <?php
                            // CALCULAR TOTAIS DA DISCIPLINA (Candidate, Jury, Vigilante)
                            $discTotalCandidates = 0;
                            $discTotalJuries = 0;
                            $discTotalVigilantes = 0;

                            foreach ($group['locations'] as $loc) {
                                foreach ($loc['juries'] as $j) {
                                    $discTotalCandidates += (int)$j['candidates_quota'];
                                    $discTotalJuries++;
                                    $discTotalVigilantes += count($j['vigilantes']);
                                }
                            }
                        ?>
                        
                        <!-- LINHA DE TOTAL DA DISCIPLINA (Igual ao Planeamento Avançado) -->
                        <div style="background: linear-gradient(90deg, #fbbf24 0%, #f59e0b 100%); border-top: 3px solid #d97706; border-bottom: 3px solid #d97706;" class="px-6 py-3">
                            <div class="flex items-center justify-between">
                                <span class="font-extrabold text-amber-900 text-base uppercase tracking-wide">
                                    📚 TOTAL <?= strtoupper($group['subject']) ?>
                                </span>
                                <div class="flex gap-8 font-bold text-amber-900">
                                    <span class="flex items-center gap-2">
                                        <span class="text-sm opacity-75">Candidatos:</span>
                                        <span class="text-lg"><?= number_format($discTotalCandidates, 0) ?></span>
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <span class="text-sm opacity-75">Júris:</span>
                                        <span class="text-lg"><?= number_format($discTotalJuries, 0) ?></span>
                                    </span>
                                    <span class="flex items-center gap-2">
                                        <span class="text-sm opacity-75">Vigilantes:</span>
                                        <span class="text-lg"><?= number_format($discTotalVigilantes, 0) ?></span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <?php
                // NOVO: Totais por disciplina
                if (!empty($groupedJuries)):
                    $disciplineTotals = [];
                    foreach ($groupedJuries as $group) {
                        $subject = $group['subject'];
                        if (!isset($disciplineTotals[$subject])) {
                            $disciplineTotals[$subject] = ['candidates' => 0, 'vigilantes' => 0, 'juries' => 0];
                        }
                        foreach ($group['locations'] as $location => $locData) {
                            foreach ($locData['juries'] as $jury) {
                                $disciplineTotals[$subject]['candidates'] += (int)$jury['candidates_quota'];
                                $disciplineTotals[$subject]['vigilantes'] += count($jury['vigilantes'] ?? []);
                                $disciplineTotals[$subject]['juries']++;
                            }
                        }
                    }
                    foreach ($disciplineTotals as $subject => $totals):
                ?>
                    <div class="bg-gradient-to-r from-amber-300 to-amber-400 border-t-4 border-b-4 border-amber-600 rounded-lg p-4 shadow-lg">
                        <div class="flex items-center justify-between">
                            <h3 class="text-base font-extrabold text-amber-900 uppercase tracking-wide flex items-center gap-2">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                                </svg>
                                📚 TOTAL <?= strtoupper($subject) ?>
                            </h3>
                            <div class="flex gap-6 font-bold text-amber-900">
                                <span class="flex items-center gap-2">
                                    <span class="text-sm opacity-75">Candidatos:</span>
                                    <span class="text-lg"><?= number_format($totals['candidates'], 0) ?></span>
                                </span>
                                <span class="flex items-center gap-2">
                                    <span class="text-sm opacity-75">Júris:</span>
                                    <span class="text-lg"><?= $totals['juries'] ?></span>
                                </span>
                                <span class="flex items-center gap-2">
                                    <span class="text-sm opacity-75">Vigilantes:</span>
                                    <span class="text-lg"><?= $totals['vigilantes'] ?></span>
                                </span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; endif; ?>
                
                <?php if (!$groupedJuries): ?>
                    <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <p class="mt-4 text-sm text-gray-500">Nenhum júri registado. Crie o primeiro júri para começar.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($canManage): ?>
<!-- Modal: Criar Júri -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-create-jury" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-2xl mx-4 rounded-lg shadow-lg p-6 focus:outline-none max-h-[90vh] overflow-y-auto" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Novo júri</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form method="POST" action="<?= url('/juries') ?>" class="grid md:grid-cols-2 gap-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="jury_subject">Disciplina</label>
                <input type="text" id="jury_subject" name="subject" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                <p class="mt-1 text-xs text-gray-500">Júris da mesma disciplina devem ter o mesmo horário</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="jury_exam_date">Data</label>
                <input type="date" id="jury_exam_date" name="exam_date" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="jury_start">Início</label>
                    <input type="time" id="jury_start" name="start_time" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="jury_end">Fim</label>
                    <input type="time" id="jury_end" name="end_time" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="jury_location">Local</label>
                <input type="text" id="jury_location" name="location" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="jury_room">Sala</label>
                <input type="text" id="jury_room" name="room" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="jury_quota">Candidatos</label>
                <input type="number" id="jury_quota" name="candidates_quota" min="1" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="jury_notes">Observações</label>
                <textarea id="jury_notes" name="notes" rows="3" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="Instruções especiais, requisitos, etc."></textarea>
            </div>
            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Júri -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-edit-jury" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-2xl mx-4 rounded-lg shadow-lg p-6 focus:outline-none max-h-[90vh] overflow-y-auto" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Editar júri</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form method="POST" data-form="edit-jury" class="grid md:grid-cols-2 gap-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="edit_jury_subject">Disciplina</label>
                <input type="text" id="edit_jury_subject" name="subject" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="edit_jury_exam_date">Data</label>
                <input type="date" id="edit_jury_exam_date" name="exam_date" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="edit_jury_start">Início</label>
                    <input type="time" id="edit_jury_start" name="start_time" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="edit_jury_end">Fim</label>
                    <input type="time" id="edit_jury_end" name="end_time" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="edit_jury_location">Local</label>
                <input type="text" id="edit_jury_location" name="location" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="edit_jury_room">Sala</label>
                <input type="text" id="edit_jury_room" name="room" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="edit_jury_quota">Candidatos</label>
                <input type="number" id="edit_jury_quota" name="candidates_quota" min="1" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="edit_jury_notes">Observações</label>
                <textarea id="edit_jury_notes" name="notes" rows="3" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></textarea>
            </div>
            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500">Atualizar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Criar Exames por Local -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-create-location" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-6xl mx-4 rounded-lg shadow-lg p-6 focus:outline-none max-h-[90vh] overflow-y-auto" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Criar Júris de Exames por Local</h2>
                <p class="text-sm text-gray-500 mt-1">Um local pode albergar várias disciplinas com seus respectivos júris</p>
            </div>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        
        <form method="POST" action="<?= url('/juries/create-location-batch') ?>" id="form-create-location">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            
            <!-- Informações do Local -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-blue-900 mb-3 uppercase flex items-center gap-2">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                    </svg>
                    Informações do Local de Realização
                </h3>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700" for="location_name">Nome do Local *</label>
                        <input type="text" id="location_name" name="location" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="Ex: Campus Central, Escola Secundária ABC, etc." required>
                        <p class="mt-1 text-xs text-gray-500">Este local pode albergar múltiplas disciplinas</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="location_date">Data dos Exames *</label>
                        <input type="date" id="location_date" name="exam_date" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>
                </div>
            </div>

            <!-- Disciplinas e Salas -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z"/>
                        </svg>
                        Disciplinas e Salas
                    </h3>
                    <button type="button" id="btn-add-discipline" class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-500 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Adicionar Disciplina
                    </button>
                </div>
                
                <div id="disciplines-container" class="space-y-4">
                    <!-- Disciplina inicial -->
                    <div class="discipline-item border-2 border-primary-300 rounded-lg p-4 bg-primary-50 relative">
                        <button type="button" class="btn-remove-discipline absolute top-2 right-2 text-red-500 hover:text-red-700 hidden z-10">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        
                        <!-- Header da Disciplina -->
                        <div class="mb-4 pr-8">
                            <h4 class="text-sm font-bold text-primary-900 mb-3 uppercase">Disciplina #1</h4>
                            <div class="grid md:grid-cols-4 gap-3">
                                <div class="md:col-span-2">
                                    <label class="block text-xs font-medium text-gray-700">Nome da Disciplina *</label>
                                    <input type="text" name="disciplines[0][subject]" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" placeholder="Ex: Matemática I" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Início *</label>
                                    <input type="time" name="disciplines[0][start_time]" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" required>
                                </div>
                                <div>
                                    <label class="block text-xs font-medium text-gray-700">Fim *</label>
                                    <input type="time" name="disciplines[0][end_time]" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" required>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Salas da Disciplina -->
                        <div class="bg-white border border-gray-200 rounded-lg p-3">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-semibold text-gray-700 uppercase">Salas desta Disciplina</label>
                                <button type="button" class="btn-add-room-to-discipline px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 flex items-center gap-1" data-discipline="0">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                    </svg>
                                    Sala
                                </button>
                            </div>
                            <div class="rooms-list space-y-2" data-discipline="0">
                                <!-- Sala inicial -->
                                <div class="room-row flex gap-2 items-start">
                                    <div class="flex-1">
                                        <input type="text" name="disciplines[0][rooms][0][room]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Nº Sala" required>
                                    </div>
                                    <div class="flex-1">
                                        <input type="number" name="disciplines[0][rooms][0][candidates_quota]" min="1" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Candidatos" required>
                                    </div>
                                    <button type="button" class="btn-remove-room text-red-500 hover:text-red-700 p-1 hidden">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <p class="mt-3 text-xs text-gray-500 flex items-start gap-2">
                    <svg class="inline w-4 h-4 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <span><strong>Exemplo:</strong> No Campus Central, no dia 15/11, podem realizar-se Matemática I (08:00-11:00), Física I (14:00-17:00), etc. Cada disciplina pode ter várias salas.</span>
                </p>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t">
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500">Criar Todos os Júris</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edição Rápida de Sala -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-quick-edit" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-md mx-4 rounded-lg shadow-lg p-6 focus:outline-none" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Edição Rápida de Sala</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <form method="POST" id="form-quick-edit" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" id="quick_jury_id" name="jury_id">
            
            <div>
                <label class="block text-sm font-medium text-gray-700" for="quick_room">Número da Sala *</label>
                <input type="text" id="quick_room" name="room" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700" for="quick_quota">Candidatos *</label>
                <input type="number" id="quick_quota" name="candidates_quota" min="1" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            
            <div class="flex justify-end gap-2 pt-4 border-t">
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-500">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Edição em Lote de Disciplina -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-batch-edit" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-4xl mx-4 rounded-lg shadow-lg p-6 focus:outline-none max-h-[90vh] overflow-y-auto" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Editar Disciplina em Lote</h2>
                <p class="text-sm text-gray-500 mt-1">Altere as informações da disciplina e suas salas</p>
            </div>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <form method="POST" id="form-batch-edit" class="space-y-6">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            
            <!-- Informações da Disciplina -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-blue-900 mb-3 uppercase">Informações da Disciplina</h3>
                <div class="grid md:grid-cols-3 gap-4">
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700" for="batch_subject">Disciplina *</label>
                        <input type="text" id="batch_subject" name="subject" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                        <p class="mt-1 text-xs text-gray-500">Esta alteração aplica-se a todas as salas</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="batch_date">Data *</label>
                        <input type="date" id="batch_date" name="exam_date" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="batch_start">Início *</label>
                        <input type="time" id="batch_start" name="start_time" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="batch_end">Fim *</label>
                        <input type="time" id="batch_end" name="end_time" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>
                    <div class="md:col-span-3">
                        <label class="block text-sm font-medium text-gray-700" for="batch_location">Local *</label>
                        <input type="text" id="batch_location" name="location" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>
                </div>
            </div>
            
            <!-- Salas -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                <h3 class="text-sm font-semibold text-gray-700 mb-3 uppercase">Salas</h3>
                <div id="batch-rooms-list" class="space-y-3">
                    <!-- Salas serão adicionadas dinamicamente -->
                </div>
            </div>
            
            <div class="flex justify-end gap-2 pt-4 border-t">
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-500">Atualizar Disciplina</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Partilhar por Email -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-share-email" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-lg mx-4 rounded-lg shadow-lg p-6 focus:outline-none" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Partilhar Lista por Email</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        <form method="POST" action="<?= url('/juries/share-email') ?>" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <p class="text-sm text-blue-800">
                        Será enviada uma lista completa dos júris organizados por data e local, incluindo vigilantes e supervisores alocados.
                    </p>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700" for="share_emails">Destinatários *</label>
                <textarea id="share_emails" name="emails" rows="3" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="Digite os emails separados por vírgula ou quebra de linha&#10;exemplo@email.com, outro@email.com" required></textarea>
                <p class="mt-1 text-xs text-gray-500">Separe múltiplos emails por vírgula ou quebra de linha</p>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700" for="share_subject">Assunto (opcional)</label>
                <input type="text" id="share_subject" name="subject" value="Lista de Júris de Exames" class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700" for="share_message">Mensagem adicional (opcional)</label>
                <textarea id="share_message" name="message" rows="3" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="Adicione uma mensagem personalizada..."></textarea>
            </div>
            
            <div class="flex justify-end gap-2 pt-4 border-t">
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-500">Enviar Email</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- Scripts -->
<script>
    // Definir CSRF token globalmente
    const CSRF_TOKEN = '<?= csrf_token() ?>';
    
    // Configurar Toastr (já carregado no layout)
    if (typeof toastr !== 'undefined') {
        toastr.options = {
            "closeButton": true,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "timeOut": "3000"
        };
    }
</script>

<!-- Script de drag-and-drop para a página de lista -->
<?php if (!$isVigilante): ?>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script src="<?= url('/js/jury-dnd.js') ?>"></script>
<?php endif; ?>

<!-- Script de modais -->
<script src="<?= url('/js/jury-modals.js') ?>"></script>
