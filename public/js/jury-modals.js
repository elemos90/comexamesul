/**
 * Sistema de Modais para Júris
 * Gerencia abertura/fechamento de modais e formulários
 */

document.addEventListener('DOMContentLoaded', function () {
    initModals();
    initFormHandlers();
    console.log('✅ Sistema de modais inicializado');
});

/**
 * Inicializar modais (abrir/fechar)
 */
function initModals() {
    // Abrir modais via atributo data-modal-target
    document.querySelectorAll('[data-modal-target]').forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const modalId = this.getAttribute('data-modal-target');
            openModal(modalId);
        });
    });

    // Fechar modais via botão .modal-close
    document.querySelectorAll('.modal-close').forEach(btn => {
        btn.addEventListener('click', function () {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });

    // Fechar modal ao clicar no backdrop
    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', function () {
            const modal = this.closest('.modal');
            if (modal) {
                closeModal(modal.id);
            }
        });
    });

    // Fechar modal com tecla ESC
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            const openModal = document.querySelector('.modal:not(.hidden)');
            if (openModal) {
                closeModal(openModal.id);
            }
        }
    });
}

/**
 * Abrir modal
 */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Fechar modal
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        document.body.style.overflow = '';

        // Limpar formulário se houver
        const form = modal.querySelector('form');
        if (form && !form.hasAttribute('data-no-reset')) {
            form.reset();
        }
    }
}

/**
 * Inicializar handlers de formulários
 */
function initFormHandlers() {
    // Handler de edição rápida (inline)
    document.querySelectorAll('.btn-edit-inline').forEach(btn => {
        btn.addEventListener('click', function () {
            const juryId = this.getAttribute('data-jury-id');
            openQuickEditModal(juryId);
        });
    });

    // Handler de edição completa
    document.querySelectorAll('[data-action="open-edit-jury"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const juryData = JSON.parse(this.getAttribute('data-jury'));
            openEditJuryModal(juryData);
        });
    });

    // Handler de edição em lote de disciplina
    document.querySelectorAll('[data-action="edit-discipline-batch"]').forEach(btn => {
        btn.addEventListener('click', function () {
            const groupData = JSON.parse(this.getAttribute('data-group'));
            openBatchEditModal(groupData);
        });
    });

    // Submit de formulário de edição rápida
    const quickEditForm = document.getElementById('form-quick-edit');
    if (quickEditForm) {
        quickEditForm.addEventListener('submit', handleQuickEdit);
    }

    // Submit de formulário de edição em lote
    const batchEditForm = document.getElementById('form-batch-edit');
    if (batchEditForm) {
        batchEditForm.addEventListener('submit', handleBatchEdit);
    }
}

/**
 * Abrir modal de edição rápida
 */
function openQuickEditModal(juryId) {
    // Buscar dados do júri via API (ou DOM)
    const juryCard = document.querySelector(`[data-jury="${juryId}"]`)?.closest('.border');
    if (!juryCard) return;

    const room = juryCard.querySelector('.font-semibold')?.textContent?.replace('Sala ', '') || '';
    const quota = juryCard.querySelector('.text-xs')?.textContent?.match(/\d+/)?.[0] || '';

    document.getElementById('quick_jury_id').value = juryId;
    document.getElementById('quick_room').value = room;
    document.getElementById('quick_quota').value = quota;

    openModal('modal-quick-edit');
}

/**
 * Abrir modal de edição completa
 */
function openEditJuryModal(juryData) {
    document.getElementById('edit_jury_subject').value = juryData.subject || '';
    document.getElementById('edit_jury_exam_date').value = juryData.exam_date || '';
    document.getElementById('edit_jury_start').value = juryData.start_time || '';
    document.getElementById('edit_jury_end').value = juryData.end_time || '';
    document.getElementById('edit_jury_location').value = juryData.location || '';
    document.getElementById('edit_jury_room').value = juryData.room || '';
    document.getElementById('edit_jury_quota').value = juryData.candidates_quota || '';

    const form = document.querySelector('[data-form="edit-jury"]');
    if (form) {
        form.action = appUrl(`/juries/${juryData.id}/update`);
    }

    openModal('modal-edit-jury');
}

/**
 * Abrir modal de edição em lote
 */
function openBatchEditModal(groupData) {
    document.getElementById('batch_subject').value = groupData.subject || '';
    document.getElementById('batch_date').value = groupData.exam_date || '';
    document.getElementById('batch_start').value = groupData.start_time || '';
    document.getElementById('batch_end').value = groupData.end_time || '';
    document.getElementById('batch_location').value = groupData.location || '';

    // Preencher salas
    const roomsList = document.getElementById('batch-rooms-list');
    if (roomsList && groupData.juries) {
        roomsList.innerHTML = '';
        groupData.juries.forEach((jury, index) => {
            const roomDiv = document.createElement('div');
            roomDiv.className = 'flex gap-3 items-start p-3 bg-white border border-gray-200 rounded';
            roomDiv.innerHTML = `
                <div class="flex-1 grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Sala *</label>
                        <input type="text" name="juries[${index}][room]" value="${jury.room}" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" required>
                        <input type="hidden" name="juries[${index}][id]" value="${jury.id}">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Candidatos *</label>
                        <input type="number" name="juries[${index}][candidates_quota]" value="${jury.candidates_quota}" min="1" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" required>
                    </div>
                </div>
            `;
            roomsList.appendChild(roomDiv);
        });
    }

    const form = document.getElementById('form-batch-edit');
    if (form) {
        form.action = appUrl('/juries/update-batch');
    }

    openModal('modal-batch-edit');
}

/**
 * Handler de submit de edição rápida
 */
async function handleQuickEdit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const juryId = formData.get('jury_id');

    try {
        const response = await fetch(appUrl(`/juries/${juryId}/update-quick`), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                room: formData.get('room'),
                candidates_quota: formData.get('candidates_quota'),
                csrf: CSRF_TOKEN
            })
        });

        const result = await response.json();

        if (response.ok) {
            toastr.success(result.message || 'Júri atualizado');
            closeModal('modal-quick-edit');
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error(result.message || 'Erro ao atualizar');
        }
    } catch (error) {
        console.error('Erro:', error);
        toastr.error('Erro ao atualizar júri');
    }
}

/**
 * Handler de submit de edição em lote
 */
async function handleBatchEdit(e) {
    e.preventDefault();

    const formData = new FormData(e.target);
    const data = {};

    // Converter FormData para objeto
    for (let [key, value] of formData.entries()) {
        if (key.startsWith('juries[')) {
            // Processar arrays de júris
            if (!data.juries) data.juries = [];
            const match = key.match(/juries\[(\d+)\]\[(\w+)\]/);
            if (match) {
                const index = parseInt(match[1]);
                const field = match[2];
                if (!data.juries[index]) data.juries[index] = {};
                data.juries[index][field] = value;
            }
        } else {
            data[key] = value;
        }
    }

    try {
        const response = await fetch(appUrl('/juries/update-batch'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (response.ok) {
            toastr.success(result.message || 'Disciplina atualizada');
            closeModal('modal-batch-edit');
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error(result.message || 'Erro ao atualizar');
        }
    } catch (error) {
        console.error('Erro:', error);
        toastr.error('Erro ao atualizar disciplina');
    }
}

// Expor funções globalmente
window.openModal = openModal;
window.closeModal = closeModal;
window.openQuickEditModal = openQuickEditModal;
window.openEditJuryModal = openEditJuryModal;
window.openBatchEditModal = openBatchEditModal;
