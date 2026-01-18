<?php
$title = 'Alterar palavra-passe';
$isPublic = false;
$errors = validation_errors();
?>
<section class="py-4 md:py-8 bg-gray-50 min-h-screen flex items-center overflow-y-auto">
    <div class="max-w-md mx-auto bg-white shadow rounded-lg p-4 md:p-6 w-full my-4">
        <!-- Logo -->
        <div class="flex justify-center mb-3 md:mb-4">
            <img src="<?= url('/assets/images/logo_unilicungo.png') ?>" alt="UniLicungo - Logo"
                class="h-12 md:h-16 w-auto object-contain">
        </div>

        <!-- Warning Box -->
        <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1">
                    <h3 class="text-sm font-semibold text-amber-900">Alteração obrigatória</h3>
                    <p class="text-xs text-amber-700 mt-1">
                        A sua palavra-passe actual é temporária. Por razões de segurança, deve definir uma nova senha
                        antes de continuar.
                    </p>
                </div>
            </div>
        </div>

        <h1 class="text-lg md:text-xl font-semibold text-gray-800 mb-3 md:mb-4">Definir nova palavra-passe</h1>

        <form method="POST" action="<?= url('/auth/force-password-change') ?>" class="space-y-4" id="password-form">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

            <div>
                <label for="password" class="block text-sm font-medium text-gray-700">Nova palavra-passe</label>
                <input type="password" id="password" name="password"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    required minlength="8" autocomplete="new-password">
                <?php if (!empty($errors['password'])): ?>
                    <p class="mt-1 text-xs text-red-600">
                        <?= htmlspecialchars($errors['password'][0]) ?>
                    </p>
                <?php endif; ?>
                <div class="mt-2 text-xs text-gray-500 space-y-1" id="password-requirements">
                    <p id="req-length" class="text-red-500">✗ Mínimo 8 caracteres</p>
                    <p id="req-number" class="text-red-500">✗ Pelo menos 1 número</p>
                    <p id="req-special" class="text-red-500">✗ Pelo menos 1 caractere especial (!@#$%^&* etc.)</p>
                </div>
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Confirmar nova
                    palavra-passe</label>
                <input type="password" id="password_confirmation" name="password_confirmation"
                    class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                    required minlength="8" autocomplete="new-password">
                <p class="mt-1 text-xs" id="password-match"></p>
            </div>

            <button type="submit" id="submit-btn"
                class="w-full bg-primary-600 text-white font-semibold py-2 rounded hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 disabled:opacity-50 disabled:cursor-not-allowed"
                disabled>
                Confirmar nova senha
            </button>
        </form>
    </div>
</section>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const passwordInput = document.getElementById('password');
        const confirmInput = document.getElementById('password_confirmation');
        const submitBtn = document.getElementById('submit-btn');
        const reqLength = document.getElementById('req-length');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');
        const passwordMatch = document.getElementById('password-match');

        function validatePassword() {
            const password = passwordInput.value;
            let valid = true;

            // Mínimo 8 caracteres
            if (password.length >= 8) {
                reqLength.textContent = '✓ Mínimo 8 caracteres';
                reqLength.className = 'text-green-600';
            } else {
                reqLength.textContent = '✗ Mínimo 8 caracteres';
                reqLength.className = 'text-red-500';
                valid = false;
            }

            // Pelo menos 1 número
            if (/[0-9]/.test(password)) {
                reqNumber.textContent = '✓ Pelo menos 1 número';
                reqNumber.className = 'text-green-600';
            } else {
                reqNumber.textContent = '✗ Pelo menos 1 número';
                reqNumber.className = 'text-red-500';
                valid = false;
            }

            // Pelo menos 1 caractere especial
            if (/[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\/]/.test(password)) {
                reqSpecial.textContent = '✓ Pelo menos 1 caractere especial';
                reqSpecial.className = 'text-green-600';
            } else {
                reqSpecial.textContent = '✗ Pelo menos 1 caractere especial';
                reqSpecial.className = 'text-red-500';
                valid = false;
            }

            checkPasswordMatch();
            return valid;
        }

        function checkPasswordMatch() {
            if (confirmInput.value.length > 0) {
                if (passwordInput.value === confirmInput.value) {
                    passwordMatch.textContent = '✓ As senhas coincidem';
                    passwordMatch.className = 'mt-1 text-xs text-green-600';
                    return true;
                } else {
                    passwordMatch.textContent = '✗ As senhas não coincidem';
                    passwordMatch.className = 'mt-1 text-xs text-red-500';
                    return false;
                }
            } else {
                passwordMatch.textContent = '';
                return false;
            }
        }

        function updateSubmitButton() {
            const passwordValid = validatePassword();
            const matchValid = passwordInput.value === confirmInput.value && confirmInput.value.length > 0;
            submitBtn.disabled = !(passwordValid && matchValid);
        }

        passwordInput.addEventListener('input', updateSubmitButton);
        confirmInput.addEventListener('input', updateSubmitButton);
    });
</script>