<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\Jury;
use App\Models\LocationTemplate;
use App\Models\LocationStats;
use App\Services\ActivityLogger;
use App\Utils\Auth;
use App\Utils\Flash;
use App\Utils\Validator;

class LocationController extends Controller
{
    // Visualização agrupada por local
    public function index(): string
    {
        $user = Auth::user();
        $juryModel = new Jury();
        
        if ($user['role'] === 'vigilante') {
            // Vigilantes não têm acesso a esta view
            redirect('/juries');
        }
        
        $locationGroups = $juryModel->getGroupedByLocationAndDate();
        
        return $this->view('locations/index', [
            'locationGroups' => $locationGroups,
            'user' => $user,
        ]);
    }

    // Dashboard de estatísticas
    public function dashboard(): string
    {
        $user = Auth::user();
        $statsModel = new LocationStats();
        
        // Atualizar estatísticas para todos os locais
        $this->refreshAllStats();
        
        $allStats = $statsModel->getAllStats();
        $topLocations = $statsModel->getTopLocations(10);
        
        // Agrupar por local
        $statsByLocation = [];
        foreach ($allStats as $stat) {
            $location = $stat['location'];
            if (!isset($statsByLocation[$location])) {
                $statsByLocation[$location] = [
                    'location' => $location,
                    'dates' => [],
                    'total_juries' => 0,
                    'total_candidates' => 0,
                    'total_vigilantes' => 0,
                ];
            }
            $statsByLocation[$location]['dates'][] = $stat;
            $statsByLocation[$location]['total_juries'] += (int) $stat['total_juries'];
            $statsByLocation[$location]['total_candidates'] += (int) $stat['total_candidates'];
            $statsByLocation[$location]['total_vigilantes'] += (int) $stat['total_vigilantes'];
        }
        
        return $this->view('locations/dashboard', [
            'statsByLocation' => array_values($statsByLocation),
            'topLocations' => $topLocations,
            'user' => $user,
        ]);
    }

    // Templates: Listar
    public function templates(): string
    {
        $user = Auth::user();
        $templateModel = new LocationTemplate();
        
        $templates = $templateModel->getAllWithCounts();
        
        return $this->view('locations/templates', [
            'templates' => $templates,
            'user' => $user,
        ]);
    }

    // Templates: Criar
    public function storeTemplate(Request $request)
    {
        $name = $request->input('name');
        $location = $request->input('location');
        $description = $request->input('description');
        $disciplines = $request->input('disciplines');
        
        $validator = new Validator();
        if (!$validator->validate(['name' => $name, 'location' => $location], [
            'name' => 'required|min:3|max:120',
            'location' => 'required|max:150',
        ])) {
            Flash::add('error', 'Verifique os dados do template.');
            $_SESSION['errors'] = $validator->errors();
            redirect('/locations/templates');
        }
        
        if (empty($disciplines) || !is_array($disciplines)) {
            Flash::add('error', 'Adicione pelo menos uma disciplina ao template.');
            redirect('/locations/templates');
        }
        
        $templateModel = new LocationTemplate();
        $templateId = $templateModel->createWithStructure([
            'name' => $name,
            'location' => $location,
            'description' => $description,
            'created_by' => Auth::id(),
        ], $disciplines);
        
        ActivityLogger::log('location_templates', $templateId, 'create');
        Flash::add('success', "Template '{$name}' criado com sucesso.");
        redirect('/locations/templates');
    }

    // Templates: Carregar para uso
    public function loadTemplate(Request $request)
    {
        $templateId = (int) $request->param('id');
        $templateModel = new LocationTemplate();
        
        $template = $templateModel->withDetails($templateId);
        if (!$template) {
            Response::json(['message' => 'Template não encontrado.'], 404);
        }
        
        Response::json(['template' => $template]);
    }

    // Templates: Toggle ativo/inativo
    public function toggleTemplate(Request $request)
    {
        $templateId = (int) $request->param('id');
        $templateModel = new LocationTemplate();
        
        if ($templateModel->toggleActive($templateId)) {
            ActivityLogger::log('location_templates', $templateId, 'toggle_active');
            Flash::add('success', 'Status do template atualizado.');
        } else {
            Flash::add('error', 'Template não encontrado.');
        }
        
        redirect('/locations/templates');
    }

    // Templates: Excluir
    public function deleteTemplate(Request $request)
    {
        $templateId = (int) $request->param('id');
        $templateModel = new LocationTemplate();
        
        $template = $templateModel->find($templateId);
        if (!$template) {
            Flash::add('error', 'Template não encontrado.');
            redirect('/locations/templates');
        }
        
        $templateModel->delete($templateId);
        ActivityLogger::log('location_templates', $templateId, 'delete');
        Flash::add('success', 'Template eliminado.');
        redirect('/locations/templates');
    }

    // Import: Mostrar página
    public function showImport(): string
    {
        $user = Auth::user();
        return $this->view('locations/import', ['user' => $user]);
    }

    // Import: Processar arquivo
    public function processImport(Request $request)
    {
        if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            Flash::add('error', 'Nenhum arquivo enviado ou erro no upload.');
            redirect('/locations/import');
        }
        
        $file = $_FILES['file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        if (!in_array($ext, ['xlsx', 'xls', 'csv'])) {
            Flash::add('error', 'Formato inválido. Use .xlsx, .xls ou .csv');
            redirect('/locations/import');
        }
        
        try {
            require_once __DIR__ . '/../../vendor/autoload.php';
            
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();
            
            if (count($rows) < 2) {
                Flash::add('error', 'Arquivo vazio ou sem dados.');
                redirect('/locations/import');
            }
            
            // Processar linhas (ignorar cabeçalho)
            $juryModel = new Jury();
            $created = 0;
            $errors = [];
            
            for ($i = 1; $i < count($rows); $i++) {
                $row = $rows[$i];
                
                // Formato esperado: Local | Data | Disciplina | Início | Fim | Sala | Candidatos
                if (count($row) < 7) {
                    $errors[] = "Linha " . ($i + 1) . ": dados insuficientes";
                    continue;
                }
                
                [$location, $date, $subject, $start, $end, $room, $quota] = $row;
                
                if (empty($location) || empty($date) || empty($subject) || empty($room)) {
                    $errors[] = "Linha " . ($i + 1) . ": campos obrigatórios vazios";
                    continue;
                }
                
                try {
                    $juryId = $juryModel->create([
                        'subject' => $subject,
                        'exam_date' => date('Y-m-d', strtotime($date)),
                        'start_time' => date('H:i:s', strtotime($start)),
                        'end_time' => date('H:i:s', strtotime($end)),
                        'location' => $location,
                        'room' => $room,
                        'candidates_quota' => (int) $quota,
                        'notes' => null,
                        'created_by' => Auth::id(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    ActivityLogger::log('juries', $juryId, 'import', ['file' => $file['name']]);
                    $created++;
                } catch (\Exception $e) {
                    $errors[] = "Linha " . ($i + 1) . ": " . $e->getMessage();
                }
            }
            
            if ($created > 0) {
                Flash::add('success', "Importados {$created} júris com sucesso.");
            }
            
            if (!empty($errors)) {
                $_SESSION['import_errors'] = $errors;
                Flash::add('warning', count($errors) . ' erro(s) encontrado(s). Verifique os detalhes.');
            }
            
        } catch (\Exception $e) {
            Flash::add('error', 'Erro ao processar arquivo: ' . $e->getMessage());
        }
        
        redirect('/locations/import');
    }

    // Export: Template vazio
    public function exportTemplate()
    {
        require_once __DIR__ . '/../../vendor/autoload.php';
        
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Cabeçalhos
        $headers = ['Local', 'Data (dd/mm/yyyy)', 'Disciplina', 'Início (HH:MM)', 'Fim (HH:MM)', 'Sala', 'Candidatos'];
        $sheet->fromArray($headers, null, 'A1');
        
        // Exemplo
        $example = [
            'Campus Central',
            '15/11/2025',
            'Matemática I',
            '08:00',
            '11:00',
            '101',
            30
        ];
        $sheet->fromArray($example, null, 'A2');
        
        // Estilizar cabeçalho
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']]
        ];
        $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
        
        // Ajustar largura das colunas
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="template_juris.xlsx"');
        header('Cache-Control: max-age=0');
        
        $writer->save('php://output');
        exit;
    }

    // Helper: Atualizar todas as estatísticas
    private function refreshAllStats(): void
    {
        $juryModel = new Jury();
        $statsModel = new LocationStats();
        
        $juries = $juryModel->statement(
            "SELECT DISTINCT location, exam_date FROM juries"
        );
        
        foreach ($juries as $jury) {
            $statsModel->updateStatsForLocation($jury['location'], $jury['exam_date']);
        }
    }
}
