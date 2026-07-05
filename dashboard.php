<?php
session_start();
require_once 'includes/functions.php';
requireLogin();

$student = getStudentInfo($_SESSION['student_id']);
$allocation = hasActiveAllocation($_SESSION['student_id']);
$availableHostels = getAvailableHostels($student['gender']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Federal Polytechnic Ayede</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            color: #333;
        }
        
        .dashboard {
            min-height: 100vh;
            padding: 2rem;
        }
        
        .header {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .welcome-text h1 {
            color: #2c5aa0;
            margin-bottom: 0.5rem;
        }
        
        .welcome-text p {
            color: #666;
        }
        
        .nav-links {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .nav-links a {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.75rem 1.25rem;
            background-color: #f0f4f8;
            color: #333;
            border-radius: 0.5rem;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .nav-links a:hover {
            background-color: #d1d5db;
        }

        .nav-links a.active {
            background-color: #2c5aa0;
            color: white;
        }
        
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 2rem;
        }
        
        .main-content {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .sidebar {
            display: flex;
            flex-direction: column;
            gap: 2rem;
        }
        
        .card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .card-title {
            color: #2c5aa0;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .status-card {
            text-align: center;
            padding: 2rem;
        }
        
        .status-icon {
            font-size: 4rem;
            margin-bottom: 1rem;
        }
        
        .status-pending {
            color: #f59e0b;
        }
        
        .status-approved {
            color: #10b981;
        }
        
        .status-rejected {
            color: #ef4444;
        }
        
        .btn {
            padding: 1rem 2rem;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
            width: 100%;
        }
        
        .btn-primary {
            background: #2c5aa0;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1e3d6f;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .info-item {
            display: flex;
            justify-content: space-between;
            padding: 0.75rem 0;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .info-item:last-child {
            border-bottom: none;
        }
        
        .info-label {
            color: #666;
            font-weight: 500;
        }
        
        .info-value {
            font-weight: 600;
        }
        
        .hostel-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .hostel-card {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            transition: all 0.3s ease;
        }
        
        .hostel-card:hover {
            border-color: #2c5aa0;
            transform: translateY(-2px);
        }
        
        .hostel-name {
            color: #2c5aa0;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1rem;
        }
        
        @media (max-width: 768px) {
            .dashboard {
                padding: 1rem;
            }
            
            .header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .hostel-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="header">
            <div class="welcome-text">
                <h1>Welcome, <?php echo htmlspecialchars($student['full_name']); ?>!</h1>
                <p>Matric Number: <strong><?php echo htmlspecialchars($student['matric_number']); ?></strong></p>
            </div>
            <div class="nav-links">
                <a href="dashboard.php" class="active"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> Profile</a>
                <a href="application-status.php"><i class="fas fa-clipboard-list"></i> Application Status</a>
                <a href="payment.php"><i class="fas fa-credit-card"></i> Payment</a>
                <a href="contact.php"><i class="fas fa-envelope"></i> Contact</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="content-grid">
            <div class="main-content">
                <?php if (!$allocation): ?>
                    <!-- No Application Yet -->
                    <div class="card">
                        <h2 class="card-title">
                            <i class="fas fa-home"></i> Apply for Hostel Accommodation
                        </h2>
                        
                        <div style="text-align: center; padding: 2rem;">
                            <i class="fas fa-building" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                            <h3 style="margin-bottom: 1rem;">No Active Application</h3>
                            <p style="color: #666; margin-bottom: 2rem;">
                                You haven't applied for hostel accommodation yet. Browse available hostels below and submit your application.
                            </p>
                            <a href="#hostels" class="btn btn-primary">
                                <i class="fas fa-search"></i> Browse Available Hostels
                            </a>
                        </div>
                    </div>
                    
                    <!-- Available Hostels -->
                    <div class="card" id="hostels">
                        <h2 class="card-title">
                            <i class="fas fa-building"></i> Available Hostels (<?php echo ucfirst($student['gender']); ?>)
                        </h2>
                        
                        <?php if (empty($availableHostels)): ?>
                            <div style="text-align: center; padding: 2rem; color: #666;">
                                <i class="fas fa-exclamation-triangle" style="font-size: 3rem; margin-bottom: 1rem; color: #f59e0b;"></i>
                                <h3>No Available Hostels</h3>
                                <p>There are currently no available hostels for <?php echo $student['gender']; ?> students.</p>
                            </div>
                        <?php else: ?>
                            <div class="hostel-grid">
                                <?php foreach ($availableHostels as $hostel): ?>
                                    <div class="hostel-card">
                                        <div class="hostel-name"><?php echo htmlspecialchars($hostel['hostel_name']); ?></div>
                                        
                                        <div class="info-item">
                                            <span class="info-label">Available Spaces:</span>
                                            <span class="info-value"><?php echo $hostel['available_spaces']; ?></span>
                                        </div>
                                        
                                        <div class="info-item">
                                            <span class="info-label">Price per Session:</span>
                                            <span class="info-value">₦<?php echo number_format($hostel['price_per_session'], 2); ?></span>
                                        </div>
                                        
                                        <div class="info-item">
                                            <span class="info-label">Facilities:</span>
                                            <span class="info-value"><?php echo htmlspecialchars($hostel['facilities']); ?></span>
                                        </div>
                                        
                                        <?php if ($hostel['description']): ?>
                                            <p style="color: #666; margin: 1rem 0; font-size: 0.9rem;">
                                                <?php echo htmlspecialchars($hostel['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                        
                                        <button class="btn btn-success" onclick="selectHostel(<?php echo $hostel['id']; ?>)">
                                            <i class="fas fa-check"></i> Select This Hostel
                                        </button>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                <?php else: ?>
                    <!-- Has Application -->
                    <div class="card">
                        <h2 class="card-title">
                            <i class="fas fa-clipboard-check"></i> Your Application Status
                        </h2>
                        
                        <div class="status-card">
                            <?php if ($allocation['admin_approved'] === 'pending'): ?>
                                <i class="fas fa-clock status-icon status-pending"></i>
                                <h3 style="color: #f59e0b;">Application Pending</h3>
                                <p style="color: #666; margin: 1rem 0;">
                                    Your hostel application is being reviewed by the administration.
                                </p>
                            <?php elseif ($allocation['admin_approved'] === 'approved'): ?>
                                <i class="fas fa-check-circle status-icon status-approved"></i>
                                <h3 style="color: #10b981;">Application Approved!</h3>
                                <p style="color: #666; margin: 1rem 0;">
                                    Congratulations! Your hostel application has been approved.
                                </p>
                            <?php else: ?>
                                <i class="fas fa-times-circle status-icon status-rejected"></i>
                                <h3 style="color: #ef4444;">Application Rejected</h3>
                                <p style="color: #666; margin: 1rem 0;">
                                    Unfortunately, your application was not approved.
                                </p>
                            <?php endif; ?>
                            
                            <a href="application-status.php" class="btn btn-primary">
                                <i class="fas fa-eye"></i> View Full Details
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Student Info -->
                <div class="card">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i> Student Information
                    </h3>
                    
                    <div class="info-item">
                        <span class="info-label">Department:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['department']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Level:</span>
                        <span class="info-value"><?php echo htmlspecialchars($student['level']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Gender:</span>
                        <span class="info-value"><?php echo ucfirst($student['gender']); ?></span>
                    </div>
                    
                    <div class="info-item">
                        <span class="info-label">Study Mode:</span>
                        <span class="info-value"><?php echo ucfirst(str_replace('_', ' ', $student['study_mode'])); ?></span>
                    </div>
                    
                    <div style="margin-top: 1.5rem;">
                        <a href="profile.php" class="btn btn-primary">
                            <i class="fas fa-edit"></i> Update Profile
                        </a>
                    </div>
                </div>
                
                <!-- Quick Actions -->
                <div class="card">
                    <h3 class="card-title">
                        <i class="fas fa-bolt"></i> Quick Actions
                    </h3>
                    
                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <a href="application-status.php" class="btn" style="background: #f0f9ff; color: #0369a1; border: 2px solid #bae6fd;">
                            <i class="fas fa-clipboard-list"></i> Check Status
                        </a>
                        
                        <a href="payment.php" class="btn" style="background: #f0fdf4; color: #059669; border: 2px solid #bbf7d0;">
                            <i class="fas fa-credit-card"></i> Make Payment
                        </a>
                        
                        <a href="contact.php" class="btn" style="background: #fffbeb; color: #d97706; border: 2px solid #fde68a;">
                            <i class="fas fa-envelope"></i> Contact Support
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function selectHostel(hostelId) {
        if (confirm('Are you sure you want to apply for this hostel?')) {
            // Show room selection
            fetch(`student_get_rooms.php?hostel_id=${hostelId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.rooms.length > 0) {
                        showRoomSelection(hostelId, data.rooms);
                    } else {
                        alert('No available rooms in this hostel.');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading rooms. Please try again.');
                });
        }
    }
    
    function showRoomSelection(hostelId, rooms) {
        let roomOptions = rooms.map(room => 
            `<option value="${room.id}">Room ${room.room_number} (${room.room_type}) - ₦${parseFloat(room.price).toLocaleString()}</option>`
        ).join('');
        
        let html = `
            <div style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 1000; display: flex; align-items: center; justify-content: center;">
                <div style="background: white; padding: 2rem; border-radius: 15px; max-width: 400px; width: 90%;">
                    <h3 style="margin-bottom: 1rem; color: #2c5aa0;">Select a Room</h3>
                    <form method="POST" action="process_application.php">
                        <input type="hidden" name="hostel_id" value="${hostelId}">
                        <div style="margin-bottom: 1rem;">
                            <label style="display: block; margin-bottom: 0.5rem;">Available Rooms:</label>
                            <select name="room_id" required style="width: 100%; padding: 0.75rem; border: 2px solid #e1e5e9; border-radius: 8px;">
                                <option value="">Select a room</option>
                                ${roomOptions}
                            </select>
                        </div>
                        <div style="display: flex; gap: 1rem;">
                            <button type="button" onclick="this.closest('div').parentElement.remove()" style="flex: 1; padding: 0.75rem; background: #6b7280; color: white; border: none; border-radius: 8px; cursor: pointer;">Cancel</button>
                            <button type="submit" name="apply" style="flex: 1; padding: 0.75rem; background: #2c5aa0; color: white; border: none; border-radius: 8px; cursor: pointer;">Apply & Pay</button>
                        </div>
                    </form>
                </div>
            </div>
        `;
        
        document.body.insertAdjacentHTML('beforeend', html);
    }
</script>
</body>
</html>
