<?php
$title = 'Importar Júris';
$breadcrumbs = [
    ['label' => 'Júris por Local', 'url' => url('/locations')],
    ['label' => 'Importar']
];
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Importar Júris via Planilha</h1>
            <p class="text-sm text-gray-500">Carregue um arquivo Excel ou CSV para criar múltiplos júris</p>
        </div>
        <a href="url('/locations/export/template')"
            class="px-4 py-2 bg-green-600 text-white text-sm font-medium rounded hover:bg-green-500 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Baixar Template
        </a>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Upload Form -->
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">📤 Upload de Arquivo</h2>

            <form method="POST" action="<?= url('/locations/import') ?>" enctype="multipart/form-data"
                class="space-y-4">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Selecione o arquivo</label>
                    <div class="flex items-center justify-center w-full">
                        <label for="file-upload"
                            class="flex flex-col items-center justify-center w-full h-48 border-2 border-gray-300 border-dashed rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <svg class="w-12 h-12 mb-3 text-gray-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                                <p class="mb-2 text-sm text-gray-500"><span class="font-semibold">Clique para
                                        carregar</span> ou arraste</p>
                                <p class="text-xs text-gray-500">XLSX, XLS ou CSV</p>
                            </div>
                            <input id="file-upload" name="file" type="file" class="hidden" accept=".xlsx,.xls,.csv"
                                required onchange="displayFileName(this)" />
                        </label>
                    </div>
                    <p id="file-name" class="mt-2 text-sm text-gray-600 italic"></p>
                </div>

                <div class="pt-4 border-t">
                    <button type="submit"
                        class="w-full px-4 py-3 bg-primary-600 text-white font-medium rounded hover:bg-primary-500 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                        </svg>
                        Importar Júris
                    </button>
                </div>
            </form>
        </div>

        <!-- Instruções -->
        <div class="space-y-6">
            <!-- Formato -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">📋 Formato da Planilha</h2>

                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-900 font-medium mb-2">Estrutura das Colunas:</p>
                    <ol class="text-sm text-blue-800 space-y-1 list-decimal list-inside">
                        <li><strong>Local</strong> - Nome do local (ex: Campus Central)</li>
                        <li><strong>Data</strong> - Formato: dd/mm/yyyy (ex: 15/11/2025)</li>
                        <li><strong>Disciplina</strong> - Nome da disciplina (ex: Matemática I)</li>
                        <li><strong>Início</strong> - Horário início (ex: 08:00)</li>
                        <li><strong>Fim</strong> - Horário fim (ex: 11:00)</li>
                        <li><strong>Sala</strong> - Número/nome da sala (ex: 101)</li>
                        <li><strong>Candidatos</strong> - Número de candidatos (ex: 30)</li>
                    </ol>
                </div>

                <div class="bg-gray-50 border border-gray-200 rounded p-3">
                    <p class="text-xs text-gray-600 font-mono">
                        Campus Central | 15/11/2025 | Matemática I | 08:00 | 11:00 | 101 | 30
                    </p>
                </div>
            </div>

            <!-- Tips -->
            <div class="bg-white border border-gray-200 rounded-lg shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">💡 Dicas</h2>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Baixe o template para garantir o formato correto</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>A primeira linha deve conter os cabeçalhos</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Use formato de 24 horas para horários (08:00, 14:00)</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Múltiplas salas da mesma disciplina = múltiplas linhas</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="currentColor"
                            viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                clip-rule="evenodd" />
                        </svg>
                        <span>Linhas com erros serão ignoradas (relatório exibido)</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Erros de Importação -->
    <?php if (!empty($_SESSION['import_errors'])): ?>
        <div class="bg-red-50 border border-red-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-red-900 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                        clip-rule="evenodd" />
                </svg>
                Erros Encontrados
            </h3>
            <div class="max-h-64 overflow-y-auto">
                <ul class="space-y-1 text-sm text-red-800">
                    <?php foreach ($_SESSION['import_errors'] as $error): ?>
                        <li class="font-mono"><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php unset($_SESSION['import_errors']); ?>
    <?php endif; ?>
</div>

<script>
    function displayFileName(input) {
        const fileNameDisplay = document.getElementById('file-name');
        if (input.files && input.files[0]) {
            fileNameDisplay.textContent = '📎 ' + input.files[0].name;
        }
    }
</script>