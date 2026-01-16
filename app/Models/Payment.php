<?php

namespace App\Models;

class Payment extends BaseModel
{
    protected string $table = 'payments';
    protected array $fillable = [
        'vacancy_id',
        'user_id',
        'nr_vigias',
        'nr_supervisoes',
        'valor_vigias',
        'valor_supervisoes',
        'total',
        'estado',
        'validated_at',
        'validated_by',
    ];

    /**
     * Obter pagamentos por vaga com dados do usuário
     */
    public function getByVacancy(int $vacancyId): array
    {
        $sql = "SELECT p.*, 
                       u.name as nome_completo,
                       u.nuit,
                       u.bank_name as banco,
                       u.nib as numero_conta,
                       u.email
                FROM {$this->table} p
                INNER JOIN users u ON u.id = p.user_id
                WHERE p.vacancy_id = :vacancy_id
                ORDER BY u.name";
        return $this->statement($sql, ['vacancy_id' => $vacancyId]);
    }

    /**
     * Verificar se existe mapa validado para a vaga
     */
    public function hasValidatedPayments(int $vacancyId): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} 
                WHERE vacancy_id = :vacancy_id AND estado IN ('validado', 'pago')";
        $result = $this->statement($sql, ['vacancy_id' => $vacancyId]);
        return ($result[0]['count'] ?? 0) > 0;
    }

    /**
     * Calcular pagamentos baseado nas participações em júris
     * Retorna array para pré-visualização (não persiste)
     * 
     * Nota: Supervisões são contadas por BLOCO (data+hora+local), não por sala individual
     */
    public function calculateForVacancy(int $vacancyId, array $rates): array
    {
        $sql = "SELECT 
                    u.id as user_id,
                    u.name as nome_completo,
                    u.nuit,
                    u.bank_name as banco,
                    u.nib as numero_conta,
                    u.email,
                    SUM(p.is_vigilante) as nr_vigias,
                    SUM(p.is_supervisor) as nr_supervisoes
                FROM (
                    -- Vigilantes: cada atribuição a um júri conta como 1 vigia
                    SELECT 
                        jv.vigilante_id as user_id,
                        1 as is_vigilante,
                        0 as is_supervisor
                    FROM jury_vigilantes jv
                    INNER JOIN juries j ON j.id = jv.jury_id
                    WHERE j.vacancy_id = :vacancy_id_1
                    
                    UNION ALL
                    
                    -- Supervisores: contagem POR BLOCO (data+hora+local), não por sala individual
                    -- Um supervisor numa data/hora/local supervisiona todas as salas desse bloco como 1 supervisão
                    SELECT 
                        supervisor_id as user_id,
                        0 as is_vigilante,
                        1 as is_supervisor
                    FROM (
                        SELECT DISTINCT 
                            j.supervisor_id,
                            j.exam_date,
                            j.start_time,
                            j.location
                        FROM juries j
                        WHERE j.vacancy_id = :vacancy_id_2 
                          AND j.supervisor_id IS NOT NULL
                    ) AS unique_blocks
                ) as p
                INNER JOIN users u ON u.id = p.user_id
                WHERE u.role NOT IN ('membro', 'coordenador')
                GROUP BY u.id, u.name, u.nuit, u.bank_name, u.nib, u.email
                ORDER BY u.name";

        $results = $this->statement($sql, [
            'vacancy_id_1' => $vacancyId,
            'vacancy_id_2' => $vacancyId
        ]);

        // Aplicar taxas
        $valorVigia = (float) ($rates['valor_por_vigia'] ?? 0);
        $valorSupervisao = (float) ($rates['valor_por_supervisao'] ?? 0);

        foreach ($results as &$row) {
            $row['valor_vigias'] = $row['nr_vigias'] * $valorVigia;
            $row['valor_supervisoes'] = $row['nr_supervisoes'] * $valorSupervisao;
            $row['total'] = $row['valor_vigias'] + $row['valor_supervisoes'];
            $row['dados_incompletos'] = empty($row['nuit']) || empty($row['banco']) || empty($row['numero_conta']);
        }

        return $results;
    }

    /**
     * Gerar e persistir mapa de pagamentos
     */
    public function generateForVacancy(int $vacancyId, array $rates): int
    {
        // Limpar pagamentos anteriores não validados
        $this->db->prepare("DELETE FROM {$this->table} WHERE vacancy_id = ? AND estado = 'previsto'")
            ->execute([$vacancyId]);

        // Calcular novos pagamentos
        $payments = $this->calculateForVacancy($vacancyId, $rates);
        error_log("DEBUG: Payment::generateForVacancy - Calculated " . count($payments) . " payments for vacancy $vacancyId");

        $count = 0;
        foreach ($payments as $payment) {
            $created = $this->create([
                'vacancy_id' => $vacancyId,
                'user_id' => $payment['user_id'],
                'nr_vigias' => $payment['nr_vigias'],
                'nr_supervisoes' => $payment['nr_supervisoes'],
                'valor_vigias' => $payment['valor_vigias'],
                'valor_supervisoes' => $payment['valor_supervisoes'],
                'total' => $payment['total'],
                'estado' => 'previsto',
            ]);

            if ($created) {
                $count++;
            } else {
                error_log("DEBUG: Payment::generateForVacancy - Failed to create payment for user " . $payment['user_id']);
            }
        }

        error_log("DEBUG: Payment::generateForVacancy - Total created: $count");

        return $count;
    }

    /**
     * Validar (congelar) pagamentos de uma vaga
     */
    public function validatePayments(int $vacancyId, int $validatedBy): bool
    {
        $sql = "UPDATE {$this->table} 
                SET estado = 'validado', 
                    validated_at = NOW(), 
                    validated_by = :validated_by
                WHERE vacancy_id = :vacancy_id AND estado = 'previsto'";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            'vacancy_id' => $vacancyId,
            'validated_by' => $validatedBy,
        ]);
    }

    /**
     * Estatísticas de pagamentos por vaga
     */
    public function getStats(int $vacancyId): array
    {
        $sql = "SELECT 
                    COUNT(DISTINCT p.user_id) AS total_beneficiarios,
                    SUM(p.nr_vigias) AS total_vigias,
                    SUM(p.nr_supervisoes) AS total_supervisoes,
                    SUM(p.total) AS valor_total,
                    p.estado,
                    SUM(CASE WHEN u.nuit IS NULL OR u.bank_name IS NULL OR u.nib IS NULL THEN 1 ELSE 0 END) AS dados_incompletos
                FROM {$this->table} p
                INNER JOIN users u ON u.id = p.user_id
                WHERE p.vacancy_id = :vacancy_id
                GROUP BY p.estado";

        $result = $this->statement($sql, ['vacancy_id' => $vacancyId]);
        return $result[0] ?? [
            'total_beneficiarios' => 0,
            'total_vigias' => 0,
            'total_supervisoes' => 0,
            'valor_total' => 0,
            'estado' => null,
            'dados_incompletos' => 0,
        ];
    }
}
