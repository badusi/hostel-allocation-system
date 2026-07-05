<?php
session_start();
require_once __DIR__ . '/includes/functions.php';
requireLogin();

$student = getStudentInfo($_SESSION['student_id']);
$allocation = hasActiveAllocation($_SESSION['student_id']);
$canUpdateMatric = empty($student['matric_number']) || empty($student['level']);

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_profile'])) {
    $phone = sanitize($_POST['phone']);
    $email = sanitize($_POST['email']);
    $id = $_SESSION['student_id'];

    // Debug: Check what's being posted
    error_log("=== PROFILE UPDATE STARTED ===");
    error_log("POST data: " . print_r($_POST, true));
    error_log("Can update matric: " . ($canUpdateMatric ? 'YES' : 'NO'));
    error_log("Student ID: " . $id);

    if (empty($phone) || empty($email)) {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
        error_log("Validation failed: Empty phone or email");
    } else {
        $db = new Database();
        
        if ($canUpdateMatric) {
            // Student can update matric and level
            $matric_number = sanitize($_POST['matric_number']);
            $level = sanitize($_POST['level']);

            error_log("Matric from form: " . $matric_number);
            error_log("Level from form: " . $level);

            // SIMPLIFIED MATRIC VALIDATION - Only basic checks
            if (empty($matric_number)) {
                $message = 'Matric number is required.';
                $messageType = 'error';
                error_log("Validation failed: Empty matric number");
            } elseif (strlen($matric_number) < 3) {
                $message = 'Matric number must be at least 3 characters long.';
                $messageType = 'error';
                error_log("Validation failed: Matric number too short");
            } elseif (empty($level)) {
                $message = 'Please select your level.';
                $messageType = 'error';
                error_log("Validation failed: Empty level");
            } else {
                // Check if matric number already exists for another student
                $db->query('SELECT id FROM students WHERE matric_number = :matric AND id != :id');
                $db->bind(':matric', $matric_number);
                $db->bind(':id', $id);
                $existing = $db->single();
                
                if ($existing) {
                    $message = 'This matric number is already registered by another student.';
                    $messageType = 'error';
                    error_log("Validation failed: Duplicate matric number");
                } else {
                    // Update with matric and level
                    $db->query('UPDATE students 
                                SET phone = :phone, 
                                    email = :email, 
                                    matric_number = :matric, 
                                    level = :level,
                                    updated_at = NOW()
                                WHERE id = :id');
                    $db->bind(':phone', $phone);
                    $db->bind(':email', $email);
                    $db->bind(':matric', $matric_number);
                    $db->bind(':level', $level);
                    $db->bind(':id', $id);
                    error_log("Using UPDATE query with matric and level: " . $matric_number);
                }
            }
        } else {
            // Student can only update phone and email
            $db->query('UPDATE students 
                        SET phone = :phone, 
                            email = :email,
                            updated_at = NOW()
                        WHERE id = :id');
            $db->bind(':phone', $phone);
            $db->bind(':email', $email);
            $db->bind(':id', $id);
            error_log("Using UPDATE query for phone and email only");
        }

        // Only execute if no validation errors
        if (empty($message)) {
            error_log("Executing update query for ID: " . $id);
            
            try {
                if ($db->execute()) {
                    $rows_affected = $db->rowCount();
                    error_log("Rows affected: " . $rows_affected);
                    
                    if ($rows_affected > 0) {
                        $message = 'Profile updated successfully!';
                        $messageType = 'success';
                        // Refresh student data
                        $student = getStudentInfo($id);
                        $canUpdateMatric = false; // After first update, disable further updates
                        
                        error_log("Profile update successful - Matric: " . ($matric_number ?? 'N/A'));
                    } else {
                        $message = 'No changes were made to your profile.';
                        $messageType = 'info';
                        error_log("No rows affected - no changes made");
                    }
                } else {
                    $message = 'Failed to update profile. Please try again.';
                    $messageType = 'error';
                    error_log("Database execution failed");
                }
            } catch (Exception $e) {
                $message = 'Database error: ' . $e->getMessage();
                $messageType = 'error';
                error_log("Database exception: " . $e->getMessage());
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Federal Polytechnic Ayede</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <div class="dashboard-header">
            <div class="container">
                <div class="dashboard-nav">
                    <h1 class="dashboard-title">
                        <i class="fas fa-user"></i> My Profile
                    </h1>
                    <div>
                        <a href="dashboard.php" style="margin-right: 1rem; color: white; text-decoration: none;">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        <a href="application-status.php" style="margin-right: 1rem; color: white; text-decoration: none;">
                            <i class="fas fa-file-alt"></i> Application Status
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
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : ($messageType === 'error' ? 'exclamation-circle' : 'info-circle'); ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="dashboard-content">
                <div class="student-info">
                    <h3><i class="fas fa-user-circle"></i> Personal Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Full Name</label>
                            <input type="text" value="<?php echo htmlspecialchars($student['full_name']); ?>" readonly style="background: #f8f9fa;">
                        </div>
                        <div class="form-group">
                            <label>Matric Number</label>
                            <input type="text" value="<?php echo htmlspecialchars($student['matric_number'] ?? 'Not set'); ?>" readonly style="background: #f8f9fa;">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Department</label>
                            <input type="text" value="<?php echo htmlspecialchars($student['department']); ?>" readonly style="background: #f8f9fa;">
                        </div>
                        <div class="form-group">
                            <label>Level</label>
                            <input type="text" value="<?php echo htmlspecialchars($student['level'] ?? 'Not set'); ?>" readonly style="background: #f8f9fa;">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Gender</label>
                            <input type="text" value="<?php echo ucfirst($student['gender']); ?>" readonly style="background: #f8f9fa;">
                        </div>
                        <div class="form-group">
                            <label>Study Mode</label>
                            <input type="text" value="<?php echo ucfirst(str_replace('_', ' ', $student['study_mode'])); ?>" readonly style="background: #f8f9fa;">
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Admission Year</label>
                            <input type="text" value="<?php echo $student['admission_year']; ?>" readonly style="background: #f8f9fa;">
                        </div>
                        <div class="form-group">
                            <label>Registration Date</label>
                            <input type="text" value="<?php echo date('M j, Y', strtotime($student['created_at'])); ?>" readonly style="background: #f8f9fa;">
                        </div>
                    </div>
                </div>
                
                <div class="allocation-section">
                    <h3><i class="fas fa-edit"></i> Update Contact Information</h3>
                    <p style="color: #666; margin-bottom: 2rem;">
                        <?php if ($canUpdateMatric): ?>
                            <strong>Important:</strong> You can set your matric number and level only once. Please ensure they are correct.
                        <?php else: ?>
                            You can update your phone number and email address. Matric number and level cannot be changed through this system.
                        <?php endif; ?>
                    </p>
                    
                    <form method="POST" id="profileForm">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">
                                    <i class="fas fa-phone"></i> Phone Number *
                                </label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?php echo htmlspecialchars($student['phone']); ?>"
                                       placeholder="e.g., 08012345678"
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i> Email Address *
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo htmlspecialchars($student['email']); ?>"
                                       placeholder="e.g., student@example.com"
                                       required>
                            </div>
                        </div>
                        
                        <?php if ($canUpdateMatric): ?>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="matric_number">
                                        <i class="fas fa-id-card"></i> Matric Number *
                                    </label>
                                    <input type="text"
                                        id="matric_number"
                                        name="matric_number"
                                        value="<?php echo htmlspecialchars($student['matric_number'] ?? ''); ?>"
                                        placeholder="e.g., cs2024010001"
                                        required
                                        minlength="3"
                                        maxlength="20">
                                    <small style="color: #666;">Enter your matric number (e.g., cs2024010001) - This can only be set once</small>
                                </div>
                                <div class="form-group">
                                    <label for="level">
                                        <i class="fas fa-layer-group"></i> Level *
                                    </label>
                                    <select id="level" name="level" required>
                                        <option value="">Select Level</option>
                                        <option value="ND 1" <?php echo ($student['level'] ?? '') === 'ND 1' ? 'selected' : ''; ?>>ND 1</option>
                                        <option value="ND 2" <?php echo ($student['level'] ?? '') === 'ND 2' ? 'selected' : ''; ?>>ND 2</option>
                                        <option value="HND 1" <?php echo ($student['level'] ?? '') === 'HND 1' ? 'selected' : ''; ?>>HND 1</option>
                                        <option value="HND 2" <?php echo ($student['level'] ?? '') === 'HND 2' ? 'selected' : ''; ?>>HND 2</option>
                                    </select>
                                    <small style="color: #666;">This can only be set once</small>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Hidden fields to preserve existing values when can't update matric -->
                            <input type="hidden" name="matric_number" value="<?php echo htmlspecialchars($student['matric_number']); ?>">
                            <input type="hidden" name="level" value="<?php echo htmlspecialchars($student['level']); ?>">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-id-card"></i> Matric Number
                                    </label>
                                    <input type="text"
                                        value="<?php echo htmlspecialchars($student['matric_number']); ?>"
                                        readonly
                                        style="background: #f8f9fa;">
                                    <small style="color: #666;">Matric number cannot be changed</small>
                                </div>
                                <div class="form-group">
                                    <label>
                                        <i class="fas fa-layer-group"></i> Level
                                    </label>
                                    <input type="text"
                                        value="<?php echo htmlspecialchars($student['level']); ?>"
                                        readonly
                                        style="background: #f8f9fa;">
                                    <small style="color: #666;">Level cannot be changed</small>
                                </div>
                            </div>
                        <?php endif; ?>

                        <button type="submit" name="update_profile" class="btn btn-primary">
                            <i class="fas fa-save"></i> 
                            <?php echo $canUpdateMatric ? 'Save Profile Information' : 'Update Contact Information'; ?>
                        </button>
                    </form>
                </div>
                
                <?php if ($allocation): ?>
                    <div class="allocation-section">
                        <h3><i class="fas fa-bed"></i> Current Accommodation Status</h3>
                        
                        <?php if ($allocation['admin_approved'] === 'pending'): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-clock"></i>
                                Your hostel application is currently under review.
                            </div>
                        <?php elseif ($allocation['admin_approved'] === 'approved'): ?>
                            <?php
                            $db = new Database();
                            $db->query('
                                SELECT h.hostel_name, r.room_number, r.room_type, r.price 
                                FROM allocations a 
                                JOIN hostels h ON a.hostel_id = h.id 
                                JOIN rooms r ON a.room_id = r.id 
                                WHERE a.id = :allocation_id
                            ');
                            $db->bind(':allocation_id', $allocation['id']);
                            $allocationDetails = $db->single();
                            ?>
                            
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                Your accommodation has been approved and confirmed!
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Hostel</label>
                                    <input type="text" value="<?php echo htmlspecialchars($allocationDetails['hostel_name']); ?>" readonly style="background: #f8f9fa;">
                                </div>
                                <div class="form-group">
                                    <label>Room Number</label>
                                    <input type="text" value="<?php echo htmlspecialchars($allocationDetails['room_number']); ?>" readonly style="background: #f8f9fa;">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Room Type</label>
                                    <input type="text" value="<?php echo ucfirst($allocationDetails['room_type']); ?>" readonly style="background: #f8f9fa;">
                                </div>
                                <div class="form-group">
                                    <label>Price per Session</label>
                                    <input type="text" value="₦<?php echo number_format($allocationDetails['price'], 2); ?>" readonly style="background: #f8f9fa;">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Payment Status</label>
                                    <input type="text" value="<?php echo ucfirst($allocation['payment_status']); ?>" readonly style="background: #f8f9fa;">
                                </div>
                                <div class="form-group">
                                    <label>Academic Session</label>
                                    <input type="text" value="<?php echo $allocation['academic_session']; ?>" readonly style="background: #f8f9fa;">
                                </div>
                            </div>
                            
                        <?php elseif ($allocation['admin_approved'] === 'rejected'): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-times-circle"></i>
                                Your hostel application was rejected. Contact the admin office for more information.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="allocation-section">
                    <h3><i class="fas fa-info-circle"></i> Important Information</h3>
                    
                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; border-left: 4px solid #2c5aa0;">
                        <h4 style="color: #2c5aa0; margin-bottom: 1rem;">Profile Update Guidelines</h4>
                        <ul style="color: #666; line-height: 1.6; margin: 0; padding-left: 1.5rem;">
                            <li>Phone number and email address can be updated anytime</li>
                            <li>Matric number and level can only be set once during first profile setup</li>
                            <li>To change other personal information (name, department), contact the admin office</li>
                            <li>Ensure your contact information is always up to date for important notifications</li>
                        </ul>
                    </div>
                    
                    <div style="margin-top: 1.5rem; text-align: center;">
                        <a href="contact.php" class="btn btn-secondary">
                            <i class="fas fa-phone"></i> Contact Admin Office
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="assets/js/main.js"></script>
    <script>
        // Phone number validation
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9+]/g, '');
            e.target.value = value;
        });
        
        // Form validation - REMOVED MATRIC VALIDATION
        document.getElementById('profileForm').addEventListener('submit', function(e) {
            const phone = document.getElementById('phone').value;
            const email = document.getElementById('email').value;
            
            // Phone validation
            const phonePattern = /^(\+234|0)[789][01]\d{8}$/;
            if (!phonePattern.test(phone.replace(/\s+/g, ''))) {
                e.preventDefault();
                alert('Please enter a valid Nigerian phone number (e.g., 08012345678)');
                return;
            }
            
            // Email validation
            const emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailPattern.test(email)) {
                e.preventDefault();
                alert('Please enter a valid email address');
                return;
            }

            <?php if ($canUpdateMatric): ?>
            // Level validation only - NO MATRIC VALIDATION
            const level = document.getElementById('level').value;
            if (!level) {
                e.preventDefault();
                alert('Please select your level');
                return;
            }
            <?php endif; ?>
        });

        // Matric number format helper
        document.getElementById('matric_number')?.addEventListener('input', function(e) {
            let value = e.target.value.toLowerCase();
            // Auto-format to lowercase
            e.target.value = value;
        });
    </script>
</body>
</html>