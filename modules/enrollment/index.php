<?php 
include '../../includes/db.php'; 
$activePage = 'enrollments'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrollment Maintenance</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
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
            <h2 class="fw-bold">Enrollment Management</h2>
            <div>
                <a href="export_excel.php" class="btn btn-success btn-sm">Export Excel</a>
                <a href="export_pdf.php" class="btn btn-danger btn-sm" target="_blank">Export PDF</a>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">+ Add Enrollment</button>
            </div>
        </div>

        <div class="card p-3 shadow-sm">
            <table id="enrollmentTable" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Student</th>
                        <th>Section / Subject</th>
                        <th>Status</th>
                        <th>Grade</th>
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
                    <h5 class="modal-title">➕ Add Enrollment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Enrollment Type</label>
                        <select id="enrollmentType" class="form-select">
                            <option value="regular">Regular (By Block Section)</option>
                            <option value="irregular">Irregular (By Subject)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-select" required></select>
                    </div>
                    
                    <div id="irregularFields" style="display: none;">
                        <div class="mb-3 p-2 bg-light border rounded">
                            <label class="form-label fw-bold text-primary">Filter by Subject</label>
                            <select id="courseSelect" class="form-select mb-2">
                                <option value="">-- Select Subject to Filter Sections --</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" id="sectionLabel">Block Section</label>
                        <select name="section_id" class="form-select" required>
                            <option value="">Select Section</option>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="Enrolled">Enrolled</option>
                                    <option value="Dropped">Dropped</option>
                                    <option value="Completed">Completed</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label">Final Grade</label>
                                <input type="text" name="final_grade" class="form-control" placeholder="e.g., 1.75">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Edit Enrollment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="enrollment_id">
                    <div class="mb-3">
                        <label class="form-label">Student</label>
                        <select name="student_id" class="form-select" disabled></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Section</label>
                        <select name="section_id" class="form-select" disabled></select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Enrolled">Enrolled</option>
                            <option value="Dropped">Dropped</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Final Grade</label>
                        <input type="text" name="final_grade" class="form-control" placeholder="e.g., 1.75">
                    </div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../../js/enrollment.js"></script>
</body>
</html>