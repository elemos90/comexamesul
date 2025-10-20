<?php

require __DIR__ . '/../../bootstrap.php';

use App\Models\ActivityLog;
use App\Models\ExamVacancy;

$vacancyModel = new ExamVacancy();
$closed = $vacancyModel->closeExpired();

if ($closed > 0) {
    $activity = new ActivityLog();
    $activity->create([
        'user_id' => null,
        'entity' => 'exam_vacancies',
        'entity_id' => null,
        'action' => 'auto_close',
        'metadata' => json_encode(['count' => $closed]),
        'ip' => 'cron',
        'created_at' => now(),
    ]);
}

echo sprintf("%d vaga(s) atualizadas.%s", $closed, PHP_EOL);
