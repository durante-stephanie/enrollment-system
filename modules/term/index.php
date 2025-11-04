<?php 
include '../../includes/db.php'; 
$activePage = 'terms'; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Term Maintenance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="../../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="content">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold">🗓️ Term Management</h2>
            <div>
                <a href="export_excel.php" class="btn btn-success btn-sm">Export Excel</a>
                <a href="export_pdf.php" class="btn btn-danger btn-sm" target="_blank">Export PDF</a>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">+ Add
                    Term</button>
            </div>
        </div>

        <div class="card p-3 shadow-sm">
            <table id="termTable" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="addForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">➕ Add Term</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Term Code</label><input type="text" name="term_code"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Start Date</label><input type="date" name="start_date"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">End Date</label><input type="date" name="end_date"
                            class="form-control" required></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Edit Term</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="term_id">
                    <div class="mb-3"><label class="form-label">Term Code</label><input type="text" name="term_code"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Start Date</label><input type="date" name="start_date"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">End Date</label><input type="date" name="end_date"
                            class="form-control" required></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../js/term.js"></script>
</body>

</html>