<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportService
{
    private ReportService $reports;

    public function __construct()
    {
        $this->reports = new ReportService();
    }

    public function vigilantesXls(array $filters = []): void
    {
        $this->ensureSpreadsheet();
        $records = $this->reports->availableVigilantes($filters);
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Vigilantes aprovados');
        $headers = ['Nome', 'Email', 'Telefone', 'Universidade', 'TitulaÃ§Ã£o', 'Ãrea', 'Banco', 'NIB'];
        foreach ($headers as $index => $title) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $title);
        }
        $row = 2;
        foreach ($records as $record) {
            $sheet->setCellValueByColumnAndRow(1, $row, $record['name']);
            $sheet->setCellValueByColumnAndRow(2, $row, $record['email']);
            $sheet->setCellValueByColumnAndRow(3, $row, $record['phone'] ?? '');
            $sheet->setCellValueByColumnAndRow(4, $row, $record['university'] ?? '');
            $sheet->setCellValueByColumnAndRow(5, $row, $record['degree'] ?? '');
            $sheet->setCellValueByColumnAndRow(6, $row, $record['major_area'] ?? '');
            $sheet->setCellValueByColumnAndRow(7, $row, $record['bank_name'] ?? '');
            $sheet->setCellValueByColumnAndRow(8, $row, $record['nib'] ?? '');
            $row++;
        }
        $this->streamSpreadsheet($spreadsheet, 'vigilantes_aprovados.xls');
    }

    public function vigilantesPdf(array $filters = []): void
    {
        $records = $this->reports->availableVigilantes($filters);
        $html = $this->renderTableHtml('Vigilantes aprovados', ['Nome', 'Email', 'Telefone', 'Universidade', 'TitulaÃ§Ã£o'], function ($record) {
            return [
                $record['name'],
                $record['email'],
                $record['phone'] ?? '',
                $record['university'] ?? '',
                $record['degree'] ?? '',
            ];
        }, $records);
        $this->streamPdf($html, 'vigilantes_aprovados.pdf');
    }

    public function supervisoresXls(): void
    {
        $this->ensureSpreadsheet();
        $records = $this->reports->supervisorsByJury();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Supervisores por jÃºri');
        $headers = ['Disciplina', 'Data', 'HorÃ¡rio', 'Local', 'Supervisor'];
        foreach ($headers as $index => $title) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $title);
        }
        $row = 2;
        foreach ($records as $record) {
            $sheet->setCellValueByColumnAndRow(1, $row, $record['subject']);
            $sheet->setCellValueByColumnAndRow(2, $row, $record['exam_date']);
            $sheet->setCellValueByColumnAndRow(3, $row, $record['start_time'] . ' - ' . $record['end_time']);
            $sheet->setCellValueByColumnAndRow(4, $row, $record['location'] . ' / ' . $record['room']);
            $sheet->setCellValueByColumnAndRow(5, $row, $record['supervisor_name'] ?? '-');
            $row++;
        }
        $this->streamSpreadsheet($spreadsheet, 'supervisores_juris.xls');
    }

    public function supervisoresPdf(): void
    {
        $records = $this->reports->supervisorsByJury();
        $html = $this->renderTableHtml('Supervisores por jÃºri', ['Disciplina', 'Data', 'HorÃ¡rio', 'Local', 'Supervisor'], function ($record) {
            return [
                $record['subject'],
                $record['exam_date'],
                $record['start_time'] . ' - ' . $record['end_time'],
                $record['location'] . ' / ' . $record['room'],
                $record['supervisor_name'] ?? '-',
            ];
        }, $records);
        $this->streamPdf($html, 'supervisores_juris.pdf');
    }

    public function vigiasXls(): void
    {
        $this->ensureSpreadsheet();
        $records = $this->reports->consolidatedVigias();
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Consolidado vigias');
        $headers = ['Disciplina', 'Data', 'HorÃ¡rio', 'Presentes H', 'Presentes M', 'Ausentes H', 'Ausentes M', 'Total'];
        foreach ($headers as $index => $title) {
            $sheet->setCellValueByColumnAndRow($index + 1, 1, $title);
        }
        $row = 2;
        foreach ($records as $record) {
            $sheet->setCellValueByColumnAndRow(1, $row, $record['subject']);
            $sheet->setCellValueByColumnAndRow(2, $row, $record['exam_date']);
            $sheet->setCellValueByColumnAndRow(3, $row, $record['start_time'] . ' - ' . $record['end_time']);
            $sheet->setCellValueByColumnAndRow(4, $row, $record['present_m'] ?? 0);
            $sheet->setCellValueByColumnAndRow(5, $row, $record['present_f'] ?? 0);
            $sheet->setCellValueByColumnAndRow(6, $row, $record['absent_m'] ?? 0);
            $sheet->setCellValueByColumnAndRow(7, $row, $record['absent_f'] ?? 0);
            $sheet->setCellValueByColumnAndRow(8, $row, $record['total'] ?? 0);
            $row++;
        }
        $this->streamSpreadsheet($spreadsheet, 'consolidado_vigias.xls');
    }

    public function vigiasPdf(): void
    {
        $records = $this->reports->consolidatedVigias();
        $html = $this->renderTableHtml('Consolidado de vigias', ['Disciplina', 'Data', 'HorÃ¡rio', 'Presentes H', 'Presentes M', 'Ausentes H', 'Ausentes M', 'Total'], function ($record) {
            return [
                $record['subject'],
                $record['exam_date'],
                $record['start_time'] . ' - ' . $record['end_time'],
                $record['present_m'] ?? 0,
                $record['present_f'] ?? 0,
                $record['absent_m'] ?? 0,
                $record['absent_f'] ?? 0,
                $record['total'] ?? 0,
            ];
        }, $records);
        $this->streamPdf($html, 'consolidado_vigias.pdf');
    }

    private function ensureSpreadsheet(): void
    {
        if (!class_exists(Spreadsheet::class)) {
            throw new \RuntimeException('PhpSpreadsheet nÃ£o estÃ¡ instalado. Execute composer install.');
        }
    }

    private function streamSpreadsheet(Spreadsheet $spreadsheet, string $filename): void
    {
        if (ob_get_length()) {
            ob_end_clean();
        }
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Cache-Control: max-age=0');
        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    private function streamPdf(string $html, string $filename): void
    {
        if (!class_exists(Dompdf::class)) {
            throw new \RuntimeException('Dompdf nÃ£o estÃ¡ instalado. Execute composer install.');
        }
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();
        if (ob_get_length()) {
            ob_end_clean();
        }
        $dompdf->stream($filename, ['Attachment' => true]);
        exit;
    }

    private function renderTableHtml(string $title, array $headers, callable $rowMapper, array $records): string
    {
        $headerHtml = '';
        foreach ($headers as $header) {
            $headerHtml .= '<th style="padding:8px;border:1px solid #ddd;background:#f8fafc;text-align:left;">' . htmlspecialchars($header) . '</th>';
        }
        $rowsHtml = '';
        foreach ($records as $record) {
            $rowValues = $rowMapper($record);
            $rowsHtml .= '<tr>';
            foreach ($rowValues as $value) {
                $rowsHtml .= '<td style="padding:6px;border:1px solid #eee;">' . htmlspecialchars((string) $value) . '</td>';
            }
            $rowsHtml .= '</tr>';
        }
        if (!$records) {
            $rowsHtml .= '<tr><td colspan="' . count($headers) . '" style="padding:10px;text-align:center;border:1px solid #eee;">Sem registos.</td></tr>';
        }
        return '<h2 style="font-family:Arial,sans-serif;margin-bottom:12px;">' . htmlspecialchars($title) . '</h2>' .
            '<table style="width:100%;border-collapse:collapse;font-family:Arial,sans-serif;font-size:12px;">' .
            '<thead><tr>' . $headerHtml . '</tr></thead>' .
            '<tbody>' . $rowsHtml . '</tbody></table>';
    }
}

