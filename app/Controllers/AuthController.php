<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\User;
use App\Models\SecurityQuestion;
use App\Services\ActivityLogger;
use App\Services\Logger;
use App\Services\NotificationService;
use App\Utils\Auth;
use App\Utils\Flash;
use App\Utils\RateLimiter;
use App\Utils\Validator;

class AuthController extends Controller
{
    public function showLogin(Request $request): string
    {
        return $this->view('auth/login');
    }

    public function login(Request $request)
    {
        // Aceitar username OU email para login
        $data = $request->only(['username', 'password']);
        $validator = new Validator();
        $rules = [
            'username' => 'required|min:3',
            'password' => 'required|min:6',
        ];
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados informados.');
            $_SESSION['old'] = $data;
            $_SESSION['errors'] = $validator->errors();
            redirect('/login');
        }

        $key = 'login:' . $request->ip();
        $maxAttempts = (int) env('RATE_LIMIT_MAX_ATTEMPTS', 5);
        $window = (int) env('RATE_LIMIT_WINDOW', 900);
        if (!RateLimiter::hit($key, $window, $maxAttempts)) {
            Flash::add('error', 'Muitas tentativas de login. Aguarde alguns minutos.');
            Logger::security('rate_limit_exceeded', ['ip' => $request->ip(), 'type' => 'login']);
            redirect('/login');
        }

        $userModel = new User();

        // Tentar encontrar por username primeiro, depois por email (retrocompatibilidade)
        $user = $userModel->findByUsername($data['username']);
        if (!$user) {
            // Fallback para email (para utilizadores existentes que ainda não migraram)
            $user = $userModel->findByEmail($data['username']);
        }

        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            $_SESSION['old'] = ['username' => $data['username']];
            Flash::add('error', 'Credenciais inválidas.');
            Logger::auth('login_failed', null, ['username' => $data['username']]);
            redirect('/login');
        }

        // Verificar se conta está activa
        if (isset($user['is_active']) && !$user['is_active']) {
            Flash::add('error', 'Conta desactivada. Contacte o Coordenador.');
            redirect('/login');
        }

        Auth::loginUser($user);
        RateLimiter::clear($key);
        Logger::auth('login_success', $user['id'], ['username' => $user['username'], 'role' => $user['role']]);
        ActivityLogger::log('auth', Auth::id(), 'login_success');

        // Verificar se deve alterar senha obrigatoriamente
        if (!empty($user['must_change_password'])) {
            Flash::add('warning', 'Deve alterar a sua palavra-passe temporária.');
            redirect('/auth/force-password-change');
        }

        // Verificar se perfil está completo
        if (empty($user['profile_complete'])) {
            Flash::add('info', 'Complete o seu perfil para aceder a todas as funcionalidades.');
            redirect('/profile/wizard');
        }

        Flash::add('success', 'Bem-vindo de volta!');
        redirect('/dashboard');
    }

    public function showRegister(): string
    {
        return $this->view('auth/register');
    }

    public function register(Request $request)
    {
        $data = $request->only(['name', 'username', 'email', 'password', 'password_confirmation']);
        $validator = new Validator();
        $rules = [
            'name' => 'required|min:3|max:120',
            'username' => 'required|min:3|max:50|alpha_dash',
            'password' => 'required|min:8',
            'password_confirmation' => 'required|min:8',
        ];

        // Email é opcional
        if (!empty($data['email'])) {
            $rules['email'] = 'email';
        }

        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados do formulário.');
            $_SESSION['old'] = $data;
            $_SESSION['errors'] = $validator->errors();
            redirect('/register');
        }

        // Validar força da senha (mín 8 chars, 1 número, 1 especial)
        if (!$this->validatePasswordStrength($data['password'])) {
            Flash::add('error', 'A senha deve ter pelo menos 8 caracteres, incluindo 1 número e 1 caractere especial.');
            $_SESSION['old'] = $data;
            redirect('/register');
        }

        if ($data['password'] !== $data['password_confirmation']) {
            Flash::add('error', 'As senhas devem coincidir.');
            $_SESSION['old'] = $data;
            redirect('/register');
        }

        $userModel = new User();

        // Verificar unicidade do username
        $username = strtolower(trim($data['username']));
        if (!$userModel->isUsernameAvailable($username)) {
            Flash::add('error', 'Este nome de utilizador já está em uso.');
            $_SESSION['old'] = $data;
            redirect('/register');
        }

        // Verificar unicidade do email (se fornecido)
        if (!empty($data['email']) && $userModel->findByEmail($data['email'])) {
            Flash::add('error', 'Este e-mail já está registado.');
            $_SESSION['old'] = $data;
            redirect('/register');
        }

        $userId = $userModel->createUser([
            'name' => trim($data['name']),
            'username' => $username,
            'email' => !empty($data['email']) ? strtolower(trim($data['email'])) : null,
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'vigilante',
            'profile_complete' => false,
            'must_change_password' => false,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLogger::log('users', $userId, 'register_self');
        Flash::add('success', 'Conta criada! Inicie sessão para completar o seu perfil.');
        redirect('/login');
    }

    /**
     * Validar força da senha
     */
    private function validatePasswordStrength(string $password): bool
    {
        // Mínimo 8 caracteres
        if (strlen($password) < 8) {
            return false;
        }
        // Pelo menos 1 número
        if (!preg_match('/[0-9]/', $password)) {
            return false;
        }
        // Pelo menos 1 caractere especial
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>_\-+=\[\]\\\\\/]/', $password)) {
            return false;
        }
        return true;
    }

    public function verifyEmail(Request $request)
    {
        Flash::add('info', 'A confirmação de e-mail não é mais necessária. Inicie sessão com as suas credenciais.');
        redirect('/login');
    }

    public function logout(): void
    {
        $userId = Auth::id();
        Logger::auth('logout', $userId);
        ActivityLogger::log('auth', $userId, 'logout');
        Auth::logout();
        Flash::add('success', 'Sessão encerrada.');
        redirect('/');
    }

    // ============================
    // ALTERAÇÃO OBRIGATÓRIA DE SENHA
    // ============================

    public function showForcePasswordChange(): string
    {
        $user = Auth::user();
        if (!$user) {
            redirect('/login');
        }
        if (empty($user['must_change_password'])) {
            redirect('/dashboard');
        }
        return $this->view('auth/force_password_change', ['user' => $user]);
    }

    public function forcePasswordChange(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            redirect('/login');
        }

        $password = $request->input('password');
        $confirm = $request->input('password_confirmation');

        if (!$password || strlen($password) < 8) {
            Flash::add('error', 'A senha deve ter pelo menos 8 caracteres.');
            redirect('/auth/force-password-change');
        }

        if (!$this->validatePasswordStrength($password)) {
            Flash::add('error', 'A senha deve conter pelo menos 1 número e 1 caractere especial.');
            redirect('/auth/force-password-change');
        }

        if ($password !== $confirm) {
            Flash::add('error', 'As senhas não coincidem.');
            redirect('/auth/force-password-change');
        }

        $userModel = new User();
        $userModel->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        $userModel->clearMustChangePassword((int) $user['id']);

        ActivityLogger::log('users', (int) $user['id'], 'forced_password_change');

        // Atualizar sessão
        $updatedUser = $userModel->find((int) $user['id']);
        Auth::loginUser($updatedUser);

        Flash::add('success', 'Palavra-passe atualizada com sucesso!');

        // Verificar se perfil está completo
        if (empty($updatedUser['profile_complete'])) {
            redirect('/profile/wizard');
        }

        redirect('/dashboard');
    }

    // ============================
    // ESQUECI A SENHA (SEM EMAIL)
    // ============================

    public function showForgotPassword(): string
    {
        return $this->view('auth/forgot');
    }

    public function forgotPassword(Request $request)
    {
        $username = $request->input('username');
        if (!$username || strlen($username) < 3) {
            Flash::add('error', 'Informe o seu nome de utilizador.');
            redirect('/password/forgot');
        }

        $userModel = new User();
        $user = $userModel->findByUsername($username);

        if (!$user) {
            // Por segurança, não revelamos se o utilizador existe
            Flash::add('success', 'Se o utilizador existir, será enviado um pedido ao Coordenador.');
            redirect('/login');
        }

        // Criar pedido de reset (interno)
        $requestId = $userModel->createPasswordResetRequest((int) $user['id']);

        // Notificar coordenadores
        try {
            $notificationService = new NotificationService();
            // Notificar coordenadores e administradores
            $coordinators = $userModel->findByRole(['coordenador', 'admin', 'administrador']);
            $coordinatorIds = array_column($coordinators, 'id');

            if (!empty($coordinatorIds)) {
                $notificationService->createAutomaticNotification(
                    'urgente',
                    'Pedido de Reset de Senha',
                    "O utilizador '{$user['name']}' (@{$user['username']}) solicitou a redefinição da palavra-passe.",
                    'password_reset_request',
                    $requestId,
                    $coordinatorIds
                );
            }
        } catch (\Exception $e) {
            // Log error but don't fail the request
            error_log("Failed to notify coordinators: " . $e->getMessage());
        }

        ActivityLogger::log('auth', (int) $user['id'], 'password_reset_requested');
        Flash::add('success', 'Pedido enviado! O Coordenador irá redefinir a sua senha e será notificado.');
        redirect('/login');
    }

    public function showResetPassword(Request $request): string
    {
        // Mantido para retrocompatibilidade com links de email antigos
        return $this->view('auth/reset', [
            'token' => $request->query('token'),
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        // Mantido para retrocompatibilidade
        $email = $request->input('email');
        $token = $request->input('token');
        $password = $request->input('password');
        $confirm = $request->input('password_confirmation');

        if (!$email || !$token || !$password) {
            Flash::add('error', 'Dados inválidos.');
            redirect('/password/reset?token=' . urlencode($token ?? '') . '&email=' . urlencode($email ?? ''));
        }

        if ($password !== $confirm) {
            Flash::add('error', 'As senhas não coincidem.');
            redirect('/password/reset?token=' . urlencode($token) . '&email=' . urlencode($email));
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);
        if (!$user) {
            Flash::add('error', 'Conta não encontrada.');
            redirect('/password/forgot');
        }

        $userModel->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        ActivityLogger::log('users', (int) $user['id'], 'password_reset');
        Flash::add('success', 'Palavra-passe atualizada. Faça login.');
        redirect('/login');
    }

    // ============================
    // RECOVERY WIZARD (NEW)
    // ============================

    public function showRecoverStep1(): string
    {
        return $this->view('auth/recover/step1_username');
    }

    public function checkUsername(Request $request)
    {
        $username = trim($request->input('username'));
        if (!$username) {
            Flash::add('error', 'Informe o utilizador.');
            redirect('/recover');
        }

        $userModel = new User();
        // Check username or email
        $user = $userModel->findByUsername($username);
        if (!$user) {
            $user = $userModel->findByEmail($username);
        }

        $_SESSION['recovery_username'] = $username; // For fallback display

        // Se não encontrar ou não tiver métodos -> Fallback direta (Security: Ambiguity)
        if (!$user) {
            redirect('/recover/fallback');
        }

        // Check active setup
        if (isset($user['is_active']) && !$user['is_active']) {
            Flash::add('error', 'Conta desactivada.');
            redirect('/login');
        }

        $methods = $userModel->getRecoveryMethods((int) $user['id']);
        if (empty($methods)) {
            redirect('/recover/fallback');
        }

        // Setup Session
        $_SESSION['recovery_user_id'] = $user['id'];
        $_SESSION['recovery_methods'] = $methods;

        redirect('/recover/method');
    }

    public function showRecoverStep2(): string
    {
        if (!isset($_SESSION['recovery_user_id']) || !isset($_SESSION['recovery_methods'])) {
            redirect('/recover');
        }

        return $this->view('auth/recover/step2_method', [
            'methods' => $_SESSION['recovery_methods']
        ]);
    }

    public function selectMethod(Request $request)
    {
        $method = $request->input('method');
        // Validate if available
        $methods = $_SESSION['recovery_methods'] ?? [];
        if (!isset($methods[$method])) {
            redirect('/recover/method');
        }

        $_SESSION['recovery_current_method'] = $method;
        redirect('/recover/verify');
    }

    public function showRecoverStep3(): string
    {
        if (!isset($_SESSION['recovery_user_id']) || !isset($_SESSION['recovery_current_method'])) {
            redirect('/recover');
        }

        $method = $_SESSION['recovery_current_method'];
        $data = ['method' => $method];

        if ($method === 'questions') {
            $sqModel = new SecurityQuestion();
            $userQuestions = $sqModel->getUserQuestions($_SESSION['recovery_user_id']);
            $data['questions'] = $userQuestions;
        }

        return $this->view('auth/recover/step3_verify', $data);
    }

    public function verifyCredential(Request $request)
    {
        if (!isset($_SESSION['recovery_user_id'])) {
            redirect('/recover');
        }

        $userId = $_SESSION['recovery_user_id'];
        $method = $_SESSION['recovery_current_method'];
        $userModel = new User();
        $valid = false;

        // Rate Limit Check (Session based for simplicity in Wizard)
        if (!isset($_SESSION['recovery_attempts']))
            $_SESSION['recovery_attempts'] = 0;
        if ($_SESSION['recovery_attempts'] >= 3) {
            Flash::add('error', 'Muitas tentativas falhadas. Solicite apoio.');
            redirect('/recover/fallback');
        }

        if ($method === 'keyword') {
            $input = $request->input('keyword');
            $valid = $userModel->verifyRecoveryKeyword($userId, $input);
        } elseif ($method === 'pin') {
            $input = $request->input('pin');
            $valid = $userModel->verifyRecoveryPin($userId, $input);
        } elseif ($method === 'questions') {
            $a1 = trim($request->input('answer_1'));
            $a2 = trim($request->input('answer_2'));
            $ids = $request->input('question_ids', []); // Array of IDs presented

            // Verify
            $sqModel = new SecurityQuestion();
            $stored = $sqModel->getUserQuestions($userId);

            // Verify Logic
            $storedMap = [];
            foreach ($stored as $s)
                $storedMap[$s['id']] = $s['answer_hash'];

            if (isset($storedMap[$ids[0]]) && isset($storedMap[$ids[1]])) {
                $v1 = password_verify(mb_strtolower($a1), $storedMap[$ids[0]]);
                $v2 = password_verify(mb_strtolower($a2), $storedMap[$ids[1]]);
                $valid = $v1 && $v2;
            }
        }

        if ($valid) {
            $_SESSION['recovery_verified'] = true;
            $_SESSION['recovery_attempts'] = 0;
            redirect('/recover/reset');
        } else {
            $_SESSION['recovery_attempts']++;
            Flash::add('error', 'Dados incorretos. Tente novamente.');
            redirect('/recover/verify');
        }
    }

    public function showRecoverStep4(): string
    {
        if (!isset($_SESSION['recovery_verified'])) {
            redirect('/recover');
        }
        return $this->view('auth/recover/step4_reset');
    }

    public function finalizeRecovery(Request $request)
    {
        if (!isset($_SESSION['recovery_verified']) || !isset($_SESSION['recovery_user_id'])) {
            redirect('/recover');
        }

        $password = $request->input('password');
        $confirm = $request->input('password_confirmation');

        if (strlen($password) < 8 || $password !== $confirm) {
            Flash::add('error', 'Senha inválida ou não coincide (mín 8 caracteres).');
            redirect('/recover/reset');
        }

        $userModel = new User();
        $userModel->updatePassword((int) $_SESSION['recovery_user_id'], password_hash($password, PASSWORD_DEFAULT));

        // Log
        ActivityLogger::log('users', (int) $_SESSION['recovery_user_id'], 'password_reset_self');

        // Cleanup
        unset($_SESSION['recovery_verified'], $_SESSION['recovery_user_id'], $_SESSION['recovery_methods'], $_SESSION['recovery_current_method']);

        Flash::add('success', 'Senha atualizada com sucesso. Faça login.');
        redirect('/login');
    }

    public function showFallback(): string
    {
        return $this->view('auth/recover/fallback');
    }

    public function processFallback(Request $request)
    {
        $username = $_SESSION['recovery_username'] ?? $request->input('username');

        $userModel = new User();
        $user = $userModel->findByUsername($username ?? '');

        if ($user) {
            $requestId = $userModel->createPasswordResetRequest((int) $user['id']);
            try {
                $notificationService = new NotificationService();
                $coordinators = $userModel->findByRole(['coordenador', 'admin', 'administrador']);
                $ids = array_column($coordinators, 'id');
                if ($ids) {
                    $notificationService->createAutomaticNotification(
                        'urgente',
                        'Pedido de Suporte (Recuperação)',
                        "O utilizador '{$user['name']}' não conseguiu recuperar a conta e solicitou apoio.",
                        'password_reset_request',
                        $requestId,
                        $ids
                    );
                }
            } catch (\Exception $e) {
            }
        }

        Flash::add('success', 'Pedido enviado à Comissão. Aguarde contacto.');
        redirect('/login');
    }
}
