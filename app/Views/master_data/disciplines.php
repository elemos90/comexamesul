<?php
$title = 'Gestão de Disciplinas';
$breadcrumbs = [
    ['label' => 'Dados Mestres'],
    ['label' => 'Disciplinas']
];
?>

<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>
    
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Disciplinas</h1>
            <p class="text-gray-600 mt-1">Gerir disciplinas dos exames de admissão</p>
        </div>
        <button type="button" data-modal-target="modal-create-discipline" 
                class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nova Disciplina
        </button>
    </div>

    <!-- Tabela de Disciplinas -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Júris</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Criado por</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if (empty($disciplines)): ?>
                    <tr>
                        <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                            Nenhuma disciplina cadastrada. Clique em "Nova Disciplina" para começar.
                        </td>
                    </tr>
                    <?php else: ?>
                        <?php foreach ($disciplines as $d): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm font-mono font-semibold text-gray-900"><?= htmlspecialchars($d['code']) ?></span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($d['name']) ?></div>
                                <?php if (!empty($d['description'])): ?>
                                <div class="text-xs text-gray-500 mt-1"><?= htmlspecialchars($d['description']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    <?= (int) $d['jury_count'] ?> júris
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                <?= htmlspecialchars($d['created_by_name'] ?? 'Sistema') ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if ((int) $d['active'] === 1): ?>
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
                                <button type="button" onclick="editDiscipline(<?= $d['id'] ?>, '<?= htmlspecialchars($d['code'], ENT_QUOTES) ?>', '<?= htmlspecialchars($d['name'], ENT_QUOTES) ?>', '<?= htmlspecialchars($d['description'] ?? '', ENT_QUOTES) ?>')"
                                        class="text-blue-600 hover:text-blue-900">Editar</button>
                                <button type="button" onclick="toggleDisciplineStatus(<?= $d['id'] ?>)"
                                        class="text-amber-600 hover:text-amber-900">
                                    <?= (int) $d['active'] === 1 ? 'Desativar' : 'Ativar' ?>
                                </button>
                                <?php if ((int) $d['jury_count'] === 0): ?>
                                <form method="POST" action="/master-data/disciplines/<?= $d['id'] ?>/delete" class="inline" onsubmit="return confirm('Eliminar esta disciplina? Esta ação não pode ser desfeita.');">
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
</div>

<!-- Modal: Criar Disciplina -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-create-discipline" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-lg mx-4 rounded-lg shadow-lg p-6" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Nova Disciplina</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form method="POST" action="/master-data/disciplines" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700" for="discipline_code">Código *</label>
                <input type="text" id="discipline_code" name="code" maxlength="20" 
                       class="mt-1 w-full rounded border border-gray-300 px-3 py-2 uppercase" 
                       placeholder="Ex: MAT1, FIS1" required>
                <p class="mt-1 text-xs text-gray-500">Código único para identificação (será convertido em maiúsculas)</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="discipline_name">Nome *</label>
                <input type="text" id="discipline_name" name="name" maxlength="180" 
                       class="mt-1 w-full rounded border border-gray-300 px-3 py-2" 
                       placeholder="Ex: Matemática I" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="discipline_description">Descrição</label>
                <textarea id="discipline_description" name="description" rows="3" 
                          class="mt-1 w-full rounded border border-gray-300 px-3 py-2" 
                          placeholder="Descrição opcional da disciplina"></textarea>
            </div>
            <div class="flex justify-end gap-2 pt-4">
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500">Guardar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal: Editar Disciplina -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-edit-discipline" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-lg mx-4 rounded-lg shadow-lg p-6" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-gray-800">Editar Disciplina</h2>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700">&times;</button>
        </div>
        <form method="POST" id="form-edit-discipline" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700" for="edit_discipline_code">Código *</label>
                <input type="text" id="edit_discipline_code" name="code" maxlength="20" 
                       class="mt-1 w-full rounded border border-gray-300 px-3 py-2 uppercase" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="edit_discipline_name">Nome *</label>
                <input type="text" id="edit_discipline_name" name="name" maxlength="180" 
                       class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="edit_discipline_description">Descrição</label>
                <textarea id="edit_discipline_description" name="description" rows="3" 
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
function editDiscipline(id, code, name, description) {
    document.getElementById('edit_discipline_code').value = code;
    document.getElementById('edit_discipline_name').value = name;
    document.getElementById('edit_discipline_description').value = description || '';
    
    const form = document.getElementById('form-edit-discipline');
    form.action = `/master-data/disciplines/${id}/update`;
    
    const modal = document.getElementById('modal-edit-discipline');
    modal.classList.remove('hidden');
    modal.classList.add('flex');
}

async function toggleDisciplineStatus(id) {
    if (!confirm('Alterar status desta disciplina?')) return;
    
    try {
        const response = await fetch(`/master-data/disciplines/${id}/toggle`, {
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
