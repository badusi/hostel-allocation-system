<?php
// Start output buffering to prevent "headers already sent" errors
ob_start();
session_start();
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$student = getStudentInfo($_SESSION['student_id']);
$allocation = hasActiveAllocation($_SESSION['student_id']);

// Redirect if no approved allocation
if (!$allocation || $allocation['admin_approved'] !== 'approved') {
    $_SESSION['error'] = 'No active hostel allocation found.';
    header('Location: dashboard.php');
    ob_end_flush();
    exit();
}

// Get allocation details with room and hostel info
$db = new Database();
$db->query('
    SELECT a.*, h.hostel_name, r.room_number, r.room_type, r.price 
    FROM allocations a 
    JOIN hostels h ON a.hostel_id = h.id 
    JOIN rooms r ON a.room_id = r.id 
    WHERE a.id = :allocation_id
');
$db->bind(':allocation_id', $allocation['id']);
$allocationDetails = $db->single();

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Handle regular payment form
    if (isset($_POST['make_payment'])) {
        $payment_method = sanitize($_POST['payment_method']);
        $reference_number = sanitize($_POST['reference_number']);
        $amount = (float)$_POST['amount'];
        
        if (!empty($payment_method) && !empty($reference_number)) {
            try {
                $db->query('UPDATE allocations SET payment_status = "paid", payment_method = :method, payment_reference = :reference, amount_paid = :amount, payment_date = NOW() WHERE id = :id');
                $db->bind(':method', $payment_method);
                $db->bind(':reference', $reference_number);
                $db->bind(':amount', $amount);
                $db->bind(':id', $allocation['id']);
                
                $result = $db->execute();
                
                if ($result) {
                    $rowCount = $db->rowCount();
                    
                    if ($rowCount > 0) {
                        // SUCCESS - REDIRECT TO RECEIPT
                        $_SESSION['payment_success'] = true;
                        $_SESSION['payment_reference'] = $reference_number;
                        $_SESSION['payment_method'] = $payment_method;
                        $_SESSION['payment_amount'] = $amount;
                        $_SESSION['allocation_id'] = $allocation['id'];
                        
                        header('Location: payment_receipt.php');
                        ob_end_flush();
                        exit();
                    } else {
                        $message = 'No allocation record was updated. Please contact support.';
                        $messageType = 'error';
                    }
                } else {
                    $message = 'Database update failed. Please try again.';
                    $messageType = 'error';
                }
            } catch (Exception $e) {
                $message = 'Payment processing error: ' . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = 'Please fill in all required fields.';
            $messageType = 'error';
        }
    }
    
    // Handle online payment - FIXED VERSION
    $has_card_fields = isset($_POST['card_number']) && isset($_POST['card_name']) && isset($_POST['expiry_date']) && isset($_POST['cvv']) && isset($_POST['card_pin']);
    
    if ($has_card_fields) {
        $card_number = sanitize($_POST['card_number'] ?? '');
        $card_name = sanitize($_POST['card_name'] ?? '');
        $expiry_date = sanitize($_POST['expiry_date'] ?? '');
        $cvv = sanitize($_POST['cvv'] ?? '');
        $card_pin = sanitize($_POST['card_pin'] ?? '');
        
        // Check if all card details are filled
        if (!empty($card_number) && !empty($card_name) && !empty($expiry_date) && !empty($cvv) && !empty($card_pin)) {
            try {
                $transaction_ref = 'TXN' . date('YmdHis') . rand(1000, 9999);
                $amount = $allocationDetails['price'];
                
                $db->query('UPDATE allocations SET payment_status = "paid", payment_method = "online_payment", payment_reference = :reference, amount_paid = :amount, payment_date = NOW() WHERE id = :id');
                $db->bind(':reference', $transaction_ref);
                $db->bind(':amount', $amount);
                $db->bind(':id', $allocation['id']);
                
                $result = $db->execute();
                
                if ($result && $db->rowCount() > 0) {
                    $_SESSION['payment_success'] = true;
                    $_SESSION['payment_reference'] = $transaction_ref;
                    $_SESSION['payment_method'] = 'online_payment';
                    $_SESSION['payment_amount'] = $amount;
                    $_SESSION['allocation_id'] = $allocation['id'];
                    
                    header('Location: payment_receipt.php');
                    ob_end_flush();
                    exit();
                } else {
                    $message = 'Online payment failed. No records updated.';
                    $messageType = 'error';
                }
            } catch (Exception $e) {
                $message = 'Online payment error: ' . $e->getMessage();
                $messageType = 'error';
            }
        } else {
            $message = 'Please fill in all card details.';
            $messageType = 'error';
        }
    }
}

// Clean output buffer since we're displaying content
ob_end_clean();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Make Payment - Federal Polytechnic Ayede</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .payment-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .room-details {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
            border-left: 4px solid #2c5aa0;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.5rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .payment-success {
            text-align: center;
            padding: 3rem;
            background: #d1fae5;
            border-radius: 15px;
            border: 1px solid #a7f3d0;
        }
        
        .payment-methods-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .payment-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            border: 2px solid #e5e7eb;
            transition: all 0.3s ease;
            text-align: center;
            cursor: pointer;
        }
        
        .payment-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .payment-card.bank {
            border-color: #2c5aa0;
        }
        
        .payment-card.online {
            border-color: #10b981;
        }
        
        .payment-card.mobile {
            border-color: #f59e0b;
        }
        
        .payment-icon {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            font-size: 1.8rem;
            color: white;
        }
        
        .bank .payment-icon {
            background: #2c5aa0;
        }
        
        .online .payment-icon {
            background: #10b981;
        }
        
        .mobile .payment-icon {
            background: #f59e0b;
        }
        
        .account-details {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin: 1.5rem 0;
            text-align: left;
        }
        
        .info-label {
            font-weight: 600;
            color: #374151;
        }
        
        .info-value {
            color: #6b7280;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            animation: modalSlideIn 0.3s ease;
        }
        
        @keyframes modalSlideIn {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .modal-header {
            background: #10b981;
            color: white;
            padding: 1.5rem;
            border-radius: 15px 15px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-header h3 {
            margin: 0;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .close-modal {
            background: none;
            border: none;
            color: white;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            width: 30px;
            height: 30px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .modal-body {
            padding: 2rem;
        }
        
        .card-input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            margin-bottom: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .card-input:focus {
            outline: none;
            border-color: #10b981;
        }
        
        .card-input-group {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .card-input-group-3 {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 1rem;
        }
        
        .card-preview {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-family: 'Courier New', monospace;
        }
        
        .card-number {
            font-size: 1.2rem;
            letter-spacing: 2px;
            margin-bottom: 1rem;
        }
        
        .card-details {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
        }
        
        .loading-spinner {
            display: none;
            text-align: center;
            padding: 1rem;
        }
        
        .spinner {
            border: 4px solid #f3f3f3;
            border-top: 4px solid #10b981;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            animation: spin 1s linear infinite;
            margin: 0 auto 1rem;
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        
        .pin-input {
            font-family: 'text-security-disc';
            -webkit-text-security: disc;
            letter-spacing: 5px;
        }
        
        /* Hide reference section for online payment */
        .reference-section {
            display: block;
        }
        
        .online-payment-active .reference-section {
            display: none;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="dashboard-header">
            <div class="container">
                <div class="dashboard-nav">
                    <h1 class="dashboard-title">
                        <i class="fas fa-credit-card"></i> Make Payment
                    </h1>
                    <div>
                        <a href="dashboard.php" style="margin-right: 1rem; color: white; text-decoration: none;">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        <a href="profile.php" style="margin-right: 1rem; color: white; text-decoration: none;">
                            <i class="fas fa-user"></i> Profile
                        </a>
                        <a href="application-status.php" style="margin-right: 1rem; color: white; text-decoration: none;">
                            <i class="fas fa-clipboard-list"></i> Application Status
                        </a>
                        <a href="payment.php" style="margin-right: 1rem; color: rgba(255,255,255,0.9); text-decoration: none;">
                            <i class="fas fa-credit-card"></i> Payment
                        </a>
                        <a href="contact.php" style="margin-right: 1rem; color: white; text-decoration: none;">
                            <i class="fas fa-envelope"></i> Contact
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
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="payment-container">
                <?php if ($allocation['payment_status'] === 'paid'): ?>
                    <!-- Payment Confirmed -->
                    <div class="payment-success">
                        <div style="font-size: 4rem; color: #10b981; margin-bottom: 1rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h3 style="color: #065f46; margin-bottom: 1rem;">Payment Confirmed!</h3>
                        <p style="color: #047857; margin-bottom: 2rem;">
                            Your payment has been processed successfully. Your accommodation is now secured.
                        </p>
                        
                        <div style="background: rgba(255,255,255,0.7); padding: 2rem; border-radius: 10px; margin: 2rem 0; text-align: left;">
                            <h4 style="color: #065f46; margin-bottom: 1rem;">Payment Details</h4>
                            <div class="info-item">
                                <span class="info-label">Amount Paid:</span>
                                <span class="info-value">₦<?php echo number_format($allocationDetails['price'], 2); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Payment Method:</span>
                                <span class="info-value"><?php echo ucfirst(str_replace('_', ' ', $allocation['payment_method'])); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Reference Number:</span>
                                <span class="info-value"><?php echo htmlspecialchars($allocation['payment_reference']); ?></span>
                            </div>
                            <div class="info-item">
                                <span class="info-label">Payment Date:</span>
                                <span class="info-value"><?php echo date('M j, Y g:i A', strtotime($allocation['payment_date'])); ?></span>
                            </div>
                        </div>
                        
                        <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                            <a href="dashboard.php" class="btn btn-primary">
                                <i class="fas fa-home"></i> Back to Dashboard
                            </a>
                            <a href="payment_receipt.php" class="btn btn-secondary">
                                <i class="fas fa-receipt"></i> View Receipt
                            </a>
                        </div>
                    </div>
                <?php else: ?>
                    <!-- Room Allocation Details -->
                    <div class="room-details">
                        <h3 style="color: #2c5aa0; margin-bottom: 1rem;">
                            <i class="fas fa-check-circle"></i> Room Allocated Successfully!
                        </h3>
                        
                        <div class="info-grid">
                            <div>
                                <strong>Hostel:</strong> <?php echo htmlspecialchars($allocationDetails['hostel_name']); ?>
                            </div>
                            <div>
                                <strong>Room Number:</strong> <?php echo htmlspecialchars($allocationDetails['room_number']); ?>
                            </div>
                            <div>
                                <strong>Room Type:</strong> <?php echo ucfirst(str_replace('_', ' ', $allocationDetails['room_type'])); ?>
                            </div>
                            <div>
                                <strong>Amount Due:</strong> ₦<?php echo number_format($allocationDetails['price'], 2); ?>
                            </div>
                        </div>
                        
                        <div style="background: #d1fae5; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                            <i class="fas fa-info-circle"></i>
                            <strong>Important:</strong> After payment, collect your room key from the hostel warden with your payment receipt.
                        </div>
                    </div>
                    
                    <!-- Payment Methods Cards -->
                    <h3 style="color: #2c5aa0; margin-bottom: 1rem;">
                        <i class="fas fa-credit-card"></i> Choose Payment Method
                    </h3>
                    
                    <div class="payment-methods-grid">
                        <!-- Bank Transfer Card -->
                        <div class="payment-card bank" onclick="selectPaymentMethod('bank_transfer')">
                            <div class="payment-icon">
                                <i class="fas fa-university"></i>
                            </div>
                            <h4 style="color: #2c5aa0; margin-bottom: 0.5rem;">Bank Transfer</h4>
                            <p style="color: #666; margin-bottom: 1.5rem;">Transfer directly to school account</p>
                            
                            <div class="account-details">
                                <h5 style="color: #374151; margin-bottom: 1rem;">Account Details</h5>
                                <div class="info-item">
                                    <span class="info-label">Bank Name:</span>
                                    <span class="info-value">First Bank Nigeria</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Account Name:</span>
                                    <span class="info-value">Federal Polytechnic Ayede</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Account Number:</span>
                                    <span class="info-value" style="font-family: monospace; font-weight: bold;">2011234567</span>
                                </div>
                            </div>
                            
                            <div style="background: #fef3c7; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                                <p style="color: #92400e; font-size: 0.9rem; margin: 0;">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Note:</strong> Use your matric number as payment reference
                                </p>
                            </div>
                        </div>
                        
                        <!-- Online Payment Card -->
                        <div class="payment-card online" onclick="openPaymentModal()">
                            <div class="payment-icon">
                                <i class="fas fa-credit-card"></i>
                            </div>
                            <h4 style="color: #10b981; margin-bottom: 0.5rem;">Online Payment</h4>
                            <p style="color: #666; margin-bottom: 1.5rem;">Pay securely with debit/credit card</p>
                            
                            <button class="btn btn-primary" style="width: 100%; margin-bottom: 1rem;">
                                <i class="fas fa-lock"></i> Pay Now - ₦<?php echo number_format($allocationDetails['price'], 2); ?>
                            </button>
                            
                            <div style="background: #dbeafe; padding: 1rem; border-radius: 8px;">
                                <p style="color: #1e40af; font-size: 0.9rem; margin: 0;">
                                    <i class="fas fa-shield-alt"></i>
                                    Secure payment powered by Paystack
                                </p>
                            </div>
                        </div>
                        
                        <!-- Mobile Money Card -->
                        <div class="payment-card mobile" onclick="selectPaymentMethod('mobile_money')">
                            <div class="payment-icon">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <h4 style="color: #f59e0b; margin-bottom: 0.5rem;">Mobile Money</h4>
                            <p style="color: #666; margin-bottom: 1.5rem;">Pay with mobile money</p>
                            
                            <div style="display: flex; gap: 0.5rem; margin-bottom: 1.5rem;">
                                <button class="btn btn-secondary" style="flex: 1; font-size: 0.8rem;">
                                    MTN MoMo
                                </button>
                                <button class="btn btn-secondary" style="flex: 1; font-size: 0.8rem;">
                                    Airtel Money
                                </button>
                            </div>
                            
                            <div style="background: #fef3c7; padding: 1rem; border-radius: 8px;">
                                <p style="color: #92400e; font-size: 0.9rem; margin: 0;">
                                    <i class="fas fa-clock"></i>
                                    Mobile money integration coming soon
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Payment Form -->
                    <form method="POST" id="paymentForm">
                        <h3 style="color: #2c5aa0; margin-bottom: 1rem;">
                            <i class="fas fa-receipt"></i> Confirm Your Payment
                        </h3>
                        
                        <div style="display: grid; grid-template-columns: 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                            <div>
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                                    <i class="fas fa-credit-card"></i> Payment Method *
                                </label>
                                <select name="payment_method" id="payment_method" required style="width: 100%; padding: 0.75rem; border: 2px solid #e1e5e9; border-radius: 8px;">
                                    <option value="">Select payment method</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="online_payment">Online Payment</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="pos">POS Payment</option>
                                    <option value="cash">Cash</option>
                                </select>
                            </div>
                            
                            <!-- Reference Number Section - Hidden for Online Payment -->
                            <div class="reference-section" id="referenceSection">
                                <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                                    <i class="fas fa-hashtag"></i> Reference Number *
                                </label>
                                <input type="text" name="reference_number" id="reference_number" 
                                       style="width: 100%; padding: 0.75rem; border: 2px solid #e1e5e9; border-radius: 8px;" 
                                       placeholder="Enter transaction reference">
                            </div>
                        </div>
                        
                        <div style="margin-bottom: 1.5rem;">
                            <label style="display: block; margin-bottom: 0.5rem; font-weight: bold;">
                                <i class="fas fa-money-bill"></i> Amount *
                            </label>
                            <input type="number" name="amount" value="<?php echo $allocationDetails['price']; ?>" required 
                                   style="width: 100%; padding: 0.75rem; border: 2px solid #e1e5e9; border-radius: 8px; background: #f8f9fa; font-weight: bold; color: #10b981;" 
                                   readonly>
                        </div>
                        
                        <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 1.5rem;">
                            <h5 style="color: #374151; margin-bottom: 0.5rem;">
                                <i class="fas fa-info-circle"></i> Payment Instructions
                            </h5>
                            <p style="color: #666; margin: 0; font-size: 0.9rem;">
                                After making payment through any of the methods above, please fill this form to confirm your payment. 
                                Your payment will be verified within 24 hours.
                            </p>
                        </div>
                        
                        <button type="submit" name="make_payment" class="btn btn-success" style="width: 100%; padding: 1rem; font-size: 1.1rem; font-weight: bold;">
                            <i class="fas fa-check-circle"></i> Confirm Payment
                        </button>
                    </form>
                    
                    <!-- Help Section -->
                    <div style="margin-top: 2rem; padding: 1.5rem; background: #f8fafc; border-radius: 10px; border-left: 4px solid #2c5aa0;">
                        <h4 style="color: #2c5aa0; margin-bottom: 1rem;">
                            <i class="fas fa-question-circle"></i> Need Assistance?
                        </h4>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                            <div>
                                <strong><i class="fas fa-phone"></i> Contact Support:</strong>
                                <br><a href="contact.php" style="color: #2c5aa0; text-decoration: none;">Get Help Here</a>
                            </div>
                            <div>
                                <strong><i class="fas fa-clock"></i> Payment Deadline:</strong>
                                <br><span style="color: #ef4444;">
                                    <?php echo date('M j, Y', strtotime($allocation['approval_date'] . ' +7 days')); ?>
                                </span>
                            </div>
                            <div>
                                <strong><i class="fas fa-shield-alt"></i> Secure Payment:</strong>
                                <br><span style="color: #10b981;">SSL Encrypted</span>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Online Payment Modal - FIXED VERSION -->
    <div id="paymentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-credit-card"></i> Online Payment</h3>
                <button type="button" class="close-modal" onclick="closePaymentModal()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="card-preview">
                    <div class="card-number" id="cardPreview">**** **** **** ****</div>
                    <div class="card-details">
                        <span id="namePreview">YOUR NAME</span>
                        <span id="expiryPreview">MM/YY</span>
                    </div>
                </div>
                
                <!-- FIXED FORM - No JavaScript prevention, uses HTML5 validation -->
                <form id="cardForm" method="POST" onsubmit="return handleOnlinePaymentSubmit()">
                    <input type="text" name="card_number" class="card-input" placeholder="Card Number (16 digits)" 
                           pattern="[0-9\s]{16,19}" title="16-digit card number" required>
                    
                    <input type="text" name="card_name" class="card-input" placeholder="Cardholder Name"
                           required>
                    
                    <div class="card-input-group">
                        <input type="text" name="expiry_date" class="card-input" placeholder="MM/YY"
                               pattern="(0[1-9]|1[0-2])\/([0-9]{2})" title="MM/YY format" required>
                        
                        <input type="text" name="cvv" class="card-input" placeholder="CVV" 
                               pattern="[0-9]{3,4}" title="3 or 4 digit CVV" required>
                    </div>
                    
                    <div class="card-input-group-3">
                        <input type="password" name="card_pin" class="card-input pin-input" placeholder="PIN (4 digits)" 
                               pattern="[0-9]{4}" title="4-digit PIN" required>
                        
                        <div style="grid-column: span 2;">
                            <div style="background: #f8f9fa; padding: 1rem; border-radius: 8px;">
                                <div class="info-item">
                                    <span class="info-label">Amount to Pay:</span>
                                    <span class="info-value" style="color: #10b981; font-weight: bold;">
                                        ₦<?php echo number_format($allocationDetails['price'], 2); ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="loading-spinner" id="paymentLoading">
                        <div class="spinner"></div>
                        <p>Processing your payment...</p>
                    </div>
                    
                    <button type="submit" class="btn btn-success" style="width: 100%; padding: 1rem; font-size: 1.1rem;">
                        <i class="fas fa-lock"></i> Pay ₦<?php echo number_format($allocationDetails['price'], 2); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Payment method selection
        function selectPaymentMethod(method) {
            const paymentForm = document.getElementById('paymentForm');
            const referenceSection = document.getElementById('referenceSection');
            const referenceInput = document.getElementById('reference_number');
            
            document.getElementById('payment_method').value = method;
            
            // Show/hide reference number based on payment method
            if (method === 'online_payment') {
                paymentForm.classList.add('online-payment-active');
                referenceInput.removeAttribute('required');
            } else {
                paymentForm.classList.remove('online-payment-active');
                referenceInput.setAttribute('required', 'required');
            }
            
            // Highlight selected card
            document.querySelectorAll('.payment-card').forEach(card => {
                card.style.borderColor = '#e5e7eb';
            });
            if (method === 'bank_transfer') {
                document.querySelector('.payment-card.bank').style.borderColor = '#2c5aa0';
            } else if (method === 'online_payment') {
                document.querySelector('.payment-card.online').style.borderColor = '#10b981';
            } else if (method === 'mobile_money') {
                document.querySelector('.payment-card.mobile').style.borderColor = '#f59e0b';
            }
        }
        
        // Modal functions
        function openPaymentModal() {
            document.getElementById('paymentModal').style.display = 'block';
        }
        
        function closePaymentModal() {
            document.getElementById('paymentModal').style.display = 'none';
            // Reset the form when closing modal
            document.getElementById('cardForm').reset();
            updateCardPreview();
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('paymentModal');
            if (event.target === modal) {
                closePaymentModal();
            }
        }
        
        // Card formatting functions
        function formatCardNumber(input) {
            let value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            let formattedValue = value.replace(/(\d{4})/g, '$1 ').trim();
            input.value = formattedValue.substring(0, 19);
        }
        
        function formatExpiryDate(input) {
            let value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            input.value = value.substring(0, 5);
        }
        
        function formatCVV(input) {
            input.value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '').substring(0, 4);
        }
        
        function formatPin(input) {
            input.value = input.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '').substring(0, 4);
        }
        
        // Update card preview
        function updateCardPreview() {
            const cardNumber = document.querySelector('input[name="card_number"]').value || '**** **** **** ****';
            const cardName = document.querySelector('input[name="card_name"]').value.toUpperCase() || 'YOUR NAME';
            const expiryDate = document.querySelector('input[name="expiry_date"]').value || 'MM/YY';
            
            document.getElementById('cardPreview').textContent = cardNumber;
            document.getElementById('namePreview').textContent = cardName;
            document.getElementById('expiryPreview').textContent = expiryDate;
        }

        // SIMPLE FORM HANDLER - Just shows loading state, allows natural submission
        function handleOnlinePaymentSubmit() {
            console.log('Online payment form submitting to server...');
            
            // Show loading state
            document.getElementById('paymentLoading').style.display = 'block';
            const submitButton = document.querySelector('#cardForm button[type="submit"]');
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            // Allow the form to submit naturally
            return true;
        }
        
        // Form validation for main payment form
        document.getElementById('paymentForm')?.addEventListener('submit', function(e) {
            const paymentMethod = document.getElementById('payment_method').value;
            const referenceNumber = document.getElementById('reference_number').value;
            const submitButton = this.querySelector('button[type="submit"]');
            
            if (!paymentMethod) {
                e.preventDefault();
                alert('Please select a payment method.');
                return;
            }
            
            // Only require reference number for non-online payments
            if (paymentMethod !== 'online_payment' && (!referenceNumber || referenceNumber.length < 3)) {
                e.preventDefault();
                alert('Please enter a valid reference number.');
                return;
            }
            
            // Prevent double submission
            if (submitButton.disabled) {
                e.preventDefault();
                return;
            }
            
            // Disable submit button to prevent double submission
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        });
        
        // Initialize form state
        document.addEventListener('DOMContentLoaded', function() {
            selectPaymentMethod('');
        });
    </script>
</body>
</html>