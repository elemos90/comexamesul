<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\ExamReport;
use App\Models\ExamVacancy;
use App\Models\ExamLocation;
use App\Models\Discipline;
use App\Utils\Auth;
use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class StatsController extends Controller
{
    public function index()
    {
        // Dashboard for report generation
        $vacancies = (new ExamVacancy())->all();
        $locations = (new ExamLocation())->all();
        $disciplines = (new Discipline())->all();

        return $this->view('reports/stats_dashboard', [
            'vacancies' => $vacancies,
            'locations' => $locations,
            'disciplines' => $disciplines,
            'title' => 'Relatórios Estatísticos',
            'breadcrumbs' => [
                ['label' => 'Dashboard', 'url' => '/dashboard'],
                ['label' => 'Relatórios Estatísticos', 'url' => ''],
            ],
        ]);
    }

    public function generate(Request $request)
    {
        $filters = $request->only(['vacancy_id', 'location_id', 'discipline_id', 'exam_date']);
        $format = $request->input('format', 'pdf');

        $data = $this->getAggregatedData($filters);

        if ($format === 'excel') {
            return $this->generateExcel($data, $filters);
        } else {
            return $this->generatePdf($data, $filters);
        }
    }

    private function getAggregatedData(array $filters): array
    {
        $reportModel = new ExamReport();

        // Build query based on filters
        // This requires joining with juries table to filter by location, discipline, date

        $sql = "SELECT 
                    SUM(r.present_m) as present_m,
                    SUM(r.present_f) as present_f,
                    SUM(r.absent_m) as absent_m,
                    SUM(r.absent_f) as absent_f,
                    SUM(r.fraudes_m) as fraudes_m,
                    SUM(r.fraudes_f) as fraudes_f,
                    COUNT(DISTINCT r.id) as total_reports,
                    COUNT(DISTINCT j.id) as total_juries
                FROM exam_reports r
                JOIN juries j ON r.jury_id = j.id
                WHERE 1=1";

        $params = [];

        if (!empty($filters['vacancy_id'])) {
            $sql .= " AND j.vacancy_id = :vacancy_id";
            $params['vacancy_id'] = $filters['vacancy_id'];
        }

        if (!empty($filters['location_id'])) {
            $sql .= " AND j.location_id = :location_id";
            $params['location_id'] = $filters['location_id'];
        }

        if (!empty($filters['discipline_id'])) {
            $sql .= " AND j.discipline_id = :discipline_id";
            $params['discipline_id'] = $filters['discipline_id'];
        }

        if (!empty($filters['exam_date'])) {
            $sql .= " AND j.exam_date = :exam_date";
            $params['exam_date'] = $filters['exam_date'];
        }

        $result = $reportModel->statement($sql, $params);

        // Calculate totals
        $stats = $result ? $result[0] : [
            'present_m' => 0,
            'present_f' => 0,
            'absent_m' => 0,
            'absent_f' => 0,
            'fraudes_m' => 0,
            'fraudes_f' => 0,
            'total_reports' => 0,
            'total_juries' => 0
        ];

        $stats['present_total'] = $stats['present_m'] + $stats['present_f'];
        $stats['absent_total'] = $stats['absent_m'] + $stats['absent_f'];
        $stats['fraudes_total'] = $stats['fraudes_m'] + $stats['fraudes_f'];
        $stats['grand_total'] = $stats['present_total'] + $stats['absent_total']; // Total candidates involved (Present + Absent)

        return $stats;
    }

    private function generatePdf(array $data, array $filters)
    {
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);

        // Load HTML template
        // We need to pass data to the view and get the string content
        // Since we are inside a controller, we can use output buffering or a helper if available.
        // Our Controller::view method includes the layout, which we might not want for PDF.
        // We'll create a specific partial for the PDF content.

        ob_start();
        extract(['stats' => $data, 'filters' => $filters]);
        include view_path('reports/pdf_template.php');
        $html = ob_get_clean();

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("relatorio_estatistico_" . date('Y-m-d_H-i') . ".pdf", ["Attachment" => false]);
        exit;
    }

    private function generateExcel(array $data, array $filters)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Quadro Estatístico');

        // Headers
        $sheet->setCellValue('A1', 'Relatório Estatístico de Presenças, Ausências e Fraudes');
        $sheet->mergeCells('A1:D1');

        // Table
        $sheet->setCellValue('A3', 'Estado');
        $sheet->setCellValue('B3', 'Masculino');
        $sheet->setCellValue('C3', 'Feminino');
        $sheet->setCellValue('D3', 'Total');

        $sheet->setCellValue('A4', 'Presentes');
        $sheet->setCellValue('B4', $data['present_m']);
        $sheet->setCellValue('C4', $data['present_f']);
        $sheet->setCellValue('D4', $data['present_total']);

        $sheet->setCellValue('A5', 'Ausentes');
        $sheet->setCellValue('B5', $data['absent_m']);
        $sheet->setCellValue('C5', $data['absent_f']);
        $sheet->setCellValue('D5', $data['absent_total']);

        $sheet->setCellValue('A6', 'Fraudes');
        $sheet->setCellValue('B6', $data['fraudes_m']);
        $sheet->setCellValue('C6', $data['fraudes_f']);
        $sheet->setCellValue('D6', $data['fraudes_total']);

        $sheet->setCellValue('A7', 'Total Geral');
        $sheet->mergeCells('A7:C7');
        $sheet->setCellValue('D7', $data['grand_total']);

        // Styling
        $sheet->getStyle('A3:D3')->getFont()->setBold(true);
        $sheet->getStyle('A7:D7')->getFont()->setBold(true);

        // Create file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="relatorio_estatistico_' . date('Y-m-d') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
