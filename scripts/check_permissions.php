<?php
/**
 * Script de Verificação de Permissões
 * Verifica se os diretórios críticos têm permissões adequadas
 */

declare(strict_types=1);

$baseDir = dirname(__DIR__);

echo "\n=== VERIFICAÇÃO DE PERMISSÕES ===\n\n";

// Diretórios críticos que precisam de permissão de escrita
$criticalDirs = [
    'storage/logs' => 'Logs da aplicação',
    'storage/cache' => 'Cache do sistema',
    'storage/uploads' => 'Uploads temporários',
    'public/uploads' => 'Uploads públicos (avatares)',
    'public/uploads/avatars' => 'Avatares de usuários',
];

$allOk = true;
$issues = [];

foreach ($criticalDirs as $dir => $description) {
    $fullPath = $baseDir . '/' . $dir;
    
    echo "Verificando: $dir\n";
    echo "Descrição: $description\n";
    
    // Verificar se o diretório existe
    if (!is_dir($fullPath)) {
        echo "   [AVISO] Diretório não existe - criando...\n";
        if (@mkdir($fullPath, 0755, true)) {
            echo "   [OK] Diretório criado com sucesso\n";
        } else {
            echo "   [ERRO] Falha ao criar diretório\n";
            $issues[] = "Criar diretório: $dir";
            $allOk = false;
        }
    } else {
        echo "   [OK] Diretório existe\n";
    }
    
    // Verificar permissões de leitura
    if (is_readable($fullPath)) {
        echo "   [OK] Permissão de leitura\n";
    } else {
        echo "   [ERRO] Sem permissão de leitura\n";
        $issues[] = "Permissão de leitura em: $dir";
        $allOk = false;
    }
    
    // Verificar permissões de escrita
    if (is_writable($fullPath)) {
        echo "   [OK] Permissão de escrita\n";
    } else {
        echo "   [ERRO] Sem permissão de escrita\n";
        $issues[] = "Permissão de escrita em: $dir";
        $allOk = false;
    }
    
    // Mostrar permissões atuais (Unix)
    if (DIRECTORY_SEPARATOR === '/') {
        $perms = substr(sprintf('%o', fileperms($fullPath)), -4);
        echo "   [INFO] Permissões atuais: $perms\n";
    }
    
    echo "\n";
}

// Testar escrita em arquivo de log
echo "Testando escrita no log principal...\n";
$logFile = $baseDir . '/storage/logs/app.log';

if (@file_put_contents($logFile, "[" . date('Y-m-d H:i:s') . "] Teste de escrita via check_permissions.php\n", FILE_APPEND)) {
    echo "   [OK] Log escrito com sucesso: $logFile\n";
} else {
    echo "   [ERRO] Falha ao escrever no log: $logFile\n";
    $issues[] = "Escrever em: storage/logs/app.log";
    $allOk = false;
}

echo "\n";

// Relatório final
echo "=== RELATÓRIO FINAL ===\n\n";

if ($allOk) {
    echo "✅ SUCESSO: Todas as permissões estão corretas!\n\n";
    echo "O sistema está pronto para uso.\n";
} else {
    echo "❌ PROBLEMAS ENCONTRADOS:\n\n";
    foreach ($issues as $issue) {
        echo "   - $issue\n";
    }
    echo "\n";
    echo "SOLUÇÕES:\n\n";
    
    if (DIRECTORY_SEPARATOR === '/') {
        // Linux/Mac
        echo "Linux/Mac:\n";
        echo "   sudo chmod -R 755 storage\n";
        echo "   sudo chmod -R 755 public/uploads\n";
        echo "   sudo chown -R www-data:www-data storage\n";
        echo "   sudo chown -R www-data:www-data public/uploads\n";
    } else {
        // Windows
        echo "Windows:\n";
        echo "   Clique direito no diretório > Propriedades > Segurança\n";
        echo "   Garanta que o usuário tem permissões de Leitura e Escrita\n";
    }
    
    exit(1);
}

// Informações adicionais
echo "\n=== INFORMAÇÕES DO SISTEMA ===\n\n";
echo "PHP Version: " . PHP_VERSION . "\n";
echo "OS: " . PHP_OS . "\n";
echo "User: " . get_current_user() . "\n";
echo "Base Path: $baseDir\n";
echo "Log File: $logFile\n";

if (function_exists('posix_getpwuid') && function_exists('posix_geteuid')) {
    $processUser = posix_getpwuid(posix_geteuid());
    echo "Process User: " . $processUser['name'] . "\n";
}

echo "\n";
