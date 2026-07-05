<?php
session_start();
require_once __DIR__ . '/../includes/functions.php';
requireAdminLogin();

$message = '';
$messageType = '';

// Handle hostel operations
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['add_hostel'])) {
        $data = [
            'hostel_name' => sanitize($_POST['hostel_name']),
            'gender' => sanitize($_POST['gender']),
            'number_of_rooms' => (int)$_POST['number_of_rooms'],
            'price_per_session' => (float)$_POST['price_per_session'],
            'facilities' => sanitize($_POST['facilities']),
            'description' => sanitize($_POST['description']),
            'image_path' => null,
            'room_types' => $_POST['room_types'] ?? []
        ];
        
        // Handle image upload
        if (isset($_FILES['hostel_image']) && $_FILES['hostel_image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = uploadHostelImage($_FILES['hostel_image']);
            if ($imagePath) {
                $data['image_path'] = $imagePath;
            }
        }
        
        if (addHostelWithRooms($data)) {
            $message = 'Hostel added successfully with ' . $data['number_of_rooms'] . ' rooms!';
            $messageType = 'success';
        } else {
            $message = 'Failed to add hostel.';
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['update_hostel'])) {
        $hostel_id = (int)$_POST['hostel_id'];
        $data = [
            'hostel_name' => sanitize($_POST['hostel_name']),
            'gender' => sanitize($_POST['gender']),
            'price_per_session' => (float)$_POST['price_per_session'],
            'facilities' => sanitize($_POST['facilities']),
            'description' => sanitize($_POST['description']),
            'image_path' => sanitize($_POST['current_image'])
        ];
        
        // Handle new image upload
        if (isset($_FILES['hostel_image']) && $_FILES['hostel_image']['error'] === UPLOAD_ERR_OK) {
            $imagePath = uploadHostelImage($_FILES['hostel_image']);
            if ($imagePath) {
                $data['image_path'] = $imagePath;
            }
        }
        
        if (updateHostel($hostel_id, $data)) {
            $message = 'Hostel updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update hostel.';
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['delete_hostel'])) {
        $hostel_id = (int)$_POST['hostel_id'];
        if (deleteHostel($hostel_id)) {
            $message = 'Hostel deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Cannot delete hostel. It may have active allocations.';
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['add_room'])) {
        $data = [
            'hostel_id' => (int)$_POST['hostel_id'],
            'room_number' => sanitize($_POST['room_number']),
            'capacity' => (int)$_POST['capacity'],
            'room_type' => sanitize($_POST['room_type']),
            'price' => (float)$_POST['price'],
            'facilities' => sanitize($_POST['facilities'])
        ];
        
        if (addRoom($data)) {
            $message = 'Room added successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to add room.';
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['update_room'])) {
        $room_id = (int)$_POST['room_id'];
        $data = [
            'room_number' => sanitize($_POST['room_number']),
            'capacity' => (int)$_POST['capacity'],
            'room_type' => sanitize($_POST['room_type']),
            'price' => (float)$_POST['price'],
            'facilities' => sanitize($_POST['facilities'])
        ];
        
        if (updateRoom($room_id, $data)) {
            $message = 'Room updated successfully!';
            $messageType = 'success';
        } else {
            $message = 'Failed to update room.';
            $messageType = 'error';
        }
    }
    
    if (isset($_POST['delete_room'])) {
        $room_id = (int)$_POST['room_id'];
        if (deleteRoom($room_id)) {
            $message = 'Room deleted successfully!';
            $messageType = 'success';
        } else {
            $message = 'Cannot delete room. It may have active allocations.';
            $messageType = 'error';
        }
    }
}

$hostels = getAllHostels();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hostels - Admin Panel</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .room-types-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .room-type-option {
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        .room-type-option:hover {
            border-color: #3b82f6;
        }
        .room-type-option.selected {
            border-color: #3b82f6;
            background-color: #eff6ff;
        }
        .room-type-option input[type="checkbox"] {
            margin-right: 0.5rem;
        }
        .room-type-details {
            display: none;
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        .room-type-details.active {
            display: block;
        }
        .room-distribution {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 1rem;
            margin: 1rem 0;
        }
        .distribution-item {
            text-align: center;
            padding: 0.5rem;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .custom-capacity-input {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            margin-top: 0.5rem;
        }
    </style>
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
                        <a href="hostels.php" class="active">Hostels</a>
                        <a href="allocations.php">Allocations</a>
                        <a href="reports.php">Reports</a>
                        <a href="settings.php">Settings</a>
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
                <!-- Hostels Management -->
                <div class="admin-card">
                    <div class="admin-card-header">
                        <h3><i class="fas fa-building"></i> Hostels Management</h3>
                        <button class="btn btn-primary" data-modal="addHostelModal">
                            <i class="fas fa-plus"></i> Add New Hostel
                        </button>
                    </div>
                    <div class="admin-card-body">
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 2rem;">
                            <?php foreach ($hostels as $hostel): ?>
                                <div class="feature-card" style="position: relative;">
                                    <div style="position: absolute; top: 10px; right: 10px;">
                                        <?php if ($hostel['status'] === 'active'): ?>
                                            <span class="badge badge-success">Active</span>
                                        <?php else: ?>
                                            <span class="badge badge-secondary">Inactive</span>
                                        <?php endif; ?>
                                    </div>
                                    
                                    <h4 style="color: #2c5aa0; margin-bottom: 1rem;">
                                        <?php echo htmlspecialchars($hostel['hostel_name']); ?>
                                    </h4>
                                    
                                    <div class="info-item">
                                        <span class="info-label">Gender:</span>
                                        <span class="info-value">
                                            <i class="fas fa-<?php echo $hostel['gender'] === 'male' ? 'mars' : 'venus'; ?>"></i>
                                            <?php echo ucfirst($hostel['gender']); ?>
                                        </span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <span class="info-label">Rooms:</span>
                                        <span class="info-value">
                                            <?php echo $hostel['total_rooms']; ?> rooms (<?php echo $hostel['available_spaces']; ?> available spaces)
                                        </span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <span class="info-label">Price per Session:</span>
                                        <span class="info-value">₦<?php echo number_format($hostel['price_per_session'], 2); ?></span>
                                    </div>
                                    
                                    <div class="info-item">
                                        <span class="info-label">Facilities:</span>
                                        <span class="info-value"><?php echo htmlspecialchars($hostel['facilities']); ?></span>
                                    </div>
                                    
                                    <p style="color: #666; margin: 1rem 0; font-size: 0.9rem;">
                                        <?php echo htmlspecialchars($hostel['description']); ?>
                                    </p>
                                    
                                    <div class="action-buttons" style="margin-top: 1.5rem;">
                                        <button class="btn btn-sm btn-info" onclick="viewRooms(<?php echo $hostel['id']; ?>)">
                                            <i class="fas fa-bed"></i> Manage Rooms
                                        </button>
                                        <button class="btn btn-sm btn-warning" onclick="editHostel(<?php echo $hostel['id']; ?>)">
                                            <i class="fas fa-edit"></i> Edit
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteHostel(<?php echo $hostel['id']; ?>)">
                                            <i class="fas fa-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Hostel Modal -->
    <div id="addHostelModal" class="modal">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h4><i class="fas fa-plus"></i> Add New Hostel</h4>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="addHostelForm">
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="hostel_name">
                                <i class="fas fa-building"></i> Hostel Name *
                            </label>
                            <input type="text" id="hostel_name" name="hostel_name" required>
                        </div>
                        <div class="form-group">
                            <label for="gender">
                                <i class="fas fa-users"></i> Gender *
                            </label>
                            <select id="gender" name="gender" required>
                                <option value="">Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="number_of_rooms">
                                <i class="fas fa-door-closed"></i> Number of Rooms *
                            </label>
                            <input type="number" id="number_of_rooms" name="number_of_rooms" min="1" max="100" required onchange="updateRoomDistribution()">
                        </div>
                        <div class="form-group">
                            <label for="price_per_session">
                                <i class="fas fa-naira-sign"></i> Base Price per Session *
                            </label>
                            <input type="number" id="price_per_session" name="price_per_session" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <!-- Room Types Selection -->
                    <div class="form-group">
                        <label style="margin-bottom: 1rem; display: block;">
                            <i class="fas fa-bed"></i> Select Room Types *
                        </label>
                        <div class="room-types-grid">
                            <div class="room-type-option" onclick="toggleRoomType(this, 'single')">
                                <input type="checkbox" name="room_types[]" value="single" onchange="toggleRoomTypeDetails('single')">
                                <strong>Single Room</strong>
                                <div style="font-size: 0.8rem; color: #666;">1 person, private space</div>
                            </div>
                            <div class="room-type-option" onclick="toggleRoomType(this, 'double')">
                                <input type="checkbox" name="room_types[]" value="double" onchange="toggleRoomTypeDetails('double')">
                                <strong>Double Room</strong>
                                <div style="font-size: 0.8rem; color: #666;">2 persons, shared space</div>
                            </div>
                            <div class="room-type-option" onclick="toggleRoomType(this, 'triple')">
                                <input type="checkbox" name="room_types[]" value="triple" onchange="toggleRoomTypeDetails('triple')">
                                <strong>Triple Room</strong>
                                <div style="font-size: 0.8rem; color: #666;">3 persons, shared space</div>
                            </div>
                            <div class="room-type-option" onclick="toggleRoomType(this, 'quad')">
                                <input type="checkbox" name="room_types[]" value="quad" onchange="toggleRoomTypeDetails('quad')">
                                <strong>Quad Room</strong>
                                <div style="font-size: 0.8rem; color: #666;">4 persons, shared space</div>
                            </div>
                            <div class="room-type-option" onclick="toggleRoomType(this, 'room_parlor')">
                                <input type="checkbox" name="room_types[]" value="room_parlor" onchange="toggleRoomTypeDetails('room_parlor')">
                                <strong>Room & Parlor</strong>
                                <div style="font-size: 0.8rem; color: #666;">2-3 persons, with sitting area</div>
                            </div>
                            <div class="room-type-option" onclick="toggleRoomType(this, 'two_bedroom')">
                                <input type="checkbox" name="room_types[]" value="two_bedroom" onchange="toggleRoomTypeDetails('two_bedroom')">
                                <strong>Two Bedroom</strong>
                                <div style="font-size: 0.8rem; color: #666;">4-5 persons, 2 bedrooms</div>
                            </div>
                            <div class="room-type-option" onclick="toggleRoomType(this, 'three_bedroom')">
                                <input type="checkbox" name="room_types[]" value="three_bedroom" onchange="toggleRoomTypeDetails('three_bedroom')">
                                <strong>Three Bedroom</strong>
                                <div style="font-size: 0.8rem; color: #666;">6-8 persons, 3 bedrooms</div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Room Distribution (shown when room types are selected) -->
                    <div id="roomDistributionSection" style="display: none;">
                        <h5 style="margin-bottom: 1rem;">Room Distribution</h5>
                        <div id="roomDistribution" class="room-distribution">
                            <!-- Distribution will be populated by JavaScript -->
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="facilities">
                            <i class="fas fa-list"></i> Facilities
                        </label>
                        <input type="text" id="facilities" name="facilities" placeholder="e.g., WiFi, Study Room, Kitchen, Laundry">
                    </div>
                    
                    <div class="form-group">
                        <label for="description">
                            <i class="fas fa-comment"></i> Description
                        </label>
                        <textarea id="description" name="description" rows="3" placeholder="Brief description of the hostel"></textarea>
                    </div>
                </div>
                <input type="hidden" name="add_hostel" value="1">
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" name="add_hostel" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Add Hostel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- Edit Hostel Modal -->
    <div id="editHostelModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h4><i class="fas fa-edit"></i> Edit Hostel</h4>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-body" id="editHostelForm">
                    <!-- Form will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" name="update_hostel" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Hostel
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <!-- View Rooms Modal -->
    <div id="viewRoomsModal" class="modal">
        <div class="modal-content" style="max-width: 1000px;">
            <div class="modal-header">
                <h4><i class="fas fa-bed"></i> Manage Hostel Rooms</h4>
                <button class="modal-close">&times;</button>
            </div>
            <div class="modal-body">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                    <h5 id="roomsHostelName">Hostel Rooms</h5>
                    <button class="btn btn-primary" onclick="showAddRoomForm()">
                        <i class="fas fa-plus"></i> Add Room
                    </button>
                </div>
                
                <!-- Add Room Form (initially hidden) -->
                <div id="addRoomForm" style="display: none; background: #f8f9fa; padding: 1.5rem; border-radius: 10px; margin-bottom: 2rem;">
                    <h6 style="margin-bottom: 1rem;">Add New Room</h6>
                    <form method="POST">
                        <input type="hidden" id="roomHostelId" name="hostel_id">
                        <div class="form-row">
                            <div class="form-group">
                                <label for="room_number">Room Number *</label>
                                <input type="text" id="room_number" name="room_number" required>
                            </div>
                            <div class="form-group">
                                <label for="capacity">Capacity *</label>
                                <input type="number" id="capacity" name="capacity" min="1" max="20" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group">
                                <label for="room_type">Room Type *</label>
                                <select id="room_type" name="room_type" required>
                                    <option value="">Select type</option>
                                    <option value="single">Single Room</option>
                                    <option value="double">Double Room</option>
                                    <option value="triple">Triple Room</option>
                                    <option value="quad">Quad Room</option>
                                    <option value="room_parlor">Room & Parlor</option>
                                    <option value="two_bedroom">Two Bedroom</option>
                                    <option value="three_bedroom">Three Bedroom</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="price">Price *</label>
                                <input type="number" id="price" name="price" step="0.01" min="0" required>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="room_facilities">Room Facilities</label>
                            <input type="text" id="room_facilities" name="facilities" placeholder="e.g., AC, Private Bathroom, Balcony">
                        </div>
                        <input type="hidden" name="add_room" value="1">
                        <div style="display: flex; gap: 1rem;">
                            <button type="submit" name="add_room" class="btn btn-success">
                                <i class="fas fa-plus"></i> Add Room
                            </button>
                            <button type="button" class="btn btn-secondary" onclick="hideAddRoomForm()">
                                Cancel
                            </button>
                        </div>
                    </form>
                </div>
                
                <div id="roomsList">
                    <!-- Rooms will be loaded here -->
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-modal-close>Close</button>
            </div>
        </div>
    </div>
    
    <!-- Edit Room Modal -->
    <div id="editRoomModal" class="modal">
        <div class="modal-content" style="max-width: 600px;">
            <div class="modal-header">
                <h4><i class="fas fa-edit"></i> Edit Room</h4>
                <button class="modal-close">&times;</button>
            </div>
            <form method="POST">
                <div class="modal-body" id="editRoomForm">
                    <!-- Form will be populated by JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-modal-close>Cancel</button>
                    <button type="submit" name="update_room" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Room
                    </button>
                </div>
            </form>
        </div>
    </div>
    
    <script src="../assets/js/main.js"></script>
    <script>
        // Store custom capacities
        const customCapacities = {
            'room_parlor': 3,
            'two_bedroom': 5,
            'three_bedroom': 8
        };

        // Room type management
        function toggleRoomType(element, roomType) {
            const checkbox = element.querySelector('input[type="checkbox"]');
            checkbox.checked = !checkbox.checked;
            element.classList.toggle('selected', checkbox.checked);
            toggleRoomTypeDetails(roomType);
            updateRoomDistribution();
        }
        
        function toggleRoomTypeDetails(roomType) {
            const checkbox = document.querySelector(`input[value="${roomType}"]`);
            const detailsDiv = document.getElementById(`${roomType}Details`);
            
            if (checkbox.checked && !detailsDiv) {
                // Create details section if it doesn't exist
                const roomTypesGrid = document.querySelector('.room-types-grid');
                const detailsDiv = document.createElement('div');
                detailsDiv.id = `${roomType}Details`;
                detailsDiv.className = 'room-type-details active';
                detailsDiv.innerHTML = getRoomTypeDetailsHTML(roomType);
                roomTypesGrid.parentNode.insertBefore(detailsDiv, roomTypesGrid.nextSibling);
            } else if (!checkbox.checked && detailsDiv) {
                detailsDiv.remove();
            }
        }
        
        function getRoomTypeDetailsHTML(roomType) {
            const roomDetails = {
                'single': { capacity: 1, fixed: true },
                'double': { capacity: 2, fixed: true },
                'triple': { capacity: 3, fixed: true },
                'quad': { capacity: 4, fixed: true },
                'room_parlor': { capacity: customCapacities.room_parlor || 1, fixed: false, min: 1 },
                'two_bedroom': { capacity: customCapacities.two_bedroom || 2, fixed: false, min: 2 },
                'three_bedroom': { capacity: customCapacities.three_bedroom || 3, fixed: false, min: 3 }
            };
            
            const details = roomDetails[roomType];

            if (details.fixed) {
                return `
                    <h6>${roomType.replace('_', ' ').toUpperCase()} Details</h6>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label>Capacity: ${details.capacity} persons</label>
                        </div>
                        <div>
                            <label>Price: exact from database (no change)</label>
                        </div>
                    </div>
                `;
            } else {
                return `
                    <h6>${roomType.replace('_', ' ').toUpperCase()} Details</h6>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label for="${roomType}_capacity">Custom Capacity *</label>
                            <input type="number" id="${roomType}_capacity" 
                                name="custom_capacity[${roomType}]" 
                                class="custom-capacity-input"
                                min="${getDefaultCapacity(roomType)}"
                                max="20" 
                                value="${details.capacity}" 
                                required 
                                onchange="updateCustomCapacity('${roomType}', this.value)">
                        </div>
                        <div>
                            <label>Price: exact from database (no change)</label>
                        </div>
                    </div>
                `;
            }
        }

        
        function updateCustomCapacity(roomType, capacity) {
            customCapacities[roomType] = parseInt(capacity);
            updateRoomDistribution();
        }
        
        function updateRoomDistribution() {
            const totalRooms = parseInt(document.getElementById('number_of_rooms').value) || 0;
            const selectedTypes = Array.from(document.querySelectorAll('input[name="room_types[]"]:checked'))
                .map(cb => cb.value);
            
            if (totalRooms > 0 && selectedTypes.length > 0) {
                document.getElementById('roomDistributionSection').style.display = 'block';
                
                let distributionHTML = '';
                selectedTypes.forEach(type => {
                    const roomCount = Math.ceil(totalRooms / selectedTypes.length);
                    const capacity = customCapacities[type] || getDefaultCapacity(type);
                    distributionHTML += `
                        <div class="distribution-item">
                            <div style="font-weight: bold; text-transform: capitalize;">${type.replace('_', ' ')}</div>
                            <div style="font-size: 1.2rem; color: #2c5aa0;">${roomCount} rooms</div>
                            <div style="font-size: 0.9rem; color: #666;">${capacity} persons each</div>
                        </div>
                    `;
                });
                
                document.getElementById('roomDistribution').innerHTML = distributionHTML;
            } else {
                document.getElementById('roomDistributionSection').style.display = 'none';
            }
        }
        
        function getDefaultCapacity(roomType) {
            const capacities = {
                'single': 1,
                'double': 2,
                'triple': 3,
                'quad': 4,
                'room_parlor': 1,
                'two_bedroom': 2,
                'three_bedroom': 3
            };
            return capacities[roomType] || 1;
        }

        
        // Existing functions
        function editHostel(hostelId) {
            fetch(`../get_hostel.php?id=${hostelId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const hostel = data.hostel;
                        document.getElementById('editHostelForm').innerHTML = `
                            <input type="hidden" name="hostel_id" value="${hostel.id}">
                            <input type="hidden" name="current_image" value="${hostel.image_path || ''}">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_hostel_name">
                                        <i class="fas fa-building"></i> Hostel Name *
                                    </label>
                                    <input type="text" id="edit_hostel_name" name="hostel_name" value="${hostel.hostel_name}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_gender">
                                        <i class="fas fa-users"></i> Gender *
                                    </label>
                                    <select id="edit_gender" name="gender" required>
                                        <option value="male" ${hostel.gender === 'male' ? 'selected' : ''}>Male</option>
                                        <option value="female" ${hostel.gender === 'female' ? 'selected' : ''}>Female</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_price_per_session">
                                        <i class="fas fa-naira-sign"></i> Price per Session *
                                    </label>
                                    <input type="number" id="edit_price_per_session" name="price_per_session" value="${hostel.price_per_session}" step="0.01" min="0" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_facilities">
                                    <i class="fas fa-list"></i> Facilities
                                </label>
                                <input type="text" id="edit_facilities" name="facilities" value="${hostel.facilities || ''}" placeholder="e.g., WiFi, Study Room, Kitchen, Laundry">
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_description">
                                    <i class="fas fa-comment"></i> Description
                                </label>
                                <textarea id="edit_description" name="description" rows="3" placeholder="Brief description of the hostel">${hostel.description || ''}</textarea>
                            </div>
                        `;
                        openModal(document.getElementById('editHostelModal'));
                    } else {
                        alert('Failed to load hostel details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading hostel details');
                });
        }
        
        function deleteHostel(hostelId) {
            if (confirm('Are you sure you want to delete this hostel? This will also delete all associated rooms.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="hostel_id" value="${hostelId}">
                    <input type="hidden" name="delete_hostel" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function viewRooms(hostelId) {
            fetch(`../get_rooms.php?hostel_id=${hostelId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const rooms = data.rooms;
                        const hostel = data.hostel;
                        document.getElementById('roomHostelId').value = hostelId;
                        document.getElementById('roomsHostelName').textContent = `${hostel.hostel_name} - Rooms Management`;
                        
                        let roomsHTML = '';
                        if (rooms.length === 0) {
                            roomsHTML = '<p style="text-align: center; color: #666; padding: 2rem;">No rooms found for this hostel.</p>';
                        } else {
                            roomsHTML = `
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                                    <div style="background: #e8f4fd; padding: 1rem; border-radius: 10px; text-align: center;">
                                        <h6 style="color: #2c5aa0; margin-bottom: 0.5rem;">Total Rooms</h6>
                                        <div style="font-size: 2rem; font-weight: bold; color: #2c5aa0;">${rooms.length}</div>
                                    </div>
                                    <div style="background: #e8f8f0; padding: 1rem; border-radius: 10px; text-align: center;">
                                        <h6 style="color: #059669; margin-bottom: 0.5rem;">Total Capacity</h6>
                                        <div style="font-size: 2rem; font-weight: bold; color: #059669;">${rooms.reduce((sum, room) => sum + room.capacity, 0)}</div>
                                    </div>
                                    <div style="background: #fef3c7; padding: 1rem; border-radius: 10px; text-align: center;">
                                        <h6 style="color: #d97706; margin-bottom: 0.5rem;">Available Spaces</h6>
                                        <div style="font-size: 2rem; font-weight: bold; color: #d97706;">${rooms.reduce((sum, room) => sum + (room.capacity - room.occupied), 0)}</div>
                                    </div>
                                </div>
                                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 1rem;">
                            `;
                            
                            rooms.forEach(room => {
                                const availableSpaces = room.capacity - room.occupied;
                                roomsHTML += `
                                    <div style="background: #f8f9fa; padding: 1.5rem; border-radius: 10px; border: 1px solid #e5e7eb; position: relative;">
                                        <div style="position: absolute; top: 10px; right: 10px; display: flex; gap: 0.5rem;">
                                            <span class="badge ${room.status === 'available' ? 'badge-success' : 'badge-secondary'}">
                                                ${room.status}
                                            </span>
                                            <button class="btn btn-sm btn-warning" onclick="editRoom(${room.id})" style="padding: 0.2rem 0.5rem;">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" onclick="deleteRoom(${room.id})" style="padding: 0.2rem 0.5rem;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                        
                                        <h6 style="color: #2c5aa0; margin-bottom: 1rem;">Room ${room.room_number}</h6>
                                        
                                        <div class="info-item">
                                            <span class="info-label">Type:</span>
                                            <span class="info-value" style="text-transform: capitalize;">${room.room_type.replace('_', ' ')}</span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Capacity:</span>
                                            <span class="info-value">
                                                ${availableSpaces}/${room.capacity} available
                                            </span>
                                        </div>
                                        <div class="info-item">
                                            <span class="info-label">Price:</span>
                                            <span class="info-value">₦${parseFloat(room.price).toLocaleString()}</span>
                                        </div>
                                        ${room.facilities ? `
                                            <div class="info-item">
                                                <span class="info-label">Facilities:</span>
                                                <span class="info-value">${room.facilities}</span>
                                            </div>
                                        ` : ''}
                                        <div class="info-item">
                                            <span class="info-label">Occupied:</span>
                                            <span class="info-value">${room.occupied} students</span>
                                        </div>
                                    </div>
                                `;
                            });
                            roomsHTML += '</div>';
                        }
                        
                        document.getElementById('roomsList').innerHTML = roomsHTML;
                        openModal(document.getElementById('viewRoomsModal'));
                    } else {
                        alert('Failed to load rooms');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading rooms');
                });
        }
        
        function editRoom(roomId) {
            fetch(`../get_room.php?id=${roomId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const room = data.room;
                        document.getElementById('editRoomForm').innerHTML = `
                            <input type="hidden" name="room_id" value="${room.id}">
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_room_number">Room Number *</label>
                                    <input type="text" id="edit_room_number" name="room_number" value="${room.room_number}" required>
                                </div>
                                <div class="form-group">
                                    <label for="edit_capacity">Capacity *</label>
                                    <input type="number" id="edit_capacity" name="capacity" value="${room.capacity}" min="1" max="20" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit_room_type">Room Type *</label>
                                    <select id="edit_room_type" name="room_type" required>
                                        <option value="single" ${room.room_type === 'single' ? 'selected' : ''}>Single Room</option>
                                        <option value="double" ${room.room_type === 'double' ? 'selected' : ''}>Double Room</option>
                                        <option value="triple" ${room.room_type === 'triple' ? 'selected' : ''}>Triple Room</option>
                                        <option value="quad" ${room.room_type === 'quad' ? 'selected' : ''}>Quad Room</option>
                                        <option value="room_parlor" ${room.room_type === 'room_parlor' ? 'selected' : ''}>Room & Parlor</option>
                                        <option value="two_bedroom" ${room.room_type === 'two_bedroom' ? 'selected' : ''}>Two Bedroom</option>
                                        <option value="three_bedroom" ${room.room_type === 'three_bedroom' ? 'selected' : ''}>Three Bedroom</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit_price">Price *</label>
                                    <input type="number" id="edit_price" name="price" value="${room.price}" step="0.01" min="0" required>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <label for="edit_facilities">Room Facilities</label>
                                <input type="text" id="edit_facilities" name="facilities" value="${room.facilities || ''}" placeholder="e.g., AC, Private Bathroom, Balcony">
                            </div>
                        `;
                        openModal(document.getElementById('editRoomModal'));
                    } else {
                        alert('Failed to load room details');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Error loading room details');
                });
        }
        
        function deleteRoom(roomId) {
            if (confirm('Are you sure you want to delete this room? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="room_id" value="${roomId}">
                    <input type="hidden" name="delete_room" value="1">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        function showAddRoomForm() {
            document.getElementById('addRoomForm').style.display = 'block';
        }
        
        function hideAddRoomForm() {
            document.getElementById('addRoomForm').style.display = 'none';
            document.getElementById('addRoomForm').querySelector('form').reset();
        }
    </script>
</body>
</html>