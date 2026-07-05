<?php
// Remove session_start() from here - it should be called in individual pages
require_once __DIR__ . '/../config/database.php';

$db = new Database(); // ✅ Create the DB object

function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function validateMatricNumber($matric) {
    // Correct format: [dept_code][year(4)][mode(01|02)][unique_number(4)]
    // Example: sl2024010001
    // Supported departments: cs, sl, ee, me, ce, ba, mc
    $pattern = '/^(cs|sl|ee|me|ce|ba|mc)\d{4}(01|02)\d{4}$/i';
    return preg_match($pattern, $matric) === 1;
}

function parseMatricNumber($matric) {
    // Return false if invalid
    if (!validateMatricNumber($matric)) {
        return false;
    }
    
    $matric = strtolower($matric);
    $deptCode = substr($matric, 0, 2);
    $departments = [
        'cs' => 'Computer Science',
        'sl' => 'Science Laboratory Technology', 
        'ee' => 'Electrical/Electronics Engineering',
        'me' => 'Mechanical Engineering',
        'ce' => 'Civil Engineering',
        'ba' => 'Business Administration',
        'mc' => 'Mass Communication'
    ];
    
    return [
        'department' => $departments[$deptCode] ?? 'Unknown Department',
        'year' => substr($matric, 2, 4),          // characters 2..5 (4 digits)
        'mode' => substr($matric, 6, 2) === '01' ? 'full_time' : 'part_time', // chars 6..7
        'unique_id' => substr($matric, 8, 4)      // chars 8..11 (4-digit unique number)
    ];
}

/**
 * Authenticate student with both matric and applicant numbers
 */
function authenticateStudent($identifier, $password, $login_type = 'matric') {
    $db = new Database();
    
    if ($login_type === 'applicant') {
        $db->query('SELECT * FROM students WHERE applicant_number = :identifier AND status = "active"');
    } else {
        $db->query('SELECT * FROM students WHERE matric_number = :identifier AND status = "active"');
    }
    
    $db->bind(':identifier', $identifier);
    $student = $db->single();
    
    if ($student) {
        // Check both password methods (hashed and RRR digits)
        if (password_verify($password, $student['password']) || 
            ($student['rrr_last_digits'] && $student['rrr_last_digits'] === $password)) {
            $_SESSION['student_id'] = $student['id'];
            $_SESSION['student_matric'] = $student['matric_number'];
            $_SESSION['student_name'] = $student['full_name'];
            $_SESSION['student_logged_in'] = true;
            return true;
        }
    }
    
    return false;
}

function authenticateAdmin($username, $password) {
    $db = new Database();
    $db->query('SELECT * FROM admin_users WHERE username = :username');
    $db->bind(':username', $username);
    $admin = $db->single();
    
    if ($admin && password_verify($password, $admin['password'])) {
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $admin['username'];
        $_SESSION['admin_name'] = $admin['full_name'];
        $_SESSION['admin_role'] = $admin['role'];
        $_SESSION['admin_logged_in'] = true;
        
        // Update last login
        $db->query('UPDATE admin_users SET last_login = NOW() WHERE id = :id');
        $db->bind(':id', $admin['id']);
        $db->execute();
        
        return true;
    }
    return false;
}

function isLoggedIn() {
    return isset($_SESSION['student_logged_in']) && $_SESSION['student_logged_in'] === true;
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdminLogin() {
    if (!isAdminLoggedIn()) {
        header('Location: index.php');
        exit();
    }
}

function getStudentInfo($student_id) {
    $db = new Database();
    $db->query('SELECT * FROM students WHERE id = :id');
    $db->bind(':id', $student_id);
    return $db->single();
}

function getAvailableHostels($gender) {
    $db = new Database();
    $db->query('SELECT * FROM hostels WHERE gender = :gender AND status = "active" AND available_spaces > 0 ORDER BY hostel_name');
    $db->bind(':gender', $gender);
    return $db->resultset();
}

function getAllHostels() {
    $db = new Database();
    $db->query('
        SELECT 
            h.*,
            (SELECT COUNT(*) FROM rooms r WHERE r.hostel_id = h.id) as total_rooms,
            (SELECT SUM(capacity) FROM rooms r WHERE r.hostel_id = h.id) as total_capacity,
            (SELECT SUM(capacity - occupied) FROM rooms r WHERE r.hostel_id = h.id) as available_spaces
        FROM hostels h 
        ORDER BY h.hostel_name
    ');
    return $db->resultset();
}

function getHostelById($hostel_id) {
    $db = new Database();
    $db->query('SELECT * FROM hostels WHERE id = :id');
    $db->bind(':id', $hostel_id);
    return $db->single();
}

function getHostelRooms($hostel_id) {
    $db = new Database();
    $db->query('SELECT * FROM rooms WHERE hostel_id = :hostel_id ORDER BY room_number');
    $db->bind(':hostel_id', $hostel_id);
    return $db->resultset();
}

function getAllRooms($hostel_id = null) {
    $db = new Database();
    if ($hostel_id) {
        $db->query('SELECT r.*, h.hostel_name FROM rooms r JOIN hostels h ON r.hostel_id = h.id WHERE r.hostel_id = :hostel_id ORDER BY r.room_number');
        $db->bind(':hostel_id', $hostel_id);
    } else {
        $db->query('SELECT r.*, h.hostel_name FROM rooms r JOIN hostels h ON r.hostel_id = h.id ORDER BY h.hostel_name, r.room_number');
    }
    return $db->resultset();
}

function getRoomById($room_id) {
    $db = new Database();
    $db->query('SELECT r.*, h.hostel_name FROM rooms r JOIN hostels h ON r.hostel_id = h.id WHERE r.id = :id');
    $db->bind(':id', $room_id);
    return $db->single();
}

function hasActiveAllocation($student_id) {
    $db = new Database();
    $db->query('SELECT * FROM allocations WHERE student_id = :student_id AND admin_approved IN ("pending", "approved")');
    $db->bind(':student_id', $student_id);
    return $db->single();
}

function allocateRoom($student_id, $hostel_id, $room_id) {
    $db = new Database();
    
    // Check if student already has allocation
    if (hasActiveAllocation($student_id)) {
        return false;
    }
    
    // Get room info
    $db->query('SELECT * FROM rooms WHERE id = :room_id AND hostel_id = :hostel_id');
    $db->bind(':room_id', $room_id);
    $db->bind(':hostel_id', $hostel_id);
    $room = $db->single();
    
    if (!$room || $room['occupied'] >= $room['capacity']) {
        return false;
    }
    
    try {
        $db->beginTransaction();
        
        // Get current active session
        $activeSession = getActiveSession();
        $sessionName = $activeSession ? $activeSession['session_name'] : '2023/2024';
        
        // Create allocation (pending admin approval)
        $db->query('INSERT INTO allocations (student_id, hostel_id, room_id, academic_session, allocation_date, admin_approved, payment_status) VALUES (:student_id, :hostel_id, :room_id, :session, NOW(), "pending", "pending")');
        $db->bind(':student_id', $student_id);
        $db->bind(':hostel_id', $hostel_id);
        $db->bind(':room_id', $room_id);
        $db->bind(':session', $sessionName);
        $db->execute();
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

/**
 * Allocate room using FCFS (First-Come-First-Served)
 */
function allocateRoomFCFS($student_id, $hostel_id, $room_type) {
    $db = new Database();
    
    // Start transaction
    $db->beginTransaction();
    
    try {
        // Find available room of the requested type
        $db->query('
            SELECT id FROM rooms 
            WHERE hostel_id = :hostel_id 
            AND room_type = :room_type 
            AND occupied < capacity 
            AND status = "available" 
            ORDER BY occupied ASC, id ASC 
            LIMIT 1
        ');
        $db->bind(':hostel_id', $hostel_id);
        $db->bind(':room_type', $room_type);
        $room = $db->single();
        
        if (!$room) {
            throw new Exception('No available rooms of this type');
        }
        
        $room_id = $room['id'];
        
        // Get active academic session
        $active_session = getActiveSession();
        if (!$active_session) {
            throw new Exception('No active academic session');
        }
        
        // Create allocation
        $db->query('
            INSERT INTO allocations (student_id, hostel_id, room_id, academic_session, allocation_date, admin_approved, payment_status) 
            VALUES (:student_id, :hostel_id, :room_id, :academic_session, NOW(), "pending", "pending")
        ');
        $db->bind(':student_id', $student_id);
        $db->bind(':hostel_id', $hostel_id);
        $db->bind(':room_id', $room_id);
        $db->bind(':academic_session', $active_session['session_name']);
        
        if (!$db->execute()) {
            throw new Exception('Failed to create allocation');
        }
        
        $allocation_id = $db->lastInsertId();
        
        // Update room occupancy
        $db->query('UPDATE rooms SET occupied = occupied + 1 WHERE id = :room_id');
        $db->bind(':room_id', $room_id);
        
        if (!$db->execute()) {
            throw new Exception('Failed to update room occupancy');
        }
        
        // Update hostel available spaces
        $db->query('UPDATE hostels SET available_spaces = available_spaces - 1 WHERE id = :hostel_id');
        $db->bind(':hostel_id', $hostel_id);
        $db->execute();
        
        // Commit transaction
        $db->commit();
        
        return $allocation_id;
        
    } catch (Exception $e) {
        $db->rollBack();
        error_log("FCFS Allocation Error: " . $e->getMessage());
        return false;
    }
}

function approveAllocation($allocation_id, $admin_id) {
    $db = new Database();
    
    try {
        $db->beginTransaction();
        
        // Get allocation details
        $db->query('SELECT * FROM allocations WHERE id = :id');
        $db->bind(':id', $allocation_id);
        $allocation = $db->single();
        
        if (!$allocation) {
            $db->rollback();
            return false;
        }
        
        // Update allocation status
        $db->query('UPDATE allocations SET admin_approved = "approved", approved_by = :admin_id, approval_date = NOW() WHERE id = :id');
        $db->bind(':admin_id', $admin_id);
        $db->bind(':id', $allocation_id);
        $db->execute();
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

function rejectAllocation($allocation_id, $admin_id, $reason) {
    $db = new Database();
    $db->query('UPDATE allocations SET admin_approved = "rejected", approved_by = :admin_id, approval_date = NOW(), rejection_reason = :reason WHERE id = :id');
    $db->bind(':admin_id', $admin_id);
    $db->bind(':id', $allocation_id);
    $db->bind(':reason', $reason);
    return $db->execute();
}

function getAllAllocations() {
    $db = new Database();
    $db->query('
        SELECT a.*, s.full_name, s.matric_number, s.gender, s.department, 
               h.hostel_name, r.room_number, r.room_type,
               au.full_name as approved_by_name
        FROM allocations a 
        JOIN students s ON a.student_id = s.id 
        JOIN hostels h ON a.hostel_id = h.id 
        JOIN rooms r ON a.room_id = r.id 
        LEFT JOIN admin_users au ON a.approved_by = au.id
        ORDER BY a.allocation_date DESC
    ');
    return $db->resultset();
}

function getPendingAllocations() {
    $db = new Database();
    $db->query('
        SELECT a.*, s.full_name, s.matric_number, s.gender, s.department, s.level,
               h.hostel_name, r.room_number, r.room_type, r.price
        FROM allocations a 
        JOIN students s ON a.student_id = s.id 
        JOIN hostels h ON a.hostel_id = h.id 
        JOIN rooms r ON a.room_id = r.id 
        WHERE a.admin_approved = "pending"
        ORDER BY a.allocation_date ASC
    ');
    return $db->resultset();
}

function getAllStudents() {
    $db = new Database();
    $db->query('SELECT * FROM students ORDER BY full_name');
    return $db->resultset();
}

function searchStudents($search_term) {
    $db = new Database();
    $db->query('
        SELECT * FROM students 
        WHERE matric_number LIKE :search 
        OR full_name LIKE :search 
        OR department LIKE :search
        ORDER BY full_name
    ');
    $db->bind(':search', '%' . $search_term . '%');
    return $db->resultset();
}

function addStudent($data) {
    $db = new Database();
    
    // Generate password from last 4 digits of identifier
    $password = substr($data['matric_number'], -4);
    
    $db->query('
        INSERT INTO students (matric_number, applicant_number, full_name, department, level, gender, phone, email, password, admission_year, study_mode, rrr_last_digits) 
        VALUES (:matric, :applicant, :name, :dept, :level, :gender, :phone, :email, :password, :year, :mode, :rrr)
    ');
    
    $db->bind(':matric', $data['matric_number']);
    $db->bind(':applicant', $data['applicant_number'] ?? null);
    $db->bind(':name', $data['full_name']);
    $db->bind(':dept', $data['department']);
    $db->bind(':level', $data['level']);
    $db->bind(':gender', $data['gender']);
    $db->bind(':phone', $data['phone']);
    $db->bind(':email', $data['email']);
    $db->bind(':password', password_hash($password, PASSWORD_DEFAULT));
    $db->bind(':year', $data['admission_year']);
    $db->bind(':mode', $data['study_mode']);
    $db->bind(':rrr', $password); // Store last 4 digits as RRR for backward compatibility
    
    return $db->execute();
}

function addHostel($data) {
    $db = new Database();
    $db->query('
        INSERT INTO hostels (hostel_name, gender, total_capacity, available_spaces, price_per_session, facilities, description, image_path) 
        VALUES (:name, :gender, :capacity, :capacity, :price, :facilities, :description, :image_path)
    ');
    
    $db->bind(':name', $data['hostel_name']);
    $db->bind(':gender', $data['gender']);
    $db->bind(':capacity', $data['total_capacity']);
    $db->bind(':price', $data['price_per_session']);
    $db->bind(':facilities', $data['facilities']);
    $db->bind(':description', $data['description']);
    $db->bind(':image_path', $data['image_path'] ?? null);
    
    return $db->execute();
}

function updateHostel($hostel_id, $data) {
    $db = new Database();
    $db->query('
        UPDATE hostels SET 
        hostel_name = :name, 
        gender = :gender, 
        total_capacity = :capacity, 
        price_per_session = :price, 
        facilities = :facilities, 
        description = :description,
        image_path = :image_path
        WHERE id = :id
    ');
    
    $db->bind(':id', $hostel_id);
    $db->bind(':name', $data['hostel_name']);
    $db->bind(':gender', $data['gender']);
    $db->bind(':capacity', $data['total_capacity']);
    $db->bind(':price', $data['price_per_session']);
    $db->bind(':facilities', $data['facilities']);
    $db->bind(':description', $data['description']);
    $db->bind(':image_path', $data['image_path']);
    
    return $db->execute();
}

function deleteHostel($hostel_id) {
    $db = new Database();
    
    try {
        $db->beginTransaction();
        
        // Check if hostel has active allocations
        $db->query('SELECT COUNT(*) as count FROM allocations WHERE hostel_id = :id AND admin_approved IN ("pending", "approved")');
        $db->bind(':id', $hostel_id);
        $result = $db->single();
        
        if ($result['count'] > 0) {
            $db->rollback();
            return false; // Cannot delete hostel with active allocations
        }
        
        // Delete rooms first
        $db->query('DELETE FROM rooms WHERE hostel_id = :id');
        $db->bind(':id', $hostel_id);
        $db->execute();
        
        // Delete hostel
        $db->query('DELETE FROM hostels WHERE id = :id');
        $db->bind(':id', $hostel_id);
        $db->execute();
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

function addRoom($data) {
    $db = new Database();
    
    try {
        $db->beginTransaction();
        
        // Add room
        $db->query('
            INSERT INTO rooms (hostel_id, room_number, capacity, room_type, price, facilities) 
            VALUES (:hostel_id, :room_number, :capacity, :room_type, :price, :facilities)
        ');
        
        $db->bind(':hostel_id', $data['hostel_id']);
        $db->bind(':room_number', $data['room_number']);
        $db->bind(':capacity', $data['capacity']);
        $db->bind(':room_type', $data['room_type']);
        $db->bind(':price', $data['price']);
        $db->bind(':facilities', $data['facilities'] ?? null);
        $db->execute();
        
        // Update hostel total capacity and available spaces
        $db->query('UPDATE hostels SET total_capacity = total_capacity + :capacity, available_spaces = available_spaces + :capacity WHERE id = :hostel_id');
        $db->bind(':capacity', $data['capacity']);
        $db->bind(':hostel_id', $data['hostel_id']);
        $db->execute();
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

function deleteAllocation($allocation_id) {
    $db = new Database();
    
    try {
        $db->beginTransaction();
        
        // Get allocation details
        $db->query('SELECT * FROM allocations WHERE id = :id');
        $db->bind(':id', $allocation_id);
        $allocation = $db->single();
        
        if ($allocation) {
            // Delete allocation
            $db->query('DELETE FROM allocations WHERE id = :id');
            $db->bind(':id', $allocation_id);
            $db->execute();
            
            // Update room occupancy only if it was approved
            if ($allocation['admin_approved'] === 'approved') {
                $db->query('UPDATE rooms SET occupied = occupied - 1 WHERE id = :room_id');
                $db->bind(':room_id', $allocation['room_id']);
                $db->execute();
                
                // Update hostel availability
                $db->query('UPDATE hostels SET available_spaces = available_spaces + 1 WHERE id = :hostel_id');
                $db->bind(':hostel_id', $allocation['hostel_id']);
                $db->execute();
            }
        }
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

// Academic Session Functions
function getActiveSession() {
    $db = new Database();
    $db->query('SELECT * FROM academic_sessions WHERE is_active = "yes" LIMIT 1');
    return $db->single();
}

function getAllSessions() {
    $db = new Database();
    $db->query('SELECT * FROM academic_sessions ORDER BY start_date DESC');
    return $db->resultset();
}

function addSession($session_name, $start_date, $end_date) {
    $db = new Database();
    $db->query('INSERT INTO academic_sessions (session_name, start_date, end_date) VALUES (:name, :start, :end)');
    $db->bind(':name', $session_name);
    $db->bind(':start', $start_date);
    $db->bind(':end', $end_date);
    return $db->execute();
}

function setActiveSession($session_id) {
    $db = new Database();
    
    try {
        $db->beginTransaction();
        
        // Deactivate all sessions
        $db->query('UPDATE academic_sessions SET is_active = "no"');
        $db->execute();
        
        // Activate selected session
        $db->query('UPDATE academic_sessions SET is_active = "yes" WHERE id = :id');
        $db->bind(':id', $session_id);
        $db->execute();
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        return false;
    }
}

function deleteSession($session_id) {
    $db = new Database();
    
    // Check if session has allocations
    $db->query('SELECT COUNT(*) as count FROM allocations a JOIN academic_sessions s ON a.academic_session = s.session_name WHERE s.id = :id');
    $db->bind(':id', $session_id);
    $result = $db->single();
    
    if ($result['count'] > 0) {
        return false; // Cannot delete session with allocations
    }
    
    $db->query('DELETE FROM academic_sessions WHERE id = :id');
    $db->bind(':id', $session_id);
    return $db->execute();
}

// Image upload function
function uploadHostelImage($file) {
    $target_dir = "uploads/hostels/";
    
    // Create directory if it doesn't exist
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = uniqid() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    // Check if image file is actual image
    $check = getimagesize($file["tmp_name"]);
    if ($check === false) {
        return false;
    }
    
    // Check file size (5MB max)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // Allow certain file formats
    if (!in_array($file_extension, ["jpg", "jpeg", "png", "gif"])) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        return $target_file;
    }
    
    return false;
}

// Statistics functions
function getSystemStats() {
    $db = new Database();
    
    $stats = [];
    
    // Total students
    $db->query('SELECT COUNT(*) as count FROM students');
    $result = $db->single();
    $stats['totalStudents'] = $result['count'];
    
    // Total hostels
    $db->query('SELECT COUNT(*) as count FROM hostels WHERE status = "active"');
    $result = $db->single();
    $stats['totalHostels'] = $result['count'];
    
    // Total rooms
    $db->query('SELECT COUNT(*) as count FROM rooms');
    $result = $db->single();
    $stats['totalRooms'] = $result['count'];
    
    // Occupied rooms
    $db->query('SELECT COUNT(*) as count FROM rooms WHERE occupied > 0');
    $result = $db->single();
    $stats['occupiedRooms'] = $result['count'];
    
    // Available spaces
    $db->query('SELECT SUM(available_spaces) as total FROM hostels WHERE status = "active"');
    $result = $db->single();
    $stats['availableSpaces'] = $result['total'] ?? 0;
    
    // Pending allocations
    $db->query('SELECT COUNT(*) as count FROM allocations WHERE admin_approved = "pending"');
    $result = $db->single();
    $stats['pendingAllocations'] = $result['count'];
    
    // Approved allocations
    $db->query('SELECT COUNT(*) as count FROM allocations WHERE admin_approved = "approved"');
    $result = $db->single();
    $stats['approvedAllocations'] = $result['count'];
    
    return $stats;
}

function getDepartments() {
    return [
        'cs' => 'Computer Science',
        'sl' => 'Science Laboratory Technology', 
        'ee' => 'Electrical/Electronics Engineering',
        'me' => 'Mechanical Engineering',
        'ce' => 'Civil Engineering',
        'ba' => 'Business Administration',
        'mc' => 'Mass Communication'
    ];
}

function formatCurrency($amount) {
    return '₦' . number_format($amount, 2);
}

function formatDate($date) {
    return date('M j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('M j, Y g:i A', strtotime($datetime));
}

/**
 * Generate matric number for new students
 */
function generateMatricNumber($department, $admission_year, $study_mode) {
    $db = new Database();
    
    // Get department code
    $dept_codes = [
        'Computer Science' => 'cs',
        'Software Engineering' => 'se',
        'Electrical Engineering' => 'ee',
        'Mechanical Engineering' => 'me',
        'Civil Engineering' => 'ce',
        'Business Administration' => 'ba',
        'Mass Communication' => 'mc'
    ];
    
    $dept_code = $dept_codes[$department] ?? 'ge';
    $mode_code = $study_mode === 'full_time' ? '01' : '02';
    $year = substr($admission_year, -2);
    
    // Get the next sequence number
    $db->query('SELECT COUNT(*) as count FROM students WHERE department = :department AND admission_year = :year');
    $db->bind(':department', $department);
    $db->bind(':year', $admission_year);
    $result = $db->single();
    
    $sequence = str_pad($result['count'] + 1, 3, '0', STR_PAD_LEFT);
    
    return $dept_code . $year . $mode_code . $sequence;
}

/**
 * Get available rooms by capacity (number of occupants)
 */
function getAvailableRoomsByCapacity($hostel_id, $capacity) {
    $db = new Database();
    $db->query('
        SELECT r.*, (r.capacity - r.occupied) as available_spaces
        FROM rooms r 
        WHERE r.hostel_id = :hostel_id 
        AND r.capacity = :capacity
        AND r.occupied < r.capacity 
        AND r.status = "available"
        ORDER BY r.room_number
    ');
    $db->bind(':hostel_id', $hostel_id);
    $db->bind(':capacity', $capacity);
    return $db->resultset();
}

/**
 * Process payment and update allocation status
 */
function processPayment($allocation_id, $payment_method, $reference_number, $amount) {
    $db = new Database();
    
    $db->query('
        UPDATE allocations 
        SET payment_status = "paid", 
            payment_method = :payment_method, 
            payment_reference = :reference, 
            payment_amount = :amount,
            payment_date = NOW()
        WHERE id = :allocation_id
    ');
    
    $db->bind(':payment_method', $payment_method);
    $db->bind(':reference', $reference_number);
    $db->bind(':amount', $amount);
    $db->bind(':allocation_id', $allocation_id);
    
    return $db->execute();
}

/**
 * Get real-time allocation status
 */
function getRealTimeAllocationStatus($student_id) {
    $db = new Database();
    $db->query('
        SELECT a.*, h.hostel_name, r.room_number, r.room_type, r.capacity,
               (SELECT COUNT(*) FROM allocations a2 
                WHERE a2.hostel_id = a.hostel_id 
                AND a2.room_type = a.room_type 
                AND a2.allocation_date < a.allocation_date 
                AND a2.admin_approved = "pending") as queue_position
        FROM allocations a
        JOIN hostels h ON a.hostel_id = h.id
        JOIN rooms r ON a.room_id = r.id
        WHERE a.student_id = :student_id 
        AND a.academic_session = (SELECT session_name FROM academic_sessions WHERE is_active = "yes")
        ORDER BY a.allocation_date DESC 
        LIMIT 1
    ');
    $db->bind(':student_id', $student_id);
    return $db->single();
}

/**
 * Add hostel with customizable room types - CORRECTED VERSION
 */
/**
 * Add hostel with customizable room types (transaction-safe version)
 */
function addHostelWithRooms($data) {
    $db = new Database();

    try {
        // Begin transaction
        $db->beginTransaction();

        // Insert hostel with zero capacity (will update later)
        $db->query('
            INSERT INTO hostels (hostel_name, gender, total_capacity, available_spaces, price_per_session, facilities, description, image_path) 
            VALUES (:name, :gender, 0, 0, :price, :facilities, :description, :image_path)
        ');
        $db->bind(':name', $data['hostel_name']);
        $db->bind(':gender', $data['gender']);
        $db->bind(':price', $data['price_per_session']);
        $db->bind(':facilities', $data['facilities']);
        $db->bind(':description', $data['description']);
        $db->bind(':image_path', $data['image_path'] ?? null);

        if (!$db->execute()) {
            throw new Exception('Failed to insert hostel');
        }

        $hostel_id = $db->lastInsertId();
        $number_of_rooms = $data['number_of_rooms'];
        $room_types = $data['room_types'];
        $base_price = $data['price_per_session'];
        $custom_capacities = $_POST['custom_capacity'] ?? [];

        // Define room configurations
        $room_configs = [
            'single' => ['capacity' => 1, 'multiplier' => 1.0],
            'double' => ['capacity' => 2, 'multiplier' => 1.0],
            'triple' => ['capacity' => 3, 'multiplier' => 1.0],
            'quad' => ['capacity' => 4, 'multiplier' => 1.0],
            'room_parlor' => ['capacity' => $custom_capacities['room_parlor'] ?? 2, 'multiplier' => 1.0],
            'two_bedroom' => ['capacity' => $custom_capacities['two_bedroom'] ?? 4, 'multiplier' => 1.0],
            'three_bedroom' => ['capacity' => $custom_capacities['three_bedroom'] ?? 6, 'multiplier' => 1.0]
        ];

        if (empty($room_types)) {
            throw new Exception('No room types selected');
        }

        $rooms_per_type = ceil($number_of_rooms / count($room_types));
        $room_counter = 1;

        // Insert rooms
        foreach ($room_types as $room_type) {
            $config = $room_configs[$room_type] ?? $room_configs['double'];
            for ($i = 0; $i < $rooms_per_type && $room_counter <= $number_of_rooms; $i++, $room_counter++) {
                $room_price = $base_price * $config['multiplier'];

                $db->query('
                    INSERT INTO rooms (hostel_id, room_number, capacity, room_type, price, facilities, status) 
                    VALUES (:hostel_id, :room_number, :capacity, :room_type, :price, :facilities, "available")
                ');
                $db->bind(':hostel_id', $hostel_id);
                $db->bind(':room_number', $room_counter);
                $db->bind(':capacity', $config['capacity']);
                $db->bind(':room_type', $room_type);
                $db->bind(':price', $room_price);
                $db->bind(':facilities', 'Basic facilities');

                if (!$db->execute()) {
                    throw new Exception("Failed to insert room {$room_counter}");
                }
            }
        }

        // Update hostel capacity using the SAME $db instance
        if (!updateHostelCapacity($db, $hostel_id)) {
            throw new Exception('Failed to update hostel capacity');
        }

        // Commit transaction
        $db->commit();
        return true;

    } catch (Exception $e) {
        // Rollback only if transaction is active
        if ($db->dbh->inTransaction()) {
            $db->rollback();
        }

        error_log("Add hostel with rooms error: " . $e->getMessage());
        return false;
    }
}


/**
 * Update hostel capacity (uses same Database instance to stay in one transaction)
 */
function updateHostelCapacity($db, $hostel_id) {
    try {
        $db->query("SELECT SUM(capacity) AS total_capacity FROM rooms WHERE hostel_id = :hostel_id");
        $db->bind(':hostel_id', $hostel_id);
        $result = $db->single();
        $total_capacity = $result['total_capacity'] ?? 0;

        $db->query("
            UPDATE hostels 
            SET total_capacity = :capacity, available_spaces = :capacity 
            WHERE id = :hostel_id
        ");
        $db->bind(':capacity', $total_capacity);
        $db->bind(':hostel_id', $hostel_id);

        return $db->execute();

    } catch (Exception $e) {
        error_log("Update hostel capacity error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get student allocation history
 */
function getStudentAllocationHistory($student_id) {
    $db = new Database();
    $db->query('
        SELECT a.*, h.hostel_name, r.room_number, r.room_type, r.price
        FROM allocations a
        JOIN hostels h ON a.hostel_id = h.id
        JOIN rooms r ON a.room_id = r.id
        WHERE a.student_id = :student_id
        ORDER BY a.allocation_date DESC
    ');
    $db->bind(':student_id', $student_id);
    return $db->resultset();
}

/**
 * Check if room is available
 */
function isRoomAvailable($room_id) {
    $db = new Database();
    $db->query('SELECT capacity, occupied FROM rooms WHERE id = :id AND status = "available"');
    $db->bind(':id', $room_id);
    $room = $db->single();
    
    return $room && $room['occupied'] < $room['capacity'];
}

/**
 * Get roommates in the same room
 */
function getRoommates($room_id, $current_student_id = null) {
    $db = new Database();
    $db->query('
        SELECT s.full_name, s.matric_number, s.department, s.level
        FROM allocations a
        JOIN students s ON a.student_id = s.id
        WHERE a.room_id = :room_id 
        AND a.admin_approved = "approved"
        AND a.student_id != :student_id
    ');
    $db->bind(':room_id', $room_id);
    $db->bind(':student_id', $current_student_id ?? 0);
    return $db->resultset();
}

/**
 * Get hostel occupancy statistics
 */
function getHostelOccupancyStats($hostel_id) {
    $db = new Database();
    $db->query('
        SELECT 
            COUNT(*) as total_rooms,
            SUM(capacity) as total_capacity,
            SUM(occupied) as total_occupied,
            SUM(capacity - occupied) as total_available,
            ROUND((SUM(occupied) / SUM(capacity)) * 100, 2) as occupancy_rate
        FROM rooms 
        WHERE hostel_id = :hostel_id
    ');
    $db->bind(':hostel_id', $hostel_id);
    return $db->single();
}

/**
 * Get gender distribution in hostel
 */
function getHostelGenderDistribution($hostel_id) {
    $db = new Database();
    $db->query('
        SELECT s.gender, COUNT(*) as count
        FROM allocations a
        JOIN students s ON a.student_id = s.id
        WHERE a.hostel_id = :hostel_id 
        AND a.admin_approved = "approved"
        GROUP BY s.gender
    ');
    $db->bind(':hostel_id', $hostel_id);
    return $db->resultset();
}

/**
 * Export data to CSV
 */
function exportToCSV($data, $filename) {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    if (!empty($data)) {
        fputcsv($output, array_keys($data[0]));
    }
    
    // Add data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

/**
 * Update room details - transaction safe
 */
function updateRoom($room_id, $data) {
    $db = new Database();

    try {
        $db->beginTransaction();

        // Get current room info
        $db->query('SELECT hostel_id, occupied FROM rooms WHERE id = :id');
        $db->bind(':id', $room_id);
        $room = $db->single();

        if (!$room) {
            throw new Exception('Room not found');
        }

        // Ensure new capacity is not less than current occupancy
        if ($data['capacity'] < $room['occupied']) {
            throw new Exception('New capacity cannot be less than current occupancy');
        }

        // Update room
        $db->query('
            UPDATE rooms SET 
                room_number = :room_number,
                capacity = :capacity,
                room_type = :room_type,
                price = :price,
                facilities = :facilities
            WHERE id = :id
        ');
        $db->bind(':id', $room_id);
        $db->bind(':room_number', $data['room_number']);
        $db->bind(':capacity', $data['capacity']);
        $db->bind(':room_type', $data['room_type']);
        $db->bind(':price', $data['price']);
        $db->bind(':facilities', $data['facilities']);
        $db->execute();

        // ✅ Use the same DB instance to update hostel capacity
        if (!updateHostelCapacity($db, $room['hostel_id'])) {
            throw new Exception('Failed to update hostel capacity');
        }

        $db->commit();
        return true;

    } catch (Exception $e) {
        if ($db->dbh->inTransaction()) {
            $db->rollback();
        }
        error_log("Update room error: " . $e->getMessage());
        return false;
    }
}

/**
 * Delete room - transaction safe
 */
function deleteRoom($room_id) {
    $db = new Database();

    try {
        $db->beginTransaction();

        // Get room info
        $db->query('SELECT hostel_id, occupied FROM rooms WHERE id = :id');
        $db->bind(':id', $room_id);
        $room = $db->single();

        if (!$room) {
            throw new Exception('Room not found');
        }

        // Check if room has occupants
        if ($room['occupied'] > 0) {
            throw new Exception('Cannot delete room with occupants');
        }

        // Delete room
        $db->query('DELETE FROM rooms WHERE id = :id');
        $db->bind(':id', $room_id);
        $db->execute();

        // ✅ Use the same DB instance to update hostel capacity
        if (!updateHostelCapacity($db, $room['hostel_id'])) {
            throw new Exception('Failed to update hostel capacity');
        }

        $db->commit();
        return true;

    } catch (Exception $e) {
        if ($db->dbh->inTransaction()) {
            $db->rollback();
        }
        error_log("Delete room error: " . $e->getMessage());
        return false;
    }
}



/**
 * Allocate room directly without admin approval
 */
function allocateRoomDirect($student_id, $hostel_id, $room_id) {
    $db = new Database();
    
    try {
        $db->beginTransaction();
        
        // Get room info
        $db->query('SELECT * FROM rooms WHERE id = :room_id AND hostel_id = :hostel_id');
        $db->bind(':room_id', $room_id);
        $db->bind(':hostel_id', $hostel_id);
        $room = $db->single();
        
        if (!$room || $room['occupied'] >= $room['capacity']) {
            throw new Exception('Room not available');
        }
        
        // Get current active session
        $activeSession = getActiveSession();
        $sessionName = $activeSession ? $activeSession['session_name'] : '2023/2024';
        
        // Create allocation (auto-approved)
        $db->query('INSERT INTO allocations (student_id, hostel_id, room_id, academic_session, allocation_date, admin_approved, payment_status) VALUES (:student_id, :hostel_id, :room_id, :session, NOW(), "approved", "pending")');
        $db->bind(':student_id', $student_id);
        $db->bind(':hostel_id', $hostel_id);
        $db->bind(':room_id', $room_id);
        $db->bind(':session', $sessionName);
        $db->execute();
        
        // Update room occupancy
        $db->query('UPDATE rooms SET occupied = occupied + 1 WHERE id = :room_id');
        $db->bind(':room_id', $room_id);
        $db->execute();
        
        // Update hostel available spaces
        $db->query('UPDATE hostels SET available_spaces = available_spaces - 1 WHERE id = :hostel_id');
        $db->bind(':hostel_id', $hostel_id);
        $db->execute();
        
        $db->commit();
        return true;
    } catch (Exception $e) {
        $db->rollback();
        error_log("Direct allocation error: " . $e->getMessage());
        return false;
    }
}
?>