<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

// Get system statistics
$stats = getSystemStats();
$recentAllocations = getPendingAllocations();
$activeSession = getActiveSession();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Federal Polytechnic Ayede</title>
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
        
        .admin-header {
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
        
        .admin-header h1 {
            color: #2c5aa0;
            font-size: 1.8rem;
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 1rem;
        }
        
        .content-section {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .section-title {
            color: #2c5aa0;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .table th,
        .table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .table th {
            background: #f8fafc;
            font-weight: 600;
            color: #2c5aa0;
        }
        
        .table tr:hover {
            background: #f8fafc;
        }
        
        .badge {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }
        
        .badge-pending {
            background: #fef3c7;
            color: #92400e;
        }
        
        .badge-approved {
            background: #d1fae5;
            color: #065f46;
        }
        
        .badge-rejected {
            background: #fee2e2;
            color: #dc2626;
        }
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            color: #666;
        }
        
        .empty-state i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
        }
        
        .session-info {
            background: #f0f9ff;
            border: 1px solid #bae6fd;
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .session-info h3 {
            color: #0369a1;
            margin-bottom: 0.5rem;
        }
        
        .session-info p {
            color: #0c4a6e;
            margin: 0;
        }
        
        @media (max-width: 768px) {
            .dashboard {
                padding: 1rem;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .table {
                font-size: 0.9rem;
            }
            
            .table th,
            .table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="admin-header">
            <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
            <div class="nav-links">
                <a href="dashboard.php" class="active"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="students.php"><i class="fas fa-users"></i> Students</a>
                <a href="hostels.php"><i class="fas fa-building"></i> Hostels</a>
                <a href="allocations.php"><i class="fas fa-bed"></i> Allocations</a>
                <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <?php if ($activeSession): ?>
            <div class="session-info">
                <h3><i class="fas fa-calendar-alt"></i> Current Academic Session</h3>
                <p><strong><?php echo htmlspecialchars($activeSession['session_name']); ?></strong> 
                   (<?php echo date('M j, Y', strtotime($activeSession['start_date'])); ?> - 
                   <?php echo date('M j, Y', strtotime($activeSession['end_date'])); ?>)</p>
            </div>
        <?php endif; ?>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon" style="color: #3b82f6;">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number" style="color: #3b82f6;">
                    <?php echo $stats['totalStudents']; ?>
                </div>
                <div class="stat-label">Total Students</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #10b981;">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-number" style="color: #10b981;">
                    <?php echo $stats['totalHostels']; ?>
                </div>
                <div class="stat-label">Active Hostels</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #f59e0b;">
                    <i class="fas fa-bed"></i>
                </div>
                <div class="stat-number" style="color: #f59e0b;">
                    <?php echo $stats['totalRooms']; ?>
                </div>
                <div class="stat-label">Total Rooms</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon" style="color: #ef4444;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-number" style="color: #ef4444;">
                    <?php echo $stats['pendingAllocations']; ?>
                </div>
                <div class="stat-label">Pending Applications</div>
            </div>
        </div>
        
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-clock"></i> Recent Allocation Requests
            </h2>
            
            <?php if (empty($recentAllocations)): ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h3>No Pending Allocations</h3>
                    <p>All allocation requests have been processed.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Matric Number</th>
                                <th>Hostel</th>
                                <th>Room</th>
                                <th>Date Applied</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($recentAllocations, 0, 10) as $allocation): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($allocation['full_name']); ?></td>
                                    <td><strong><?php echo htmlspecialchars($allocation['matric_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($allocation['hostel_name']); ?></td>
                                    <td><?php echo htmlspecialchars($allocation['room_number']); ?></td>
                                    <td><?php echo date('M j, Y', strtotime($allocation['allocation_date'])); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $allocation['admin_approved']; ?>">
                                            <?php echo ucfirst($allocation['admin_approved']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <?php if (count($recentAllocations) > 10): ?>
                    <div style="text-align: center; margin-top: 1rem;">
                        <a href="allocations.php" style="color: #2c5aa0; text-decoration: none;">
                            View all <?php echo count($recentAllocations); ?> pending allocations →
                        </a>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-info-circle"></i> Quick Actions
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                <a href="students.php" style="text-decoration: none;">
                    <div style="background: #f0f9ff; padding: 1.5rem; border-radius: 10px; text-align: center; border: 2px solid #bae6fd; transition: all 0.3s ease;">
                        <i class="fas fa-user-plus" style="font-size: 2rem; color: #0369a1; margin-bottom: 0.5rem;"></i>
                        <h4 style="color: #0369a1; margin: 0;">Add Student</h4>
                    </div>
                </a>
                
                <a href="hostels.php" style="text-decoration: none;">
                    <div style="background: #f0fdf4; padding: 1.5rem; border-radius: 10px; text-align: center; border: 2px solid #bbf7d0; transition: all 0.3s ease;">
                        <i class="fas fa-building" style="font-size: 2rem; color: #059669; margin-bottom: 0.5rem;"></i>
                        <h4 style="color: #059669; margin: 0;">Manage Hostels</h4>
                    </div>
                </a>
                
                <a href="allocations.php" style="text-decoration: none;">
                    <div style="background: #fffbeb; padding: 1.5rem; border-radius: 10px; text-align: center; border: 2px solid #fde68a; transition: all 0.3s ease;">
                        <i class="fas fa-check-circle" style="font-size: 2rem; color: #d97706; margin-bottom: 0.5rem;"></i>
                        <h4 style="color: #d97706; margin: 0;">Review Applications</h4>
                    </div>
                </a>
                
                <a href="reports.php" style="text-decoration: none;">
                    <div style="background: #fef2f2; padding: 1.5rem; border-radius: 10px; text-align: center; border: 2px solid #fecaca; transition: all 0.3s ease;">
                        <i class="fas fa-chart-bar" style="font-size: 2rem; color: #dc2626; margin-bottom: 0.5rem;"></i>
                        <h4 style="color: #dc2626; margin: 0;">View Reports</h4>
                    </div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>
