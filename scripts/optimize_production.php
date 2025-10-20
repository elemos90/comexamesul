<?php
/**
 * Script de Otimização para Produção
 * Otimiza banco de dados e arquivos do sistema
 * 
 * Executar: php scripts/optimize_production.php
 * 
 * ⚠️ ATENÇÃO: Fazer backup antes de executar
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Connection;

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  OTIMIZAÇÃO DE PRODUÇÃO - admissao.cycode.net              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

echo "⚠️  Este script irá:\n";
echo "   • Otimizar tabelas do banco de dados\n";
echo "   • Limpar cache antigo\n";
echo "   • Limpar logs antigos\n";
echo "   • Otimizar autoload do Composer\n";
echo "   • Limpar uploads temporários\n\n";

echo "🛡️  Recomenda-se fazer BACKUP antes de continuar.\n\n";

// Confirmação
if (php_sapi_name() === 'cli') {
    echo "Deseja continuar? (s/n): ";
    $handle = fopen("php://stdin", "r");
    $confirm = trim(fgets($handle));
    fclose($handle);
    
    if (strtolower($confirm) !== 's') {
        echo "\n❌ Operação cancelada.\n";
        exit(0);
    }
}

echo "\n";
echo str_repeat('═', 60) . "\n";
echo "INICIANDO OTIMIZAÇÃO...\n";
echo str_repeat('═', 60) . "\n\n";

$db = Connection::getInstance();
$totalImprovement = 0;

// ============================================
// 1. OTIMIZAR BANCO DE DADOS
// ============================================
echo "🗄️  OTIMIZANDO BANCO DE DADOS\n";
echo str_repeat('─', 60) . "\n";

$tables = [
    'users',
    'juries',
    'jury_vigilantes',
    'exam_vacancies',
    'vacancy_applications',
    'exam_reports',
    'activity_log',
    'password_resets',
    'disciplines',
    'exam_locations',
    'exam_rooms',
    'location_templates',
    'availability_change_requests',
    'application_status_history',
    'email_notifications'
];

foreach ($tables as $table) {
    try {
        // Verificar se tabela existe
        $exists = $db->query("SHOW TABLES LIKE '{$table}'")->fetch();
        
        if (!$exists) {
            echo "⏭️  Tabela {$table} não existe - pulando\n";
            continue;
        }
        
        // Otimizar tabela
        echo "   Otimizando {$table}... ";
        $start = microtime(true);
        $db->exec("OPTIMIZE TABLE {$table}");
        $time = round((microtime(true) - $start) * 1000, 2);
        echo "✓ ({$time}ms)\n";
        
        $totalImprovement++;
    } catch (Exception $e) {
        echo "⚠️  Erro: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// ============================================
// 2. ANALISAR TABELAS
// ============================================
echo "📊 ATUALIZANDO ESTATÍSTICAS DO BANCO\n";
echo str_repeat('─', 60) . "\n";

foreach (['users', 'juries', 'jury_vigilantes'] as $table) {
    try {
        $exists = $db->query("SHOW TABLES LIKE '{$table}'")->fetch();
        if ($exists) {
            echo "   Analisando {$table}... ";
            $db->exec("ANALYZE TABLE {$table}");
            echo "✓\n";
        }
    } catch (Exception $e) {
        echo "⚠️  Erro: " . $e->getMessage() . "\n";
    }
}

echo "\n";

// ============================================
// 3. LIMPAR CACHE
// ============================================
echo "🧹 LIMPANDO CACHE\n";
echo str_repeat('─', 60) . "\n";

$cachePath = base_path('storage/cache');
$cacheFiles = glob($cachePath . '/*');
$cacheCleared = 0;
$cacheSize = 0;

if ($cacheFiles) {
    foreach ($cacheFiles as $file) {
        if (is_file($file)) {
            $cacheSize += filesize($file);
            unlink($file);
            $cacheCleared++;
        }
    }
}

echo "   ✓ {$cacheCleared} arquivos removidos (" . formatBytes($cacheSize) . ")\n\n";

// ============================================
// 4. LIMPAR LOGS ANTIGOS
// ============================================
echo "📝 LIMPANDO LOGS ANTIGOS (>30 dias)\n";
echo str_repeat('─', 60) . "\n";

$logsPath = base_path('storage/logs');
$logsRemoved = 0;
$logsSize = 0;

if (is_dir($logsPath)) {
    $logFiles = glob($logsPath . '/*.log');
    
    foreach ($logFiles as $file) {
        if (is_file($file) && filemtime($file) < strtotime('-30 days')) {
            $logsSize += filesize($file);
            unlink($file);
            $logsRemoved++;
        }
    }
}

echo "   ✓ {$logsRemoved} logs removidos (" . formatBytes($logsSize) . ")\n\n";

// ============================================
// 5. LIMPAR SESSÕES ANTIGAS
// ============================================
echo "🔐 LIMPANDO SESSÕES ANTIGAS (>7 dias)\n";
echo str_repeat('─', 60) . "\n";

$sessionsPath = base_path('storage/sessions');
$sessionsRemoved = 0;

if (is_dir($sessionsPath)) {
    $sessionFiles = glob($sessionsPath . '/sess_*');
    
    foreach ($sessionFiles as $file) {
        if (is_file($file) && filemtime($file) < strtotime('-7 days')) {
            unlink($file);
            $sessionsRemoved++;
        }
    }
}

echo "   ✓ {$sessionsRemoved} sessões removidas\n\n";

// ============================================
// 6. LIMPAR TOKENS DE RESET EXPIRADOS
// ============================================
echo "🔑 LIMPANDO TOKENS EXPIRADOS\n";
echo str_repeat('─', 60) . "\n";

try {
    $stmt = $db->prepare("DELETE FROM password_resets WHERE expires_at < NOW()");
    $stmt->execute();
    $tokensDeleted = $stmt->rowCount();
    echo "   ✓ {$tokensDeleted} tokens expirados removidos\n";
} catch (Exception $e) {
    echo "   ⚠️  Erro: " . $e->getMessage() . "\n";
}

echo "\n";

// ============================================
// 7. OTIMIZAR AUTOLOAD DO COMPOSER
// ============================================
echo "📦 OTIMIZANDO AUTOLOAD DO COMPOSER\n";
echo str_repeat('─', 60) . "\n";

$composerPath = base_path();
if (file_exists($composerPath . '/composer.json')) {
    echo "   Executando: composer dump-autoload --optimize\n";
    
    $output = [];
    $returnVar = 0;
    exec("cd {$composerPath} && composer dump-autoload --optimize 2>&1", $output, $returnVar);
    
    if ($returnVar === 0) {
        echo "   ✓ Autoload otimizado\n";
    } else {
        echo "   ⚠️  Erro ao otimizar autoload\n";
        echo "   " . implode("\n   ", $output) . "\n";
    }
} else {
    echo "   ⚠️  composer.json não encontrado\n";
}

echo "\n";

// ============================================
// 8. VERIFICAR OPcache
// ============================================
echo "⚡ VERIFICANDO OPCACHE\n";
echo str_repeat('─', 60) . "\n";

if (function_exists('opcache_get_status')) {
    $opcache = opcache_get_status();
    
    if ($opcache && $opcache['opcache_enabled']) {
        echo "   ✓ OPcache está HABILITADO\n";
        
        if (function_exists('opcache_reset')) {
            opcache_reset();
            echo "   ✓ OPcache resetado\n";
        }
        
        echo "   • Memory used: " . formatBytes($opcache['memory_usage']['used_memory']) . "\n";
        echo "   • Hit rate: " . round($opcache['opcache_statistics']['opcache_hit_rate'], 2) . "%\n";
    } else {
        echo "   ⚠️  OPcache está DESABILITADO\n";
        echo "   Recomenda-se habilitar no php.ini para melhor performance\n";
    }
} else {
    echo "   ⚠️  OPcache não disponível\n";
}

echo "\n";

// ============================================
// 9. ESTATÍSTICAS DO BANCO
// ============================================
echo "📊 ESTATÍSTICAS DO BANCO DE DADOS\n";
echo str_repeat('─', 60) . "\n";

try {
    // Tamanho do banco
    $stmt = $db->query("
        SELECT 
            ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
        FROM information_schema.TABLES 
        WHERE table_schema = DATABASE()
    ");
    $dbSize = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "   • Tamanho total: " . $dbSize['size_mb'] . " MB\n";
    
    // Contagem de registros
    $counts = [];
    foreach (['users', 'juries', 'jury_vigilantes', 'vacancy_applications'] as $table) {
        try {
            $count = $db->query("SELECT COUNT(*) FROM {$table}")->fetchColumn();
            $counts[$table] = $count;
        } catch (Exception $e) {
            $counts[$table] = 0;
        }
    }
    
    echo "   • Usuários: " . number_format($counts['users']) . "\n";
    echo "   • Júris: " . number_format($counts['juries']) . "\n";
    echo "   • Alocações: " . number_format($counts['jury_vigilantes']) . "\n";
    echo "   • Candidaturas: " . number_format($counts['vacancy_applications']) . "\n";
    
} catch (Exception $e) {
    echo "   ⚠️  Erro ao obter estatísticas\n";
}

echo "\n";

// ============================================
// RESUMO FINAL
// ============================================
echo str_repeat('═', 60) . "\n";
echo "✅ OTIMIZAÇÃO CONCLUÍDA\n";
echo str_repeat('═', 60) . "\n\n";

echo "Resumo:\n";
echo "   • {$totalImprovement} tabelas otimizadas\n";
echo "   • {$cacheCleared} arquivos de cache removidos (" . formatBytes($cacheSize) . ")\n";
echo "   • {$logsRemoved} logs antigos removidos (" . formatBytes($logsSize) . ")\n";
echo "   • {$sessionsRemoved} sessões antigas removidas\n";
echo "   • Autoload do Composer otimizado\n";

echo "\n💡 Recomendações:\n";
echo "   1. Executar este script mensalmente\n";
echo "   2. Monitorar crescimento do banco de dados\n";
echo "   3. Fazer backup antes de otimizações\n";
echo "   4. Habilitar OPcache se não estiver ativo\n";

echo "\n🎉 Sistema otimizado e pronto para uso!\n\n";

// ============================================
// FUNÇÕES AUXILIARES
// ============================================

function formatBytes($bytes, $precision = 2): string
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

exit(0);
