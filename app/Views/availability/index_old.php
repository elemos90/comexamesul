<?php
$title = 'Minha disponibilidade';
$breadcrumbs = [
    ['label' => 'Disponibilidade']
];
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6 max-w-3xl">
        <h1 class="text-2xl font-semibold text-gray-800 mb-4">Actualize a sua disponibilidade</h1>
        <p class="text-sm text-gray-600 mb-6">
            A comissao de exames utiliza esta informacao para alocar vigilantes nas vigias dos exames. Actualize sempre que houver mudancas na sua agenda.
        </p>

        <form method="POST" action="/availability" class="space-y-5">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-700">Estado actual:</span>
                <span class="px-3 py-1.5 rounded-full text-xs font-semibold <?= $available ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' ?>">
                    <?= $available ? 'Disponivel' : 'Indisponivel' ?>
                </span>
            </div>
            <div class="grid md:grid-cols-2 gap-4">
                <label class="border rounded-lg px-4 py-3 flex items-center justify-between cursor-pointer <?= $available ? 'border-primary-300 bg-primary-50' : 'border-gray-200' ?>">
                    <span class="text-sm text-gray-700">Disponivel para vigias</span>
                    <input type="radio" name="available" value="1" <?= $available ? 'checked' : '' ?> class="h-4 w-4 text-primary-600 focus:ring-primary-500">
                </label>
                <label class="border rounded-lg px-4 py-3 flex items-center justify-between cursor-pointer <?= !$available ? 'border-primary-300 bg-primary-50' : 'border-gray-200' ?>">
                    <span class="text-sm text-gray-700">Indisponivel por agora</span>
                    <input type="radio" name="available" value="0" <?= !$available ? 'checked' : '' ?> class="h-4 w-4 text-primary-600 focus:ring-primary-500">
                </label>
            </div>
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Guardar alteracoes</button>
            </div>
        </form>
    </div>
</div>
