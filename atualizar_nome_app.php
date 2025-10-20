<?php
/**
 * Script para atualizar o nome da aplicação no arquivo .env
 * 
 * Execução:
 * php atualizar_nome_app.php
 */

$envFile = __DIR__ . '/.env';
$newAppName = 'Portal da Comissão de Exames de Admissão';

echo "🔄 Atualizando nome da aplicação...\n\n";

if (!file_exists($envFile)) {
    echo "❌ Arquivo .env não encontrado!\n";
    echo "💡 Copie .env.example para .env primeiro:\n";
    echo "   copy .env.example .env\n\n";
    exit(1);
}

// Ler arquivo .env
$content = file_get_contents($envFile);

// Verificar se APP_NAME existe
if (strpos($content, 'APP_NAME=') !== false) {
    // Substituir linha inteira do APP_NAME
    $content = preg_replace(
        '/^APP_NAME=.*$/m',
        'APP_NAME="' . $newAppName . '"',
        $content
    );
    echo "✅ APP_NAME encontrado e atualizado!\n";
} else {
    // Adicionar APP_NAME no início do arquivo
    $content = 'APP_NAME="' . $newAppName . '"' . "\n" . $content;
    echo "✅ APP_NAME adicionado ao arquivo!\n";
}

// Atualizar MAIL_FROM_NAME se existir
if (strpos($content, 'MAIL_FROM_NAME=') !== false) {
    $content = preg_replace(
        '/^MAIL_FROM_NAME=.*$/m',
        'MAIL_FROM_NAME="' . $newAppName . '"',
        $content
    );
    echo "✅ MAIL_FROM_NAME atualizado!\n";
}

// Salvar arquivo
file_put_contents($envFile, $content);

echo "\n🎉 Nome da aplicação atualizado com sucesso!\n";
echo "📝 Novo nome: $newAppName\n\n";
echo "🔄 Recarregue as páginas do sistema para ver a mudança.\n";
