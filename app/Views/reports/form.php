<?php
$title = 'Relatório de exame';
$breadcrumbs = [
    ['label' => 'Júris', 'url' => '/juries'],
    ['label' => 'Relatório']
];
?>
<div class="max-w-2xl mx-auto space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>
    <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
        <h1 class="text-2xl font-semibold text-gray-800 mb-4">Submeter relatório</h1>
        <p class="text-sm text-gray-600 mb-6">Preencha os dados estatísticos do júri <strong><?= htmlspecialchars($jury['subject']) ?></strong> realizado em <?= htmlspecialchars(date('d/m/Y', strtotime($jury['exam_date']))) ?>.</p>
        <form method="POST" action="/juries/<?= $jury['id'] ?>/report" class="grid md:grid-cols-2 gap-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div>
                <label class="block text-sm font-medium text-gray-700" for="present_m">Presentes (Homens)</label>
                <input type="number" id="present_m" name="present_m" min="0" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="present_f">Presentes (Mulheres)</label>
                <input type="number" id="present_f" name="present_f" min="0" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="absent_m">Ausentes (Homens)</label>
                <input type="number" id="absent_m" name="absent_m" min="0" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700" for="absent_f">Ausentes (Mulheres)</label>
                <input type="number" id="absent_f" name="absent_f" min="0" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700" for="occurrences">Ocorrências</label>
                <textarea id="occurrences" name="occurrences" rows="4" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" placeholder="Registe incidentes e notas relevantes"></textarea>
            </div>
            <div class="md:col-span-2 flex justify-end gap-2">
                <a href="/juries/<?= $jury['id'] ?>" class="px-4 py-2 text-sm text-gray-600">Cancelar</a>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded">Submeter relatório</button>
            </div>
        </form>
    </div>
</div>
