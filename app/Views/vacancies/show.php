<?php
$title = 'Detalhe da vaga';
$breadcrumbs = [
    ['label' => 'Vagas', 'url' => '/vacancies'],
    ['label' => $vacancy['title'] ?? 'Detalhe']
];
$isCoordinator = in_array($user['role'], ['coordenador', 'membro'], true);
$isVigilante = $user['role'] === 'vigilante';
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
        <div class="flex items-start justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-gray-800"><?= htmlspecialchars($vacancy['title']) ?></h1>
                <p class="mt-2 text-sm text-gray-600 whitespace-pre-line"><?= nl2br(htmlspecialchars($vacancy['description'])) ?></p>
            </div>
            <div class="text-sm text-gray-600">
                <p><span class="font-medium">Limite:</span> <?= htmlspecialchars(date('d/m/Y H:i', strtotime($vacancy['deadline_at']))) ?></p>
                <p class="mt-1"><span class="font-medium">Estado:</span> <?= htmlspecialchars($vacancy['status']) ?></p>
            </div>
        </div>
    </div>

    <?php if ($isVigilante): ?>
        <div class="bg-gradient-to-r from-blue-50 to-primary-50 border-2 border-primary-200 rounded-xl shadow-sm p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-primary-600 rounded-full flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-gray-800 mb-2">Como me Candidatar?</h3>
                    <p class="text-sm text-gray-700 mb-4 leading-relaxed">
                        Para se candidatar a esta vaga, você deve atualizar sua <strong>disponibilidade para vigilância</strong>. 
                        A comissão analisará os vigilantes disponíveis e fará as alocações necessárias.
                    </p>
                    <div class="flex flex-wrap gap-3">
                        <a href="/availability" class="inline-flex items-center gap-2 px-5 py-3 bg-primary-600 text-white font-semibold rounded-lg hover:bg-primary-500 transition-colors shadow-sm">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Atualizar Disponibilidade
                        </a>
                        <a href="/juries" class="inline-flex items-center gap-2 px-5 py-3 bg-white border-2 border-gray-300 text-gray-700 font-semibold rounded-lg hover:bg-gray-50 transition-colors">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            Ver Júris Agendados
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($isCoordinator): ?>
        <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-3">Proximo passo</h2>
            <p class="text-sm text-gray-600 mb-4">
                Utilize o modulo de juris para alocar vigilantes disponiveis a esta vigia. A lista actual de vigilantes com disponibilidade activa possui
                <span class="font-semibold"><?= (int) ($availableCount ?? 0) ?></span> registos.
            </p>
            <a href="/juries" class="inline-flex items-center px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded hover:bg-primary-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                Gerir alocacoes de juris
            </a>
        </div>
    <?php endif; ?>
</div>
