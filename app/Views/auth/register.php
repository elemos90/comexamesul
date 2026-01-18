<?php
$title = 'Criar conta de vigilante';
$isPublic = true;
$errors = validation_errors();

// Verificar se há vagas abertas para mostrar banner
$vacancyModel = new \App\Models\ExamVacancy();
$openVacanciesCount = count($vacancyModel->openVacancies());
?>
<section class="py-4 md:py-8 bg-gray-50 min-h-screen flex items-center overflow-y-auto">
    <div class="max-w-2xl mx-auto bg-white shadow rounded-lg p-4 md:p-6 w-full my-4">
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
                            Crie uma conta para se candidatar às oportunidades disponíveis.
                        </p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <h1 class="text-lg md:text-xl font-semibold text-gray-800 mb-3 md:mb-4">Registar vigilante</h1>

        <form method="POST" action="<?= url('/register') ?>" class="grid md:grid-cols-2 gap-3 md:gap-4"
            id="register-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <!-- Nome Completo -->
            <div class="md:col-span-2">
                <label for="name" class="block text-sm font-medium text-gray-700">Nome completo *</label>
                <input type="text" id="name" name="name" value="<?= htmlspecialchars(old('name')) ?>"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    required>
                <?php if (!empty($errors['name'])): ?>
                    <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['name'][0]) ?></p>
                <?php endif; ?>
            </div>

            <!-- Username (Obrigatório) -->
            <div>
                <label for="username" class="block text-sm font-medium text-gray-700">Nome de utilizador *</label>
                <input type="text" id="username" name="username" value="<?= htmlspecialchars(old('username')) ?>"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    placeholder="ex: joao.silva" required autocomplete="username" pattern="[a-zA-Z0-9._]+"
                    title="Apenas letras, números, pontos e underscores">
                <?php if (!empty($errors['username'])): ?>
                    <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['username'][0]) ?></p>
                <?php endif; ?>
                <p class="mt-1 text-xs text-gray-500">Será usado para fazer login (ex: nome.apelido)</p>
            </div>

            <!-- Email (Opcional) -->
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email <span
                        class="text-gray-400">(opcional)</span></label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars(old('email')) ?>"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    autocomplete="email">
                <?php if (!empty($errors['email'])): ?>
                    <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['email'][0]) ?></p>
                <?php endif; ?>
            </div>

            <!-- Password -->
            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Palavra-passe *</label>
                <input type="password" id="password" name="password"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    required minlength="8" autocomplete="new-password">
                <?php if (!empty($errors['password'])): ?>
                    <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($errors['password'][0]) ?></p>
                <?php endif; ?>
                <div class="mt-1 text-xs text-gray-500" id="password-requirements">
                    <span id="req-length" class="text-red-500">✗ Mínimo 8 caracteres</span><br>
                    <span id="req-number" class="text-red-500">✗ Pelo menos 1 número</span><br>
                    <span id="req-special" class="text-red-500">✗ Pelo menos 1 caractere especial</span>
                </div>
            </div>

            <!-- Confirmar Password -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar
                    palavra-passe *</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    required minlength="8" autocomplete="new-password">
                <p class="mt-1 text-xs text-gray-500" id="password-match"></p>
            </div>

            <div class="md:col-span-2 flex items-center justify-between text-sm text-gray-600">
                <span>Ao registar, confirmo que os dados são verdadeiros.</span>
                <a href="<?= url('/login') ?>" class="text-primary-600 font-medium">Já tenho conta</a>
            </div>

            <div class="md:col-span-2">
                <button type="submit" id="submit-btn"
                    class="w-full bg-primary-600 text-white font-semibold py-2 rounded hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed">
                    Criar conta
                </button>
                <p class="mt-3 text-xs text-gray-500">Depois de criar a conta, será solicitado a completar o seu perfil.
                </p>
            </div>
        </form>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');
        const reqLength = document.getElementById('req-length');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');
        const passwordMatch = document.getElementById('password-match');

        function validatePassword() {
            const password = passwordInput.value;

            // Mínimo 8 caracteres
            if (password.length >= 8) {
                reqLength.textContent = '✓ Mínimo 8 caracteres';
                reqLength.className = 'text-green-600';
            } else {
                reqLength.textContent = '✗ Mínimo 8 caracteres';
                reqLength.className = 'text-red-500';
            }

            // Pelo menos 1 número
            if (/[0-9]/.test(password)) {
                reqNumber.textContent = '✓ Pelo menos 1 número';
                reqNumber.className = 'text-green-600';
            } else {
                reqNumber.textContent = '✗ Pelo menos 1 número';
                reqNumber.className = 'text-red-500';
            }

            // Pelo menos 1 caractere especial
            if (/[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/]/.test(password)) {
                reqSpecial.textContent = '✓ Pelo menos 1 caractere especial';
                reqSpecial.className = 'text-green-600';
            } else {
                reqSpecial.textContent = '✗ Pelo menos 1 caractere especial';
                reqSpecial.className = 'text-red-500';
            }

            checkPasswordMatch();
        }

        function checkPasswordMatch() {
            if (confirmInput.value.length > 0) {
                if (passwordInput.value === confirmInput.value) {
                    passwordMatch.textContent = '✓ As senhas coincidem';
                    passwordMatch.className = 'mt-1 text-xs text-green-600';
                } else {
                    passwordMatch.textContent = '✗ As senhas não coincidem';
                    passwordMatch.className = 'mt-1 text-xs text-red-500';
                }
            } else {
                passwordMatch.textContent = '';
            }
        }

        passwordInput.addEventListener('input', validatePassword);
        confirmInput.addEventListener('input', checkPasswordMatch);
    });
</script>