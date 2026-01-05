<?php
include '../includes/session.php';
checkLogin('student');
include '../includes/db.php';

$activePage = 'enrollment';
$student_id = $_SESSION['user_id'];
$message = '';

// Handle Enrollment Action
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enroll_btn'])) {
    $section_id = $_POST['section_id'];

    // 1. Check if already enrolled in this specific section
    $check = $conn->query("SELECT * FROM tblenrollment WHERE student_id = $student_id AND section_id = $section_id AND is_deleted = 0");
    
    // 2. Check if already enrolled in the SAME SUBJECT (different section)
    // Get course_id of the section trying to enroll
    $course_qry = $conn->query("SELECT course_id FROM tblsection WHERE section_id = $section_id");
    $target_course_id = $course_qry->fetch_assoc()['course_id'];

    $subject_check = $conn->query("SELECT e.enrollment_id FROM tblenrollment e 
                                   JOIN tblsection s ON e.section_id = s.section_id 
                                   WHERE e.student_id = $student_id AND s.course_id = $target_course_id AND e.is_deleted = 0");

    if ($check->num_rows > 0) {
        $message = '<div class="alert alert-warning">You are already enrolled in this section.</div>';
    } elseif ($subject_check->num_rows > 0) {
        $message = '<div class="alert alert-danger">You are already enrolled in this subject (different section).</div>';
    } else {
        $sql = "INSERT INTO tblenrollment (student_id, section_id, status, date_enrolled) VALUES ($student_id, $section_id, 'Enrolled', NOW())";
        if ($conn->query($sql)) {
            $message = '<div class="alert alert-success">Successfully enrolled!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error enrolling.</div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Enrollment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="content flex-grow-1 p-4">
            <h3 class="fw-bold mb-4">Available Sections</h3>
            <?php echo $message; ?>

            <div class="card p-3 shadow-sm">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Section Code</th>
                            <th>Subject</th>
                            <th>Schedule</th>
                            <th>Instructor</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch available sections (You might want to filter this by Term later)
                        $sql = "SELECT s.section_id, s.section_code, s.day_pattern, s.start_time, s.end_time, 
                                       c.course_code, c.course_title, 
                                       CONCAT(i.first_name, ' ', i.last_name) as instructor
                                FROM tblsection s
                                JOIN tblcourse c ON s.course_id = c.course_id
                                LEFT JOIN tblinstructor i ON s.instructor_id = i.instructor_id
                                WHERE s.is_deleted = 0";
                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()) {
                            $time = date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time']));
                            echo "<tr>
                                    <td>{$row['section_code']}</td>
                                    <td>
                                        <div class='fw-bold'>{$row['course_code']}</div>
                                        <small class='text-muted'>{$row['course_title']}</small>
                                    </td>
                                    <td>{$row['day_pattern']} <br> <small>{$time}</small></td>
                                    <td>{$row['instructor']}</td>
                                    <td>
                                        <form method='POST'>
                                            <input type='hidden' name='section_id' value='{$row['section_id']}'>
                                            <button type='submit' name='enroll_btn' class='btn btn-primary btn-sm'>Enroll</button>
                                        </form>
                                    </td>
                                  </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>