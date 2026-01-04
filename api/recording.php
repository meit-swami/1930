<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
requireLogin();

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? null;
    
    if (!$id) {
        http_response_code(404);
        exit();
    }
    
    $stmt = $conn->prepare("SELECT * FROM recordings WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $recording = $result->fetch_assoc();
        $filePath = __DIR__ . '/../uploads/recordings/' . $recording['filename'];
        
        if (file_exists($filePath)) {
            header('Content-Type: ' . ($recording['mime_type'] ?? 'audio/webm'));
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
        } else {
            http_response_code(404);
        }
    } else {
        http_response_code(404);
    }
    $stmt->close();
    
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    header('Content-Type: application/json');
    
    $id = $_GET['id'] ?? null;
    if (!$id) {
        echo json_encode(['success' => false, 'error' => 'Recording ID required']);
        exit();
    }
    
    // Get recording info first
    $stmt = $conn->prepare("SELECT filename FROM recordings WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $recording = $result->fetch_assoc();
        $filePath = __DIR__ . '/../uploads/recordings/' . $recording['filename'];
        
        // Delete from database
        $stmt2 = $conn->prepare("DELETE FROM recordings WHERE id = ?");
        $stmt2->bind_param("s", $id);
        
        if ($stmt2->execute()) {
            // Delete file if exists
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'error' => 'Failed to delete recording']);
        }
        $stmt2->close();
    } else {
        echo json_encode(['success' => false, 'error' => 'Recording not found']);
    }
    $stmt->close();
} else {
    http_response_code(405);
}

