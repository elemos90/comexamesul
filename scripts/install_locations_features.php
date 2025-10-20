<?php

/**
 * Script de instalação das novas funcionalidades de Locais
 * 
 * Este script instala as tabelas necessárias para:
 * - Templates de Locais
 * - Estatísticas por Local
 * - Import/Export de planilhas
 * 
 * Uso: php scripts/install_locations_features.php
 */

require_once __DIR__ . '/../bootstrap.php';

echo "================================================\n";
echo "  INSTALAÇÃO: Funcionalidades de Locais\n";
echo "================================================\n\n";

// Carregar configurações do .env
$dbHost = env('DB_HOST', '127.0.0.1');
$dbPort = env('DB_PORT', '3306');
$dbName = env('DB_DATABASE', 'comexamesul');
$dbUser = env('DB_USERNAME', 'root');
$dbPass = env('DB_PASSWORD', '');

echo "🔌 Conectando ao banco de dados...\n";
echo "   Host: {$dbHost}:{$dbPort}\n";
echo "   Database: {$dbName}\n";
echo "   User: {$dbUser}\n\n";

try {
    $dsn = "mysql:host={$dbHost};port={$dbPort};dbname={$dbName};charset=utf8mb4";
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    
    echo "✅ Conexão estabelecida com sucesso!\n\n";
    
    // Ler arquivo de migração
    $migrationFile = __DIR__ . '/../app/Database/location_templates_migration.sql';
    
    if (!file_exists($migrationFile)) {
        throw new Exception("Arquivo de migração não encontrado: {$migrationFile}");
    }
    
    echo "📄 Lendo arquivo de migração...\n";
    $sql = file_get_contents($migrationFile);
    
    // Separar statements SQL
    $statements = array_filter(
        array_map('trim', explode(';', $sql)),
        function($stmt) { return !empty($stmt) && !preg_match('/^--/', $stmt); }
    );
    
    echo "📊 Executando migrações...\n\n";
    
    $success = 0;
    $errors = 0;
    
    foreach ($statements as $index => $statement) {
        try {
            // Extrair nome da tabela do CREATE TABLE
            if (preg_match('/CREATE TABLE.*?`?(\w+)`?\s*\(/i', $statement, $matches)) {
                $tableName = $matches[1];
                echo "   ⏳ Criando tabela: {$tableName}...";
                
                $pdo->exec($statement);
                
                echo " ✅\n";
                $success++;
            } else {
                $pdo->exec($statement);
                $success++;
            }
        } catch (PDOException $e) {
            $errors++;
            echo " ❌\n";
            echo "   Erro: " . $e->getMessage() . "\n";
        }
    }
    
    echo "\n================================================\n";
    echo "  RESULTADO DA INSTALAÇÃO\n";
    echo "================================================\n\n";
    echo "✅ Statements executados com sucesso: {$success}\n";
    echo ($errors > 0 ? "❌" : "✅") . " Erros: {$errors}\n\n";
    
    if ($errors === 0) {
        echo "🎉 INSTALAÇÃO CONCLUÍDA COM SUCESSO!\n\n";
        
        echo "📋 Tabelas criadas:\n";
        echo "   • location_templates (Templates de locais)\n";
        echo "   • location_template_disciplines (Disciplinas dos templates)\n";
        echo "   • location_template_rooms (Salas dos templates)\n";
        echo "   • location_stats (Estatísticas por local)\n\n";
        
        echo "🚀 Próximos passos:\n";
        echo "   1. Acesse o sistema: http://localhost/juries\n";
        echo "   2. Faça login como coordenador\n";
        echo "   3. Navegue para: Menu → Locais\n";
        echo "   4. Explore as 4 novas funcionalidades:\n";
        echo "      • Visualização por Local\n";
        echo "      • Dashboard de Estatísticas\n";
        echo "      • Templates de Locais\n";
        echo "      • Importar/Exportar\n\n";
        
        echo "📚 Documentação:\n";
        echo "   • NOVAS_FUNCIONALIDADES.md - Guia completo\n";
        echo "   • GUIA_CRIACAO_JURIS_POR_LOCAL.md - Como criar júris\n\n";
    } else {
        echo "⚠️  INSTALAÇÃO CONCLUÍDA COM ERROS\n";
        echo "   Revise os erros acima e corrija antes de usar o sistema.\n\n";
    }
    
} catch (Exception $e) {
    echo "\n❌ ERRO FATAL:\n";
    echo "   " . $e->getMessage() . "\n\n";
    exit(1);
}

echo "================================================\n";
