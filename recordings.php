<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$pageTitle = 'Recordings';
require_once 'includes/header.php';

$conn = getDBConnection();

// Get recordings
$result = $conn->query("SELECT * FROM recordings ORDER BY created_at DESC");
$recordings = [];
while ($row = $result->fetch_assoc()) {
    $recordings[] = $row;
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">
            <i class="bi bi-mic me-2"></i>
            Recordings
        </h2>
        <p class="text-muted">View and manage call recordings</p>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0">Recordings Library</h5>
    </div>
    <div class="card-body">
        <?php if (empty($recordings)): ?>
            <p class="text-muted text-center py-4">No recordings found</p>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Session ID</th>
                            <th>Filename</th>
                            <th>Duration</th>
                            <th>File Size</th>
                            <th>Language</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recordings as $recording): ?>
                            <tr>
                                <td><code><?php echo substr($recording['session_id'], 0, 8); ?>...</code></td>
                                <td><?php echo htmlspecialchars($recording['filename']); ?></td>
                                <td><?php echo round(($recording['duration'] ?? 0) / 60, 1); ?> min</td>
                                <td><?php echo round(($recording['file_size'] ?? 0) / 1024, 2); ?> KB</td>
                                <td>
                                    <span class="badge bg-secondary">
                                        <?php echo ucfirst($recording['language'] ?? 'english'); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M d, Y H:i', strtotime($recording['created_at'])); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-primary" onclick="playRecording('<?php echo $recording['id']; ?>')">
                                        <i class="bi bi-play-circle"></i>
                                    </button>
                                    <button class="btn btn-sm btn-danger" onclick="deleteRecording('<?php echo $recording['id']; ?>')">
                                        <i class="bi bi-trash"></i>
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

<!-- Audio Player Modal -->
<div class="modal fade" id="audioModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Recording Playback</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <audio id="audioPlayer" controls class="w-100">
                    Your browser does not support the audio element.
                </audio>
            </div>
        </div>
    </div>
</div>

<script>
function playRecording(id) {
    const audioPlayer = document.getElementById('audioPlayer');
    audioPlayer.src = `api/recording.php?id=${id}`;
    const modal = new bootstrap.Modal(document.getElementById('audioModal'));
    modal.show();
}

function deleteRecording(id) {
    if (confirm('Are you sure you want to delete this recording?')) {
        fetch(`api/recording.php?id=${id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast('Recording deleted', 'success');
                setTimeout(() => location.reload(), 1000);
            } else {
                showToast('Failed to delete recording', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showToast('Error deleting recording', 'danger');
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>

