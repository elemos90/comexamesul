<?php
$title = 'Minhas Candidaturas';
$breadcrumbs = [
    ['label' => 'Candidaturas']
];
?>
<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div>
        <h1 class="text-2xl font-semibold text-gray-800">Minhas Candidaturas</h1>
        <p class="mt-2 text-sm text-gray-600">Gerencie suas candidaturas às vagas abertas para participar como vigilante
            nos exames de admissão</p>
    </div>

    <?php if (!$profileComplete): ?>
        <!-- Alerta de Perfil Incompleto -->
        <div class="bg-red-50 border-2 border-red-200 rounded-lg p-6">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-red-900 mb-2">Complete seu Perfil para se Candidatar</h3>
                    <p class="text-sm text-red-800 mb-4">
                        Para se candidatar a vagas, você precisa completar os seguintes campos do seu perfil:
                    </p>
                    <ul class="list-disc list-inside text-sm text-red-800 mb-4 space-y-1">
                        <?php foreach ($missingFields as $field => $label): ?>
                            <li><strong><?= htmlspecialchars($label) ?></strong></li>
                        <?php endforeach; ?>
                    </ul>
                    <a href="<?= url('/profile') ?>"
                        class="inline-flex items-center gap-2 px-5 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Completar Perfil Agora
                    </a>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Minhas Candidaturas -->
    <?php if (!empty($myApplications)): ?>
        <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Minhas Candidaturas</h2>
            <div class="space-y-3">
                <?php foreach ($myApplications as $app): ?>
                    <?php
                    $statusColors = [
                        'pendente' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                        'aprovada' => 'bg-green-100 text-green-700 border-green-200',
                        'rejeitada' => 'bg-red-100 text-red-700 border-red-200',
                        'cancelada' => 'bg-gray-100 text-gray-600 border-gray-200',
                    ];
                    $statusClass = $statusColors[$app['status']] ?? 'bg-gray-100 text-gray-600';
                    ?>
                    <div
                        class="border border-gray-200 rounded-lg p-4 <?= $app['status'] === 'aprovada' ? 'bg-green-50' : '' ?>">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($app['vacancy_title']) ?></h3>
                                <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-gray-600">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                        Candidatou-se: <?= htmlspecialchars(date('d/m/Y', strtotime($app['applied_at']))) ?>
                                    </span>
                                    <span class="flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Prazo: <?= htmlspecialchars(date('d/m/Y', strtotime($app['deadline_at']))) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="px-3 py-1.5 rounded-full text-xs font-semibold border <?= $statusClass ?>">
                                    <?= htmlspecialchars(ucfirst($app['status'])) ?>
                                </span>

                                <?php if ($app['vacancy_status'] === 'fechada' || $app['vacancy_status'] === 'encerrada'): ?>
                                    <!-- Vaga fechada/encerrada - não permitir alterações -->
                                    <?php
                                    $lockMsg = $app['vacancy_status'] === 'encerrada'
                                        ? 'Vaga encerrada e arquivada'
                                        : 'Vaga fechada pelo coordenador';
                                    $lockColor = $app['vacancy_status'] === 'encerrada'
                                        ? 'bg-purple-100 text-purple-600'
                                        : 'bg-gray-100 text-gray-500';
                                    ?>
                                    <span class="px-3 py-1.5 text-xs font-medium <?= $lockColor ?> rounded cursor-not-allowed"
                                        title="<?= $lockMsg ?>">
                                        <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                    </span>
                                <?php else: ?>
                                    <?php if ($app['status'] === 'pendente'): ?>
                                        <!-- Cancelar candidatura pendente (direto, sem justificativa) -->
                                        <form method="POST" action="<?= url('/applications/' . $app['id'] . '/cancel-direct') ?>"
                                            class="inline" onsubmit="return confirm('Deseja realmente cancelar esta candidatura?');">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors"
                                                title="Cancelar candidatura">
                                                Cancelar
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <?php if ($app['status'] === 'aprovada'): ?>
                                        <!-- Cancelar candidatura aprovada (pode exigir justificativa se alocado) -->
                                        <a href="url('/availability/<?= $app['id'] ?>/cancel"
                                            class="px-3 py-1.5 text-xs font-medium bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors"
                                            title="Solicitar cancelamento">
                                            Cancelar
                                        </a>
                                    <?php endif; ?>

                                    <?php if (in_array($app['status'], ['cancelada', 'rejeitada'])): ?>
                                        <!-- Recandidatar-se -->
                                        <form method="POST" action="<?= url('/applications/' . $app['id'] . '/reapply') ?>"
                                            class="inline" onsubmit="return confirm('Deseja recandidatar-se a esta vaga?');">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <button type="submit"
                                                class="px-3 py-1.5 text-xs font-medium bg-primary-600 text-white rounded hover:bg-primary-700 transition-colors"
                                                title="Recandidatar-se">
                                                Recandidatar-se
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Vagas Abertas -->
    <div class="bg-white border border-gray-100 rounded-lg shadow-sm p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Vagas Abertas</h2>

        <?php if (!empty($openVacancies)): ?>
            <div class="grid md:grid-cols-2 gap-4">
                <?php foreach ($openVacancies as $vacancy): ?>
                    <?php
                    // Verificar candidatura e seu status
                    $myApplication = null;
                    foreach ($myApplications as $app) {
                        if ($app['vacancy_id'] == $vacancy['id']) {
                            $myApplication = $app;
                            break;
                        }
                    }

                    $deadline = new DateTime($vacancy['deadline_at']);
                    $now = new DateTime();
                    $diff = $now->diff($deadline);
                    $daysLeft = $diff->days;
                    $expired = $deadline < $now;

                    // Definir cores do card baseado no status
                    $cardClass = 'border-gray-200';
                    if ($myApplication) {
                        if ($myApplication['status'] === 'aprovada') {
                            $cardClass = 'border-green-400 bg-green-50';
                        } elseif ($myApplication['status'] === 'pendente') {
                            $cardClass = 'border-yellow-400 bg-yellow-50';
                        } elseif ($myApplication['status'] === 'rejeitada') {
                            $cardClass = 'border-red-300 bg-red-50';
                        } elseif ($myApplication['status'] === 'cancelada') {
                            $cardClass = 'border-gray-300 bg-gray-50';
                        }
                    }
                    ?>
                    <div class="border-2 <?= $cardClass ?> rounded-lg p-5">
                        <div class="flex items-start justify-between gap-3 mb-3">
                            <h3 class="font-bold text-lg text-gray-800"><?= htmlspecialchars($vacancy['title']) ?></h3>
                            <?php if ($daysLeft <= 3 && !$expired): ?>
                                <span
                                    class="flex-shrink-0 px-2 py-1 bg-amber-100 text-amber-700 text-xs font-semibold rounded-full">
                                    <?= $daysLeft ?> dia(s)
                                </span>
                            <?php endif; ?>
                        </div>

                        <p class="text-sm text-gray-600 mb-4 line-clamp-3"><?= htmlspecialchars($vacancy['description']) ?></p>

                        <div class="mb-4 text-sm text-gray-600 space-y-1">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Prazo:
                                    <strong><?= htmlspecialchars(date('d/m/Y H:i', strtotime($vacancy['deadline_at']))) ?></strong></span>
                            </div>
                        </div>

                        <?php if ($myApplication): ?>
                            <!-- Mostrar status da candidatura -->
                            <?php if ($myApplication['status'] === 'aprovada'): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-green-700">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Candidatura Aprovada
                                    </div>
                                    <span class="px-2 py-1 bg-green-200 text-green-800 text-xs font-bold rounded">APROVADO</span>
                                </div>
                            <?php elseif ($myApplication['status'] === 'pendente'): ?>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-sm font-semibold text-yellow-700">
                                        <svg class="w-5 h-5 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Aguardando Aprovação
                                    </div>
                                    <span class="px-2 py-1 bg-yellow-200 text-yellow-800 text-xs font-bold rounded">PENDENTE</span>
                                </div>
                                <?php if ($vacancy['status'] === 'aberta'): ?>
                                    <form method="POST" action="<?= url('/applications/' . $myApplication['id'] . '/cancel-direct') ?>"
                                        class="mt-3" onsubmit="return confirm('Deseja cancelar esta candidatura?');">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                        <button type="submit"
                                            class="w-full px-3 py-1.5 text-xs font-medium bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
                                            Cancelar Candidatura
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <div class="mt-3 px-3 py-2 bg-gray-100 text-gray-600 text-xs text-center rounded">
                                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                        </svg>
                                        Vaga encerrada
                                    </div>
                                <?php endif; ?>
                            <?php elseif ($myApplication['status'] === 'rejeitada'): ?>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-sm font-semibold text-red-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Candidatura Rejeitada
                                        </div>
                                        <span class="px-2 py-1 bg-red-200 text-red-800 text-xs font-bold rounded">REJEITADO</span>
                                    </div>
                                    <?php if (!$expired && $vacancy['status'] === 'aberta'): ?>
                                        <form method="POST" action="<?= url('/applications/' . $myApplication['id'] . '/reapply') ?>"
                                            onsubmit="return confirm('Deseja recandidatar-se a esta vaga?');">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <button type="submit"
                                                class="w-full px-3 py-2 text-sm font-medium bg-primary-600 text-white rounded hover:bg-primary-700 transition-colors">
                                                Recandidatar-me
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <?php
                                        $lockMsg = $vacancy['status'] === 'encerrada' ? 'Vaga encerrada e arquivada' : 'Vaga fechada';
                                        $lockColor = $vacancy['status'] === 'encerrada' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600';
                                        ?>
                                        <div class="px-3 py-2 <?= $lockColor ?> text-xs text-center rounded">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <?= $lockMsg ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php elseif ($myApplication['status'] === 'cancelada'): ?>
                                <div class="space-y-3">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 text-sm font-semibold text-gray-700">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                            </svg>
                                            Candidatura Cancelada
                                        </div>
                                        <span class="px-2 py-1 bg-gray-300 text-gray-700 text-xs font-bold rounded">CANCELADO</span>
                                    </div>
                                    <?php if (!$expired && $vacancy['status'] === 'aberta'): ?>
                                        <form method="POST" action="<?= url('/applications/' . $myApplication['id'] . '/reapply') ?>"
                                            onsubmit="return confirm('Deseja recandidatar-se a esta vaga?');">
                                            <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                            <button type="submit"
                                                class="w-full px-3 py-2 text-sm font-medium bg-primary-600 text-white rounded hover:bg-primary-700 transition-colors">
                                                Recandidatar-me
                                            </button>
                                        </form>
                                    <?php else: ?>
                                        <?php
                                        $lockMsg = $vacancy['status'] === 'encerrada' ? 'Vaga encerrada e arquivada' : 'Vaga fechada';
                                        $lockColor = $vacancy['status'] === 'encerrada' ? 'bg-purple-100 text-purple-700' : 'bg-gray-100 text-gray-600';
                                        ?>
                                        <div class="px-3 py-2 <?= $lockColor ?> text-xs text-center rounded">
                                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                            <?= $lockMsg ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($expired): ?>
                            <div class="flex items-center gap-2 text-sm font-medium text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Prazo Encerrado
                            </div>
                        <?php elseif (!$profileComplete): ?>
                            <button disabled
                                class="w-full px-4 py-2 bg-gray-300 text-gray-600 font-medium rounded cursor-not-allowed"
                                title="Complete seu perfil para se candidatar">
                                <div class="flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                    </svg>
                                    Complete o Perfil
                                </div>
                            </button>
                        <?php else: ?>
                            <form method="POST" action="<?= url('/vacancies/' . $vacancy['id'] . '/apply') ?>">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars(csrf_token()) ?>">
                                <button type="submit"
                                    class="w-full px-4 py-2 bg-primary-600 text-white font-semibold rounded hover:bg-primary-700 transition-colors">
                                    <div class="flex items-center justify-center gap-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                        </svg>
                                        Candidatar-me
                                    </div>
                                </button>
                            </form>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="text-lg font-semibold text-gray-700 mb-2">Sem vagas abertas no momento</h3>
                <p class="text-gray-500">Novas oportunidades serão publicadas em breve.</p>
            </div>
        <?php endif; ?>
    </div>
</div>