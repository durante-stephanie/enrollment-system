<?php
include '../includes/session.php';
checkLogin('student');
include '../includes/db.php';
$activePage = 'cor';
$student_id = $_SESSION['user_id'];

// Get Student Details
$student_sql = "SELECT s.*, p.program_name FROM tblstudent s 
                JOIN tblprogram p ON s.program_id = p.program_id 
                WHERE s.student_id = $student_id";
$s_res = $conn->query($student_sql);
$student_info = $s_res->fetch_assoc();

// Get Enrolled Subjects
$enroll_sql = "SELECT c.course_code, c.course_title, c.units, sec.section_code, sec.day_pattern, sec.start_time, sec.end_time
               FROM tblenrollment e
               JOIN tblsection sec ON e.section_id = sec.section_id
               JOIN tblcourse c ON sec.course_id = c.course_id
               WHERE e.student_id = $student_id AND e.is_deleted = 0";
$subjects = $conn->query($enroll_sql);
$total_units = 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Certificate of Registration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="content flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="fw-bold">Certificate of Registration (COR)</h3>
                <a href="generate_cor.php" target="_blank" class="btn btn-danger">
                    <i class="fas fa-file-pdf"></i> Download PDF
                </a>
            </div>

            <div class="card p-4 shadow-sm border-0">
                <div class="text-center mb-4">
                    <h4>Polytechnic University of the Philippines</h4>
                    <p class="text-muted">Taguig Campus</p>
                    <h5 class="fw-bold mt-3">REGISTRATION FORM</h5>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <p><strong>Name:</strong> <?php echo $student_info['last_name'] . ', ' . $student_info['first_name']; ?></p>
                        <p><strong>Student No:</strong> <?php echo $student_info['student_no']; ?></p>
                    </div>
                    <div class="col-md-6 text-end">
                        <p><strong>Program:</strong> <?php echo $student_info['program_name']; ?></p>
                        <p><strong>Year Level:</strong> <?php echo $student_info['year_level']; ?></p>
                        <p><strong>Date:</strong> <?php echo date('F d, Y'); ?></p>
                    </div>
                </div>

                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Subject Title</th>
                            <th>Section</th>
                            <th>Units</th>
                            <th>Schedule</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while($sub = $subjects->fetch_assoc()): 
                            $total_units += $sub['units'];
                            $time = date('h:i A', strtotime($sub['start_time'])) . '-' . date('h:i A', strtotime($sub['end_time']));
                        ?>
                        <tr>
                            <td><?php echo $sub['course_code']; ?></td>
                            <td><?php echo $sub['course_title']; ?></td>
                            <td><?php echo $sub['section_code']; ?></td>
                            <td><?php echo $sub['units']; ?></td>
                            <td><?php echo $sub['day_pattern'] . ' ' . $time; ?></td>
                        </tr>
                        <?php endwhile; ?>
                        <tr class="table-secondary fw-bold">
                            <td colspan="3" class="text-end">Total Units:</td>
                            <td colspan="2"><?php echo $total_units; ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>