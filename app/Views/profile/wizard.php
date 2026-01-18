<?php
$title = 'Completar Perfil';
$isPublic = false;

$user = $user ?? [];
$progress = $progress ?? 0;
$missingFields = $missingFields ?? [];
$requiredFields = $requiredFields ?? [];
$optionalFields = $optionalFields ?? [];
$errors = validation_errors();
?>

<div class="max-w-4xl mx-auto">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">Completar Perfil</h1>
        <p class="mt-1 text-sm text-gray-600">
            Complete os seus dados para poder candidatar-se a vagas, ser alocado a júris e receber pagamentos.
        </p>
    </div>

    <!-- Progress Bar -->
    <div class="mb-6 bg-white rounded-lg shadow p-4">
        <div class="flex items-center justify-between mb-2">
            <span class="text-sm font-medium text-gray-700">Progresso do perfil</span>
            <span class="text-sm font-semibold text-primary-600">
                <?= $progress ?>%
            </span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-3">
            <div class="bg-primary-600 h-3 rounded-full transition-all duration-300" style="width: <?= $progress ?>%">
            </div>
        </div>
        <?php if (!empty($missingFields)): ?>
            <p class="mt-2 text-xs text-amber-600">
                <strong>Campos em falta:</strong>
                <?= implode(', ', array_values($missingFields)) ?>
            </p>
        <?php endif; ?>
    </div>

    <!-- Info Box -->
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-blue-900">Porquê completar o perfil?</h3>
                <ul class="text-xs text-blue-700 mt-1 list-disc list-inside space-y-1">
                    <li>Para se candidatar a vagas de vigilância</li>
                    <li>Para ser alocado a júris de exames</li>
                    <li>Para receber pagamentos correctamente</li>
                    <li>Para aparecer nos relatórios e mapas</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Form -->
    <form method="POST" action="<?= url('/profile/wizard') ?>" class="space-y-6">
        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">

        <!-- Secção A: Dados Pessoais -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 rounded-t-lg">
                <h2 class="text-lg font-semibold text-gray-900">
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 bg-primary-600 text-white text-sm rounded-full mr-2">A</span>
                    Dados Pessoais
                </h2>
            </div>
            <div class="p-4 grid md:grid-cols-2 gap-4">
                <!-- Nome -->
                <div class="md:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700">Nome completo *</label>
                    <input type="text" id="name" name="name"
                        value="<?= htmlspecialchars(old('name', $user['name'] ?? '')) ?>"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                        required>
                    <?php if (!empty($errors['name'])): ?>
                        <p class="mt-1 text-xs text-red-600">
                            <?= htmlspecialchars($errors['name'][0]) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Género -->
                <div>
                    <label for="gender" class="block text-sm font-medium text-gray-700">Género *</label>
                    <select id="gender" name="gender"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                        required>
                        <option value="">Seleccione...</option>
                        <option value="M" <?= old('gender', $user['gender'] ?? '') === 'M' ? 'selected' : '' ?>>Masculino
                        </option>
                        <option value="F" <?= old('gender', $user['gender'] ?? '') === 'F' ? 'selected' : '' ?>>Feminino
                        </option>
                        <option value="O" <?= old('gender', $user['gender'] ?? '') === 'O' ? 'selected' : '' ?>>Outro
                        </option>
                    </select>
                    <?php if (!empty($errors['gender'])): ?>
                        <p class="mt-1 text-xs text-red-600">
                            <?= htmlspecialchars($errors['gender'][0]) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Data de Nascimento -->
                <div>
                    <label for="birth_date" class="block text-sm font-medium text-gray-700">Data de nascimento</label>
                    <input type="date" id="birth_date" name="birth_date"
                        value="<?= htmlspecialchars(old('birth_date', $user['birth_date'] ?? '')) ?>"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Tipo de Documento -->
                <div>
                    <label for="document_type" class="block text-sm font-medium text-gray-700">Tipo de documento</label>
                    <select id="document_type" name="document_type"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Seleccione...</option>
                        <option value="BI" <?= old('document_type', $user['document_type'] ?? '') === 'BI' ? 'selected' : '' ?>>Bilhete de Identidade</option>
                        <option value="Passaporte" <?= old('document_type', $user['document_type'] ?? '') === 'Passaporte' ? 'selected' : '' ?>>Passaporte</option>
                        <option value="DIRE" <?= old('document_type', $user['document_type'] ?? '') === 'DIRE' ? 'selected' : '' ?>>DIRE</option>
                    </select>
                </div>

                <!-- Número do Documento -->
                <div>
                    <label for="document_number" class="block text-sm font-medium text-gray-700">Número do
                        documento</label>
                    <input type="text" id="document_number" name="document_number"
                        value="<?= htmlspecialchars(old('document_number', $user['document_number'] ?? '')) ?>"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Telefone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">Telefone *</label>
                    <input type="tel" id="phone" name="phone"
                        value="<?= htmlspecialchars(old('phone', $user['phone'] ?? '')) ?>"
                        placeholder="84xxxxxxx ou 82xxxxxxx"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                        required>
                    <?php if (!empty($errors['phone'])): ?>
                        <p class="mt-1 text-xs text-red-600">
                            <?= htmlspecialchars($errors['phone'][0]) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- NUIT -->
                <div>
                    <label for="nuit" class="block text-sm font-medium text-gray-700">NUIT *</label>
                    <input type="text" id="nuit" name="nuit"
                        value="<?= htmlspecialchars(old('nuit', $user['nuit'] ?? '')) ?>" placeholder="9 dígitos"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                        required>
                    <?php if (!empty($errors['nuit'])): ?>
                        <p class="mt-1 text-xs text-red-600">
                            <?= htmlspecialchars($errors['nuit'][0]) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Secção B: Dados Académicos -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 rounded-t-lg">
                <h2 class="text-lg font-semibold text-gray-900">
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 bg-gray-400 text-white text-sm rounded-full mr-2">B</span>
                    Dados Académicos <span class="text-sm font-normal text-gray-500">(opcional)</span>
                </h2>
            </div>
            <div class="p-4 grid md:grid-cols-2 gap-4">
                <!-- Grau -->
                <div>
                    <label for="degree" class="block text-sm font-medium text-gray-700">Grau académico</label>
                    <select id="degree" name="degree"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500">
                        <option value="">Seleccione...</option>
                        <option value="Licenciado" <?= old('degree', $user['degree'] ?? '') === 'Licenciado' ? 'selected' : '' ?>>Licenciado</option>
                        <option value="Mestre" <?= old('degree', $user['degree'] ?? '') === 'Mestre' ? 'selected' : '' ?>>
                            Mestre</option>
                        <option value="Doutor" <?= old('degree', $user['degree'] ?? '') === 'Doutor' ? 'selected' : '' ?>>
                            Doutor</option>
                        <option value="Outro" <?= old('degree', $user['degree'] ?? '') === 'Outro' ? 'selected' : '' ?>>
                            Outro</option>
                    </select>
                </div>

                <!-- Área -->
                <div>
                    <label for="major_area" class="block text-sm font-medium text-gray-700">Área de formação</label>
                    <input type="text" id="major_area" name="major_area"
                        value="<?= htmlspecialchars(old('major_area', $user['major_area'] ?? '')) ?>"
                        placeholder="ex: Ciências da Educação"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500">
                </div>

                <!-- Instituição de Origem -->
                <div class="md:col-span-2">
                    <label for="origin_university" class="block text-sm font-medium text-gray-700">Universidade de
                        Origem *</label>
                    <select id="origin_university" name="origin_university"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500">
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
                            <option value="<?= htmlspecialchars($uni) ?>" <?= old('origin_university', $user['origin_university'] ?? '') === $uni ? 'selected' : '' ?>><?= htmlspecialchars($uni) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Universidade Atual -->
                <div class="md:col-span-2">
                    <label for="university" class="block text-sm font-medium text-gray-700">Universidade / Instituição
                        Atual *</label>
                    <input type="text" id="university" name="university"
                        value="<?= htmlspecialchars(old('university', $user['university'] ?? '')) ?>"
                        placeholder="Ex: Universidade Licungo"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                        required>
                </div>
            </div>
        </div>

        <!-- Secção C: Dados Bancários -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 bg-amber-50 border-b border-amber-200 rounded-t-lg">
                <h2 class="text-lg font-semibold text-gray-900">
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 bg-amber-500 text-white text-sm rounded-full mr-2">C</span>
                    Dados Bancários <span class="text-sm font-normal text-amber-700">(obrigatório para
                        pagamentos)</span>
                </h2>
            </div>
            <div class="p-4 grid md:grid-cols-2 gap-4">
                <!-- Banco -->
                <div>
                    <label for="bank_name" class="block text-sm font-medium text-gray-700">Banco *</label>
                    <select id="bank_name" name="bank_name"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                        required>
                        <option value="">Seleccione...</option>
                        <option value="BCI" <?= old('bank_name', $user['bank_name'] ?? '') === 'BCI' ? 'selected' : '' ?>>
                            BCI - Banco Comercial e de Investimentos</option>
                        <option value="Millennium BIM" <?= old('bank_name', $user['bank_name'] ?? '') === 'Millennium BIM' ? 'selected' : '' ?>>Millennium BIM</option>
                        <option value="Standard Bank" <?= old('bank_name', $user['bank_name'] ?? '') === 'Standard Bank' ? 'selected' : '' ?>>Standard Bank</option>
                        <option value="BCI Fomento" <?= old('bank_name', $user['bank_name'] ?? '') === 'BCI Fomento' ? 'selected' : '' ?>>BCI Fomento</option>
                        <option value="Absa" <?= old('bank_name', $user['bank_name'] ?? '') === 'Absa' ? 'selected' : '' ?>>Absa</option>
                        <option value="FNB" <?= old('bank_name', $user['bank_name'] ?? '') === 'FNB' ? 'selected' : '' ?>>
                            FNB Moçambique</option>
                        <option value="Moza Banco" <?= old('bank_name', $user['bank_name'] ?? '') === 'Moza Banco' ? 'selected' : '' ?>>Moza Banco</option>
                        <option value="Letshego" <?= old('bank_name', $user['bank_name'] ?? '') === 'Letshego' ? 'selected' : '' ?>>Letshego</option>
                        <option value="M-Pesa" <?= old('bank_name', $user['bank_name'] ?? '') === 'M-Pesa' ? 'selected' : '' ?>>M-Pesa</option>
                        <option value="e-Mola" <?= old('bank_name', $user['bank_name'] ?? '') === 'e-Mola' ? 'selected' : '' ?>>e-Mola</option>
                        <option value="Outro" <?= old('bank_name', $user['bank_name'] ?? '') === 'Outro' ? 'selected' : '' ?>>Outro</option>
                    </select>
                    <?php if (!empty($errors['bank_name'])): ?>
                        <p class="mt-1 text-xs text-red-600">
                            <?= htmlspecialchars($errors['bank_name'][0]) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- NIB -->
                <div>
                    <label for="nib" class="block text-sm font-medium text-gray-700">NIB / Nº de conta *</label>
                    <input type="text" id="nib" name="nib"
                        value="<?= htmlspecialchars(old('nib', $user['nib'] ?? '')) ?>"
                        placeholder="Número de conta bancária"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                        required>
                    <?php if (!empty($errors['nib'])): ?>
                        <p class="mt-1 text-xs text-red-600">
                            <?= htmlspecialchars($errors['nib'][0]) ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Titular da Conta -->
                <div class="md:col-span-2">
                    <label for="bank_account_holder" class="block text-sm font-medium text-gray-700">Titular da conta
                        *</label>
                    <input type="text" id="bank_account_holder" name="bank_account_holder"
                        value="<?= htmlspecialchars(old('bank_account_holder', $user['bank_account_holder'] ?? $user['name'] ?? '')) ?>"
                        placeholder="Nome conforme aparece na conta bancária"
                        class="mt-1 w-full rounded border border-gray-300 px-3 py-2 focus:ring-primary-500 focus:border-primary-500"
                        required>
                    <?php if (!empty($errors['bank_account_holder'])): ?>
                        <p class="mt-1 text-xs text-red-600">
                            <?= htmlspecialchars($errors['bank_account_holder'][0]) ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Secção D: Declarações -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 bg-gray-50 border-b border-gray-200 rounded-t-lg">
                <h2 class="text-lg font-semibold text-gray-900">
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 bg-primary-600 text-white text-sm rounded-full mr-2">D</span>
                    Declarações
                </h2>
            </div>
            <div class="p-4 space-y-4">
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="terms_accepted" name="terms_accepted" value="1"
                        class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" required>
                    <label for="terms_accepted" class="text-sm text-gray-700">
                        Declaro que os dados fornecidos são verdadeiros e correctos. *
                    </label>
                </div>
                <div class="flex items-start gap-3">
                    <input type="checkbox" id="data_authorization" name="data_authorization" value="1"
                        class="mt-1 h-4 w-4 text-primary-600 focus:ring-primary-500 border-gray-300 rounded" required>
                    <label for="data_authorization" class="text-sm text-gray-700">
                        Autorizo o uso dos meus dados bancários para processamento de pagamentos de vigilância. *
                    </label>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end gap-3">
            <a href="<?= url('/logout') ?>"
                class="px-6 py-2 border border-gray-300 text-gray-700 rounded hover:bg-gray-50">
                Sair
            </a>
            <button type="submit"
                class="px-6 py-2 bg-primary-600 text-white font-semibold rounded hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Guardar e Continuar
            </button>
        </div>
    </form>
</div>