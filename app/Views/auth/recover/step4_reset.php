<?php
$title = 'Redefinir Senha';
$isPublic = true;
?>
<section class="py-8 bg-gray-50 min-h-screen flex items-center">
    <div class="max-w-md mx-auto bg-white shadow rounded-lg p-6 w-full">
        <h1 class="text-xl font-semibold text-gray-800 mb-2 text-center">Nova Senha</h1>
        <p class="text-sm text-gray-500 mb-6 text-center">
            Identidade confirmada. Defina a sua nova palavra-passe.
        </p>

        <form method="POST" action="<?= url('/recover/reset') ?>" class="space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Nova palavra-passe</label>
                <input type="password" id="password" name="password"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required minlength="8">
                <p class="text-xs text-gray-500 mt-1">Mínimo 8 caracteres</p>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar
                    palavra-passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
            </div>

            <button type="submit"
                class="w-full bg-green-600 text-white font-semibold py-2 rounded hover:bg-green-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                Alterar Senha
            </button>
        </form>
    </div>
</section>