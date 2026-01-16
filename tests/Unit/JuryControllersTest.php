<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Testes Unitários para os Novos Controllers de Júri
 * 
 * Valida a existência e estrutura dos controllers refatorados
 */
class JuryControllersTest extends TestCase
{
    /**
     * @test
     * Verifica que JuryCalendarController existe e tem os métodos necessários
     */
    public function testJuryCalendarControllerExists(): void
    {
        $this->assertTrue(
            class_exists(\App\Controllers\JuryCalendarController::class),
            'JuryCalendarController class should exist'
        );
    }

    /**
     * @test
     */
    public function testJuryCalendarControllerMethods(): void
    {
        $methods = [
            'calendar',
            'calendarEvents'
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Controllers\JuryCalendarController::class, $method),
                "Method {$method} should exist in JuryCalendarController"
            );
        }
    }

    /**
     * @test
     * Verifica que JuryWizardController existe e tem os métodos necessários
     */
    public function testJuryWizardControllerExists(): void
    {
        $this->assertTrue(
            class_exists(\App\Controllers\JuryWizardController::class),
            'JuryWizardController class should exist'
        );
    }

    /**
     * @test
     */
    public function testJuryWizardControllerMethods(): void
    {
        $methods = [
            'planningByVacancy',
            'createJuriesForVacancy',
            'validateVacancyPlanning',
            'autoAllocateVacancy',
            'clearVacancyAllocations',
            'getVacancyStats',
            'getEligibleForJury',
            'manageVacancyJuries',
            'getVacancyApprovedCandidates'
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Controllers\JuryWizardController::class, $method),
                "Method {$method} should exist in JuryWizardController"
            );
        }
    }

    /**
     * @test
     * Verifica que JuryBulkController existe e tem os métodos necessários
     */
    public function testJuryBulkControllerExists(): void
    {
        $this->assertTrue(
            class_exists(\App\Controllers\JuryBulkController::class),
            'JuryBulkController class should exist'
        );
    }

    /**
     * @test
     */
    public function testJuryBulkControllerMethods(): void
    {
        $methods = [
            'createBatch',
            'createLocationBatch',
            'updateBatch',
            'createBulk',
            'syncRoomNames',
            'updateDiscipline',
            'bulkAssignSupervisor'
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Controllers\JuryBulkController::class, $method),
                "Method {$method} should exist in JuryBulkController"
            );
        }
    }

    /**
     * @test
     * Verifica que o JuryController original ainda existe
     */
    public function testOriginalJuryControllerExists(): void
    {
        $this->assertTrue(
            class_exists(\App\Controllers\JuryController::class),
            'Original JuryController class should still exist'
        );
    }

    /**
     * @test
     * Verifica que todos os controllers herdam de Controller base
     */
    public function testControllersExtendBaseController(): void
    {
        $controllers = [
            \App\Controllers\JuryCalendarController::class,
            \App\Controllers\JuryWizardController::class,
            \App\Controllers\JuryBulkController::class,
        ];

        foreach ($controllers as $controller) {
            $reflection = new \ReflectionClass($controller);
            $parent = $reflection->getParentClass();

            $this->assertNotFalse($parent, "{$controller} should have a parent class");
            $this->assertEquals(
                'App\Controllers\Controller',
                $parent->getName(),
                "{$controller} should extend App\Controllers\Controller"
            );
        }
    }
}
