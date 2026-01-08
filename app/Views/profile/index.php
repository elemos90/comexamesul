<?php
$title = 'Perfil pessoal';
$breadcrumbs = [
    ['label' => 'Perfil']
];
?>
<div class="space-y-8">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Sidebar - Avatar e Informações -->
        <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-5">
            <div class="flex flex-col items-center text-center">
                <div
                    class="w-24 h-24 bg-primary-100 rounded-full flex items-center justify-center text-primary-600 text-3xl font-semibold">
                    <?= strtoupper(substr($user['name'] ?? 'U', 0, 2)) ?>
                </div>
                <h2 class="mt-4 text-lg font-semibold text-gray-800"><?= htmlspecialchars($user['name']) ?></h2>
                <p class="text-sm text-gray-500 mt-1"><?= htmlspecialchars($user['email']) ?></p>
                <span class="mt-2 px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                    <?= ucfirst($user['role']) ?>
                </span>
            </div>

            <div class="mt-6 pt-6 border-t border-gray-200">
                <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    <span class="font-medium">Email (não editável)</span>
                </div>
                <p class="text-xs text-gray-500 bg-gray-50 rounded p-2">
                    O email não pode ser alterado. Para usar outro email, crie uma nova conta.
                </p>
            </div>

            <form method="POST" action="<?= url('/profile/avatar') ?>" enctype="multipart/form-data"
                class="mt-6 space-y-3">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                <label class="block text-sm font-medium text-gray-700" for="avatar">Atualizar foto</label>
                <input type="file" name="avatar" id="avatar" accept="image/*" class="w-full text-sm text-gray-600">
                <button type="submit"
                    class="w-full bg-primary-600 text-white text-sm font-medium py-2 rounded hover:bg-primary-500">Guardar
                    foto</button>
            </form>
        </div>

        <!-- Formulários Principais -->
        <div class="lg:col-span-2 space-y-6">
            <form method="POST" action="<?= url('/profile') ?>">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

                <!-- Seção 1: Dados Pessoais -->
                <div class="bg-white border border-gray-100 rounded-lg shadow-sm mb-6">
                    <div class="px-5 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-50 to-white">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-800">Dados Pessoais</h3>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Informações básicas de identificação</p>
                    </div>
                    <div class="p-5 grid md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700" for="name">Nome completo <span
                                    class="text-red-500">*</span></label>
                            <input
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                                id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                            <?php $fieldErrors = validation_errors('name'); ?>
                            <?php if (!empty($fieldErrors)): ?>
                                <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($fieldErrors[0]) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="phone">Telefone / WhatsApp <span
                                    class="text-red-500">*</span></label>
                            <input type="tel" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="phone"
                                name="phone" value="<?= htmlspecialchars($user['phone'] ?? '+258 ') ?>" required
                                placeholder="+258 84 123 4567" maxlength="17">
                            <p class="mt-1 text-xs text-gray-500">Digite 9 a 11 dígitos (8X XXX XXXX ou 8X XXX XXXXXXX)
                            </p>
                            <?php $fieldErrors = validation_errors('phone'); ?>
                            <?php if (!empty($fieldErrors)): ?>
                                <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($fieldErrors[0]) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="gender">Gênero</label>
                            <select id="gender" name="gender"
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2">
                                <option value="">Selecione...</option>
                                <?php foreach (['M' => 'Masculino', 'F' => 'Feminino', 'O' => 'Outro'] as $key => $label): ?>
                                    <option value="<?= $key ?>" <?= ($user['gender'] ?? '') === $key ? 'selected' : '' ?>>
                                        <?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php $fieldErrors = validation_errors('gender'); ?>
                            <?php if (!empty($fieldErrors)): ?>
                                <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($fieldErrors[0]) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="nuit">NUIT <span
                                    class="text-red-500">*</span></label>
                            <input type="text" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="nuit"
                                name="nuit" value="<?= htmlspecialchars($user['nuit'] ?? '') ?>" required
                                placeholder="123456789" maxlength="9" pattern="\d{9}">
                            <p class="mt-1 text-xs text-gray-500">9 dígitos (Número Único de Identificação Tributária)
                            </p>
                            <?php $fieldErrors = validation_errors('nuit'); ?>
                            <?php if (!empty($fieldErrors)): ?>
                                <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($fieldErrors[0]) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700" for="origin_university">Universidade
                                de Origem <span class="text-red-500">*</span></label>
                            <select id="origin_university" name="origin_university"
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                                <option value="">Selecione a universidade...</option>
                                <?php
                                $universities = [
                                    'Universidade Eduardo Mondlane (UEM)',
                                    'Universidade Pedagógica (UP)',
                                    'Instituto Superior de Ciências e Tecnologia de Moçambique (ISCTEM)',
                                    'Universidade Católica de Moçambique (UCM)',
                                    'Universidade São Tomás de Moçambique (USTM)',
                                    'Universidade Politécnica (UniPol)',
                                    'Universidade Licungo (UniLicungo)',
                                    'Universidade Lúrio (UniLúrio)',
                                    'Universidade Zambeze (UniZambeze)',
                                    'Universidade Save (UniSave)',
                                    'Instituto Superior de Relações Internacionais (ISRI)',
                                    'Instituto Superior de Tecnologias e Gestão (ISTEG)',
                                    'Instituto Superior Monitor (ISM)',
                                    'Instituto Superior de Comunicação e Imagem de Moçambique (ISCIM)',
                                    'Instituto Superior de Ciências da Saúde (ISCISA)',
                                    'Universidade Wutivi',
                                    'Instituto Superior Politécnico de Gaza (ISPG)',
                                    'Universidade Adventista de Moçambique (UADM)',
                                    'Universidade Mussa Bin Bique',
                                    'Instituto Superior de Gestão, Comércio e Finanças (ISGCoF)',
                                    'Outra'
                                ];
                                foreach ($universities as $uni):
                                    ?>
                                    <option value="<?= htmlspecialchars($uni) ?>" <?= ($user['origin_university'] ?? '') === $uni ? 'selected' : '' ?>><?= htmlspecialchars($uni) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php $fieldErrors = validation_errors('origin_university'); ?>
                            <?php if (!empty($fieldErrors)): ?>
                                <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($fieldErrors[0]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Seção 2: Dados Acadêmicos -->
                <div class="bg-white border border-gray-100 rounded-lg shadow-sm mb-6">
                    <div class="px-5 py-4 border-b border-gray-200 bg-gradient-to-r from-purple-50 to-white">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path d="M12 14l9-5-9-5-9 5 9 5z" />
                                <path
                                    d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 14l9-5-9-5-9 5 9 5zm0 0l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222" />
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-800">Dados Acadêmicos</h3>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Formação e qualificações</p>
                    </div>
                    <div class="p-5 grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="university">Universidade <span
                                    class="text-red-500">*</span></label>
                            <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="university"
                                name="university" value="<?= htmlspecialchars($user['university'] ?? '') ?>" required
                                placeholder="Ex: Universidade Eduardo Mondlane">
                            <?php $fieldErrors = validation_errors('university'); ?>
                            <?php if (!empty($fieldErrors)): ?>
                                <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($fieldErrors[0]) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="degree">Titulação <span
                                    class="text-red-500">*</span></label>
                            <select id="degree" name="degree"
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                                <option value="">Selecione...</option>
                                <?php foreach (['Licenciado', 'Mestre', 'Doutor'] as $degree): ?>
                                    <option value="<?= $degree ?>" <?= ($user['degree'] ?? '') === $degree ? 'selected' : '' ?>><?= $degree ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php $fieldErrors = validation_errors('degree'); ?>
                            <?php if (!empty($fieldErrors)): ?>
                                <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($fieldErrors[0]) ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700" for="major_area">Área de Formação /
                                Disciplina <span class="text-red-500">*</span></label>
                            <input class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="major_area"
                                name="major_area" value="<?= htmlspecialchars($user['major_area'] ?? '') ?>" required
                                placeholder="Ex: Matemática, Física, Engenharia Civil...">
                            <?php $fieldErrors = validation_errors('major_area'); ?>
                            <?php if (!empty($fieldErrors)): ?>
                                <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($fieldErrors[0]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Seção 3: Dados Bancários -->
                <div class="bg-white border border-gray-100 rounded-lg shadow-sm mb-6">
                    <div class="px-5 py-4 border-b border-gray-200 bg-gradient-to-r from-green-50 to-white">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z" />
                            </svg>
                            <h3 class="text-lg font-semibold text-gray-800">Dados Bancários</h3>
                        </div>
                        <p class="text-xs text-gray-600 mt-1">Informações para pagamento</p>
                    </div>
                    <div class="p-5 grid md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="bank_name">Banco <span
                                    class="text-red-500">*</span></label>
                            <select id="bank_name" name="bank_name"
                                class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                                <option value="">Selecione o banco...</option>
                                <?php
                                $banks = [
                                    'Millennium BIM',
                                    'Standard Bank',
                                    'Banco Comercial e de Investimentos (BCI)',
                                    'Absa Bank Moçambique (ex-Barclays)',
                                    'First National Bank (FNB)',
                                    'Nedbank Moçambique',
                                    'BancABC',
                                    'Ecobank Moçambique',
                                    'Banco de Moçambique (BM)',
                                    'Banco SOCREMO',
                                    'Banco Único',
                                    'Letshego Bank',
                                    'Access Bank Moçambique',
                                    'Banco Terra',
                                    'MozaBanco',
                                    'Outro'
                                ];
                                foreach ($banks as $bank):
                                    ?>
                                    <option value="<?= htmlspecialchars($bank) ?>" <?= ($user['bank_name'] ?? '') === $bank ? 'selected' : '' ?>><?= htmlspecialchars($bank) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php $fieldErrors = validation_errors('bank_name'); ?>
                            <?php if (!empty($fieldErrors)): ?>
                                <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($fieldErrors[0]) ?></p>
                            <?php endif; ?>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700" for="nib">NIB <span
                                    class="text-red-500">*</span></label>
                            <input type="text" class="mt-1 w-full rounded border border-gray-300 px-3 py-2" id="nib"
                                name="nib" value="<?= htmlspecialchars($user['nib'] ?? '') ?>" required
                                placeholder="12345678901234567890123" maxlength="23" pattern="\d{23}">
                            <p class="mt-1 text-xs text-gray-500">23 dígitos (Número de Identificação Bancária)</p>
                            <?php $fieldErrors = validation_errors('nib'); ?>
                            <?php if (!empty($fieldErrors)): ?>
                                <p class="mt-1 text-xs text-red-600"><?= htmlspecialchars($fieldErrors[0]) ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Botão de Salvar -->
                <div class="flex items-center justify-end gap-3 bg-gray-50 rounded-lg p-4 border border-gray-200">
                    <p class="text-xs text-gray-500 mr-auto">
                        <span class="text-red-500">*</span> Campos obrigatórios
                    </p>
                    <button type="submit"
                        class="inline-flex items-center gap-2 px-5 py-2.5 bg-primary-600 text-white text-sm font-semibold rounded-lg hover:bg-primary-700 transition-colors shadow-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Guardar Alterações
                    </button>
                </div>
            </form>

            <div class="bg-white border border-gray-100 rounded-lg shadow-sm">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-800">Atualizar palavra-passe</h3>
                </div>
                <form method="POST" action="<?= url('/profile/password') ?>" class="p-5 grid md:grid-cols-2 gap-4">
                    <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700" for="current_password">Palavra-passe
                            atual</label>
                        <input type="password" id="current_password" name="current_password"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700" for="password">Nova palavra-passe</label>
                        <input type="password" id="password" name="password"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700"
                            for="password_confirmation">Confirmar</label>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="mt-1 w-full rounded border border-gray-300 px-3 py-2" required>
                    </div>
                    <div class="md:col-span-2">
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-gray-800 text-white text-sm font-medium rounded hover:bg-gray-700">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const phoneInput = document.getElementById('phone');
        const prefix = '+258 ';

        // Garantir que sempre comece com +258
        if (!phoneInput.value || phoneInput.value === '') {
            phoneInput.value = prefix;
        } else if (!phoneInput.value.startsWith(prefix)) {
            // Se já tem valor mas não tem o prefixo, adicionar
            const digits = phoneInput.value.replace(/\D/g, '');
            if (digits.startsWith('258')) {
                phoneInput.value = '+' + digits.substring(0, 3) + ' ' + digits.substring(3, 14);
            } else {
                phoneInput.value = prefix + digits.substring(0, 11);
            }
        }

        // Ao focar, posicionar cursor após o prefixo
        phoneInput.addEventListener('focus', function () {
            if (this.value === prefix) {
                this.setSelectionRange(prefix.length, prefix.length);
            }
        });

        // Prevenir remoção do prefixo
        phoneInput.addEventListener('keydown', function (e) {
            const cursorPos = this.selectionStart;

            // Prevenir backspace/delete se tentar remover o prefixo
            if ((e.key === 'Backspace' || e.key === 'Delete') && cursorPos <= prefix.length) {
                e.preventDefault();
                return;
            }

            // Prevenir Home, mover para após o prefixo
            if (e.key === 'Home') {
                e.preventDefault();
                this.setSelectionRange(prefix.length, prefix.length);
                return;
            }

            // Apenas números após o prefixo
            if (cursorPos >= prefix.length && e.key.length === 1) {
                if (!/[0-9]/.test(e.key) && e.key !== ' ') {
                    e.preventDefault();
                }
            }
        });

        // Garantir prefixo ao digitar
        phoneInput.addEventListener('input', function (e) {
            let value = this.value;

            // Se tentou remover o prefixo, restaurar
            if (!value.startsWith(prefix)) {
                const digitsOnly = value.replace(/\D/g, '');
                if (digitsOnly.startsWith('258')) {
                    this.value = '+' + digitsOnly.substring(0, 3) + ' ' + digitsOnly.substring(3, 14);
                } else {
                    this.value = prefix + digitsOnly.substring(0, 11);
                }
            }

            // Formatar: +258 8X XXX XXXX ou +258 8X XXX XXXXXXX (9-11 dígitos)
            if (value.startsWith(prefix)) {
                const digitsAfterPrefix = value.substring(prefix.length).replace(/\D/g, '');

                if (digitsAfterPrefix.length > 0) {
                    let formatted = prefix;

                    // Primeiros 2 dígitos (8X)
                    formatted += digitsAfterPrefix.substring(0, 2);

                    // Adicionar espaço
                    if (digitsAfterPrefix.length > 2) {
                        formatted += ' ' + digitsAfterPrefix.substring(2, 5);
                    }

                    // Adicionar espaço (até 11 dígitos)
                    if (digitsAfterPrefix.length > 5) {
                        formatted += ' ' + digitsAfterPrefix.substring(5, 11);
                    }

                    this.value = formatted;
                }
            }
        });

        // Prevenir colar texto sem prefixo
        phoneInput.addEventListener('paste', function (e) {
            e.preventDefault();
            const pastedText = (e.clipboardData || window.clipboardData).getData('text');
            const digitsOnly = pastedText.replace(/\D/g, '');

            if (digitsOnly.startsWith('258')) {
                this.value = '+' + digitsOnly.substring(0, 3) + ' ' + digitsOnly.substring(3, 14);
            } else {
                this.value = prefix + digitsOnly.substring(0, 11);
            }
        });

        // Prevenir seleção do prefixo
        phoneInput.addEventListener('mouseup', function () {
            if (this.selectionStart < prefix.length) {
                this.setSelectionRange(prefix.length, this.selectionEnd);
            }
        });
    });
</script>