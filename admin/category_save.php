<?php
require_once '../config/database.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    die(json_encode(['success' => false, 'message' => 'Unauthorized']));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $name = $_POST['name'];
    $description = $_POST['description'];
    $slug = strtolower(str_replace(' ', '-', $name));

    if ($id > 0) {
        // Update existing category
        $stmt = $conn->prepare("UPDATE categories SET name = ?, description = ?, slug = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $description, $slug, $id);
    } else {
        // Create new category
        $stmt = $conn->prepare("INSERT INTO categories (name, description, slug) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $description, $slug);
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