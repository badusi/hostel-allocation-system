<?php
session_start();
require_once __DIR__ . '/includes/functions.php';

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['send_message'])) {
    $name = sanitize($_POST['name']);
    $email = sanitize($_POST['email']);
    $phone = sanitize($_POST['phone']);
    $subject = sanitize($_POST['subject']);
    $messageText = sanitize($_POST['message']);
    
    if (!empty($name) && !empty($email) && !empty($subject) && !empty($messageText)) {
        // In a real system, this would send an email or save to database
        $message = 'Thank you for your message! We will get back to you within 24 hours.';
        $messageType = 'success';
    } else {
        $message = 'Please fill in all required fields.';
        $messageType = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - Federal Polytechnic Ayede</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <img src="assets/images/logo.png" alt="Federal Polytechnic Ayede Logo" onerror="this.style.display='none'">
                    <div class="logo-text">
                        <h1>Federal Polytechnic Ayede</h1>
                        <p>Hostel Allocation System</p>
                    </div>
                </div>
                <nav class="nav-links">
                    <a href="index.html">Home</a>
                    <a href="index.html#features">Features</a>
                    <a href="index.html#about">About</a>
                    <a href="contact.php" class="active">Contact</a>
                    <?php if (isLoggedIn()): ?>
                        <a href="dashboard.php" class="login-btn">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                    <?php else: ?>
                        <a href="login.php" class="login-btn">
                            <i class="fas fa-sign-in-alt"></i> Student Login
                        </a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>
    </header>

    <!-- Contact Section -->
    <section style="padding: 4rem 0; background: #f8f9fa; min-height: 100vh;">
        <div class="container">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>" style="margin-bottom: 2rem;">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div style="text-align: center; margin-bottom: 3rem;">
                <h1 style="color: #2c5aa0; font-size: 2.5rem; margin-bottom: 1rem;">Contact Us</h1>
                <p style="color: #666; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">
                    Get in touch with us for any questions, support, or assistance regarding the hostel allocation system.
                </p>
            </div>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 3rem; margin-bottom: 4rem;">
                <!-- Contact Information -->
                <div>
                    <h2 style="color: #2c5aa0; margin-bottom: 2rem;">Get in Touch</h2>
                    
                    <div class="feature-card" style="margin-bottom: 2rem;">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #2c5aa0, #1e3a8a);">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <h3>Visit Our Office</h3>
                        <p>
                            Federal Polytechnic Ayede<br>
                            Student Affairs Office<br>
                            Oyo State, Nigeria<br>
                            PMB 1010, Ayede
                        </p>
                    </div>
                    
                    <div class="feature-card" style="margin-bottom: 2rem;">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                            <i class="fas fa-phone"></i>
                        </div>
                        <h3>Call Us</h3>
                        <p>
                            <strong>Main Office:</strong> +234 803 XXX XXXX<br>
                            <strong>Hostel Office:</strong> +234 806 XXX XXXX<br>
                            <strong>Emergency:</strong> +234 809 XXX XXXX<br>
                            <small style="color: #666;">Office Hours: 8:00 AM - 5:00 PM (Mon-Fri)</small>
                        </p>
                    </div>
                    
                    <div class="feature-card" style="margin-bottom: 2rem;">
                        <div class="feature-icon" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h3>Email Us</h3>
                        <p>
                            <strong>General Inquiries:</strong><br>
                            info@fedpolyayede.edu.ng<br><br>
                            <strong>Hostel Support:</strong><br>
                            hostel@fedpolyayede.edu.ng<br><br>
                            <strong>Technical Support:</strong><br>
                            support@fedpolyayede.edu.ng
                        </p>
                    </div>
                </div>
                
                <!-- Contact Form -->
                <div>
                    <h2 style="color: #2c5aa0; margin-bottom: 2rem;">Send us a Message</h2>
                    
                    <form method="POST" style="background: white; padding: 2rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1);">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="name">
                                    <i class="fas fa-user"></i> Full Name *
                                </label>
                                <input type="text" 
                                       id="name" 
                                       name="name" 
                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>"
                                       required>
                            </div>
                            <div class="form-group">
                                <label for="email">
                                    <i class="fas fa-envelope"></i> Email Address *
                                </label>
                                <input type="email" 
                                       id="email" 
                                       name="email" 
                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                                       required>
                            </div>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label for="phone">
                                    <i class="fas fa-phone"></i> Phone Number
                                </label>
                                <input type="tel" 
                                       id="phone" 
                                       name="phone" 
                                       value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>"
                                       placeholder="e.g., 08012345678">
                            </div>
                            <div class="form-group">
                                <label for="subject">
                                    <i class="fas fa-tag"></i> Subject *
                                </label>
                                <select id="subject" name="subject" required>
                                    <option value="">Select a subject</option>
                                    <option value="hostel_application" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'hostel_application') ? 'selected' : ''; ?>>Hostel Application</option>
                                    <option value="payment_issues" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'payment_issues') ? 'selected' : ''; ?>>Payment Issues</option>
                                    <option value="technical_support" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'technical_support') ? 'selected' : ''; ?>>Technical Support</option>
                                    <option value="room_allocation" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'room_allocation') ? 'selected' : ''; ?>>Room Allocation</option>
                                    <option value="general_inquiry" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'general_inquiry') ? 'selected' : ''; ?>>General Inquiry</option>
                                    <option value="complaint" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'complaint') ? 'selected' : ''; ?>>Complaint</option>
                                    <option value="other" <?php echo (isset($_POST['subject']) && $_POST['subject'] === 'other') ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="message">
                                <i class="fas fa-comment"></i> Message *
                            </label>
                            <textarea id="message" 
                                      name="message" 
                                      rows="6" 
                                      placeholder="Please describe your inquiry or issue in detail..."
                                      required><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" name="send_message" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-paper-plane"></i> Send Message
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- FAQ Section -->
            <div style="background: white; padding: 3rem; border-radius: 15px; box-shadow: 0 5px 20px rgba(0,0,0,0.1); margin-bottom: 3rem;">
                <h2 style="color: #2c5aa0; text-align: center; margin-bottom: 3rem;">Frequently Asked Questions</h2>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem;">
                    <div>
                        <h4 style="color: #374151; margin-bottom: 1rem;">
                            <i class="fas fa-question-circle" style="color: #2c5aa0;"></i>
                            How do I apply for hostel accommodation?
                        </h4>
                        <p style="color: #666; line-height: 1.6;">
                            Log in to the student portal with your matric number and RRR last 4 digits, then select your preferred hostel and room from the available options.
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="color: #374151; margin-bottom: 1rem;">
                            <i class="fas fa-question-circle" style="color: #2c5aa0;"></i>
                            What payment methods are accepted?
                        </h4>
                        <p style="color: #666; line-height: 1.6;">
                            We accept bank transfers, online payments, and mobile money. All payment details are provided after your application is approved.
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="color: #374151; margin-bottom: 1rem;">
                            <i class="fas fa-question-circle" style="color: #2c5aa0;"></i>
                            How long does application approval take?
                        </h4>
                        <p style="color: #666; line-height: 1.6;">
                            Application review typically takes 2-3 business days. You will be notified via email and SMS once a decision is made.
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="color: #374151; margin-bottom: 1rem;">
                            <i class="fas fa-question-circle" style="color: #2c5aa0;"></i>
                            Can I change my room after allocation?
                        </h4>
                        <p style="color: #666; line-height: 1.6;">
                            Room changes are only allowed in exceptional circumstances and must be approved by the hostel administration office.
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="color: #374151; margin-bottom: 1rem;">
                            <i class="fas fa-question-circle" style="color: #2c5aa0;"></i>
                            What if I forget my login password?
                        </h4>
                        <p style="color: #666; line-height: 1.6;">
                            Your password is the last 4 digits of your school fees RRR. If you need help, contact the student affairs office with your matric number.
                        </p>
                    </div>
                    
                    <div>
                        <h4 style="color: #374151; margin-bottom: 1rem;">
                            <i class="fas fa-question-circle" style="color: #2c5aa0;"></i>
                            Are there different hostels for male and female students?
                        </h4>
                        <p style="color: #666; line-height: 1.6;">
                            Yes, we have separate hostels for male and female students. You will only see hostels available for your gender when applying.
                        </p>
                    </div>
                </div>
            </div>
            
            <!-- Office Hours -->
            <div style="background: linear-gradient(135deg, #2c5aa0, #1e3a8a); color: white; padding: 2rem; border-radius: 15px; text-align: center;">
                <h3 style="margin-bottom: 1rem;">
                    <i class="fas fa-clock"></i> Office Hours
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 2rem;">
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Student Affairs Office</h4>
                        <p style="opacity: 0.9;">Monday - Friday: 8:00 AM - 5:00 PM</p>
                        <p style="opacity: 0.9;">Saturday: 9:00 AM - 2:00 PM</p>
                        <p style="opacity: 0.9;">Sunday: Closed</p>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Hostel Office</h4>
                        <p style="opacity: 0.9;">Monday - Friday: 8:00 AM - 6:00 PM</p>
                        <p style="opacity: 0.9;">Saturday: 9:00 AM - 4:00 PM</p>
                        <p style="opacity: 0.9;">Sunday: 10:00 AM - 2:00 PM</p>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 0.5rem;">Emergency Contact</h4>
                        <p style="opacity: 0.9;">24/7 Emergency Line</p>
                        <p style="opacity: 0.9;">+234 809 XXX XXXX</p>
                        <p style="opacity: 0.9;">For urgent hostel issues</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Federal Polytechnic Ayede</h3>
                    <p>Providing quality education and comfortable accommodation for students since our establishment. We are committed to excellence in all our services.</p>
                </div>
                <div class="footer-section">
                    <h3>Quick Links</h3>
                    <p><a href="login.php">Student Login</a></p>
                    <p><a href="admin/">Admin Portal</a></p>
                    <p><a href="contact.php">Contact Us</a></p>
                    <p><a href="index.html#about">About Us</a></p>
                </div>
                <div class="footer-section">
                    <h3>Student Services</h3>
                    <p><a href="login.php">Apply for Hostel</a></p>
                    <p><a href="login.php">Check Application Status</a></p>
                    <p><a href="login.php">Make Payment</a></p>
                    <p><a href="contact.php">Get Support</a></p>
                </div>
                <div class="footer-section">
                    <h3>Contact Info</h3>
                    <p><i class="fas fa-phone"></i> +234 803 XXX XXXX</p>
                    <p><i class="fas fa-envelope"></i> info@fedpolyayede.edu.ng</p>
                    <p><i class="fas fa-map-marker-alt"></i> Ayede, Oyo State, Nigeria</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 Federal Polytechnic Ayede. All rights reserved. | Designed with ❤️ for our students</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const name = document.getElementById('name').value.trim();
            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value;
            const message = document.getElementById('message').value.trim();
            
            if (!name || !email || !subject || !message) {
                e.preventDefault();
                alert('Please fill in all required fields.');
                return;
            }
            
            if (message.length < 10) {
                e.preventDefault();
                alert('Please provide a more detailed message (at least 10 characters).');
                return;
            }
        });
        
        // Phone number formatting
        document.getElementById('phone').addEventListener('input', function(e) {
            let value = e.target.value.replace(/[^0-9+]/g, '');
            e.target.value = value;
        });
    </script>
</body>
</html>
