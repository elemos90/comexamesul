<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Notification;

class NotificationModelTest extends TestCase
{
    public function test_can_instantiate_notification_model()
    {
        $notification = new Notification();
        $this->assertInstanceOf(Notification::class, $notification);
    }

    public function test_can_create_notification()
    {
        $notificationModel = new Notification();
        $id = $notificationModel->create([
            'type' => 'info',
            'subject' => 'Test Subject',
            'message' => 'Test Message',
            'created_at' => date('Y-m-d H:i:s')
        ]);

        $this->assertIsInt($id);
        $this->assertGreaterThan(0, $id);

        $found = $notificationModel->find($id);
        $this->assertNotNull($found);
        $this->assertEquals('Test Subject', $found['subject']);
    }
}
