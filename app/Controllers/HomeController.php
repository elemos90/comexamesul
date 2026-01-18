<?php

namespace App\Controllers;

use App\Models\ExamVacancy;
use App\Models\Jury;
use App\Utils\Auth;

class HomeController extends Controller
{
    public function index(): string
    {
        // Se usuário estiver logado, redireciona para dashboard (opcional, mas comum)
        // Se quiser que logados também vejam a home, remova este bloco
        if (Auth::check()) {
            header('Location: ' . url('/dashboard'));
            exit;
        }

        $vacancies = (new ExamVacancy())->openVacancies();

        // Buscar júris futuros
        $juries = (new Jury())->statement(
            "SELECT * FROM juries 
             WHERE exam_date >= CURDATE() 
             ORDER BY exam_date, start_time 
             LIMIT 30"
        );

        // Agrupar júris por local e depois por disciplina
        $juriesByLocation = [];
        foreach ($juries as $jury) {
            $location = $jury['location'] ?: 'Sem Local';
            $subject = $jury['subject'];
            $examDate = $jury['exam_date'];

            // Criar chave única para disciplina + data
            $disciplineKey = $subject . '_' . $examDate;

            if (!isset($juriesByLocation[$location])) {
                $juriesByLocation[$location] = [];
            }

            if (!isset($juriesByLocation[$location][$disciplineKey])) {
                $juriesByLocation[$location][$disciplineKey] = [
                    'subject' => $subject,
                    'exam_date' => $examDate,
                    'start_time' => $jury['start_time'],
                    'end_time' => $jury['end_time'],
                    'rooms' => []
                ];
            }

            $juriesByLocation[$location][$disciplineKey]['rooms'][] = [
                'room' => $jury['room'],
                'candidates_quota' => $jury['candidates_quota']
            ];
        }

        // 1. Calcular Dias até o próximo exame
        $daysToNextExam = 0;
        if (!empty($juries)) {
            $nextExamDate = new \DateTime($juries[0]['exam_date']);
            $today = new \DateTime();
            $interval = $today->diff($nextExamDate);
            $daysToNextExam = (int) $interval->format('%r%a');
            if ($daysToNextExam < 0)
                $daysToNextExam = 0;
        }

        // 2. Próximos Eventos (Sidebar - Limit 4)
        $upcomingExams = array_slice($juries, 0, 4);

        // 3. Calendário Completo (Limit 12 for table)
        $calendarExams = array_slice($juries, 0, 12);

        // 4. Atualizações Recentes (Dinâmico)
        $recentUpdates = [];

        // Adicionar vacaturas recentes
        foreach ($vacancies as $vacancy) {
            $createdAt = new \DateTime($vacancy['created_at'] ?? 'now');
            $recentUpdates[] = [
                'date' => $createdAt->format('d M'),
                'text' => "Nova vaga disponível: {$vacancy['title']}",
                'type' => 'vacancy'
            ];
        }

        // Adicionar alertas de exames próximos (ex: amanhã ou daqui a 2 dias)
        foreach ($juries as $jury) {
            $examDate = new \DateTime($jury['exam_date']);
            $diff = $today->diff($examDate)->days;
            if ($diff <= 3 && $diff >= 0) {
                $recentUpdates[] = [
                    'date' => $examDate->format('d M'),
                    'text' => "Exame de {$jury['subject']} aproxima-se ({$jury['location']})",
                    'type' => 'exam'
                ];
            }
        }

        // Fallback se não houver atualizações reais
        if (empty($recentUpdates)) {
            $recentUpdates[] = [
                'date' => date('d M'),
                'text' => 'Portal de Exames atualizado para ' . date('Y'),
                'type' => 'system'
            ];
        }

        return $this->view('home/index', [
            'vacancies' => $vacancies,
            'juriesByLocation' => $juriesByLocation,
            'daysToNextExam' => $daysToNextExam,
            'upcomingExams' => $upcomingExams,
            'calendarExams' => $calendarExams,
            'recentUpdates' => array_slice($recentUpdates, 0, 5)
        ]);
    }

    /**
     * Página inicial pública (se precisar no futuro)
     */
    public function publicHome(): string
    {
        $vacancies = (new ExamVacancy())->openVacancies();

        // Buscar júris futuros
        $juries = (new Jury())->statement(
            "SELECT * FROM juries 
             WHERE exam_date >= CURDATE() 
             ORDER BY exam_date, start_time 
             LIMIT 30"
        );

        // Agrupar júris por local e depois por disciplina
        $juriesByLocation = [];
        foreach ($juries as $jury) {
            $location = $jury['location'] ?: 'Sem Local';
            $subject = $jury['subject'];
            $examDate = $jury['exam_date'];

            // Criar chave única para disciplina + data
            $disciplineKey = $subject . '_' . $examDate;

            if (!isset($juriesByLocation[$location])) {
                $juriesByLocation[$location] = [];
            }

            if (!isset($juriesByLocation[$location][$disciplineKey])) {
                $juriesByLocation[$location][$disciplineKey] = [
                    'subject' => $subject,
                    'exam_date' => $examDate,
                    'start_time' => $jury['start_time'],
                    'end_time' => $jury['end_time'],
                    'rooms' => []
                ];
            }

            $juriesByLocation[$location][$disciplineKey]['rooms'][] = [
                'room' => $jury['room'],
                'candidates_quota' => $jury['candidates_quota']
            ];
        }

        return $this->view('home/index', [
            'vacancies' => $vacancies,
            'juriesByLocation' => $juriesByLocation,
        ]);
    }
}
