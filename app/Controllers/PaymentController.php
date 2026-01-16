<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\Payment;
use App\Models\PaymentRate;
use App\Models\ExamVacancy;
use App\Services\PaymentService;
use App\Utils\Auth;

class PaymentController extends Controller
{
    private PaymentService $paymentService;
    private PaymentRate $rateModel;
    private Payment $paymentModel;
    private ExamVacancy $vacancyModel;

    public function __construct()
    {
        $this->paymentService = new PaymentService();
        $this->rateModel = new PaymentRate();
        $this->paymentModel = new Payment();
        $this->vacancyModel = new ExamVacancy();
    }

    /**
     * Dashboard de pagamentos
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        $vacancies = $this->vacancyModel->all();
        $selectedVacancy = $request->input('vacancy_id');

        $payments = [];
        $stats = null;
        $rates = null;

        if ($selectedVacancy) {
            $payments = $this->paymentModel->getByVacancy((int) $selectedVacancy);
            $stats = $this->paymentService->getPaymentStats((int) $selectedVacancy);
            $rates = $this->rateModel->getActiveForVacancy((int) $selectedVacancy);
        }

        return $this->view('payments/index', [
            'user' => $user,
            'vacancies' => $vacancies,
            'selectedVacancy' => $selectedVacancy,
            'payments' => $payments,
            'stats' => $stats,
            'rates' => $rates,
            'title' => 'Mapa de Pagamentos',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'Pagamentos', 'url' => ''],
            ],
        ]);
    }

    /**
     * Gestão de taxas
     */
    public function rates(Request $request)
    {
        $user = Auth::user();
        $vacancies = $this->vacancyModel->all();
        $rates = $this->rateModel->getAllWithVacancy();

        return $this->view('payments/rates', [
            'user' => $user,
            'vacancies' => $vacancies,
            'rates' => $rates,
            'title' => 'Taxas de Pagamento',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'Pagamentos', 'url' => '/payments'],
                ['label' => 'Taxas', 'url' => ''],
            ],
        ]);
    }

    /**
     * Criar nova taxa
     */
    public function storeRate(Request $request)
    {
        $vacancyId = $request->input('vacancy_id');
        $valorVigia = $request->input('valor_por_vigia', 0);
        $valorSupervisao = $request->input('valor_por_supervisao', 0);
        $moeda = $request->input('moeda', 'MZN');

        if (!$vacancyId) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Vaga obrigatória.'];
            redirect('/payments/rates');
            return;
        }

        $data = [
            'vacancy_id' => (int) $vacancyId,
            'valor_por_vigia' => (float) $valorVigia,
            'valor_por_supervisao' => (float) $valorSupervisao,
            'moeda' => $moeda,
            'ativo' => 1,
        ];

        $this->rateModel->createRate($data);

        $_SESSION['flash'] = [
            'type' => 'success',
            'message' => 'Taxa de pagamento criada com sucesso.',
        ];

        redirect('/payments/rates');
    }

    /**
     * Pré-visualização do mapa
     */
    public function preview(Request $request)
    {
        $vacancyId = $request->param('vacancyId');
        $user = Auth::user();
        $vacancy = $this->vacancyModel->find($vacancyId);

        if (!$vacancy) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Vaga não encontrada.'];
            redirect('/payments');
            return null;
        }

        $result = $this->paymentService->previewPayments($vacancyId);

        return $this->view('payments/preview', [
            'user' => $user,
            'vacancy' => $vacancy,
            'preview' => $result,
            'title' => 'Pré-visualização de Pagamentos',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'Pagamentos', 'url' => '/payments'],
                ['label' => 'Pré-visualização', 'url' => ''],
            ],
        ]);
    }

    /**
     * Gerar mapa de pagamentos
     */
    public function generate(Request $request)
    {
        $vacancyId = $request->param('vacancyId') ?: $request->input('vacancy_id');

        if (!$vacancyId) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'ID da vaga não fornecido.'];
            redirect('/payments');
            return;
        }

        error_log("DEBUG: PaymentController::generate called for vacancy $vacancyId");

        $result = $this->paymentService->generatePaymentMap($vacancyId);

        $_SESSION['flash'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'] ?? $result['error'],
        ];

        redirect("/payments?vacancy_id={$vacancyId}");
    }

    /**
     * Validar (congelar) mapa
     */
    public function validate(Request $request)
    {
        $vacancyId = $request->param('vacancyId');
        $user = Auth::user();
        $result = $this->paymentService->validatePayments($vacancyId, (int) $user['id']);

        $_SESSION['flash'] = [
            'type' => $result['success'] ? 'success' : 'error',
            'message' => $result['message'] ?? $result['error'],
        ];

        redirect("/payments?vacancy_id={$vacancyId}");
    }

    /**
     * Exportar mapa
     */
    public function export(Request $request)
    {
        $vacancyId = $request->param('vacancyId');
        $format = $request->input('format', 'pdf');
        $vacancy = $this->vacancyModel->find($vacancyId);

        if (!$vacancy) {
            $_SESSION['flash'] = ['type' => 'error', 'message' => 'Vaga não encontrada.'];
            redirect('/payments');
            return null;
        }

        $data = $this->paymentService->exportPayments($vacancyId);
        $data['vacancy'] = $vacancy;

        switch ($format) {
            case 'csv':
                $this->exportCsv($data);
                break;
            case 'excel':
                $this->exportExcel($data);
                break;
            case 'pdf':
            default:
                return $this->view('payments/export_pdf', $data);
        }
    }

    /**
     * Exportar CSV
     */
    private function exportCsv(array $data): void
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="mapa_pagamentos_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM para Excel reconhecer UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeçalho
        fputcsv($output, ['Ord.', 'Nome Completo', 'Nº Vigias', 'Nº Supervisões', 'Valor (MZN)', 'NUIT', 'Banco', 'Nº Conta'], ';');

        $ord = 1;
        foreach ($data['payments'] as $p) {
            fputcsv($output, [
                $ord++,
                $p['nome_completo'],
                $p['nr_vigias'],
                $p['nr_supervisoes'],
                number_format($p['total'], 2, ',', '.'),
                $p['nuit'] ?? '',
                $p['banco'] ?? '',
                $p['numero_conta'] ?? '',
            ], ';');
        }

        fclose($output);
        exit;
    }

    /**
     * Exportar Excel (CSV compatível)
     */
    private function exportExcel(array $data): void
    {
        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="mapa_pagamentos_' . date('Y-m-d') . '.xls"');

        echo "<!DOCTYPE html><html><head><meta charset='utf-8'></head><body>";
        echo "<table border='1'>";
        echo "<tr><th>Ord.</th><th>Nome Completo</th><th>Nº Vigias</th><th>Nº Supervisões</th><th>Valor (MZN)</th><th>NUIT</th><th>Banco</th><th>Nº Conta</th></tr>";

        $ord = 1;
        foreach ($data['payments'] as $p) {
            echo "<tr>";
            echo "<td>{$ord}</td>";
            echo "<td>{$p['nome_completo']}</td>";
            echo "<td>{$p['nr_vigias']}</td>";
            echo "<td>{$p['nr_supervisoes']}</td>";
            echo "<td>" . number_format($p['total'], 2, ',', '.') . "</td>";
            echo "<td>" . ($p['nuit'] ?? '') . "</td>";
            echo "<td>" . ($p['banco'] ?? '') . "</td>";
            echo "<td>" . ($p['numero_conta'] ?? '') . "</td>";
            echo "</tr>";
            $ord++;
        }

        echo "</table></body></html>";
        exit;
    }

    /**
     * Meu Mapa de Pagamento (Individual - Vigilante/Supervisor)
     * Read-only view of own payments
     */
    public function myMap(Request $request)
    {
        $user = Auth::user();
        $userId = (int) $user['id'];

        // Buscar vagas com pagamentos do utilizador
        $vacancies = $this->paymentModel->statement(
            "SELECT DISTINCT ev.id, ev.title, YEAR(ev.deadline_at) as year
             FROM payments p
             INNER JOIN exam_vacancies ev ON ev.id = p.vacancy_id
             WHERE p.user_id = :user_id
             ORDER BY ev.deadline_at DESC",
            ['user_id' => $userId]
        );

        $selectedVacancy = $request->input('vacancy_id');
        $payment = null;
        $rates = null;
        $participations = [];
        $vacancy = null;

        // Se há vaga selecionada, buscar dados do utilizador
        if ($selectedVacancy) {
            $selectedVacancy = (int) $selectedVacancy;

            // Buscar pagamento do utilizador (APENAS o próprio)
            $payment = $this->paymentModel->statement(
                "SELECT * FROM payments WHERE user_id = :user_id AND vacancy_id = :vacancy_id LIMIT 1",
                ['user_id' => $userId, 'vacancy_id' => $selectedVacancy]
            )[0] ?? null;

            // Buscar taxas ativas
            $rates = $this->rateModel->getActiveForVacancy($selectedVacancy);

            // Buscar dados da vaga
            $vacancy = $this->vacancyModel->find($selectedVacancy);

            // Buscar participações detalhadas (vigias + supervisões)
            $participations = $this->getMyParticipations($userId, $selectedVacancy);
        } elseif (!empty($vacancies)) {
            // Auto-selecionar primeira vaga se existir
            $selectedVacancy = (int) $vacancies[0]['id'];

            $payment = $this->paymentModel->statement(
                "SELECT * FROM payments WHERE user_id = :user_id AND vacancy_id = :vacancy_id LIMIT 1",
                ['user_id' => $userId, 'vacancy_id' => $selectedVacancy]
            )[0] ?? null;

            $rates = $this->rateModel->getActiveForVacancy($selectedVacancy);
            $vacancy = $this->vacancyModel->find($selectedVacancy);
            $participations = $this->getMyParticipations($userId, $selectedVacancy);
        }

        return $this->view('payments/my_map', [
            'user' => $user,
            'vacancies' => $vacancies,
            'selectedVacancy' => $selectedVacancy,
            'vacancy' => $vacancy,
            'payment' => $payment,
            'rates' => $rates,
            'participations' => $participations,
            'title' => 'Meu Mapa de Pagamento',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'Meu Mapa de Pagamento', 'url' => ''],
            ],
        ]);
    }

    /**
     * Buscar participações detalhadas do utilizador (vigias e supervisões)
     */
    private function getMyParticipations(int $userId, int $vacancyId): array
    {
        // Buscar vigias
        $vigilantias = $this->paymentModel->statement(
            "SELECT j.subject, j.exam_date, j.start_time, j.end_time, j.room, 
                    COALESCE(el.name, j.location) as location, 'Vigilante' as papel
             FROM jury_vigilantes jv
             INNER JOIN juries j ON j.id = jv.jury_id
             LEFT JOIN exam_locations el ON el.id = j.location_id
             WHERE jv.vigilante_id = :user_id AND j.vacancy_id = :vacancy_id
             ORDER BY j.exam_date, j.start_time",
            ['user_id' => $userId, 'vacancy_id' => $vacancyId]
        );

        // Buscar supervisões
        $supervisoes = $this->paymentModel->statement(
            "SELECT j.subject, j.exam_date, j.start_time, j.end_time, j.room, 
                    COALESCE(el.name, j.location) as location, 'Supervisor' as papel
             FROM juries j
             LEFT JOIN exam_locations el ON el.id = j.location_id
             WHERE j.supervisor_id = :user_id AND j.vacancy_id = :vacancy_id
             ORDER BY j.exam_date, j.start_time",
            ['user_id' => $userId, 'vacancy_id' => $vacancyId]
        );

        // Combinar e ordenar
        $all = array_merge($vigilantias, $supervisoes);
        usort($all, function ($a, $b) {
            $cmp = strcmp($a['exam_date'], $b['exam_date']);
            if ($cmp !== 0)
                return $cmp;
            return strcmp($a['start_time'], $b['start_time']);
        });

        return $all;
    }
}
