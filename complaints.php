<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$pageTitle = 'Complaints';
require_once 'includes/header.php';

$conn = getDBConnection();

// Handle search and filter
$search = $_GET['search'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$fraudTypeFilter = $_GET['fraud_type'] ?? '';

// Build query
$where = [];
$params = [];
$types = '';

if ($search) {
    $where[] = "(victim_name LIKE ? OR phone_number LIKE ? OR description LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= 'sss';
}

if ($statusFilter) {
    $where[] = "status = ?";
    $params[] = $statusFilter;
    $types .= 's';
}

if ($fraudTypeFilter) {
    $where[] = "fraud_type = ?";
    $params[] = $fraudTypeFilter;
    $types .= 's';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Get complaints
$sql = "SELECT * FROM complaints $whereClause ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
$complaints = [];
while ($row = $result->fetch_assoc()) {
    $complaints[] = $row;
}
$stmt->close();

// Fraud type labels
$fraudTypeLabels = [
    'otp_theft' => 'OTP Theft',
    'wrong_transfer' => 'Wrong Money Transfer',
    'upi_scam' => 'UPI Scam',
    'bank_fraud' => 'Bank Account Fraud',
    'phishing' => 'Phishing Attack',
    'other' => 'Other'
];
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">
            <i class="bi bi-file-text me-2"></i>
            Complaints
        </h2>
        <p class="text-muted">Manage and view fraud complaints</p>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="" class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search" placeholder="Search by name, phone, or description" value="<?php echo htmlspecialchars($search); ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo $statusFilter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    <option value="in_progress" <?php echo $statusFilter === 'in_progress' ? 'selected' : ''; ?>>In Progress</option>
                    <option value="resolved" <?php echo $statusFilter === 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Fraud Type</label>
                <select class="form-select" name="fraud_type">
                    <option value="">All Types</option>
                    <?php foreach ($fraudTypeLabels as $key => $label): ?>
                        <option value="<?php echo $key; ?>" <?php echo $fraudTypeFilter === $key ? 'selected' : ''; ?>>
                            <?php echo $label; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Complaints Table -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Complaints List</h5>
        <a href="export.php" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-download me-1"></i> Export
        </a>
    </div>
    <div class="card-body">
        <?php if (empty($complaints)): ?>
            <p class="text-muted text-center py-4">No complaints found</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Victim Name</th>
                            <th>Phone</th>
                            <th>Fraud Type</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($complaints as $complaint): ?>
                            <tr>
                                <td><code><?php echo substr($complaint['id'], 0, 8); ?>...</code></td>
                                <td><?php echo htmlspecialchars($complaint['victim_name']); ?></td>
                                <td><?php echo htmlspecialchars($complaint['phone_number']); ?></td>
                                <td>
                                    <span class="badge bg-info">
                                        <?php echo $fraudTypeLabels[$complaint['fraud_type']] ?? $complaint['fraud_type']; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($complaint['amount_stolen']): ?>
                                        ₹<?php echo number_format($complaint['amount_stolen']); ?>
                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php
                                    $status = $complaint['status'] ?? 'pending';
                                    $badgeClass = $status === 'resolved' ? 'bg-success' : ($status === 'in_progress' ? 'bg-warning' : 'bg-secondary');
                                    ?>
                                    <span class="badge <?php echo $badgeClass; ?>">
                                        <?php echo ucfirst($status); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($complaint['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-outline-primary" onclick="viewComplaint('<?php echo $complaint['id']; ?>')">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Complaint Detail Modal -->
<div class="modal fade" id="complaintModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complaint Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="complaintDetails">
                <!-- Details will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewComplaint(id) {
    fetch(`api/complaint.php?id=${id}`)
        .then(response => response.json())
        .then(data => {
            const modal = document.getElementById('complaintModal');
            const details = document.getElementById('complaintDetails');
            
            details.innerHTML = `
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Victim Name:</strong> ${data.victim_name || 'N/A'}
                    </div>
                    <div class="col-md-6">
                        <strong>Phone Number:</strong> ${data.phone_number || 'N/A'}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Email:</strong> ${data.email || 'N/A'}
                    </div>
                    <div class="col-md-6">
                        <strong>Location:</strong> ${data.location || 'N/A'}
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <strong>Fraud Type:</strong> ${data.fraud_type || 'N/A'}
                    </div>
                    <div class="col-md-6">
                        <strong>Amount Stolen:</strong> ₹${data.amount_stolen ? data.amount_stolen.toLocaleString() : 'N/A'}
                    </div>
                </div>
                <div class="mb-3">
                    <strong>Description:</strong>
                    <p class="mt-2">${data.description || 'No description provided'}</p>
                </div>
            `;
            
            new bootstrap.Modal(modal).show();
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load complaint details');
        });
}
</script>

<?php require_once 'includes/footer.php'; ?>

