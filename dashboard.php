<?php
// Set Permissions-Policy header for microphone access (must be before any output)
header('Permissions-Policy: microphone=*, camera=*');

require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$pageTitle = 'Dashboard';
require_once 'includes/header.php';

$conn = getDBConnection();

// Get statistics
$stats = [];

// Total calls today
$today = date('Y-m-d');
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM sessions WHERE DATE(start_time) = '$today'");
    if ($result) {
        $stats['totalCallsToday'] = $result->fetch_assoc()['count'] ?? 0;
    } else {
        $stats['totalCallsToday'] = 0;
    }
} catch (Exception $e) {
    $stats['totalCallsToday'] = 0;
}

// Active sessions
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM sessions WHERE status = 'active'");
    if ($result) {
        $stats['activeSessions'] = $result->fetch_assoc()['count'] ?? 0;
    } else {
        $stats['activeSessions'] = 0;
    }
} catch (Exception $e) {
    $stats['activeSessions'] = 0;
}

// Resolved complaints
try {
    $result = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE status = 'resolved'");
    if ($result) {
        $stats['resolvedComplaints'] = $result->fetch_assoc()['count'] ?? 0;
    } else {
        $stats['resolvedComplaints'] = 0;
    }
} catch (Exception $e) {
    $stats['resolvedComplaints'] = 0;
}

// Average duration
try {
    $result = $conn->query("SELECT AVG(duration) as avg FROM sessions WHERE duration > 0");
    if ($result) {
        $avgDuration = $result->fetch_assoc()['avg'] ?? 0;
        $stats['averageDuration'] = round($avgDuration / 60, 1); // Convert to minutes
    } else {
        $stats['averageDuration'] = 0;
    }
} catch (Exception $e) {
    $stats['averageDuration'] = 0;
}

// Get Omni Dimension credits (if available)
$stats['credits'] = null;
try {
    $result = $conn->query("SELECT credits_available FROM omnidimension_credits ORDER BY last_updated DESC LIMIT 1");
    if ($result && $row = $result->fetch_assoc()) {
        $stats['credits'] = $row['credits_available'];
    }
} catch (Exception $e) {
    // Table might not exist yet
}

// Get recent sessions
$recentSessions = [];
try {
    $result = $conn->query("SELECT * FROM sessions ORDER BY start_time DESC LIMIT 5");
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $recentSessions[] = $row;
        }
    }
} catch (Exception $e) {
    // Table might not exist yet
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">
            <i class="bi bi-speedometer2 me-2"></i>
            Dashboard
        </h2>
        <p class="text-muted">Overview of your system</p>
    </div>
</div>

<!-- Statistics Cards -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Total Calls Today</h6>
                        <h3 class="mb-0"><?php echo $stats['totalCallsToday']; ?></h3>
                    </div>
                    <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-telephone text-primary fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Active Sessions</h6>
                        <h3 class="mb-0"><?php echo $stats['activeSessions']; ?></h3>
                    </div>
                    <div class="bg-success bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-activity text-success fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Resolved Complaints</h6>
                        <h3 class="mb-0"><?php echo $stats['resolvedComplaints']; ?></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-check-circle text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Avg. Duration</h6>
                        <h3 class="mb-0"><?php echo $stats['averageDuration']; ?> min</h3>
                    </div>
                    <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-clock text-warning fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Credits Card (if available) -->
<?php if ($stats['credits'] !== null): ?>
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted mb-2">Credits Available</h6>
                        <h3 class="mb-0"><?php echo number_format($stats['credits'], 2); ?></h3>
                    </div>
                    <div class="bg-info bg-opacity-10 rounded-circle p-3">
                        <i class="bi bi-wallet2 text-info fs-4"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- Recent Sessions -->
<div class="row">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">
                    <i class="bi bi-clock-history me-2"></i>
                    Recent Sessions
                </h5>
            </div>
            <div class="card-body">
                <?php if (empty($recentSessions)): ?>
                    <p class="text-muted text-center py-4">No sessions found</p>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Session ID</th>
                                    <th>Caller Name</th>
                                    <th>Language</th>
                                    <th>Status</th>
                                    <th>Duration</th>
                                    <th>Start Time</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentSessions as $session): ?>
                                    <tr>
                                        <td><code><?php echo substr($session['id'], 0, 8); ?>...</code></td>
                                        <td><?php echo htmlspecialchars($session['caller_name'] ?? 'N/A'); ?></td>
                                        <td>
                                            <span class="badge bg-secondary">
                                                <?php echo ucfirst($session['language'] ?? 'english'); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                            $status = $session['status'] ?? 'active';
                                            $badgeClass = $status === 'active' ? 'bg-success' : ($status === 'completed' ? 'bg-primary' : 'bg-warning');
                                            ?>
                                            <span class="badge <?php echo $badgeClass; ?>">
                                                <?php echo ucfirst($status); ?>
                                            </span>
                                        </td>
                                        <td><?php echo round(($session['duration'] ?? 0) / 60, 1); ?> min</td>
                                        <td><?php echo date('M d, Y H:i', strtotime($session['start_time'])); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Omni Dimension Web Widget -->
<script id="omnidimension-web-widget" async src="https://omnidim.io/web_widget.js?secret_key=a469de91883194b7ef7e7c7f67b661f4"></script>

<!-- Load credits on page load -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fetch latest credits from Omni Dimension
    fetch('api/omnidimension.php?action=agent_info')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.credits !== null && data.credits !== undefined) {
                // Update credits display if element exists
                const creditsElement = document.querySelector('[data-credits]');
                if (creditsElement) {
                    creditsElement.textContent = parseFloat(data.credits).toFixed(2);
                }
            }
        })
        .catch(error => console.error('Error fetching credits:', error));
});
</script>

<?php require_once 'includes/footer.php'; ?>

