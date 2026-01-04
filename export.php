<?php
require_once 'config/database.php';
require_once 'config/session.php';
requireLogin();

$pageTitle = 'Export Data';
require_once 'includes/header.php';

$conn = getDBConnection();

// Handle export
if (isset($_GET['export']) && $_GET['export'] === 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="complaints_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // CSV headers
    fputcsv($output, ['ID', 'Victim Name', 'Phone', 'Email', 'Fraud Type', 'Amount', 'Status', 'Created At']);
    
    // Get all complaints
    $result = $conn->query("SELECT * FROM complaints ORDER BY created_at DESC");
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $row['id'],
            $row['victim_name'],
            $row['phone_number'],
            $row['email'] ?? '',
            $row['fraud_type'],
            $row['amount_stolen'] ?? 0,
            $row['status'],
            $row['created_at']
        ]);
    }
    
    fclose($output);
    exit();
}
?>

<div class="row mb-4">
    <div class="col-12">
        <h2 class="mb-0">
            <i class="bi bi-download me-2"></i>
            Export Data
        </h2>
        <p class="text-muted">Export complaints data in various formats</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
                <h5 class="mb-0">Export Options</h5>
            </div>
            <div class="card-body">
                <div class="mb-4">
                    <h6>Export Format</h6>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="format" id="formatCsv" value="csv" checked>
                        <label class="form-check-label" for="formatCsv">
                            CSV (Comma Separated Values)
                        </label>
                    </div>
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="radio" name="format" id="formatExcel" value="excel" disabled>
                        <label class="form-check-label" for="formatExcel">
                            Excel (Coming Soon)
                        </label>
                    </div>
                </div>
                
                <div class="mb-4">
                    <h6>Select Fields</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fieldId" checked>
                                <label class="form-check-label" for="fieldId">ID</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fieldName" checked>
                                <label class="form-check-label" for="fieldName">Victim Name</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fieldPhone" checked>
                                <label class="form-check-label" for="fieldPhone">Phone Number</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fieldEmail" checked>
                                <label class="form-check-label" for="fieldEmail">Email</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fieldType" checked>
                                <label class="form-check-label" for="fieldType">Fraud Type</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fieldAmount" checked>
                                <label class="form-check-label" for="fieldAmount">Amount</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fieldStatus" checked>
                                <label class="form-check-label" for="fieldStatus">Status</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="fieldDate" checked>
                                <label class="form-check-label" for="fieldDate">Created Date</label>
                            </div>
                        </div>
                    </div>
                </div>
                
                <a href="?export=csv" class="btn btn-primary">
                    <i class="bi bi-download me-2"></i> Export CSV
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

