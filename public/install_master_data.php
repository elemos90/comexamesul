<?php
/**
 * INSTALAÇÃO: Dados Mestres
 * Acesse: http://localhost/install_master_data.php
 */

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Connection;

// Prevenir acesso após instalação
if (file_exists(__DIR__ . '/../.master_data_installed')) {
    die('❌ Migration já foi executada. Apague o arquivo .master_data_installed para executar novamente.');
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
        }
        .links a:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🚀 Instalação: Dados Mestres</h1>
        <p class="subtitle">Sistema de Gestão de Disciplinas, Locais e Salas</p>

        <div class="info-box">
            <h3>📦 O que será instalado:</h3>
            <ul>
                <li>✅ Tabela de <strong>Disciplinas</strong> (10 exemplos)</li>
                <li>✅ Tabela de <strong>Locais</strong> (4 exemplos)</li>
                <li>✅ Tabela de <strong>Salas</strong> (19 exemplos)</li>
                <li>✅ Relacionamentos e índices</li>
            </ul>
        </div>

        <button id="installBtn" class="btn" onclick="install()">
            ⚡ Executar Instalação
        </button>

        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p>Instalando... Por favor aguarde...</p>
        </div>

        <div id="result" class="result"></div>

        <div id="links" class="links" style="display:none;">
            <h4>🎯 Próximos Passos:</h4>
            <a href="/master-data/disciplines">📚 Ver Disciplinas</a>
            <a href="/master-data/locations">📍 Ver Locais</a>
            <a href="/master-data/rooms">🏛️ Ver Salas</a>
            <a href="/juries/planning">📅 Criar Júris</a>
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
                const response = await fetch('?action=install', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
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
                    `;
                    links.style.display = 'block';
                } else {
                    result.className = 'result error';
                    result.innerHTML = `
                        <h3>❌ Erro na Instalação</h3>
                        <p>${data.message}</p>
                        ${data.errors ? '<ul>' + data.errors.map(e => '<li>' + e + '</li>').join('') + '</ul>' : ''}
                    `;
                    btn.disabled = false;
                }
            } catch (error) {
                loading.classList.remove('active');
                result.style.display = 'block';
                result.className = 'result error';
                result.innerHTML = `
                    <h3>❌ Erro de Conexão</h3>
                    <p>${error.message}</p>
                `;
                btn.disabled = false;
            }
        }
    </script>
</body>
</html>

<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_GET['action']) && $_GET['action'] === 'install') {
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
            'message' => 'Erro: ' . $e->getMessage()
        ]);
    }
    
    exit;
}
?>
