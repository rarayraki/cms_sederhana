<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $username = $_POST['username'];
    $email = $_POST['email'];
    $full_name = $_POST['full_name'];
    $role = $_POST['role'];
    $password = $_POST['password'];

    // Check if username or email already exists
    $stmt = $conn->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
    $stmt->bind_param("ssi", $username, $email, $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Username or email already exists']);
        exit();
    }

    if ($id > 0) {
        // Update existing user
        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, role = ?, password = ? WHERE id = ?");
            $stmt->bind_param("sssssi", $username, $email, $full_name, $role, $hashed_password, $id);
        } else {
            $stmt = $conn->prepare("UPDATE users SET username = ?, email = ?, full_name = ?, role = ? WHERE id = ?");
            $stmt->bind_param("ssssi", $username, $email, $full_name, $role, $id);
        }
    } else {
        // Create new user
        if (empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Password is required for new users']);
            exit();
        }
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO users (username, email, full_name, role, password) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $username, $email, $full_name, $role, $hashed_password);
    }

    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => $stmt->error]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?> 