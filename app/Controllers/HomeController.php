<?php

namespace App\Controllers;

use App\Models\ExamVacancy;
use App\Models\Jury;
use App\Utils\Auth;

class HomeController extends Controller
{
    public function index(): string
    {
        // Redirecionar usuários autenticados para dashboard, outros para login
        if (Auth::check()) {
            header('Location: ' . url('/dashboard'));
            exit;
        }

        header('Location: ' . url('/login'));
        exit;
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
