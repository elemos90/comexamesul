<?php
$title = 'Gestão de Salas';
$breadcrumbs = [
    ['label' => 'Dados Mestres'],
    ['label' => 'Salas']
];
?>

<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Salas de Exames</h1>
            <p class="text-gray-600 mt-1">Gerir salas de realização de júris por local</p>
        </div>
        <?php if ($locationId > 0): ?>
        <button type="button" data-modal-target="modal-create-room" 
                class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nova Sala
        </button>
        <?php endif; ?>
    </div>

    <!-- Filtro por Local -->
    <div class="bg-white rounded-lg shadow-sm border p-4">
        <form method="GET" action="/master-data/rooms" class="flex items-end gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-gray-700 mb-2" for="location">Selecionar Local *</label>
                <select id="location" name="location" class="w-full rounded border border-gray-300 px-3 py-2" onchange="this.form.submit()" required>
                    <option value="">-- Escolha um local --</option>
                    <?php foreach ($locations as $loc): ?>
                    <option value="<?= $loc['id'] ?>" <?= $locationId === (int) $loc['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($loc['code']) ?> - <?= htmlspecialchars($loc['name']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-500">
                Filtrar
            </button>
        </form>
    </div>

    <?php if ($locationId === 0): ?>
        <!-- Nenhum local selecionado -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-8 text-center">
            <svg class="mx-auto h-12 w-12 text-blue-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-lg font-medium text-blue-900 mb-2">Selecione um local</h3>
            <p class="text-blue-700">Para visualizar e gerir salas, escolha um local no filtro acima.</p>
        </div>
    <?php else: ?>
        <!-- Informação do Local Selecionado -->
        <?php if ($selectedLocation): ?>
        <div class="bg-white rounded-lg shadow-sm border p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">
                        <?= htmlspecialchars($selectedLocation['code']) ?> - <?= htmlspecialchars($selectedLocation['name']) ?>
                    </h2>
                    <?php if (!empty($selectedLocation['address'])): ?>
                    <p class="text-sm text-gray-600 mt-1">
                        📍 <?= htmlspecialchars($selectedLocation['address']) ?>
                        <?php if (!empty($selectedLocation['city'])): ?>
                        - <?= htmlspecialchars($selectedLocation['city']) ?>
                        <?php endif; ?>
                    </p>
                    <?php endif; ?>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">Total de Salas</p>
                    <p class="text-2xl font-bold text-primary-600"><?= count($rooms) ?></p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tabela de Salas -->
        <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Capacidade</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Edifício/Piso</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Júris</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (empty($rooms)): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                Nenhuma sala cadastrada neste local. Clique em "Nova Sala" para começar.
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($rooms as $r): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="text-sm font-mono font-semibold text-gray-900"><?= htmlspecialchars($r['code']) ?></span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($r['name']) ?></div>
                                    <?php if (!empty($r['notes'])): ?>
                                    <div class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($r['notes']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        👥 <?= (int) $r['capacity'] ?> pessoas
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php if (!empty($r['building']) || !empty($r['floor'])): ?>
                                        <?= !empty($r['building']) ? htmlspecialchars($r['building']) : '' ?>
                                        <?= !empty($r['floor']) ? ' - ' . htmlspecialchars($r['floor']) : '' ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <?= (int) ($r['jury_count'] ?? 0) ?> júris
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if ((int) $r['active'] === 1): ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Ativo
                                    </span>
                                    <?php else: ?>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inativo
                                    </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                                    <button type="button" onclick='editRoom(<?= json_encode($r, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'
                                            class="text-blue-600 hover:text-blue-900">Editar</button>
                                    <button type="button" onclick="toggleRoomStatus(<?= $r['id'] ?>)"
                                            class="text-amber-600 hover:text-amber-900">
                                        <?= (int) $r['active'] === 1 ? 'Desativar' : 'Ativar' ?>
                                    </button>
                                    <?php if ((int) ($r['jury_count'] ?? 0) === 0): ?>
                                    <form method="POST" action="/master-data/rooms/<?= $r['id'] ?>/delete" class="inline" onsubmit="return confirm('Eliminar esta sala? Esta ação não pode ser desfeita.');">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <button type="submit" class="text-red-600 hover:text-red-900">Eliminar</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Modal: Criar Sala -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-create-room" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-lg mx-4 rounded-lg shadow-lg p-6" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Nova Sala</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form method="POST" action="/master-data/rooms" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="location_id" value="<?= $locationId ?>">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="room_code">Código *</label>
                    <input type="text" id="room_code" name="code" maxlength="20" 
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2 uppercase" 
                           placeholder="Ex: A101" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="room_capacity">Capacidade *</label>
                    <input type="number" id="room_capacity" name="capacity" min="1" 
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2" 
                           placeholder="Ex: 50" required>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700" for="room_name">Nome *</label>
                <input type="text" id="room_name" name="name" maxlength="60" 
                       class="mt-1 w-full rounded border border-gray-300 px-3 py-2" 
                       placeholder="Ex: Sala A - Bloco Principal" required>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="room_building">Edifício</label>
                    <input type="text" id="room_building" name="building" maxlength="60" 
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2" 
                           placeholder="Ex: Bloco A">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="room_floor">Piso</label>
                    <input type="text" id="room_floor" name="floor" maxlength="30" 
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2" 
                           placeholder="Ex: 1º Andar">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700" for="room_notes">Observações</label>
                <textarea id="room_notes" name="notes" rows="2" 
                          class="mt-1 w-full rounded border border-gray-300 px-3 py-2" 
                          placeholder="Observações adicionais (opcional)"></textarea>
            </div>
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Sala -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-edit-room" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-lg mx-4 rounded-lg shadow-lg p-6" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Editar Sala</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form method="POST" id="form-edit-room" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="location_id" id="edit_room_location_id" value="<?= $locationId ?>">
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="edit_room_code">Código *</label>
                    <input type="text" id="edit_room_code" name="code" maxlength="20" 
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2 uppercase" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="edit_room_capacity">Capacidade *</label>
                    <input type="number" id="edit_room_capacity" name="capacity" min="1" 
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700" for="edit_room_name">Nome *</label>
                <input type="text" id="edit_room_name" name="name" maxlength="60" 
                       class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="edit_room_building">Edifício</label>
                    <input type="text" id="edit_room_building" name="building" maxlength="60" 
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700" for="edit_room_floor">Piso</label>
                    <input type="text" id="edit_room_floor" name="floor" maxlength="30" 
                           class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700" for="edit_room_notes">Observações</label>
                <textarea id="edit_room_notes" name="notes" rows="2" 
                          class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></textarea>
            </div>
            
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-500">Atualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editRoom(room) {
    document.getElementById('edit_room_code').value = room.code;
    document.getElementById('edit_room_name').value = room.name;
    document.getElementById('edit_room_capacity').value = room.capacity;
    document.getElementById('edit_room_building').value = room.building || '';
    document.getElementById('edit_room_floor').value = room.floor || '';
    document.getElementById('edit_room_notes').value = room.notes || '';
    document.getElementById('edit_room_location_id').value = room.location_id;
    
    const form = document.getElementById('form-edit-room');
    form.action = `/master-data/rooms/${room.id}/update`;
    
    const modal = document.getElementById('modal-edit-room');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

async function toggleRoomStatus(id) {
    if (!confirm('Alterar status desta sala?')) return;
    
    try {
        const response = await fetch(`/master-data/rooms/${id}/toggle`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': '<?= csrf_token() ?>'
            },
            body: JSON.stringify({ csrf: '<?= csrf_token() ?>' })
        });
        
        const result = await response.json();
        
        if (result.success) {
            location.reload();
        } else {
            alert(result.message || 'Erro ao alterar status');
        }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao alterar status');
    }
}
</script>
