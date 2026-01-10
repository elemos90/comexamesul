<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Services\ConsolidatedReportService;
use App\Utils\Auth;

/**
 * Controller para Relatório Consolidado de Exames
 * 
 * Responsabilidades:
 * - Página principal com filtros
 * - Geração de relatório
 * - Exportação (PDF, Excel, CSV)
 * - Validação do relatório
 */
class ConsolidatedReportController extends Controller
{
    private ConsolidatedReportService $reportService;

    public function __construct()
    {
        $this->reportService = new ConsolidatedReportService();
    }

    /**
     * Página principal do Relatório Consolidado
     */
    public function index(): string
    {
        $user = Auth::user();

        // Verificar acesso (Coordenador ou Membro)
        if (!in_array($user['role'], ['coordenador', 'membro'])) {
            return $this->view('errors/403', ['message' => 'Sem permissão para aceder a esta página.']);
        }

        // Dados para filtros
        $vacancies = $this->reportService->getVacanciesForFilter();
        $locations = $this->reportService->getLocationsForFilter();
        $disciplines = $this->reportService->getDisciplinesForFilter();

        return $this->view('reports/consolidated', [
            'user' => $user,
            'vacancies' => $vacancies,
            'locations' => $locations,
            'disciplines' => $disciplines,
            'canValidate' => $user['role'] === 'coordenador',
        ]);
    }

    /**
     * Gerar relatório com filtros (AJAX)
     */
    public function generate(Request $request): void
    {
        // Log para depuração
        error_log('[ConsolidatedReport] generate() chamado');

        try {
            $user = Auth::user();
            error_log('[ConsolidatedReport] User: ' . ($user['name'] ?? 'null'));

            if (!in_array($user['role'], ['coordenador', 'membro'])) {
                Response::json(['success' => false, 'message' => 'Sem permissão'], 403);
                return;
            }

            // Parse filters from request body (already parsed as JSON by index.php)
            $filters = [
                'vacancy_id' => $request->input('vacancy_id'),
                'location' => $request->input('location'),
                'discipline' => $request->input('discipline'),
                'date_from' => $request->input('date_from'),
                'date_to' => $request->input('date_to'),
                'year' => $request->input('year'),
            ];

            // Remover filtros vazios
            $filters = array_filter($filters, fn($v) => !empty($v));
            error_log('[ConsolidatedReport] Filters: ' . json_encode($filters));

            $data = $this->reportService->getConsolidatedData($filters);
            error_log('[ConsolidatedReport] Data retrieved successfully');

            $chartData = $this->reportService->getChartData($filters);
            error_log('[ConsolidatedReport] Chart data retrieved');

            Response::json([
                'success' => true,
                'data' => $data,
                'charts' => $chartData,
            ]);

        } catch (\Exception $e) {
            error_log('[ConsolidatedReport] ERROR: ' . $e->getMessage());
            error_log('[ConsolidatedReport] TRACE: ' . $e->getTraceAsString());
            Response::json([
                'success' => false,
                'message' => 'Erro ao gerar relatório: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Exportar para PDF
     */
    public function exportPdf(Request $request): void
    {
        $user = Auth::user();

        if (!in_array($user['role'], ['coordenador', 'membro'])) {
            http_response_code(403);
            die('Sem permissão');
        }

        $filters = $this->extractFiltersFromQuery($request);
        $data = $this->reportService->getConsolidatedData($filters);

        // Gerar HTML do relatório
        ob_start();
        extract(['data' => $data, 'user' => $user]);
        include __DIR__ . '/../Views/reports/consolidated_pdf.php';
        $html = ob_get_clean();

        // Usar Dompdf se disponível, senão output HTML
        if (class_exists('Dompdf\Dompdf')) {
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            $dompdf->stream('relatorio_consolidado_' . date('Y-m-d') . '.pdf', ['Attachment' => true]);
        } else {
            // Fallback: imprimir HTML
            header('Content-Type: text/html; charset=utf-8');
            echo $html;
        }
    }

    /**
     * Exportar para Excel
     */
    public function exportExcel(Request $request): void
    {
        $user = Auth::user();

        if (!in_array($user['role'], ['coordenador', 'membro'])) {
            http_response_code(403);
            die('Sem permissão');
        }

        $filters = $this->extractFiltersFromQuery($request);
        $data = $this->reportService->getConsolidatedData($filters);

        // Se PhpSpreadsheet disponível
        if (class_exists('PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $this->generateExcelWithPhpSpreadsheet($data);
        } else {
            // Fallback: gerar HTML table como Excel
            $this->generateExcelFallback($data);
        }
    }

    /**
     * Exportar para CSV
     */
    public function exportCsv(Request $request): void
    {
        $user = Auth::user();

        if (!in_array($user['role'], ['coordenador', 'membro'])) {
            http_response_code(403);
            die('Sem permissão');
        }

        $filters = $this->extractFiltersFromQuery($request);
        $data = $this->reportService->getConsolidatedData($filters);

        $filename = 'relatorio_consolidado_' . date('Y-m-d') . '.csv';

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');

        // BOM para Excel reconhecer UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Cabeçalho
        fputcsv($output, ['RELATÓRIO CONSOLIDADO DE EXAMES'], ';');
        fputcsv($output, ['Gerado em: ' . $data['generated_at']], ';');
        fputcsv($output, [], ';');

        // Resumo
        fputcsv($output, ['=== RESUMO GERAL ==='], ';');
        fputcsv($output, ['Total de Exames', $data['summary']['total_exames']], ';');
        fputcsv($output, ['Total de Júris/Salas', $data['summary']['total_juris']], ';');
        fputcsv($output, ['Candidatos Esperados', $data['summary']['total_esperados']], ';');
        fputcsv($output, ['Presentes', $data['summary']['total_presentes']], ';');
        fputcsv($output, ['Ausentes', $data['summary']['total_ausentes']], ';');
        fputcsv($output, ['Fraudes', $data['summary']['total_fraudes']], ';');
        fputcsv($output, [], ';');

        // Estatísticas por género
        fputcsv($output, ['=== ESTATÍSTICAS POR GÉNERO ==='], ';');
        fputcsv($output, ['Indicador', 'Masculino', 'Feminino', 'Total'], ';');
        fputcsv($output, [
            'Presentes',
            $data['statistics']['presentes']['masculino'],
            $data['statistics']['presentes']['feminino'],
            $data['statistics']['presentes']['total']
        ], ';');
        fputcsv($output, [
            'Ausentes',
            $data['statistics']['ausentes']['masculino'],
            $data['statistics']['ausentes']['feminino'],
            $data['statistics']['ausentes']['total']
        ], ';');
        fputcsv($output, [
            'Fraudes',
            $data['statistics']['fraudes']['masculino'],
            $data['statistics']['fraudes']['feminino'],
            $data['statistics']['fraudes']['total']
        ], ';');
        fputcsv($output, [], ';');

        // Por disciplina
        fputcsv($output, ['=== DETALHAMENTO POR DISCIPLINA ==='], ';');
        fputcsv($output, ['Disciplina', 'Salas', 'Esperados', 'Presentes', 'Ausentes', 'Fraudes', 'Taxa Presença (%)'], ';');
        foreach ($data['by_discipline'] as $disc) {
            fputcsv($output, [
                $disc['disciplina'],
                $disc['salas'],
                $disc['esperados'],
                $disc['presentes'],
                $disc['ausentes'],
                $disc['fraudes'],
                $disc['taxa_presenca']
            ], ';');
        }
        fputcsv($output, [], ';');

        // Ocorrências
        if (!empty($data['occurrences'])) {
            fputcsv($output, ['=== OCORRÊNCIAS ==='], ';');
            fputcsv($output, ['Data', 'Disciplina', 'Local', 'Sala', 'Fraudes M', 'Fraudes F', 'Observações'], ';');
            foreach ($data['occurrences'] as $occ) {
                fputcsv($output, [
                    $occ['data'],
                    $occ['disciplina'],
                    $occ['local'],
                    $occ['sala'],
                    $occ['fraudes_m'],
                    $occ['fraudes_f'],
                    $occ['observacoes'] ?? ''
                ], ';');
            }
        }

        fclose($output);
        exit;
    }

    /**
     * Extrair filtros da query string
     */
    private function extractFiltersFromQuery(Request $request): array
    {
        return array_filter([
            'vacancy_id' => $request->query('vacancy_id'),
            'location' => $request->query('location'),
            'discipline' => $request->query('discipline'),
            'date_from' => $request->query('date_from'),
            'date_to' => $request->query('date_to'),
            'year' => $request->query('year'),
        ], fn($v) => !empty($v));
    }

    /**
     * Gerar Excel com PhpSpreadsheet
     */
    private function generateExcelWithPhpSpreadsheet(array $data): void
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // Aba 1: Resumo
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumo Geral');
        $sheet->setCellValue('A1', 'RELATÓRIO CONSOLIDADO DE EXAMES');
        $sheet->setCellValue('A3', 'Total de Exames');
        $sheet->setCellValue('B3', $data['summary']['total_exames']);
        $sheet->setCellValue('A4', 'Total de Júris');
        $sheet->setCellValue('B4', $data['summary']['total_juris']);
        $sheet->setCellValue('A5', 'Presentes');
        $sheet->setCellValue('B5', $data['summary']['total_presentes']);
        $sheet->setCellValue('A6', 'Ausentes');
        $sheet->setCellValue('B6', $data['summary']['total_ausentes']);
        $sheet->setCellValue('A7', 'Fraudes');
        $sheet->setCellValue('B7', $data['summary']['total_fraudes']);

        // Aba 2: Estatísticas
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Estatísticas');
        $sheet2->setCellValue('A1', 'Indicador');
        $sheet2->setCellValue('B1', 'Masculino');
        $sheet2->setCellValue('C1', 'Feminino');
        $sheet2->setCellValue('D1', 'Total');

        $row = 2;
        foreach (['presentes', 'ausentes', 'fraudes'] as $tipo) {
            $sheet2->setCellValue('A' . $row, ucfirst($tipo));
            $sheet2->setCellValue('B' . $row, $data['statistics'][$tipo]['masculino']);
            $sheet2->setCellValue('C' . $row, $data['statistics'][$tipo]['feminino']);
            $sheet2->setCellValue('D' . $row, $data['statistics'][$tipo]['total']);
            $row++;
        }

        // Aba 3: Por Disciplina
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Por Disciplina');
        $sheet3->setCellValue('A1', 'Disciplina');
        $sheet3->setCellValue('B1', 'Salas');
        $sheet3->setCellValue('C1', 'Presentes');
        $sheet3->setCellValue('D1', 'Ausentes');
        $sheet3->setCellValue('E1', 'Fraudes');

        $row = 2;
        foreach ($data['by_discipline'] as $disc) {
            $sheet3->setCellValue('A' . $row, $disc['disciplina']);
            $sheet3->setCellValue('B' . $row, $disc['salas']);
            $sheet3->setCellValue('C' . $row, $disc['presentes']);
            $sheet3->setCellValue('D' . $row, $disc['ausentes']);
            $sheet3->setCellValue('E' . $row, $disc['fraudes']);
            $row++;
        }

        $spreadsheet->setActiveSheetIndex(0);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="relatorio_consolidado_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * Fallback: Gerar Excel como HTML
     */
    private function generateExcelFallback(array $data): void
    {
        $filename = 'relatorio_consolidado_' . date('Y-m-d') . '.xls';

        header('Content-Type: application/vnd.ms-excel; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        echo '<html><head><meta charset="utf-8"></head><body>';
        echo '<h1>Relatório Consolidado de Exames</h1>';
        echo '<p>Gerado em: ' . $data['generated_at'] . '</p>';

        // Resumo
        echo '<h2>Resumo Geral</h2>';
        echo '<table border="1">';
        echo '<tr><th>Indicador</th><th>Valor</th></tr>';
        echo '<tr><td>Total de Exames</td><td>' . $data['summary']['total_exames'] . '</td></tr>';
        echo '<tr><td>Total de Júris</td><td>' . $data['summary']['total_juris'] . '</td></tr>';
        echo '<tr><td>Presentes</td><td>' . $data['summary']['total_presentes'] . '</td></tr>';
        echo '<tr><td>Ausentes</td><td>' . $data['summary']['total_ausentes'] . '</td></tr>';
        echo '<tr><td>Fraudes</td><td>' . $data['summary']['total_fraudes'] . '</td></tr>';
        echo '</table>';

        // Estatísticas
        echo '<h2>Estatísticas por Género</h2>';
        echo '<table border="1">';
        echo '<tr><th>Indicador</th><th>Masculino</th><th>Feminino</th><th>Total</th></tr>';
        foreach (['presentes', 'ausentes', 'fraudes'] as $tipo) {
            echo '<tr>';
            echo '<td>' . ucfirst($tipo) . '</td>';
            echo '<td>' . $data['statistics'][$tipo]['masculino'] . '</td>';
            echo '<td>' . $data['statistics'][$tipo]['feminino'] . '</td>';
            echo '<td>' . $data['statistics'][$tipo]['total'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        // Por Disciplina
        echo '<h2>Por Disciplina</h2>';
        echo '<table border="1">';
        echo '<tr><th>Disciplina</th><th>Salas</th><th>Presentes</th><th>Ausentes</th><th>Fraudes</th></tr>';
        foreach ($data['by_discipline'] as $disc) {
            echo '<tr>';
            echo '<td>' . $disc['disciplina'] . '</td>';
            echo '<td>' . $disc['salas'] . '</td>';
            echo '<td>' . $disc['presentes'] . '</td>';
            echo '<td>' . $disc['ausentes'] . '</td>';
            echo '<td>' . $disc['fraudes'] . '</td>';
            echo '</tr>';
        }
        echo '</table>';

        echo '</body></html>';
        exit;
    }
}
