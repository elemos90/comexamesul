<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\User;
use App\Models\SecurityQuestion;
use App\Services\ActivityLogger;
use App\Utils\Auth;
use App\Utils\Flash;
use App\Utils\Validator;

class ProfileController extends Controller
{
    public function show(): string
    {
        $user = Auth::user();

        // Se perfil não está completo, redirecionar para o wizard
        if (empty($user['profile_complete'])) {
            redirect('/profile/wizard');
        }

        return $this->view('profile/index', ['user' => $user]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Email não pode ser alterado - removido da lista de campos aceitos
        $data = $request->only([
            'name',
            'phone',
            'gender',
            'birth_date',
            'document_type',
            'document_number',
            'origin_university',
            'university',
            'nuit',
            'degree',
            'major_area',
            'bank_name',
            'nib',
            'bank_account_holder'
        ]);
        $data = array_map(static function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $data);

        $validator = new Validator();
        $rules = [
            'name' => 'required|min:3|max:150',
            'phone' => 'required|phone_mz',
            'gender' => 'required|in:M,F,O',
            'birth_date' => 'required|date',
            'document_type' => 'required|in:BI,Passaporte,DIRE',
            'document_number' => 'required|min:5|max:50',
            'origin_university' => 'required|min:3|max:200',
            'university' => 'required|max:150',
            'nuit' => 'required|nuit',
            'degree' => 'required|in:Licenciado,Mestre,Doutor',
            'major_area' => 'required|max:150',
            'bank_name' => 'required|max:150',
            'nib' => 'required|nib',
            'bank_account_holder' => 'required|min:3|max:150',
        ];
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Corrija os campos assinalados.');
            $_SESSION['errors'] = $validator->errors();
            redirect('/profile');
        }

        $model = new User();
        $model->updateUser((int) $user['id'], $data);

        // Verificar e atualizar status de perfil completo (ambas as colunas)
        $model->checkAndUpdateProfileStatus((int) $user['id']);

        // Sincronizar a nova coluna profile_complete
        $updatedUser = $model->find((int) $user['id']);
        if ($model->isProfileComplete($updatedUser)) {
            $model->setProfileComplete((int) $user['id'], true);
        }

        // Atualizar a sessão com os novos dados
        $refreshedUser = $model->find((int) $user['id']);
        Auth::loginUser($refreshedUser);

        ActivityLogger::log('users', (int) $user['id'], 'profile_update', $data);
        Flash::add('success', 'Perfil atualizado com sucesso.');
        redirect('/profile');
    }

    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $current = $request->input('current_password');
        $password = $request->input('password');
        $confirm = $request->input('password_confirmation');
        if (!$current || !$password) {
            Flash::add('error', 'Preencha todos os campos.');
            redirect('/profile');
        }
        if (!password_verify($current, $user['password_hash'])) {
            Flash::add('error', 'Palavra-passe atual incorreta.');
            redirect('/profile');
        }
        if ($password !== $confirm) {
            Flash::add('error', 'As novas palavras-passe não coincidem.');
            redirect('/profile');
        }
        $model = new User();
        $model->updatePassword((int) $user['id'], password_hash($password, PASSWORD_DEFAULT));
        ActivityLogger::log('users', (int) $user['id'], 'password_change');
        Flash::add('success', 'Palavra-passe atualizada.');
        redirect('/profile');
    }

    public function updateAvatar(Request $request)
    {
        $user = Auth::user();
        $file = $request->files('avatar');
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            Flash::add('error', 'Falha ao carregar a imagem.');
            redirect('/profile');
        }
        if ($file['size'] > 2 * 1024 * 1024) {
            Flash::add('error', 'Imagem deve ter no máximo 2MB.');
            redirect('/profile');
        }
        $allowed = ['image/jpeg' => '.jpg', 'image/png' => '.png'];
        $mime = mime_content_type($file['tmp_name']);
        if (!isset($allowed[$mime])) {
            Flash::add('error', 'Formato inválido (apenas JPG ou PNG).');
            redirect('/profile');
        }
        $filename = 'avatar_' . $user['id'] . '_' . time() . $allowed[$mime];
        $targetDir = public_path('uploads/avatars');
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0775, true);
        }
        $target = $targetDir . '/' . $filename;
        if (!move_uploaded_file($file['tmp_name'], $target)) {
            Flash::add('error', 'Não foi possível guardar o avatar.');
            redirect('/profile');
        }
        $relativePath = '/uploads/avatars/' . $filename;
        $model = new User();
        $model->updateUser((int) $user['id'], ['avatar_url' => $relativePath]);
        ActivityLogger::log('users', (int) $user['id'], 'avatar_update');
        Flash::add('success', 'Foto atualizada.');
        redirect('/profile');
    }

    public function showRecovery(): void
    {
        $user = Auth::user();
        $userModel = new User();
        $recoveryStatus = $userModel->getRecoveryMethods((int) $user['id']);

        $questionModel = new SecurityQuestion();
        $questions = $questionModel->getActiveQuestions();

        echo $this->view('profile/recovery', [
            'user' => $user,
            'recoveryStatus' => $recoveryStatus,
            'questions' => $questions,
            'title' => 'Recuperação de Conta'
        ]);
    }

    public function updateRecoveryKeyword(Request $request): void
    {
        $user = Auth::user();
        $keyword = $request->input('keyword');
        $password = $request->input('current_password');

        if (empty($keyword) || mb_strlen($keyword) < 3) {
            Flash::add('error', 'Palavra-chave deve ter no mínimo 3 caracteres.');
            redirect('/profile/recovery');
        }

        if (!password_verify($password, $user['password_hash'])) {
            Flash::add('error', 'Senha atual incorreta.');
            redirect('/profile/recovery');
        }

        // Não deve ser igual à senha (aproximadamente, strings iguais)
        if ($keyword === $password) {
            Flash::add('error', 'Palavra-chave não pode ser igual à senha.');
            redirect('/profile/recovery');
        }

        $userModel = new User();
        $userModel->setRecoveryKeyword((int) $user['id'], $keyword);

        ActivityLogger::log('users', (int) $user['id'], 'recovery_keyword_set');
        Flash::add('success', 'Palavra-chave atualizada.');
        redirect('/profile/recovery');
    }

    public function updateRecoveryPin(Request $request): void
    {
        $user = Auth::user();
        $pin = $request->input('pin');
        $password = $request->input('current_password');

        if (empty($pin) || !preg_match('/^\d{4,6}$/', $pin)) {
            Flash::add('error', 'PIN deve ter 4 a 6 dígitos numéricos.');
            redirect('/profile/recovery');
        }

        if (!password_verify($password, $user['password_hash'])) {
            Flash::add('error', 'Senha atual incorreta.');
            redirect('/profile/recovery');
        }

        $userModel = new User();
        $userModel->setRecoveryPin((int) $user['id'], $pin);

        ActivityLogger::log('users', (int) $user['id'], 'recovery_pin_set');
        Flash::add('success', 'PIN atualizado.');
        redirect('/profile/recovery');
    }

    public function updateRecoveryQuestions(Request $request): void
    {
        $user = Auth::user();
        $password = $request->input('current_password');

        $q1 = $request->input('question_1');
        $a1 = $request->input('answer_1');
        $q2 = $request->input('question_2');
        $a2 = $request->input('answer_2');

        if (!password_verify($password, $user['password_hash'])) {
            Flash::add('error', 'Senha atual incorreta.');
            redirect('/profile/recovery');
        }

        if ($q1 === $q2) {
            Flash::add('error', 'Selecione perguntas diferentes.');
            redirect('/profile/recovery');
        }

        if (empty($a1) || empty($a2)) {
            Flash::add('error', 'Responda a ambas as perguntas.');
            redirect('/profile/recovery');
        }

        $answers = [
            ['question_id' => $q1, 'answer' => $a1],
            ['question_id' => $q2, 'answer' => $a2]
        ];

        $sqModel = new SecurityQuestion();
        $sqModel->saveUserAnswers((int) $user['id'], $answers);

        ActivityLogger::log('users', (int) $user['id'], 'recovery_questions_set');
        Flash::add('success', 'Perguntas de segurança atualizadas.');
        redirect('/profile/recovery');
    }
}