<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Testes Unitários para Services
 * 
 * Valida a existência e estrutura dos Services
 */
class ServicesTest extends TestCase
{
    /**
     * @test
     * Verifica que todos os Services principais existem
     */
    public function testCoreServicesExist(): void
    {
        $services = [
            \App\Services\AllocationService::class,
            \App\Services\SmartAllocationService::class,
            \App\Services\StatsCacheService::class,
            \App\Services\ActivityLogger::class,
            \App\Services\MailService::class,
        ];

        foreach ($services as $service) {
            $this->assertTrue(
                class_exists($service),
                "Service {$service} should exist"
            );
        }
    }

    /**
     * @test
     * Verifica que AllocationService tem os métodos necessários
     */
    public function testAllocationServiceMethods(): void
    {
        $methods = [
            'canAssignVigilante',
            'canAssignSupervisor',
            'assignVigilante',
            'unassignVigilante',
            'autoAllocateJury',
            'getAllocationStats',
            'getEligibleVigilantes',
            'getEligibleSupervisors',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Services\AllocationService::class, $method),
                "Method {$method} should exist in AllocationService"
            );
        }
    }

    /**
     * @test
     * Verifica que SmartAllocationService tem os métodos necessários
     */
    public function testSmartAllocationServiceMethods(): void
    {
        $methods = [
            'autoAllocateVacancy',
            'clearVacancyAllocations',
            'getVacancyAllocationStats',
            'getApprovedCandidates',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Services\SmartAllocationService::class, $method),
                "Method {$method} should exist in SmartAllocationService"
            );
        }
    }

    /**
     * @test
     * Verifica que ActivityLogger tem os métodos necessários
     */
    public function testActivityLoggerMethods(): void
    {
        $methods = [
            'log',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Services\ActivityLogger::class, $method),
                "Method {$method} should exist in ActivityLogger"
            );
        }
    }

    /**
     * @test
     * Verifica que MailService tem os métodos necessários
     */
    public function testMailServiceMethods(): void
    {
        $methods = [
            'send',
            'sendPasswordReset',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Services\MailService::class, $method),
                "Method {$method} should exist in MailService"
            );
        }
    }
}
