<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $student_type = sanitize($_POST['student_type']);
    $applicant_number = sanitize($_POST['applicant_number']);
    $matric_number = sanitize($_POST['matric_number']);
    $full_name = sanitize($_POST['full_name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $department = sanitize($_POST['department']);
    $level = sanitize($_POST['level']);
    $gender = sanitize($_POST['gender']);
    $study_mode = sanitize($_POST['study_mode']);
    $admission_year = sanitize($_POST['admission_year']);
    
    if ($student_type === 'new' && empty($applicant_number)) {
        $error = 'Applicant number is required for new students';
    } elseif ($student_type === 'returning' && empty($matric_number)) {
        $error = 'Matric number is required for returning students';
    } elseif (empty($full_name) || empty($email) || empty($phone) || empty($department) || empty($level) || empty($gender)) {
        $error = 'Please fill in all required fields';
    } else {
        // Check if student already exists
        $db = new Database();
        
        if ($student_type === 'new') {
            $db->query('SELECT id FROM students WHERE applicant_number = :applicant_number');
            $db->bind(':applicant_number', $applicant_number);
        } else {
            $db->query('SELECT id FROM students WHERE matric_number = :matric_number');
            $db->bind(':matric_number', $matric_number);
        }
        
        $existingStudent = $db->single();
        
        if ($existingStudent) {
            $error = $student_type === 'new' ? 'Applicant number already registered' : 'Matric number already registered';
        } else {
            // For new students, matric number will be NULL (assigned by school later)
            if ($student_type === 'new') {
                $matric_number = null;
            }
            
            // Generate password (last 4 digits of applicant number or matric number)
            if ($student_type === 'new') {
                $password = substr($applicant_number, -4);
            } else {
                $password = substr($matric_number, -4);
            }
            
            // DEBUG: Check what values we have
            error_log("Registration Data - Level: " . $level . ", Gender: " . $gender);
            
            // CORRECTED INSERT QUERY with explicit column mapping
            $db->query('
                INSERT INTO students (
                    applicant_number, matric_number, full_name, email, phone, department, 
                    level, gender, study_mode, admission_year, password, rrr_last_digits, status, created_at
                ) 
                VALUES (
                    :applicant_number, :matric_number, :full_name, :email, :phone, :department,
                    :student_level, :gender, :study_mode, :admission_year, :password, :rrr_digits, "active", NOW()
                )
            ');
            
            $db->bind(':applicant_number', $student_type === 'new' ? $applicant_number : null);
            $db->bind(':matric_number', $matric_number);
            $db->bind(':full_name', $full_name);
            $db->bind(':email', $email);
            $db->bind(':phone', $phone);
            $db->bind(':department', $department);
            $db->bind(':student_level', $level); // Unique parameter name
            $db->bind(':gender', $gender);
            $db->bind(':study_mode', $study_mode);
            $db->bind(':admission_year', $admission_year);
            $db->bind(':password', password_hash($password, PASSWORD_DEFAULT));
            $db->bind(':rrr_digits', $password);
            
            if ($db->execute()) {
                // Set success message in session and redirect to login page
                $_SESSION['registration_success'] = true;
                $_SESSION['registration_message'] = $student_type === 'new' ? 
                    'Registration successful! Please use your applicant number and the last 4 digits to login. Your matric number will be assigned by the school later.' : 
                    'Registration successful! Please use your matric number and the last 4 digits to login.';
                
                header('Location: login.php');
                exit();
            } else {
                $error = 'Registration failed. Please try again.';
                error_log("Registration failed - check database structure");
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
    <title>Student Registration - Federal Polytechnic Ayede</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <form class="login-form" method="POST">
            <div style="text-align: center; margin-bottom: 2rem;">
                <img src="assets/images/logo.png" alt="Federal Polytechnic Ayede" style="width: 80px; height: 80px; margin-bottom: 1rem;" onerror="this.style.display='none'">
                <h2>Student Registration</h2>
                <p style="color: #666; margin-bottom: 0;">Federal Polytechnic Ayede<br>Hostel Allocation System</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="student_type">
                    <i class="fas fa-user-graduate"></i> Student Type *
                </label>
                <select id="student_type" name="student_type" required onchange="toggleStudentFields()">
                    <option value="">Select student type</option>
                    <option value="new" <?php echo (isset($_POST['student_type']) && $_POST['student_type'] === 'new') ? 'selected' : ''; ?>>New Student (Applicant)</option>
                    <option value="returning" <?php echo (isset($_POST['student_type']) && $_POST['student_type'] === 'returning') ? 'selected' : ''; ?>>Returning Student (Matric)</option>
                </select>
            </div>
            
            <div id="new_student_fields" style="display: none;">
                <div class="form-group">
                    <label for="applicant_number">
                        <i class="fas fa-id-card"></i> Applicant Number *
                    </label>
                    <input type="text" 
                           id="applicant_number" 
                           name="applicant_number" 
                           placeholder="e.g., NDDFTAPLCSC109801"
                           value="<?php echo isset($_POST['applicant_number']) ? htmlspecialchars($_POST['applicant_number']) : ''; ?>"
                           pattern="[A-Z0-9]{16,18}"
                           title="Format: NDDFTAPLCSC109801 (Full-time) or NDDPTAPLCSC109801 (Part-time)">
                    <small style="color: #666; font-size: 0.8rem;">
                        Format: NDDFTAPLCSC109801 (Full-time) or NDDPTAPLCSC109801 (Part-time)
                    </small>
                </div>
            </div>
            
            <div id="returning_student_fields" style="display: none;">
                <div class="form-group">
                    <label for="matric_number">
                        <i class="fas fa-id-card"></i> Matric Number *
                    </label>
                    <input type="text" 
                           id="matric_number" 
                           name="matric_number" 
                           placeholder="e.g., cs202001001"
                           value="<?php echo isset($_POST['matric_number']) ? htmlspecialchars($_POST['matric_number']) : ''; ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label for="full_name">
                    <i class="fas fa-user"></i> Full Name *
                </label>
                <input type="text" 
                       id="full_name" 
                       name="full_name" 
                       placeholder="Enter your full name"
                       value="<?php echo isset($_POST['full_name']) ? htmlspecialchars($_POST['full_name']) : ''; ?>"
                       required>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i> Email Address *
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           placeholder="e.g., student@example.com"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                           required>
                </div>
                <div class="form-group">
                    <label for="phone">
                        <i class="fas fa-phone"></i> Phone Number *
                    </label>
                    <input type="tel" 
                           id="phone" 
                           name="phone" 
                           placeholder="e.g., 08012345678"
                           value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                           required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="department">
                        <i class="fas fa-building"></i> Department *
                    </label>
                    <select id="department" name="department" required>
                        <option value="">Select department</option>
                        <option value="Computer Science" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Computer Science') ? 'selected' : ''; ?>>Computer Science</option>
                        <option value="Software Engineering" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Software Engineering') ? 'selected' : ''; ?>>Software Engineering</option>
                        <option value="Electrical Engineering" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Electrical Engineering') ? 'selected' : ''; ?>>Electrical Engineering</option>
                        <option value="Mechanical Engineering" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Mechanical Engineering') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                        <option value="Civil Engineering" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Civil Engineering') ? 'selected' : ''; ?>>Civil Engineering</option>
                        <option value="Business Administration" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Business Administration') ? 'selected' : ''; ?>>Business Administration</option>
                        <option value="Mass Communication" <?php echo (isset($_POST['department']) && $_POST['department'] === 'Mass Communication') ? 'selected' : ''; ?>>Mass Communication</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="level">
                        <i class="fas fa-graduation-cap"></i> Level *
                    </label>
                    <select id="level" name="level" required>
                        <option value="">Select level</option>
                        <option value="ND 1" <?php echo (isset($_POST['level']) && $_POST['level'] === 'ND 1') ? 'selected' : ''; ?>>ND 1</option>
                        <option value="ND 2" <?php echo (isset($_POST['level']) && $_POST['level'] === 'ND 2') ? 'selected' : ''; ?>>ND 2</option>
                        <option value="HND 1" <?php echo (isset($_POST['level']) && $_POST['level'] === 'HND 1') ? 'selected' : ''; ?>>HND 1</option>
                        <option value="HND 2" <?php echo (isset($_POST['level']) && $_POST['level'] === 'HND 2') ? 'selected' : ''; ?>>HND 2</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label for="gender">
                        <i class="fas fa-venus-mars"></i> Gender *
                    </label>
                    <select id="gender" name="gender" required>
                        <option value="">Select gender</option>
                        <option value="male" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'male') ? 'selected' : ''; ?>>Male</option>
                        <option value="female" <?php echo (isset($_POST['gender']) && $_POST['gender'] === 'female') ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="study_mode">
                        <i class="fas fa-clock"></i> Study Mode *
                    </label>
                    <select id="study_mode" name="study_mode" required>
                        <option value="">Select study mode</option>
                        <option value="full_time" <?php echo (isset($_POST['study_mode']) && $_POST['study_mode'] === 'full_time') ? 'selected' : ''; ?>>Full Time</option>
                        <option value="part_time" <?php echo (isset($_POST['study_mode']) && $_POST['study_mode'] === 'part_time') ? 'selected' : ''; ?>>Part Time</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label for="admission_year">
                    <i class="fas fa-calendar"></i> Admission Year *
                </label>
                <select id="admission_year" name="admission_year" required>
                    <option value="">Select admission year</option>
                    <?php for ($year = date('Y'); $year >= 2020; $year--): ?>
                        <option value="<?php echo $year; ?>" <?php echo (isset($_POST['admission_year']) && $_POST['admission_year'] == $year) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endfor; ?>
                </select>
            </div>
            
            <button type="submit" class="login-btn-form">
                <i class="fas fa-user-plus"></i> Register
            </button>
            
            <div style="text-align: center; margin-top: 2rem;">
                <p style="color: #666; font-size: 0.9rem;">
                    Already have an account? 
                    <a href="login.php" style="color: #2c5aa0; text-decoration: none;">
                        <i class="fas fa-sign-in-alt"></i> Login here
                    </a>
                </p>
                <p style="margin-top: 1rem;">
                    <a href="index.html" style="color: #666; text-decoration: none;">
                        <i class="fas fa-arrow-left"></i> Back to Home
                    </a>
                </p>
            </div>
        </form>
    </div>
    
    <script>
        function toggleStudentFields() {
            const studentType = document.getElementById('student_type').value;
            const newStudentFields = document.getElementById('new_student_fields');
            const returningStudentFields = document.getElementById('returning_student_fields');
            
            newStudentFields.style.display = studentType === 'new' ? 'block' : 'none';
            returningStudentFields.style.display = studentType === 'returning' ? 'block' : 'none';
            
            // Set required attributes
            const applicantNumber = document.getElementById('applicant_number');
            const matricNumber = document.getElementById('matric_number');
            
            if (applicantNumber) applicantNumber.required = studentType === 'new';
            if (matricNumber) matricNumber.required = studentType === 'returning';
        }
        
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleStudentFields();
        });
        
        // Phone number validation
        document.getElementById('phone').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9+]/g, '');
        });
        
        // Applicant number validation - auto-format to uppercase
        document.getElementById('applicant_number').addEventListener('input', function(e) {
            e.target.value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
        });
        
        // Matric number validation - auto-format to lowercase
        document.getElementById('matric_number').addEventListener('input', function(e) {
            e.target.value = e.target.value.toLowerCase().replace(/[^a-z0-9]/g, '');
        });
    </script>
</body>
</html>