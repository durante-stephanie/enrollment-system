<?php
session_start();
include 'includes/db.php';

// If already logged in, redirect
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'admin') header("Location: modules/dashboard/dashboard.php");
    else if ($_SESSION['role'] == 'faculty') header("Location: faculty/dashboard.php");
    else if ($_SESSION['role'] == 'student') header("Location: student/dashboard.php");
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $role = $_POST['role'];

    $user = null;

    if ($role == 'admin') {
        $stmt = $conn->prepare("SELECT admin_id as id, password, name FROM tbladmin WHERE username = ?");
        $stmt->bind_param("s", $username);
    } elseif ($role == 'faculty') {
        // Faculty uses Email to login
        $stmt = $conn->prepare("SELECT instructor_id as id, password, CONCAT(first_name, ' ', last_name) as name FROM tblinstructor WHERE email = ? AND is_deleted = 0");
        $stmt->bind_param("s", $username);
    } elseif ($role == 'student') {
        // Student uses Student No to login
        $stmt = $conn->prepare("SELECT student_id as id, password, CONCAT(first_name, ' ', last_name) as name FROM tblstudent WHERE student_no = ? AND is_deleted = 0");
        $stmt->bind_param("s", $username);
    }

    if (isset($stmt)) {
        $stmt->execute();
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            // Verify Password
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $role;
                $_SESSION['name'] = $row['name'];
                
                // Redirect based on role
                if ($role == 'admin') header("Location: modules/dashboard/dashboard.php");
                elseif ($role == 'faculty') header("Location: faculty/dashboard.php");
                elseif ($role == 'student') header("Location: student/dashboard.php");
                exit;
            } else {
                $error = "Invalid password.";
            }
        } else {
            $error = "User not found.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - SIS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/style.css">
    <style>
        body {
            background-color: var(--navy);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 400px;
            background: white;
            padding: 40px;
            border-radius: var(--radius-lg);
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <h3 class="text-center fw-bold mb-4" style="color: var(--navy);">SIS Login</h3>
        
        <?php if($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Role</label>
                <select name="role" class="form-select" id="roleSelect">
                    <option value="student">Student</option>
                    <option value="faculty">Faculty</option>
                    <option value="admin">Admin</option>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label" id="userLabel">Student No.</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Login</button>
        </form>
    </div>

    <script>
        const roleSelect = document.getElementById('roleSelect');
        const userLabel = document.getElementById('userLabel');

        roleSelect.addEventListener('change', function() {
            if(this.value === 'student') userLabel.innerText = 'Student No.';
            else if(this.value === 'faculty') userLabel.innerText = 'Email';
            else userLabel.innerText = 'Username';
        });
    </script>
</body>
</html>