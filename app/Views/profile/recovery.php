<?php
$title = 'Segurança e Recuperação';
$breadcrumbs = [
    ['label' => 'Perfil', 'url' => url('/profile')],
    ['label' => 'Recuperação']
];
?>
<div class="space-y-8">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Sidebar -->
        <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-5 h-fit">
            <div class="flex flex-col items-center text-center">
                <div
                    class="w-24 h-24 bg-primary-100 rounded-full flex items-center justify-center text-primary-600 text-3xl font-semibold">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?>
                </div>
                <h2 class="mt-4 text-lg font-semibold text-gray-800">
                    <?= htmlspecialchars($user['name']) ?>
                </h2>
                <span class="mt-2 px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                    <?= ucfirst($user['role']) ?>
                </span>
            </div>

            <nav class="mt-6 space-y-1">
                <a href="<?= url('/profile') ?>"
                    class="flex items-center gap-3 px-4 py-2 text-gray-600 hover:bg-gray-50 rounded-lg transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Dados Pessoais
                </a>
                <a href="<?= url('/profile/recovery') ?>"
                    class="flex items-center gap-3 px-4 py-2 bg-primary-50 text-primary-700 rounded-lg font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    Recuperação de Conta
                </a>
            </nav>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="bg-yellow-50 border border-yellow-200 rounded p-3">
                    <p class="text-xs text-yellow-800 font-medium mb-1">Nota de Segurança</p>
                    <p class="text-xs text-yellow-700">Configure pelo menos um método de recuperação para garantir que
                        nunca perde o acesso à sua conta.</p>
                </div>
            </div>
        </div>

        <!-- Conteúdo Principal -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Palavra-Chave -->
            <div class="bg-white border border-gray-100 rounded-lg shadow-sm">
                <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Palavra-Chave de Recuperação</h3>
                    <?php if (!empty($recoveryStatus['keyword'])): ?>
                        <span
                            class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">Configurado</span>
                    <?php else: ?>
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full font-medium">Não
                            configurado</span>
                    <?php endif; ?>
                </div>
                <div class="p-5">
                    <p class="text-sm text-gray-600 mb-4">
                        Uma palavra ou frase secreta que só você conhece. Não use a sua senha normal.
                    </p>
                    <form action="<?= url('/profile/recovery/keyword') ?>" method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Palavra-Chave</label>
                            <input type="text" name="keyword"
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required minlength="3"
                                placeholder="Ex: Batata Frita 123">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Senha Atual (para confirmar)</label>
                            <input type="password" name="current_password"
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                        </div>
                        <div class="text-right">
                            <button type="submit"
                                class="px-4 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-700">
                                Guardar Palavra-Chave
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Perguntas de Segurança -->
            <div class="bg-white border border-gray-100 rounded-lg shadow-sm">
                <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Perguntas de Segurança</h3>
                    <?php if (!empty($recoveryStatus['questions'])): ?>
                        <span
                            class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">Configurado</span>
                    <?php else: ?>
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full font-medium">Não
                            configurado</span>
                    <?php endif; ?>
                </div>
                <div class="p-5">
                    <p class="text-sm text-gray-600 mb-4">
                        Responda a duas perguntas pessoais. As respostas são ignoram maiúsculas/minúsculas.
                    </p>
                    <form action="<?= url('/profile/recovery/questions') ?>" method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Pergunta 1</label>
                                <select name="question_1" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                                    required>
                                    <option value="">Selecione...</option>
                                    <?php foreach ($questions as $q): ?>
                                        <option value="<?= $q['id'] ?>">
                                            <?= htmlspecialchars($q['question']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Resposta 1</label>
                                <input type="text" name="answer_1"
                                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                            </div>
                        </div>

                        <div class="border-t border-gray-100 my-4"></div>

                        <div class="grid md:grid-cols-2 gap-4">
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Pergunta 2</label>
                                <select name="question_2" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                                    required>
                                    <option value="">Selecione...</option>
                                    <!-- Reutilizando perguntas, o controller deve validar se são diferentes -->
                                    <?php foreach ($questions as $q): ?>
                                        <option value="<?= $q['id'] ?>">
                                            <?= htmlspecialchars($q['question']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="block text-sm font-medium text-gray-700">Resposta 2</label>
                                <input type="text" name="answer_2"
                                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                            </div>
                        </div>

                        <div class="pt-2">
                            <label class="block text-sm font-medium text-gray-700">Senha Atual (para confirmar)</label>
                            <input type="password" name="current_password"
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                        </div>
                        <div class="text-right">
                            <button type="submit"
                                class="px-4 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-700">
                                Guardar Perguntas
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- PIN -->
            <div class="bg-white border border-gray-100 rounded-lg shadow-sm">
                <div class="px-5 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">PIN de Recuperação</h3>
                    <?php if (!empty($recoveryStatus['pin'])): ?>
                        <span
                            class="px-2 py-1 bg-green-100 text-green-700 text-xs rounded-full font-medium">Configurado</span>
                    <?php else: ?>
                        <span class="px-2 py-1 bg-gray-100 text-gray-600 text-xs rounded-full font-medium">Não
                            configurado</span>
                    <?php endif; ?>
                </div>
                <div class="p-5">
                    <p class="text-sm text-gray-600 mb-4">
                        Um código numérico de 4 a 6 dígitos.
                    </p>
                    <form action="<?= url('/profile/recovery/pin') ?>" method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Novo PIN</label>
                            <input type="text" name="pin" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                                required pattern="\d{4,6}" placeholder="4 a 6 dígitos" maxlength="6">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Senha Atual (para confirmar)</label>
                            <input type="password" name="current_password"
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                        </div>
                        <div class="text-right">
                            <button type="submit"
                                class="px-4 py-2 bg-gray-800 text-white text-sm rounded hover:bg-gray-700">
                                Guardar PIN
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>