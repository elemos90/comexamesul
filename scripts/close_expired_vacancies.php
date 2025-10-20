<?php
/**
 * Script Manual: Fechar Vagas Expiradas
 * Use este script para fechar manualmente todas as vagas que já expiraram
 * 
 * Uso: php scripts/close_expired_vacancies.php
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Models\ExamVacancy;

echo "==============================================\n";
echo "  FECHAMENTO DE VAGAS EXPIRADAS\n";
echo "==============================================\n\n";

try {
    $vacancyModel = new ExamVacancy();
    
    // Buscar vagas abertas antes do fechamento
    echo "📋 Verificando vagas abertas...\n";
    $openBefore = $vacancyModel->openVacancies();
    echo "   Vagas abertas: " . count($openBefore) . "\n\n";
    
    if (!empty($openBefore)) {
        echo "📊 Detalhes das vagas abertas:\n";
        foreach ($openBefore as $v) {
            $deadline = new DateTime($v['deadline_at']);
            $now = new DateTime();
            $expired = $now > $deadline;
            
            echo "   • {$v['title']}\n";
            echo "     Prazo: " . $deadline->format('d/m/Y H:i') . "\n";
            echo "     Status: " . ($expired ? "❌ EXPIRADA" : "✅ Ativa") . "\n\n";
        }
    }
    
    // Fechar vagas expiradas
    echo "🔒 Fechando vagas expiradas...\n";
    $closedCount = $vacancyModel->closeExpired();
    
    if ($closedCount > 0) {
        echo "   ✅ {$closedCount} vaga(s) fechada(s) com sucesso!\n\n";
    } else {
        echo "   ℹ️  Nenhuma vaga expirada para fechar\n\n";
    }
    
    // Verificar vagas abertas após o fechamento
    echo "📋 Verificando vagas abertas após fechamento...\n";
    $openAfter = $vacancyModel->openVacancies();
    echo "   Vagas abertas: " . count($openAfter) . "\n\n";
    
    // Resumo
    echo "==============================================\n";
    echo "📊 RESUMO\n";
    echo "==============================================\n";
    echo "Antes:   " . count($openBefore) . " vaga(s) aberta(s)\n";
    echo "Fechadas: {$closedCount} vaga(s)\n";
    echo "Depois:  " . count($openAfter) . " vaga(s) aberta(s)\n";
    echo "==============================================\n\n";
    
    echo "✅ Operação concluída com sucesso!\n\n";
    
} catch (Exception $e) {
    echo "\n❌ ERRO: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n\n";
    exit(1);
}
