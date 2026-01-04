<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
requireLogin();

header('Content-Type: application/json');

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Complaint ID required']);
        exit();
    }
    
    $stmt = $conn->prepare("SELECT * FROM complaints WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $complaint = $result->fetch_assoc();
        echo json_encode($complaint);
    } else {
        echo json_encode(['success' => false, 'error' => 'Complaint not found']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

