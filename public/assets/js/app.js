(function () {
    var csrfMeta = document.querySelector("meta[name='csrf-token']");
    var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';
    
    // Flags para prevenir múltiplos event listeners
    var listenersInitialized = {
        vacancy: false,
        jury: false,
        quick: false,
        batch: false,
        template: false,
        modals: false
    };

    document.addEventListener('DOMContentLoaded', function () {
        initToasts();
        initModals();
        initVacancyEditor();
        initJuryEditor();
        initDragAndDrop();
        initDisciplineRooms();
        initTemplates();
        initQuickEdit();
        initBatchEdit();
    });

    function initToasts() {
        var container = document.getElementById('toast-container');
        if (!container) { return; }

        var items = container.querySelectorAll('.toast-item');
        if (!items.length) { return; }

        items.forEach(function (item, index) {
            var type = item.dataset.type || 'info';
            var message = item.dataset.message || '';
            if (!message) { return; }

            var autoCloseDelay = 5000 + (index * 200);
            var closeButton = item.querySelector('[data-dismiss="toast"]');

            var removeToast = function () {
                if (!item.parentElement) { return; }
                item.style.transition = 'all 0.3s ease';
                item.style.opacity = '0';
                item.style.transform = 'translateX(100%)';
                setTimeout(function () {
                    if (item.parentElement) {
                        item.remove();
                    }
                    if (container && !container.querySelector('.toast-item')) {
                        container.remove();
                    }
                }, 300);
            };

            var timerId = setTimeout(removeToast, autoCloseDelay);

            if (closeButton) {
                closeButton.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    clearTimeout(timerId);
                    removeToast();
                });
            }
        });
    }

    function initModals() {
        if (listenersInitialized.modals) { 
            console.log('initModals já inicializado, pulando...');
            return; 
        }
        listenersInitialized.modals = true;
        console.log('Inicializando sistema de modais...');
        
        // Usar delegação de eventos para botões de abertura de modais
        document.addEventListener('click', function(event) {
            var button = event.target.closest('[data-modal-target]');
            if (button) {
                event.preventDefault();
                event.stopPropagation();
                var modalId = button.getAttribute('data-modal-target');
                console.log('Botão de abrir modal clicado:', modalId);
                openModal(modalId);
            }
        });
        
        // Botões de fechar modais
        document.body.addEventListener('click', function (event) {
            var closeBtn = event.target.closest('.modal-close');
            if (closeBtn) {
                event.preventDefault();
                event.stopPropagation();
                console.log('Botão de fechar modal clicado');
                closeModal(closeBtn.closest('.modal'));
            }
        });
        
        // Fechar com ESC
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                console.log('ESC pressionado, fechando modais abertos');
                document.querySelectorAll('.modal.open').forEach(closeModal);
            }
        });
        
        console.log('Sistema de modais inicializado com sucesso!');
    }

    function openModal(id) {
        var modal = typeof id === 'string' ? document.getElementById(id) : id;
        if (!modal) {
            console.error('Modal não encontrado:', id);
            return;
        }
        console.log('Abrindo modal:', modal.id || id);
        modal.classList.remove('hidden');
        modal.classList.add('flex', 'open');
        modal.setAttribute('aria-hidden', 'false');
        var focusable = modal.querySelector('input, textarea, select, button');
        if (focusable) { focusable.focus(); }
    }

    function closeModal(modal) {
        // Aceitar tanto ID (string) quanto elemento DOM
        var modalEl = typeof modal === 'string' ? document.getElementById(modal) : modal;
        if (!modalEl) {
            console.error('Modal não encontrado para fechar:', modal);
            return;
        }
        console.log('Fechando modal:', modalEl.id || 'sem ID');
        modalEl.classList.add('hidden');
        modalEl.classList.remove('flex', 'open');
        modalEl.setAttribute('aria-hidden', 'true');
    }

    function initVacancyEditor() {
        var form = document.querySelector('form[data-form="edit-vacancy"]');
        if (!form || listenersInitialized.vacancy) { return; }
        
        listenersInitialized.vacancy = true;
        
        // Usar delegação de eventos no document
        document.addEventListener('click', function(e) {
            var button = e.target.closest('[data-action="open-edit-vacancy"]');
            if (!button) return;
            
            e.preventDefault();
            
            try {
                var payload = JSON.parse(button.getAttribute('data-vacancy'));
                form.setAttribute('action', '/vacancies/' + payload.id + '/update');
                form.querySelector('#edit_vacancy_title').value = payload.title || '';
                form.querySelector('#edit_vacancy_description').value = payload.description || '';
                form.querySelector('#edit_vacancy_deadline').value = payload.deadline_at || '';
                form.querySelector('#edit_vacancy_status').value = payload.status || 'aberta';
                openModal('modal-edit-vacancy');
            } catch (error) {
                console.error('Erro ao preparar edição da vaga', error);
            }
        });
    }

    function initJuryEditor() {
        var form = document.querySelector('form[data-form="edit-jury"]');
        if (!form || listenersInitialized.jury) { return; }
        
        listenersInitialized.jury = true;
        
        // Usar delegação de eventos no document
        document.addEventListener('click', function(e) {
            var button = e.target.closest('[data-action="open-edit-jury"]');
            if (!button) return;
            
            e.preventDefault();
            
            try {
                var payload = JSON.parse(button.getAttribute('data-jury'));
                form.setAttribute('action', '/juries/' + payload.id + '/update');
                form.querySelector('#edit_jury_subject').value = payload.subject || '';
                form.querySelector('#edit_jury_exam_date').value = payload.exam_date || '';
                form.querySelector('#edit_jury_start').value = payload.start_time || '';
                form.querySelector('#edit_jury_end').value = payload.end_time || '';
                form.querySelector('#edit_jury_location').value = payload.location || '';
                form.querySelector('#edit_jury_room').value = payload.room || '';
                form.querySelector('#edit_jury_quota').value = payload.candidates_quota || 0;
                form.querySelector('#edit_jury_notes').value = payload.notes || '';
                openModal('modal-edit-jury');
            } catch (error) {
                console.error('Erro ao preparar edição do júri', error);
            }
        });
    }

    function initDragAndDrop() {
        if (typeof Sortable === 'undefined') { 
            console.error('Sortable não está carregado!');
            return; 
        }

        console.log('Inicializando Drag and Drop...');
        
        var draggableItems = document.querySelectorAll('.draggable-item');
        console.log('Itens arrastáveis encontrados:', draggableItems.length);
        
        draggableItems.forEach(function (item) {
            item.classList.add('bg-white', 'border', 'border-gray-200', 'rounded', 'p-3', 'shadow-sm', 'focus:outline-none', 'focus:ring-2', 'focus:ring-primary-500', 'cursor-move', 'transition-all');
        });

        var dropzones = document.querySelectorAll('.dropzone');
        console.log('Zonas de drop encontradas:', dropzones.length);
        
        dropzones.forEach(function (zone) {
            console.log('Inicializando dropzone:', zone.getAttribute('data-jury'));
            new Sortable(zone, {
                group: { name: 'vigilantes', pull: true, put: true },
                animation: 150,
                ghostClass: 'opacity-50',
                dragClass: 'shadow-lg',
                chosenClass: 'ring-2 ring-primary-500',
                onAdd: function (evt) {
                    var assignUrl = zone.getAttribute('data-assign-url');
                    var vigilanteId = evt.item.getAttribute('data-id');
                    var originJury = evt.from.getAttribute('data-jury');
                    var currentJury = zone.getAttribute('data-jury');
                    if (!assignUrl || !vigilanteId) { return; }
                    
                    evt.item.classList.add('opacity-50', 'pointer-events-none');
                    
                    sendRequest(assignUrl, { vigilante_id: vigilanteId }).then(function (success) {
                        evt.item.classList.remove('opacity-50', 'pointer-events-none');
                        if (!success) {
                            evt.from.appendChild(evt.item);
                            return;
                        }
                        evt.item.classList.add('bg-green-50', 'border-green-300');
                        setTimeout(function() {
                            evt.item.classList.remove('bg-green-50', 'border-green-300');
                        }, 1500);
                        
                        if (originJury && originJury !== currentJury) {
                            var unassignUrl = evt.from.getAttribute('data-unassign-url');
                            if (unassignUrl) {
                                sendRequest(unassignUrl, { vigilante_id: vigilanteId }, true);
                            }
                        }
                    });
                },
                onRemove: function (evt) {
                    var destinationJury = evt.to.getAttribute('data-jury');
                    if (destinationJury) { return; }
                    var unassignUrl = evt.from.getAttribute('data-unassign-url');
                    var vigilanteId = evt.item.getAttribute('data-id');
                    if (!unassignUrl || !vigilanteId) { return; }
                    sendRequest(unassignUrl, { vigilante_id: vigilanteId }).then(function (success) {
                        if (!success) {
                            evt.from.appendChild(evt.item);
                        }
                    });
                }
            });
        });

        var vigilantePool = document.getElementById('available-vigilantes');
        console.log('Pool de vigilantes:', vigilantePool ? 'Encontrado' : 'NÃO encontrado');
        
        if (vigilantePool) {
            console.log('Inicializando pool de vigilantes...');
            new Sortable(vigilantePool, {
                group: { name: 'vigilantes', pull: true, put: true },
                animation: 150,
                onStart: function() {
                    console.log('Começou a arrastar do pool');
                }
            });
        }

        document.querySelectorAll('.dropzone-single').forEach(function (zone) {
            new Sortable(zone, {
                group: { name: 'supervisores', pull: true, put: true },
                animation: 150,
                ghostClass: 'opacity-50',
                dragClass: 'shadow-lg',
                chosenClass: 'ring-2 ring-amber-500',
                onAdd: function (evt) {
                    var item = evt.item;
                    var url = zone.getAttribute('data-set-url');
                    var supervisorId = item.getAttribute('data-id');
                    if (!url || !supervisorId) { return; }
                    
                    Array.from(zone.querySelectorAll('.draggable-item')).forEach(function (node, index) {
                        if (index > 0 && node !== item) { node.remove(); }
                    });
                    
                    item.classList.add('opacity-50', 'pointer-events-none');
                    
                    sendRequest(url, { supervisor_id: supervisorId }).then(function (success) {
                        item.classList.remove('opacity-50', 'pointer-events-none');
                        if (!success) {
                            evt.from.appendChild(item);
                        } else {
                            item.classList.add('bg-amber-100', 'border-amber-300');
                        }
                    });
                }
            });
        });

        document.querySelectorAll('.supervisor-pool').forEach(function (pool) {
            new Sortable(pool, {
                group: { name: 'supervisores', pull: true, put: true },
                animation: 150
            });
        });
    }

    function sendRequest(url, payload, silent) {
        if (silent === undefined) { silent = false; }
        var params = new URLSearchParams();
        if (csrfToken) {
            params.append('csrf', csrfToken);
        }
        Object.keys(payload).forEach(function (key) {
            params.append(key, payload[key]);
        });
        return fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: params
        }).then(function (response) {
            return response.json().catch(function () {
                return { message: response.ok ? 'Operação concluída.' : 'Erro na operação.' };
            }).then(function (data) {
                if (!response.ok) {
                    if (!silent) { showToast('error', data.message || 'Não foi possível concluir.'); }
                    return false;
                }
                if (!silent) { showToast('success', data.message || 'Dados atualizados.'); }
                return true;
            });
        }).catch(function () {
            if (!silent) { showToast('error', 'Falha de comunicação com o servidor.'); }
            return false;
        });
    }

    function showToast(type, message) {
        if (window.toastr) {
            (toastr[type] || toastr.info)(message);
        } else {
            console.log('[' + type + '] ' + message);
        }
    }

    function initDisciplineRooms() {
        var btnAddDiscipline = document.getElementById('btn-add-discipline');
        var disciplinesContainer = document.getElementById('disciplines-container');
        
        if (!btnAddDiscipline || !disciplinesContainer) { return; }
        
        var disciplineIndex = 1;
        
        // Adicionar nova disciplina
        btnAddDiscipline.addEventListener('click', function() {
            var newDiscipline = document.createElement('div');
            newDiscipline.className = 'discipline-item border-2 border-primary-300 rounded-lg p-4 bg-primary-50 relative animate-fade-in';
            newDiscipline.innerHTML = `
                <button type="button" class="btn-remove-discipline absolute top-2 right-2 text-red-500 hover:text-red-700 z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                
                <div class="mb-4 pr-8">
                    <h4 class="text-sm font-bold text-primary-900 mb-3 uppercase">Disciplina #${disciplineIndex + 1}</h4>
                    <div class="grid md:grid-cols-4 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700">Nome da Disciplina *</label>
                            <input type="text" name="disciplines[${disciplineIndex}][subject]" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" placeholder="Ex: Física I" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Início *</label>
                            <input type="time" name="disciplines[${disciplineIndex}][start_time]" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Fim *</label>
                            <input type="time" name="disciplines[${disciplineIndex}][end_time]" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" required>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Salas desta Disciplina</label>
                        <button type="button" class="btn-add-room-to-discipline px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 flex items-center gap-1" data-discipline="${disciplineIndex}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Sala
                        </button>
                    </div>
                    <div class="rooms-list space-y-2" data-discipline="${disciplineIndex}">
                        <div class="room-row flex gap-2 items-start">
                            <div class="flex-1">
                                <input type="text" name="disciplines[${disciplineIndex}][rooms][0][room]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Nº Sala" required>
                            </div>
                            <div class="flex-1">
                                <input type="number" name="disciplines[${disciplineIndex}][rooms][0][candidates_quota]" min="1" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Candidatos" required>
                            </div>
                            <button type="button" class="btn-remove-room text-red-500 hover:text-red-700 p-1 hidden">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            disciplinesContainer.appendChild(newDiscipline);
            disciplineIndex++;
            updateDisciplineRemoveButtons();
        });
        
        // Remover disciplina
        disciplinesContainer.addEventListener('click', function(e) {
            var removeDiscBtn = e.target.closest('.btn-remove-discipline');
            if (removeDiscBtn) {
                var disciplineItem = removeDiscBtn.closest('.discipline-item');
                if (disciplineItem) {
                    disciplineItem.style.opacity = '0';
                    disciplineItem.style.transform = 'scale(0.95)';
                    disciplineItem.style.transition = 'all 0.2s';
                    setTimeout(function() {
                        disciplineItem.remove();
                        updateDisciplineRemoveButtons();
                        updateDisciplineNumbers();
                    }, 200);
                }
            }
            
            // Adicionar sala a disciplina específica
            var addRoomBtn = e.target.closest('.btn-add-room-to-discipline');
            if (addRoomBtn) {
                var disciplineIdx = addRoomBtn.getAttribute('data-discipline');
                var roomsList = disciplinesContainer.querySelector('.rooms-list[data-discipline="' + disciplineIdx + '"]');
                if (roomsList) {
                    var currentRooms = roomsList.querySelectorAll('.room-row');
                    var roomIdx = currentRooms.length;
                    
                    var newRoom = document.createElement('div');
                    newRoom.className = 'room-row flex gap-2 items-start';
                    newRoom.innerHTML = `
                        <div class="flex-1">
                            <input type="text" name="disciplines[${disciplineIdx}][rooms][${roomIdx}][room]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Nº Sala" required>
                        </div>
                        <div class="flex-1">
                            <input type="number" name="disciplines[${disciplineIdx}][rooms][${roomIdx}][candidates_quota]" min="1" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Candidatos" required>
                        </div>
                        <button type="button" class="btn-remove-room text-red-500 hover:text-red-700 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    `;
                    roomsList.appendChild(newRoom);
                    updateRoomRemoveButtons(roomsList);
                }
            }
            
            // Remover sala
            var removeRoomBtn = e.target.closest('.btn-remove-room');
            if (removeRoomBtn) {
                var roomRow = removeRoomBtn.closest('.room-row');
                var roomsList = roomRow ? roomRow.closest('.rooms-list') : null;
                if (roomRow) {
                    roomRow.style.opacity = '0';
                    roomRow.style.transition = 'opacity 0.2s';
                    setTimeout(function() {
                        roomRow.remove();
                        if (roomsList) {
                            updateRoomRemoveButtons(roomsList);
                        }
                    }, 200);
                }
            }
        });
        
        function updateDisciplineRemoveButtons() {
            var disciplines = disciplinesContainer.querySelectorAll('.discipline-item');
            disciplines.forEach(function(disc) {
                var removeBtn = disc.querySelector('.btn-remove-discipline');
                if (removeBtn) {
                    if (disciplines.length === 1) {
                        removeBtn.classList.add('hidden');
                    } else {
                        removeBtn.classList.remove('hidden');
                    }
                }
            });
        }
        
        function updateDisciplineNumbers() {
            var disciplines = disciplinesContainer.querySelectorAll('.discipline-item');
            disciplines.forEach(function(disc, idx) {
                var header = disc.querySelector('h4');
                if (header) {
                    header.textContent = 'Disciplina #' + (idx + 1);
                }
            });
        }
        
        function updateRoomRemoveButtons(roomsList) {
            var rooms = roomsList.querySelectorAll('.room-row');
            rooms.forEach(function(room) {
                var removeBtn = room.querySelector('.btn-remove-room');
                if (removeBtn) {
                    if (rooms.length === 1) {
                        removeBtn.classList.add('hidden');
                    } else {
                        removeBtn.classList.remove('hidden');
                    }
                }
            });
        }
        
        // Inicializar botões de remoção
        updateDisciplineRemoveButtons();
        var allRoomsLists = disciplinesContainer.querySelectorAll('.rooms-list');
        allRoomsLists.forEach(function(list) {
            updateRoomRemoveButtons(list);
        });
    }

    function initTemplates() {
        // Template: Criar estrutura de disciplinas
        var btnAddTemplateDiscipline = document.getElementById('btn-add-template-discipline');
        var templateDisciplinesContainer = document.getElementById('template-disciplines-container');
        
        if (!btnAddTemplateDiscipline || !templateDisciplinesContainer) { return; }
        
        var templateDisciplineIndex = 0;
        
        // Adicionar primeira disciplina automaticamente
        addTemplateDiscipline();
        
        btnAddTemplateDiscipline.addEventListener('click', function() {
            addTemplateDiscipline();
        });
        
        function addTemplateDiscipline() {
            var newDiscipline = document.createElement('div');
            newDiscipline.className = 'discipline-item border-2 border-primary-300 rounded-lg p-4 bg-primary-50 relative';
            newDiscipline.innerHTML = `
                <button type="button" class="btn-remove-template-discipline absolute top-2 right-2 text-red-500 hover:text-red-700 z-10">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
                
                <div class="mb-4 pr-8">
                    <h4 class="text-sm font-bold text-primary-900 mb-3 uppercase">Disciplina #${templateDisciplineIndex + 1}</h4>
                    <div class="grid md:grid-cols-4 gap-3">
                        <div class="md:col-span-2">
                            <label class="block text-xs font-medium text-gray-700">Nome da Disciplina *</label>
                            <input type="text" name="disciplines[${templateDisciplineIndex}][subject]" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" placeholder="Ex: Matemática I" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Início *</label>
                            <input type="time" name="disciplines[${templateDisciplineIndex}][start_time]" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" required>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700">Fim *</label>
                            <input type="time" name="disciplines[${templateDisciplineIndex}][end_time]" class="mt-1 w-full rounded border border-gray-300 px-2 py-2 text-sm" required>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white border border-gray-200 rounded-lg p-3">
                    <div class="flex items-center justify-between mb-2">
                        <label class="block text-xs font-semibold text-gray-700 uppercase">Salas</label>
                        <button type="button" class="btn-add-template-room px-2 py-1 bg-blue-500 text-white text-xs rounded hover:bg-blue-600 flex items-center gap-1" data-discipline="${templateDisciplineIndex}">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Sala
                        </button>
                    </div>
                    <div class="template-rooms-list space-y-2" data-discipline="${templateDisciplineIndex}">
                        <div class="room-row flex gap-2 items-start">
                            <div class="flex-1">
                                <input type="text" name="disciplines[${templateDisciplineIndex}][rooms][0][room]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Nº Sala" required>
                            </div>
                            <div class="flex-1">
                                <input type="number" name="disciplines[${templateDisciplineIndex}][rooms][0][candidates_quota]" min="1" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Candidatos" required>
                            </div>
                            <button type="button" class="btn-remove-template-room text-red-500 hover:text-red-700 p-1 hidden">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
            
            templateDisciplinesContainer.appendChild(newDiscipline);
            templateDisciplineIndex++;
            updateTemplateDisciplineButtons();
        }
        
        // Eventos delegados
        templateDisciplinesContainer.addEventListener('click', function(e) {
            // Remover disciplina
            var removeDiscBtn = e.target.closest('.btn-remove-template-discipline');
            if (removeDiscBtn) {
                var disciplineItem = removeDiscBtn.closest('.discipline-item');
                if (disciplineItem) {
                    disciplineItem.remove();
                    updateTemplateDisciplineButtons();
                    updateTemplateDisciplineNumbers();
                }
            }
            
            // Adicionar sala
            var addRoomBtn = e.target.closest('.btn-add-template-room');
            if (addRoomBtn) {
                var disciplineIdx = addRoomBtn.getAttribute('data-discipline');
                var roomsList = templateDisciplinesContainer.querySelector('.template-rooms-list[data-discipline="' + disciplineIdx + '"]');
                if (roomsList) {
                    var currentRooms = roomsList.querySelectorAll('.room-row');
                    var roomIdx = currentRooms.length;
                    
                    var newRoom = document.createElement('div');
                    newRoom.className = 'room-row flex gap-2 items-start';
                    newRoom.innerHTML = `
                        <div class="flex-1">
                            <input type="text" name="disciplines[${disciplineIdx}][rooms][${roomIdx}][room]" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Nº Sala" required>
                        </div>
                        <div class="flex-1">
                            <input type="number" name="disciplines[${disciplineIdx}][rooms][${roomIdx}][candidates_quota]" min="1" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" placeholder="Candidatos" required>
                        </div>
                        <button type="button" class="btn-remove-template-room text-red-500 hover:text-red-700 p-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    `;
                    roomsList.appendChild(newRoom);
                    updateTemplateRoomButtons(roomsList);
                }
            }
            
            // Remover sala
            var removeRoomBtn = e.target.closest('.btn-remove-template-room');
            if (removeRoomBtn) {
                var roomRow = removeRoomBtn.closest('.room-row');
                var roomsList = roomRow ? roomRow.closest('.template-rooms-list') : null;
                if (roomRow) {
                    roomRow.remove();
                    if (roomsList) {
                        updateTemplateRoomButtons(roomsList);
                    }
                }
            }
        });
        
        function updateTemplateDisciplineButtons() {
            var disciplines = templateDisciplinesContainer.querySelectorAll('.discipline-item');
            disciplines.forEach(function(disc) {
                var removeBtn = disc.querySelector('.btn-remove-template-discipline');
                if (removeBtn) {
                    if (disciplines.length === 1) {
                        removeBtn.classList.add('hidden');
                    } else {
                        removeBtn.classList.remove('hidden');
                    }
                }
            });
        }
        
        function updateTemplateDisciplineNumbers() {
            var disciplines = templateDisciplinesContainer.querySelectorAll('.discipline-item');
            disciplines.forEach(function(disc, idx) {
                var header = disc.querySelector('h4');
                if (header) {
                    header.textContent = 'Disciplina #' + (idx + 1);
                }
            });
        }
        
        function updateTemplateRoomButtons(roomsList) {
            var rooms = roomsList.querySelectorAll('.room-row');
            rooms.forEach(function(room) {
                var removeBtn = room.querySelector('.btn-remove-template-room');
                if (removeBtn) {
                    if (rooms.length === 1) {
                        removeBtn.classList.add('hidden');
                    } else {
                        removeBtn.classList.remove('hidden');
                    }
                }
            });
        }
        
        // Carregar template - usar delegação de eventos
        if (!listenersInitialized.template) {
            listenersInitialized.template = true;
            
            document.addEventListener('click', function(e) {
                var btn = e.target.closest('.btn-load-template');
                if (!btn) return;
                
                e.preventDefault();
                
                var templateId = btn.getAttribute('data-template-id');
                if (!templateId) return;
                
                fetch('/locations/templates/' + templateId + '/load', {
                    method: 'GET',
                    headers: {'Content-Type': 'application/json'}
                })
                .then(function(response) { return response.json(); })
                .then(function(data) {
                    if (data.template) {
                        loadTemplateIntoForm(data.template);
                        showToast('success', 'Template carregado! Preencha a data e crie os júris.');
                    }
                })
                .catch(function() {
                    showToast('error', 'Erro ao carregar template.');
                });
            });
        }
        
        function loadTemplateIntoForm(template) {
            // Redirecionar para página de júris e preencher o modal
            window.location.href = '/juries?load_template=' + template.id;
        }
    }

    function initQuickEdit() {
        var modal = document.getElementById('modal-quick-edit');
        var form = document.getElementById('form-quick-edit');
        
        if (!modal || !form || listenersInitialized.quick) { return; }
        
        listenersInitialized.quick = true;
        
        // Usar delegação de eventos no document para capturar cliques em botões dinâmicos
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-edit-inline');
            if (!btn) return;
            
            e.preventDefault();
            
            var juryId = btn.getAttribute('data-jury-id');
            var roomEl = btn.closest('.border').querySelector('.text-sm.font-semibold');
            var quotaEl = btn.closest('.border').querySelector('.text-xs.text-gray-500');
            
            // Extrair valores atuais
            var roomText = roomEl ? roomEl.textContent.replace('Sala ', '').trim() : '';
            var quotaText = quotaEl ? quotaEl.textContent.replace(' candidatos', '').trim() : '';
            
            // Preencher formulário
            document.getElementById('quick_jury_id').value = juryId;
            document.getElementById('quick_room').value = roomText;
            document.getElementById('quick_quota').value = quotaText;
            
            // Abrir modal
            openModal('modal-quick-edit');
        });
        
        // Submit do formulário - remover listener anterior se existir
        var newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);
        form = newForm;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var juryId = document.getElementById('quick_jury_id').value;
            var formData = new FormData(form);
            
            fetch('/juries/' + juryId + '/update-quick', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast('success', 'Sala atualizada com sucesso!');
                    closeModal('modal-quick-edit');
                    setTimeout(function() { window.location.reload(); }, 1000);
                } else {
                    showToast('error', data.message || 'Erro ao atualizar sala.');
                }
            })
            .catch(function() {
                showToast('error', 'Erro ao processar pedido.');
            });
        });
    }

    function initBatchEdit() {
        var modal = document.getElementById('modal-batch-edit');
        var form = document.getElementById('form-batch-edit');
        
        if (!modal || !form || listenersInitialized.batch) { return; }
        
        listenersInitialized.batch = true;
        
        // Usar delegação de eventos no document
        document.addEventListener('click', function(e) {
            var btn = e.target.closest('[data-action="edit-discipline-batch"]');
            if (!btn) return;
            
            e.preventDefault();
            
            var groupData = JSON.parse(btn.getAttribute('data-group'));
            
            // Preencher informações da disciplina
            document.getElementById('batch_subject').value = groupData.subject;
            document.getElementById('batch_date').value = groupData.exam_date;
            document.getElementById('batch_start').value = groupData.start_time;
            document.getElementById('batch_end').value = groupData.end_time;
            document.getElementById('batch_location').value = groupData.location;
            
            // Criar lista de salas
            var roomsList = document.getElementById('batch-rooms-list');
            roomsList.innerHTML = '';
            
            groupData.juries.forEach(function(jury, index) {
                var roomDiv = document.createElement('div');
                roomDiv.className = 'grid grid-cols-3 gap-3 items-start bg-white border border-gray-200 rounded p-3';
                roomDiv.innerHTML = `
                    <input type="hidden" name="juries[${index}][id]" value="${jury.id}">
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Sala</label>
                        <input type="text" name="juries[${index}][room]" value="${jury.room}" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" required>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Candidatos</label>
                        <input type="number" name="juries[${index}][candidates_quota]" value="${jury.candidates_quota}" min="1" class="w-full rounded border border-gray-300 px-2 py-1.5 text-sm" required>
                    </div>
                    <div class="flex items-end">
                        <span class="text-xs text-gray-500 pb-2">Sala ${index + 1}</span>
                    </div>
                `;
                roomsList.appendChild(roomDiv);
            });
            
            // Abrir modal
            openModal('modal-batch-edit');
        });
        
        // Submit do formulário - remover listener anterior se existir
        var newForm = form.cloneNode(true);
        form.parentNode.replaceChild(newForm, form);
        form = newForm;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            var formData = new FormData(form);
            
            fetch('/juries/update-batch', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                body: formData
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    showToast('success', 'Disciplina atualizada com sucesso!');
                    closeModal('modal-batch-edit');
                    setTimeout(function() { window.location.reload(); }, 1000);
                } else {
                    showToast('error', data.message || 'Erro ao atualizar disciplina.');
                }
            })
            .catch(function() {
                showToast('error', 'Erro ao processar pedido.');
            });
        });
    }
})();
