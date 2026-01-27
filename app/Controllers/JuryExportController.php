<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\Jury;

use App\Http\Response;
use App\Utils\Auth;

/**
 * Controller for Jury Exports (Excel, PDF, Print)
 */
class JuryExportController extends Controller
{
    /**
     * Export juries to Excel
     */
    public function exportExcel(Request $request): void
    {
        try {
            $user = Auth::user();
            $vacancyId = $request->input('vacancy_id', 'current');

            // Get jury data (Ensure JuryService exists or adapt logic)
            // Assuming App\Services\JuryService exists as per original code context
            // If not, we might need to replicate the query logic here or check imports
            if (class_exists('\\App\\Services\\JuryService')) {
                $juryService = new \App\Services\JuryService();
                $data = $juryService->getDashboardData($user, $vacancyId);
            } else {
                // Fallback implementation if Service isn't available directly
                // (Simplified for now, verifying dependency existence is key)
                Response::json(['success' => false, 'message' => 'Service error during export'], 500);
                return;
            }

            // Check if PhpSpreadsheet is available
            if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\Spreadsheet')) {
                Response::json(['success' => false, 'message' => 'PhpSpreadsheet não está instalado'], 500);
                return;
            }

            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setTitle('Júris de Exames');

            // Header styling
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
                'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
            ];

            // Set headers
            $headers = ['Disciplina', 'Sala', 'Data', 'Hora', 'Local', 'Vaga', 'Vigilante', 'Supervisor'];
            $col = 'A';
            foreach ($headers as $header) {
                $sheet->setCellValue($col . '1', $header);
                $sheet->getStyle($col . '1')->applyFromArray($headerStyle);
                $sheet->getColumnDimension($col)->setAutoSize(true);
                $col++;
            }

            // Fill data
            $row = 2;
            if (isset($data['juries']) && is_array($data['juries'])) {
                foreach ($data['juries'] as $jury) {
                    $sheet->setCellValue('A' . $row, $jury['discipline'] ?? '');
                    $sheet->setCellValue('B' . $row, $jury['room'] ?? '');
                    $sheet->setCellValue('C' . $row, date('d/m/Y', strtotime($jury['exam_date'])));
                    $sheet->setCellValue('D' . $row, substr($jury['exam_time'], 0, 5));
                    $sheet->setCellValue('E' . $row, $jury['location'] ?? '');
                    $sheet->setCellValue('F' . $row, $jury['vacancy_name'] ?? '');

                    // Vigilantes
                    $vigilantes = isset($jury['vigilantes']) ? array_column($jury['vigilantes'], 'name') : [];
                    $sheet->setCellValue('G' . $row, implode(', ', $vigilantes));

                    // Supervisor
                    $sheet->setCellValue('H' . $row, $jury['supervisor_name'] ?? '');

                    $row++;
                }
            }

            // Auto-size columns
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }

            // Output
            $filename = 'juris_exames_' . date('Y-m-d_H-i-s') . '.xlsx';

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '"');
            header('Cache-Control: max-age=0');

            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            error_log('Excel export error: ' . $e->getMessage());
            Response::json(['success' => false, 'message' => 'Erro ao exportar: ' . $e->getMessage()], 500);
        }
    }
}
