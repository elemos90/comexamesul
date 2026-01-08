<?php
$title = 'Vagas de vigilancia';
$breadcrumbs = [
    ['label' => 'Vagas']
];
$isCoordinator = in_array($user['role'], ['coordenador', 'membro'], true);
$isVigilante = $user['role'] === 'vigilante';
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Gestao de vagas</h1>
            <p class="text-sm text-gray-500">Acompanhe as vigias publicadas e o respectivo estado.</p>
        </div>
        <?php if ($isCoordinator): ?>
            <?php if ($hasOpenVacancy ?? false): ?>
                <button type="button" disabled
                    class="px-4 py-2 bg-gray-300 text-gray-500 text-sm font-medium rounded cursor-not-allowed"
                    title="Já existe uma vaga aberta. Feche-a antes de criar nova.">
                    Nova vaga
                </button>
            <?php else: ?>
                <button type="button" data-modal-target="modal-create-vacancy"
                    class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Nova
                    vaga</button>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if ($hasOpenVacancy ?? false): ?>
        <div class="bg-amber-50 border-l-4 border-amber-500 text-amber-800 text-sm rounded-lg px-4 py-3 shadow-sm">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <p class="font-semibold mb-1">⚠️ Vaga Atualmente Aberta</p>
                    <p class="mb-2">
                        A vaga <strong>"<?= htmlspecialchars($openVacancy['title']) ?>"</strong> está aberta para
                        candidaturas até
                        <strong><?= date('d/m/Y \à\s H:i', strtotime($openVacancy['deadline_at'])) ?></strong>.
                    </p>
                    <?php if ($isCoordinator): ?>
                        <p class="text-xs text-amber-700">
                            Apenas UMA vaga pode estar aberta por vez. Feche ou encerre esta vaga antes de criar uma nova.
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isVigilante): ?>
        <div class="bg-blue-50 border border-blue-100 text-blue-800 text-sm rounded-lg px-4 py-3">
            Mantenha a sua <a href="<?= url('/availability') ?>" class="underline font-medium">disponibilidade</a>
            atualizada para que a comissao possa alocar vigilantes nas vigias.
        </div>
    <?php endif; ?>

    <div class="bg-white border border-gray-100 rounded-lg shadow-sm overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Titulo
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Limite
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <?= $isVigilante ? 'Detalhes' : 'Accoes' ?>
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-100">
                <?php foreach ($vacancies as $vacancy): ?>
                    <?php $deadline = date('d/m/Y H:i', strtotime($vacancy['deadline_at'])); ?>
                    <tr>
                        <td class="px-4 py-3 text-sm text-gray-700 font-medium">
                            <a href="<?= url('/vacancies/' . $vacancy['id']) ?>"
                                class="hover:text-primary-600"><?= htmlspecialchars($vacancy['title']) ?></a>
                            <p class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($vacancy['description']) ?></p>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600"><?= $deadline ?></td>
                        <td class="px-4 py-3 text-sm">
                            <?php
                            $statusColors = [
                                'aberta' => 'bg-green-100 text-green-700 border border-green-300',
                                'fechada' => 'bg-yellow-100 text-yellow-700 border border-yellow-300',
                                'encerrada' => 'bg-purple-100 text-purple-700 border border-purple-300'
                            ];
                            $colorClass = $statusColors[$vacancy['status']] ?? 'bg-gray-100 text-gray-600';
                            ?>
                            <span
                                class="px-2 py-1 rounded-full text-xs font-medium <?= $colorClass ?>"><?= htmlspecialchars(ucfirst($vacancy['status'])) ?></span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <?php if ($isCoordinator): ?>
                                    <button type="button"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors"
                                        data-action="open-edit-vacancy" data-vacancy='<?= json_encode([
                                            'id' => $vacancy['id'],
                                            'title' => $vacancy['title'],
                                            'description' => $vacancy['description'],
                                            'deadline_at' => date('Y-m-d\TH:i', strtotime($vacancy['deadline_at'])),
                                            'status' => $vacancy['status'],
                                        ]) ?>' title="Editar dados da vaga">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Editar
                                    </button>
                                    <?php if ($vacancy['status'] === 'aberta'): ?>
                                        <form method="POST" action="<?= url('/vacancies/' . $vacancy['id'] . '/close') ?>"
                                            class="inline">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-yellow-100 text-yellow-700 rounded hover:bg-yellow-200 transition-colors"
                                                title="Fechar vaga para novas candidaturas">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                </svg>
                                                Fechar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($vacancy['status'] === 'fechada'): ?>
                                        <form method="POST" action="<?= url('/vacancies/' . $vacancy['id'] . '/finalize') ?>"
                                            class="inline"
                                            onsubmit="event.preventDefault(); confirmFinalize(this, '<?= htmlspecialchars($vacancy['title'], ENT_QUOTES) ?>');">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-purple-100 text-purple-700 rounded hover:bg-purple-200 transition-colors"
                                                title="Encerrar e arquivar vaga permanentemente">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                                </svg>
                                                Encerrar
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if ($user['role'] === 'coordenador' && $vacancy['status'] !== 'encerrada'): ?>
                                        <form method="POST" action="<?= url('/vacancies/' . $vacancy['id'] . '/delete') ?>"
                                            class="inline"
                                            onsubmit="event.preventDefault(); confirmDelete(this, '<?= htmlspecialchars($vacancy['title'], ENT_QUOTES) ?>');">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors"
                                                title="Remover vaga permanentemente">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Remover
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <a href="<?= url('/vacancies/' . $vacancy['id']) ?>"
                                        class="px-3 py-1.5 text-xs font-medium bg-primary-100 text-primary-700 rounded hover:bg-primary-200 inline-flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                        Ver detalhes
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if (!$vacancies): ?>
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-sm text-gray-500">Sem vagas registadas.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if ($isCoordinator): ?>
    <div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-create-vacancy" role="dialog"
        aria-modal="true" aria-hidden="true">
        <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
        <div class="modal-content relative bg-white w-full max-w-lg mx-4 rounded-lg shadow-lg p-6 focus:outline-none"
            tabindex="-1">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Nova vaga</h2>
                <button type="button" class="modal-close text-gray-500 hover:text-gray-700">&times;</button>
            </div>
            <form method="POST" action="<?= url('/vacancies') ?>" class="space-y-4">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="vacancy_title">Titulo</label>
                    <input type="text" id="vacancy_title" name="title" value="<?= htmlspecialchars(old('title')) ?>"
                        class="mt-1 w-full rounded border <?= validation_errors('title') ? 'border-red-500' : 'border-gray-300' ?> px-3 py-2"
                        required>
                    <?php if ($errors = validation_errors('title')): ?>
                        <?php foreach ($errors as $error): ?>
                            <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="vacancy_description">Descricao</label>
                    <textarea id="vacancy_description" name="description" rows="3"
                        class="mt-1 w-full rounded border <?= validation_errors('description') ? 'border-red-500' : 'border-gray-300' ?> px-3 py-2"
                        required><?= htmlspecialchars(old('description')) ?></textarea>
                    <?php if ($errors = validation_errors('description')): ?>
                        <?php foreach ($errors as $error): ?>
                            <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="vacancy_deadline">Limite de
                        candidatura</label>
                    <input type="datetime-local" id="vacancy_deadline" name="deadline_at"
                        value="<?= htmlspecialchars(old('deadline_at')) ?>"
                        class="mt-1 w-full rounded border <?= validation_errors('deadline_at') ? 'border-red-500' : 'border-gray-300' ?> px-3 py-2"
                        required>
                    <?php if ($errors = validation_errors('deadline_at')): ?>
                        <?php foreach ($errors as $error): ?>
                            <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($error) ?></p>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600">Cancelar</button>
                    <button type="submit"
                        class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded">Publicar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-edit-vacancy" role="dialog"
        aria-modal="true" aria-hidden="true">
        <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
        <div class="modal-content relative bg-white w-full max-w-lg mx-4 rounded-lg shadow-lg p-6 focus:outline-none"
            tabindex="-1">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-gray-800">Editar vaga</h2>
                <button type="button" class="modal-close text-gray-500 hover:text-gray-700">&times;</button>
            </div>
            <form method="POST" data-form="edit-vacancy" class="space-y-4">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="edit_vacancy_title">Titulo</label>
                    <input type="text" id="edit_vacancy_title" name="title"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="edit_vacancy_description">Descricao</label>
                    <textarea id="edit_vacancy_description" name="description" rows="3"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="edit_vacancy_deadline">Limite de
                        candidatura</label>
                    <input type="datetime-local" id="edit_vacancy_deadline" name="deadline_at"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="edit_vacancy_status">Estado</label>
                    <select id="edit_vacancy_status" name="status"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                        <?php foreach (['aberta', 'fechada', 'encerrada'] as $status): ?>
                            <option value="<?= $status ?>"><?= $status ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600">Cancelar</button>
                    <button type="submit"
                        class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded">Atualizar</button>
                </div>
            </form>
        </div>
    </div>

    <?php if (validation_errors()): ?>
        <script>
            // Reabrir modal automaticamente se houver erros de validação
            document.addEventListener('DOMContentLoaded', function () {
                var modal = document.getElementById('modal-create-vacancy');
                if (modal) {
                    modal.classList.remove('hidden');
                    modal.classList.add('flex', 'open');
                    modal.setAttribute('aria-hidden', 'false');
                    var firstInput = modal.querySelector('input[type="text"]');
                    if (firstInput) { firstInput.focus(); }
                }
            });
        </script>
    <?php endif; ?>
<?php endif; ?>

<!-- Modal: Confirmação de Ação -->
<div id="modal-confirm-action"
    class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-[60] items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4 transform transition-all">
        <div class="p-6">
            <div id="confirm-icon-container"
                class="flex items-center justify-center w-12 h-12 mx-auto rounded-full mb-4">
                <svg id="confirm-icon" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h3 id="confirm-title" class="text-lg font-semibold text-gray-900 text-center mb-2">Confirmar Ação</h3>
            <p id="confirm-message" class="text-sm text-gray-600 text-center mb-6">Tem certeza que deseja realizar esta
                ação?</p>
            <div class="flex gap-3">
                <button type="button" id="btn-confirm-cancel"
                    class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                    Cancelar
                </button>
                <button type="button" id="btn-confirm-ok"
                    class="flex-1 px-4 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2">
                    Confirmar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // Função para mostrar modal de confirmação
    function showConfirmModal(title, message, formElement, type = 'danger') {
        const modal = document.getElementById('modal-confirm-action');
        const titleEl = document.getElementById('confirm-title');
        const messageEl = document.getElementById('confirm-message');
        const iconContainer = document.getElementById('confirm-icon-container');
        const btnOk = document.getElementById('btn-confirm-ok');
        const btnCancel = document.getElementById('btn-confirm-cancel');

        titleEl.textContent = title;
        messageEl.innerHTML = message;

        // Estilizar baseado no tipo
        if (type === 'danger') {
            iconContainer.className = 'flex items-center justify-center w-12 h-12 mx-auto rounded-full mb-4 bg-red-100';
            iconContainer.querySelector('svg').className = 'w-6 h-6 text-red-600';
            btnOk.className = 'flex-1 px-4 py-2.5 bg-red-600 text-white font-medium rounded-lg hover:bg-red-700 transition-colors flex items-center justify-center gap-2';
        } else {
            iconContainer.className = 'flex items-center justify-center w-12 h-12 mx-auto rounded-full mb-4 bg-purple-100';
            iconContainer.querySelector('svg').className = 'w-6 h-6 text-purple-600';
            btnOk.className = 'flex-1 px-4 py-2.5 bg-purple-600 text-white font-medium rounded-lg hover:bg-purple-700 transition-colors flex items-center justify-center gap-2';
        }

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Remover listeners antigos clonando os botões
        const newBtnOk = btnOk.cloneNode(true);
        const newBtnCancel = btnCancel.cloneNode(true);
        btnOk.parentNode.replaceChild(newBtnOk, btnOk);
        btnCancel.parentNode.replaceChild(newBtnCancel, btnCancel);

        // Listener para confirmar - submeter o form
        newBtnOk.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            formElement.submit();
        });

        // Listener para cancelar
        newBtnCancel.addEventListener('click', () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        });

        // Fechar ao clicar fora
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        });
    }

    // Função para encerrar vaga
    function confirmFinalize(formElement, vacancyTitle) {
        showConfirmModal(
            '📦 Encerrar Vaga',
            `<strong>${vacancyTitle}</strong><br><br>
            <span class="text-purple-600">⚠️ Esta ação irá:</span>
            <ul class="text-left mt-2 space-y-1 text-sm">
                <li>• Marcar a vaga como <strong>concluída</strong></li>
                <li>• Bloquear futuras alterações</li>
                <li>• Arquivar permanentemente</li>
            </ul>`,
            formElement,
            'archive'
        );
    }

    // Função para remover vaga
    function confirmDelete(formElement, vacancyTitle) {
        showConfirmModal(
            '🗑️ Remover Vaga',
            `<strong>${vacancyTitle}</strong><br><br>
            <span class="text-red-600">⚠️ Esta ação irá:</span>
            <ul class="text-left mt-2 space-y-1 text-sm">
                <li>• Remover a vaga <strong>permanentemente</strong></li>
                <li>• Apagar todo o histórico</li>
                <li>• Esta ação <strong>NÃO PODE</strong> ser desfeita!</li>
            </ul>`,
            formElement,
            'danger'
        );
    }
</script>