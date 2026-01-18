<?php
$title = 'Recuperar palavra-passe';
$isPublic = true;
$errors = validation_errors();
?>
<section class="py-4 md:py-8 bg-gray-50 min-h-screen flex items-center overflow-y-auto">
    <div class="max-w-md mx-auto bg-white shadow rounded-lg p-4 md:p-6 w-full my-4">
        <!-- Logo -->
        <div class="flex justify-center mb-3 md:mb-4">
            <img src="<?= url('/assets/images/logo_unilicungo.png') ?>" alt="UniLicungo - Logo"
                class="h-12 md:h-16 w-auto object-contain">
        </div>

        <h1 class="text-lg md:text-xl font-semibold text-gray-800 mb-2">Recuperar palavra-passe</h1>
        <p class="text-sm text-gray-600 mb-4">
            Informe o seu nome de utilizador. Será enviado um pedido ao Coordenador para redefinir a sua senha.
        </p>

        <!-- Info Box -->
        <div class="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-blue-900">Como funciona?</h3>
                    <ul class="text-xs text-blue-700 mt-1 list-disc list-inside space-y-1">
                        <li>O Coordenador será notificado do seu pedido</li>
                        <li>Uma senha temporária será gerada</li>
                        <li>Receberá uma notificação no sistema</li>
                        <li>Ao fazer login, deverá criar uma nova senha</li>
                    </ul>
                </div>
            </div>
        </div>

        <form method="POST" action="<?= url('/password/forgot') ?>" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Nome de utilizador</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars(old('username')) ?>"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="ex: joao.silva" required autocomplete="username">
                <?php if (!empty($errors['username'])): ?>
                    <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['username'][0]) ?></p>
                <?php endif; ?>
            </div>

            <button type="submit"
                class="w-full bg-primary-600 text-white font-semibold py-2 rounded hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Solicitar recuperação
            </button>
        </form>

        <p class="mt-4 text-sm text-center text-gray-600">
            Lembra-se da senha? <a class="text-primary-600 font-medium hover:underline"
                href="<?= url('/login') ?>">Voltar ao login</a>
        </p>
    </div>
</section>