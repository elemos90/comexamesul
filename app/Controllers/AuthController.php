<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\MailService;
use App\Services\PasswordResetService;
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
        $data = $request->only(['email', 'password']);
        $validator = new Validator();
        $rules = [
            'email' => 'required|email',
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
            redirect('/login');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($data['email']);
        if (!$user || !password_verify($data['password'], $user['password_hash'])) {
            $_SESSION['old'] = ['email' => $data['email']];
            Flash::add('error', 'Credenciais invalidas.');
            redirect('/login');
        }

        Auth::loginUser($user);
        RateLimiter::clear($key);
        ActivityLogger::log('auth', Auth::id(), 'login_success');
        Flash::add('success', 'Bem-vindo de volta!');
        redirect('/dashboard');
    }

    public function showRegister(): string
    {
        return $this->view('auth/register');
    }

    public function register(Request $request)
    {
        $data = $request->only(['name', 'email', 'password', 'password_confirmation']);
        $validator = new Validator();
        $rules = [
            'name' => 'required|min:3|max:120',
            'email' => 'required|email',
            'password' => 'required|min:6',
            'password_confirmation' => 'required|min:6',
        ];
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados do formulario.');
            $_SESSION['old'] = $data;
            $_SESSION['errors'] = $validator->errors();
            redirect('/register');
        }

        if ($data['password'] !== $data['password_confirmation']) {
            Flash::add('error', 'As senhas devem coincidir.');
            redirect('/register');
        }

        $userModel = new User();
        if ($userModel->findByEmail($data['email'])) {
            Flash::add('error', 'E-mail ja registado.');
            redirect('/register');
        }

        $userId = $userModel->createUser([
            'name' => trim($data['name']),
            'email' => strtolower(trim($data['email'])),
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => 'vigilante',
            'email_verified_at' => now(),
            'verification_token' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLogger::log('users', $userId, 'register_self');
        Flash::add('success', 'Conta criada! Ja pode iniciar sessao.');
        redirect('/login');
    }

    public function verifyEmail(Request $request)
    {
        Flash::add('info', 'A confirmacao de e-mail nao e mais necessaria. Inicie sessao com as suas credenciais.');
        redirect('/login');
    }

    public function logout(): void
    {
        ActivityLogger::log('auth', Auth::id(), 'logout');
        Auth::logout();
        Flash::add('success', 'Sessao encerrada.');
        redirect('/');
    }

    public function showForgotPassword(): string
    {
        return $this->view('auth/forgot');
    }

    public function forgotPassword(Request $request)
    {
        $email = $request->input('email');
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Flash::add('error', 'Informe um e-mail valido.');
            redirect('/password/forgot');
        }
        $userModel = new User();
        $user = $userModel->findByEmail($email);
        if (!$user) {
            Flash::add('error', 'Conta nao encontrada.');
            redirect('/password/forgot');
        }
        $resetService = new PasswordResetService();
        $token = $resetService->createToken($email);
        $link = env('APP_URL', 'http://localhost') . '/password/reset?token=' . urlencode($token) . '&email=' . urlencode($email);
        $mail = new MailService();
        $subject = 'Recuperacao de Palavra-passe';
        $body = sprintf('<p>Ola %s,</p><p>Para definir uma nova palavra-passe clique no link abaixo (valido por 60 minutos):</p><p><a href="%s">Repor palavra-passe</a></p>', htmlspecialchars($user['name']), $link);
        $mail->send($email, $subject, $body, env('MAIL_FROM_ADDRESS', 'no-reply@example.com'), env('MAIL_FROM_NAME', 'Portal de Exames'));
        Flash::add('success', 'Se o e-mail existir, enviaremos instrucoes.');
        redirect('/login');
    }

    public function showResetPassword(Request $request): string
    {
        return $this->view('auth/reset', [
            'token' => $request->query('token'),
            'email' => $request->query('email'),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $email = $request->input('email');
        $token = $request->input('token');
        $password = $request->input('password');
        $confirm = $request->input('password_confirmation');

        if (!$email || !$token || !$password) {
            Flash::add('error', 'Dados invalidos.');
            redirect('/password/reset?token=' . urlencode($token) . '&email=' . urlencode($email));
        }

        if ($password !== $confirm) {
            Flash::add('error', 'As senhas nao coincidem.');
            redirect('/password/reset?token=' . urlencode($token) . '&email=' . urlencode($email));
        }

        $resetService = new PasswordResetService();
        if (!$resetService->validateToken($email, $token)) {
            Flash::add('error', 'Token invalido ou expirado.');
            redirect('/password/forgot');
        }

        $userModel = new User();
        $user = $userModel->findByEmail($email);
        if (!$user) {
            Flash::add('error', 'Conta nao encontrada.');
            redirect('/password/forgot');
        }

        $userModel->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        $resetService->consumeToken($email);
        ActivityLogger::log('users', (int) $user['id'], 'password_reset');
        Flash::add('success', 'Palavra-passe atualizada. Faca login.');
        redirect('/login');
    }
}
