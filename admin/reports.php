<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$stats = getSystemStats();
$allocations = getAllAllocations();
$hostels = getAllHostels();

// Calculate additional statistics
$maleStudents = 0;
$femaleStudents = 0;
$departmentStats = [];

$db = new Database();
$db->query('SELECT gender, department FROM students');
$students = $db->resultset();

foreach ($students as $student) {
    if ($student['gender'] === 'male') $maleStudents++;
    if ($student['gender'] === 'female') $femaleStudents++;
    
    $dept = $student['department'];
    if (!isset($departmentStats[$dept])) {
        $departmentStats[$dept] = 0;
    }
    $departmentStats[$dept]++;
}

// Hostel occupancy stats
$hostelStats = [];
foreach ($hostels as $hostel) {
    $occupancyRate = $hostel['total_capacity'] > 0 ? 
        (($hostel['total_capacity'] - $hostel['available_spaces']) / $hostel['total_capacity']) * 100 : 0;
    
    $hostelStats[] = [
        'name' => $hostel['hostel_name'],
        'total_capacity' => $hostel['total_capacity'],
        'occupied' => $hostel['total_capacity'] - $hostel['available_spaces'],
        'available' => $hostel['available_spaces'],
        'occupancy_rate' => round($occupancyRate, 1)
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports - Federal Polytechnic Ayede</title>
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
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: #f8fafc;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
            border: 2px solid #e2e8f0;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }
        
        .chart-container {
            background: #f8fafc;
            padding: 2rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }
        
        .progress-bar {
            background: #e2e8f0;
            border-radius: 10px;
            height: 20px;
            margin: 0.5rem 0;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
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
        
        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background: #2c5aa0;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1e3d6f;
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
        }
    </style>
</head>
<body>
    <div class="dashboard">
        <div class="admin-header">
            <h1><i class="fas fa-chart-bar"></i> Reports & Analytics</h1>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="students.php"><i class="fas fa-users"></i> Students</a>
                <a href="hostels.php"><i class="fas fa-building"></i> Hostels</a>
                <a href="allocations.php"><i class="fas fa-bed"></i> Allocations</a>
                <a href="reports.php" class="active"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <!-- System Overview -->
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-chart-pie"></i> System Overview
            </h2>
            
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number" style="color: #3b82f6;"><?php echo $stats['totalStudents']; ?></div>
                    <div class="stat-label">Total Students</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #10b981;"><?php echo $stats['totalHostels']; ?></div>
                    <div class="stat-label">Active Hostels</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #f59e0b;"><?php echo $stats['totalRooms']; ?></div>
                    <div class="stat-label">Total Rooms</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #ef4444;"><?php echo $stats['pendingAllocations']; ?></div>
                    <div class="stat-label">Pending Applications</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #8b5cf6;"><?php echo $stats['approvedAllocations']; ?></div>
                    <div class="stat-label">Approved Allocations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number" style="color: #06b6d4;"><?php echo $stats['availableSpaces']; ?></div>
                    <div class="stat-label">Available Spaces</div>
                </div>
            </div>
        </div>
        
        <!-- Gender Distribution -->
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-users"></i> Student Demographics
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 2rem;">
                <div>
                    <h4 style="margin-bottom: 1rem; color: #2c5aa0;">Gender Distribution</h4>
                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Male Students</span>
                            <span><?php echo $maleStudents; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="background: #3b82f6; width: <?php echo $stats['totalStudents'] > 0 ? ($maleStudents / $stats['totalStudents']) * 100 : 0; ?>%;"></div>
                        </div>
                    </div>
                    <div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>Female Students</span>
                            <span><?php echo $femaleStudents; ?></span>
                        </div>
                        <div class="progress-bar">
                            <div class="progress-fill" style="background: #ec4899; width: <?php echo $stats['totalStudents'] > 0 ? ($femaleStudents / $stats['totalStudents']) * 100 : 0; ?>%;"></div>
                        </div>
                    </div>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 1rem; color: #2c5aa0;">Department Distribution</h4>
                    <?php foreach ($departmentStats as $dept => $count): ?>
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span><?php echo htmlspecialchars($dept); ?></span>
                                <span><?php echo $count; ?></span>
                            </div>
                            <div class="progress-bar">
                                <div class="progress-fill" style="background: #10b981; width: <?php echo $stats['totalStudents'] > 0 ? ($count / $stats['totalStudents']) * 100 : 0; ?>%;"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        
        <!-- Hostel Occupancy -->
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-building"></i> Hostel Occupancy Report
            </h2>
            
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Hostel Name</th>
                            <th>Total Capacity</th>
                            <th>Occupied</th>
                            <th>Available</th>
                            <th>Occupancy Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hostelStats as $hostel): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($hostel['name']); ?></strong></td>
                                <td><?php echo $hostel['total_capacity']; ?></td>
                                <td><?php echo $hostel['occupied']; ?></td>
                                <td><?php echo $hostel['available']; ?></td>
                                <td>
                                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                                        <div class="progress-bar" style="width: 100px; height: 15px;">
                                            <div class="progress-fill" style="background: <?php echo $hostel['occupancy_rate'] > 80 ? '#ef4444' : ($hostel['occupancy_rate'] > 60 ? '#f59e0b' : '#10b981'); ?>; width: <?php echo $hostel['occupancy_rate']; ?>%;"></div>
                                        </div>
                                        <span><?php echo $hostel['occupancy_rate']; ?>%</span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($hostel['occupancy_rate'] >= 100): ?>
                                        <span style="color: #ef4444; font-weight: bold;">Full</span>
                                    <?php elseif ($hostel['occupancy_rate'] >= 80): ?>
                                        <span style="color: #f59e0b; font-weight: bold;">Nearly Full</span>
                                    <?php else: ?>
                                        <span style="color: #10b981; font-weight: bold;">Available</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recent Activity -->
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-clock"></i> Recent Allocation Activity
            </h2>
            
            <?php if (empty($allocations)): ?>
                <div style="text-align: center; padding: 3rem; color: #666;">
                    <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 1rem; color: #d1d5db;"></i>
                    <h3>No Allocation Activity</h3>
                    <p>No allocation requests have been made yet.</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Student</th>
                                <th>Hostel</th>
                                <th>Room</th>
                                <th>Status</th>
                                <th>Processed By</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($allocations, 0, 20) as $allocation): ?>
                                <tr>
                                    <td><?php echo date('M j, Y', strtotime($allocation['allocation_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($allocation['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($allocation['hostel_name']); ?></td>
                                    <td><?php echo htmlspecialchars($allocation['room_number']); ?></td>
                                    <td>
                                        <span style="padding: 0.25rem 0.75rem; border-radius: 20px; font-size: 0.8rem; font-weight: 500; 
                                                     background: <?php echo $allocation['admin_approved'] === 'approved' ? '#d1fae5' : ($allocation['admin_approved'] === 'rejected' ? '#fee2e2' : '#fef3c7'); ?>; 
                                                     color: <?php echo $allocation['admin_approved'] === 'approved' ? '#065f46' : ($allocation['admin_approved'] === 'rejected' ? '#dc2626' : '#92400e'); ?>;">
                                            <?php echo ucfirst($allocation['admin_approved']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo $allocation['approved_by_name'] ? htmlspecialchars($allocation['approved_by_name']) : 'N/A'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Export Options -->
        <div class="content-section">
            <h2 class="section-title">
                <i class="fas fa-download"></i> Export Reports
            </h2>
            
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
                <button class="btn btn-primary" onclick="exportReport('students')">
                    <i class="fas fa-users"></i> Export Students List
                </button>
                <button class="btn btn-primary" onclick="exportReport('allocations')">
                    <i class="fas fa-bed"></i> Export Allocations
                </button>
                <button class="btn btn-primary" onclick="exportReport('hostels')">
                    <i class="fas fa-building"></i> Export Hostels Report
                </button>
                <button class="btn btn-primary" onclick="exportReport('occupancy')">
                    <i class="fas fa-chart-bar"></i> Export Occupancy Report
                </button>
            </div>
        </div>
    </div>
    
    <script>
        function exportReport(type) {
            // In a real system, this would generate and download reports
            alert(`Exporting ${type} report... (Feature coming soon)`);
        }
    </script>
</body>
</html>
