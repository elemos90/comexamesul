<?php
/**
 * INSTALADOR SIMPLES - Dados Mestres
 * Acesse: http://localhost/comexamesul/public/install.php
 */

declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Connection;

// Verificar se já foi instalado
$installed = file_exists(__DIR__ . '/../.master_data_installed');

// Processar instalação via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    header('Content-Type: application/json');
    
    try {
        $db = Connection::getInstance();
        
        // Ler arquivo SQL
        $sqlFile = __DIR__ . '/../app/Database/migrations_master_data_simple.sql';
        
        if (!file_exists($sqlFile)) {
            echo json_encode([
                'success' => false,
                'message' => 'Arquivo SQL não encontrado: ' . $sqlFile
            ]);
            exit;
        }
        
        $sql = file_get_contents($sqlFile);
        
        // Executar SQL
        $db->exec($sql);
        
        // Verificar tabelas criadas
        $disciplines = $db->query("SELECT COUNT(*) as count FROM disciplines")->fetch(PDO::FETCH_ASSOC);
        $locations = $db->query("SELECT COUNT(*) as count FROM exam_locations")->fetch(PDO::FETCH_ASSOC);
        $rooms = $db->query("SELECT COUNT(*) as count FROM exam_rooms")->fetch(PDO::FETCH_ASSOC);
        
        // Criar arquivo de flag
        file_put_contents(__DIR__ . '/../.master_data_installed', date('Y-m-d H:i:s'));
        
        echo json_encode([
            'success' => true,
            'message' => 'Instalação concluída!',
            'counts' => [
                'disciplines' => $disciplines['count'],
                'locations' => $locations['count'],
                'rooms' => $rooms['count']
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Erro: ' . $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
    }
    
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Dados Mestres</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 800px;
            width: 100%;
            padding: 40px;
        }
        h1 {
            color: #667eea;
            font-size: 32px;
            margin-bottom: 10px;
        }
        .subtitle {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }
        .info-box {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
        }
        .info-box h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 18px;
        }
        .info-box ul {
            margin-left: 20px;
        }
        .info-box li {
            margin: 8px 0;
            color: #333;
        }
        .warning-box {
            background: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            color: #92400e;
        }
        .success-box {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 20px;
            margin: 20px 0;
            border-radius: 8px;
            color: #065f46;
        }
        .btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 16px 32px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        .btn-secondary {
            background: #6b7280;
            margin-top: 10px;
            display: block;
            text-align: center;
            text-decoration: none;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .result {
            margin-top: 30px;
            padding: 20px;
            border-radius: 8px;
            display: none;
        }
        .result.success {
            background: #d1fae5;
            border: 2px solid #10b981;
            color: #065f46;
        }
        .result.error {
            background: #fee2e2;
            border: 2px solid #ef4444;
            color: #991b1b;
        }
        .result h3 {
            margin-bottom: 15px;
            font-size: 20px;
        }
        .result ul {
            margin-left: 20px;
        }
        .result li {
            margin: 8px 0;
        }
        .result pre {
            background: rgba(0,0,0,0.05);
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            margin-top: 10px;
            font-size: 11px;
            max-height: 300px;
        }
        .loading {
            display: none;
            text-align: center;
            margin: 20px 0;
        }
        .loading.active {
            display: block;
        }
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        .links {
            margin-top: 30px;
            padding: 20px;
            background: #f9fafb;
            border-radius: 8px;
        }
        .links h4 {
            margin-bottom: 15px;
            color: #333;
        }
        .links a {
            display: inline-block;
            margin: 5px 10px 5px 0;
            padding: 8px 16px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            transition: background 0.2s;
        }
        .links a:hover {
            background: #5568d3;
        }
        code {
            background: rgba(0,0,0,0.05);
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalação: Dados Mestres</h1>
        <p class="subtitle">Sistema de Gestão de Disciplinas, Locais e Salas</p>

        <?php if ($installed): ?>
            <div class="success-box">
                <strong>✅ Já Instalado</strong>
                <p>Os dados mestres já foram instalados anteriormente em: <strong><?= file_get_contents(__DIR__ . '/../.master_data_installed') ?></strong></p>
                <p style="margin-top: 10px;">Para reinstalar, apague o arquivo <code>.master_data_installed</code> na raiz do projeto.</p>
            </div>
        <?php else: ?>
            <div class="info-box">
                <h3>📦 O que será instalado:</h3>
                <ul>
                    <li>✅ Tabela de <strong>Disciplinas</strong> (10 exemplos)</li>
                    <li>✅ Tabela de <strong>Locais</strong> (4 exemplos)</li>
                    <li>✅ Tabela de <strong>Salas</strong> (19 exemplos)</li>
                    <li>✅ Relacionamentos e índices</li>
                </ul>
            </div>
        <?php endif; ?>

        <button id="installBtn" class="btn" onclick="install()" <?= $installed ? 'disabled' : '' ?>>
            <?= $installed ? '✅ Já Instalado' : '⚡ Executar Instalação' ?>
        </button>

        <a href="index.php?url=/master-data/disciplines" class="btn btn-secondary">
            🏠 Ir para Disciplinas
        </a>

        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p>Instalando... Por favor aguarde...</p>
        </div>

        <div id="result" class="result"></div>

        <div id="links" class="links" style="display:none;">
            <h4>🎯 Próximos Passos:</h4>
            <a href="index.php?url=/master-data/disciplines">📚 Ver Disciplinas</a>
            <a href="index.php?url=/master-data/locations">📍 Ver Locais</a>
            <a href="index.php?url=/master-data/rooms">🏛️ Ver Salas</a>
            <a href="index.php?url=/juries/planning">📅 Criar Júris</a>
        </div>
    </div>

    <script>
        async function install() {
            const btn = document.getElementById('installBtn');
            const loading = document.getElementById('loading');
            const result = document.getElementById('result');
            const links = document.getElementById('links');

            btn.disabled = true;
            loading.classList.add('active');
            result.style.display = 'none';

            try {
                const formData = new FormData();
                formData.append('action', 'install');

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });

                const data = await response.json();

                loading.classList.remove('active');
                result.style.display = 'block';

                if (data.success) {
                    result.className = 'result success';
                    result.innerHTML = `
                        <h3>✅ Instalação Concluída com Sucesso!</h3>
                        <ul>
                            <li><strong>Disciplinas:</strong> ${data.counts.disciplines} cadastradas</li>
                            <li><strong>Locais:</strong> ${data.counts.locations} cadastrados</li>
                            <li><strong>Salas:</strong> ${data.counts.rooms} cadastradas</li>
                        </ul>
                        <p style="margin-top: 15px; font-weight: bold;">✅ Sistema pronto para uso!</p>
                        <p style="margin-top: 10px;">A página será recarregada em 3 segundos...</p>
                    `;
                    links.style.display = 'block';
                    
                    // Recarregar após 3 segundos
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    result.className = 'result error';
                    result.innerHTML = `
                        <h3>❌ Erro na Instalação</h3>
                        <p><strong>Mensagem:</strong> ${data.message}</p>
                        ${data.trace ? '<details><summary>Ver detalhes técnicos</summary><pre>' + data.trace + '</pre></details>' : ''}
                        <div style="margin-top: 15px; padding: 15px; background: rgba(0,0,0,0.05); border-radius: 8px;">
                            <p style="margin-bottom: 10px;"><strong>💡 Dicas de Resolução:</strong></p>
                            <ul>
                                <li>Verifique se o MySQL está rodando (XAMPP Control Panel)</li>
                                <li>Verifique se o banco <code>comexamesul</code> existe</li>
                                <li>Verifique se a tabela <code>users</code> existe (execute migrations principais primeiro)</li>
                                <li>Verifique o arquivo <code>.env</code> (credenciais do banco)</li>
                            </ul>
                        </div>
                    `;
                    btn.disabled = false;
                }
            } catch (error) {
                loading.classList.remove('active');
                result.style.display = 'block';
                result.className = 'result error';
                result.innerHTML = `
                    <h3>❌ Erro de Conexão</h3>
                    <p><strong>Erro:</strong> ${error.message}</p>
                    <div style="margin-top: 15px; padding: 15px; background: rgba(0,0,0,0.05); border-radius: 8px;">
                        <p style="margin-bottom: 10px;"><strong>Possíveis causas:</strong></p>
                        <ul>
                            <li>Apache não está rodando</li>
                            <li>MySQL não está rodando</li>
                            <li>Banco de dados não configurado</li>
                            <li>Erro de permissões</li>
                        </ul>
                    </div>
                `;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>
