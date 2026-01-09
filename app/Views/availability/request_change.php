<?php
$title = 'Solicitar Alteração de Disponibilidade';
$breadcrumbs = [
    ['label' => 'Disponibilidade', 'url' => url('/availability')],
    ['label' => 'Solicitar Alteração']
];
$statusText = $newStatus == 1 ? 'Disponível' : 'Indisponível';
$statusColor = $newStatus == 1 ? 'green' : 'red';
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="max-w-4xl">
        <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
            <div class="flex items-start gap-4 mb-6">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-<?= $statusColor ?>-100 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-<?= $statusColor ?>-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h1 class="text-2xl font-bold text-gray-800 mb-2">
                        Alterar Disponibilidade para <span
                            class="text-<?= $statusColor ?>-600"><?= $statusText ?></span>
                    </h1>
                    <p class="text-sm text-gray-600">
                        Você está alocado a júris de exame. Para alterar sua disponibilidade, é necessário fornecer uma
                        justificativa.
                    </p>
                </div>
            </div>

            <!-- Júris Alocados -->
            <div class="mb-6 bg-<?= $statusColor ?>-50 border border-<?= $statusColor ?>-200 rounded-lg p-4">
                <h3 class="font-semibold text-<?= $statusColor ?>-900 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    Você está alocado aos seguintes júris:
                </h3>
                <div class="space-y-2">
                    <?php foreach ($allocations as $allocation): ?>
                        <div class="bg-white border border-<?= $statusColor ?>-200 rounded p-3">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <p class="font-semibold text-gray-800"><?= htmlspecialchars($allocation['subject']) ?>
                                    </p>
                                    <div class="mt-1 flex flex-wrap items-center gap-3 text-sm text-gray-600">
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                            </svg>
                                            <?= htmlspecialchars(date('d/m/Y', strtotime($allocation['exam_date']))) ?>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            <?= htmlspecialchars(substr($allocation['start_time'], 0, 5)) ?> -
                                            <?= htmlspecialchars(substr($allocation['end_time'], 0, 5)) ?>
                                        </span>
                                        <span class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                            </svg>
                                            <?= htmlspecialchars($allocation['location']) ?> - Sala
                                            <?= htmlspecialchars($allocation['room']) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Formulário -->
            <form method="POST" action="<?= url('/availability/change/submit') ?>" enctype="multipart/form-data"
                class="space-y-6">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <input type="hidden" name="new_status" value="<?= $newStatus ?>">

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2" for="reason">
                        Justificativa <span class="text-red-600">*</span>
                    </label>
                    <textarea id="reason" name="reason" rows="6" required minlength="20"
                        class="w-full rounded border border-gray-300 px-3 py-2 focus:border-primary-500 focus:ring-1 focus:ring-primary-500"
                        placeholder="Descreva detalhadamente o motivo da alteração de disponibilidade (mínimo 20 caracteres)..."><?= htmlspecialchars(old('reason')) ?></textarea>
                    <p class="mt-1 text-xs text-gray-500">Mínimo: 20 caracteres</p>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2" for="attachment">
                        Anexar Documento Comprobatório <span class="text-gray-500">(Opcional)</span>
                    </label>
                    <div
                        class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-primary-400 transition-colors">
                        <input type="file" id="attachment" name="attachment" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            class="hidden" onchange="updateFileName(this)">
                        <label for="attachment" class="cursor-pointer">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                            </svg>
                            <p class="text-sm text-gray-600 mb-1"><span class="font-semibold text-primary-600">Clique
                                    para selecionar</span> ou arraste um arquivo</p>
                            <p class="text-xs text-gray-500">PDF, JPG, PNG, DOC ou DOCX (máximo 5MB)</p>
                        </label>
                        <p id="file-name" class="mt-3 text-sm font-medium text-gray-700 hidden"></p>
                    </div>
                </div>

                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-yellow-800">
                            <p class="font-semibold mb-1">Importante:</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>Sua solicitação será analisada pelo coordenador</li>
                                <li>A alteração só será efetivada após aprovação</li>
                                <?php if ($newStatus == 0): ?>
                                    <li>Se aprovado, você será desalocado dos júris acima</li>
                                    <li>A comissão precisará realocar outros vigilantes</li>
                                <?php endif; ?>
                                <li>Justificativas insuficientes podem ser rejeitadas</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t">
                    <a href="<?= url('/availability') ?>"
                        class="px-5 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors">
                        Voltar
                    </a>
                    <button type="submit"
                        class="px-5 py-2.5 bg-<?= $statusColor ?>-600 text-white font-semibold rounded-lg hover:bg-<?= $statusColor ?>-700 transition-colors shadow-sm">
                        Enviar Solicitação
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function updateFileName(input) {
        const fileNameDisplay = document.getElementById('file-name');
        if (input.files && input.files[0]) {
            const fileName = input.files[0].name;
            const fileSize = (input.files[0].size / 1024 / 1024).toFixed(2);
            fileNameDisplay.textContent = `📎 ${fileName} (${fileSize} MB)`;
            fileNameDisplay.classList.remove('hidden');
        } else {
            fileNameDisplay.classList.add('hidden');
        }
    }

    // Drag and drop
    const dropZone = document.querySelector('[for="attachment"]').parentElement;
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });

    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }

    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.add('border-primary-500', 'bg-primary-50');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, () => {
            dropZone.classList.remove('border-primary-500', 'bg-primary-50');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        document.getElementById('attachment').files = files;
        updateFileName(document.getElementById('attachment'));
    }, false);
</script>