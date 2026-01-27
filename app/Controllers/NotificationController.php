<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\NotificationService;
use App\Utils\Auth;

class NotificationController extends Controller
{
    private NotificationService $service;

    public function __construct()
    {
        $this->service = new NotificationService();
    }

    /**
     * User's notification listing
     */
    public function index(Request $request): string
    {
        $userId = Auth::id();
        $page = (int) $request->input('page', 1);
        $perPage = 25;
        $filter = $request->input('filter', 'unread');
        $onlyUnread = ($filter !== 'all');

        $notifications = $this->service->getNotificationsForUser($userId, $onlyUnread, $page, $perPage);
        $total = $this->service->getTotalCount($userId, $onlyUnread);

        return $this->view('notifications/index', [
            'notifications' => $notifications,
            'title' => 'Minhas Notificações',
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage)
            ]
        ]);
    }

    /**
     * API: Get unread count (for badge)
     */
    public function getUnreadCount(): void
    {
        $userId = Auth::id();
        $count = $this->service->getUnreadCount($userId);

        Response::json(['count' => $count]);
    }

    /**
     * API: Mark as read
     */
    public function markAsRead(Request $request): void
    {
        $id = (int) $request->param('id');
        $userId = Auth::id();

        $result = $this->service->markAsRead($id, $userId);
        Response::json($result);
    }

    /**
     * WIZARD STEP 1: Type and Context
     */
    public function create(): string
    {
        // Clear any previous wizard session
        unset($_SESSION['notification_wizard']);

        return $this->view('notifications/create_step1', [
            'title' => 'Nova Notificação - Tipo e Contexto'
        ]);
    }

    /**
     * WIZARD STEP 2: Recipients
     */
    public function wizardStep2(Request $request): void
    {
        // Save step 1 data
        $_SESSION['notification_wizard']['type'] = $request->input('type');
        $_SESSION['notification_wizard']['context_type'] = $request->input('context_type');
        $_SESSION['notification_wizard']['context_id'] = $request->input('context_id');

        redirect('/notifications/create/step2');
    }

    /**
     * Show step 2 form
     */
    public function createStep2(): string
    {
        if (!isset($_SESSION['notification_wizard']['type'])) {
            redirect('/notifications/create');
            return '';
        }

        return $this->view('notifications/create_step2', [
            'title' => 'Nova Notificação - Destinatários',
            'wizard' => $_SESSION['notification_wizard']
        ]);
    }

    /**
     * WIZARD STEP 3: Content
     */
    public function wizardStep3(Request $request): void
    {
        error_log("=== WIZARD STEP 3 START ===");

        // Validate previous step data
        if (!isset($_SESSION['notification_wizard']['type'])) {
            error_log("Wizard Step 3: Missing wizard session data");
            redirect('/notifications/create?error=invalid_session');
            return;
        }

        error_log("Wizard Step 3: Session data valid");

        // Save step 2 data
        $_SESSION['notification_wizard']['recipient_group'] = $request->input('recipient_group');
        $_SESSION['notification_wizard']['recipient_ids'] = $request->input('recipient_ids', []);

        // Calculate recipients
        $group = $request->input('recipient_group');
        $contextId = $_SESSION['notification_wizard']['context_id'] ?? null;

        // FIX: Convert empty string to null, otherwise cast to int
        $resourceId = ($contextId === '' || $contextId === null) ? null : (int) $contextId;

        error_log("Wizard Step 3: Group={$group}, ResourceId=" . ($resourceId ?? 'null'));

        try {
            if ($group === 'specific') {
                $recipientIds = $request->input('recipient_ids', []);
                error_log("Wizard Step 3: Specific recipients count=" . count($recipientIds));
            } else {
                error_log("Wizard Step 3: Calling getRecipientsByGroup...");
                $recipientIds = $this->service->getRecipientsByGroup($group, $resourceId);
                error_log("Wizard Step 3: Recipients found=" . count($recipientIds));
            }

            $_SESSION['notification_wizard']['recipients'] = $recipientIds;
            $_SESSION['notification_wizard']['recipient_count'] = count($recipientIds);

            error_log("Wizard Step 3: Success, redirecting...");
            redirect('/notifications/create/step3');
        } catch (\Exception $e) {
            error_log('Wizard Step 3 ERROR: ' . $e->getMessage());
            error_log('File: ' . $e->getFile() . ':' . $e->getLine());
            error_log('Trace: ' . $e->getTraceAsString());
            redirect('/notifications/create?error=recipient_calculation_failed');
        }
    }

    /**
     * Show step 3 form
     */
    public function createStep3(): string
    {
        if (!isset($_SESSION['notification_wizard']['recipients'])) {
            redirect('/notifications/create');
            return '';
        }

        return $this->view('notifications/create_step3', [
            'title' => 'Nova Notificação - Conteúdo',
            'wizard' => $_SESSION['notification_wizard']
        ]);
    }

    /**
     * WIZARD STEP 4: Channels
     */
    public function wizardStep4(Request $request): void
    {
        // Save step 3 data
        $_SESSION['notification_wizard']['subject'] = $request->input('subject');
        $_SESSION['notification_wizard']['message'] = $request->input('message');

        redirect('/notifications/create/step4');
    }

    /**
     * Show step 4 form
     */
    public function createStep4(): string
    {
        if (!isset($_SESSION['notification_wizard']['subject'])) {
            redirect('/notifications/create');
            return '';
        }

        return $this->view('notifications/create_step4', [
            'title' => 'Nova Notificação - Canais',
            'wizard' => $_SESSION['notification_wizard']
        ]);
    }

    /**
     * WIZARD STEP 5: Confirmation and Send
     */
    public function wizardStep5(Request $request): void
    {
        // Save step 4 data
        $channels = [];
        $channels[] = 'internal'; // Always internal

        if ($request->input('email') === 'on') {
            $channels[] = 'email';
        }

        if ($request->input('sms') === 'on') {
            $channels[] = 'sms';
        }

        $_SESSION['notification_wizard']['channels'] = $channels;

        redirect('/notifications/create/step5');
    }

    /**
     * Show step 5 confirmation
     */
    public function createStep5(): string
    {
        if (!isset($_SESSION['notification_wizard']['channels'])) {
            redirect('/notifications/create');
            return '';
        }

        return $this->view('notifications/create_step5', [
            'title' => 'Nova Notificação - Confirmação',
            'wizard' => $_SESSION['notification_wizard']
        ]);
    }

    /**
     * Final send action
     */
    public function send(Request $request): void
    {
        if (!isset($_SESSION['notification_wizard'])) {
            redirect('/notifications/create?error=session_expired');
            return;
        }

        $wizard = $_SESSION['notification_wizard'];
        $result = $this->service->createManualNotification($wizard, Auth::id());

        // Clear wizard session
        unset($_SESSION['notification_wizard']);

        if ($result['success']) {
            redirect('/notifications/history?success=notification_sent');
        } else {
            redirect('/notifications/create?error=' . urlencode($result['message']));
        }
    }

    /**
     * Notification history (coordenador only)
     */
    public function history(Request $request): string
    {
        $page = (int) $request->input('page', 1);
        $perPage = 50;

        $filters = [
            'type' => $request->input('type'),
            'context_type' => $request->input('context_type'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to')
        ];

        // Remove empty filters
        $filters = array_filter($filters);

        $notifications = $this->service->getNotificationHistory($filters, $page, $perPage);
        $total = $this->service->getHistoryCount($filters);

        return $this->view('notifications/history', [
            'notifications' => $notifications,
            'filters' => $filters,
            'title' => 'Histórico de Notificações',
            'pagination' => [
                'current_page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'last_page' => ceil($total / $perPage)
            ]
        ]);
    }
}
