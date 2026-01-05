<?php
include '../includes/session.php';
checkLogin('student');
include '../includes/db.php';
$activePage = 'grades';
$student_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Grades</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="content flex-grow-1 p-4">
            <h3 class="fw-bold mb-4">My Grades</h3>
            <div class="card p-3 shadow-sm">
                <table class="table table-striped">
                    <thead class="table-dark">
                        <tr>
                            <th>Subject</th>
                            <th>Description</th>
                            <th>Units</th>
                            <th>Final Grade</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT c.course_code, c.course_title, c.units, e.letter_grade, e.status
                                FROM tblenrollment e
                                JOIN tblsection sec ON e.section_id = sec.section_id
                                JOIN tblcourse c ON sec.course_id = c.course_id
                                WHERE e.student_id = $student_id AND e.is_deleted = 0";
                        $result = $conn->query($sql);

                        while ($row = $result->fetch_assoc()) {
                            $grade = $row['letter_grade'] ? $row['letter_grade'] : 'N/A';
                            $statusClass = ($row['status'] == 'Completed' || $row['status'] == 'Passed') ? 'text-success' : 'text-primary';
                            
                            echo "<tr>
                                    <td class='fw-bold'>{$row['course_code']}</td>
                                    <td>{$row['course_title']}</td>
                                    <td>{$row['units']}</td>
                                    <td class='fw-bold'>{$grade}</td>
                                    <td class='{$statusClass}'>{$row['status']}</td>
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