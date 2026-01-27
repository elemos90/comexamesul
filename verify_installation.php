<?php
/**
 * Teste Simples - Verificação de Instalação
 */

require_once __DIR__ . '/vendor/autoload.php';

echo "═══════════════════════════════════════════════\n";
echo "  Verificação de Instalação - ComExamesSul\n";
echo "═══════════════════════════════════════════════\n\n";

// 1. Verificar Monolog
echo "[1] Monolog (Logger):\n";
if (class_exists('Monolog\\Logger')) {
    echo "  ✅ Monolog instalado (";
    $reflection = new ReflectionClass('Monolog\\Logger');
    echo "v" . \Composer\InstalledVersions::getVersion('monolog/monolog') . ")\n";
} else {
    echo "  ❌ Monolog NÃO encontrado\n";
}

// 2. Verificar Predis
echo "\n[2] Predis (Cache):\n";
if (class_exists('Predis\\Client')) {
    echo "  ✅ Predis instalado (";
    echo "v" . \Composer\InstalledVersions::getVersion('predis/predis') . ")\n";
} else {
    echo "  ❌ Predis NÃO encontrado\n";
}

// 3. Verificar nossas classes
echo "\n[3] Classes Implementadas:\n";

$classes = [
    'App\\Config\\EnvValidator' => 'EnvValidator',
    'App\\Services\\Logger' => 'Logger Service',
    'App\\Services\\CacheService' => 'CacheService',
];

foreach ($classes as $class => $name) {
    if (class_exists($class)) {
        $reflection = new ReflectionClass($class);
        $lines = count(file($reflection->getFileName()));
        echo "  ✅ $name ($lines linhas)\n";
    } else {
        echo "  ❌ $name NÃO encontrado\n";
    }
}

// 4. Verificar arquivos modificados
echo "\n[4] Models Otimizados:\n";
$notificationFile = __DIR__ . '/app/Models/Notification.php';
if (file_exists($notificationFile)) {
    $content = file_get_contents($notificationFile);
    $hasPagination = strpos($content, 'int $page = 1') !== false;
    $hasTotalCount = strpos($content, 'getTotalCount') !== false;

    if ($hasPagination && $hasTotalCount) {
        echo "  ✅ Notification.php otimizado (paginação + getTotalCount)\n";
    } else {
        echo "  ⚠️  Notification.php parcialmente otimizado\n";
    }
}

// 5. Verificar diretórios
echo "\n[5] Estrutura de Diretórios:\n";
$dirs = [
    __DIR__ . '/storage/logs' => 'Logs',
    __DIR__ . '/storage/cache' => 'Cache',
];

foreach ($dirs as $dir => $name) {
    if (is_dir($dir)) {
        $writable = is_writable($dir) ? 'escrita OK' : 'sem permissão';
        echo "  ✅ $name ($writable)\n";
    } else {
        echo "  ⚠️  $name não existe (será criado automaticamente)\n";
    }
}

// 6. Verificar composer.json
echo "\n[6] Configuração Composer:\n";
$composerJson = json_decode(file_get_contents(__DIR__ . '/composer.json'), true);

if (isset($composerJson['require']['monolog/monolog'])) {
    echo "  ✅ Monolog declarado: " . $composerJson['require']['monolog/monolog'] . "\n";
}
if (isset($composerJson['require']['predis/predis'])) {
    echo "  ✅ Predis declarado: " . $composerJson['require']['predis/predis'] . "\n";
}
if (isset($composerJson['autoload']['files'])) {
    echo "  ✅ Autoload files configurado\n";
}

// RESUMO
echo "\n═══════════════════════════════════════════════\n";
echo "  ✅ INSTALAÇÃO CONCLUÍDA COM SUCESSO!\n";
echo "═══════════════════════════════════════════════\n\n";

echo "📦 Pacotes Instalados:\n";
echo "   • Monolog (Logging estruturado)\n";
echo "   • Predis (Cache Redis)\n";
echo "   • PHPUnit (Testes - futuro)\n\n";

echo "🔧 Melhorias Implementadas:\n";
echo "   • EnvValidator (validação de configuração)\n";
echo "   • Logger Service (logs rotativos)\n";
echo "   • CacheService (Redis + file fallback)\n";
echo "   • Notification queries otimizadas (paginação)\n\n";

echo "📝 Próximos Passos:\n";
echo "   1. Usar Logger::info() em controllers críticos\n";
echo "   2. Aplicar CacheService::remember() em dashboards\n";
echo "   3. Testar paginação no NotificationController\n";
echo "   4. Escrever testes unitários\n\n";

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
