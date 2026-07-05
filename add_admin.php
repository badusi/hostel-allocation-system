<?php
$mysqli = new mysqli('localhost', 'root', '', 'hostel_allocation');


if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}

// New admin credentials
$username = "admin";
$password = "admin123";    // Change this to a strong password
$full_name = "System Administrator";
$email = "admin@fedpolyayede.edu.ng";
$role = "super_admin";

// Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Insert into the database
$stmt = $mysqli->prepare("INSERT INTO admin_users (username, password, full_name, email, role) VALUES (?, ?, ?, ?, ?)");
$stmt->bind_param("sssss", $username, $hashed_password, $full_name, $email, $role);

if ($stmt->execute()) {
    echo "✅ New admin added successfully!";
} else {
    echo "❌ Error: " . $stmt->error;
}

$stmt->close();
$mysqli->close();
?>
