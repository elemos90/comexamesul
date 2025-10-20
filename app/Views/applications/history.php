<?php
$title = 'Histórico da Candidatura';
$breadcrumbs = [
    ['label' => 'Candidaturas', 'url' => '/applications'],
    ['label' => 'Histórico']
];
?>

<div class="space-y-6">
    <?php include view_path('partials/breadcrumbs.php'); ?>

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold text-gray-800">Histórico da Candidatura</h1>
            <p class="mt-2 text-sm text-gray-600">Timeline completa de todas as mudanças de status</p>
        </div>
        <a href="/applications?vacancy=<?= $application['vacancy_id'] ?>" class="px-4 py-2 bg-gray-100 text-gray-700 rounded hover:bg-gray-200 transition-colors">
            ← Voltar
        </a>
    </div>

    <!-- Informações da Candidatura -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Informações</h2>
        
        <div class="grid md:grid-cols-2 gap-6">
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Vaga</h3>
                <p class="text-base font-semibold text-gray-800"><?= htmlspecialchars($vacancy['title']) ?></p>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Vigilante</h3>
                <p class="text-base font-semibold text-gray-800"><?= htmlspecialchars($vigilante['name']) ?></p>
                <p class="text-sm text-gray-600"><?= htmlspecialchars($vigilante['email']) ?></p>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Status Atual</h3>
                <?php
                $statusColors = [
                    'pendente' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                    'aprovada' => 'bg-green-100 text-green-700 border-green-200',
                    'rejeitada' => 'bg-red-100 text-red-700 border-red-200',
                    'cancelada' => 'bg-gray-100 text-gray-600 border-gray-200',
                ];
                $statusClass = $statusColors[$application['status']] ?? 'bg-gray-100 text-gray-600';
                ?>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold border <?= $statusClass ?>">
                    <?= htmlspecialchars(ucfirst($application['status'])) ?>
                </span>
            </div>
            
            <div>
                <h3 class="text-sm font-medium text-gray-500 mb-2">Recandidaturas</h3>
                <p class="text-base text-gray-800"><?= (int) ($application['reapply_count'] ?? 0) ?> / 3</p>
            </div>
        </div>

        <?php if ($application['rejection_reason']): ?>
            <div class="mt-4 p-4 bg-red-50 border-l-4 border-red-400 rounded">
                <h3 class="text-sm font-semibold text-red-800 mb-1">Motivo da Rejeição:</h3>
                <p class="text-sm text-red-700"><?= nl2br(htmlspecialchars($application['rejection_reason'])) ?></p>
            </div>
        <?php endif; ?>
    </div>

    <!-- Timeline do Histórico -->
    <div class="bg-white border border-gray-200 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-6">Timeline de Mudanças</h2>
        
        <?php if (empty($history)): ?>
            <div class="text-center py-8 text-gray-500">
                <p>Nenhum histórico disponível.</p>
            </div>
        <?php else: ?>
            <div class="relative">
                <!-- Linha vertical -->
                <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-gray-200"></div>
                
                <div class="space-y-6">
                    <?php foreach ($history as $index => $entry): ?>
                        <?php
                        $isFirst = $index === 0;
                        $isLast = $index === count($history) - 1;
                        
                        $statusIcons = [
                            'pendente' => '🟡',
                            'aprovada' => '🟢',
                            'rejeitada' => '🔴',
                            'cancelada' => '⚫',
                        ];
                        
                        $statusColors = [
                            'pendente' => 'bg-yellow-100 border-yellow-300',
                            'aprovada' => 'bg-green-100 border-green-300',
                            'rejeitada' => 'bg-red-100 border-red-300',
                            'cancelada' => 'bg-gray-100 border-gray-300',
                        ];
                        
                        $newStatusIcon = $statusIcons[$entry['new_status']] ?? '⚪';
                        $newStatusColor = $statusColors[$entry['new_status']] ?? 'bg-gray-100 border-gray-300';
                        ?>
                        
                        <div class="relative pl-12">
                            <!-- Ícone -->
                            <div class="absolute left-0 w-8 h-8 rounded-full border-2 flex items-center justify-center <?= $newStatusColor ?>">
                                <span class="text-lg"><?= $newStatusIcon ?></span>
                            </div>
                            
                            <!-- Conteúdo -->
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <div>
                                        <?php if ($entry['old_status']): ?>
                                            <p class="text-sm text-gray-600">
                                                <span class="font-medium"><?= htmlspecialchars(ucfirst($entry['old_status'])) ?></span>
                                                <span class="mx-2">→</span>
                                                <span class="font-semibold text-gray-800"><?= htmlspecialchars(ucfirst($entry['new_status'])) ?></span>
                                            </p>
                                        <?php else: ?>
                                            <p class="text-sm font-semibold text-gray-800">
                                                <?= htmlspecialchars(ucfirst($entry['new_status'])) ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <p class="text-xs text-gray-500 mt-1">
                                            <?= date('d/m/Y H:i', strtotime($entry['changed_at'])) ?>
                                        </p>
                                    </div>
                                    
                                    <?php if ($entry['changed_by_name']): ?>
                                        <div class="text-right">
                                            <p class="text-xs text-gray-500">Por:</p>
                                            <p class="text-sm font-medium text-gray-700"><?= htmlspecialchars($entry['changed_by_name']) ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <?php if ($entry['reason']): ?>
                                    <div class="mt-2 pt-2 border-t border-gray-200">
                                        <p class="text-xs font-medium text-gray-500 mb-1">Observação:</p>
                                        <p class="text-sm text-gray-700"><?= nl2br(htmlspecialchars($entry['reason'])) ?></p>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($entry['metadata']): ?>
                                    <?php $metadata = json_decode($entry['metadata'], true); ?>
                                    <?php if ($metadata): ?>
                                        <div class="mt-2 pt-2 border-t border-gray-200">
                                            <details class="text-xs">
                                                <summary class="cursor-pointer text-gray-500 hover:text-gray-700">Detalhes técnicos</summary>
                                                <pre class="mt-2 p-2 bg-gray-100 rounded text-xs overflow-x-auto"><?= htmlspecialchars(json_encode($metadata, JSON_PRETTY_PRINT)) ?></pre>
                                            </details>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Estatísticas -->
    <div class="grid md:grid-cols-3 gap-4">
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Total de Mudanças</h3>
            <p class="text-3xl font-bold text-gray-800"><?= count($history) ?></p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Data de Criação</h3>
            <p class="text-lg font-semibold text-gray-800"><?= date('d/m/Y H:i', strtotime($application['applied_at'])) ?></p>
        </div>
        
        <div class="bg-white border border-gray-200 rounded-lg p-4">
            <h3 class="text-sm font-medium text-gray-500 mb-2">Última Atualização</h3>
            <p class="text-lg font-semibold text-gray-800"><?= date('d/m/Y H:i', strtotime($application['updated_at'])) ?></p>
        </div>
    </div>
</div>
