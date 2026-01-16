<?php

namespace App\Controllers;

use App\Http\Request;
use App\Http\Response;
use App\Models\ExamVacancy;
use App\Models\JuryVigilante;
use App\Utils\Auth;

/**
 * Controller para funcionalidades do Calendário de Júris
 * 
 * Extraído do JuryController para melhorar a organização do código.
 * 
 * @package App\Controllers
 */
class JuryCalendarController extends Controller
{
    /**
     * Página do Calendário Visual de Júris
     */
    public function calendar(Request $request): string
    {
        $user = Auth::user();
        $vacancyModel = new ExamVacancy();

        $vacancyId = isset($_GET['vacancy_id']) ? (int) $_GET['vacancy_id'] : null;
        $allVacancies = $vacancyModel->statement('SELECT * FROM exam_vacancies ORDER BY created_at DESC LIMIT 10');

        return $this->view('juries/calendar', [
            'user' => $user,
            'vacancyId' => $vacancyId,
            'allVacancies' => $allVacancies,
        ]);
    }

    /**
     * API: Eventos do calendário para FullCalendar
     * 
     * Retorna eventos formatados para o componente FullCalendar.
     * Cada evento inclui informações do júri, supervisor e vigilantes.
     */
    public function calendarEvents(Request $request)
    {
        try {
            $start = $_GET['start'] ?? date('Y-m-01');
            $end = $_GET['end'] ?? date('Y-m-t');
            $vacancyId = isset($_GET['vacancy_id']) && $_GET['vacancy_id'] ? (int) $_GET['vacancy_id'] : null;

            $db = database();

            $sql = "
                SELECT j.*, 
                       COALESCE(el.name, j.location) as location_name,
                       j.room as room_name,
                       supervisor.name as supervisor_name,
                       (SELECT COUNT(*) FROM jury_vigilantes WHERE jury_id = j.id) as vigilantes_count,
                       CEIL(j.candidates_quota / 50.0) + 1 as vigilantes_required
                FROM juries j
                LEFT JOIN exam_locations el ON el.id = j.location_id
                LEFT JOIN users supervisor ON supervisor.id = j.supervisor_id
                WHERE j.exam_date >= :start AND j.exam_date <= :end
            ";

            $params = ['start' => $start, 'end' => $end];

            if ($vacancyId) {
                $sql .= " AND j.vacancy_id = :vacancy_id";
                $params['vacancy_id'] = $vacancyId;
            }

            $sql .= " ORDER BY j.exam_date, j.start_time";

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $juries = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $juryVigilantes = new JuryVigilante();

            $events = [];
            foreach ($juries as $jury) {
                $vigilantes = $juryVigilantes->vigilantesForJury((int) $jury['id']);
                $vigilantesCount = (int) $jury['vigilantes_count'];
                $vigilantesRequired = (int) $jury['vigilantes_required'];
                $hasSupervisor = !empty($jury['supervisor_id']);

                // Determinar classe CSS baseada no estado de alocação
                $className = $this->getEventClassName($vigilantesCount, $vigilantesRequired, $hasSupervisor);

                $events[] = [
                    'id' => $jury['id'],
                    'title' => $jury['subject'],
                    'start' => $jury['exam_date'] . 'T' . $jury['start_time'],
                    'end' => $jury['exam_date'] . 'T' . $jury['end_time'],
                    'className' => $className,
                    'extendedProps' => [
                        'date' => date('d/m/Y', strtotime($jury['exam_date'])),
                        'time' => substr($jury['start_time'], 0, 5) . ' - ' . substr($jury['end_time'], 0, 5),
                        'location' => $jury['location_name'],
                        'room' => $jury['room_name'],
                        'supervisor' => $jury['supervisor_name'] ?: 'Não definido',
                        'vigilantes_count' => $vigilantesCount,
                        'vigilantes_required' => $vigilantesRequired,
                        'vigilantes_list' => array_map(fn($v) => $v['name'], $vigilantes),
                        'vacancy_id' => $jury['vacancy_id'],
                    ]
                ];
            }

            Response::json(['success' => true, 'events' => $events]);
        } catch (\Exception $e) {
            error_log("Erro ao buscar eventos do calendário: " . $e->getMessage());
            Response::json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Determina a classe CSS do evento baseado no estado de alocação
     * 
     * @param int $vigilantesCount Número de vigilantes alocados
     * @param int $vigilantesRequired Número de vigilantes necessários
     * @param bool $hasSupervisor Se tem supervisor
     * @return string Classe CSS do evento
     */
    private function getEventClassName(int $vigilantesCount, int $vigilantesRequired, bool $hasSupervisor): string
    {
        if ($vigilantesCount >= $vigilantesRequired && $hasSupervisor) {
            return 'event-complete';
        }

        if ($vigilantesCount > 0) {
            return $hasSupervisor ? 'event-partial' : 'event-no-supervisor';
        }

        if (!$hasSupervisor) {
            return 'event-no-supervisor';
        }

        return 'event-empty';
    }
}
