<?php
/**
 * Webhook Handler for Omni Dimension Events
 * Receives events from Python service and updates database
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/omnidimension.php';

header('Content-Type: application/json');

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

$conn = getDBConnection();
$data = json_decode(file_get_contents('php://input'), true);

if (!$data) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit();
}

$event_type = $data['event_type'] ?? '';
$event_data = $data['data'] ?? [];

try {
    switch ($event_type) {
        case 'call.created':
            // Call was created
            $call_id = $event_data['call_id'] ?? null;
            $session_id = $event_data['session_id'] ?? null;
            
            if ($call_id && $session_id) {
                $stmt = $conn->prepare("UPDATE sessions SET omni_call_id = ?, status = 'active' WHERE id = ?");
                $stmt->bind_param("ss", $call_id, $session_id);
                $stmt->execute();
                $stmt->close();
            }
            break;
        
        case 'call.started':
            // Call started (connected)
            $call_id = $event_data['call_id'] ?? null;
            $session_id = $event_data['session_id'] ?? null;
            
            if ($call_id && $session_id) {
                $stmt = $conn->prepare("UPDATE sessions SET status = 'active' WHERE id = ?");
                $stmt->bind_param("s", $session_id);
                $stmt->execute();
                $stmt->close();
            }
            break;
        
        case 'call.ended':
        case 'call.disconnected':
            // Call ended or disconnected
            $call_id = $event_data['call_id'] ?? null;
            $session_id = $event_data['session_id'] ?? null;
            $duration = $event_data['duration'] ?? 0;
            
            if ($session_id) {
                $end_time = date('Y-m-d H:i:s');
                $stmt = $conn->prepare("UPDATE sessions SET status = 'completed', end_time = ?, duration = ? WHERE id = ?");
                $stmt->bind_param("sis", $end_time, $duration, $session_id);
                $stmt->execute();
                $stmt->close();
            }
            break;
        
        case 'chat.created':
            // Chat was created
            $chat_id = $event_data['chat_id'] ?? null;
            $session_id = $event_data['session_id'] ?? null;
            
            if ($chat_id && $session_id) {
                $stmt = $conn->prepare("UPDATE sessions SET omni_chat_id = ?, status = 'active' WHERE id = ?");
                $stmt->bind_param("ss", $chat_id, $session_id);
                $stmt->execute();
                $stmt->close();
            }
            break;
        
        case 'chat.message':
            // Chat message received
            $chat_id = $event_data['chat_id'] ?? null;
            $message = $event_data['message'] ?? '';
            $role = $event_data['role'] ?? 'user';
            $response = $event_data['response'] ?? null;
            
            // Find session by chat_id
            $stmt = $conn->prepare("SELECT id FROM sessions WHERE omni_chat_id = ?");
            $stmt->bind_param("s", $chat_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $row = $result->fetch_assoc();
                $session_id = $row['id'];
                
                // Save message
                $message_id = uniqid('msg_', true);
                $timestamp = date('Y-m-d H:i:s');
                $stmt = $conn->prepare("INSERT INTO chat_messages (id, session_id, role, content, timestamp) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $message_id, $session_id, $role, $message, $timestamp);
                $stmt->execute();
                $stmt->close();
                
                // Save response if available
                if ($response && $role === 'user') {
                    $response_id = uniqid('msg_', true);
                    $response_content = is_string($response) ? $response : json_encode($response);
                    $stmt = $conn->prepare("INSERT INTO chat_messages (id, session_id, role, content, timestamp) VALUES (?, ?, 'assistant', ?, ?)");
                    $stmt->bind_param("ssss", $response_id, $session_id, $response_content, $timestamp);
                    $stmt->execute();
                    $stmt->close();
                }
            }
            $stmt->close();
            break;
        
        case 'chat.ended':
            // Chat ended
            $chat_id = $event_data['chat_id'] ?? null;
            $session_id = $event_data['session_id'] ?? null;
            
            if ($session_id) {
                $end_time = date('Y-m-d H:i:s');
                $stmt = $conn->prepare("UPDATE sessions SET status = 'completed', end_time = ? WHERE id = ?");
                $stmt->bind_param("ss", $end_time, $session_id);
                $stmt->execute();
                $stmt->close();
            }
            break;
    }
    
    echo json_encode(['success' => true, 'event_type' => $event_type]);
} catch (Exception $e) {
    error_log("Webhook error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(['error' => 'Internal server error']);
}

