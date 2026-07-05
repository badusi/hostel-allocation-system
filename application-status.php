<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$student = getStudentInfo($_SESSION['student_id']);
$allocation = hasActiveAllocation($_SESSION['student_id']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Status - Federal Polytechnic Ayede</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <div class="dashboard-header">
            <div class="container">
                <div class="dashboard-nav">
                    <h1 class="dashboard-title">
                        <i class="fas fa-file-alt"></i> Application Status
                    </h1>
                    <div>
                        <a href="dashboard.php" style="margin-right: 1rem; color: white; text-decoration: none;">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        <a href="profile.php" style="margin-right: 1rem; color: white; text-decoration: none;">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <a href="payment.php" style="margin-right: 1rem; color: white; text-decoration: none;">
                            <i class="fas fa-credit-card"></i> Payment
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
        </div>
        
        <div class="container">
            <div class="dashboard-content">
                <?php if (!$allocation): ?>
                    <div class="allocation-section">
                        <div style="text-align: center; padding: 3rem; background: white; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                            <div style="font-size: 4rem; color: #e5e7eb; margin-bottom: 1rem;">
                                <i class="fas fa-file-alt"></i>
                            </div>
                            <h3 style="color: #374151; margin-bottom: 1rem;">No Application Found</h3>
                            <p style="color: #6b7280; margin-bottom: 2rem;">
                                You haven't submitted a hostel application yet. Start by applying for accommodation.
                            </p>
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Apply for Hostel
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Application Timeline -->
                    <div class="allocation-section">
                        <h3><i class="fas fa-timeline"></i> Application Timeline</h3>
                        
                        <div style="position: relative; padding-left: 2rem;">
                            <!-- Timeline line -->
                            <div style="position: absolute; left: 1rem; top: 0; bottom: 0; width: 2px; background: #e5e7eb;"></div>
                            
                            <!-- Step 1: Application Submitted -->
                            <div style="position: relative; margin-bottom: 2rem;">
                                <div style="position: absolute; left: -1.5rem; top: 0.5rem; width: 2rem; height: 2rem; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                    <i class="fas fa-check" style="font-size: 0.8rem;"></i>
                                </div>
                                <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #10b981;">
                                    <h4 style="color: #10b981; margin-bottom: 0.5rem;">
                                        <i class="fas fa-paper-plane"></i> Application Submitted
                                    </h4>
                                    <p style="color: #666; margin-bottom: 0.5rem;">
                                        Your hostel application has been successfully submitted to the system.
                                    </p>
                                    <small style="color: #9ca3af;">
                                        <i class="fas fa-clock"></i> 
                                        <?php echo date('M j, Y g:i A', strtotime($allocation['allocation_date'])); ?>
                                    </small>
                                </div>
                            </div>
                            
                            <!-- Step 2: Under Review -->
                            <div style="position: relative; margin-bottom: 2rem;">
                                <?php if ($allocation['admin_approved'] === 'pending'): ?>
                                    <div style="position: absolute; left: -1.5rem; top: 0.5rem; width: 2rem; height: 2rem; background: #f59e0b; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                        <i class="fas fa-clock" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #f59e0b;">
                                        <h4 style="color: #f59e0b; margin-bottom: 0.5rem;">
                                            <i class="fas fa-search"></i> Under Review
                                        </h4>
                                        <p style="color: #666; margin-bottom: 0.5rem;">
                                            Your application is currently being reviewed by the administration team.
                                        </p>
                                        <small style="color: #9ca3af;">
                                            <i class="fas fa-hourglass-half"></i> 
                                            Review typically takes 2-3 business days
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <div style="position: absolute; left: -1.5rem; top: 0.5rem; width: 2rem; height: 2rem; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                        <i class="fas fa-check" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #10b981;">
                                        <h4 style="color: #10b981; margin-bottom: 0.5rem;">
                                            <i class="fas fa-search"></i> Review Completed
                                        </h4>
                                        <p style="color: #666; margin-bottom: 0.5rem;">
                                            Your application has been reviewed by the administration.
                                        </p>
                                        <small style="color: #9ca3af;">
                                            <i class="fas fa-check-circle"></i> 
                                            Reviewed on <?php echo date('M j, Y g:i A', strtotime($allocation['approval_date'])); ?>
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Step 3: Decision -->
                            <div style="position: relative; margin-bottom: 2rem;">
                                <?php if ($allocation['admin_approved'] === 'approved'): ?>
                                    <div style="position: absolute; left: -1.5rem; top: 0.5rem; width: 2rem; height: 2rem; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                        <i class="fas fa-check" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #10b981;">
                                        <h4 style="color: #10b981; margin-bottom: 0.5rem;">
                                            <i class="fas fa-thumbs-up"></i> Application Approved
                                        </h4>
                                        <p style="color: #666; margin-bottom: 0.5rem;">
                                            Congratulations! Your hostel application has been approved.
                                        </p>
                                        <small style="color: #9ca3af;">
                                            <i class="fas fa-calendar-check"></i> 
                                            Approved on <?php echo date('M j, Y g:i A', strtotime($allocation['approval_date'])); ?>
                                        </small>
                                    </div>
                                <?php elseif ($allocation['admin_approved'] === 'rejected'): ?>
                                    <div style="position: absolute; left: -1.5rem; top: 0.5rem; width: 2rem; height: 2rem; background: #ef4444; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                        <i class="fas fa-times" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #ef4444;">
                                        <h4 style="color: #ef4444; margin-bottom: 0.5rem;">
                                            <i class="fas fa-thumbs-down"></i> Application Rejected
                                        </h4>
                                        <p style="color: #666; margin-bottom: 0.5rem;">
                                            Unfortunately, your application has been rejected.
                                        </p>
                                        <?php if ($allocation['rejection_reason']): ?>
                                            <p style="color: #666; margin-bottom: 0.5rem; font-style: italic;">
                                                <strong>Reason:</strong> <?php echo htmlspecialchars($allocation['rejection_reason']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <small style="color: #9ca3af;">
                                            <i class="fas fa-calendar-times"></i> 
                                            Rejected on <?php echo date('M j, Y g:i A', strtotime($allocation['approval_date'])); ?>
                                        </small>
                                    </div>
                                <?php else: ?>
                                    <div style="position: absolute; left: -1.5rem; top: 0.5rem; width: 2rem; height: 2rem; background: #e5e7eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                        <i class="fas fa-clock" style="font-size: 0.8rem;"></i>
                                    </div>
                                    <div style="background: #f9fafb; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #e5e7eb;">
                                        <h4 style="color: #9ca3af; margin-bottom: 0.5rem;">
                                            <i class="fas fa-hourglass-half"></i> Awaiting Decision
                                        </h4>
                                        <p style="color: #666; margin-bottom: 0.5rem;">
                                            Waiting for administration decision on your application.
                                        </p>
                                        <small style="color: #9ca3af;">
                                            <i class="fas fa-info-circle"></i> 
                                            You will be notified once a decision is made
                                        </small>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <!-- Step 4: Payment (if approved) -->
                            <?php if ($allocation['admin_approved'] === 'approved'): ?>
                                <div style="position: relative; margin-bottom: 2rem;">
                                    <?php if ($allocation['payment_status'] === 'paid'): ?>
                                        <div style="position: absolute; left: -1.5rem; top: 0.5rem; width: 2rem; height: 2rem; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                            <i class="fas fa-check" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #10b981;">
                                            <h4 style="color: #10b981; margin-bottom: 0.5rem;">
                                                <i class="fas fa-credit-card"></i> Payment Completed
                                            </h4>
                                            <p style="color: #666; margin-bottom: 0.5rem;">
                                                Your hostel fee payment has been confirmed and processed.
                                            </p>
                                            <small style="color: #9ca3af;">
                                                <i class="fas fa-check-circle"></i> 
                                                Payment confirmed
                                            </small>
                                        </div>
                                    <?php else: ?>
                                        <div style="position: absolute; left: -1.5rem; top: 0.5rem; width: 2rem; height: 2rem; background: #f59e0b; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                            <i class="fas fa-exclamation" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #f59e0b;">
                                            <h4 style="color: #f59e0b; margin-bottom: 0.5rem;">
                                                <i class="fas fa-credit-card"></i> Payment Required
                                            </h4>
                                            <p style="color: #666; margin-bottom: 1rem;">
                                                Please proceed to make payment to secure your accommodation.
                                            </p>
                                            <a href="payment.php" class="btn btn-primary">
                                                <i class="fas fa-credit-card"></i> Make Payment
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <!-- Step 5: Accommodation Confirmed -->
                                <?php if ($allocation['payment_status'] === 'paid'): ?>
                                    <div style="position: relative;">
                                        <div style="position: absolute; left: -1.5rem; top: 0.5rem; width: 2rem; height: 2rem; background: #10b981; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white;">
                                            <i class="fas fa-home" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <div style="background: white; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #10b981;">
                                            <h4 style="color: #10b981; margin-bottom: 0.5rem;">
                                                <i class="fas fa-home"></i> Accommodation Confirmed
                                            </h4>
                                            <p style="color: #666; margin-bottom: 0.5rem;">
                                                Your hostel accommodation is now confirmed and ready for occupancy.
                                            </p>
                                            <small style="color: #9ca3af;">
                                                <i class="fas fa-key"></i> 
                                                You can now move into your assigned room
                                            </small>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div style="position: relative;">
                                        <div style="position: absolute; left: -1.5rem; top: 0.5rem; width: 2rem; height: 2rem; background: #e5e7eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #9ca3af;">
                                            <i class="fas fa-home" style="font-size: 0.8rem;"></i>
                                        </div>
                                        <div style="background: #f9fafb; padding: 1.5rem; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); border-left: 4px solid #e5e7eb;">
                                            <h4 style="color: #9ca3af; margin-bottom: 0.5rem;">
                                                <i class="fas fa-home"></i> Accommodation Confirmation
                                            </h4>
                                            <p style="color: #666; margin-bottom: 0.5rem;">
                                                Complete payment to confirm your accommodation.
                                            </p>
                                            <small style="color: #9ca3af;">
                                                <i class="fas fa-info-circle"></i> 
                                                Pending payment completion
                                            </small>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Application Details -->
                    <div class="allocation-section">
                        <h3><i class="fas fa-info-circle"></i> Application Details</h3>
                        
                        <?php
                        $db = new Database();
                        $db->query('
                            SELECT a.*, h.hostel_name, h.image_path, r.room_number, r.room_type, r.price 
                            FROM allocations a 
                            JOIN hostels h ON a.hostel_id = h.id 
                            JOIN rooms r ON a.room_id = r.id 
                            WHERE a.id = :allocation_id
                        ');
                        $db->bind(':allocation_id', $allocation['id']);
                        $allocationDetails = $db->single();
                        ?>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                            <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                                <h4 style="color: #2c5aa0; margin-bottom: 1.5rem;">
                                    <i class="fas fa-building"></i> Accommodation Details
                                </h4>
                                
                                
                                
                                <div class="info-item">
                                    <span class="info-label">Hostel:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($allocationDetails['hostel_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Room Number:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($allocationDetails['room_number']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Room Type:</span>
                                    <span class="info-value"><?php echo ucfirst($allocationDetails['room_type']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Price per Session:</span>
                                    <span class="info-value">₦<?php echo number_format($allocationDetails['price'], 2); ?></span>
                                </div>
                            </div>
                            
                            <div style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                                <h4 style="color: #2c5aa0; margin-bottom: 1.5rem;">
                                    <i class="fas fa-calendar"></i> Application Information
                                </h4>
                                
                                <div class="info-item">
                                    <span class="info-label">Application Date:</span>
                                    <span class="info-value"><?php echo date('M j, Y g:i A', strtotime($allocation['allocation_date'])); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Academic Session:</span>
                                    <span class="info-value"><?php echo $allocation['academic_session']; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Current Status:</span>
                                    <span class="info-value">
                                        <?php if ($allocation['admin_approved'] === 'pending'): ?>
                                            <span style="color: #f59e0b;">
                                                <i class="fas fa-clock"></i> Pending Review
                                            </span>
                                        <?php elseif ($allocation['admin_approved'] === 'approved'): ?>
                                            <span style="color: #10b981;">
                                                <i class="fas fa-check-circle"></i> Approved
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #ef4444;">
                                                <i class="fas fa-times-circle"></i> Rejected
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Payment Status:</span>
                                    <span class="info-value">
                                        <?php if ($allocation['payment_status'] === 'paid'): ?>
                                            <span style="color: #10b981;">
                                                <i class="fas fa-check-circle"></i> Paid
                                            </span>
                                        <?php else: ?>
                                            <span style="color: #f59e0b;">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Actions -->
                    <div class="allocation-section">
                        <h3><i class="fas fa-bolt"></i> Quick Actions</h3>
                        
                        <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                            <?php if ($allocation['admin_approved'] === 'approved' && $allocation['payment_status'] === 'pending'): ?>
                                <a href="payment.php" class="btn btn-primary">
                                    <i class="fas fa-credit-card"></i> Make Payment
                                </a>
                            <?php endif; ?>
                            
                            <a href="profile.php" class="btn btn-secondary">
                                <i class="fas fa-user"></i> Update Profile
                            </a>
                            
                            <a href="contact.php" class="btn btn-secondary">
                                <i class="fas fa-phone"></i> Contact Support
                            </a>
                            
                            <button onclick="window.print()" class="btn btn-secondary">
                                <i class="fas fa-print"></i> Print Status
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Auto-refresh status every 30 seconds
        setInterval(function() {
            // Only refresh if application is pending
            <?php if ($allocation && $allocation['admin_approved'] === 'pending'): ?>
                location.reload();
            <?php endif; ?>
        }, 30000);
    </script>
</body>
</html>
