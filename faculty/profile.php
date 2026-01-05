<?php
include '../includes/session.php';
checkLogin('faculty');
include '../includes/db.php';

$instructor_id = $_SESSION['user_id'];
$msg = '';

// Handle Password Change
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['change_password'])) {
    $current_pass = $_POST['current_password'];
    $new_pass = $_POST['new_password'];
    $confirm_pass = $_POST['confirm_password'];

    // Fetch current password
    $stmt = $conn->prepare("SELECT password FROM tblinstructor WHERE instructor_id = ?");
    $stmt->bind_param("i", $instructor_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    if (password_verify($current_pass, $row['password'])) {
        if ($new_pass === $confirm_pass) {
            if (strlen($new_pass) >= 6) {
                $new_hash = password_hash($new_pass, PASSWORD_DEFAULT);
                $update = $conn->prepare("UPDATE tblinstructor SET password = ? WHERE instructor_id = ?");
                $update->bind_param("si", $new_hash, $instructor_id);
                
                if ($update->execute()) {
                    $msg = '<div class="alert alert-success">Password updated successfully!</div>';
                } else {
                    $msg = '<div class="alert alert-danger">Database error.</div>';
                }
            } else {
                $msg = '<div class="alert alert-warning">Password must be at least 6 characters long.</div>';
            }
        } else {
            $msg = '<div class="alert alert-danger">New passwords do not match.</div>';
        }
    } else {
        $msg = '<div class="alert alert-danger">Incorrect current password.</div>';
    }
}

// Fetch Faculty Data
$stmt = $conn->prepare("SELECT i.*, d.dept_name 
                        FROM tblinstructor i 
                        LEFT JOIN tbldepartment d ON i.dept_id = d.dept_id 
                        WHERE i.instructor_id = ?");
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$info = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php $activePage = 'profile'; include 'sidebar.php'; ?>
        
        <div class="content flex-grow-1 p-4">
            <h3 class="fw-bold mb-4">My Profile & Security</h3>
            <?php echo $msg; ?>

            <div class="row">
                <div class="col-md-7">
                    <div class="card p-4 shadow-sm border-0 mb-4">
                        <h5 class="fw-bold text-navy border-bottom pb-2 mb-3">Instructor Information</h5>
                        <form>
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Full Name</label>
                                    <input type="text" class="form-control" value="<?php echo $info['first_name'] . ' ' . $info['last_name']; ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-muted">Department</label>
                                    <input type="text" class="form-control" value="<?php echo $info['dept_name']; ?>" readonly>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label text-muted">Email Address</label>
                                <input type="text" class="form-control" value="<?php echo $info['email']; ?>" readonly>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card p-4 shadow-sm border-0">
                        <h5 class="fw-bold text-navy border-bottom pb-2 mb-3">Security Settings</h5>
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Current Password</label>
                                <input type="password" name="current_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">New Password</label>
                                <input type="password" name="new_password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Confirm New Password</label>
                                <input type="password" name="confirm_password" class="form-control" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary w-100">
                                <i class="fas fa-lock me-2"></i> Update Password
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>