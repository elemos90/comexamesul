<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\User;
use App\Services\UserRoleService;
use App\Services\UserPromotionService;
use App\Services\ActivityLogger;
use App\Utils\Auth;
use App\Utils\Flash;

class UserController extends Controller
{
    private User $userModel;
    private UserRoleService $roleService;
    private UserPromotionService $promotionService;

    public function __construct()
    {
        $this->userModel = new User();
        $this->roleService = new UserRoleService();
        $this->promotionService = new UserPromotionService();
    }

    /**
     * List all users (Coordenador only)
     */
    public function index(): void
    {
        // Get all users with their roles
        $users = $this->userModel->statement("
            SELECT u.*, 
                   GROUP_CONCAT(ur.role ORDER BY FIELD(ur.role, 'coordenador', 'membro', 'supervisor', 'vigilante') SEPARATOR ',') as roles
            FROM users u
            LEFT JOIN user_roles ur ON ur.user_id = u.id
            GROUP BY u.id
            ORDER BY u.name ASC
        ");

        // Parse roles for each user
        foreach ($users as &$user) {
            $user['roles_array'] = !empty($user['roles']) ? explode(',', $user['roles']) : [];
        }

        echo $this->view('users/index', [
            'users' => $users,
            'title' => 'Gestão de Utilizadores'
        ]);
    }

    /**
     * Show create form
     */
    public function create(): void
    {
        echo $this->view('users/create', [
            'title' => 'Criar Utilizador',
            'roles' => UserRoleService::getValidRoles()
        ]);
    }

    /**
     * Store new user
     */
    public function store(Request $request): void
    {
        $name = $request->input('name');
        $username = $request->input('username');
        $email = $request->input('email');
        $phone = $request->input('phone');
        $role = $request->input('role');
        $isActive = $request->input('is_active', 'on') === 'on';

        // Validation
        if (empty($name) || empty($username) || empty($role)) {
            redirect('/admin/users/create?error=missing_fields');
            return;
        }

        // Check username
        if (!$this->userModel->isUsernameAvailable($username)) {
            redirect('/admin/users/create?error=username_exists');
            return;
        }

        // Check email only if provided
        if (!empty($email)) {
            $existing = $this->userModel->findByEmail($email);
            if (!empty($existing)) {
                redirect('/admin/users/create?error=email_exists');
                return;
            }
        }

        // Generate temporary password (8 chars, alphanumeric)
        // Usamos uma senha simples para facilitar a digitação manual, pois será forçada a troca
        $tempPassword = bin2hex(random_bytes(4));
        $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

        // Create user
        $userId = $this->userModel->create([
            'name' => $name,
            'username' => $username,
            'email' => empty($email) ? null : $email,
            'phone' => $phone,
            'password_hash' => $hashedPassword,
            'role' => $role,
            'is_active' => $isActive ? 1 : 0,
            'must_change_password' => 1,
            'profile_complete' => 0,
            'temp_password_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // Add role using RoleService (handles hierarchy)
        $this->roleService->addRole($userId, $role, Auth::id());

        // Log activity
        ActivityLogger::log('users', $userId, 'user_created', [
            'name' => $name,
            'username' => $username,
            'role' => $role,
            'created_by' => Auth::id()
        ]);

        // Send welcome email if email provided
        $emailNote = '';
        if (!empty($email)) {
            // Nota: Se o envio de email falhar, não paramos o processo
            try {
                $this->sendWelcomeEmail($email, $name, $tempPassword, $username);
                $emailNote = '<br><span class="text-sm">(Email enviado)</span>';
            } catch (\Exception $e) {
                $emailNote = '<br><span class="text-sm text-red-600">(Falha ao enviar email)</span>';
            }
        }

        // Mostramos as credenciais via Flash para o admin poder copiar e entregar ao utilizador
        // Isto é CRÍTICO pois o sistema agora funciona independente de emails
        $message = "Utilizador criado com sucesso!<br>
                   <strong>Username:</strong> {$username}<br>
                   <strong>Senha Temporária:</strong> {$tempPassword}
                   {$emailNote}";

        Flash::add('success', $message);

        redirect('/admin/users');
    }

    /**
     * Show edit form
     */
    public function edit(Request $request): void
    {
        $id = (int) $request->param('id');
        $user = $this->userModel->find($id);

        if (!$user) {
            redirect('/admin/users?error=user_not_found');
            return;
        }

        // Get current roles
        $user['roles_array'] = $this->roleService->getUserRoles($id);

        // Get audit history
        $auditLog = $this->promotionService->getPromotionHistory($id);

        echo $this->view('users/edit', [
            'user' => $user,
            'roles' => UserRoleService::getValidRoles(),
            'auditLog' => $auditLog,
            'title' => 'Editar Utilizador'
        ]);
    }

    /**
     * Update user
     */
    public function update(Request $request): void
    {
        $id = (int) $request->param('id');
        $user = $this->userModel->find($id);

        if (!$user) {
            redirect('/admin/users?error=user_not_found');
            return;
        }

        $name = $request->input('name');
        $email = $request->input('email');
        $phone = $request->input('phone');

        // Update basic info
        $this->userModel->update($id, [
            'name' => $name,
            'email' => $email,
            'phone' => $phone,
            'updated_at' => now()
        ]);

        ActivityLogger::log('users', $id, 'user_updated', [
            'updated_by' => Auth::id()
        ]);

        redirect('/admin/users/' . $id . '/edit?success=user_updated');
    }

    /**
     * Toggle user active status
     */
    public function toggleStatus(Request $request): void
    {
        try {
            $id = (int) $request->param('id');
            $user = $this->userModel->find($id);

            if (!$user) {
                Response::json(['success' => false, 'message' => 'Utilizador não encontrado'], 404);
                return;
            }

            // Cannot deactivate yourself
            if ($id === Auth::id()) {
                Response::json(['success' => false, 'message' => 'Não pode desativar a própria conta'], 403);
                return;
            }

            $newStatus = !$user['is_active'];
            $reason = $request->input('reason');

            $this->userModel->update($id, [
                'is_active' => $newStatus,
                'deactivated_at' => $newStatus ? null : now(),
                'deactivation_reason' => $newStatus ? null : $reason,
                'updated_at' => now()
            ]);

            $action = $newStatus ? 'reactivated' : 'deactivated';
            $details = $newStatus ? 'Conta reativada' : 'Conta desativada' . ($reason ? ': ' . $reason : '');

            $db = database();
            $stmt = $db->prepare("INSERT INTO user_audit_log (user_id, action, details, performed_by) VALUES (?, ?, ?, ?)");
            $stmt->execute([$id, $action, $details, Auth::id()]);

            Response::json([
                'success' => true,
                'message' => $newStatus ? 'Utilizador reativado' : 'Utilizador desativado',
                'is_active' => $newStatus
            ]);

        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Promote to Supervisor
     */
    public function promoteToSupervisor(Request $request): void
    {
        try {
            $id = (int) $request->param('id');
            $result = $this->promotionService->promoteToSupervisor($id, Auth::id());

            Response::json($result);
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Promote to Committee Member
     */
    public function promoteToCommitteeMember(Request $request): void
    {
        try {
            $id = (int) $request->param('id');
            $result = $this->promotionService->promoteToMember($id, Auth::id());

            Response::json($result);
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Update user roles
     */
    public function updateRoles(Request $request): void
    {
        try {
            $id = (int) $request->param('id');
            $selectedRoles = $request->input('roles', []);

            if (!is_array($selectedRoles)) {
                $selectedRoles = [$selectedRoles];
            }

            $currentRoles = $this->roleService->getUserRoles($id);

            // Add new roles
            foreach ($selectedRoles as $role) {
                if (!in_array($role, $currentRoles)) {
                    $this->roleService->addRole($id, $role, Auth::id());
                }
            }

            // Remove unchecked roles
            foreach ($currentRoles as $role) {
                if (!in_array($role, $selectedRoles)) {
                    $this->roleService->removeRole($id, $role, Auth::id());
                }
            }

            Response::json(['success' => true, 'message' => 'Papéis atualizados com sucesso']);

        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Get audit log for user
     */
    public function getAuditLog(Request $request): void
    {
        try {
            $id = (int) $request->param('id');
            $history = $this->promotionService->getPromotionHistory($id);

            Response::json(['success' => true, 'history' => $history]);
        } catch (\Exception $e) {
            Response::json(['success' => false, 'message' => 'Erro: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Send welcome email with credentials
     */
    private function sendWelcomeEmail(string $email, string $name, string $tempPassword, string $username = ''): void
    {
        // TODO: Passar username para o EmailService
        $emailService = new \App\Services\EmailService();
        $sent = $emailService->sendWelcomeEmail($email, $name, $tempPassword);

        if (!$sent) {
            error_log("Failed to send welcome email to: {$email}");
        }
    }

    /**
     * Show Password Reset Resolution Wizard
     */
    public function showResolvePasswordReset(Request $request): void
    {
        $id = (int) $request->param('id');
        $resetRequest = $this->userModel->findPasswordResetRequest($id);

        if (!$resetRequest) {
            redirect('/dashboard?error=request_not_found');
            return;
        }

        if ($resetRequest['status'] !== 'pending') {
            redirect('/notifications?info=request_already_resolved');
            return;
        }

        echo $this->view('admin/password_reset', [
            'resetRequest' => $resetRequest,
            'title' => 'Resolver Pedido de Reset de Senha'
        ]);
    }

    /**
     * Process Password Reset Resolution
     */
    public function resolvePasswordReset(Request $request): void
    {
        $id = (int) $request->param('id');
        $resetRequest = $this->userModel->findPasswordResetRequest($id);

        if (!$resetRequest) {
            redirect('/dashboard?error=request_not_found');
            return;
        }

        $passwordType = $request->input('password_type'); // 'auto' or 'manual'
        $manualPassword = $request->input('manual_password');

        $newPassword = '';

        if ($passwordType === 'manual') {
            if (empty($manualPassword) || strlen($manualPassword) < 6) {
                redirect("/admin/password-reset/{$id}?error=invalid_password");
                return;
            }
            $newPassword = $manualPassword;
        } else {
            // Auto generate (8 chars alphanumeric)
            $newPassword = bin2hex(random_bytes(4));
        }

        try {
            // Resolve logic (updates DB, sets must_change_password)
            $this->userModel->resolvePasswordReset($id, Auth::id(), $newPassword);

            // Notify User
            $notificationService = new \App\Services\NotificationService();
            $notificationService->createAutomaticNotification(
                'urgente',
                'Nova Senha Temporária',
                "O seu pedido de reset foi processado. Sua nova senha temporária é: {$newPassword}. Por favor faça login e altere-a imediatamente.",
                'user_account',
                $resetRequest['user_id'],
                [$resetRequest['user_id']]
            );

            // También enviamos email específico si es posible?
            // A notificação automática já envia email se o canal estiver configurado.

            // Flash success message with the password so Admin can see it too
            $message = "Senha resetada com sucesso!<br>
                        <strong>Nova Senha:</strong> {$newPassword}<br>
                        O utilizador foi notificado.";

            Flash::add('success', $message);

            redirect('/notifications'); // Redirect back to notifications

        } catch (\Exception $e) {
            error_log("Failed to resolve password reset: " . $e->getMessage());
            redirect("/admin/password-reset/{$id}?error=server_error");
        }
    }
}
