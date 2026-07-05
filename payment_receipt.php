<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
requireLogin();

if (!isset($_SESSION['payment_success'])) {
    header('Location: dashboard.php');
    ob_end_flush();
    exit();
}

$student = getStudentInfo($_SESSION['student_id']);
$allocation = hasActiveAllocation($_SESSION['student_id']);

// Get allocation details with room and hostel info
$db = new Database();
$db->query('
    SELECT a.*, h.hostel_name, r.room_number, r.room_type, r.price, a.amount_paid 
    FROM allocations a 
    JOIN hostels h ON a.hostel_id = h.id 
    JOIN rooms r ON a.room_id = r.id 
    WHERE a.id = :id
');
$db->bind(':id', $allocation['id']);
$allocation_details = $db->single();

// Clear the payment success flag
unset($_SESSION['payment_success']);

// Custom error logging
$log_file = __DIR__ . '/payment_debug.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Payment script started\n", FILE_APPEND);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - Federal Polytechnic Ayede</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8fafc;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .receipt-container {
            max-width: 600px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            border: 2px solid #2c5aa0;
        }
        
        .receipt-header {
            text-align: center;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid #e2e8f0;
        }
        
        .receipt-details {
            margin-bottom: 2rem;
        }
        
        .receipt-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .important-notice {
            background: #fffbeb;
            border: 2px solid #f59e0b;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 2rem 0;
        }
        
        .print-btn {
            background: #2c5aa0;
            color: white;
            padding: 1rem 2rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0 auto;
            transition: background-color 0.3s ease;
        }
        
        .print-btn:hover {
            background: #1e3a8a;
        }
        
        .success-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 1rem;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            .receipt-container, .receipt-container * {
                visibility: visible;
            }
            .receipt-container {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                max-width: 100%;
                box-shadow: none;
                border: none;
            }
            .print-btn, .no-print {
                display: none !important;
            }
        }
        
        .dashboard-header {
            background: linear-gradient(135deg, #2c5aa0 0%, #1e3a8a 100%);
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
        }
        
        .dashboard-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .dashboard-title {
            margin: 0;
            font-size: 1.5rem;
        }
        
        .logout-btn {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border: 1px solid rgba(255,255,255,0.3);
            border-radius: 5px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.1);
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="dashboard-header">
            <div class="dashboard-nav">
                <h1 class="dashboard-title">
                    <i class="fas fa-receipt"></i> Payment Receipt
                </h1>
                <div>
                    <a href="dashboard.php" style="margin-right: 1rem; color: white; text-decoration: none;">
                        <i class="fas fa-home"></i> Dashboard
                    </a>
                    <a href="profile.php" style="margin-right: 1rem; color: white; text-decoration: none;">
                        <i class="fas fa-user"></i> Profile
                    </a>
                    <span style="margin-right: 1rem; color: rgba(255,255,255,0.8);">
                        Welcome, <?php echo htmlspecialchars($student['full_name']); ?>
                    </span>
                    <a href="logout.php" class="logout-btn">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        </div>
        
        <div class="receipt-container">
            <div class="receipt-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1 style="color: #2c5aa0; margin-bottom: 0.5rem;">
                    Payment Successful!
                </h1>
                <p style="color: #666; font-size: 1.1rem;">Federal Polytechnic Ayede - Hostel Accommodation</p>
                <p style="color: #10b981; font-weight: bold; margin-top: 0.5rem;">
                    <i class="fas fa-shield-alt"></i> Payment Verified and Confirmed
                </p>
            </div>
            
            <div class="receipt-details">
                <h3 style="color: #2c5aa0; margin-bottom: 1rem; padding-bottom: 0.5rem; border-bottom: 2px solid #e2e8f0;">
                    <i class="fas fa-file-invoice"></i> Payment Receipt
                </h3>
                
                <div class="receipt-item">
                    <span><strong><i class="fas fa-user"></i> Student Name:</strong></span>
                    <span><?php echo htmlspecialchars($student['full_name']); ?></span>
                </div>
                
                <div class="receipt-item">
                    <span><strong><i class="fas fa-id-card"></i> Matric Number:</strong></span>
                    <span><?php echo htmlspecialchars($student['matric_number']); ?></span>
                </div>
                
                <div class="receipt-item">
                    <span><strong><i class="fas fa-building"></i> Hostel:</strong></span>
                    <span><?php echo htmlspecialchars($allocation_details['hostel_name']); ?></span>
                </div>
                
                <div class="receipt-item">
                    <span><strong><i class="fas fa-door-open"></i> Room Number:</strong></span>
                    <span><?php echo htmlspecialchars($allocation_details['room_number']); ?></span>
                </div>
                
                <div class="receipt-item">
                    <span><strong><i class="fas fa-bed"></i> Room Type:</strong></span>
                    <span><?php echo ucfirst(str_replace('_', ' ', $allocation_details['room_type'])); ?></span>
                </div>
                
                <div class="receipt-item">
                    <span><strong><i class="fas fa-credit-card"></i> Payment Method:</strong></span>
                    <span><?php echo ucfirst(str_replace('_', ' ', $allocation_details['payment_method'] ?? 'N/A')); ?></span>
                </div>
                
                <div class="receipt-item">
                    <span><strong><i class="fas fa-money-bill-wave"></i> Amount Paid:</strong></span>
                    <span style="font-weight: bold; color: #059669; font-size: 1.1rem;">
                        ₦<?php echo number_format($allocation_details['amount_paid'] ?? $allocation_details['price'], 2); ?>
                    </span>
                </div>
                
                <div class="receipt-item">
                    <span><strong><i class="fas fa-calendar"></i> Payment Date:</strong></span>
                    <span><?php echo date('F j, Y g:i A', strtotime($allocation_details['payment_date'] ?? 'now')); ?></span>
                </div>
                
                <div class="receipt-item">
                    <span><strong><i class="fas fa-hashtag"></i> Reference No:</strong></span>
                    <span style="font-family: monospace; font-weight: bold;">
                        <?php echo htmlspecialchars($allocation_details['payment_reference'] ?? 'N/A'); ?>
                    </span>
                </div>
            </div>
            
            <div class="important-notice">
                <h4 style="color: #d97706; margin-bottom: 1rem;">
                    <i class="fas fa-exclamation-triangle"></i> Important Instructions
                </h4>
                <p style="margin-bottom: 0.5rem;"><strong>1. Present this receipt to the hostel warden to collect your room key.</strong></p>
                <p style="margin-bottom: 0.5rem;"><strong>2. Keep this receipt safe as proof of payment.</strong></p>
                <p style="margin-bottom: 0.5rem;"><strong>3. Report to your allocated hostel within 48 hours.</strong></p>
                <p style="margin-bottom: 0;"><strong>4. Contact hostel administration for any issues.</strong></p>
            </div>
            
            <div style="text-align: center; margin-top: 2rem;">
                <button onclick="window.print()" class="print-btn">
                    <i class="fas fa-print"></i> Print Receipt
                </button>
                <div style="margin-top: 1rem;" class="no-print">
                    <a href="dashboard.php" style="display: inline-block; margin: 0.5rem; color: #2c5aa0; text-decoration: none; padding: 0.5rem 1rem; border: 1px solid #2c5aa0; border-radius: 5px;">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                    <a href="payment.php" style="display: inline-block; margin: 0.5rem; color: #059669; text-decoration: none; padding: 0.5rem 1rem; border: 1px solid #059669; border-radius: 5px;">
                        <i class="fas fa-credit-card"></i> Make Another Payment
                    </a>
                </div>
            </div>
            
            <!-- Watermark -->
            <div style="text-align: center; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #e2e8f0;">
                <p style="color: #9ca3af; font-size: 0.9rem;">
                    <i class="fas fa-lock"></i> This is an computer-generated receipt. No signature required.
                </p>
            </div>
        </div>
    </div>

    <script>
        // Auto-print option (optional)
        function autoPrint() {
            window.print();
        }
        
        // You can uncomment the line below to auto-print when page loads
        // window.addEventListener('load', autoPrint);
        
        // Add confirmation before leaving page
        window.addEventListener('beforeunload', function(e) {
            // Optional: Add confirmation if needed
        });
    </script>
</body>
</html>