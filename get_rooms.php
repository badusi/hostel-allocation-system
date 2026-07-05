<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
requireAdminLogin();

header('Content-Type: application/json');

if (!isset($_GET['hostel_id']) || !is_numeric($_GET['hostel_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid hostel ID']);
    exit();
}

$hostel_id = (int)$_GET['hostel_id'];

try {
    // Get hostel info
    $hostel = getHostelById($hostel_id);
    if (!$hostel) {
        echo json_encode(['success' => false, 'message' => 'Hostel not found']);
        exit();
    }

    $rooms = getHostelRooms($hostel_id);

    // ✅ Apply hostel price_per_session to each room if price is missing
    foreach ($rooms as &$room) {
        if (empty($room['price']) || $room['price'] == 0) {
            $room['price'] = isset($hostel['price_per_session']) ? $hostel['price_per_session'] : 0;
        }
    }

    echo json_encode([
        'success' => true,
        'rooms' => $rooms,
        'hostel' => [
            'hostel_name' => $hostel['hostel_name'],
            'gender' => $hostel['gender'],
            'price_per_session' => isset($hostel['price_per_session']) ? $hostel['price_per_session'] : 0
        ]
    ]);
} catch (Exception $e) {
    error_log("Get rooms error: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error loading rooms: ' . $e->getMessage()]);
}
?>
