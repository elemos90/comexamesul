<?php

/**
 * Script de Validação de Queries SELECT
 * 
 * Verifica se ainda existem SELECT * inseguros no código
 * Execução: php scripts/validate_select_queries.php
 */

echo "🔍 Validando queries SELECT no projeto...\n\n";

$projectRoot = dirname(__DIR__);
$errors = [];
$warnings = [];
$safe = [];

// Padrões a verificar
$patterns = [
    'models' => [
        'path' => $projectRoot . '/app/Models',
        'pattern' => '/SELECT\s+\*/i',
        'exclude_views' => false
    ],
    'services' => [
        'path' => $projectRoot . '/app/Services',
        'pattern' => '/SELECT\s+\*/i',
        'exclude_views' => true
    ],
    'controllers' => [
        'path' => $projectRoot . '/app/Controllers',
        'pattern' => '/SELECT\s+\*/i',
        'exclude_views' => false
    ]
];

// Views SQL que são seguras
$safeViews = [
    'vw_eligible_vigilantes',
    'vw_vigilante_workload',
    'vw_allocation_stats',
    'vw_jury_slots',
    'vw_eligible_supervisors',
    'v_application_stats',
    'v_applications_by_day',
    'v_top_vigilantes'
];

function scanDirectory($dir, $pattern, $excludeViews, $safeViews) {
    $results = [];
    
    if (!is_dir($dir)) {
        return $results;
    }
    
    $files = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir)
    );
    
    foreach ($files as $file) {
        if ($file->isFile() && $file->getExtension() === 'php') {
            $content = file_get_contents($file->getPathname());
            $lines = explode("\n", $content);
            
            foreach ($lines as $lineNum => $line) {
                if (preg_match($pattern, $line)) {
                    // Verificar se é uma VIEW segura
                    $isSafeView = false;
                    if ($excludeViews) {
                        foreach ($safeViews as $view) {
                            if (stripos($line, $view) !== false) {
                                $isSafeView = true;
                                break;
                            }
                        }
                    }
                    
                    // Verificar se tem comentário explicativo
                    $hasComment = false;
                    if ($lineNum > 0) {
                        $prevLine = $lines[$lineNum - 1];
                        if (stripos($prevLine, 'SELECT * seguro') !== false || 
                            stripos($prevLine, 'VIEW com campos') !== false) {
                            $hasComment = true;
                        }
                    }
                    
                    $results[] = [
                        'file' => str_replace(dirname($dir) . DIRECTORY_SEPARATOR, '', $file->getPathname()),
                        'line' => $lineNum + 1,
                        'content' => trim($line),
                        'safe' => $isSafeView && $hasComment,
                        'view' => $isSafeView
                    ];
                }
            }
        }
    }
    
    return $results;
}

// Escanear cada diretório
foreach ($patterns as $name => $config) {
    echo "📁 Verificando {$name}...\n";
    
    $results = scanDirectory(
        $config['path'], 
        $config['pattern'], 
        $config['exclude_views'], 
        $safeViews
    );
    
    foreach ($results as $result) {
        if ($result['safe']) {
            $safe[] = $result;
        } elseif ($result['view']) {
            $warnings[] = $result;
        } else {
            $errors[] = $result;
        }
    }
}

// Relatório
echo "\n" . str_repeat('=', 70) . "\n";
echo "📊 RELATÓRIO DE VALIDAÇÃO\n";
echo str_repeat('=', 70) . "\n\n";

if (empty($errors) && empty($warnings)) {
    echo "✅ Nenhum SELECT * inseguro encontrado!\n\n";
    
    if (!empty($safe)) {
        echo "✅ SELECT * seguros (VIEWs documentadas): " . count($safe) . "\n\n";
        foreach ($safe as $item) {
            echo "   {$item['file']}:{$item['line']}\n";
        }
    }
} else {
    if (!empty($errors)) {
        echo "❌ ERROS CRÍTICOS: " . count($errors) . " SELECT * inseguros encontrados\n\n";
        foreach ($errors as $error) {
            echo "   ❌ {$error['file']}:{$error['line']}\n";
            echo "      {$error['content']}\n\n";
        }
    }
    
    if (!empty($warnings)) {
        echo "⚠️  AVISOS: " . count($warnings) . " SELECTs em VIEWs sem comentário\n\n";
        foreach ($warnings as $warning) {
            echo "   ⚠️  {$warning['file']}:{$warning['line']}\n";
            echo "      {$warning['content']}\n";
            echo "      💡 Adicione comentário: // SELECT * seguro: [nome_view] é uma VIEW\n\n";
        }
    }
    
    if (!empty($safe)) {
        echo "\n✅ SELECT * seguros (VIEWs documentadas): " . count($safe) . "\n";
    }
}

// Verificar se Models têm selectColumns
echo "\n" . str_repeat('=', 70) . "\n";
echo "🔍 Verificando propriedade selectColumns nos Models\n";
echo str_repeat('=', 70) . "\n\n";

$modelsPath = $projectRoot . '/app/Models';
$modelsWithoutSelectColumns = [];

if (is_dir($modelsPath)) {
    $files = glob($modelsPath . '/*.php');
    
    foreach ($files as $file) {
        $filename = basename($file);
        
        // Pular BaseModel e outros utilitários
        if (in_array($filename, ['BaseModel.php', 'Model.php'])) {
            continue;
        }
        
        $content = file_get_contents($file);
        
        if (strpos($content, 'selectColumns') === false) {
            $modelsWithoutSelectColumns[] = $filename;
        }
    }
}

if (empty($modelsWithoutSelectColumns)) {
    echo "✅ Todos os Models possuem selectColumns!\n";
} else {
    echo "⚠️  Models sem selectColumns: " . count($modelsWithoutSelectColumns) . "\n\n";
    foreach ($modelsWithoutSelectColumns as $model) {
        echo "   ⚠️  {$model}\n";
    }
}

// Resumo final
echo "\n" . str_repeat('=', 70) . "\n";
echo "📈 RESUMO\n";
echo str_repeat('=', 70) . "\n";
echo "✅ SELECTs Seguros: " . count($safe) . "\n";
echo "⚠️  Avisos: " . count($warnings) . "\n";
echo "❌ Erros Críticos: " . count($errors) . "\n";
echo "⚠️  Models sem selectColumns: " . count($modelsWithoutSelectColumns) . "\n";
echo str_repeat('=', 70) . "\n\n";

// Exit code
if (!empty($errors)) {
    echo "❌ Validação FALHOU! Corrija os erros críticos.\n";
    exit(1);
} elseif (!empty($warnings) || !empty($modelsWithoutSelectColumns)) {
    echo "⚠️  Validação com avisos. Recomenda-se corrigir.\n";
    exit(0);
} else {
    echo "✅ Validação PASSOU! Sistema seguro.\n";
    exit(0);
}
