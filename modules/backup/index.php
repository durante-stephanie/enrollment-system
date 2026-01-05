<?php 
include '../../includes/db.php'; 
$activePage = 'backup'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Backup & Restore</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="content">
        <h2 class="fw-bold mb-4">💾 Backup & Restore</h2>

        <div class="row">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-download me-2"></i>Backup Database</h5>
                    </div>
                    <div class="card-body text-center p-5">
                        <p class="text-muted">
                            Download a full SQL backup of the current system database.<br>
                            This file can be used to restore the system later.
                        </p>
                        <button id="btnBackup" class="btn btn-primary btn-lg mt-3">
                            <i class="fas fa-file-download"></i> Generate Backup
                        </button>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header bg-danger text-white">
                        <h5 class="mb-0"><i class="fas fa-upload me-2"></i>Restore Database</h5>
                    </div>
                    <div class="card-body p-5">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>Warning:</strong> Restoring will <u>overwrite</u> existing data. 
                            Use this on a new installation or if you are sure.
                        </div>
                        
                        <form id="restoreForm" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Select SQL File</label>
                                <input type="file" name="sql_file" class="form-control" accept=".sql" required>
                            </div>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-danger btn-lg" id="btnRestore">
                                    <i class="fas fa-trash-restore"></i> Restore Database
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../js/backup.js"></script>
</body>
</html>