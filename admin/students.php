<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$message = '';
$messageType = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_student'])) {
        $data = [
            'matric_number' => sanitize($_POST['matric_number']),
            'full_name' => sanitize($_POST['full_name']),
            'department' => sanitize($_POST['department']),
            'level' => sanitize($_POST['level']),
            'gender' => sanitize($_POST['gender']),
            'phone' => sanitize($_POST['phone']),
            'email' => sanitize($_POST['email']),
            'rrr_last_digits' => sanitize($_POST['rrr_last_digits']),
            'admission_year' => (int)$_POST['admission_year'],
            'study_mode' => sanitize($_POST['study_mode'])
        ];
        
        if (validateMatricNumber($data['matric_number'])) {
            if (addStudent($data)) {
                $message = 'Student added successfully!';
                $messageType = 'success';
            } else {
                $message = 'Failed to add student. Matric number may already exist.';
                $messageType = 'error';
            }
        } else {
            $message = 'Invalid matric number format.';
            $messageType = 'error';
        }
    }
}

// Get students with search
$search = $_GET['search'] ?? '';
if ($search) {
    $students = searchStudents($search);
} else {
    $students = getAllStudents();
}

$departments = getDepartments();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Students Management - Federal Polytechnic Ayede</title>
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
        
        .admin-dashboard {
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
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .section-title {
            color: #2c5aa0;
            font-size: 1.5rem;
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
        
        .search-form {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        
        .search-input {
            flex: 1;
            min-width: 250px;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
        }
        
        .search-input:focus {
            outline: none;
            border-color: #2c5aa0;
        }
        
        .students-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        
        .students-table th,
        .students-table td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .students-table th {
            background: #f8fafc;
            font-weight: 600;
            color: #2c5aa0;
        }
        
        .students-table tr:hover {
            background: #f8fafc;
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
            margin: 5% auto;
            padding: 2rem;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
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
        
        .form-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
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
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 1rem;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus {
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
        
        @media (max-width: 768px) {
            .admin-dashboard {
                padding: 1rem;
            }
            
            .admin-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            
            .section-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .search-form {
                flex-direction: column;
            }
            
            .search-input {
                min-width: auto;
            }
            
            .students-table {
                font-size: 0.9rem;
            }
            
            .students-table th,
            .students-table td {
                padding: 0.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="admin-dashboard">
        <div class="admin-header">
            <h1><i class="fas fa-users"></i> Students Management</h1>
            <div class="nav-links">
                <a href="dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a>
                <a href="students.php" class="active"><i class="fas fa-users"></i> Students</a>
                <a href="hostels.php"><i class="fas fa-building"></i> Hostels</a>
                <a href="allocations.php"><i class="fas fa-bed"></i> Allocations</a>
                <a href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a>
                <a href="settings.php"><i class="fas fa-cogs"></i> Settings</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
        
        <div class="content-section">
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $messageType; ?>">
                    <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <div class="section-header">
                <h2 class="section-title">All Students</h2>
                <button class="btn btn-primary" onclick="openModal('addStudentModal')">
                    <i class="fas fa-plus"></i> Add Student
                </button>
            </div>
            
            <form class="search-form" method="GET">
                <input type="text" name="search" class="search-input" 
                       placeholder="Search by matric number, name, or department..." 
                       value="<?php echo htmlspecialchars($search); ?>">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Search
                </button>
                <?php if ($search): ?>
                    <a href="students.php" class="btn" style="background: #6b7280; color: white;">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>
            
            <?php if (empty($students)): ?>
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h3><?php echo $search ? 'No students found' : 'No students registered'; ?></h3>
                    <p><?php echo $search ? 'Try adjusting your search terms.' : 'Students will appear here once they are added to the system.'; ?></p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="students-table">
                        <thead>
                            <tr>
                                <th>Matric Number</th>
                                <th>Full Name</th>
                                <th>Department</th>
                                <th>Level</th>
                                <th>Gender</th>
                                <th>Phone</th>
                                <th>Study Mode</th>
                                <th>Admission Year</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($student['matric_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                    <td><?php echo htmlspecialchars($student['department']); ?></td>
                                    <td><?php echo htmlspecialchars($student['level']); ?></td>
                                    <td><?php echo ucfirst($student['gender']); ?></td>
                                    <td><?php echo htmlspecialchars($student['phone']); ?></td>
                                    <td><?php echo ucfirst(str_replace('_', ' ', $student['study_mode'])); ?></td>
                                    <td><?php echo $student['admission_year']; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Add Student Modal -->
    <div id="addStudentModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeModal('addStudentModal')">&times;</span>
            <h2 style="color: #2c5aa0; margin-bottom: 1.5rem;">
                <i class="fas fa-user-plus"></i> Add New Student
            </h2>
            
            <form method="POST">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Matric Number *</label>
                        <input type="text" name="matric_number" required 
                               placeholder="e.g., cs202001001">
                        <small style="color: #666;">Format: [dept][year][mode][number]</small>
                    </div>
                    
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="full_name" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Department *</label>
                        <select name="department" required>
                            <option value="">Select Department</option>
                            <?php foreach ($departments as $code => $name): ?>
                                <option value="<?php echo $name; ?>"><?php echo $name; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Level *</label>
                        <select name="level" required>
                            <option value="">Select Level</option>
                            <option value="ND1">ND1</option>
                            <option value="ND2">ND2</option>
                            <option value="HND1">HND1</option>
                            <option value="HND2">HND2</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Gender *</label>
                        <select name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number</label>
                        <input type="tel" name="phone" placeholder="08012345678">
                    </div>
                    
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email">
                    </div>
                    
                    <div class="form-group">
                        <label>RRR Last 4 Digits *</label>
                        <input type="text" name="rrr_last_digits" required 
                               maxlength="4" pattern="[0-9]{4}" 
                               placeholder="1234">
                    </div>
                    
                    <div class="form-group">
                        <label>Admission Year *</label>
                        <select name="admission_year" required>
                            <option value="">Select Year</option>
                            <?php for ($year = date('Y'); $year >= 2020; $year--): ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Study Mode *</label>
                        <select name="study_mode" required>
                            <option value="">Select Mode</option>
                            <option value="full_time">Full Time</option>
                            <option value="part_time">Part Time</option>
                        </select>
                    </div>
                </div>
                
                <div style="text-align: right; margin-top: 2rem;">
                    <button type="button" onclick="closeModal('addStudentModal')" 
                            style="background: #6b7280; color: white; margin-right: 1rem;" class="btn">
                        Cancel
                    </button>
                    <button type="submit" name="add_student" class="btn btn-primary">
                        <i class="fas fa-save"></i> Add Student
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
        function openModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
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
