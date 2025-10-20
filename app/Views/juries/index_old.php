<?php
$title = 'Júris de exames';
$breadcrumbs = [
    ['label' => 'Júris']
];
$canManage = in_array($user['role'], ['coordenador', 'membro'], true);
$isVigilante = $user['role'] === 'vigilante';
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Planeamento de júris</h1>
            <p class="text-sm text-gray-500">Distribua vigilantes e supervisores garantindo ausência de conflitos.</p>
        </div>
        <?php if ($canManage): ?>
            <button type="button" data-modal-target="modal-create-jury" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500">Novo júri</button>
        <?php endif; ?>
    </div>

    <?php if ($isVigilante): ?>
        <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Disciplina</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Data</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Horário</th>
                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Supervisor</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-100">
                    <?php foreach ($juries as $jury): ?>
                        <tr>
                            <td class="px-4 py-3 text-sm font-medium text-gray-700"><?= htmlspecialchars($jury['subject']) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars(date('d/m/Y', strtotime($jury['exam_date']))) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars(substr($jury['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr($jury['end_time'], 0, 5)) ?></td>
                            <td class="px-4 py-3 text-sm text-gray-600"><?= htmlspecialchars($jury['supervisor_name'] ?? '—') ?></td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$juries): ?>
                        <tr>
                            <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Ainda não foi alocado a júris.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="grid lg:grid-cols-4 gap-6">
            <div class="lg:col-span-1 bg-white border border-gray-100 rounded-lg shadow-sm p-4">
                <h2 class="text-sm font-semibold text-gray-700 uppercase">Vigilantes aprovados</h2>
                <p class="text-xs text-gray-500 mt-1">Arraste para o júri desejado.</p>
                <ul id="available-vigilantes" class="mt-4 space-y-2" data-pool="true">
                    <?php foreach ($vigilantes as $vigilante): ?>
                        <li class="draggable-item" data-id="<?= $vigilante['id'] ?>" tabindex="0">
                            <div>
                                <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($vigilante['name']) ?></p>
                                <p class="text-xs text-gray-500"><?= htmlspecialchars($vigilante['email']) ?></p>
                            </div>
                        </li>
                    <?php endforeach; ?>
                    <?php if (!$vigilantes): ?>
                        <li class="text-xs text-gray-500">Sem vigilantes aprovados.</li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="lg:col-span-3 space-y-6">
                <?php foreach ($juries as $jury): ?>
                    <div class="bg-white border border-gray-100 rounded-lg shadow-sm">
                        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-800"><?= htmlspecialchars($jury['subject']) ?></h3>
                                <p class="text-sm text-gray-500"><?= htmlspecialchars(date('d/m/Y', strtotime($jury['exam_date']))) ?> · <?= htmlspecialchars(substr($jury['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr($jury['end_time'], 0, 5)) ?> · <?= htmlspecialchars($jury['location']) ?></p>
                            </div>
                            <div class="flex items-center gap-2 text-xs text-gray-500">
                                <button type="button" class="px-3 py-1.5 bg-gray-100 rounded" data-action="open-edit-jury" data-jury='<?= json_encode([
                                    'id' => $jury['id'],
                                    'subject' => $jury['subject'],
                                    'exam_date' => $jury['exam_date'],
                                    'start_time' => substr($jury['start_time'], 0, 5),
                                    'end_time' => substr($jury['end_time'], 0, 5),
                                    'location' => $jury['location'],
                                    'room' => $jury['room'],
                                    'candidates_quota' => $jury['candidates_quota'],
                                    'notes' => $jury['notes'],
                                ]) ?>'>Editar</button>
                                <form method="POST" action="/juries/<?= $jury['id'] ?>/delete" onsubmit="return confirm('Eliminar este júri?');">
                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <button type="submit" class="px-3 py-1.5 bg-red-100 text-red-600 rounded">Eliminar</button>
                                </form>
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4 p-5">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 uppercase mb-2">Vigilantes</h4>
                                <ul class="dropzone" data-jury="<?= $jury['id'] ?>" data-assign-url="/juries/<?= $jury['id'] ?>/assign" data-unassign-url="/juries/<?= $jury['id'] ?>/unassign">
                                    <?php foreach ($jury['vigilantes'] as $member): ?>
                                        <li class="draggable-item" data-id="<?= $member['id'] ?>" tabindex="0">
                                            <div>
                                                <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($member['name']) ?></p>
                                                <p class="text-xs text-gray-500"><?= htmlspecialchars($member['email']) ?></p>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                    <?php if (!$jury['vigilantes']): ?>
                                        <li class="text-xs text-gray-400 italic">Arraste vigilantes aprovados para aqui.</li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 uppercase mb-2">Supervisor</h4>
                                <div class="dropzone-single" data-jury="<?= $jury['id'] ?>" data-set-url="/juries/<?= $jury['id'] ?>/set-supervisor">
                                    <?php if (!empty($jury['supervisor_name'])): ?>
                                        <div class="draggable-item" data-id="<?= $jury['supervisor_id'] ?>" tabindex="0">
                                            <div>
                                                <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($jury['supervisor_name']) ?></p>
                                                <p class="text-xs text-gray-500">Supervisor designado</p>
                                            </div>
                                        </div>
                                    <?php else: ?>
                                        <p class="text-xs text-gray-400 italic">Arraste um supervisor elegível.</p>
                                    <?php endif; ?>
                                </div>
                                <div class="mt-3 border border-dashed border-gray-200 rounded p-3 bg-gray-50">
                                    <p class="text-xs text-gray-500 uppercase font-semibold">Supervisores</p>
                                    <ul class="mt-2 space-y-2" class="supervisor-pool">
                                        <?php foreach ($supervisors as $supervisor): ?>
                                            <li class="draggable-item" data-id="<?= $supervisor['id'] ?>" tabindex="0">
                                                <div>
                                                    <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($supervisor['name']) ?></p>
                                                    <p class="text-xs text-gray-500"><?= htmlspecialchars($supervisor['email']) ?></p>
                                                </div>
                                            </li>
                                        <?php endforeach; ?>
                                        <?php if (!$supervisors): ?>
                                            <li class="text-xs text-gray-400">Sem supervisores elegíveis.</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        <?php if ($jury['has_report']): ?>
                            <div class="px-5 pb-4 text-xs text-green-600">Relatório submetido.</div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
                <?php if (!$juries): ?>
                    <p class="text-sm text-gray-500">Nenhum júri registado.</p>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if ($canManage): ?>
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-create-jury" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-2xl mx-4 rounded-lg shadow-lg p-6 focus:outline-none" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Novo júri</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form method="POST" action="/juries" class="grid md:grid-cols-2 gap-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="jury_subject">Disciplina</label>
                <input type="text" id="jury_subject" name="subject" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
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
                <input type="number" id="jury_quota" name="candidates_quota" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="jury_notes">Observações</label>
                <textarea id="jury_notes" name="notes" rows="3" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></textarea>
            </div>
            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded">Guardar</button>
            </div>
        </form>
    </div>
</div>

<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-edit-jury" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-2xl mx-4 rounded-lg shadow-lg p-6 focus:outline-none" tabindex="-1">
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
                <input type="number" id="edit_jury_quota" name="candidates_quota" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="edit_jury_notes">Observações</label>
                <textarea id="edit_jury_notes" name="notes" rows="3" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></textarea>
            </div>
            <div class="md:col-span-2 flex justify-end gap-2">
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded">Atualizar</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>



