<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';
requireLogin();

header('Content-Type: application/json');

if (!isset($_GET['hostel_id']) || !is_numeric($_GET['hostel_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid hostel ID']);
    exit();
}

$hostel_id = (int)$_GET['hostel_id'];

try {
    $hostel = getHostelById($hostel_id);
    if (!$hostel) {
        echo json_encode(['success' => false, 'message' => 'Hostel not found']);
        exit();
    }

    $rooms = getHostelRooms($hostel_id);
    echo json_encode([
        'success' => true,
        'rooms' => $rooms,
        'hostel' => [
            'hostel_name' => $hostel['hostel_name'],
            'gender' => $hostel['gender']
        ]
    ]);
} catch (Exception $e) {
    error_log("Get rooms error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading rooms: ' . $e->getMessage()]);
}
?>
