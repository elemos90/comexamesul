<?php
$title = 'Recuperar Acesso';
$isPublic = true;
?>
<section class="py-8 bg-gray-50 min-h-screen flex items-center">
    <div class="max-w-md mx-auto bg-white shadow rounded-lg p-6 w-full">
        <!-- Logo -->
        <div class="flex justify-center mb-4">
            <img src="<?= url('/assets/images/logo_unilicungo.png') ?>" alt="UniLicungo - Logo"
                class="h-16 w-auto object-contain">
        </div>

        <h1 class="text-xl font-semibold text-gray-800 mb-2 text-center">Recuperar Acesso</h1>
        <p class="text-sm text-gray-500 mb-6 text-center">
            Informe o seu nome de utilizador para identificarmos a sua conta.
        </p>

        <form method="POST" action="<?= url('/recover/check') ?>" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Nome de utilizador</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars(old('username')) ?>"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="ex: joao.silva" required autofocus>
            </div>

            <button type="submit"
                class="w-full bg-primary-600 text-white font-semibold py-2 rounded hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Continuar
            </button>
        </form>

        <p class="mt-4 text-sm text-center text-gray-600">
            Lembrou-se da senha? <a class="text-primary-600 font-medium hover:underline"
                href="<?= url('/login') ?>">Entrar</a>
        </p>
    </div>
</section>