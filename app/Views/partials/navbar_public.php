<?php
$vacancyModel = new \App\Models\ExamVacancy();
$openVacanciesCount = count($vacancyModel->openVacancies());
?>
<nav class="bg-white border-b border-gray-200 shadow-sm fixed top-0 left-0 right-0 z-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">
            <a href="<?= url('/') ?>" class="flex items-center gap-2">
                <img src="<?= url('/assets/images/logo_unilicungo.png') ?>" alt="UniLicungo"
                    class="h-10 w-auto object-contain">
                <span
                    class="text-lg font-bold text-primary-600 hidden md:inline"><?= htmlspecialchars(env('APP_NAME', 'Portal')) ?></span>
            </a>
            <div class="flex items-center gap-2 sm:gap-4">
                <?php if ($openVacanciesCount > 0): ?>
                    <!-- Badge de vagas abertas - versão mobile compacta -->
                    <a href="<?= url('/login') ?>"
                        class="inline-flex items-center gap-1 sm:gap-2 px-2 sm:px-3 py-2 bg-amber-50 border border-amber-200 text-amber-700 text-xs sm:text-sm font-semibold rounded-lg hover:bg-amber-100 transition-colors"
                        title="Entre para se candidatar às vagas">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span class="hidden xs:inline"><?= $openVacanciesCount ?>
                            Vaga<?= $openVacanciesCount > 1 ? 's' : '' ?></span>
                        <span class="inline xs:hidden"><?= $openVacanciesCount ?></span>
                        <span class="flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-2 w-2 rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                    </a>
                <?php endif; ?>
                <a href="<?= url('/login') ?>"
                    class="hidden sm:block text-sm font-medium text-gray-700 hover:text-primary-600">Entrar</a>
                <a href="<?= url('/register') ?>"
                    class="px-3 sm:px-4 py-2 text-xs sm:text-sm font-semibold text-white bg-primary-600 hover:bg-primary-500 rounded-lg shadow-sm transition-colors">Registar</a>
            </div>
        </div>
    </div>
</nav>