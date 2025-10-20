<?php

namespace App\Controllers;

use App\Http\Request;
use App\Services\ApplicationStatsService;
use App\Models\EmailNotification;
use App\Utils\Auth;
use App\Utils\Flash;

class ApplicationDashboardController extends Controller
{
    /**
     * Dashboard de Candidaturas
     */
    public function index(): string
    {
        $user = Auth::user();
        if (!$user || !in_array($user['role'], ['coordenador', 'membro'])) {
            Flash::add('error', 'Acesso restrito a coordenadores e membros.');
            redirect('/dashboard');
        }

        $statsService = new ApplicationStatsService();

        // Estatísticas gerais
        $generalStats = $statsService->getGeneralStats();
        
        // Distribuição de status
        $statusDistribution = $statsService->getStatusDistribution();
        
        // Candidaturas por dia (últimos 30 dias)
        $applicationsByDay = $statsService->getApplicationsByDay(30);
        
        // Top vigilantes mais ativos
        $topVigilantes = $statsService->getTopVigilantes(10);
        
        // Tempo médio de revisão por coordenador
        $avgReviewTime = $statsService->getAvgReviewTimeByCoordinator();
        
        // Candidaturas urgentes (pendentes há mais de 48h)
        $urgentApplications = $statsService->getUrgentPendingApplications();
        
        // Motivos de rejeição mais comuns
        $topRejectionReasons = $statsService->getTopRejectionReasons(5);

        // Estatísticas de emails
        $emailModel = new EmailNotification();
        $emailStats = $emailModel->getStats();

        return $this->view('applications/dashboard', [
            'user' => $user,
            'generalStats' => $generalStats,
            'statusDistribution' => $statusDistribution,
            'applicationsByDay' => $applicationsByDay,
            'topVigilantes' => $topVigilantes,
            'avgReviewTime' => $avgReviewTime,
            'urgentApplications' => $urgentApplications,
            'topRejectionReasons' => $topRejectionReasons,
            'emailStats' => $emailStats,
        ]);
    }

    /**
     * Exportar relatório
     */
    public function export(Request $request)
    {
        $user = Auth::user();
        if (!$user || !in_array($user['role'], ['coordenador', 'membro'])) {
            Flash::add('error', 'Acesso restrito.');
            redirect('/dashboard');
        }

        $statsService = new ApplicationStatsService();
        
        // Filtros (opcional)
        $filters = [
            'status' => $request->input('status'),
            'vacancy_id' => $request->input('vacancy_id'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $data = $statsService->exportData($filters);

        // Gerar CSV
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=relatorio_candidaturas_' . date('Y-m-d') . '.csv');

        $output = fopen('php://output', 'w');
        
        // BOM para UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        // Cabeçalho
        fputcsv($output, [
            'ID',
            'Status',
            'Vaga',
            'Vigilante',
            'Email',
            'Data Candidatura',
            'Data Revisão',
            'Revisado Por',
            'Tempo Revisão (h)',
            'Recandidaturas',
        ], ';');

        // Dados
        foreach ($data as $row) {
            fputcsv($output, [
                $row['id'],
                $row['status'],
                $row['vacancy_title'],
                $row['vigilante_name'],
                $row['vigilante_email'],
                $row['applied_at'] ? date('d/m/Y H:i', strtotime($row['applied_at'])) : '',
                $row['reviewed_at'] ? date('d/m/Y H:i', strtotime($row['reviewed_at'])) : '',
                $row['reviewed_by_name'] ?? '',
                $row['review_hours'] ?? '',
                $row['reapply_count'],
            ], ';');
        }

        fclose($output);
        exit;
    }
}
