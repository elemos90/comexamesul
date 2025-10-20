#!/usr/bin/env php
<?php

/**
 * Cron Job: Verificar e Fechar Prazos de Candidaturas
 * Executa a cada hora para verificar e fechar vagas expiradas
 * 
 * Configuração no crontab:
 * 0 * * * * php /caminho/para/scripts/check_deadlines_cron.php >> /caminho/para/storage/logs/deadlines.log 2>&1
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Services\EmailNotificationService;
use App\Models\ExamVacancy;

$startTime = microtime(true);
echo "[" . date('Y-m-d H:i:s') . "] Verificando prazos de candidaturas...\n";

try {
    $vacancyModel = new ExamVacancy();
    $emailService = new EmailNotificationService();

    // 1. FECHAR VAGAS EXPIRADAS AUTOMATICAMENTE
    echo "\n1️⃣ Fechando vagas expiradas...\n";
    $closedCount = $vacancyModel->closeExpired();
    
    if ($closedCount > 0) {
        echo "  ✅ {$closedCount} vaga(s) fechada(s) automaticamente\n";
    } else {
        echo "  ℹ️  Nenhuma vaga expirada para fechar\n";
    }

    // 2. BUSCAR VAGAS AINDA ABERTAS
    echo "\n2️⃣ Verificando vagas abertas...\n";
    $openVacancies = $vacancyModel->openVacancies();

    if (empty($openVacancies)) {
        echo "  ℹ️  Nenhuma vaga aberta no momento.\n";
    } else {
        echo "  ℹ️  Encontradas " . count($openVacancies) . " vaga(s) aberta(s).\n";
    
        // 3. NOTIFICAR SOBRE PRAZOS PRÓXIMOS
        echo "\n3️⃣ Verificando prazos próximos (48h)...\n";
        $totalNotifications = 0;
    
        foreach ($openVacancies as $vacancy) {
            $deadline = new DateTime($vacancy['deadline_at']);
            $now = new DateTime();
            $diff = $now->diff($deadline);
            $hoursLeft = ($diff->days * 24) + $diff->h;
    
            // Notificar se faltarem 48 horas (2 dias)
            if ($hoursLeft > 0 && $hoursLeft <= 48) {
                echo "  📢 Vaga: {$vacancy['title']}\n";
                echo "     Prazo: " . $deadline->format('d/m/Y H:i') . "\n";
                echo "     Restam: {$hoursLeft}h\n";
                echo "     Notificando vigilantes...\n";
    
                $count = $emailService->notifyDeadlineApproaching($vacancy['id']);
                
                echo "     ✅ {$count} notificação(ões) enviada(s)\n\n";
                $totalNotifications += $count;
            }
        }
        
        if ($totalNotifications === 0) {
            echo "  ℹ️  Nenhuma vaga com prazo próximo (48h)\n";
        }
    }

    $duration = round(microtime(true) - $startTime, 2);
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "📊 RESUMO FINAL\n";
    echo str_repeat("=", 50) . "\n";
    echo "  🔒 Vagas fechadas: $closedCount\n";
    echo "  📧 Notificações enviadas: " . ($totalNotifications ?? 0) . "\n";
    echo "  ⏱️  Tempo de execução: {$duration}s\n";
    echo str_repeat("=", 50) . "\n";
    echo "[" . date('Y-m-d H:i:s') . "] ✅ Finalizado com sucesso.\n\n";

} catch (Exception $e) {
    echo "❌ ERRO FATAL: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
