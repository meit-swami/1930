<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
requireLogin();

header('Content-Type: application/json');

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'PATCH') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Queue ID required']);
        exit();
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $status = $data['status'] ?? null;
    
    if ($status) {
        $stmt = $conn->prepare("UPDATE call_queue SET status = ? WHERE id = ?");
        $stmt->bind_param("ss", $status, $id);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to update queue']);
        }
        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Status required']);
    }
    
} elseif ($method === 'DELETE') {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Queue ID required']);
        exit();
    }
    
    $stmt = $conn->prepare("DELETE FROM call_queue WHERE id = ?");
    $stmt->bind_param("s", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to delete queue entry']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

