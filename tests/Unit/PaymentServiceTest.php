<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Testes Unitários para PaymentService
 * 
 * Valida a existência e estrutura do PaymentService
 */
class PaymentServiceTest extends TestCase
{
    /**
     * @test
     * Verifica que PaymentService existe
     */
    public function testPaymentServiceExists(): void
    {
        $this->assertTrue(
            class_exists(\App\Services\PaymentService::class),
            "PaymentService should exist"
        );
    }

    /**
     * @test
     * Verifica que PaymentService tem todos os métodos públicos necessários
     */
    public function testPaymentServiceHasRequiredMethods(): void
    {
        $methods = [
            'previewPayments',
            'generatePaymentMap',
            'validatePayments',
            'getPaymentStats',
            'exportPayments',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Services\PaymentService::class, $method),
                "Method {$method} should exist in PaymentService"
            );
        }
    }

    /**
     * @test
     * Verifica que previewPayments aceita vacancyId como parâmetro
     */
    public function testPreviewPaymentsSignature(): void
    {
        $reflection = new \ReflectionMethod(\App\Services\PaymentService::class, 'previewPayments');

        $this->assertEquals(1, $reflection->getNumberOfRequiredParameters());
        $this->assertEquals('vacancyId', $reflection->getParameters()[0]->getName());
        $this->assertEquals('int', $reflection->getParameters()[0]->getType()->getName());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * @test
     * Verifica que generatePaymentMap aceita vacancyId como parâmetro
     */
    public function testGeneratePaymentMapSignature(): void
    {
        $reflection = new \ReflectionMethod(\App\Services\PaymentService::class, 'generatePaymentMap');

        $this->assertEquals(1, $reflection->getNumberOfRequiredParameters());
        $this->assertEquals('vacancyId', $reflection->getParameters()[0]->getName());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * @test
     * Verifica que validatePayments aceita vacancyId e userId como parâmetros
     */
    public function testValidatePaymentsSignature(): void
    {
        $reflection = new \ReflectionMethod(\App\Services\PaymentService::class, 'validatePayments');

        $this->assertEquals(2, $reflection->getNumberOfRequiredParameters());
        $this->assertEquals('vacancyId', $reflection->getParameters()[0]->getName());
        $this->assertEquals('userId', $reflection->getParameters()[1]->getName());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * @test
     * Verifica que getPaymentStats aceita vacancyId como parâmetro
     */
    public function testGetPaymentStatsSignature(): void
    {
        $reflection = new \ReflectionMethod(\App\Services\PaymentService::class, 'getPaymentStats');

        $this->assertEquals(1, $reflection->getNumberOfRequiredParameters());
        $this->assertEquals('vacancyId', $reflection->getParameters()[0]->getName());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * @test
     * Verifica que exportPayments aceita vacancyId como parâmetro
     */
    public function testExportPaymentsSignature(): void
    {
        $reflection = new \ReflectionMethod(\App\Services\PaymentService::class, 'exportPayments');

        $this->assertEquals(1, $reflection->getNumberOfRequiredParameters());
        $this->assertEquals('vacancyId', $reflection->getParameters()[0]->getName());
        $this->assertEquals('array', $reflection->getReturnType()->getName());
    }

    /**
     * @test
     * Verifica que o métido calculateTotals existe (privado)
     */
    public function testCalculateTotalsMethodExists(): void
    {
        $reflection = new \ReflectionClass(\App\Services\PaymentService::class);

        $this->assertTrue(
            $reflection->hasMethod('calculateTotals'),
            "Private method calculateTotals should exist"
        );

        $method = $reflection->getMethod('calculateTotals');
        $this->assertTrue($method->isPrivate(), "calculateTotals should be private");
    }
}
