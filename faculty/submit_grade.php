<?php
include '../includes/session.php';
checkLogin('faculty');
include '../includes/db.php';

if (!isset($_GET['section_id'])) {
    header("Location: grading.php");
    exit;
}

$section_id = $_GET['section_id'];
$instructor_id = $_SESSION['user_id'];
$msg = '';

// Verify this section belongs to this instructor (Security Check)
$chk = $conn->query("SELECT * FROM tblsection WHERE section_id = $section_id AND instructor_id = $instructor_id");
if ($chk->num_rows == 0) {
    die("Unauthorized access to this section.");
}

// Handle Grade Submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    foreach ($_POST['grades'] as $enrollment_id => $grade) {
        // Determine Status based on Grade
        // Example: If grade <= 3.0 it is passed (1.0 is highest), if > 3.0 or 5.0 is failed.
        // Adjust this logic based on your school's grading system.
        // Assuming 1.0-3.0 = Passed, 5.0 = Failed, INC = Incomplete
        
        $status = 'Enrolled'; // Default
        if ($grade !== '') {
            if (is_numeric($grade)) {
                if ($grade <= 3.0) $status = 'Passed';
                else $status = 'Failed';
            } else {
                if(strtoupper($grade) == 'INC') $status = 'Incomplete';
                else $status = 'Enrolled'; 
            }
        }
        
        $stmt = $conn->prepare("UPDATE tblenrollment SET letter_grade = ?, status = ? WHERE enrollment_id = ?");
        $stmt->bind_param("ssi", $grade, $status, $enrollment_id);
        $stmt->execute();
    }
    $msg = '<div class="alert alert-success">Grades saved successfully!</div>';
}

// Fetch Students
$sql = "SELECT e.enrollment_id, e.letter_grade, s.student_no, s.last_name, s.first_name, p.program_code
        FROM tblenrollment e
        JOIN tblstudent s ON e.student_id = s.student_id
        JOIN tblprogram p ON s.program_id = p.program_id
        WHERE e.section_id = $section_id AND e.is_deleted = 0
        ORDER BY s.last_name ASC";
$students = $conn->query($sql);

// Get Section Info for Header
$sec_info = $chk->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Input Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php $activePage = 'grading'; include 'sidebar.php'; ?>
        <div class="content flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <a href="grading.php" class="text-muted text-decoration-none"><i class="fas fa-arrow-left"></i> Back</a>
                    <h3 class="fw-bold mt-2">Grading Sheet: <?php echo $sec_info['section_code']; ?></h3>
                </div>
                <button type="submit" form="gradeForm" class="btn btn-success">
                    <i class="fas fa-save"></i> Save Grades
                </button>
            </div>

            <?php echo $msg; ?>

            <div class="card p-4 shadow-sm border-0">
                <form method="POST" id="gradeForm">
                    <table class="table table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Student No</th>
                                <th>Name</th>
                                <th>Program</th>
                                <th width="150">Final Grade</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if($students->num_rows > 0): ?>
                                <?php while($row = $students->fetch_assoc()): ?>
                                <tr>
                                    <td><?php echo $row['student_no']; ?></td>
                                    <td class="fw-bold"><?php echo $row['last_name'] . ', ' . $row['first_name']; ?></td>
                                    <td><?php echo $row['program_code']; ?></td>
                                    <td>
                                        <input type="text" 
                                               name="grades[<?php echo $row['enrollment_id']; ?>]" 
                                               class="form-control text-center fw-bold" 
                                               value="<?php echo $row['letter_grade']; ?>"
                                               placeholder="0.00">
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="4" class="text-center">No students enrolled.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </form>
            </div>
        </div>
    </div>
</body>
</html>