<?php
$title = 'Definir nova palavra-passe';
$isPublic = true;
?>
<section class="py-16 bg-gray-50">
    <div class="max-w-md mx-auto bg-white shadow rounded-lg p-8">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Nova palavra-passe</h1>
        <form method="POST" action="<?= url('/password/reset') ?>" class="space-y-5">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <input type="hidden" name="token" value="<?= htmlspecialchars($token ?? '') ?>">
            <input type="hidden" name="email" value="<?= htmlspecialchars($email ?? '') ?>">
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Nova palavra-passe</label>
                <input type="password" id="password" name="password"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    required>
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar
                    palavra-passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    required>
            </div>
            <button type="submit"
                class="w-full bg-primary-600 text-white font-semibold py-2 rounded hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Guardar</button>
        </form>
    </div>
</section>