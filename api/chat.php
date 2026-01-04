<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
requireLogin();

header('Content-Type: application/json');

$conn = getDBConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    $sessionId = $data['session_id'] ?? null;
    $message = $data['message'] ?? '';
    
    if (!$sessionId || !$message) {
        echo json_encode(['success' => false, 'error' => 'Session ID and message required']);
        exit();
    }
    
    // Save user message
    $messageId = uniqid('msg_', true);
    $timestamp = date('Y-m-d H:i:s');
    
    $stmt = $conn->prepare("INSERT INTO chat_messages (id, session_id, role, content, timestamp) VALUES (?, ?, 'user', ?, ?)");
    $stmt->bind_param("ssss", $messageId, $sessionId, $message, $timestamp);
    $stmt->execute();
    $stmt->close();
    
    // Simple AI response (you can integrate with Gemini API here)
    $response = "Thank you for your message. I'm here to help you with your fraud complaint. Please provide more details about the incident.";
    
    // Save AI response
    $responseId = uniqid('msg_', true);
    $stmt = $conn->prepare("INSERT INTO chat_messages (id, session_id, role, content, timestamp) VALUES (?, ?, 'assistant', ?, ?)");
    $stmt->bind_param("ssss", $responseId, $sessionId, $response, $timestamp);
    $stmt->execute();
    $stmt->close();
    
    echo json_encode(['success' => true, 'response' => $response]);
} else {
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
}

