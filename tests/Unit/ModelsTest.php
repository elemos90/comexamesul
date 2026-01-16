<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;

/**
 * Testes Unitários para Models
 * 
 * Valida a existência e estrutura dos Models principais
 */
class ModelsTest extends TestCase
{
    /**
     * @test
     * Verifica que todos os Models principais existem
     */
    public function testCoreModelsExist(): void
    {
        $models = [
            \App\Models\User::class,
            \App\Models\Jury::class,
            \App\Models\JuryVigilante::class,
            \App\Models\ExamVacancy::class,
            \App\Models\ExamLocation::class,
            \App\Models\ExamRoom::class,
            \App\Models\Discipline::class,
            \App\Models\Application::class,
            \App\Models\ExamReport::class,
            \App\Models\PaymentRate::class,
        ];

        foreach ($models as $model) {
            $this->assertTrue(
                class_exists($model),
                "Model {$model} should exist"
            );
        }
    }

    /**
     * @test
     * Verifica que User Model tem os métodos necessários
     */
    public function testUserModelMethods(): void
    {
        $methods = [
            'find',
            'findByEmail',
            'create',
            'update',
            'availableVigilantes',
            'supervisors',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Models\User::class, $method),
                "Method {$method} should exist in User model"
            );
        }
    }

    /**
     * @test
     * Verifica que Jury Model tem os métodos necessários
     */
    public function testJuryModelMethods(): void
    {
        $methods = [
            'find',
            'create',
            'update',
            'delete',
            'all',
            'withAllocations',
            'forVacancy',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Models\Jury::class, $method),
                "Method {$method} should exist in Jury model"
            );
        }
    }

    /**
     * @test
     * Verifica que ExamVacancy Model tem os métodos necessários
     */
    public function testExamVacancyModelMethods(): void
    {
        $methods = [
            'find',
            'all',
            'openVacancies',
            'closeExpired',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Models\ExamVacancy::class, $method),
                "Method {$method} should exist in ExamVacancy model"
            );
        }
    }

    /**
     * @test
     * Verifica que Application Model tem os métodos necessários
     */
    public function testApplicationModelMethods(): void
    {
        $methods = [
            'find',
            'forVacancy',
            'forUser',
            'approve',
            'reject',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(
                method_exists(\App\Models\Application::class, $method),
                "Method {$method} should exist in Application model"
            );
        }
    }

    /**
     * @test
     * Verifica que todos os Models herdam de Model base
     */
    public function testModelsExtendBaseModel(): void
    {
        $models = [
            \App\Models\User::class,
            \App\Models\Jury::class,
            \App\Models\ExamVacancy::class,
            \App\Models\Application::class,
        ];

        foreach ($models as $model) {
            $reflection = new \ReflectionClass($model);
            $parent = $reflection->getParentClass();

            $this->assertNotFalse($parent, "{$model} should have a parent class");
            $this->assertEquals(
                'App\Models\Model',
                $parent->getName(),
                "{$model} should extend App\Models\Model"
            );
        }
    }
}
