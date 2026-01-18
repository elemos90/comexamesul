<?php
$title = 'Verificar Identidade';
$isPublic = true;
?>
<section class="py-8 bg-gray-50 min-h-screen flex items-center">
    <div class="max-w-md mx-auto bg-white shadow rounded-lg p-6 w-full">
        <h1 class="text-xl font-semibold text-gray-800 mb-2 text-center">Verificação</h1>
        <p class="text-sm text-gray-500 mb-6 text-center">
            <?php if ($method === 'keyword'): ?>
                Introduza a sua Palavra-Chave Secreta.
            <?php elseif ($method === 'pin'): ?>
                Introduza o seu PIN de Recuperação.
            <?php elseif ($method === 'questions'): ?>
                Responda às perguntas de segurança.
            <?php endif; ?>
        </p>

        <form method="POST" action="<?= url('/recover/verify') ?>" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <?php if ($method === 'keyword'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Palavra-Chave</label>
                    <input type="text" name="keyword" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required
                        autocomolete="off">
                </div>

            <?php elseif ($method === 'pin'): ?>
                <div>
                    <label class="block text-sm font-medium text-gray-700">PIN</label>
                    <input type="text" name="pin" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required
                        pattern="\d*" inputmode="numeric" autocomolete="off">
                </div>

            <?php elseif ($method === 'questions' && !empty($questions)): ?>
                <?php
                // Assumindo q temos 2 perguntas
                $i = 1;
                foreach ($questions as $q):
                    ?>
                    <input type="hidden" name="question_ids[]" value="<?= $q['id'] ?>">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">
                            <?= htmlspecialchars($q['question']) ?>
                        </label>
                        <input type="text" name="answer_<?= $i ?>" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                            required placeholder="Sua resposta">
                    </div>
                    <?php $i++; endforeach; ?>
            <?php endif; ?>

            <button type="submit"
                class="w-full bg-primary-600 text-white font-semibold py-2 rounded hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Verificar
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="<?= url('/recover/method') ?>" class="text-xs text-primary-600 hover:underline">Voltar à escolha de
                método</a>
        </div>
    </div>
</section>