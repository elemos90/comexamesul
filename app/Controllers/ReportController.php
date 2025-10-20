<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\ExamReport;
use App\Models\Jury;
use App\Services\ActivityLogger;
use App\Utils\Auth;
use App\Utils\Flash;
use App\Utils\Validator;

class ReportController extends Controller
{
    public function show(Request $request): string
    {
        $juryId = (int) $request->param('id');
        $juryModel = new Jury();
        $jury = $juryModel->find($juryId);
        if (!$jury) {
            http_response_code(404);
            return $this->view('errors/404');
        }
        $this->authorizeSupervisor($jury);
        $reportModel = new ExamReport();
        $report = $reportModel->findByJury($juryId);
        if ($report) {
            Flash::add('error', 'Relatorio ja submetido.');
            redirect('/juries/' . $juryId);
        }
        return $this->view('reports/form', ['jury' => $jury]);
    }

    public function store(Request $request)
    {
        $juryId = (int) $request->param('id');
        $juryModel = new Jury();
        $jury = $juryModel->find($juryId);
        if (!$jury) {
            Flash::add('error', 'Juri nao encontrado.');
            redirect('/juries');
        }
        $this->authorizeSupervisor($jury);
        $reportModel = new ExamReport();
        if ($reportModel->findByJury($juryId)) {
            Flash::add('error', 'Relatorio ja submetido.');
            redirect('/juries/' . $juryId);
        }
        $data = $request->only(['present_m','present_f','absent_m','absent_f','occurrences']);
        $validator = new Validator();
        $rules = [
            'present_m' => 'required|numeric',
            'present_f' => 'required|numeric',
            'absent_m' => 'required|numeric',
            'absent_f' => 'required|numeric',
        ];
        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados do relatorio.');
            $_SESSION['errors'] = $validator->errors();
            redirect('/juries/' . $juryId . '/report');
        }
        $total = (int) $data['present_m'] + (int) $data['present_f'] + (int) $data['absent_m'] + (int) $data['absent_f'];
        $reportModel->create([
            'jury_id' => $juryId,
            'supervisor_id' => Auth::id(),
            'present_m' => (int) $data['present_m'],
            'present_f' => (int) $data['present_f'],
            'absent_m' => (int) $data['absent_m'],
            'absent_f' => (int) $data['absent_f'],
            'total' => $total,
            'occurrences' => $data['occurrences'] ?? null,
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        ActivityLogger::log('exam_reports', $juryId, 'create');
        Flash::add('success', 'Relatorio submetido.');
        redirect('/juries/' . $juryId);
    }

    private function authorizeSupervisor(array $jury): void
    {
        if ((int) $jury['supervisor_id'] !== (int) Auth::id()) {
            Flash::add('error', 'Apenas o supervisor designado pode submeter.');
            redirect('/juries');
        }
    }
}
