<?php 
include '../../includes/db.php'; 
$activePage = 'prerequisites'; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Prerequisite Maintenance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
    <!-- Select2 CSS + Bootstrap 5 Theme -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" />
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" />
    <link rel="stylesheet" href="../../css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>

<body>
    <?php include '../../includes/sidebar.php'; ?>

    <div class="content">

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2 class="fw-bold">🔗 Prerequisite Management</h2>
            <div>
                <a href="export_excel.php" class="btn btn-success btn-sm">Export Excel</a>
                <a href="export_pdf.php" class="btn btn-danger btn-sm" target="_blank">Export PDF</a>
            </div>
        </div>

        <div class="card p-3 shadow-sm mb-4">
            <h5 class="mb-3">Add New Prerequisite</h5>
            <form id="addForm" class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label">Course</label>
                    <select name="course_id" class="form-select" required></select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Prerequisite Course</label>
                    <select name="prereq_course_id" class="form-select" required></select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">Add Link</button>
                </div>
            </form>
        </div>

        <div class="card p-3 shadow-sm">
            <table id="prereqTable" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Course</th>
                        <th>Prerequisite</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Edit Prerequisite</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="old_course_id">
                    <input type="hidden" name="old_prereq_course_id">
                    <div class="mb-3">
                        <label class="form-label">Course</label>
                        <select name="course_id" class="form-select" required></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Prerequisite Course</label>
                        <select name="prereq_course_id" class="form-select" required></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../../js/prerequisite.js"></script>
</body>

</html>