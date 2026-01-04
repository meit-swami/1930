<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
requireLogin();

header('Content-Type: application/json');

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'POST') {
    // Create new session
    $data = json_decode(file_get_contents('php://input'), true);
    
    $id = uniqid('session_', true);
    $callerName = $data['caller_name'] ?? 'Anonymous';
    $language = $data['language'] ?? 'english';
    $startTime = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("INSERT INTO sessions (id, caller_name, language, status, start_time, duration) VALUES (?, ?, ?, 'active', ?, 0)");
    $stmt->bind_param("ssss", $id, $callerName, $language, $startTime);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'session_id' => $id]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create session']);
    }
    $stmt->close();
    
} elseif ($method === 'PATCH') {
    // Update session
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Session ID required']);
        exit();
    }
    
    $data = json_decode(file_get_contents('php://input'), true);
    $updates = [];
    $params = [];
    $types = '';
    
    if (isset($data['status'])) {
        $updates[] = "status = ?";
        $params[] = $data['status'];
        $types .= 's';
    }
    
    if (isset($data['duration'])) {
        $updates[] = "duration = ?";
        $params[] = $data['duration'];
        $types .= 'i';
    }
    
    if (isset($data['end_time'])) {
        $updates[] = "end_time = ?";
        $params[] = $data['end_time'];
        $types .= 's';
    } else if (isset($data['status']) && $data['status'] === 'completed') {
        $updates[] = "end_time = ?";
        $params[] = date('Y-m-d H:i:s');
        $types .= 's';
    }
    
    if (empty($updates)) {
        echo json_encode(['success' => false, 'error' => 'No updates provided']);
        exit();
    }
    
    $sql = "UPDATE sessions SET " . implode(', ', $updates) . " WHERE id = ?";
    $params[] = $id;
    $types .= 's';
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to update session']);
    }
    $stmt->close();
    
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

