<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
requireAdminLogin();

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid room ID']);
    exit();
}

$room_id = (int)$_GET['id'];

try {
    $room = getRoomById($room_id);
    
    if ($room) {
        echo json_encode(['success' => true, 'room' => $room]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Room not found']);
    }
} catch (Exception $e) {
    error_log("Get room error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading room details']);
}
?>