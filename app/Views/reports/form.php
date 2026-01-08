<?php
$title = 'Relatório de exame';
$breadcrumbs = [
    ['label' => 'Júris', 'url' => url('/juries')],
    ['label' => 'Relatório']
];
?>
<div class="max-w-3xl mx-auto space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-3">
        <svg class="w-6 h-6 text-blue-600 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <h3 class="font-semibold text-blue-800">Informação Importante</h3>
            <p class="text-blue-700 text-sm">Os dados submetidos são estatísticos e não identificam candidatos
                individualmente.</p>
        </div>
    </div>

    <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
        <div class="border-b pb-4 mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Submeter Relatório do Júri</h1>
            <p class="text-sm text-gray-600 mt-1">
                Júri: <strong><?= htmlspecialchars($jury['subject']) ?></strong> |
                Data: <?= htmlspecialchars(date('d/m/Y', strtotime($jury['exam_date']))) ?> |
                Sala: <?= htmlspecialchars($jury['room'] ?? 'N/A') ?>
            </p>
        </div>

        <form method="POST" action="<?= url('/juries/' . $jury['id'] . '/report') ?>" id="reportForm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <!-- Presentes -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center gap-2">
                    <span class="bg-green-100 text-green-800 text-xs font-bold px-2 py-1 rounded">1</span>
                    Presentes
                </h3>
                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="present_m">Masculino</label>
                        <input type="number" id="present_m" name="present_m" min="0" value="0"
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:ring-green-500 focus:border-green-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="present_f">Feminino</label>
                        <input type="number" id="present_f" name="present_f" min="0" value="0"
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:ring-green-500 focus:border-green-500"
                            required>
                    </div>
                    <div class="col-span-2 text-right text-sm font-medium text-gray-600 pt-2 border-t border-gray-200">
                        Total Presentes: <span id="total_present" class="text-green-700 text-lg">0</span>
                    </div>
                </div>
            </div>

            <!-- Ausentes -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center gap-2">
                    <span class="bg-red-100 text-red-800 text-xs font-bold px-2 py-1 rounded">2</span>
                    Ausentes
                </h3>
                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="absent_m">Masculino</label>
                        <input type="number" id="absent_m" name="absent_m" min="0" value="0"
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:ring-red-500 focus:border-red-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="absent_f">Feminino</label>
                        <input type="number" id="absent_f" name="absent_f" min="0" value="0"
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:ring-red-500 focus:border-red-500"
                            required>
                    </div>
                    <div class="col-span-2 text-right text-sm font-medium text-gray-600 pt-2 border-t border-gray-200">
                        Total Ausentes: <span id="total_absent" class="text-red-700 text-lg">0</span>
                    </div>
                </div>
            </div>

            <!-- Fraudes -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center gap-2">
                    <span class="bg-yellow-100 text-yellow-800 text-xs font-bold px-2 py-1 rounded">3</span>
                    Fraudes
                </h3>
                <div class="grid grid-cols-2 gap-4 bg-gray-50 p-4 rounded-lg">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="fraudes_m">Masculino</label>
                        <input type="number" id="fraudes_m" name="fraudes_m" min="0" value="0"
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:ring-yellow-500 focus:border-yellow-500"
                            required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1" for="fraudes_f">Feminino</label>
                        <input type="number" id="fraudes_f" name="fraudes_f" min="0" value="0"
                            class="w-full rounded border border-gray-300 px-3 py-2 focus:ring-yellow-500 focus:border-yellow-500"
                            required>
                    </div>
                    <div class="col-span-2 text-right text-sm font-medium text-gray-600 pt-2 border-t border-gray-200">
                        Total Fraudes: <span id="total_fraud" class="text-yellow-700 text-lg">0</span>
                        <p class="text-xs text-gray-500 mt-1">(As fraudes já estão incluídas nos presentes)</p>
                    </div>
                </div>
            </div>

            <!-- Total Geral -->
            <div class="bg-gray-100 p-4 rounded-lg mb-6 text-center">
                <span class="text-gray-600 font-medium uppercase tracking-wider text-sm">Total Geral de
                    Candidatos</span>
                <div class="text-3xl font-bold text-gray-900 mt-1" id="grand_total">0</div>
            </div>

            <!-- Observações -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2" for="occurrences">Observações
                    (Opcional)</label>
                <textarea id="occurrences" name="occurrences" rows="3"
                    class="w-full rounded border border-gray-300 px-3 py-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="Registe incidentes e notas relevantes..."></textarea>
            </div>

            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="<?= url('/juries/' . $jury['id']) ?>"
                    class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 rounded-lg border border-gray-300">Cancelar</a>
                <button type="submit"
                    class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 shadow-sm">
                    Submeter Relatório
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = ['present_m', 'present_f', 'absent_m', 'absent_f', 'fraudes_m', 'fraudes_f'];

        function updateTotals() {
            const vals = {};
            inputs.forEach(id => {
                vals[id] = parseInt(document.getElementById(id).value) || 0;
            });

            const totalPresent = vals.present_m + vals.present_f;
            const totalAbsent = vals.absent_m + vals.absent_f;
            const totalFraud = vals.fraudes_m + vals.fraudes_f;
            const grandTotal = totalPresent + totalAbsent;

            document.getElementById('total_present').textContent = totalPresent;
            document.getElementById('total_absent').textContent = totalAbsent;
            document.getElementById('total_fraud').textContent = totalFraud;
            document.getElementById('grand_total').textContent = grandTotal;

            // Validation warnings
            if (vals.fraudes_m > vals.present_m) {
                document.getElementById('fraudes_m').classList.add('border-red-500');
            } else {
                document.getElementById('fraudes_m').classList.remove('border-red-500');
            }

            if (vals.fraudes_f > vals.present_f) {
                document.getElementById('fraudes_f').classList.add('border-red-500');
            } else {
                document.getElementById('fraudes_f').classList.remove('border-red-500');
            }
        }

        inputs.forEach(id => {
            document.getElementById(id).addEventListener('input', updateTotals);
        });
    });
</script>