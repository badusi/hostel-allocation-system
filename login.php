<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit();
}


// Check for registration success message
$registration_success = false;
$registration_message = '';

if (isset($_SESSION['registration_success']) && $_SESSION['registration_success']) {
    $registration_success = true;
    $registration_message = $_SESSION['registration_message'] ?? 'Registration successful!';
    
    // Clear the session variables
    unset($_SESSION['registration_success']);
    unset($_SESSION['registration_message']);
}


$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login_type = sanitize($_POST['login_type']);
    $identifier = sanitize($_POST['identifier']);
    $password = sanitize($_POST['password']);
    
    if (empty($identifier) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        if (authenticateStudent($identifier, $password, $login_type)) {
            header('Location: dashboard.php');
            exit();
        } else {
            $error = 'Invalid login credentials';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - Federal Polytechnic Ayede</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="login-container">
        <form class="login-form" method="POST">
            <div style="text-align: center; margin-bottom: 2rem;">
                <img src="assets/images/logo.png" alt="Federal Polytechnic Ayede" style="width: 80px; height: 80px; margin-bottom: 1rem;" onerror="this.style.display='none'">
                <h2>Student Login</h2>
                <p style="color: #666; margin-bottom: 0;">Federal Polytechnic Ayede<br>Hostel Allocation System</p>
            </div>
            
            <?php if ($registration_success): ?>
                <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $registration_message; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-error" style="margin-bottom: 1.5rem;">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="form-group">
                <label for="login_type">
                    <i class="fas fa-user-graduate"></i> Login As
                </label>
                <select id="login_type" name="login_type" required>
                    <option value="matric">Returning Student (Matric Number)</option>
                    <option value="applicant">New Student (Applicant Number)</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="identifier" id="identifier_label">
                    <i class="fas fa-id-card"></i> Matric Number
                </label>
                <input type="text" 
                       id="identifier" 
                       name="identifier" 
                       placeholder="e.g., cs202001001" 
                       value="<?php echo isset($_POST['identifier']) ? htmlspecialchars($_POST['identifier']) : ''; ?>"
                       required>
                <small style="color: #666; font-size: 0.8rem;" id="identifier_help">
                    Format: [dept][year][mode][number] (e.g., cs202001001)
                </small>
            </div>
            
            <div class="form-group">
                <label for="password">
                    <i class="fas fa-lock"></i> Password
                </label>
                <input type="password" 
                       id="password" 
                       name="password" 
                       placeholder="Enter last 4 digits of your identifier" 
                       maxlength="4"
                       required>
                <small style="color: #666; font-size: 0.8rem;" id="password_help">
                    Use the last 4 digits of your identifier
                </small>
            </div>
            
            <button type="submit" class="login-btn-form">
                <i class="fas fa-sign-in-alt"></i> Login
            </button>
            
            <div style="text-align: center; margin-top: 2rem;">
                <p style="color: #666; font-size: 0.9rem;">
                    Don't have an account? 
                    <a href="register.php" style="color: #2c5aa0; text-decoration: none;">
                        <i class="fas fa-user-plus"></i> Register here
                    </a>
                </p>
                <p style="color: #666; font-size: 0.9rem; margin-top: 0.5rem;">
                    Forgot your password? 
                    <a href="contact.php" style="color: #2c5aa0; text-decoration: none;">
                        <i class="fas fa-phone"></i> Contact Admin
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
        document.getElementById('login_type').addEventListener('change', function(e) {
            const loginType = e.target.value;
            const identifierLabel = document.getElementById('identifier_label');
            const identifierInput = document.getElementById('identifier');
            const identifierHelp = document.getElementById('identifier_help');
            const passwordHelp = document.getElementById('password_help');
            
            if (loginType === 'applicant') {
                identifierLabel.innerHTML = '<i class="fas fa-id-card"></i> Applicant Number';
                identifierInput.placeholder = 'e.g., NDDFTAPLCSC109801';
                identifierHelp.textContent = 'Enter your applicant number';
                passwordHelp.textContent = 'Use the last 4 digits of your applicant number';
            } else {
                identifierLabel.innerHTML = '<i class="fas fa-id-card"></i> Matric Number';
                identifierInput.placeholder = 'e.g., cs202001001';
                identifierHelp.textContent = 'Format: [dept][year][mode][number] (e.g., cs202001001)';
                passwordHelp.textContent = 'Use the last 4 digits of your matric number';
            }
        });
        
        // Auto-format identifier input
        document.getElementById('identifier').addEventListener('input', function(e) {
            let value = e.target.value.toLowerCase().replace(/[^a-z0-9]/g, '');
            e.target.value = value;
        });
        
        // Only allow numbers for password
        document.getElementById('password').addEventListener('input', function(e) {
            e.target.value = e.target.value.replace(/[^0-9]/g, '');
        });
    </script>
</body>
</html>