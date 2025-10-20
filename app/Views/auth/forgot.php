<?php
$title = 'Recuperar palavra-passe';
$isPublic = true;
?>
<section class="py-16 bg-gray-50">
    <div class="max-w-md mx-auto bg-white shadow rounded-lg p-8">
        <h1 class="text-2xl font-semibold text-gray-800 mb-6">Recuperar acesso</h1>
        <p class="text-sm text-gray-600 mb-6">Informe o email registado para receber instruções de redefinição. O link expira após 60 minutos.</p>
        <form method="POST" action="/password/forgot" class="space-y-5">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars(old('email')) ?>" class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500" required>
            </div>
            <button type="submit" class="w-full bg-primary-600 text-white font-semibold py-2 rounded hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Enviar instruções</button>
        </form>
        <p class="mt-6 text-sm text-gray-600"><a class="text-primary-600 font-medium" href="/login">Voltar ao login</a></p>
    </div>
</section>
