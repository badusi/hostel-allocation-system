<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid hostel ID']);
    exit();
}

$hostel_id = (int)$_GET['id'];

try {
    $hostel = getHostelById($hostel_id);
    
    if ($hostel) {
        echo json_encode(['success' => true, 'hostel' => $hostel]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Hostel not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading hostel information']);
}
?>
