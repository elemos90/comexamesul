<?php

namespace App\Models;

class ActivityLog extends BaseModel
{
    protected string $table = 'activity_log';
    protected array $fillable = [
        'user_id',
        'entity',
        'entity_id',
        'action',
        'metadata',
        'ip',
        'created_at',
    ];
}
