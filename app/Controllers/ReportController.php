<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\ExamReport;
use App\Models\Jury;
use App\Models\JuryVigilante;
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

        $this->authorizeSubmission($jury);

        $reportModel = new ExamReport();
        // Check if THIS user already submitted for this jury (allow multiple reports per jury if from different users, but logic says "one per jury" in original code. 
        // User request says: "Um utilizador pode submeter 1 lançamento por júri".
        // And "Se houver mais de um lançamento no mesmo júri: somar os valores".
        // So we should check if THIS user submitted.

        // Original code checked findByJury which returned one record. We need to check if *this user* submitted.
        // But the model findByJury returns *any* report. 
        // Let's assume for now we want to check if *this user* submitted.
        // However, the original code blocked if ANY report existed.
        // The new requirement says "Consolidação por Júri: Se houver mais de um lançamento no mesmo júri".
        // So we should allow multiple reports.

        // Let's check if the current user already submitted
        $existingReport = $reportModel->statement(
            "SELECT id FROM exam_reports WHERE jury_id = :jury AND supervisor_id = :user LIMIT 1",
            ['jury' => $juryId, 'user' => Auth::id()]
        );

        if ($existingReport) {
            Flash::add('error', 'Você já submeteu um relatório para este júri.');
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

        $role = $this->authorizeSubmission($jury);

        $reportModel = new ExamReport();

        // Check double submission by same user
        $existingReport = $reportModel->statement(
            "SELECT id FROM exam_reports WHERE jury_id = :jury AND supervisor_id = :user LIMIT 1",
            ['jury' => $juryId, 'user' => Auth::id()]
        );

        if ($existingReport) {
            Flash::add('error', 'Você já submeteu um relatório para este júri.');
            redirect('/juries/' . $juryId);
        }

        $data = $request->only(['present_m', 'present_f', 'absent_m', 'absent_f', 'fraudes_m', 'fraudes_f', 'occurrences']);
        $validator = new Validator();
        $rules = [
            'present_m' => 'required|numeric|min:0',
            'present_f' => 'required|numeric|min:0',
            'absent_m' => 'required|numeric|min:0',
            'absent_f' => 'required|numeric|min:0',
            'fraudes_m' => 'required|numeric|min:0',
            'fraudes_f' => 'required|numeric|min:0',
        ];

        if (!$validator->validate($data, $rules)) {
            Flash::add('error', 'Verifique os dados do relatorio.');
            $_SESSION['errors'] = $validator->errors();
            redirect('/juries/' . $juryId . '/report');
        }

        // Validate consistency
        if ((int) $data['fraudes_m'] > (int) $data['present_m']) {
            Flash::add('error', 'Número de fraudes masculinas não pode ser maior que presentes.');
            redirect('/juries/' . $juryId . '/report');
        }
        if ((int) $data['fraudes_f'] > (int) $data['present_f']) {
            Flash::add('error', 'Número de fraudes femininas não pode ser maior que presentes.');
            redirect('/juries/' . $juryId . '/report');
        }

        $total = (int) $data['present_m'] + (int) $data['present_f'] + (int) $data['absent_m'] + (int) $data['absent_f'];

        $reportModel->create([
            'jury_id' => $juryId,
            'supervisor_id' => Auth::id(), // Using supervisor_id column to store the submitter ID
            'role' => $role,
            'present_m' => (int) $data['present_m'],
            'present_f' => (int) $data['present_f'],
            'absent_m' => (int) $data['absent_m'],
            'absent_f' => (int) $data['absent_f'],
            'fraudes_m' => (int) $data['fraudes_m'],
            'fraudes_f' => (int) $data['fraudes_f'],
            'total' => $total,
            'occurrences' => $data['occurrences'] ?? null,
            'submitted_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        ActivityLogger::log('exam_reports', $juryId, 'create');
        Flash::add('success', 'Relatorio submetido com sucesso.');
        redirect('/juries/' . $juryId);
    }

    private function authorizeSubmission(array $jury): string
    {
        $userId = (int) Auth::id();
        $userRole = Auth::user()['role']; // Assuming role is available in session user

        // 1. Check if user is the supervisor
        if ((int) $jury['supervisor_id'] === $userId) {
            return 'supervisor';
        }

        // 2. Check if user is an allocated vigilante
        $juryVigilanteModel = new JuryVigilante();
        $isAllocated = $juryVigilanteModel->statement(
            "SELECT id FROM jury_vigilantes WHERE jury_id = :jury AND vigilante_id = :user",
            ['jury' => $jury['id'], 'user' => $userId]
        );

        if ($isAllocated) {
            return 'vigilante';
        }

        // 3. Allow admins/coordinators
        if (in_array($userRole, ['admin', 'coordenador'], true)) {
            return $userRole;
        }

        // 4. Strict allocation check for others
        Flash::add('error', 'Apenas vigilantes ou supervisores alocados a este júri podem submeter o relatório.');
        redirect('/juries');
        exit; // Should not be reached due to redirect
    }
}

