<?php
$title = 'Nova Notificação - Conteúdo';
$breadcrumbs = [
    ['label' => 'Notificações'],
    ['label' => 'Nova Notificação'],
    ['label' => 'Passo 3/5']
];
?>

<div class="max-w-3xl mx-auto">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <!-- Progress Bar -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-primary-600">Passo 3 de 5</span>
            <span class="text-sm text-gray-500">Conteúdo</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="bg-primary-600 h-2 rounded-full" style="width: 60%"></div>
        </div>
    </div>

    <!-- Form -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-6">Conteúdo da Notificação</h2>

        <form method="POST" action="<?= url('/notifications/wizard/step4') ?>" class="space-y-6">
            <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

            <!-- Subject -->
            <div>
                <label for="subject" class="block text-sm font-medium text-gray-700 mb-2">
                    Assunto *
                </label>
                <input type="text" id="subject" name="subject" required maxlength="255"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                    placeholder="Ex: Alteração de Horário do Exame de Matemática">
                <div class="flex items-center justify-between mt-1">
                    <p class="text-xs text-gray-500">Seja claro e direto</p>
                    <p class="text-xs text-gray-400"><span id="subject_count">0</span>/255</p>
                </div>
            </div>

            <!-- Message -->
            <div>
                <label for="message" class="block text-sm font-medium text-gray-700 mb-2">
                    Mensagem *
                </label>
                <textarea id="message" name="message" required rows="8"
                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none"
                    placeholder="Escreva a mensagem aqui...&#10;&#10;Mantenha a linguagem institucional e profissional."></textarea>
                <div class="flex items-center justify-between mt-1">
                    <p class="text-xs text-gray-500">Máximo recomendado: 1000 caracteres</p>
                    <p class="text-xs" id="message_count_text"><span id="message_count">0</span> caracteres</p>
                </div>
            </div>

            <!-- Preview Card -->
            <div class="border-t pt-6">
                <h3 class="text-sm font-medium text-gray-700 mb-3">Pré-visualização</h3>
                <div class="border border-gray-200 rounded-lg p-4 bg-gray-50">
                    <div class="flex items-start gap-3">
                        <div class="flex-shrink-0">
                            <div class="w-10 h-10 rounded-full bg-primary-100 flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path
                                        d="M10 2a6 6 0 00-6 6v3.586l-.707.707A1 1 0 004 14h12a1 1 0 00.707-1.707L16 11.586V8a6 6 0 00-6-6zM10 18a3 3 0 01-3-3h6a3 3 0 01-3 3z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-xs text-gray-500 mb-1">Agora</p>
                            <h4 class="font-medium text-gray-900 mb-1" id="preview_subject">
                                <span class="text-gray-400">Sem assunto</span>
                            </h4>
                            <p class="text-sm text-gray-600 whitespace-pre-wrap" id="preview_message">
                                <span class="text-gray-400">Sem mensagem</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Guidelines -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <h4 class="text-sm font-medium text-blue-900 mb-2">💡 Diretrizes de Conteúdo</h4>
                <ul class="text-sm text-blue-800 space-y-1">
                    <li>• Use linguagem institucional e profissional</li>
                    <li>• Seja claro e objetivo</li>
                    <li>• Evite abreviações ou gírias</li>
                    <li>• Inclua informações relevantes (datas, locais, contactos)</li>
                </ul>
            </div>

            <!-- Actions -->
            <div class="flex items-center justify-between pt-4 border-t">
                <a href="<?= url('/notifications/create/step2') ?>"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                    ← Voltar
                </a>
                <button type="submit"
                    class="px-6 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">
                    Próximo: Canais →
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Character counters
    document.getElementById('subject').addEventListener('input', function () {
        const count = this.value.length;
        document.getElementById('subject_count').textContent = count;
        document.getElementById('preview_subject').textContent = this.value || 'Sem assunto';
        document.getElementById('preview_subject').classList.toggle('text-gray-400', !this.value);
    });

    document.getElementById('message').addEventListener('input', function () {
        const count = this.value.length;
        const countEl = document.getElementById('message_count');
        const countText = document.getElementById('message_count_text');

        countEl.textContent = count;

        if (count > 1000) {
            countText.classList.add('text-orange-600');
            countText.classList.remove('text-gray-400');
        } else {
            countText.classList.remove('text-orange-600');
            countText.classList.add('text-gray-400');
        }

        const preview = document.getElementById('preview_message');
        preview.textContent = this.value || 'Sem mensagem';
        preview.classList.toggle('text-gray-400', !this.value);
    });
</script>