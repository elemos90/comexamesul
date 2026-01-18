<?php
$title = 'Nova Notificação - Destinatários';
$breadcrumbs = [
    ['label' => 'Notificações'],
    ['label' => 'Nova Notificação'],
    ['label' => 'Passo 2/5']
];
?>

<div class="max-w-3xl mx-auto">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <!-- Progress Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-primary-600">Passo 2 de 5</span>
            <span class="text-sm text-gray-500">Destinatários</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-primary-600 h-2 rounded-full" style="width: 40%"></div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Quem Deve Receber Esta Notificação?</h2>

        <form method="POST" action="<?= url('/notifications/wizard/step3') ?>" class="space-y-6">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

            <!-- Recipient Groups -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-3">Grupo de Destinatários *</label>
                <div class="space-y-3">
                    <label
                        class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 transition">
                        <input type="radio" name="recipient_group" value="all_vigilantes" required
                            class="h-4 w-4 text-primary-600">
                        <div class="ml-3 flex-1">
                            <span class="font-medium text-gray-900">Todos os Vigilantes</span>
                            <p class="text-sm text-gray-500">Todos os utilizadores com papel de vigilante ativo</p>
                        </div>
                    </label>

                    <?php if ($wizard['context_type'] === 'exam'): ?>
                        <label
                            class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 transition">
                            <input type="radio" name="recipient_group" value="exam_vigilantes" required
                                class="h-4 w-4 text-primary-600">
                            <div class="ml-3 flex-1">
                                <span class="font-medium text-gray-900">Vigilantes de Exame Específico</span>
                                <p class="text-sm text-gray-500">Apenas vigilantes atribuídos a júris do exame selecionado
                                </p>
                            </div>
                        </label>
                    <?php endif; ?>

                    <label
                        class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 transition">
                        <input type="radio" name="recipient_group" value="supervisors" required
                            class="h-4 w-4 text-primary-600">
                        <div class="ml-3 flex-1">
                            <span class="font-medium text-gray-900">Supervisores</span>
                            <p class="text-sm text-gray-500">Todos os supervisores ativos</p>
                        </div>
                    </label>

                    <label
                        class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 transition">
                        <input type="radio" name="recipient_group" value="committee_members" required
                            class="h-4 w-4 text-primary-600">
                        <div class="ml-3 flex-1">
                            <span class="font-medium text-gray-900">Membros da Comissão</span>
                            <p class="text-sm text-gray-500">Membros da comissão e coordenadores</p>
                        </div>
                    </label>

                    <label
                        class="flex items-center p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 transition"
                        onclick="toggleSpecificUsers()">
                        <input type="radio" name="recipient_group" value="specific" required
                            class="h-4 w-4 text-primary-600" id="specific_radio">
                        <div class="ml-3 flex-1">
                            <span class="font-medium text-gray-900">Utilizadores Específicos</span>
                            <p class="text-sm text-gray-500">Selecionar utilizadores individualmente</p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Specific Users Selection (hidden by default) -->
            <div id="specific_users_field" class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-2">Selecionar Utilizadores</label>
                <input type="text" id="user_search" placeholder="Pesquisar por nome ou email..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg mb-2 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <div class="border border-gray-300 rounded-lg max-h-64 overflow-y-auto p-2" id="user_list">
                    <p class="text-sm text-gray-500 p-4 text-center">Selecione "Utilizadores Específicos" para pesquisar
                    </p>
                </div>
            </div>

            <!-- Recipient Count -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                        </path>
                    </svg>
                    <span class="text-sm font-medium text-blue-900">
                        <span id="recipient_count">0</span> destinatários serão notificados
                    </span>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="<?= url('/notifications/create') ?>"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    ← Voltar
                </a>
                <button type="submit"
                    class="px-6 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                    Próximo: Conteúdo →
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function toggleSpecificUsers() {
        const field = document.getElementById('specific_users_field');
        const radio = document.getElementById('specific_radio');
        if (radio.checked) {
            field.classList.remove('hidden');
            loadUsers();
        }
    }

    // Hide specific users when other options selected
    document.querySelectorAll('input[name="recipient_group"]').forEach(radio => {
        radio.addEventListener('change', function () {
            const field = document.getElementById('specific_users_field');
            if (this.value !== 'specific') {
                field.classList.add('hidden');
            }
        });
    });

    function loadUsers() {
        // TODO: Load users via AJAX for selection
        const list = document.getElementById('user_list');
        list.innerHTML = '<p class="text-sm text-gray-500 p-4">Carregando utilizadores...</p>';
    }
</script>