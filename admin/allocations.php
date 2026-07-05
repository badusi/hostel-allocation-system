<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$message = '';
$messageType = '';

// Handle allocation actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['approve_allocation'])) {
        $allocation_id = (int)$_POST['allocation_id'];
        if (approveAllocation($allocation_id, $_SESSION['admin_id'])) {
            $message = 'Allocation approved successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to approve allocation.';
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['reject_allocation'])) {
        $allocation_id = (int)$_POST['allocation_id'];
        $reason = sanitize($_POST['rejection_reason']);
        if (rejectAllocation($allocation_id, $_SESSION['admin_id'], $reason)) {
            $message = 'Allocation rejected successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to reject allocation.';
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['delete_allocation'])) {
        $allocation_id = (int)$_POST['allocation_id'];
        if (deleteAllocation($allocation_id)) {
            $message = 'Allocation deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to delete allocation.';
            $messageType = 'error';
        }
    }
}

$allocations = getAllAllocations();
$pendingAllocations = getPendingAllocations();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Allocations Management - Federal Polytechnic Ayede</title>
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
        
        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
            margin: 0.25rem;
        }
        
        .btn-success {
            background: #10b981;
            color: white;
        }
        
        .btn-success:hover {
            background: #059669;
        }
        
        .btn-danger {
            background: #ef4444;
            color: white;
        }
        
        .btn-danger:hover {
            background: #dc2626;
        }
        
        .btn-warning {
            background: #f59e0b;
            color: white;
        }
        
        .btn-warning:hover {
            background: #d97706;
        }
        
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        
        .modal-content {
            background-color: white;
            margin: 10% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
        }
        
        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }
        
        .close:hover {
            color: #000;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #333;
            font-weight: 500;
        }
        
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            resize: vertical;
            min-height: 100px;
        }
        
        .form-group textarea:focus {
            outline: none;
            border-color: #2c5aa0;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
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
        
        .tabs {
            display: flex;
            border-bottom: 2px solid #e2e8f0;
            margin-bottom: 2rem;
        }
        
        .tab {
            padding: 1rem 2rem;
            cursor: pointer;
            border-bottom: 2px solid transparent;
            transition: all 0.3s ease;
        }
        
        .tab.active {
            color: #2c5aa0;
            border-bottom-color: #2c5aa0;
        }
        
        .tab-content {
            display: none;
        }
        
        .tab-content.active {
            display: block;
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
            <h1><i class="fas fa-bed"></i> Allocations Management</h1>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="students.php"><i class="fas fa-users"></i> Students</a>
                <a href="hostels.php"><i class="fas fa-building"></i> Hostels</a>
                <a href="allocations.php" class="active"><i class="fas fa-bed"></i> Allocations</a>
                <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="alert alert-<?php echo $messageType; ?>">
                <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>
        
        <div class="content-section">
            <div class="tabs">
                <div class="tab active" onclick="showTab('pending')">
                    <i class="fas fa-clock"></i> Pending Allocations (<?php echo count($pendingAllocations); ?>)
                </div>
                <div class="tab" onclick="showTab('all')">
                    <i class="fas fa-list"></i> All Allocations (<?php echo count($allocations); ?>)
                </div>
            </div>
            
            <!-- Pending Allocations Tab -->
            <div id="pending" class="tab-content active">
                <?php if (empty($pendingAllocations)): ?>
                    <div class="empty-state">
                        <i class="fas fa-check-circle"></i>
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
                                    <th>Department</th>
                                    <th>Hostel</th>
                                    <th>Room</th>
                                    <th>Date Applied</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pendingAllocations as $allocation): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($allocation['full_name']); ?></td>
                                        <td><strong><?php echo htmlspecialchars($allocation['matric_number']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($allocation['department']); ?></td>
                                        <td><?php echo htmlspecialchars($allocation['hostel_name']); ?></td>
                                        <td><?php echo htmlspecialchars($allocation['room_number']); ?></td>
                                        <td><?php echo date('M j, Y', strtotime($allocation['allocation_date'])); ?></td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="allocation_id" value="<?php echo $allocation['id']; ?>">
                                                <button type="submit" name="approve_allocation" class="btn btn-success"
                                                        onclick="return confirm('Approve this allocation?')">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            
                                            <button class="btn btn-danger" onclick="openRejectModal(<?php echo $allocation['id']; ?>)">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- All Allocations Tab -->
            <div id="all" class="tab-content">
                <?php if (empty($allocations)): ?>
                    <div class="empty-state">
                        <i class="fas fa-bed"></i>
                        <h3>No Allocations Found</h3>
                        <p>No allocation requests have been made yet.</p>
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
                                    <th>Approved By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($allocations as $allocation): ?>
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
                                        <td>
                                            <?php echo $allocation['approved_by_name'] ? htmlspecialchars($allocation['approved_by_name']) : 'N/A'; ?>
                                        </td>
                                        <td>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="allocation_id" value="<?php echo $allocation['id']; ?>">
                                                <button type="submit" name="delete_allocation" class="btn btn-danger"
                                                        onclick="return confirm('Delete this allocation? This action cannot be undone.')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Reject Allocation Modal -->
    <div id="rejectModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('rejectModal')">&times;</span>
            <h2 style="color: #2c5aa0; margin-bottom: 1.5rem;">
                <i class="fas fa-times-circle"></i> Reject Allocation
            </h2>
            
            <form method="POST">
                <input type="hidden" id="rejectAllocationId" name="allocation_id">
                
                <div class="form-group">
                    <label>Reason for Rejection *</label>
                    <textarea name="rejection_reason" required 
                              placeholder="Please provide a reason for rejecting this allocation..."></textarea>
                </div>
                
                <div style="text-align: right; margin-top: 2rem;">
                    <button type="button" onclick="closeModal('rejectModal')" 
                            style="background: #6b7280; color: white; margin-right: 1rem;" class="btn">
                        Cancel
                    </button>
                    <button type="submit" name="reject_allocation" class="btn btn-danger">
                        <i class="fas fa-times"></i> Reject Allocation
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function showTab(tabName) {
            // Hide all tab contents
            const tabContents = document.querySelectorAll('.tab-content');
            tabContents.forEach(content => content.classList.remove('active'));
            
            // Remove active class from all tabs
            const tabs = document.querySelectorAll('.tab');
            tabs.forEach(tab => tab.classList.remove('active'));
            
            // Show selected tab content
            document.getElementById(tabName).classList.add('active');
            
            // Add active class to clicked tab
            event.target.classList.add('active');
        }
        
        function openRejectModal(allocationId) {
            document.getElementById('rejectAllocationId').value = allocationId;
            document.getElementById('rejectModal').style.display = 'block';
        }
        
        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }
        
        // Close modal when clicking outside
        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let modal of modals) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        }
    </script>
</body>
</html>
