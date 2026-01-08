<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\PaymentRate;
use PDO;

class PaymentService
{
    private PDO $db;
    private Payment $paymentModel;
    private PaymentRate $rateModel;

    public function __construct()
    {
        $this->db = database();
        $this->paymentModel = new Payment();
        $this->rateModel = new PaymentRate();
    }

    /**
     * Gerar pré-visualização de pagamentos (sem persistir)
     */
    public function previewPayments(int $vacancyId): array
    {
        $rates = $this->rateModel->getActiveForVacancy($vacancyId);

        if (!$rates) {
            return [
                'success' => false,
                'error' => 'Não há taxa de pagamento definida para esta vaga.',
                'data' => [],
            ];
        }

        $payments = $this->paymentModel->calculateForVacancy($vacancyId, $rates);

        // Calcular totais
        $totals = $this->calculateTotals($payments);

        return [
            'success' => true,
            'data' => $payments,
            'totals' => $totals,
            'rates' => $rates,
        ];
    }

    /**
     * Gerar e persistir mapa de pagamentos
     */
    public function generatePaymentMap(int $vacancyId): array
    {
        // Verificar se já existe mapa validado
        if ($this->paymentModel->hasValidatedPayments($vacancyId)) {
            return [
                'success' => false,
                'error' => 'Já existe um mapa de pagamentos validado para esta vaga. Não é possível gerar novo mapa.',
            ];
        }

        $rates = $this->rateModel->getActiveForVacancy($vacancyId);

        if (!$rates) {
            return [
                'success' => false,
                'error' => 'Não há taxa de pagamento definida para esta vaga.',
            ];
        }

        $count = $this->paymentModel->generateForVacancy($vacancyId, $rates);

        return [
            'success' => true,
            'message' => "Mapa de pagamentos gerado com sucesso. {$count} registos criados.",
            'count' => $count,
        ];
    }

    /**
     * Validar (congelar) mapa de pagamentos
     */
    public function validatePayments(int $vacancyId, int $userId): array
    {
        // Verificar se existem pagamentos para validar
        $payments = $this->paymentModel->getByVacancy($vacancyId);

        if (empty($payments)) {
            return [
                'success' => false,
                'error' => 'Não há pagamentos para validar. Gere o mapa primeiro.',
            ];
        }

        $previstos = array_filter($payments, fn($p) => $p['estado'] === 'previsto');

        if (empty($previstos)) {
            return [
                'success' => false,
                'error' => 'Todos os pagamentos já foram validados.',
            ];
        }

        $this->paymentModel->validatePayments($vacancyId, $userId);

        return [
            'success' => true,
            'message' => 'Mapa de pagamentos validado com sucesso. Os valores estão agora congelados.',
        ];
    }

    /**
     * Obter estatísticas de pagamentos
     */
    public function getPaymentStats(int $vacancyId): array
    {
        return $this->paymentModel->getStats($vacancyId);
    }

    /**
     * Calcular totais do mapa
     */
    private function calculateTotals(array $payments): array
    {
        $totals = [
            'total_beneficiarios' => count($payments),
            'total_vigias' => 0,
            'total_supervisoes' => 0,
            'valor_total' => 0,
            'dados_incompletos' => 0,
        ];

        foreach ($payments as $p) {
            $totals['total_vigias'] += $p['nr_vigias'];
            $totals['total_supervisoes'] += $p['nr_supervisoes'];
            $totals['valor_total'] += $p['total'];
            if ($p['dados_incompletos']) {
                $totals['dados_incompletos']++;
            }
        }

        return $totals;
    }

    /**
     * Exportar mapa para array (para PDF/Excel/CSV)
     */
    public function exportPayments(int $vacancyId): array
    {
        $payments = $this->paymentModel->getByVacancy($vacancyId);
        $rates = $this->rateModel->getActiveForVacancy($vacancyId);
        $stats = $this->paymentModel->getStats($vacancyId);

        return [
            'payments' => $payments,
            'rates' => $rates,
            'stats' => $stats,
        ];
    }
}
