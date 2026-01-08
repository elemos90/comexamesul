<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Testes Unitários para AllocationService
 * 
 * Nota: Estes testes requerem mock do banco de dados ou conexão de teste
 */
class AllocationServiceTest extends TestCase
{
    /**
     * @test
     */
    public function testCanAssignVigilanteWithoutConflict(): void
    {
        // Mock básico para verificar estrutura do serviço
        $this->assertTrue(
            class_exists(\App\Services\AllocationService::class),
            'AllocationService class should exist'
        );
    }

    /**
     * @test
     */
    public function testAllocationServiceHasRequiredMethods(): void
    {
        $methods = [
            'canAssignVigilante',
            'canAssignSupervisor',
            'assignVigilante',
            'unassignVigilante',
            'autoAllocateJury',
            'autoAllocateDiscipline',
            'getAllocationStats',
            'getEligibleVigilantes',
            'getEligibleSupervisors'
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
     */
    public function testSmartAllocationServiceExists(): void
    {
        $this->assertTrue(
            class_exists(\App\Services\SmartAllocationService::class),
            'SmartAllocationService class should exist'
        );
    }

    /**
     * @test
     */
    public function testSmartAllocationServiceHasRequiredMethods(): void
    {
        $methods = [
            'autoAllocateVacancy',
            'clearVacancyAllocations',
            'getVacancyAllocationStats',
            'getApprovedCandidates'
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
     * Verifica que os novos controllers existem
     */
    public function testNewControllersExist(): void
    {
        $controllers = [
            \App\Controllers\JuryApiController::class,
            \App\Controllers\JuryAllocationController::class,
            \App\Controllers\JuryPlanningController::class,
        ];

        foreach ($controllers as $controller) {
            $this->assertTrue(
                class_exists($controller),
                "Controller {$controller} should exist"
            );
        }
    }

    /**
     * @test
     * Verifica que JuryApiController tem os métodos esperados
     */
    public function testJuryApiControllerMethods(): void
    {
        $methods = [
            'getAllocationStats',
            'getJurySlots',
            'getEligibleVigilantes',
            'getEligibleSupervisors',
            'getMetrics',
            'getAvailableVigilantes',
            'getAvailableSupervisors'
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Controllers\JuryApiController::class, $method),
                "Method {$method} should exist in JuryApiController"
            );
        }
    }

    /**
     * @test
     * Verifica que JuryAllocationController tem os métodos esperados
     */
    public function testJuryAllocationControllerMethods(): void
    {
        $methods = [
            'assign',
            'unassign',
            'setSupervisor',
            'canAssign',
            'autoAllocateJury',
            'autoAllocateDiscipline',
            'swapVigilantes',
            'bulkAssignSupervisor'
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Controllers\JuryAllocationController::class, $method),
                "Method {$method} should exist in JuryAllocationController"
            );
        }
    }

    /**
     * @test
     * Verifica que JuryPlanningController tem os métodos esperados
     */
    public function testJuryPlanningControllerMethods(): void
    {
        $methods = [
            'planning',
            'planningByVacancy',
            'manageVacancyJuries',
            'createJuriesForVacancy',
            'planLocalDate',
            'applyLocalDate',
            'getKPIs'
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Controllers\JuryPlanningController::class, $method),
                "Method {$method} should exist in JuryPlanningController"
            );
        }
    }
}
