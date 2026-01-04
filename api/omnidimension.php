<?php
/**
 * Omni Dimension API Proxy
 * Communicates with Python service for Omni Dimension integration
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../config/omnidimension.php';
requireLogin();

header('Content-Type: application/json');

$conn = getDBConnection();
$method = $_SERVER['REQUEST_METHOD'];
$python_service_url = PYTHON_SERVICE_URL;

// Helper function to call Python service
function callPythonService($endpoint, $method = 'GET', $data = null) {
    global $python_service_url;
    
    $url = rtrim($python_service_url, '/') . '/' . ltrim($endpoint, '/');
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    } elseif ($method === 'PATCH') {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PATCH');
        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        }
    }
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code >= 200 && $http_code < 300) {
        return json_decode($response, true);
    } else {
        error_log("Python service error: HTTP $http_code - $response");
        return ['error' => 'Service unavailable', 'http_code' => $http_code];
    }
}

// Route: GET /api/omnidimension.php/agent/info
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'agent_info') {
    $result = callPythonService('/agent/info');
    echo json_encode($result);
    exit();
}

// Route: POST /api/omnidimension.php/call/create
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'create_call') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Create session in database if not exists
    $session_id = $data['session_id'] ?? uniqid('session_', true);
    $caller_name = $data['caller_name'] ?? 'Anonymous';
    $language = $data['language'] ?? 'english';
    $call_type = $data['call_type'] ?? 'inbound';
    $phone_number = $data['phone_number'] ?? null;
    
    // Check if session exists
    $stmt = $conn->prepare("SELECT id FROM sessions WHERE id = ?");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Create new session
        $start_time = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO sessions (id, caller_name, language, status, start_time, duration, call_type, phone_number) VALUES (?, ?, ?, 'active', ?, 0, ?, ?)");
        $stmt->bind_param("ssssss", $session_id, $caller_name, $language, $start_time, $call_type, $phone_number);
        $stmt->execute();
    }
    $stmt->close();
    
    // Call Python service
    $result = callPythonService('/call/create', 'POST', [
        'call_type' => $call_type,
        'phone_number' => $phone_number,
        'session_id' => $session_id,
        'caller_name' => $caller_name,
        'language' => $language
    ]);
    
    if (isset($result['call_id'])) {
        // Update session with call_id
        $call_id = $result['call_id'];
        $stmt = $conn->prepare("UPDATE sessions SET omni_call_id = ? WHERE id = ?");
        $stmt->bind_param("ss", $call_id, $session_id);
        $stmt->execute();
        $stmt->close();
    }
    
    echo json_encode($result);
    exit();
}

// Route: POST /api/omnidimension.php/call/end
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'end_call') {
    $data = json_decode(file_get_contents('php://input'), true);
    $call_id = $data['call_id'] ?? $_GET['call_id'] ?? null;
    
    if (!$call_id) {
        echo json_encode(['error' => 'Call ID required']);
        exit();
    }
    
    // Call Python service to end call
    $result = callPythonService("/call/end/$call_id", 'POST');
    
    // Update session status
    if (isset($data['session_id'])) {
        $session_id = $data['session_id'];
        $stmt = $conn->prepare("UPDATE sessions SET status = 'completed', end_time = NOW() WHERE id = ?");
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $stmt->close();
    }
    
    echo json_encode($result);
    exit();
}

// Route: GET /api/omnidimension.php/call/status
if ($method === 'GET' && isset($_GET['action']) && $_GET['action'] === 'call_status') {
    $call_id = $_GET['call_id'] ?? null;
    
    if (!$call_id) {
        echo json_encode(['error' => 'Call ID required']);
        exit();
    }
    
    $result = callPythonService("/call/status/$call_id");
    echo json_encode($result);
    exit();
}

// Route: POST /api/omnidimension.php/chat/create
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'create_chat') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Create session in database if not exists
    $session_id = $data['session_id'] ?? uniqid('session_', true);
    $caller_name = $data['caller_name'] ?? 'Anonymous';
    $language = $data['language'] ?? 'english';
    
    // Check if session exists
    $stmt = $conn->prepare("SELECT id FROM sessions WHERE id = ?");
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        // Create new session
        $start_time = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO sessions (id, caller_name, language, status, start_time, duration, call_type) VALUES (?, ?, ?, 'active', ?, 0, 'chat')");
        $stmt->bind_param("ssss", $session_id, $caller_name, $language, $start_time);
        $stmt->execute();
    }
    $stmt->close();
    
    // Call Python service
    $result = callPythonService('/chat/create', 'POST', [
        'session_id' => $session_id,
        'caller_name' => $caller_name,
        'language' => $language
    ]);
    
    if (isset($result['chat_id'])) {
        // Update session with chat_id
        $chat_id = $result['chat_id'];
        $stmt = $conn->prepare("UPDATE sessions SET omni_chat_id = ? WHERE id = ?");
        $stmt->bind_param("ss", $chat_id, $session_id);
        $stmt->execute();
        $stmt->close();
    }
    
    echo json_encode($result);
    exit();
}

// Route: POST /api/omnidimension.php/chat/send
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'send_chat') {
    $data = json_decode(file_get_contents('php://input'), true);
    $chat_id = $data['chat_id'] ?? null;
    $message = $data['message'] ?? '';
    $session_id = $data['session_id'] ?? null;
    
    if (!$chat_id || !$message) {
        echo json_encode(['error' => 'Chat ID and message required']);
        exit();
    }
    
    // Save user message to database
    if ($session_id) {
        $message_id = uniqid('msg_', true);
        $timestamp = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO chat_messages (id, session_id, role, content, timestamp) VALUES (?, ?, 'user', ?, ?)");
        $stmt->bind_param("ssss", $message_id, $session_id, $message, $timestamp);
        $stmt->execute();
        $stmt->close();
    }
    
    // Call Python service
    $result = callPythonService("/chat/send/$chat_id", 'POST', [
        'message' => $message,
        'role' => 'user'
    ]);
    
    // Save assistant response if available
    if ($session_id && isset($result['response'])) {
        $response_id = uniqid('msg_', true);
        $timestamp = date('Y-m-d H:i:s');
        $stmt = $conn->prepare("INSERT INTO chat_messages (id, session_id, role, content, timestamp) VALUES (?, ?, 'assistant', ?, ?)");
        $response_content = is_string($result['response']) ? $result['response'] : json_encode($result['response']);
        $stmt->bind_param("ssss", $response_id, $session_id, $response_content, $timestamp);
        $stmt->execute();
        $stmt->close();
    }
    
    echo json_encode($result);
    exit();
}

// Route: POST /api/omnidimension.php/chat/end
if ($method === 'POST' && isset($_GET['action']) && $_GET['action'] === 'end_chat') {
    $data = json_decode(file_get_contents('php://input'), true);
    $chat_id = $data['chat_id'] ?? $_GET['chat_id'] ?? null;
    
    if (!$chat_id) {
        echo json_encode(['error' => 'Chat ID required']);
        exit();
    }
    
    // Call Python service
    $result = callPythonService("/chat/end/$chat_id", 'POST');
    
    // Update session status
    if (isset($data['session_id'])) {
        $session_id = $data['session_id'];
        $stmt = $conn->prepare("UPDATE sessions SET status = 'completed', end_time = NOW() WHERE id = ?");
        $stmt->bind_param("s", $session_id);
        $stmt->execute();
        $stmt->close();
    }
    
    echo json_encode($result);
    exit();
}

// Default: method not allowed
echo json_encode(['error' => 'Invalid action or method']);
exit();

