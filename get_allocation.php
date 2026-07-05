<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid allocation ID']);
    exit();
}

$allocation_id = (int)$_GET['id'];

try {
    $db = new Database();
    $db->query('
        SELECT a.*, s.full_name, s.matric_number, s.gender, s.department, s.level,
               h.hostel_name, r.room_number, r.room_type, r.price,
               au.full_name as approved_by_name
        FROM allocations a 
        JOIN students s ON a.student_id = s.id 
        JOIN hostels h ON a.hostel_id = h.id 
        JOIN rooms r ON a.room_id = r.id 
        LEFT JOIN admin_users au ON a.approved_by = au.id
        WHERE a.id = :id
    ');
    $db->bind(':id', $allocation_id);
    $allocation = $db->single();
    
    if ($allocation) {
        echo json_encode(['success' => true, 'allocation' => $allocation]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Allocation not found']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error loading allocation details']);
}
?>
