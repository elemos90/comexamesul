<!-- Modal: Criar Júri Individual -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-create-jury" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-2xl mx-4 rounded-lg shadow-lg p-6 focus:outline-none max-h-[90vh] overflow-y-auto" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Novo júri</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
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
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500">Criar Júri</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Criar Júris por Local (Lote) -->
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

<script>
// JavaScript para adicionar/remover disciplinas e salas dinamicamente
document.addEventListener('DOMContentLoaded', function() {
    let disciplineCount = 1;
    
    // Adicionar disciplina
    document.getElementById('btn-add-discipline')?.addEventListener('click', function() {
        const container = document.getElementById('disciplines-container');
        const newDiscipline = createDisciplineElement(disciplineCount);
        container.appendChild(newDiscipline);
        disciplineCount++;
        updateRemoveButtons();
    });
    
    // Delegação de eventos para botões dinâmicos
    document.getElementById('disciplines-container')?.addEventListener('click', function(e) {
        // Remover disciplina
        if (e.target.closest('.btn-remove-discipline')) {
            e.target.closest('.discipline-item').remove();
            updateRemoveButtons();
        }
        
        // Adicionar sala
        if (e.target.closest('.btn-add-room-to-discipline')) {
            const btn = e.target.closest('.btn-add-room-to-discipline');
            const disciplineIndex = btn.dataset.discipline;
            const roomsList = document.querySelector(`.rooms-list[data-discipline="${disciplineIndex}"]`);
            const roomCount = roomsList.querySelectorAll('.room-row').length;
            const newRoom = createRoomElement(disciplineIndex, roomCount);
            roomsList.appendChild(newRoom);
            updateRoomRemoveButtons(roomsList);
        }
        
        // Remover sala
        if (e.target.closest('.btn-remove-room')) {
            const roomRow = e.target.closest('.room-row');
            const roomsList = roomRow.closest('.rooms-list');
            roomRow.remove();
            updateRoomRemoveButtons(roomsList);
        }
    });
    
    function createDisciplineElement(index) {
        const div = document.createElement('div');
        div.className = 'discipline-item border-2 border-primary-300 rounded-lg p-4 bg-primary-50 relative';
        div.innerHTML = `
            <button type="button" class="btn-remove-discipline absolute top-2 right-2 text-red-500 hover:text-red-700 z-10">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
            
            <div class="mb-4 pr-8">
                <h4 class="text-sm font-bold text-primary-900 mb-3 uppercase">Disciplina #${index + 1}</h4>
                <div class="grid md:grid-cols-4 gap-3">
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-700">Nome da Disciplina *</label>
                        <input type="text" name="disciplines[${index}][subject]" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" placeholder="Ex: Matemática I" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Início *</label>
                        <input type="time" name="disciplines[${index}][start_time]" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700">Fim *</label>
                        <input type="time" name="disciplines[${index}][end_time]" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" required>
                    </div>
                </div>
            </div>
            
            <div class="bg-white border border-gray-200 rounded-lg p-3">
                <div class="flex items-center justify-between mb-2">
                    <label class="block text-xs font-semibold text-gray-700 uppercase">Salas desta Disciplina</label>
                    <button type="button" class="btn-add-room-to-discipline px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 flex items-center gap-1" data-discipline="${index}">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Sala
                    </button>
                </div>
                <div class="rooms-list space-y-2" data-discipline="${index}">
                    ${createRoomElement(index, 0).outerHTML}
                </div>
            </div>
        `;
        return div;
    }
    
    function createRoomElement(disciplineIndex, roomIndex) {
        const div = document.createElement('div');
        div.className = 'room-row flex gap-2 items-start';
        div.innerHTML = `
            <div class="flex-1">
                <input type="text" name="disciplines[${disciplineIndex}][rooms][${roomIndex}][room]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Nº Sala" required>
            </div>
            <div class="flex-1">
                <input type="number" name="disciplines[${disciplineIndex}][rooms][${roomIndex}][candidates_quota]" min="1" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Candidatos" required>
            </div>
            <button type="button" class="btn-remove-room text-red-500 hover:text-red-700 p-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        `;
        return div;
    }
    
    function updateRemoveButtons() {
        const disciplines = document.querySelectorAll('.discipline-item');
        disciplines.forEach((d, i) => {
            const btn = d.querySelector('.btn-remove-discipline');
            if (disciplines.length > 1) {
                btn.classList.remove('hidden');
            } else {
                btn.classList.add('hidden');
            }
        });
    }
    
    function updateRoomRemoveButtons(roomsList) {
        const rooms = roomsList.querySelectorAll('.room-row');
        rooms.forEach((r, i) => {
            const btn = r.querySelector('.btn-remove-room');
            if (rooms.length > 1) {
                btn.classList.remove('hidden');
            } else {
                btn.classList.add('hidden');
            }
        });
    }
});
</script>
