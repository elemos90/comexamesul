<!DOCTYPE html>
<html lang="pt">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - Dados Mestres</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
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
            background: rgba(0, 0, 0, 0.05);
            padding: 10px;
            border-radius: 4px;
            overflow-x: auto;
            margin-top: 10px;
            font-size: 12px;
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
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
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
    </style>
</head>

<body>
    <div class="container">
        <h1>🚀 Instalação: Dados Mestres</h1>
        <p class="subtitle">Sistema de Gestão de Disciplinas, Locais e Salas</p>

        <?php if ($installed): ?>
            <div class="warning-box">
                <strong>⚠️ Já Instalado</strong>
                <p>Os dados mestres já foram instalados anteriormente.</p>
                <p>Para reinstalar, apague o arquivo <code>.master_data_installed</code> na raiz do projeto.</p>
            </div>
        <?php endif; ?>

        <div class="info-box">
            <h3>📦 O que será instalado:</h3>
            <ul>
                <li>✅ Tabela de <strong>Disciplinas</strong> (10 exemplos)</li>
                <li>✅ Tabela de <strong>Locais</strong> (4 exemplos)</li>
                <li>✅ Tabela de <strong>Salas</strong> (19 exemplos)</li>
                <li>✅ Relacionamentos e índices</li>
            </ul>
        </div>

        <button id="installBtn" class="btn" onclick="install()" <?= $installed ? 'disabled' : '' ?>>
            <?= $installed ? '✅ Já Instalado' : '⚡ Executar Instalação' ?>
        </button>

        <a href="<?= url('/master-data/disciplines') ?>" class="btn btn-secondary"
            style="display: block; text-align: center; text-decoration: none;">
            🏠 Voltar ao Sistema
        </a>

        <div id="loading" class="loading">
            <div class="spinner"></div>
            <p>Instalando... Por favor aguarde...</p>
        </div>

        <div id="result" class="result"></div>

        <div id="links" class="links" style="display:none;">
            <h4>🎯 Próximos Passos:</h4>
            <a href="<?= url('/master-data/disciplines') ?>">📚 Ver Disciplinas</a>
            <a href="<?= url('/master-data/locations') ?>">📍 Ver Locais</a>
            <a href="<?= url('/master-data/rooms') ?>">🏛️ Ver Salas</a>
            <a href="<?= url('/juries/planning') ?>">📅 Criar Júris</a>
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
                const response = await fetch('<?= url('/install/master-data/execute') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
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
                        <p style="margin-top: 15px;">✅ Sistema pronto para uso!</p>
                    `;
                    links.style.display = 'block';
                } else {
                    result.className = 'result error';
                    result.innerHTML = `
                        <h3>❌ Erro na Instalação</h3>
                        <p><strong>Mensagem:</strong> ${data.message}</p>
                        ${data.trace ? '<pre>' + data.trace + '</pre>' : ''}
                        <p style="margin-top: 15px;"><strong>Dica:</strong> Verifique se as migrations principais foram executadas (tabela 'users' existe).</p>
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
                    <p style="margin-top: 10px;"><strong>Possíveis causas:</strong></p>
                    <ul>
                        <li>Servidor Apache/MySQL não está rodando</li>
                        <li>Banco de dados não configurado corretamente</li>
                        <li>Erro de permissões no servidor</li>
                    </ul>
                `;
                btn.disabled = false;
            }
        }
    </script>
</body>

</html>