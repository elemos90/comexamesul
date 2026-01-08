<?php
// reports/stats_dashboard.php
?>
<div class="max-w-4xl mx-auto space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
        <div class="border-b pb-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Relatórios Estatísticos</h1>
            <p class="text-sm text-gray-600 mt-1">Gere relatórios institucionais de presenças, ausências e fraudes.</p>
        </div>

        <form action="<?= url('/stats/generate') ?>" method="GET" target="_blank" class="space-y-6">
            <div class="grid md:grid-cols-2 gap-6">
                <!-- Filtros -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Edição / Vaga</label>
                    <select name="vacancy_id"
                        class="w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todas as Vagas</option>
                        <?php foreach ($vacancies as $v): ?>
                            <option value="<?= $v['id'] ?>">
                                <?= htmlspecialchars($v['title']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Local de Exame</label>
                    <select name="location_id"
                        class="w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todos os Locais</option>
                        <?php foreach ($locations as $l): ?>
                            <option value="<?= $l['id'] ?>">
                                <?= htmlspecialchars($l['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Disciplina</label>
                    <select name="discipline_id"
                        class="w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">Todas as Disciplinas</option>
                        <?php foreach ($disciplines as $d): ?>
                            <option value="<?= $d['id'] ?>">
                                <?= htmlspecialchars($d['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Data do Exame</label>
                    <input type="date" name="exam_date"
                        class="w-full rounded border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>
            </div>

            <div class="pt-6 border-t border-gray-100">
                <label class="block text-sm font-medium text-gray-700 mb-3">Formato de Saída</label>
                <div class="flex gap-4">
                    <label
                        class="flex items-center gap-2 cursor-pointer p-3 border rounded-lg hover:bg-gray-50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                        <input type="radio" name="format" value="pdf" checked
                            class="text-indigo-600 focus:ring-indigo-500">
                        <span class="font-medium text-gray-700">PDF Institucional</span>
                    </label>
                    <label
                        class="flex items-center gap-2 cursor-pointer p-3 border rounded-lg hover:bg-gray-50 has-[:checked]:border-indigo-500 has-[:checked]:bg-indigo-50">
                        <input type="radio" name="format" value="excel" class="text-indigo-600 focus:ring-indigo-500">
                        <span class="font-medium text-gray-700">Excel (Analítico)</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white font-medium rounded-lg hover:bg-indigo-700 shadow-sm flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Gerar Relatório
                </button>
            </div>
        </form>
    </div>
</div>