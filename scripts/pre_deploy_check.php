<?php
/**
 * Script de Verificação Pré-Deploy
 * Verifica se o sistema está pronto para produção
 * 
 * Executar: php scripts/pre_deploy_check.php
 */

declare(strict_types=1);

echo "╔════════════════════════════════════════════════════════════╗\n";
echo "║  PRÉ-DEPLOY CHECK - admissao.cycode.net                   ║\n";
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
// 1. VERIFICAÇÕES DE PHP
// ============================================
echo "📌 Verificações de PHP\n";
echo str_repeat('─', 60) . "\n";

check(
    'PHP 8.1+',
    version_compare(PHP_VERSION, '8.1.0', '>='),
    'PHP 8.1+ é obrigatório. Versão atual: ' . PHP_VERSION
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

check(
    'Extensão JSON',
    extension_loaded('json'),
    'Extensão json não instalada'
);

check(
    'Extensão FileInfo',
    extension_loaded('fileinfo'),
    'Extensão fileinfo não instalada'
);

echo "\n";

// ============================================
// 2. VERIFICAÇÕES DE ARQUIVOS
// ============================================
echo "📁 Verificações de Arquivos\n";
echo str_repeat('─', 60) . "\n";

check(
    'Arquivo bootstrap.php existe',
    file_exists(__DIR__ . '/../bootstrap.php'),
    'bootstrap.php não encontrado'
);

check(
    'Arquivo composer.json existe',
    file_exists(__DIR__ . '/../composer.json'),
    'composer.json não encontrado'
);

check(
    'Pasta vendor/ existe',
    is_dir(__DIR__ . '/../vendor'),
    'Pasta vendor/ não encontrada. Execute: composer install',
    false
);

check(
    'Arquivo .env.example existe',
    file_exists(__DIR__ . '/../.env.example'),
    '.env.example não encontrado'
);

check(
    'Arquivo public/index.php existe',
    file_exists(__DIR__ . '/../public/index.php'),
    'public/index.php não encontrado'
);

check(
    'Arquivo public/.htaccess existe',
    file_exists(__DIR__ . '/../public/.htaccess'),
    'public/.htaccess não encontrado'
);

echo "\n";

// ============================================
// 3. VERIFICAÇÕES DE DIRETÓRIOS
// ============================================
echo "📂 Verificações de Diretórios\n";
echo str_repeat('─', 60) . "\n";

$writableDirs = [
    'storage/logs',
    'storage/cache',
    'public/uploads',
    'public/uploads/avatars'
];

foreach ($writableDirs as $dir) {
    $path = __DIR__ . '/../' . $dir;
    
    if (!is_dir($path)) {
        @mkdir($path, 0775, true);
    }
    
    check(
        "Pasta {$dir}/ existe",
        is_dir($path),
        "Pasta {$dir}/ não encontrada"
    );
    
    check(
        "Pasta {$dir}/ é gravável",
        is_writable($path),
        "Pasta {$dir}/ não tem permissão de escrita. Execute: chmod 775 {$dir}",
        false
    );
}

echo "\n";

// ============================================
// 4. VERIFICAÇÕES DE DEPENDÊNCIAS
// ============================================
echo "📦 Verificações de Dependências Composer\n";
echo str_repeat('─', 60) . "\n";

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    check(
        'Dompdf instalado',
        class_exists('Dompdf\Dompdf'),
        'Dompdf não instalado. Execute: composer require dompdf/dompdf',
        false
    );
    
    check(
        'PHPSpreadsheet instalado',
        class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet'),
        'PHPSpreadsheet não instalado. Execute: composer require phpoffice/phpspreadsheet',
        false
    );
    
    check(
        'PHPMailer instalado',
        class_exists('PHPMailer\PHPMailer\PHPMailer'),
        'PHPMailer não instalado. Execute: composer require phpmailer/phpmailer',
        false
    );
} else {
    echo "⚠️  Vendor não encontrado - pulando verificações de dependências\n\n";
}

echo "\n";

// ============================================
// 5. VERIFICAÇÕES DE SEGURANÇA
// ============================================
echo "🔒 Verificações de Segurança\n";
echo str_repeat('─', 60) . "\n";

check(
    'Arquivo .env NÃO existe (deve ser criado no servidor)',
    !file_exists(__DIR__ . '/../.env'),
    '.env detectado. Remova antes de fazer upload (use .env.example)',
    false
);

check(
    '.gitignore protege .env',
    strpos(file_get_contents(__DIR__ . '/../.gitignore'), '.env') !== false,
    '.env não está no .gitignore'
);

check(
    '.gitignore protege vendor/',
    strpos(file_get_contents(__DIR__ . '/../.gitignore'), '/vendor/') !== false,
    'vendor/ não está no .gitignore',
    false
);

// Verificar arquivos de teste
$testFiles = glob(__DIR__ . '/../public/test_*.php');
$testFiles = array_merge($testFiles, glob(__DIR__ . '/../public/debug_*.php'));

check(
    'Nenhum arquivo de teste em public/',
    count($testFiles) === 0,
    'Arquivos de teste encontrados: ' . implode(', ', array_map('basename', $testFiles)),
    false
);

echo "\n";

// ============================================
// 6. VERIFICAÇÕES DE MIGRATIONS
// ============================================
echo "🗄️  Verificações de Migrations SQL\n";
echo str_repeat('─', 60) . "\n";

$requiredMigrations = [
    'migrations.sql',
    'migrations_master_data_simple.sql',
    'migrations_auto_allocation.sql',
    'migrations_triggers.sql',
    'performance_indexes.sql'
];

foreach ($requiredMigrations as $migration) {
    check(
        "Migration {$migration} existe",
        file_exists(__DIR__ . '/../app/Database/' . $migration),
        "Migration {$migration} não encontrada",
        false
    );
}

echo "\n";

// ============================================
// RESUMO FINAL
// ============================================
echo str_repeat('═', 60) . "\n";
echo "📊 RESUMO\n";
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
if (count($errors) === 0 && $passed >= ($total * 0.8)) {
    echo "✅ SISTEMA PRONTO PARA DEPLOY!\n\n";
    echo "Próximos passos:\n";
    echo "1. Fazer upload para: /home/cycodene/admissao.cycode.net/\n";
    echo "2. Criar .env no servidor (use env.production.example)\n";
    echo "3. Executar: composer install --no-dev --optimize-autoloader\n";
    echo "4. Importar migrations SQL no phpMyAdmin\n";
    echo "5. Configurar SSL/HTTPS via cPanel\n";
    echo "6. Acessar: https://admissao.cycode.net\n\n";
    exit(0);
} elseif (count($errors) === 0) {
    echo "⚠️  SISTEMA QUASE PRONTO\n";
    echo "Corrija os avisos antes de fazer deploy.\n\n";
    exit(1);
} else {
    echo "❌ SISTEMA NÃO ESTÁ PRONTO\n";
    echo "Corrija os erros críticos antes de fazer deploy.\n\n";
    exit(2);
}
