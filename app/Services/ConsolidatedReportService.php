<?php

namespace App\Services;

use PDO;

/**
 * Serviço para geração de Relatórios Consolidados de Exames
 * 
 * Responsabilidades:
 * - Agregar dados de todos os relatórios de exame
 * - Calcular estatísticas por género, disciplina, local
 * - Fornecer dados para gráficos
 * - Listar ocorrências (fraudes, observações)
 */
class ConsolidatedReportService
{
    private PDO $db;

    public function __construct()
    {
        $this->db = database();
    }

    /**
     * Obter dados consolidados com filtros aplicados
     * 
     * @param array $filters Filtros: vacancy_id, year, location, discipline, date_from, date_to
     * @return array Dados consolidados para todas as secções
     */
    public function getConsolidatedData(array $filters = []): array
    {
        return [
            'identification' => $this->getIdentification($filters),
            'summary' => $this->getSummaryCards($filters),
            'statistics' => $this->getStatsByGender($filters),
            'by_discipline' => $this->getStatsByDiscipline($filters),
            'occurrences' => $this->getOccurrences($filters),
            'filters_applied' => $filters,
            'generated_at' => date('Y-m-d H:i:s'),
        ];
    }

    /**
     * SECÇÃO 1: Identificação institucional
     */
    public function getIdentification(array $filters = []): array
    {
        $vacancyInfo = null;

        if (!empty($filters['vacancy_id'])) {
            $stmt = $this->db->prepare("SELECT * FROM exam_vacancies WHERE id = :id");
            $stmt->execute(['id' => $filters['vacancy_id']]);
            $vacancyInfo = $stmt->fetch(PDO::FETCH_ASSOC);
        }

        // Buscar coordenador
        $coordenador = $this->db->query(
            "SELECT name FROM users WHERE role = 'coordenador' LIMIT 1"
        )->fetch(PDO::FETCH_ASSOC);

        return [
            'instituicao' => 'Universidade Licungo',
            'comissao' => 'Comissão de Exames de Admissão',
            'ano_processo' => $vacancyInfo['title'] ?? ($filters['year'] ?? date('Y')),
            'coordenador' => $coordenador['name'] ?? 'N/A',
            'data_geracao' => date('d/m/Y H:i'),
            'vacancy' => $vacancyInfo,
        ];
    }

    /**
     * SECÇÃO 2: Cards de resumo (Visão Geral)
     */
    public function getSummaryCards(array $filters = []): array
    {
        $whereClause = $this->buildWhereClause($filters);
        $params = $this->buildParams($filters);

        // Total de exames (disciplinas distintas)
        $sql = "SELECT COUNT(DISTINCT j.subject) as total_exames,
                       COUNT(DISTINCT j.id) as total_juris,
                       SUM(j.candidates_quota) as total_esperados
                FROM juries j
                {$whereClause}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $juryStats = $stmt->fetch(PDO::FETCH_ASSOC);

        // Estatísticas dos relatórios
        $sql = "SELECT 
                    SUM(er.present_m + er.present_f) as total_presentes,
                    SUM(er.absent_m + er.absent_f) as total_ausentes,
                    SUM(COALESCE(er.fraudes_m, 0) + COALESCE(er.fraudes_f, 0)) as total_fraudes
                FROM exam_reports er
                INNER JOIN juries j ON j.id = er.jury_id
                {$whereClause}";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $reportStats = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'total_exames' => (int) ($juryStats['total_exames'] ?? 0),
            'total_juris' => (int) ($juryStats['total_juris'] ?? 0),
            'total_esperados' => (int) ($juryStats['total_esperados'] ?? 0),
            'total_presentes' => (int) ($reportStats['total_presentes'] ?? 0),
            'total_ausentes' => (int) ($reportStats['total_ausentes'] ?? 0),
            'total_fraudes' => (int) ($reportStats['total_fraudes'] ?? 0),
        ];
    }

    /**
     * SECÇÃO 3: Estatísticas consolidadas por género
     */
    public function getStatsByGender(array $filters = []): array
    {
        $whereClause = $this->buildWhereClause($filters);
        $params = $this->buildParams($filters);

        $sql = "SELECT 
                    SUM(er.present_m) as presentes_m,
                    SUM(er.present_f) as presentes_f,
                    SUM(er.present_m + er.present_f) as presentes_total,
                    SUM(er.absent_m) as ausentes_m,
                    SUM(er.absent_f) as ausentes_f,
                    SUM(er.absent_m + er.absent_f) as ausentes_total,
                    SUM(COALESCE(er.fraudes_m, 0)) as fraudes_m,
                    SUM(COALESCE(er.fraudes_f, 0)) as fraudes_f,
                    SUM(COALESCE(er.fraudes_m, 0) + COALESCE(er.fraudes_f, 0)) as fraudes_total
                FROM exam_reports er
                INNER JOIN juries j ON j.id = er.jury_id
                {$whereClause}";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        return [
            'presentes' => [
                'masculino' => (int) ($stats['presentes_m'] ?? 0),
                'feminino' => (int) ($stats['presentes_f'] ?? 0),
                'total' => (int) ($stats['presentes_total'] ?? 0),
            ],
            'ausentes' => [
                'masculino' => (int) ($stats['ausentes_m'] ?? 0),
                'feminino' => (int) ($stats['ausentes_f'] ?? 0),
                'total' => (int) ($stats['ausentes_total'] ?? 0),
            ],
            'fraudes' => [
                'masculino' => (int) ($stats['fraudes_m'] ?? 0),
                'feminino' => (int) ($stats['fraudes_f'] ?? 0),
                'total' => (int) ($stats['fraudes_total'] ?? 0),
            ],
        ];
    }

    /**
     * SECÇÃO 5: Estatísticas por disciplina
     */
    public function getStatsByDiscipline(array $filters = []): array
    {
        $whereClause = $this->buildWhereClause($filters);
        $params = $this->buildParams($filters);

        $sql = "SELECT 
                    j.subject as disciplina,
                    COUNT(DISTINCT j.id) as total_salas,
                    SUM(er.present_m + er.present_f) as presentes,
                    SUM(er.absent_m + er.absent_f) as ausentes,
                    SUM(COALESCE(er.fraudes_m, 0) + COALESCE(er.fraudes_f, 0)) as fraudes,
                    SUM(j.candidates_quota) as esperados
                FROM juries j
                LEFT JOIN exam_reports er ON er.jury_id = j.id
                {$whereClause}
                GROUP BY j.subject
                ORDER BY j.subject";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            return [
                'disciplina' => $row['disciplina'],
                'salas' => (int) $row['total_salas'],
                'esperados' => (int) ($row['esperados'] ?? 0),
                'presentes' => (int) ($row['presentes'] ?? 0),
                'ausentes' => (int) ($row['ausentes'] ?? 0),
                'fraudes' => (int) ($row['fraudes'] ?? 0),
                'taxa_presenca' => $row['esperados'] > 0
                    ? round(($row['presentes'] / $row['esperados']) * 100, 1)
                    : 0,
            ];
        }, $results);
    }

    /**
     * SECÇÃO 6: Ocorrências (fraudes e observações)
     */
    public function getOccurrences(array $filters = []): array
    {
        $whereClause = $this->buildWhereClause($filters);
        $params = $this->buildParams($filters);

        // Fraudes
        $sql = "SELECT 
                    j.subject as disciplina,
                    j.exam_date,
                    j.room as sala,
                    j.location as local,
                    COALESCE(er.fraudes_m, 0) as fraudes_m,
                    COALESCE(er.fraudes_f, 0) as fraudes_f,
                    er.occurrences as observacoes,
                    u.name as vigilante
                FROM exam_reports er
                INNER JOIN juries j ON j.id = er.jury_id
                LEFT JOIN users u ON u.id = er.supervisor_id
                {$whereClause}
                AND (COALESCE(er.fraudes_m, 0) > 0 OR COALESCE(er.fraudes_f, 0) > 0 OR er.occurrences IS NOT NULL)
                ORDER BY j.exam_date DESC, j.subject";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $occurrences = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return array_map(function ($row) {
            return [
                'disciplina' => $row['disciplina'],
                'data' => date('d/m/Y', strtotime($row['exam_date'])),
                'local' => $row['local'],
                'sala' => $row['sala'],
                'fraudes_m' => (int) $row['fraudes_m'],
                'fraudes_f' => (int) $row['fraudes_f'],
                'total_fraudes' => (int) $row['fraudes_m'] + (int) $row['fraudes_f'],
                'observacoes' => $row['observacoes'],
                'vigilante' => $row['vigilante'],
            ];
        }, $occurrences);
    }

    /**
     * Dados para gráficos
     */
    public function getChartData(array $filters = []): array
    {
        $stats = $this->getStatsByGender($filters);
        $byDiscipline = $this->getStatsByDiscipline($filters);

        return [
            // Gráfico de barras: Presentes vs Ausentes vs Fraudes
            'presence_chart' => [
                'labels' => ['Presentes', 'Ausentes', 'Fraudes'],
                'data' => [
                    $stats['presentes']['total'],
                    $stats['ausentes']['total'],
                    $stats['fraudes']['total'],
                ],
                'colors' => ['#10b981', '#f59e0b', '#ef4444'],
            ],
            // Gráfico por género
            'gender_chart' => [
                'labels' => ['Masculino', 'Feminino'],
                'datasets' => [
                    [
                        'label' => 'Presentes',
                        'data' => [$stats['presentes']['masculino'], $stats['presentes']['feminino']],
                        'backgroundColor' => '#10b981',
                    ],
                    [
                        'label' => 'Ausentes',
                        'data' => [$stats['ausentes']['masculino'], $stats['ausentes']['feminino']],
                        'backgroundColor' => '#f59e0b',
                    ],
                ],
            ],
            // Gráfico por disciplina (taxa de presença)
            'discipline_chart' => [
                'labels' => array_column($byDiscipline, 'disciplina'),
                'data' => array_column($byDiscipline, 'taxa_presenca'),
                'colors' => $this->generateColorPalette(count($byDiscipline)),
            ],
        ];
    }

    /**
     * Obter lista de vagas disponíveis para filtro
     */
    public function getVacanciesForFilter(): array
    {
        $stmt = $this->db->query(
            "SELECT id, title, YEAR(created_at) as year 
             FROM exam_vacancies 
             ORDER BY created_at DESC"
        );
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Obter lista de locais disponíveis para filtro
     */
    public function getLocationsForFilter(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT location FROM juries WHERE location IS NOT NULL ORDER BY location"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Obter lista de disciplinas disponíveis para filtro
     */
    public function getDisciplinesForFilter(): array
    {
        $stmt = $this->db->query(
            "SELECT DISTINCT subject FROM juries WHERE subject IS NOT NULL ORDER BY subject"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Construir cláusula WHERE com filtros
     */
    private function buildWhereClause(array $filters): string
    {
        $conditions = ['1=1'];

        if (!empty($filters['vacancy_id'])) {
            $conditions[] = 'j.vacancy_id = :vacancy_id';
        }
        if (!empty($filters['location'])) {
            $conditions[] = 'j.location = :location';
        }
        if (!empty($filters['discipline'])) {
            $conditions[] = 'j.subject = :discipline';
        }
        if (!empty($filters['date_from'])) {
            $conditions[] = 'j.exam_date >= :date_from';
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = 'j.exam_date <= :date_to';
        }
        if (!empty($filters['year'])) {
            $conditions[] = 'YEAR(j.exam_date) = :year';
        }

        return 'WHERE ' . implode(' AND ', $conditions);
    }

    /**
     * Construir parâmetros para query
     */
    private function buildParams(array $filters): array
    {
        $params = [];

        if (!empty($filters['vacancy_id'])) {
            $params['vacancy_id'] = $filters['vacancy_id'];
        }
        if (!empty($filters['location'])) {
            $params['location'] = $filters['location'];
        }
        if (!empty($filters['discipline'])) {
            $params['discipline'] = $filters['discipline'];
        }
        if (!empty($filters['date_from'])) {
            $params['date_from'] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $params['date_to'] = $filters['date_to'];
        }
        if (!empty($filters['year'])) {
            $params['year'] = $filters['year'];
        }

        return $params;
    }

    /**
     * Gerar paleta de cores para gráficos
     */
    private function generateColorPalette(int $count): array
    {
        $baseColors = [
            '#3b82f6',
            '#10b981',
            '#f59e0b',
            '#ef4444',
            '#8b5cf6',
            '#ec4899',
            '#06b6d4',
            '#84cc16',
            '#f97316',
            '#6366f1'
        ];

        $colors = [];
        for ($i = 0; $i < $count; $i++) {
            $colors[] = $baseColors[$i % count($baseColors)];
        }
        return $colors;
    }
}
