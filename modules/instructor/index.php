<?php 
include '../../includes/db.php'; 
$activePage = 'instructors'; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Instructor Maintenance</title>
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
            <h2 class="fw-bold">👨‍🏫 Instructor Management</h2>
            <div>
                <a href="export_excel.php" class="btn btn-success btn-sm">Export Excel</a>
                <a href="export_pdf.php" class="btn btn-danger btn-sm" target="_blank">Export PDF</a>
                <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#addModal">+ Add
                    Instructor</button>
            </div>
        </div>

        <div class="d-flex justify-content-between mb-2">
            <div id="customFilter"></div>
        </div>

        <div class="card p-3 shadow-sm">
            <table id="instructorTable" class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Department</th>
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
                    <h5 class="modal-title">➕ Add Instructor</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3"><label class="form-label">Last Name</label><input type="text" name="last_name"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">First Name</label><input type="text" name="first_name"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Department</label><select name="dept_id"
                            class="form-select" required></select></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-success">Save</button></div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form id="editForm" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">✏️ Edit Instructor</h5><button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="instructor_id">
                    <div class="mb-3"><label class="form-label">Last Name</label><input type="text" name="last_name"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">First Name</label><input type="text" name="first_name"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email"
                            class="form-control" required></div>
                    <div class="mb-3"><label class="form-label">Department</label><select name="dept_id"
                            class="form-select" required></select></div>
                </div>
                <div class="modal-footer"><button type="submit" class="btn btn-primary">Update</button></div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="../../js/instructor.js"></script>
</body>

</html>