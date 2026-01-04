<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$pageTitle = 'Call Queue';
require_once 'includes/header.php';

$conn = getDBConnection();

// Get queue entries
$result = $conn->query("SELECT * FROM call_queue ORDER BY position ASC, created_at ASC");
$queueEntries = [];
while ($row = $result->fetch_assoc()) {
    $queueEntries[] = $row;
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">
            <i class="bi bi-people me-2"></i>
            Call Queue
        </h2>
        <p class="text-muted">Manage incoming call queue</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Queue Management</h5>
        <button class="btn btn-sm btn-primary" onclick="refreshQueue()">
            <i class="bi bi-arrow-clockwise me-1"></i> Refresh
        </button>
    </div>
    <div class="card-body">
        <?php if (empty($queueEntries)): ?>
            <p class="text-muted text-center py-4">No entries in queue</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Position</th>
                            <th>Caller Name</th>
                            <th>Phone Number</th>
                            <th>Language</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Wait Time</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($queueEntries as $entry): ?>
                            <tr>
                                <td>
                                    <span class="badge bg-primary">#<?php echo $entry['position']; ?></span>
                                </td>
                                <td><?php echo htmlspecialchars($entry['caller_name'] ?? 'N/A'); ?></td>
                                <td><?php echo htmlspecialchars($entry['phone_number']); ?></td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo ucfirst($entry['language'] ?? 'english'); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $priority = $entry['priority'] ?? 'normal';
                                    $priorityClass = $priority === 'high' ? 'bg-danger' : ($priority === 'low' ? 'bg-info' : 'bg-warning');
                                    ?>
                                    <span class="badge <?php echo $priorityClass; ?>">
                                        <?php echo ucfirst($priority); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php
                                    $status = $entry['status'] ?? 'waiting';
                                    $statusClass = $status === 'in_call' ? 'bg-success' : ($status === 'completed' ? 'bg-primary' : ($status === 'abandoned' ? 'bg-danger' : 'bg-secondary'));
                                    ?>
                                    <span class="badge <?php echo $statusClass; ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $status)); ?>
                                    </span>
                                </td>
                                <td><?php echo $entry['estimated_wait_time'] ?? 0; ?> min</td>
                                <td><?php echo date('M d, H:i', strtotime($entry['created_at'])); ?></td>
                                <td>
                                    <?php if ($entry['status'] === 'waiting'): ?>
                                        <button class="btn btn-sm btn-success" onclick="startCall('<?php echo $entry['id']; ?>')">
                                            <i class="bi bi-telephone"></i>
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm btn-danger" onclick="removeFromQueue('<?php echo $entry['id']; ?>')">
                                        <i class="bi bi-x-circle"></i>
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

<script>
function refreshQueue() {
    location.reload();
}

function startCall(queueId) {
    if (confirm('Start call for this queue entry?')) {
        fetch(`api/queue.php?id=${queueId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                status: 'in_call'
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Call started', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Failed to start call', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error starting call', 'danger');
        });
    }
}

function removeFromQueue(queueId) {
    if (confirm('Remove this entry from queue?')) {
        fetch(`api/queue.php?id=${queueId}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Removed from queue', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Failed to remove', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error removing entry', 'danger');
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>

