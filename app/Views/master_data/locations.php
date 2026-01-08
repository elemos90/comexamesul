<?php
$title = 'Cadastro de Locais';
$breadcrumbs = [
    ['label' => 'Dados Mestres'],
    ['label' => 'Cadastro de Locais']
];
?>

<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Cadastro de Locais</h1>
            <p class="text-gray-600 mt-1">Gerir cadastro de locais onde os exames serão realizados</p>
        </div>
        <button type="button" data-modal-target="modal-create-location"
            class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
            </svg>
            Novo Local
        </button>
    </div>

    <!-- Cards de Locais -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <?php foreach ($locations as $loc): ?>
            <div class="bg-white rounded-lg shadow-sm border overflow-hidden hover:shadow-md transition">
                <div class="p-5">
                    <div class="flex justify-between items-start mb-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-mono font-semibold text-gray-600 bg-gray-100 px-2 py-1 rounded">
                                    <?= htmlspecialchars($loc['code']) ?>
                                </span>
                                <?php if ((int) $loc['active'] === 1): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Ativo
                                    </span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inativo
                                    </span>
                                <?php endif; ?>
                            </div>
                            <h3 class="text-lg font-bold text-gray-900 mt-2"><?= htmlspecialchars($loc['name']) ?></h3>
                        </div>
                    </div>

                    <?php if (!empty($loc['address']) || !empty($loc['city'])): ?>
                        <div class="text-sm text-gray-600 mb-3 flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span>
                                <?= htmlspecialchars($loc['address'] ?? '') ?>
                                <?= !empty($loc['city']) ? ', ' . htmlspecialchars($loc['city']) : '' ?>
                            </span>
                        </div>
                    <?php endif; ?>

                    <div class="flex items-center gap-4 mb-4">
                        <div class="text-sm">
                            <span class="text-gray-500">Salas:</span>
                            <span class="font-semibold text-gray-900"><?= (int) $loc['room_count'] ?></span>
                            <span class="text-xs text-gray-500">(<?= (int) $loc['active_rooms'] ?> ativas)</span>
                        </div>
                        <div class="text-sm">
                            <span class="text-gray-500">Júris:</span>
                            <span class="font-semibold text-gray-900"><?= (int) $loc['jury_count'] ?></span>
                        </div>
                    </div>

                    <?php if (!empty($loc['capacity'])): ?>
                        <div class="text-xs text-gray-500 mb-4">
                            Capacidade total: <?= number_format((int) $loc['capacity']) ?> candidatos
                        </div>
                    <?php endif; ?>

                    <div class="flex items-center justify-between pt-3 border-t">
                        <a href="<?= url('/master-data/rooms?location=' . $loc['id']) ?>"
                            class="text-sm text-blue-600 hover:text-blue-800 font-medium">
                            Gerir Salas →
                        </a>
                        <div class="flex items-center gap-2">
                            <button type="button"
                                onclick="editLocation(<?= $loc['id'] ?>, <?= htmlspecialchars(json_encode($loc), ENT_QUOTES) ?>)"
                                class="text-sm text-blue-600 hover:text-blue-900">Editar</button>
                            <button type="button" onclick="toggleLocationStatus(<?= $loc['id'] ?>)"
                                class="text-sm text-amber-600 hover:text-amber-900">
                                <?= (int) $loc['active'] === 1 ? 'Desativar' : 'Ativar' ?>
                            </button>
                            <?php if ((int) $loc['jury_count'] === 0): ?>
                                <form method="POST" action="<?= url('/master-data/locations/' . $loc['id'] . '/delete') ?>"
                                    class="inline" onsubmit="return confirm('Eliminar este local e todas as suas salas?');">
                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                    <button type="submit" class="text-sm text-red-600 hover:text-red-900">Eliminar</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($locations)): ?>
            <div class="col-span-full bg-white rounded-lg shadow-sm border p-12 text-center">
                <p class="text-gray-500">Nenhum local cadastrado. Clique em "Novo Local" para começar.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Modal: Criar Local -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-create-location" role="dialog"
    aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-2xl mx-4 rounded-lg shadow-lg p-6 max-h-[90vh] overflow-y-auto"
        tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Novo Local de Realização</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form method="POST" action="<?= url('/master-data/locations') ?>" class="grid md:grid-cols-2 gap-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700" for="location_code">Código *</label>
                <input type="text" id="location_code" name="code" maxlength="20"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 uppercase" placeholder="Ex: CC, ES1"
                    required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="location_capacity">Capacidade Total</label>
                <input type="number" id="location_capacity" name="capacity" min="0"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="Total de candidatos">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="location_name">Nome *</label>
                <input type="text" id="location_name" name="name" maxlength="150"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="Ex: Campus Central"
                    required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="location_address">Endereço</label>
                <input type="text" id="location_address" name="address" maxlength="255"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="Rua, Av., etc.">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="location_city">Cidade</label>
                <input type="text" id="location_city" name="city" maxlength="100"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="Ex: Beira">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="location_description">Descrição</label>
                <textarea id="location_description" name="description" rows="3"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                    placeholder="Informações adicionais sobre o local"></textarea>
            </div>
            <div class="md:col-span-2 flex justify-end gap-2 pt-4">
                <button type="button"
                    class="modal-close px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</button>
                <button type="submit"
                    class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Local -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-edit-location" role="dialog"
    aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-2xl mx-4 rounded-lg shadow-lg p-6 max-h-[90vh] overflow-y-auto"
        tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Editar Local</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form method="POST" id="form-edit-location" class="grid md:grid-cols-2 gap-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700" for="edit_location_code">Código *</label>
                <input type="text" id="edit_location_code" name="code" maxlength="20"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 uppercase" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="edit_location_capacity">Capacidade
                    Total</label>
                <input type="number" id="edit_location_capacity" name="capacity" min="0"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="edit_location_name">Nome *</label>
                <input type="text" id="edit_location_name" name="name" maxlength="150"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="edit_location_address">Endereço</label>
                <input type="text" id="edit_location_address" name="address" maxlength="255"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="edit_location_city">Cidade</label>
                <input type="text" id="edit_location_city" name="city" maxlength="100"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="edit_location_description">Descrição</label>
                <textarea id="edit_location_description" name="description" rows="3"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2"></textarea>
            </div>
            <div class="md:col-span-2 flex justify-end gap-2 pt-4">
                <button type="button"
                    class="modal-close px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</button>
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded hover:bg-blue-500">Atualizar</button>
            </div>
        </form>
    </div>
</div>

<script>
    function editLocation(id, data) {
        document.getElementById('edit_location_code').value = data.code;
        document.getElementById('edit_location_name').value = data.name;
        document.getElementById('edit_location_address').value = data.address || '';
        document.getElementById('edit_location_city').value = data.city || '';
        document.getElementById('edit_location_capacity').value = data.capacity || '';
        document.getElementById('edit_location_description').value = data.description || '';

        const form = document.getElementById('form-edit-location');
        form.action = `/master-data/locations/${id}/update`;

        const modal = document.getElementById('modal-edit-location');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    async function toggleLocationStatus(id) {
        if (!confirm('Alterar status deste local?')) return;

        try {
            const response = await fetch(`/master-data/locations/${id}/toggle`, {
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