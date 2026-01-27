<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\AllocationService;
use App\Models\User;
use App\Models\Jury;
use App\Models\JuryVigilante;

class AllocationServiceTest extends TestCase
{
    public function test_can_instantiate_service()
    {
        $service = new AllocationService();
        $this->assertInstanceOf(AllocationService::class, $service);
    }

    public function test_can_assign_vigilante_valid()
    {
        // 1. Setup Data
        $userModel = new User();
        $vigilanteId = $userModel->create([
            'name' => 'Vigilante Test',
            'email' => 'vig@test.com',
            'role' => 'vigilante',
            'available_for_vigilance' => 1,
            'supervisor_eligible' => 0
        ]);

        $juryModel = new Jury();
        $juryId = $juryModel->create([
            'subject' => 'Math 101',
            'exam_date' => '2026-05-20',
            'start_time' => '08:00:00',
            'end_time' => '10:00:00',
            'vigilantes_capacity' => 2,
            'candidates_quota' => 50
        ]);

        // 2. Test canAssignVigilante
        $service = new AllocationService();
        $result = $service->canAssignVigilante($vigilanteId, $juryId);

        $this->assertTrue($result['can_assign'], 'Validation failed: ' . ($result['reason'] ?? 'None'));
        $this->assertEquals('success', $result['severity']);
    }

    /*
    public function test_cannot_assign_vigilante_if_capacity_full()
    {
        // 1. Setup Data
        $userModel = new User();
        $v1 = $userModel->create(['name' => 'V1', 'role' => 'vigilante', 'available_for_vigilance' => 1]);
        $v2 = $userModel->create(['name' => 'V2', 'role' => 'vigilante', 'available_for_vigilance' => 1]);
        $v3 = $userModel->create(['name' => 'V3', 'role' => 'vigilante', 'available_for_vigilance' => 1]);

        $juryModel = new Jury();
        $juryId = $juryModel->create([
            'subject' => 'Physics',
            'vigilantes_capacity' => 2,
            'exam_date' => '2026-05-21',
            'start_time' => '14:00',
            'end_time' => '16:00'
        ]);

        // 2. Fill capacity
        $jvModel = new JuryVigilante();
        $jvModel->create(['jury_id' => $juryId, 'vigilante_id' => $v1]);
        $jvModel->create(['jury_id' => $juryId, 'vigilante_id' => $v2]);

        // 3. Test assign 3rd
        $service = new AllocationService();
        $result = $service->canAssignVigilante($v3, $juryId);

        $this->assertFalse($result['can_assign']);
        $this->assertStringContainsString('Capacidade', $result['reason']);
    }

    public function test_auto_allocate_jury()
    {
        // 1. Setup Vigilantes
        $userModel = new User();
        $userModel->create(['name' => 'A1', 'role' => 'vigilante', 'available_for_vigilance' => 1]);
        $userModel->create(['name' => 'A2', 'role' => 'vigilante', 'available_for_vigilance' => 1]);
        $userModel->create(['name' => 'A3', 'role' => 'vigilante', 'available_for_vigilance' => 1]);

        // 2. Setup Jury
        $juryModel = new Jury();
        $juryId = $juryModel->create([
            'subject' => 'Chemistry',
            'vigilantes_capacity' => 2,
            'exam_date' => '2026-06-01',
            'start_time' => '08:00',
            'end_time' => '10:00'
        ]);

        // 3. Run Auto Allocate
        $service = new AllocationService();
        $result = $service->autoAllocateJury($juryId, 1);

        $this->assertTrue($result['success'], 'Auto allocation failed: ' . ($result['message'] ?? ''));
        $this->assertEquals(2, $result['allocated']);

        // 4. Verify in DB
        $db = \App\Database\Connection::getInstance();
        $stmt = $db->prepare("SELECT COUNT(*) FROM jury_vigilantes WHERE jury_id = ?");
        $stmt->execute([$juryId]);
        $this->assertEquals(2, $stmt->fetchColumn());
    }
    */
}
