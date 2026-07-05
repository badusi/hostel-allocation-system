<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply'])) {
    $student_id = $_SESSION['student_id'];
    $hostel_id = (int)$_POST['hostel_id'];
    $room_id = (int)$_POST['room_id'];
    
    // Check if student already has allocation
    if (hasActiveAllocation($student_id)) {
        $_SESSION['error'] = 'You already have an active hostel allocation.';
        header('Location: dashboard.php');
        exit();
    }
    
    // Allocate room directly (auto-approve)
    if (allocateRoomDirect($student_id, $hostel_id, $room_id)) {
        // Get allocation details
        $allocation = hasActiveAllocation($student_id);
        $_SESSION['allocation_id'] = $allocation['id'];
        $_SESSION['success'] = 'Room allocated successfully! Please proceed with payment.';
        header('Location: payment.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to allocate room. Please try again.';
        header('Location: dashboard.php');
        exit();
    }
}

header('Location: dashboard.php');
exit();
?>