<?php
/**
 * API: Buscar salas por local
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../bootstrap.php';

use App\Models\ExamRoom;

if (!isset($_GET['location_id']) || empty($_GET['location_id'])) {
    echo json_encode([]);
    exit;
}

$locationId = (int)$_GET['location_id'];
$roomModel = new ExamRoom();

try {
    $rooms = $roomModel->getByLocation($locationId);
    echo json_encode($rooms);
} catch (Exception $e) {
    echo json_encode([]);
}
