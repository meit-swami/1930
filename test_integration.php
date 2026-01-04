<?php
/**
 * Integration Test Page
 * Tests Omni Dimension integration endpoints
 */

require_once 'config/database.php';
require_once 'config/session.php';
require_once 'config/omnidimension.php';
requireLogin();

$pageTitle = 'Integration Test';
require_once 'includes/header.php';

$python_service_url = PYTHON_SERVICE_URL;
$api_key = OMNIDIMENSION_API_KEY;
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">
            <i class="bi bi-bug me-2"></i>
            Integration Test
        </h2>
        <p class="text-muted">Test Omni Dimension integration endpoints</p>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">Configuration</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <strong>Python Service URL:</strong><br>
                    <code><?php echo htmlspecialchars($python_service_url); ?></code>
                </div>
                <div class="mb-2">
                    <strong>API Key:</strong><br>
                    <code><?php echo htmlspecialchars(substr($api_key, 0, 20)) . '...'; ?></code>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">Test Results</h5>
            </div>
            <div class="card-body" id="testResults">
                <p class="text-muted">Click "Run Tests" to check integration</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Test Actions</h5>
                <button class="btn btn-primary" onclick="runAllTests()">
                    <i class="bi bi-play-circle me-2"></i> Run All Tests
                </button>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <button class="btn btn-outline-primary w-100" onclick="testHealth()">
                            <i class="bi bi-heart-pulse me-2"></i> Test Health
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-outline-info w-100" onclick="testAgentInfo()">
                            <i class="bi bi-robot me-2"></i> Test Agent Info
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button class="btn btn-outline-success w-100" onclick="testPHPAPI()">
                            <i class="bi bi-code-slash me-2"></i> Test PHP API
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function showResult(testName, success, message, data = null) {
    const resultsDiv = document.getElementById('testResults');
    const badgeClass = success ? 'bg-success' : 'bg-danger';
    const icon = success ? '✓' : '✗';
    
    let html = `
        <div class="mb-2 p-2 border rounded">
            <div class="d-flex justify-content-between align-items-center">
                <strong>${testName}</strong>
                <span class="badge ${badgeClass}">${icon}</span>
            </div>
            <div class="mt-2">
                <small class="text-muted">${message}</small>
            </div>
    `;
    
    if (data) {
        html += `
            <div class="mt-2">
                <pre class="bg-light p-2 rounded" style="font-size: 0.85em; max-height: 200px; overflow-y: auto;">${JSON.stringify(data, null, 2)}</pre>
            </div>
        `;
    }
    
    html += '</div>';
    resultsDiv.innerHTML = html;
}

async function testHealth() {
    showResult('Health Check', false, 'Testing...');
    
    try {
        const response = await fetch('<?php echo $python_service_url; ?>/health');
        const data = await response.json();
        
        if (response.ok && data.status === 'ok') {
            showResult('Health Check', true, 'Service is running and healthy', data);
        } else {
            showResult('Health Check', false, 'Service responded but status is not OK', data);
        }
    } catch (error) {
        showResult('Health Check', false, `Error: ${error.message}. Make sure Python service is running on port 8000.`);
    }
}

async function testAgentInfo() {
    showResult('Agent Info', false, 'Testing...');
    
    try {
        const response = await fetch('<?php echo $python_service_url; ?>/agent/info');
        const data = await response.json();
        
        if (response.ok && data.success) {
            showResult('Agent Info', true, 'Agent information retrieved successfully', data);
        } else {
            showResult('Agent Info', false, 'Failed to get agent info', data);
        }
    } catch (error) {
        showResult('Agent Info', false, `Error: ${error.message}`);
    }
}

async function testPHPAPI() {
    showResult('PHP API Proxy', false, 'Testing...');
    
    try {
        const response = await fetch('api/omnidimension.php?action=agent_info');
        const data = await response.json();
        
        if (data.success || data.agent_id) {
            showResult('PHP API Proxy', true, 'PHP API is working and can communicate with Python service', data);
        } else {
            showResult('PHP API Proxy', false, 'PHP API responded but may have errors', data);
        }
    } catch (error) {
        showResult('PHP API Proxy', false, `Error: ${error.message}`);
    }
}

async function runAllTests() {
    document.getElementById('testResults').innerHTML = '<p class="text-muted">Running tests...</p>';
    
    await testHealth();
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    await testAgentInfo();
    await new Promise(resolve => setTimeout(resolve, 1000));
    
    await testPHPAPI();
}
</script>

<?php require_once 'includes/footer.php'; ?>

