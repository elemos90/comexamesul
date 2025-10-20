<?php
$title = 'Templates de Júris';
$breadcrumbs = [
    ['label' => 'Júris por Local', 'url' => '/locations'],
    ['label' => 'Templates']
];
$canManage = in_array($user['role'], ['coordenador', 'membro'], true);
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Templates de Júris</h1>
            <p class="text-sm text-gray-500">Salve configurações de júris por local para reutilizar rapidamente</p>
        </div>
        <?php if ($canManage): ?>
            <button type="button" data-modal-target="modal-create-template" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Novo Template
            </button>
        <?php endif; ?>
    </div>

    <?php if (empty($templates)): ?>
        <div class="bg-white border border-gray-200 rounded-lg p-12 text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="mt-4 text-sm text-gray-500">Nenhum template criado ainda. Crie o primeiro!</p>
        </div>
    <?php else: ?>
        <div class="grid lg:grid-cols-2 gap-6">
            <?php foreach ($templates as $template): ?>
                <div class="bg-white border-2 <?= (int)$template['is_active'] === 1 ? 'border-green-200' : 'border-gray-200' ?> rounded-lg shadow-sm overflow-hidden">
                    <!-- Header -->
                    <div class="px-5 py-4 bg-gradient-to-r <?= (int)$template['is_active'] === 1 ? 'from-green-50 to-emerald-50' : 'from-gray-50 to-gray-100' ?> border-b border-gray-200">
                        <div class="flex items-start justify-between">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-lg font-bold text-gray-800"><?= htmlspecialchars($template['name']) ?></h3>
                                    <?php if ((int)$template['is_active'] === 1): ?>
                                        <span class="px-2 py-0.5 bg-green-100 text-green-700 text-xs font-semibold rounded">Ativo</span>
                                    <?php else: ?>
                                        <span class="px-2 py-0.5 bg-gray-200 text-gray-600 text-xs font-semibold rounded">Inativo</span>
                                    <?php endif; ?>
                                </div>
                                <p class="text-sm text-gray-600 flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                    </svg>
                                    <?= htmlspecialchars($template['location']) ?>
                                </p>
                                <?php if (!empty($template['description'])): ?>
                                    <p class="text-xs text-gray-500 mt-2"><?= htmlspecialchars($template['description']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Stats -->
                    <div class="grid grid-cols-3 divide-x divide-gray-200 bg-gray-50">
                        <div class="px-4 py-3 text-center">
                            <p class="text-xl font-bold text-blue-600"><?= (int)$template['disciplines_count'] ?></p>
                            <p class="text-xs text-gray-600 mt-1">Disciplinas</p>
                        </div>
                        <div class="px-4 py-3 text-center">
                            <p class="text-xl font-bold text-green-600"><?= (int)$template['rooms_count'] ?></p>
                            <p class="text-xs text-gray-600 mt-1">Salas</p>
                        </div>
                        <div class="px-4 py-3 text-center">
                            <p class="text-xl font-bold text-purple-600"><?= number_format((int)$template['total_capacity']) ?></p>
                            <p class="text-xs text-gray-600 mt-1">Capacidade</p>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="px-5 py-3 bg-white border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-xs text-gray-500">
                                <p>Criado por: <span class="font-medium"><?= htmlspecialchars($template['creator_name'] ?? 'Sistema') ?></span></p>
                                <p>Em: <?= date('d/m/Y H:i', strtotime($template['created_at'])) ?></p>
                            </div>
                            <?php if ($canManage): ?>
                                <div class="flex items-center gap-2">
                                    <button type="button" class="btn-load-template px-3 py-1.5 text-xs font-medium bg-blue-100 text-blue-700 rounded hover:bg-blue-200" data-template-id="<?= $template['id'] ?>">
                                        Usar
                                    </button>
                                    <form method="POST" action="/locations/templates/<?= $template['id'] ?>/toggle" class="inline">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 rounded hover:bg-gray-200">
                                            <?= (int)$template['is_active'] === 1 ? 'Desativar' : 'Ativar' ?>
                                        </button>
                                    </form>
                                    <form method="POST" action="/locations/templates/<?= $template['id'] ?>/delete" onsubmit="return confirm('Eliminar este template?');" class="inline">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <button type="submit" class="px-3 py-1.5 text-xs font-medium bg-red-100 text-red-600 rounded hover:bg-red-200">
                                            Eliminar
                                        </button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php if ($canManage): ?>
<!-- Modal: Criar Template -->
<div class="modal fixed inset-0 hidden items-center justify-center z-50" id="modal-create-template" role="dialog" aria-hidden="true">
    <div class="modal-backdrop absolute inset-0 bg-gray-900/50"></div>
    <div class="modal-content relative bg-white w-full max-w-6xl mx-4 rounded-lg shadow-lg p-6 focus:outline-none max-h-[90vh] overflow-y-auto" tabindex="-1">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h2 class="text-lg font-semibold text-gray-800">Criar Template de Local</h2>
                <p class="text-sm text-gray-500 mt-1">Salve uma configuração de local para reutilizar</p>
            </div>
            <button type="button" class="modal-close text-gray-500 hover:text-gray-700 text-2xl">&times;</button>
        </div>
        
        <form method="POST" action="/locations/templates" id="form-create-template">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            
            <!-- Info do Template -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="text-sm font-semibold text-blue-900 mb-3 uppercase">Informações do Template</h3>
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="template_name">Nome do Template *</label>
                        <input type="text" id="template_name" name="name" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="Ex: Campus Central - Padrão" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="template_location">Local *</label>
                        <input type="text" id="template_location" name="location" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="Ex: Campus Central" required>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700" for="template_description">Descrição</label>
                        <textarea id="template_description" name="description" rows="2" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="Descrição opcional do template"></textarea>
                    </div>
                </div>
            </div>

            <!-- Disciplinas (reutiliza estrutura similar ao modal de criação) -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-semibold text-gray-800 uppercase">Disciplinas</h3>
                    <button type="button" id="btn-add-template-discipline" class="px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded hover:bg-green-500 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Adicionar
                    </button>
                </div>
                
                <div id="template-disciplines-container" class="space-y-4">
                    <!-- Disciplina inicial será adicionada via JS -->
                </div>
            </div>

            <div class="flex justify-end gap-2 pt-4 border-t">
                <button type="button" class="modal-close px-4 py-2 text-sm text-gray-600 hover:bg-gray-100 rounded">Cancelar</button>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500">Salvar Template</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>
