<?php
$title = 'Nova Notificação - Tipo e Contexto';
$breadcrumbs = [
    ['label' => 'Notificações'],
    ['label' => 'Nova Notificação'],
    ['label' => 'Passo 1/5']
];
?>

<div class="max-w-3xl mx-auto">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <!-- Progress Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-primary-600">Passo 1 de 5</span>
            <span class="text-sm text-gray-500">Tipo e Contexto</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-primary-600 h-2 rounded-full" style="width: 20%"></div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Tipo e Contexto da Notificação</h2>

        <form method="POST" action="<?= url('/notifications/wizard/step2') ?>" class="space-y-6">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

            <!-- Notification Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Tipo de Notificação *</label>
                <div class="space-y-3">
                    <label
                        class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-blue-500 transition">
                        <input type="radio" name="type" value="informativa" required class="h-4 w-4 text-blue-600">
                        <div class="ml-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-3 h-3 bg-blue-500 rounded-full"></span>
                                <span class="font-medium text-gray-900">Informativa</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Comunicações gerais e informações não urgentes</p>
                        </div>
                    </label>

                    <label
                        class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-orange-500 transition">
                        <input type="radio" name="type" value="alerta" required class="h-4 w-4 text-orange-600">
                        <div class="ml-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-3 h-3 bg-orange-500 rounded-full"></span>
                                <span class="font-medium text-gray-900">Alerta</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Avisos importantes que requerem atenção</p>
                        </div>
                    </label>

                    <label
                        class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-red-500 transition">
                        <input type="radio" name="type" value="urgente" required class="h-4 w-4 text-red-600">
                        <div class="ml-3">
                            <div class="flex items-center gap-2">
                                <span class="inline-block w-3 h-3 bg-red-500 rounded-full"></span>
                                <span class="font-medium text-gray-900">Urgente</span>
                            </div>
                            <p class="text-sm text-gray-500 mt-1">Comunicações críticas que requerem ação imediata</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Context -->
            <div>
                <label for="context_type" class="block text-sm font-medium text-gray-700 mb-2">Contexto *</label>
                <select id="context_type" name="context_type" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Selecione o contexto...</option>
                    <option value="general">Geral (sem contexto específico)</option>
                    <option value="exam">Exame Específico</option>
                    <option value="jury">Júri Específico</option>
                    <option value="payment">Pagamentos</option>
                    <option value="report">Relatórios</option>
                    <option value="user">Utilizadores</option>
                </select>
                <p class="mt-1 text-xs text-gray-500">O contexto ajuda a organizar e vincular a notificação ao recurso
                    relacionado</p>
            </div>

            <!-- Context ID (conditional) -->
            <div id="context_id_field" class="hidden">
                <label for="context_id" class="block text-sm font-medium text-gray-700 mb-2">ID do Recurso</label>
                <input type="number" id="context_id" name="context_id"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Ex: ID do júri, exame, etc">
                <p class="mt-1 text-xs text-gray-500">Opcional: ID do recurso específico (júri, exame, etc)</p>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="<?= url('/notifications/history') ?>"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    Cancelar
                </a>
                <button type="submit"
                    class="px-6 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                    Próximo: Destinatários →
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Show/hide context ID field based on selection
    document.getElementById('context_type').addEventListener('change', function () {
        const contextIdField = document.getElementById('context_id_field');
        if (this.value && this.value !== 'general') {
            contextIdField.classList.remove('hidden');
        } else {
            contextIdField.classList.add('hidden');
            document.getElementById('context_id').value = '';
        }
    });
</script>