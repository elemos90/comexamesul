<?php
$title = 'Nova Notificação - Canais';
$breadcrumbs = [
    ['label' => 'Notificações'],
    ['label' => 'Nova Notificação'],
    ['label' => 'Passo 4/5']
];
?>

<div class="max-w-3xl mx-auto">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <!-- Progress Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-primary-600">Passo 4 de 5</span>
            <span class="text-sm text-gray-500">Canais de Envio</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-primary-600 h-2 rounded-full" style="width: 80%"></div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Como Enviar Esta Notificação?</h2>

        <form method="POST" action="<?= url('/notifications/wizard/step5') ?>" class="space-y-6">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

            <!-- Channels -->
            <div class="space-y-4">
                <!-- Internal (always checked, disabled) -->
                <label class="flex items-start p-4 border-2 border-green-200 bg-green-50 rounded-lg">
                    <input type="checkbox" name="internal" checked disabled class="h-5 w-5 text-green-600 mt-0.5">
                    <div class="ml-3 flex-1">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z">
                                </path>
                            </svg>
                            <span class="font-medium text-gray-900">Notificação Interna</span>
                            <span class="text-xs bg-green-600 text-white px-2 py-0.5 rounded-full">Obrigatório</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            Aparece no painel de notificações do utilizador no sistema
                        </p>
                        <p class=" text-xs text-green-700 mt-2 font-medium">
                            ✓
                            <?= $wizard['recipient_count'] ?? 0 ?> utilizadores serão notificados internamente
                        </p>
                    </div>
                </label>

                <!-- Email -->
                <label
                    class="flex items-start p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-primary-500 transition">
                    <input type="checkbox" id="email_checkbox" name="email" class="h-5 w-5 text-primary-600 mt-0.5">
                    <div class="ml-3 flex-1">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z">
                                </path>
                            </svg>
                            <span class="font-medium text-gray-900">Email</span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1">
                            Envia email para todos os destinatários com endereço de email registado
                        </p>
                        <p class="text-xs text-gray-500 mt-2" id="email_count">
                            ℹ️ Marque para enviar emails
                        </p>
                    </div>
                </label>

                <!-- SMS (disabled - not configured) -->
                <label class="flex items-start p-4 border-2 border-gray-200 bg-gray-50 rounded-lg opacity-60">
                    <input type="checkbox" name="sms" disabled class="h-5 w-5 text-gray-400 mt-0.5">
                    <div class="ml-3 flex-1">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z">
                                </path>
                            </svg>
                            <span class="font-medium text-gray-500">SMS</span>
                            <span class="text-xs bg-gray-400 text-white px-2 py-0.5 rounded-full">Não Configurado</span>
                        </div>
                        <p class="text-sm text-gray-500 mt-1">
                            Funcionalidade futura - provedor SMS ainda não configurado
                        </p>
                    </div>
                </label>
            </div>

            <!-- Warning -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h4 class="text-sm font-medium text-yellow-900">Importante</h4>
                        <p class="text-sm text-yellow-800 mt-1">
                            O envio de emails pode demorar alguns minutos dependendo do número de destinatários.
                            Certifique-se de que o conteúdo está correto antes de prosseguir.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="<?= url('/notifications/create/step3') ?>"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    ← Voltar
                </a>
                <button type="submit"
                    class="px-6 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                    Próximo: Confirmação →
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Update email count when checkbox changes
    document.getElementById('email_checkbox').addEventListener('change', function () {
        const countEl = document.getElementById('email_count');
        if (this.checked) {
            countEl.innerHTML = '✉️ <?= $wizard['recipient_count'] ?? 0 ?> emails serão enviados';
            countEl.classList.add('text-primary-600', 'font-medium');
            countEl.classList.remove('text-gray-500');
        } else {
            countEl.innerHTML = 'ℹ️ Marque para enviar emails';
            countEl.classList.remove('text-primary-600', 'font-medium');
            countEl.classList.add('text-gray-500');
        }
    });
</script>