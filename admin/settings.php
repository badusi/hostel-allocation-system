<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$message = '';
$messageType = '';

file_put_contents('log.txt', "Session POST: " . json_encode($_POST) . PHP_EOL, FILE_APPEND);

if (isset($_POST['add_sessionm'])) {
    file_put_contents('log.txt', "✅ Add Session Triggered!" . PHP_EOL, FILE_APPEND);
}


file_put_contents('log.txt', "Active Session POST: " . json_encode($_POST) . PHP_EOL, FILE_APPEND);

if (isset($_POST['set_active_session'])) {
    file_put_contents('log.txt', "✅ Add Active Session Triggered!" . PHP_EOL, FILE_APPEND);
}


file_put_contents('log.txt', "Delete Session POST: " . json_encode($_POST) . PHP_EOL, FILE_APPEND);

if (isset($_POST['delete_session'])) {
    file_put_contents('log.txt', "✅  Delete Session Triggered!" . PHP_EOL, FILE_APPEND);
}


// Handle session management
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_session'])) {
        $session_name = sanitize($_POST['session_name']);
        $start_date = sanitize($_POST['start_date']);
        $end_date = sanitize($_POST['end_date']);
        
        if (!empty($session_name) && !empty($start_date) && !empty($end_date)) {
            if (addSession($session_name, $start_date, $end_date)) {
                $message = 'Academic session added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to add academic session.';
                $messageType = 'error';
            }
        } else {
            $message = 'Please fill in all fields.';
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['set_active_session'])) {
        $session_id = (int)$_POST['session_id'];
        if (setActiveSession($session_id)) {
            $message = 'Active session updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update active session.';
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['delete_session'])) {
        $session_id = (int)$_POST['session_id'];
        if (deleteSession($session_id)) {
            $message = 'Academic session deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Cannot delete session. It may have associated allocations.';
            $messageType = 'error';
        }
    }
}

$sessions = getAllSessions();
$activeSession = getActiveSession();
$stats = getSystemStats();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="dashboard">
        <div class="admin-header">
            <div class="container">
                <div class="admin-nav">
                    <h1><i class="fas fa-user-shield"></i> Admin Panel</h1>
                    <div>
                        <a href="dashboard.php">Dashboard</a>
                        <a href="students.php">Students</a>
                        <a href="hostels.php">Hostels</a>
                        <a href="allocations.php">Allocations</a>
                        <a href="reports.php">Reports</a>
                        <a href="settings.php" class="active">Settings</a>
                        <span style="margin: 0 1rem; color: rgba(255,255,255,0.8);">
                            <?php echo htmlspecialchars($_SESSION['admin_name']); ?>
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
            
            <div class="admin-content">
                <!-- System Information -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3><i class="fas fa-info-circle"></i> System Information</h3>
                    </div>
                    <div class="admin-card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem;">
                            <div>
                                <h4 style="color: #2c5aa0; margin-bottom: 1rem;">System Statistics</h4>
                                <div class="info-item">
                                    <span class="info-label">Total Students:</span>
                                    <span class="info-value"><?php echo $stats['totalStudents']; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Active Hostels:</span>
                                    <span class="info-value"><?php echo $stats['totalHostels']; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Total Rooms:</span>
                                    <span class="info-value"><?php echo $stats['totalRooms']; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Pending Applications:</span>
                                    <span class="info-value"><?php echo $stats['pendingAllocations']; ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <h4 style="color: #2c5aa0; margin-bottom: 1rem;">Current Session</h4>
                                <?php if ($activeSession): ?>
                                    <div class="info-item">
                                        <span class="info-label">Session Name:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($activeSession['session_name']); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Start Date:</span>
                                        <span class="info-value"><?php echo date('M j, Y', strtotime($activeSession['start_date'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">End Date:</span>
                                        <span class="info-value"><?php echo date('M j, Y', strtotime($activeSession['end_date'])); ?></span>
                                    </div>
                                    <div class="info-item">
                                        <span class="info-label">Status:</span>
                                        <span class="info-value" style="color: #10b981;">
                                            <i class="fas fa-check-circle"></i> Active
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div style="padding: 1rem; background: #fee2e2; border-radius: 8px; border-left: 4px solid #ef4444;">
                                        <p style="color: #991b1b; margin: 0;">
                                            <i class="fas fa-exclamation-triangle"></i>
                                            No active academic session set. Please set an active session.
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div>
                                <h4 style="color: #2c5aa0; margin-bottom: 1rem;">System Status</h4>
                                <div class="info-item">
                                    <span class="info-label">Database:</span>
                                    <span class="info-value" style="color: #10b981;">
                                        <i class="fas fa-check-circle"></i> Connected
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Last Backup:</span>
                                    <span class="info-value">N/A</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">System Version:</span>
                                    <span class="info-value">v1.0.0</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">PHP Version:</span>
                                    <span class="info-value"><?php echo PHP_VERSION; ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Academic Session Management -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3><i class="fas fa-calendar-alt"></i> Academic Session Management</h3>
                        <button class="btn btn-primary" data-modal="addSessionModal">
                            <i class="fas fa-plus"></i> Add New Session
                        </button>
                    </div>
                    <div class="admin-card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Session Name</th>
                                        <th>Start Date</th>
                                        <th>End Date</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($sessions)): ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; color: #666; padding: 2rem;">
                                                No academic sessions found. Add a new session to get started.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($sessions as $session): ?>
                                            <tr>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($session['session_name']); ?></strong>
                                                </td>
                                                <td><?php echo date('M j, Y', strtotime($session['start_date'])); ?></td>
                                                <td><?php echo date('M j, Y', strtotime($session['end_date'])); ?></td>
                                                <td>
                                                    <?php if ($session['is_active'] === 'yes'): ?>
                                                        <span class="badge badge-success">Active</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-secondary">Inactive</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <div class="action-buttons">
                                                        <?php if ($session['is_active'] !== 'yes'): ?>
                                                            <form method="POST" style="display: inline;">
                                                                <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                                                <input type="hidden" name="set_active_session" value="1">
                                                                <button type="submit" name="set_active_session" class="btn btn-sm btn-success" 
                                                                        onclick="return confirm('Set this as the active session?')">
                                                                    <i class="fas fa-check"></i> Set Active
                                                                </button>
                                                            </form>
                                                        <?php endif; ?>
                                                        
                                                        <form method="POST" style="display: inline;">
                                                            <input type="hidden" name="session_id" value="<?php echo $session['id']; ?>">
                                                            <input type="hidden" name="delete_session" value="1">
                                                            <button type="submit" name="delete_session" class="btn btn-sm btn-danger" 
                                                                    onclick="return confirm('Are you sure you want to delete this session?')">
                                                                <i class="fas fa-trash"></i> Delete
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                
                <!-- Admin Account Settings -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3><i class="fas fa-user-cog"></i> Admin Account Settings</h3>
                    </div>
                    <div class="admin-card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                            <div>
                                <h4 style="color: #2c5aa0; margin-bottom: 1rem;">Current Admin</h4>
                                <div class="info-item">
                                    <span class="info-label">Username:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Full Name:</span>
                                    <span class="info-value"><?php echo htmlspecialchars($_SESSION['admin_name']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Role:</span>
                                    <span class="info-value"><?php echo ucfirst($_SESSION['admin_role']); ?></span>
                                </div>
                            </div>
                            
                            <div>
                                <h4 style="color: #2c5aa0; margin-bottom: 1rem;">Quick Actions</h4>
                                <div style="display: flex; flex-direction: column; gap: 1rem;">
                                    <button class="btn btn-secondary" onclick="alert('Password change feature coming soon')">
                                        <i class="fas fa-key"></i> Change Password
                                    </button>
                                    <button class="btn btn-secondary" onclick="alert('Backup feature coming soon')">
                                        <i class="fas fa-download"></i> Backup Database
                                    </button>
                                    <button class="btn btn-warning" onclick="clearCache()">
                                        <i class="fas fa-broom"></i> Clear Cache
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Session Modal -->
    <div id="addSessionModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4><i class="fas fa-plus"></i> Add New Academic Session</h4>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="session_name">
                            <i class="fas fa-tag"></i> Session Name *
                        </label>
                        <input type="text" 
                               id="session_name" 
                               name="session_name" 
                               placeholder="e.g., 2024/2025"
                               required>
                        <small style="color: #666;">Format: YYYY/YYYY (e.g., 2024/2025)</small>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date">
                                <i class="fas fa-calendar"></i> Start Date *
                            </label>
                            <input type="date" 
                                   id="start_date" 
                                   name="start_date" 
                                   required>
                        </div>
                        <div class="form-group">
                            <label for="end_date">
                                <i class="fas fa-calendar"></i> End Date *
                            </label>
                            <input type="date" 
                                   id="end_date" 
                                   name="end_date" 
                                   required>
                        </div>
                    </div>
                </div>
                <input type="hidden" name="add_session" value="1">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" name="add_session" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Session
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        function clearCache() {
            if (confirm('Are you sure you want to clear the system cache?')) {
                // In a real system, this would make an AJAX call to clear cache
                alert('Cache cleared successfully!');
            }
        }
        
        // Session name validation
        document.getElementById('session_name').addEventListener('input', function(e) {
            const value = e.target.value;
            const pattern = /^\d{4}\/\d{4}$/;
            
            if (value && !pattern.test(value)) {
                e.target.style.borderColor = '#ef4444';
            } else {
                e.target.style.borderColor = '#e5e7eb';
            }
        });
        
        // Date validation
        document.getElementById('end_date').addEventListener('change', function(e) {
            const startDate = document.getElementById('start_date').value;
            const endDate = e.target.value;
            
            if (startDate && endDate && new Date(endDate) <= new Date(startDate)) {
                alert('End date must be after start date');
                e.target.value = '';
            }
        });
    </script>
</body>
</html>
