<?php
$title = 'Recuperação não disponível';
$isPublic = true;
?>
<section class="py-8 bg-gray-50 min-h-screen flex items-center">
    <div class="max-w-md mx-auto bg-white shadow rounded-lg p-6 w-full">
        <div class="text-center mb-6">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-red-100 mb-4">
                <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
            </div>
            <h1 class="text-xl font-semibold text-gray-800">Recuperação Automática Indisponível</h1>
            <p class="text-sm text-gray-500 mt-2">
                Não conseguimos verificar a sua identidade através dos métodos automáticos (ou não estão configurados).
            </p>
        </div>

        <div class="bg-gray-50 p-4 rounded-lg mb-6 text-sm text-gray-700">
            <p>Pode solicitar que um Administrador ou Coordenador redefina a sua senha manualmente. Eles serão
                notificados e entrarão em contacto.</p>
        </div>

        <form method="POST" action="<?= url('/recover/fallback') ?>">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <?php if (!empty($_SESSION['recovery_username'])): ?>
                <input type="hidden" name="username" value="<?= htmlspecialchars($_SESSION['recovery_username']) ?>">
                <p class="text-sm text-center mb-4">Utilizador: <strong>
                        <?= htmlspecialchars($_SESSION['recovery_username']) ?>
                    </strong></p>
            <?php else: ?>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Confirme seu utilizador</label>
                    <input type="text" name="username" class="mt-1 w-full rounded border border-gray-300 px-3 py-2"
                        required>
                </div>
            <?php endif; ?>

            <button type="submit"
                class="w-full bg-gray-800 text-white font-semibold py-2 rounded hover:bg-gray-700 focus:outline-none">
                Solicitar Apoio ao Administrador
            </button>
        </form>

        <div class="mt-4 text-center">
            <a href="<?= url('/login') ?>" class="text-xs text-gray-500 hover:text-gray-800">Cancelar e voltar ao
                Login</a>
        </div>
    </div>
</section>