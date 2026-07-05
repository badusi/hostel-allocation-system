<?php
session_start();
require_once 'includes/functions.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['apply'])) {
    $hostel_id = (int)$_POST['hostel_id'];
    $room_id = (int)$_POST['room_id'];
    $student_id = $_SESSION['student_id'];
    
    if (allocateRoom($student_id, $hostel_id, $room_id)) {
        header('Location: dashboard.php?success=1');
    } else {
        header('Location: dashboard.php?error=1');
    }
    exit();
}

// If accessed directly, redirect to dashboard
header('Location: dashboard.php');
exit();
?>
