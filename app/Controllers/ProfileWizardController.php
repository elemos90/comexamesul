<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Utils\Auth;
use App\Utils\Flash;
use App\Utils\Validator;

class ProfileWizardController extends Controller
{
    /**
     * Campos obrigatórios para perfil completo
     */
    private array $requiredFields = [
        'name' => 'Nome completo',
        'phone' => 'Telefone',
        'gender' => 'Género',
        'birth_date' => 'Data de nascimento',
        'document_type' => 'Tipo de documento',
        'document_number' => 'Número do documento',
        'nuit' => 'NUIT',
        'origin_university' => 'Universidade de origem',
        'university' => 'Universidade atual',
        'degree' => 'Grau académico',
        'major_area' => 'Área de formação',
        'bank_name' => 'Banco',
        'nib' => 'NIB',
        'bank_account_holder' => 'Titular da conta',
    ];

    /**
     * Campos opcionais (mas recomendados)
     */
    private array $optionalFields = [];

    /**
     * Mostrar wizard de completar perfil
     */
    public function show(): string
    {
        $user = Auth::user();
        if (!$user) {
            redirect('/login');
        }

        // Se o perfil já está completo, redirecionar para dashboard
        if (!empty($user['profile_complete'])) {
            redirect('/dashboard');
        }

        // Calcular progresso
        $progress = $this->calculateProgress($user);
        $missingFields = $this->getMissingFields($user);

        return $this->view('profile/wizard', [
            'user' => $user,
            'progress' => $progress,
            'missingFields' => $missingFields,
            'requiredFields' => $this->requiredFields,
            'optionalFields' => $this->optionalFields,
        ]);
    }

    /**
     * Salvar dados do perfil
     */
    public function save(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            redirect('/login');
        }

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
            'bank_account_holder',
            'terms_accepted',
            'data_authorization'
        ]);

        // Limpar e normalizar dados
        $data = array_map(function ($value) {
            return is_string($value) ? trim($value) : $value;
        }, $data);

        // Validação
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
            'degree' => 'required|in:Licenciado,Mestre,Doutor,Outro',
            'major_area' => 'required|max:150',
            'bank_name' => 'required|max:150',
            'nib' => 'required|nib',
            'bank_account_holder' => 'required|min:3|max:150',
        ];

        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Corrija os campos assinalados.');
            $_SESSION['errors'] = $validator->errors();
            $_SESSION['old'] = $data;
            redirect('/profile/wizard');
        }

        // Verificar declarações
        if (empty($data['terms_accepted'])) {
            Flash::add('error', 'Deve confirmar que os dados são verdadeiros.');
            $_SESSION['old'] = $data;
            redirect('/profile/wizard');
        }
        if (empty($data['data_authorization'])) {
            Flash::add('error', 'Deve autorizar o uso dos dados para pagamentos.');
            $_SESSION['old'] = $data;
            redirect('/profile/wizard');
        }

        // Remover campos de declaração antes de salvar
        unset($data['terms_accepted'], $data['data_authorization']);

        // Atualizar utilizador
        $userModel = new User();
        $userModel->updateUser((int) $user['id'], $data);

        // Verificar se perfil agora está completo
        $updatedUser = $userModel->find((int) $user['id']);
        $allFieldsFilled = $this->areRequiredFieldsFilled($updatedUser);

        if ($allFieldsFilled) {
            $userModel->setProfileComplete((int) $user['id'], true);
            $userModel->markProfileComplete((int) $user['id']); // Também atualizar profile_completed legado

            ActivityLogger::log('users', (int) $user['id'], 'profile_completed', $data);

            // Atualizar sessão
            $updatedUser = $userModel->find((int) $user['id']);
            Auth::loginUser($updatedUser);

            Flash::add('success', 'Perfil completado com sucesso! Agora pode aceder a todas as funcionalidades.');
            redirect('/dashboard');
        } else {
            $missing = $this->getMissingFields($updatedUser);
            Flash::add('warning', 'Perfil guardado, mas ainda faltam campos obrigatórios: ' . implode(', ', array_values($missing)));
            redirect('/profile/wizard');
        }
    }

    /**
     * Calcular progresso do perfil (0-100)
     */
    private function calculateProgress(array $user): int
    {
        $filled = 0;
        $total = count($this->requiredFields);

        foreach (array_keys($this->requiredFields) as $field) {
            if (!empty($user[$field]) && trim($user[$field]) !== '') {
                $filled++;
            }
        }

        return (int) round(($filled / $total) * 100);
    }

    /**
     * Obter campos obrigatórios em falta
     */
    private function getMissingFields(array $user): array
    {
        $missing = [];
        foreach ($this->requiredFields as $field => $label) {
            if (empty($user[$field]) || trim($user[$field]) === '') {
                $missing[$field] = $label;
            }
        }
        return $missing;
    }

    /**
     * Verificar se todos os campos obrigatórios estão preenchidos
     */
    private function areRequiredFieldsFilled(array $user): bool
    {
        foreach (array_keys($this->requiredFields) as $field) {
            if (empty($user[$field]) || trim($user[$field]) === '') {
                return false;
            }
        }
        return true;
    }
}
