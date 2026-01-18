<?php
$title = 'Escolha um Método';
$isPublic = true;
?>
<section class="py-8 bg-gray-50 min-h-screen flex items-center">
    <div class="max-w-md mx-auto bg-white shadow rounded-lg p-6 w-full">
        <h1 class="text-xl font-semibold text-gray-800 mb-2 text-center">Como deseja recuperar?</h1>
        <p class="text-sm text-gray-500 mb-6 text-center">
            Selecione um dos métodos configurados na sua conta.
        </p>

        <form method="POST" action="<?= url('/recover/method') ?>" class="space-y-3">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <?php if (isset($methods['keyword'])): ?>
                <button type="submit" name="method" value="keyword"
                    class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-primary-500 transition-colors text-left group">
                    <div
                        class="bg-primary-100 p-2 rounded-full text-primary-600 mr-4 group-hover:bg-primary-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11.536 16 13 17.464 11.465 19 9 21.536 7.464 20C5.121 17.657 5.121 13.536 7.464 11.2 9.807 8.857 13.143 8.357 15.5 10.322">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-800">Palavra-Chave Secreta</span>
                        <span class="block text-xs text-gray-500">Use a palavra ou frase que definiu</span>
                    </div>
                </button>
            <?php endif; ?>

            <?php if (isset($methods['questions'])): ?>
                <button type="submit" name="method" value="questions"
                    class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-primary-500 transition-colors text-left group">
                    <div
                        class="bg-blue-100 p-2 rounded-full text-blue-600 mr-4 group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                            </path>
                        </svg>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-800">Perguntas de Segurança</span>
                        <span class="block text-xs text-gray-500">Responda às perguntas pessoais</span>
                    </div>
                </button>
            <?php endif; ?>

            <?php if (isset($methods['pin'])): ?>
                <button type="submit" name="method" value="pin"
                    class="w-full flex items-center p-4 border border-gray-200 rounded-lg hover:bg-gray-50 hover:border-primary-500 transition-colors text-left group">
                    <div
                        class="bg-green-100 p-2 rounded-full text-green-600 mr-4 group-hover:bg-green-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 4v1m6 11h2m-6 0h-2v4h-4v-4H8m13-4V7a1 1 0 00-1-1H4a1 1 0 00-1 1v3M4 7h16"></path>
                        </svg>
                    </div>
                    <div>
                        <span class="block font-medium text-gray-800">PIN de Recuperação</span>
                        <span class="block text-xs text-gray-500">Código numérico</span>
                    </div>
                </button>
            <?php endif; ?>
        </form>

        <div class="mt-6 text-center">
            <p class="text-sm text-gray-600">Não consegue usar estes métodos?</p>
            <a href="<?= url('/recover/fallback') ?>" class="text-sm text-primary-600 font-medium hover:underline">Pedir
                apoio ao Coordenador</a>
        </div>

        <div class="mt-4 text-center">
            <a href="<?= url('/login') ?>" class="text-xs text-gray-500 hover:text-gray-800">Cancelar e voltar ao
                Login</a>
        </div>
    </div>
</section>