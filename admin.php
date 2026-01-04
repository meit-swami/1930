<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$pageTitle = 'Admin Analytics';
require_once 'includes/header.php';

$conn = getDBConnection();

// Get analytics data
$stats = [];

// Total complaints
$result = $conn->query("SELECT COUNT(*) as count FROM complaints");
$stats['totalComplaints'] = $result->fetch_assoc()['count'] ?? 0;

// Complaints by status
$result = $conn->query("SELECT status, COUNT(*) as count FROM complaints GROUP BY status");
$complaintsByStatus = [];
while ($row = $result->fetch_assoc()) {
    $complaintsByStatus[$row['status']] = $row['count'];
}

// Complaints by fraud type
$result = $conn->query("SELECT fraud_type, COUNT(*) as count FROM complaints GROUP BY fraud_type");
$complaintsByType = [];
while ($row = $result->fetch_assoc()) {
    $complaintsByType[$row['fraud_type']] = $row['count'];
}

// Total amount stolen
$result = $conn->query("SELECT SUM(amount_stolen) as total FROM complaints WHERE amount_stolen IS NOT NULL");
$stats['totalAmount'] = $result->fetch_assoc()['total'] ?? 0;

// Complaints this month
$monthStart = date('Y-m-01');
$result = $conn->query("SELECT COUNT(*) as count FROM complaints WHERE created_at >= '$monthStart'");
$stats['complaintsThisMonth'] = $result->fetch_assoc()['count'] ?? 0;

// Total sessions
$result = $conn->query("SELECT COUNT(*) as count FROM sessions");
$stats['totalSessions'] = $result->fetch_assoc()['count'] ?? 0;
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">
            <i class="bi bi-graph-up me-2"></i>
            Admin Analytics
        </h2>
        <p class="text-muted">Comprehensive analytics and insights</p>
    </div>
</div>

<!-- Key Metrics -->
<div class="row g-4 mb-4">
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Complaints</h6>
                <h2 class="mb-0"><?php echo $stats['totalComplaints']; ?></h2>
                <small class="text-muted">All time</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-2">This Month</h6>
                <h2 class="mb-0"><?php echo $stats['complaintsThisMonth']; ?></h2>
                <small class="text-muted">New complaints</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Amount</h6>
                <h2 class="mb-0">₹<?php echo number_format($stats['totalAmount']); ?></h2>
                <small class="text-muted">Reported stolen</small>
            </div>
        </div>
    </div>
    
    <div class="col-md-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-muted mb-2">Total Sessions</h6>
                <h2 class="mb-0"><?php echo $stats['totalSessions']; ?></h2>
                <small class="text-muted">All sessions</small>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Complaints by Status -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">Complaints by Status</h5>
            </div>
            <div class="card-body">
                <canvas id="statusChart" height="200"></canvas>
            </div>
        </div>
    </div>
    
    <!-- Complaints by Fraud Type -->
    <div class="col-md-6 mb-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">Complaints by Fraud Type</h5>
            </div>
            <div class="card-body">
                <canvas id="typeChart" height="200"></canvas>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Status Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: <?php echo json_encode(array_keys($complaintsByStatus)); ?>,
        datasets: [{
            data: <?php echo json_encode(array_values($complaintsByStatus)); ?>,
            backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Type Chart
const typeCtx = document.getElementById('typeChart').getContext('2d');
new Chart(typeCtx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode(array_keys($complaintsByType)); ?>,
        datasets: [{
            label: 'Complaints',
            data: <?php echo json_encode(array_values($complaintsByType)); ?>,
            backgroundColor: '#3b82f6'
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php require_once 'includes/footer.php'; ?>

