<?php

namespace App\Models;

class PaymentRate extends BaseModel
{
    protected string $table = 'payment_rates';
    protected array $fillable = [
        'vacancy_id',
        'valor_por_vigia',
        'valor_por_supervisao',
        'moeda',
        'ativo',
    ];

    /**
     * Obter taxa ativa para uma vaga
     */
    public function getActiveForVacancy(int $vacancyId): ?array
    {
        $sql = "SELECT * FROM {$this->table} 
                WHERE vacancy_id = :vacancy_id AND ativo = 1 
                LIMIT 1";
        $result = $this->statement($sql, ['vacancy_id' => $vacancyId]);
        return $result[0] ?? null;
    }

    /**
     * Obter todas as taxas por vaga
     */
    public function getByVacancy(int $vacancyId): array
    {
        $sql = "SELECT pr.*, ev.title as vacancy_title 
                FROM {$this->table} pr
                INNER JOIN exam_vacancies ev ON ev.id = pr.vacancy_id
                WHERE pr.vacancy_id = :vacancy_id 
                ORDER BY pr.created_at DESC";
        return $this->statement($sql, ['vacancy_id' => $vacancyId]);
    }

    /**
     * Definir uma taxa como ativa (desativa outras da mesma vaga)
     */
    public function setActive(int $id): bool
    {
        // Buscar vaga da taxa
        $rate = $this->find($id);
        if (!$rate) {
            return false;
        }

        $vacancyId = $rate['vacancy_id'];

        // Desativar todas as taxas da vaga
        $this->db->prepare("UPDATE {$this->table} SET ativo = 0 WHERE vacancy_id = ?")
            ->execute([$vacancyId]);

        // Ativar a taxa selecionada
        $this->db->prepare("UPDATE {$this->table} SET ativo = 1 WHERE id = ?")
            ->execute([$id]);

        return true;
    }

    /**
     * Criar nova taxa de pagamento
     */
    public function createRate(array $data): int
    {
        // Se marcada como ativa, desativar outras
        if (!empty($data['ativo'])) {
            $this->db->prepare("UPDATE {$this->table} SET ativo = 0 WHERE vacancy_id = ?")
                ->execute([$data['vacancy_id']]);
        }

        return $this->create($data);
    }

    /**
     * Listar todas as taxas com info da vaga
     */
    public function getAllWithVacancy(): array
    {
        $sql = "SELECT pr.*, ev.title as vacancy_title, YEAR(ev.deadline_at) as vacancy_year
                FROM {$this->table} pr
                INNER JOIN exam_vacancies ev ON ev.id = pr.vacancy_id
                ORDER BY ev.deadline_at DESC, ev.title";
        return $this->statement($sql);
    }
}
