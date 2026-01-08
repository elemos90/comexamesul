<?php
$title = 'Iniciar sessão';
$isPublic = true;
$errors = validation_errors();

// Verificar se há vagas abertas para mostrar banner
$vacancyModel = new \App\Models\ExamVacancy();
$openVacanciesCount = count($vacancyModel->openVacancies());
?>
<section class="py-4 md:py-8 bg-gray-50 min-h-screen flex items-center overflow-y-auto">
    <div class="max-w-md mx-auto bg-white shadow rounded-lg p-4 md:p-6 w-full my-4">
        <!-- Logo -->
        <div class="flex justify-center mb-3 md:mb-4">
            <img src="<?= url('/assets/images/logo_unilicungo.png') ?>" alt="UniLicungo - Logo"
                class="h-12 md:h-16 w-auto object-contain">
        </div>

        <?php if ($openVacanciesCount > 0): ?>
            <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-amber-900">
                            <?= $openVacanciesCount ?> Vaga<?= $openVacanciesCount > 1 ? 's' : '' ?>
                            Aberta<?= $openVacanciesCount > 1 ? 's' : '' ?> para Vigilância
                        </h3>
                        <p class="text-xs text-amber-700 mt-1">
                            Entre ou registe-se para se candidatar às oportunidades disponíveis.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <h1 class="text-lg md:text-xl font-semibold text-gray-800 mb-3 md:mb-4">Aceder ao portal</h1>
        <form method="POST" action="<?= url('/login') ?>" class="space-y-3 md:space-y-4">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars(old('email')) ?>"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    required>
                <?php if (!empty($errors['email'])): ?>
                    <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['email'][0]) ?></p>
                <?php endif; ?>
            </div>
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Palavra-passe</label>
                <input type="password" id="password" name="password"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    required>
                <?php if (!empty($errors['password'])): ?>
                    <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['password'][0]) ?></p>
                <?php endif; ?>
            </div>
            <div class="flex items-center justify-between text-sm">
                <a class="text-primary-600 hover:text-primary-500" href="<?= url('/password/forgot') ?>">Esqueci a
                    palavra-passe</a>
            </div>
            <button type="submit"
                class="w-full bg-primary-600 text-white font-semibold py-2 rounded hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">Entrar</button>
        </form>
        <p class="mt-3 md:mt-4 text-sm text-center text-gray-600">
            Ainda não tem conta? <a class="text-primary-600 font-medium hover:underline"
                href="<?= url('/register') ?>">Crie aqui</a>
        </p>
    </div>
</section>