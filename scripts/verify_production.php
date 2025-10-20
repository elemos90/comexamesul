<?php
/**
 * Script de Verificação Pós-Deploy
 * Verifica se o sistema está funcionando corretamente em produção
 * 
 * Executar via SSH: php scripts/verify_production.php
 * Ou acessar via navegador: https://admissao.cycode.net/verify-production.php
 */

declare(strict_types=1);

// Definir que estamos em modo de verificação
define('VERIFICATION_MODE', true);

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  VERIFICAÇÃO PÓS-DEPLOY - admissao.cycode.net              ║\n";
echo "╚════════════════════════════════════════════════════════════╝\n\n";

$errors = [];
$warnings = [];
$passed = 0;
$total = 0;

function check(string $name, bool $condition, string $errorMsg = '', bool $critical = true): void
{
    global $errors, $warnings, $passed, $total;
    $total++;
    
    if ($condition) {
        echo "✅ {$name}\n";
        $passed++;
    } else {
        if ($critical) {
            echo "❌ {$name}\n";
            if ($errorMsg) $errors[] = $errorMsg;
        } else {
            echo "⚠️  {$name}\n";
            if ($errorMsg) $warnings[] = $errorMsg;
        }
    }
}

// ============================================
// 1. VERIFICAÇÕES DE AMBIENTE
// ============================================
echo "🌐 Verificações de Ambiente\n";
echo str_repeat('─', 60) . "\n";

check(
    'PHP 8.1+',
    version_compare(PHP_VERSION, '8.1.0', '>='),
    'PHP 8.1+ é obrigatório. Versão atual: ' . PHP_VERSION
);

check(
    'Modo Produção',
    !ini_get('display_errors') || ini_get('display_errors') === 'Off',
    'display_errors deve estar OFF em produção',
    false
);

check(
    'HTTPS Ativo',
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') || 
    (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https'),
    'HTTPS não está ativo. Configure SSL/TLS',
    false
);

check(
    'Extensão PDO MySQL',
    extension_loaded('pdo_mysql'),
    'Extensão pdo_mysql não instalada'
);

check(
    'Extensão MBString',
    extension_loaded('mbstring'),
    'Extensão mbstring não instalada'
);

echo "\n";

// ============================================
// 2. VERIFICAÇÕES DE ARQUIVOS
// ============================================
echo "📁 Verificações de Arquivos\n";
echo str_repeat('─', 60) . "\n";

$basePath = dirname(__DIR__);

check(
    'Bootstrap existe',
    file_exists($basePath . '/bootstrap.php'),
    'bootstrap.php não encontrado'
);

check(
    'Arquivo .env existe',
    file_exists($basePath . '/.env'),
    'Arquivo .env não encontrado. Crie a partir do .env.example'
);

check(
    'Pasta vendor/ existe',
    is_dir($basePath . '/vendor'),
    'Pasta vendor/ não encontrada. Execute: composer install'
);

check(
    'Autoload do Composer',
    file_exists($basePath . '/vendor/autoload.php'),
    'Autoload do Composer não encontrado'
);

echo "\n";

// ============================================
// 3. VERIFICAÇÕES DE PERMISSÕES
// ============================================
echo "🔐 Verificações de Permissões\n";
echo str_repeat('─', 60) . "\n";

$writableDirs = [
    'storage/logs',
    'storage/cache',
    'public/uploads',
    'public/uploads/avatars'
];

foreach ($writableDirs as $dir) {
    $path = $basePath . '/' . $dir;
    
    check(
        "Pasta {$dir}/ é gravável",
        is_dir($path) && is_writable($path),
        "Pasta {$dir}/ não tem permissão de escrita. Execute: chmod 775 {$dir}"
    );
}

check(
    '.env é protegido',
    file_exists($basePath . '/.env') && (fileperms($basePath . '/.env') & 0777) <= 0600,
    '.env deve ter permissões 600. Execute: chmod 600 .env',
    false
);

echo "\n";

// ============================================
// 4. VERIFICAÇÕES DE CONFIGURAÇÃO
// ============================================
echo "⚙️  Verificações de Configuração (.env)\n";
echo str_repeat('─', 60) . "\n";

if (file_exists($basePath . '/.env')) {
    $envContent = file_get_contents($basePath . '/.env');
    
    check(
        'APP_ENV está em produção',
        strpos($envContent, 'APP_ENV=production') !== false,
        'APP_ENV deve ser "production"',
        false
    );
    
    check(
        'APP_DEBUG está desativado',
        strpos($envContent, 'APP_DEBUG=false') !== false,
        'APP_DEBUG deve ser "false" em produção'
    );
    
    check(
        'APP_URL configurado',
        strpos($envContent, 'admissao.cycode.net') !== false,
        'APP_URL deve ser https://admissao.cycode.net',
        false
    );
    
    check(
        'SESSION_SECURE ativado',
        strpos($envContent, 'SESSION_SECURE=true') !== false,
        'SESSION_SECURE deve ser "true" com HTTPS',
        false
    );
    
    check(
        'Senha padrão não está em uso',
        strpos($envContent, 'DB_PASSWORD=') !== false && 
        strpos($envContent, 'DB_PASSWORD=password') === false &&
        strpos($envContent, 'DB_PASSWORD=TROCAR') === false,
        'Senha do banco parece ser padrão. Altere para senha forte',
        false
    );
}

echo "\n";

// ============================================
// 5. VERIFICAÇÕES DE BANCO DE DADOS
// ============================================
echo "🗄️  Verificações de Banco de Dados\n";
echo str_repeat('─', 60) . "\n";

try {
    require_once $basePath . '/bootstrap.php';
    
    $db = \App\Database\Connection::getInstance();
    
    check(
        'Conexão com banco de dados',
        $db instanceof PDO,
        'Não foi possível conectar ao banco de dados'
    );
    
    // Verificar tabelas principais
    $tables = ['users', 'juries', 'jury_vigilantes', 'exam_vacancies', 'vacancy_applications'];
    $stmt = $db->query("SHOW TABLES");
    $existingTables = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($tables as $table) {
        check(
            "Tabela {$table} existe",
            in_array($table, $existingTables),
            "Tabela {$table} não encontrada. Execute as migrations"
        );
    }
    
    // Verificar se há usuários
    if (in_array('users', $existingTables)) {
        $userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
        check(
            'Há usuários cadastrados',
            $userCount > 0,
            'Nenhum usuário encontrado. Importe os dados iniciais',
            false
        );
        
        // Verificar usuário coordenador
        $coordenador = $db->query("SELECT COUNT(*) FROM users WHERE role = 'coordenador'")->fetchColumn();
        check(
            'Há pelo menos um coordenador',
            $coordenador > 0,
            'Nenhum coordenador encontrado. Crie um usuário coordenador'
        );
    }
    
    // Verificar views
    $views = ['vw_eligible_vigilantes', 'vw_allocation_stats', 'vw_jury_slots'];
    $stmt = $db->query("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
    $existingViews = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($views as $view) {
        check(
            "View {$view} existe",
            in_array($view, $existingViews),
            "View {$view} não encontrada. Execute migrations_auto_allocation.sql",
            false
        );
    }
    
} catch (Exception $e) {
    check(
        'Conexão com banco de dados',
        false,
        'Erro ao conectar: ' . $e->getMessage()
    );
}

echo "\n";

// ============================================
// 6. VERIFICAÇÕES DE DEPENDÊNCIAS
// ============================================
echo "📦 Verificações de Dependências\n";
echo str_repeat('─', 60) . "\n";

if (file_exists($basePath . '/vendor/autoload.php')) {
    require_once $basePath . '/vendor/autoload.php';
    
    check(
        'Dompdf instalado',
        class_exists('Dompdf\Dompdf'),
        'Dompdf não instalado',
        false
    );
    
    check(
        'PHPSpreadsheet instalado',
        class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet'),
        'PHPSpreadsheet não instalado',
        false
    );
    
    check(
        'PHPMailer instalado',
        class_exists('PHPMailer\PHPMailer\PHPMailer'),
        'PHPMailer não instalado',
        false
    );
}

echo "\n";

// ============================================
// 7. VERIFICAÇÕES DE SEGURANÇA
// ============================================
echo "🔒 Verificações de Segurança\n";
echo str_repeat('─', 60) . "\n";

check(
    'Arquivos de teste removidos',
    !file_exists($basePath . '/public/test_routes.php') &&
    !file_exists($basePath . '/public/debug_top3.php'),
    'Arquivos de teste encontrados em public/. Remova antes de produção',
    false
);

check(
    '.htaccess existe',
    file_exists($basePath . '/public/.htaccess'),
    'Arquivo .htaccess não encontrado em public/'
);

if (file_exists($basePath . '/public/.htaccess')) {
    $htaccess = file_get_contents($basePath . '/public/.htaccess');
    
    check(
        'HTTPS forçado no .htaccess',
        strpos($htaccess, 'RewriteCond %{HTTPS}') !== false,
        'HTTPS não está sendo forçado no .htaccess',
        false
    );
    
    check(
        'Proteção de .env no .htaccess',
        strpos($htaccess, '.env') !== false,
        'Arquivo .env não está protegido no .htaccess',
        false
    );
}

echo "\n";

// ============================================
// 8. VERIFICAÇÕES DE PERFORMANCE
// ============================================
echo "⚡ Verificações de Performance\n";
echo str_repeat('─', 60) . "\n";

if (isset($db)) {
    // Verificar índices importantes
    $indexes = $db->query("
        SELECT DISTINCT INDEX_NAME 
        FROM information_schema.STATISTICS 
        WHERE TABLE_SCHEMA = DATABASE() 
        AND TABLE_NAME = 'juries'
    ")->fetchAll(PDO::FETCH_COLUMN);
    
    check(
        'Índices de performance aplicados',
        count($indexes) > 2,
        'Poucos índices encontrados. Execute performance_indexes.sql',
        false
    );
}

check(
    'OPcache habilitado',
    function_exists('opcache_get_status') && opcache_get_status() !== false,
    'OPcache não está habilitado. Configure no php.ini',
    false
);

echo "\n";

// ============================================
// RESUMO FINAL
// ============================================
echo str_repeat('═', 60) . "\n";
echo "📊 RESUMO DA VERIFICAÇÃO\n";
echo str_repeat('═', 60) . "\n\n";

echo "Testes passados: {$passed}/{$total}\n";
echo "Taxa de sucesso: " . round(($passed / $total) * 100, 1) . "%\n\n";

if (count($errors) > 0) {
    echo "❌ ERROS CRÍTICOS (" . count($errors) . "):\n";
    foreach ($errors as $i => $error) {
        echo "   " . ($i + 1) . ". {$error}\n";
    }
    echo "\n";
}

if (count($warnings) > 0) {
    echo "⚠️  AVISOS (" . count($warnings) . "):\n";
    foreach ($warnings as $i => $warning) {
        echo "   " . ($i + 1) . ". {$warning}\n";
    }
    echo "\n";
}

// Status final
if (count($errors) === 0 && $passed >= ($total * 0.85)) {
    echo "✅ SISTEMA FUNCIONANDO CORRETAMENTE!\n\n";
    echo "🎉 Parabéns! O sistema está em produção e operacional.\n\n";
    echo "Próximos passos:\n";
    echo "1. Configurar backup automático (scripts/backup_production.sh)\n";
    echo "2. Configurar monitoramento (UptimeRobot)\n";
    echo "3. Treinar usuários finais\n";
    echo "4. Monitorar logs regularmente\n\n";
    exit(0);
} elseif (count($errors) === 0) {
    echo "⚠️  SISTEMA FUNCIONANDO COM AVISOS\n";
    echo "Revise os avisos acima para otimizar o sistema.\n\n";
    exit(1);
} else {
    echo "❌ SISTEMA COM PROBLEMAS CRÍTICOS\n";
    echo "Corrija os erros acima antes de usar em produção.\n\n";
    exit(2);
}
