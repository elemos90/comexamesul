<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Utils\Auth;

class ActivityLogger
{
    public static function log(string $entity, ?int $entityId, string $action, array $metadata = []): void
    {
        $model = new ActivityLog();
        $model->create([
            'user_id' => Auth::id(),
            'entity' => $entity,
            'entity_id' => $entityId,
            'action' => $action,
            'metadata' => json_encode($metadata),
            'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1',
            'created_at' => now(),
        ]);
    }
}
