<?php
$title = 'Planeamento de Júris por Vaga';
$breadcrumbs = [
    ['label' => 'Júris', 'url' => '/juries'],
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
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <h3 class="text-lg font-medium text-yellow-900 mb-2">Nenhuma vaga aberta</h3>
            <p class="text-yellow-700 mb-4">Crie uma vaga primeiro para poder planejar os júris</p>
            <a href="/vacancies" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded hover:bg-yellow-500">
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
                            <span class="px-2 py-1 text-xs font-semibold rounded-full <?= $vacancy['status'] === 'aberta' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
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
                                        <div class="text-2xl font-bold <?= $stats['occupancy_rate'] >= 80 ? 'text-green-600' : 'text-orange-600' ?>">
                                            <?= $stats['occupancy_rate'] ?>%
                                        </div>
                                        <div class="text-xs text-gray-500">Ocupação</div>
                                    </div>
                                </div>
                                
                                <div class="mt-3 flex gap-2 text-xs">
                                    <span class="flex items-center text-green-600">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                        <?= $stats['juries_complete'] ?> completos
                                    </span>
                                    <?php if ($stats['juries_incomplete'] > 0): ?>
                                    <span class="flex items-center text-orange-600">
                                        <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
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
                                <a href="/juries/vacancy/<?= $vacancy['id'] ?>/manage" 
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
                            
                            <a href="/vacancies/<?= $vacancy['id'] ?>" 
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

<!-- Modal: Criar Júris (Wizard Simples) -->
<div id="modal-create-juries" class="modal hidden fixed inset-0 bg-gray-900 bg-opacity-50 z-50 items-center justify-center">
    <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between p-6 border-b">
            <h2 class="text-xl font-bold text-gray-900">Criar Júris para Vaga</h2>
            <button type="button" class="modal-close text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <form id="form-create-juries" class="p-6 space-y-6">
            <input type="hidden" id="create_vacancy_id" name="vacancy_id">
            <input type="hidden" name="csrf" value="<?= \App\Utils\Csrf::token() ?>">
            
            <!-- Vaga Selecionada -->
            <div class="bg-blue-50 border border-blue-200 rounded p-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <div class="font-semibold text-blue-900" id="selected_vacancy_title"></div>
                        <div class="text-sm text-blue-700">Júris serão vinculados a esta vaga</div>
                    </div>
                </div>
            </div>

            <!-- Informações Básicas -->
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Local *</label>
                    <select name="location" id="select-location" class="w-full rounded border border-gray-300 px-3 py-2" required>
                        <option value="">Selecione o local...</option>
                        <?php foreach ($locations as $loc): ?>
                        <option value="<?= htmlspecialchars($loc['name']) ?>" data-location-id="<?= $loc['id'] ?>">
                            <?= htmlspecialchars($loc['name']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Data do Exame *</label>
                    <input type="date" name="exam_date" class="w-full rounded border border-gray-300 px-3 py-2" required>
                </div>
            </div>

            <!-- Disciplina -->
            <div class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📚 Disciplina e Horário</h3>
                
                <div class="grid md:grid-cols-3 gap-4 mb-6">
                    <div class="md:col-span-1">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Disciplina *</label>
                        <select name="subject" class="w-full rounded border border-gray-300 px-3 py-2" required>
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
                        <input type="time" name="start_time" id="start_time" class="w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Horário Fim *</label>
                        <input type="time" name="end_time" id="end_time" class="w-full rounded border border-gray-300 px-3 py-2" required>
                        <p id="time-validation-error" class="mt-1 text-xs text-red-600 hidden"></p>
                        <p id="time-validation-success" class="mt-1 text-xs text-green-600 hidden"></p>
                    </div>
                </div>
            </div>

            <!-- Salas -->
            <div class="border-t pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">🏫 Salas</h3>
                    <button type="button" id="btn-add-room" class="px-3 py-1 bg-blue-600 text-white text-sm rounded hover:bg-blue-500">
                        + Adicionar Sala
                    </button>
                </div>

                <div id="rooms-container" class="space-y-3">
                    <!-- Primeira sala -->
                    <div class="flex gap-3 items-start room-row">
                        <div class="flex-1">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Sala *</label>
                            <select name="rooms[0][room]" class="w-full rounded border border-gray-300 px-3 py-2 room-select" onchange="updateRoomCapacity(this)" required>
                                <option value="">Selecione a sala...</option>
                            </select>
                        </div>
                        <div class="w-32">
                            <label class="block text-xs font-medium text-gray-700 mb-1">Candidatos *</label>
                            <input type="number" name="rooms[0][candidates_quota]" min="1" class="w-full rounded border border-gray-300 px-3 py-2 room-capacity" required>
                        </div>
                        <div class="w-10 pt-7">
                            <!-- Primeira sala não tem botão remover -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Ações -->
            <div class="flex items-center justify-end gap-3 pt-4 border-t">
                <button type="button" class="modal-close px-4 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white rounded hover:bg-primary-500">
                    Criar Júris
                </button>
            </div>
        </form>
    </div>
</div>

<script>
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
document.getElementById('select-location').addEventListener('change', function() {
    selectedLocationId = this.options[this.selectedIndex]?.dataset?.locationId || null;
    updateAllRoomSelects();
});

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

function getRoomOptions(locationId) {
    let html = '<option value="">Selecione a sala...</option>';
    
    if (!locationId) {
        return '<option value="">Selecione um local primeiro</option>';
    }
    
    const filteredRooms = masterRooms.filter(room => room.location_id == locationId);
    
    if (filteredRooms.length === 0) {
        return '<option value="">Nenhuma sala cadastrada para este local</option>';
    }
    
    filteredRooms.forEach(room => {
        html += `<option value="${room.code}" data-capacity="${room.capacity}">${room.code} - ${room.name} (Cap: ${room.capacity})</option>`;
    });
    
    return html;
}

function updateAllRoomSelects() {
    const roomSelects = document.querySelectorAll('.room-select');
    roomSelects.forEach(select => {
        const currentValue = select.value;
        select.innerHTML = getRoomOptions(selectedLocationId);
        // Tentar manter o valor selecionado
        if (currentValue) {
            select.value = currentValue;
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
}

function addRoom() {
    if (!selectedLocationId) {
        alert('Por favor, selecione um local primeiro');
        return;
    }
    
    const roomsContainer = document.getElementById('rooms-container');
    const roomDiv = document.createElement('div');
    roomDiv.className = 'flex gap-3 items-start room-row';
    roomDiv.innerHTML = `
        <div class="flex-1">
            <label class="block text-xs font-medium text-gray-700 mb-1">Sala *</label>
            <select name="rooms[${roomCount}][room]" class="w-full rounded border border-gray-300 px-3 py-2 room-select" onchange="updateRoomCapacity(this)" required>
                ${getRoomOptions(selectedLocationId)}
            </select>
        </div>
        <div class="w-32">
            <label class="block text-xs font-medium text-gray-700 mb-1">Candidatos *</label>
            <input type="number" name="rooms[${roomCount}][candidates_quota]" min="1" class="w-full rounded border border-gray-300 px-3 py-2 room-capacity" required>
        </div>
        <div class="w-10 pt-7">
            <button type="button" onclick="this.closest('.room-row').remove()" class="text-red-600 hover:text-red-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    `;
    
    roomsContainer.appendChild(roomDiv);
    roomCount++;
}

document.getElementById('btn-add-room').addEventListener('click', addRoom);

document.getElementById('form-create-juries').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    
    // Validar campos básicos
    if (!formData.get('location') || !formData.get('exam_date') || !formData.get('subject') || 
        !formData.get('start_time') || !formData.get('end_time')) {
        alert('❌ Por favor, preencha todos os campos obrigatórios');
        return;
    }
    
    // Coletar salas
    const rooms = [];
    let roomIndex = 0;
    while (formData.has(`rooms[${roomIndex}][room]`)) {
        const room = formData.get(`rooms[${roomIndex}][room]`);
        const quota = formData.get(`rooms[${roomIndex}][candidates_quota]`);
        
        if (room && quota) {
            rooms.push({
                room: room,
                candidates_quota: parseInt(quota)
            });
        }
        roomIndex++;
    }
    
    if (rooms.length === 0) {
        alert('❌ Adicione pelo menos uma sala');
        return;
    }
    
    // Montar dados para envio (uma única disciplina)
    const data = {
        vacancy_id: parseInt(formData.get('vacancy_id')),
        location: formData.get('location'),
        exam_date: formData.get('exam_date'),
        csrf: formData.get('csrf'), // Token CSRF
        disciplines: [
            {
                subject: formData.get('subject'),
                start_time: formData.get('start_time'),
                end_time: formData.get('end_time'),
                rooms: rooms
            }
        ]
    };
    
    // Enviar para API
    try {
        const response = await fetch('/juries/create-for-vacancy', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify(data)
        });
        
        // Verificar se sessão expirou
        if (response.status === 401) {
            alert('⚠️ Sessão expirada. Redirecionando para login...');
            window.location.href = '/login';
            return;
        }
        
        const result = await response.json();
        
        if (result.success) {
            alert(`✅ ${result.message}\n\n${result.total} júri(s) criado(s) para ${formData.get('subject')}\n\nRedirecionando para gestão...`);
            window.location.href = `/juries/vacancy/${data.vacancy_id}/manage`;
        } else {
            // Verificar se tem redirect (outra forma de sessão expirada)
            if (result.redirect) {
                alert(`⚠️ ${result.message}`);
                window.location.href = result.redirect;
            } else {
                alert(`❌ Erro: ${result.message}`);
            }
        }
    } catch (error) {
        console.error('Erro ao criar júris:', error);
        alert('❌ Erro ao criar júris. Verifique o console para mais detalhes.');
    }
});

// Fechar modais
document.querySelectorAll('.modal-close').forEach(btn => {
    btn.addEventListener('click', function() {
        this.closest('.modal').classList.add('hidden');
        this.closest('.modal').classList.remove('flex');
    });
});
</script>
