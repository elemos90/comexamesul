<?php
$title = 'Relatório de exame';
$breadcrumbs = [
    ['label' => 'Júris', 'url' => url('/juries')],
    ['label' => 'Relatório']
];
$totalEsperado = $jury['candidates_quota'] ?? 0;
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
            <p class="text-sm text-gray-600 mt-1">
                <span class="inline-flex items-center bg-indigo-100 text-indigo-800 px-2 py-0.5 rounded font-medium">
                    📋 Total esperado de candidatos: <?= $totalEsperado ?>
                </span>
            </p>
        </div>

        <form method="POST" action="<?= url('/juries/' . $jury['id'] . '/report') ?>" id="reportForm">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" id="total_esperado" value="<?= $totalEsperado ?>">

            <!-- SECÇÃO B: Tabela de Registo -->
            <div class="mb-6">
                <h3 class="text-lg font-medium text-gray-900 mb-3 flex items-center gap-2">
                    <span class="bg-gray-100 text-gray-800 text-xs font-bold px-2 py-1 rounded">B</span>
                    Registo de Presenças, Ausências e Fraudes
                </h3>

                <div class="overflow-hidden rounded-lg border border-gray-200">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-100 text-gray-700 text-sm font-medium">
                                <th class="text-left px-4 py-3 w-1/4">Estado</th>
                                <th class="text-center px-4 py-3 w-1/4">Masculino</th>
                                <th class="text-center px-4 py-3 w-1/4">Feminino</th>
                                <th class="text-center px-4 py-3 w-1/4 bg-gray-200">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <!-- Presentes -->
                            <tr class="bg-green-50">
                                <td class="px-4 py-3 font-medium text-green-800">
                                    <span class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full bg-green-500"></span>
                                        Presentes
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" id="present_m" name="present_m" min="0" value="0"
                                        class="w-full text-center rounded border border-gray-300 px-3 py-2 focus:ring-green-500 focus:border-green-500"
                                        required>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" id="present_f" name="present_f" min="0" value="0"
                                        class="w-full text-center rounded border border-gray-300 px-3 py-2 focus:ring-green-500 focus:border-green-500"
                                        required>
                                </td>
                                <td class="px-4 py-3 text-center bg-green-100 font-bold text-green-800 text-lg"
                                    id="total_present">0</td>
                            </tr>
                            <!-- Ausentes -->
                            <tr class="bg-red-50">
                                <td class="px-4 py-3 font-medium text-red-800">
                                    <span class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full bg-red-500"></span>
                                        Ausentes
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" id="absent_m" name="absent_m" min="0" value="0"
                                        class="w-full text-center rounded border border-gray-300 px-3 py-2 focus:ring-red-500 focus:border-red-500"
                                        required>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" id="absent_f" name="absent_f" min="0" value="0"
                                        class="w-full text-center rounded border border-gray-300 px-3 py-2 focus:ring-red-500 focus:border-red-500"
                                        required>
                                </td>
                                <td class="px-4 py-3 text-center bg-red-100 font-bold text-red-800 text-lg"
                                    id="total_absent">0</td>
                            </tr>
                            <!-- Fraudes -->
                            <tr class="bg-yellow-50">
                                <td class="px-4 py-3 font-medium text-yellow-800">
                                    <span class="flex items-center gap-2">
                                        <span class="w-3 h-3 rounded-full bg-yellow-500"></span>
                                        Fraudes
                                    </span>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" id="fraudes_m" name="fraudes_m" min="0" value="0"
                                        class="w-full text-center rounded border border-gray-300 px-3 py-2 focus:ring-yellow-500 focus:border-yellow-500"
                                        required>
                                </td>
                                <td class="px-4 py-2">
                                    <input type="number" id="fraudes_f" name="fraudes_f" min="0" value="0"
                                        class="w-full text-center rounded border border-gray-300 px-3 py-2 focus:ring-yellow-500 focus:border-yellow-500"
                                        required>
                                </td>
                                <td class="px-4 py-3 text-center bg-yellow-100 font-bold text-yellow-800 text-lg"
                                    id="total_fraud">0</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p class="text-xs text-gray-500 mt-2 italic">
                    ℹ️ Fraudes não alteram o número total esperado de candidatos. São registadas como ocorrências.
                </p>
            </div>

            <!-- RESUMO DO JÚRI (Bloco Informativo) -->
            <div class="mb-6 p-4 rounded-lg border-2" id="resumo-bloco">
                <h4 class="text-sm font-bold text-gray-700 mb-3 uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Resumo do Júri
                </h4>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="bg-gray-50 p-3 rounded">
                        <div class="text-xs text-gray-500 uppercase">Esperados</div>
                        <div class="text-2xl font-bold text-gray-800"><?= $totalEsperado ?></div>
                    </div>
                    <div class="bg-green-50 p-3 rounded">
                        <div class="text-xs text-green-600 uppercase">Presentes</div>
                        <div class="text-2xl font-bold text-green-700" id="resumo_presentes">0</div>
                    </div>
                    <div class="bg-red-50 p-3 rounded">
                        <div class="text-xs text-red-600 uppercase">Ausentes</div>
                        <div class="text-2xl font-bold text-red-700" id="resumo_ausentes">0</div>
                    </div>
                    <div class="bg-yellow-50 p-3 rounded">
                        <div class="text-xs text-yellow-600 uppercase">Fraudes</div>
                        <div class="text-2xl font-bold text-yellow-700" id="resumo_fraudes">0</div>
                    </div>
                </div>

                <!-- Validação Visual -->
                <div class="mt-4 p-3 rounded-lg text-center" id="validation-status">
                    <!-- Populated by JavaScript -->
                </div>
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
                <button type="submit" id="btn-submit"
                    class="px-6 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 shadow-sm disabled:bg-gray-400 disabled:cursor-not-allowed">
                    Submeter Relatório
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const inputs = ['present_m', 'present_f', 'absent_m', 'absent_f', 'fraudes_m', 'fraudes_f'];
        const totalEsperado = parseInt(document.getElementById('total_esperado').value) || 0;
        const submitBtn = document.getElementById('btn-submit');
        const resumoBloco = document.getElementById('resumo-bloco');
        const validationStatus = document.getElementById('validation-status');

        function updateTotals() {
            const vals = {};
            inputs.forEach(id => {
                vals[id] = parseInt(document.getElementById(id).value) || 0;
            });

            const totalPresent = vals.present_m + vals.present_f;
            const totalAbsent = vals.absent_m + vals.absent_f;
            const totalFraud = vals.fraudes_m + vals.fraudes_f;
            const grandTotal = totalPresent + totalAbsent;

            // Update table totals
            document.getElementById('total_present').textContent = totalPresent;
            document.getElementById('total_absent').textContent = totalAbsent;
            document.getElementById('total_fraud').textContent = totalFraud;

            // Update summary block
            document.getElementById('resumo_presentes').textContent = totalPresent;
            document.getElementById('resumo_ausentes').textContent = totalAbsent;
            document.getElementById('resumo_fraudes').textContent = totalFraud;

            // Validation: Presentes + Ausentes = Total Esperado
            const isValid = (grandTotal === totalEsperado);

            // Validation: Fraudes cannot exceed Presentes by gender
            const fraudValid = (vals.fraudes_m <= vals.present_m) && (vals.fraudes_f <= vals.present_f);

            // Update validation status
            if (isValid && fraudValid) {
                resumoBloco.classList.remove('border-red-300', 'bg-red-50');
                resumoBloco.classList.add('border-green-300', 'bg-green-50');
                validationStatus.className = 'mt-4 p-3 rounded-lg text-center bg-green-100 text-green-800';
                validationStatus.innerHTML = `
                    <span class="flex items-center justify-center gap-2 font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        ✓ Totais consistentes (${grandTotal} de ${totalEsperado})
                    </span>
                `;
                submitBtn.disabled = false;
            } else {
                resumoBloco.classList.remove('border-green-300', 'bg-green-50');
                resumoBloco.classList.add('border-red-300', 'bg-red-50');

                let errorMsg = '';
                if (!isValid) {
                    errorMsg = `⚠️ Inconsistência: Presentes (${totalPresent}) + Ausentes (${totalAbsent}) = ${grandTotal} ≠ ${totalEsperado} esperados`;
                }
                if (!fraudValid) {
                    if (errorMsg) errorMsg += '<br>';
                    errorMsg += '⚠️ Fraudes não podem exceder o número de presentes por género';
                }

                validationStatus.className = 'mt-4 p-3 rounded-lg text-center bg-red-100 text-red-800';
                validationStatus.innerHTML = `
                    <span class="flex items-center justify-center gap-2 font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        ${errorMsg}
                    </span>
                `;
                submitBtn.disabled = true;
            }

            // Visual feedback for fraud fields
            if (vals.fraudes_m > vals.present_m) {
                document.getElementById('fraudes_m').classList.add('border-red-500', 'ring-1', 'ring-red-500');
            } else {
                document.getElementById('fraudes_m').classList.remove('border-red-500', 'ring-1', 'ring-red-500');
            }

            if (vals.fraudes_f > vals.present_f) {
                document.getElementById('fraudes_f').classList.add('border-red-500', 'ring-1', 'ring-red-500');
            } else {
                document.getElementById('fraudes_f').classList.remove('border-red-500', 'ring-1', 'ring-red-500');
            }
        }

        inputs.forEach(id => {
            document.getElementById(id).addEventListener('input', updateTotals);
        });

        // Initial calculation
        updateTotals();
    });
</script>