<?php

namespace App\Controllers;

use App\Http\Request;
use App\Models\ExamVacancy;
use App\Models\Jury;
use App\Models\JuryVigilante;
use App\Models\User;
use App\Services\StatsCacheService;
use App\Utils\Auth;

class DashboardController extends Controller
{
    private StatsCacheService $cache;
    
    public function __construct()
    {
        $this->cache = new StatsCacheService();
    }
    
    public function index(Request $request): string
    {
        $user = Auth::user();
        
        // If user session exists but user was deleted from database, logout
        if ($user === null) {
            Auth::logout();
            header('Location: /login');
            exit;
        }
        
        $vacancies = new ExamVacancy();
        $juries = new Jury();
        $juryVigilantes = new JuryVigilante();
        $userModel = new User();

        // Fechar vagas expiradas automaticamente (verificação em tempo real)
        $vacancies->closeExpired();

        $data = [
            'user' => $user,
            'upcomingJuries' => [],
            'isAvailable' => (bool) ($user['available_for_vigilance'] ?? false),
            'availableVigilantes' => null,
        ];

        if ($user['role'] === 'vigilante') {
            // Cache específico por vigilante
            $cacheKey = 'dashboard_vigilante_' . $user['id'];
            $cachedData = $this->cache->remember($cacheKey, function() use ($juryVigilantes, $juries, $user, $vacancies) {
                return [
                    'openVacancies' => count($vacancies->openVacancies()),
                    'upcomingJuries' => $this->fetchUpcomingForVigilante($juryVigilantes, $juries, (int) $user['id'])
                ];
            }, 180); // 3 minutos
            
            $data['openVacancies'] = $cachedData['openVacancies'];
            $data['upcomingJuries'] = $cachedData['upcomingJuries'];
        } else {
            // Cache para coordenadores/membros
            $cachedStats = $this->cache->remember('dashboard_stats', function() use ($userModel, $juries, $vacancies) {
                $allJuries = $juries->withAllocations();
                
                // Filtrar apenas júris futuros
                $futureJuries = array_filter($allJuries, function ($jury) {
                    $dateTime = $jury['exam_date'] . ' ' . $jury['start_time'];
                    return strtotime($dateTime) >= time();
                });
                
                return [
                    'openVacancies' => count($vacancies->openVacancies()),
                    'availableVigilantes' => count($userModel->availableVigilantes()),
                    'upcomingJuries' => $this->filterNextDayOnly($futureJuries)
                ];
            }, 300); // 5 minutos
            
            $data['openVacancies'] = $cachedStats['openVacancies'];
            $data['availableVigilantes'] = $cachedStats['availableVigilantes'];
            $data['upcomingJuries'] = $cachedStats['upcomingJuries'];
        }

        return $this->view('dashboard/index', $data);
    }

    private function fetchUpcomingForVigilante(JuryVigilante $juryVigilantes, Jury $juries, int $userId): array
    {
        $sql = "SELECT j.* FROM jury_vigilantes jv INNER JOIN juries j ON j.id = jv.jury_id WHERE jv.vigilante_id = :user AND j.exam_date >= CURDATE() ORDER BY j.exam_date, j.start_time";
        $stmt = $juryVigilantes->statement($sql, ['user' => $userId]);
        
        // Filtrar apenas júris futuros
        $upcoming = array_filter($stmt, function ($jury) {
            $dateTime = $jury['exam_date'] . ' ' . $jury['start_time'];
            return strtotime($dateTime) >= time();
        });
        
        // Retornar apenas júris do próximo dia
        return $this->filterNextDayOnly($upcoming);
    }
    
    private function filterNextDayOnly(array $juries): array
    {
        if (empty($juries)) {
            return [];
        }
        
        // Encontrar a data mais próxima
        $firstJury = reset($juries);
        $nextDate = $firstJury['exam_date'];
        
        // Filtrar apenas júris dessa data
        return array_filter($juries, function ($jury) use ($nextDate) {
            return $jury['exam_date'] === $nextDate;
        });
    }
}
