/**
 * Sistema de Drag-and-Drop para Alocação de Vigilantes e Supervisores
 * Implementa validações em tempo real, feedback visual e equilíbrio de carga
 */

// Estado global
const AllocationState = {
    draggingElement: null,
    draggedData: null,
    validationCache: new Map()
};

/**
 * Inicializar Drag-and-Drop
 */
document.addEventListener('DOMContentLoaded', function() {
    initDraggableLists();
    initDropZones();
    initSearch();
    refreshMetrics();
    
    console.log('✅ Sistema de Drag-and-Drop inicializado');
});

/**
 * Tornar listas de vigilantes/supervisores arrastáveis
 */
function initDraggableLists() {
    // Lista de vigilantes
    const availableList = document.getElementById('availableList');
    if (availableList) {
        Sortable.create(availableList, {
            group: {
                name: 'vigilantes',
                pull: 'clone',
                put: false
            },
            animation: 150,
            sort: false,
            onStart: function(evt) {
                const item = evt.item;
                AllocationState.draggingElement = item;
                AllocationState.draggedData = {
                    id: item.dataset.id,
                    name: item.dataset.name,
                    workload: parseInt(item.dataset.workload || 0),
                    role: item.dataset.role
                };
                console.log('Arrastando:', AllocationState.draggedData);
            },
            onEnd: function(evt) {
                // Limpar clones
                if (evt.item && evt.clone) {
                    evt.item.remove();
                }
                resetAllDropZones();
            }
        });
    }
    
    // Lista de supervisores
    const supervisorsList = document.getElementById('supervisorsList');
    if (supervisorsList) {
        Sortable.create(supervisorsList, {
            group: {
                name: 'supervisors',
                pull: 'clone',
                put: false
            },
            animation: 150,
            sort: false,
            onStart: function(evt) {
                const item = evt.item;
                AllocationState.draggingElement = item;
                AllocationState.draggedData = {
                    id: item.dataset.id,
                    name: item.dataset.name,
                    workload: parseInt(item.dataset.workload || 0),
                    role: item.dataset.role
                };
            },
            onEnd: function(evt) {
                if (evt.item && evt.clone) {
                    evt.item.remove();
                }
                resetAllDropZones();
            }
        });
    }
}

/**
 * Inicializar zonas de drop (júris)
 */
function initDropZones() {
    // Zonas de vigilantes
    document.querySelectorAll('.drop-zone-vigilantes').forEach(zone => {
        Sortable.create(zone, {
            group: 'vigilantes',
            animation: 150,
            onAdd: function(evt) {
                handleDrop(evt, zone, 'vigilante');
            },
            onMove: function(evt) {
                return handleDragOver(evt, zone, 'vigilante');
            }
        });
    });
    
    // Zonas de supervisores
    document.querySelectorAll('.drop-zone-supervisor').forEach(zone => {
        Sortable.create(zone, {
            group: 'supervisors',
            animation: 150,
            onAdd: function(evt) {
                handleDrop(evt, zone, 'supervisor');
            },
            onMove: function(evt) {
                return handleDragOver(evt, zone, 'supervisor');
            }
        });
    });
}

/**
 * Handler de drag over - validação visual em tempo real
 */
function handleDragOver(evt, zone, expectedRole) {
    if (!AllocationState.draggedData) return false;
    
    const juryId = parseInt(zone.dataset.juryId);
    const draggedId = parseInt(AllocationState.draggedData.id);
    const draggedRole = AllocationState.draggedData.role;
    
    // Verificar tipo correto
    if (draggedRole !== expectedRole) {
        setDropZoneFeedback(zone, 'invalid', 'Tipo incorreto');
        return false;
    }
    
    // Se for supervisor, verificar se já tem
    if (expectedRole === 'supervisor') {
        const hasExisting = zone.querySelector('.allocated-person');
        if (hasExisting) {
            setDropZoneFeedback(zone, 'warning', 'Já tem supervisor (será substituído)');
            return true; // Permitir substituição
        }
    }
    
    // Se for vigilante, verificar capacidade
    if (expectedRole === 'vigilante') {
        const capacity = parseInt(zone.dataset.capacity || 2);
        const current = parseInt(zone.dataset.current || 0);
        
        if (current >= capacity) {
            setDropZoneFeedback(zone, 'invalid', 'Capacidade máxima atingida');
            return false;
        }
    }
    
    // Validação assíncrona com cache
    const cacheKey = `${draggedId}_${juryId}_${expectedRole}`;
    
    if (AllocationState.validationCache.has(cacheKey)) {
        const cached = AllocationState.validationCache.get(cacheKey);
        applyValidationFeedback(zone, cached);
        return cached.can_assign;
    }
    
    // Validar no servidor (assíncrono)
    validateAssignment(draggedId, juryId, expectedRole).then(result => {
        AllocationState.validationCache.set(cacheKey, result);
        applyValidationFeedback(zone, result);
    });
    
    // Permitir temporariamente (será validado no drop)
    setDropZoneFeedback(zone, 'valid', 'Validando...');
    return true;
}

/**
 * Handler de drop - executar alocação
 */
function handleDrop(evt, zone, role) {
    evt.item.remove(); // Remover clone
    
    if (!AllocationState.draggedData) {
        toastr.error('Erro ao processar drag');
        return;
    }
    
    const juryId = parseInt(zone.dataset.juryId);
    const personId = parseInt(AllocationState.draggedData.id);
    const personName = AllocationState.draggedData.name;
    
    // Loading
    setDropZoneFeedback(zone, 'valid', 'Alocando...');
    
    if (role === 'supervisor') {
        assignSupervisor(juryId, personId, personName, zone);
    } else {
        assignVigilante(juryId, personId, personName, zone);
    }
    
    // Limpar cache
    AllocationState.validationCache.clear();
}

/**
 * Validar alocação no servidor
 */
async function validateAssignment(personId, juryId, type) {
    try {
        const response = await fetch('/api/allocation/can-assign', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                vigilante_id: personId,
                jury_id: juryId,
                type: type
            })
        });
        
        return await response.json();
    } catch (error) {
        console.error('Erro na validação:', error);
        return { can_assign: false, reason: 'Erro de conexão', severity: 'error' };
    }
}

/**
 * Alocar vigilante (com atualização dinâmica)
 */
async function assignVigilante(juryId, vigilanteId, vigilanteName, zone) {
    try {
        const response = await fetch(`/juries/${juryId}/assign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                vigilante_id: vigilanteId,
                csrf: CSRF_TOKEN
            })
        });
        
        const result = await response.json();
        
        if (response.ok && result.message) {
            toastr.success(result.message);
            
            // Adicionar visualmente
            addAllocatedPerson(zone, vigilanteId, vigilanteName, 'vigilante', juryId);
            
            // Atualizar contador
            const current = parseInt(zone.dataset.current || 0);
            zone.dataset.current = current + 1;
            updateVigilanteCounter(zone);
            
            // Recarregar listas de disponíveis (atualizar badges de carga)
            await reloadAvailableLists();
            
            // Atualizar métricas
            refreshMetrics();
        } else {
            toastr.error(result.message || 'Erro ao alocar vigilante');
        }
        
        resetAllDropZones();
        
    } catch (error) {
        console.error('Erro:', error);
        toastr.error('Erro ao alocar vigilante');
        resetAllDropZones();
    }
}

/**
 * Alocar supervisor (com atualização dinâmica)
 */
async function assignSupervisor(juryId, supervisorId, supervisorName, zone) {
    try {
        const response = await fetch(`/juries/${juryId}/set-supervisor`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                supervisor_id: supervisorId,
                csrf: CSRF_TOKEN
            })
        });
        
        const result = await response.json();
        
        if (response.ok && result.message) {
            toastr.success(result.message);
            
            // Limpar zona e adicionar novo
            zone.innerHTML = '';
            addAllocatedPerson(zone, supervisorId, supervisorName, 'supervisor', juryId);
            
            zone.classList.add('bg-blue-50', 'border-blue-300');
            zone.classList.remove('border-gray-300');
            
            // Recarregar listas
            await reloadAvailableLists();
            
            // Atualizar métricas
            refreshMetrics();
        } else {
            toastr.error(result.message || 'Erro ao alocar supervisor');
        }
        
        resetAllDropZones();
        
    } catch (error) {
        console.error('Erro:', error);
        toastr.error('Erro ao alocar supervisor');
        resetAllDropZones();
    }
}

/**
 * Adicionar pessoa alocada visualmente
 */
function addAllocatedPerson(zone, personId, personName, role, juryId) {
    const div = document.createElement('div');
    div.className = `allocated-person ${role === 'supervisor' ? 'bg-blue-100 border-blue-300' : 'bg-green-100 border-green-300'} border rounded p-2 mb-2 text-sm`;
    div.innerHTML = `
        <div class="flex justify-between items-center">
            <span class="font-medium ${role === 'supervisor' ? 'text-blue-900' : 'text-green-900'}">${personName}</span>
            <button onclick="${role === 'supervisor' ? 'removeSupervisor' : 'removeVigilante'}(${juryId}, ${personId})" 
                    class="text-red-500 hover:text-red-700 text-xs">✕</button>
        </div>
    `;
    zone.appendChild(div);
}

/**
 * Remover vigilante (com atualização dinâmica)
 */
async function removeVigilante(juryId, vigilanteId) {
    if (!confirm('Remover este vigilante?')) return;
    
    try {
        const response = await fetch(`/juries/${juryId}/unassign`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                vigilante_id: vigilanteId,
                csrf: CSRF_TOKEN
            })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            toastr.success(result.message);
            
            // Atualizar visualmente sem reload
            removeAllocatedPersonFromDOM(juryId, vigilanteId, 'vigilante');
            
            // Recarregar listas de disponíveis
            await reloadAvailableLists();
            
            // Atualizar métricas
            refreshMetrics();
        } else {
            toastr.error(result.message || 'Erro ao remover');
        }
    } catch (error) {
        console.error('Erro:', error);
        toastr.error('Erro ao remover vigilante');
    }
}

/**
 * Remover supervisor (com atualização dinâmica)
 */
async function removeSupervisor(juryId, supervisorId) {
    if (!confirm('Remover este supervisor?')) return;
    
    try {
        const response = await fetch(`/juries/${juryId}/set-supervisor`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                supervisor_id: null,
                csrf: CSRF_TOKEN
            })
        });
        
        const result = await response.json();
        
        if (response.ok) {
            toastr.success('Supervisor removido');
            
            // Atualizar visualmente
            removeAllocatedPersonFromDOM(juryId, supervisorId, 'supervisor');
            
            // Recarregar listas
            await reloadAvailableLists();
            
            // Atualizar métricas
            refreshMetrics();
        } else {
            toastr.error(result.message || 'Erro ao remover');
        }
    } catch (error) {
        console.error('Erro:', error);
        toastr.error('Erro ao remover supervisor');
    }
}

/**
 * Auto-alocar júri (rápido - com atualização dinâmica)
 */
async function autoAllocateJury(juryId) {
    if (!confirm('Auto-alocar vigilantes neste júri usando algoritmo de equilíbrio?')) return;
    
    const btn = event.target;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.textContent = 'Alocando...';
    
    try {
        const response = await fetch('/api/allocation/auto-allocate-jury', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                jury_id: juryId,
                csrf: CSRF_TOKEN
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            toastr.success(result.message);
            
            // Recarregar página após pequeno delay (necessário para ver os alocados)
            setTimeout(() => location.reload(), 1000);
        } else {
            toastr.error(result.message || 'Erro na auto-alocação');
            btn.disabled = false;
            btn.textContent = originalText;
        }
    } catch (error) {
        console.error('Erro:', error);
        toastr.error('Erro na auto-alocação');
        btn.disabled = false;
        btn.textContent = originalText;
    }
}

/**
 * Auto-alocar disciplina completa (OTIMIZADO)
 */
async function autoAllocateDiscipline(subject, examDate) {
    if (!confirm(`Auto-alocar TODOS os júris de "${subject}" usando algoritmo de equilíbrio?\n\nProcessamento otimizado em lote.`)) return;
    
    const btn = event.target;
    const originalText = btn.textContent;
    btn.disabled = true;
    btn.innerHTML = '<span class="animate-pulse">⏳ Alocando...</span>';
    
    const startTime = Date.now();
    
    try {
        const response = await fetch('/api/allocation/auto-allocate-discipline', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': CSRF_TOKEN
            },
            body: JSON.stringify({
                subject: subject,
                exam_date: examDate,
                csrf: CSRF_TOKEN
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            const duration = ((Date.now() - startTime) / 1000).toFixed(2);
            toastr.success(`${result.message}`, 'Sucesso!', { timeOut: 3000 });
            
            // Recarregar após pequeno delay
            setTimeout(() => location.reload(), 1500);
        } else {
            toastr.error(result.message || 'Erro na auto-alocação');
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    } catch (error) {
        console.error('Erro:', error);
        toastr.error('Erro na auto-alocação');
        btn.disabled = false;
        btn.innerHTML = originalText;
    }
}

/**
 * Atualizar métricas em tempo real
 */
async function refreshMetrics() {
    try {
        const response = await fetch('/api/allocation/metrics');
        const data = await response.json();
        
        if (data.success && data.metrics) {
            const m = data.metrics;
            
            // Atualizar DOM
            updateMetric('total-juries', m.total_juries);
            updateMetric('slots-available', m.slots_available);
            updateMetric('allocated', m.total_allocated);
            updateMetric('no-supervisor', m.juries_without_supervisor);
            updateMetric('std-dev', parseFloat(m.workload_std_deviation).toFixed(2));
            
            // Atualizar qualidade do equilíbrio
            const balanceEl = document.getElementById('metric-balance');
            if (balanceEl) {
                let html = '';
                if (m.balance_quality === 'excellent') {
                    html = '<span class="text-green-600">Excelente</span>';
                } else if (m.balance_quality === 'good') {
                    html = '<span class="text-yellow-600">Bom</span>';
                } else {
                    html = '<span class="text-red-600">Melhorar</span>';
                }
                balanceEl.innerHTML = html;
            }
        }
    } catch (error) {
        console.error('Erro ao atualizar métricas:', error);
    }
}

function updateMetric(id, value) {
    const el = document.getElementById(`metric-${id}`);
    if (el) el.textContent = value;
}

/**
 * Feedback visual de drop zone
 */
function setDropZoneFeedback(zone, type, message) {
    zone.classList.remove('drag-over-valid', 'drag-over-warning', 'drag-over-invalid');
    
    if (type === 'valid') {
        zone.classList.add('drag-over-valid');
    } else if (type === 'warning') {
        zone.classList.add('drag-over-warning');
    } else if (type === 'invalid') {
        zone.classList.add('drag-over-invalid');
    }
    
    zone.title = message || '';
}

function applyValidationFeedback(zone, validation) {
    if (validation.can_assign) {
        const feedbackType = validation.severity === 'warning' ? 'warning' : 'valid';
        setDropZoneFeedback(zone, feedbackType, validation.reason || 'Pode alocar');
    } else {
        setDropZoneFeedback(zone, 'invalid', validation.reason || 'Não pode alocar');
    }
}

function resetAllDropZones() {
    document.querySelectorAll('.drop-zone').forEach(zone => {
        zone.classList.remove('drag-over-valid', 'drag-over-warning', 'drag-over-invalid');
        zone.title = '';
    });
}

/**
 * Atualizar contador de vigilantes
 */
function updateVigilanteCounter(zone) {
    const capacity = parseInt(zone.dataset.capacity || 2);
    const current = parseInt(zone.dataset.current || 0);
    
    const label = zone.parentElement.querySelector('label');
    if (label) {
        label.textContent = `Vigilantes (${current}/${capacity})`;
    }
    
    // Atualizar cor da zona
    if (current >= capacity) {
        zone.classList.add('bg-green-50', 'border-green-300');
        zone.classList.remove('border-gray-300');
    }
}

/**
 * Busca de vigilantes
 */
function initSearch() {
    const searchInput = document.getElementById('searchVigilantes');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase();
            const items = document.querySelectorAll('#availableList .draggable-item');
            
            items.forEach(item => {
                const name = item.dataset.name.toLowerCase();
                if (name.includes(term)) {
                    item.style.display = '';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
}

/**
 * Recarregar listas de vigilantes e supervisores disponíveis (atualização dinâmica)
 */
async function reloadAvailableLists() {
    try {
        // Recarregar vigilantes
        const vigilantesResponse = await fetch('/api/allocation/available-vigilantes');
        const vigilantesData = await vigilantesResponse.json();
        
        if (vigilantesData.success) {
            updateVigilantesList(vigilantesData.vigilantes);
        }
        
        // Recarregar supervisores
        const supervisorsResponse = await fetch('/api/allocation/available-supervisors');
        const supervisorsData = await supervisorsResponse.json();
        
        if (supervisorsData.success) {
            updateSupervisorsList(supervisorsData.supervisors);
        }
        
        console.log('✅ Listas atualizadas dinamicamente');
        
    } catch (error) {
        console.error('Erro ao recarregar listas:', error);
    }
}

/**
 * Atualizar lista de vigilantes no DOM
 */
function updateVigilantesList(vigilantes) {
    const listContainer = document.getElementById('availableList');
    if (!listContainer) return;
    
    // Salvar termo de busca atual
    const searchInput = document.getElementById('searchVigilantes');
    const searchTerm = searchInput ? searchInput.value : '';
    
    // Limpar e reconstruir
    listContainer.innerHTML = '';
    
    vigilantes.forEach(v => {
        const workload = parseInt(v.workload_score || 0);
        const workloadClass = workload === 0 ? 'workload-low' : (workload <= 2 ? 'workload-medium' : 'workload-high');
        
        const div = document.createElement('div');
        div.className = 'draggable-item bg-gray-50 border rounded p-3 hover:bg-gray-100';
        div.dataset.id = v.id;
        div.dataset.name = v.name;
        div.dataset.workload = workload;
        div.dataset.role = 'vigilante';
        
        div.innerHTML = `
            <div class="flex justify-between items-start">
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-gray-900 truncate">
                        ${v.name}
                    </div>
                    <div class="text-xs text-gray-500 truncate">
                        ${v.email}
                    </div>
                </div>
                <span class="workload-badge ${workloadClass} ml-2 flex-shrink-0">
                    ${workload}
                </span>
            </div>
            <div class="text-xs text-gray-400 mt-1">
                Vigias: ${v.vigilance_count || 0} | Sup: ${v.supervision_count || 0}
            </div>
        `;
        
        listContainer.appendChild(div);
    });
    
    // Re-inicializar Sortable
    initDraggableLists();
    
    // Reaplicar filtro de busca se existir
    if (searchTerm && searchInput) {
        searchInput.value = searchTerm;
        searchInput.dispatchEvent(new Event('input'));
    }
}

/**
 * Atualizar lista de supervisores no DOM
 */
function updateSupervisorsList(supervisors) {
    const listContainer = document.getElementById('supervisorsList');
    if (!listContainer) return;
    
    listContainer.innerHTML = '';
    
    supervisors.forEach(s => {
        const workload = parseInt(s.workload_score || 0);
        const workloadClass = workload === 0 ? 'workload-low' : (workload <= 3 ? 'workload-medium' : 'workload-high');
        
        const div = document.createElement('div');
        div.className = 'draggable-item bg-blue-50 border border-blue-200 rounded p-2 hover:bg-blue-100';
        div.dataset.id = s.id;
        div.dataset.name = s.name;
        div.dataset.workload = workload;
        div.dataset.role = 'supervisor';
        
        div.innerHTML = `
            <div class="flex justify-between items-center">
                <div class="text-sm font-medium text-gray-900 truncate">
                    ${s.name}
                </div>
                <span class="workload-badge ${workloadClass} ml-2">
                    ${workload}
                </span>
            </div>
            <div class="text-xs text-gray-500 mt-1">
                Supervisões: ${s.supervision_count || 0}
            </div>
        `;
        
        listContainer.appendChild(div);
    });
    
    // Re-inicializar Sortable
    initDraggableLists();
}

/**
 * Remover pessoa alocada do DOM
 */
function removeAllocatedPersonFromDOM(juryId, personId, role) {
    const juryCard = document.querySelector(`[data-jury-id="${juryId}"]`);
    if (!juryCard) return;
    
    if (role === 'supervisor') {
        const zone = juryCard.querySelector('.drop-zone-supervisor');
        if (zone) {
            zone.innerHTML = '<div class="text-xs text-gray-400 text-center py-2">Arraste supervisor aqui</div>';
            zone.classList.remove('bg-blue-50', 'border-blue-300');
            zone.classList.add('border-gray-300');
        }
    } else {
        // Vigilante: remover elemento específico e atualizar contador
        const zone = juryCard.querySelector('.drop-zone-vigilantes');
        if (zone) {
            const allocatedPerson = Array.from(zone.querySelectorAll('.allocated-person')).find(el => {
                const btn = el.querySelector('button');
                return btn && btn.getAttribute('onclick').includes(personId);
            });
            
            if (allocatedPerson) {
                allocatedPerson.remove();
            }
            
            // Atualizar contador
            const current = parseInt(zone.dataset.current || 0);
            if (current > 0) {
                zone.dataset.current = current - 1;
                updateVigilanteCounter(zone);
            }
            
            // Se ficou vazio, mostrar placeholder
            if (zone.querySelectorAll('.allocated-person').length === 0) {
                const placeholder = document.createElement('div');
                placeholder.className = 'text-xs text-gray-400 text-center py-2';
                placeholder.textContent = 'Arraste vigilantes aqui';
                zone.appendChild(placeholder);
            }
        }
    }
}

// Expor funções globais
window.autoAllocateJury = autoAllocateJury;
window.autoAllocateDiscipline = autoAllocateDiscipline;
window.removeVigilante = removeVigilante;
window.removeSupervisor = removeSupervisor;
window.refreshMetrics = refreshMetrics;
window.reloadAvailableLists = reloadAvailableLists;
