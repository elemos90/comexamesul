#!/usr/bin/env php
<?php

// Cron Job: Enviar Emails Pendentes
// Executa a cada 5 minutos
// 
// Configuração no crontab: executar a cada 5 minutos
// Windows Task Scheduler: executar a cada 5 minutos

require_once __DIR__ . '/../../bootstrap.php';

use App\Services\EmailNotificationService;
use App\Models\EmailNotification;

$startTime = microtime(true);
echo "[" . date('Y-m-d H:i:s') . "] Iniciando envio de emails...\n";

try {
    $emailModel = new EmailNotification();
    $emailService = new EmailNotificationService();

    // Buscar emails pendentes (limite de 50 por execução)
    $pendingEmails = $emailModel->getPending(50);

    if (empty($pendingEmails)) {
        echo "Nenhum email pendente para enviar.\n";
        exit(0);
    }

    echo "Encontrados " . count($pendingEmails) . " emails pendentes.\n";

    $sent = 0;
    $failed = 0;

    foreach ($pendingEmails as $email) {
        echo "  Enviando para: {$email['user_email']} ({$email['subject']})... ";

        try {
            $success = $emailService->send($email['id']);
            
            if ($success) {
                echo "✅ OK\n";
                $sent++;
            } else {
                echo "❌ FALHOU\n";
                $failed++;
            }

            // Pequeno delay para evitar spam
            usleep(100000); // 0.1 segundo

        } catch (Exception $e) {
            echo "❌ ERRO: " . $e->getMessage() . "\n";
            $emailModel->markAsFailed($email['id'], $e->getMessage());
            $failed++;
        }
    }

    // Tentar reenviar emails falhados (máximo 10)
    $retryEmails = $emailModel->retryFailed(10);
    if (!empty($retryEmails)) {
        echo "\nRetentando " . count($retryEmails) . " emails falhados...\n";
        
        foreach ($retryEmails as $email) {
            echo "  Reenviando para: {$email['user_email']}... ";
            
            try {
                $success = $emailService->send($email['id']);
                
                if ($success) {
                    echo "✅ OK\n";
                    $sent++;
                } else {
                    echo "❌ FALHOU\n";
                    $failed++;
                }

                usleep(100000);

            } catch (Exception $e) {
                echo "❌ ERRO: " . $e->getMessage() . "\n";
                $failed++;
            }
        }
    }

    $duration = round(microtime(true) - $startTime, 2);
    
    echo "\n";
    echo "Resumo:\n";
    echo "  ✅ Enviados: $sent\n";
    echo "  ❌ Falhados: $failed\n";
    echo "  ⏱️  Tempo: {$duration}s\n";
    echo "[" . date('Y-m-d H:i:s') . "] Finalizado.\n\n";

} catch (Exception $e) {
    echo "❌ ERRO FATAL: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
    exit(1);
}
