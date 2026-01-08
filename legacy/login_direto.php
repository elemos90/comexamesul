<?php
/**
 * LOGIN DIRETO - Bypass de rotas
 * Acesse: http://localhost/comexamesul/public/login_direto.php
 */

session_start();

require_once __DIR__ . '/../bootstrap.php';

use App\Database\Connection;
use App\Utils\Flash;

$error = '';
$success = '';

// Processar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    
    try {
        $db = Connection::getInstance();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($user && password_verify($password, $user['password_hash'])) {
            // Login bem-sucedido
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            $success = "Login realizado com sucesso! Redirecionando...";
            
            // Redirecionar após 2 segundos
            header("refresh:2;url=dashboard_direto.php");
        } else {
            $error = "Email ou senha incorretos.";
        }
    } catch (Exception $e) {
        $error = "Erro ao fazer login: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portal de Exames</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .login-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }
        h1 {
            color: #667eea;
            font-size: 28px;
            margin-bottom: 10px;
            text-align: center;
        }
        .subtitle {
            color: #666;
            text-align: center;
            margin-bottom: 30px;
            font-size: 14px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            display: block;
            color: #333;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 14px;
        }
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.4);
        }
        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 2px solid #ef4444;
        }
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 2px solid #10b981;
        }
        .info-box {
            background: #f0f4ff;
            border-left: 4px solid #667eea;
            padding: 16px;
            margin-top: 20px;
            border-radius: 8px;
        }
        .info-box h3 {
            color: #667eea;
            font-size: 14px;
            margin-bottom: 10px;
        }
        .info-box ul {
            margin-left: 20px;
            font-size: 13px;
            color: #333;
        }
        .info-box li {
            margin: 5px 0;
        }
        code {
            background: rgba(0,0,0,0.05);
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
        }
        .links {
            text-align: center;
            margin-top: 20px;
            font-size: 13px;
        }
        .links a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .links a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>🔐 Login</h1>
        <p class="subtitle">Portal da Comissão de Exames</p>

        <?php if ($error): ?>
            <div class="alert alert-error">
                ❌ <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="alert alert-success">
                ✅ <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required 
                       placeholder="seu-email@example.com"
                       value="coordenador@unilicungo.ac.mz">
            </div>

            <div class="form-group">
                <label for="password">Senha</label>
                <input type="password" id="password" name="password" required 
                       placeholder="••••••••"
                       value="password">
            </div>

            <button type="submit" class="btn">Entrar</button>
        </form>

        <div class="info-box">
            <h3>👤 Contas de Teste</h3>
            <ul>
                <li><strong>Coordenador:</strong><br>
                    Email: <code>coordenador@unilicungo.ac.mz</code><br>
                    Senha: <code>password</code>
                </li>
                <li><strong>Membro:</strong><br>
                    Email: <code>membro@unilicungo.ac.mz</code><br>
                    Senha: <code>password</code>
                </li>
                <li><strong>Vigilante:</strong><br>
                    Email: <code>vigilante1@unilicungo.ac.mz</code><br>
                    Senha: <code>password</code>
                </li>
            </ul>
        </div>

        <div class="links">
            <a href="test_master_data.php">🔍 Ver Dados Instalados</a>
        </div>
    </div>
</body>
</html>
