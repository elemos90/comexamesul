<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Utils\Auth;
use App\Utils\Flash;
use App\Utils\Validator;

class ProfileController extends Controller
{
    public function show(): string
    {
        $user = Auth::user();
        return $this->view('profile/index', ['user' => $user]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        
        // Email não pode ser alterado - removido da lista de campos aceitos
        $data = $request->only([
            'name','phone','gender','origin_university','university','nuit','degree','major_area','bank_name','nib'
        ]);
        $data = array_map(static function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $data);

        $validator = new Validator();
        $rules = [
            'name' => 'required|min:3|max:150',
            'phone' => 'required|phone_mz',
            'gender' => 'required|in:M,F,O',
            'origin_university' => 'required|min:3|max:200',
            'university' => 'required|max:150',
            'nuit' => 'required|nuit',
            'degree' => 'required|in:Licenciado,Mestre,Doutor',
            'major_area' => 'required|max:150',
            'bank_name' => 'required|max:150',
            'nib' => 'required|nib',
        ];
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Corrija os campos assinalados.');
            $_SESSION['errors'] = $validator->errors();
            redirect('/profile');
        }

        $model = new User();
        $model->updateUser((int) $user['id'], $data);
        
        // Verificar e atualizar status de perfil completo
        $model->checkAndUpdateProfileStatus((int) $user['id']);
        
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
}