<?php
include '../includes/session.php';
checkLogin('student');
include '../includes/db.php';
$activePage = 'schedule';
$student_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Schedule</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php include 'sidebar.php'; ?>
        <div class="content flex-grow-1 p-4">
            <h3 class="fw-bold mb-4">My Class Schedule</h3>
            <div class="card p-3 shadow-sm">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Course Code</th>
                            <th>Description</th>
                            <th>Section</th>
                            <th>Days</th>
                            <th>Time</th>
                            <th>Room</th>
                            <th>Instructor</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT c.course_code, c.course_title, sec.section_code, sec.day_pattern, sec.start_time, sec.end_time, 
                                       r.room_code, CONCAT(i.last_name, ', ', i.first_name) as instructor
                                FROM tblenrollment e
                                JOIN tblsection sec ON e.section_id = sec.section_id
                                JOIN tblcourse c ON sec.course_id = c.course_id
                                LEFT JOIN tblroom r ON sec.room_id = r.room_id
                                LEFT JOIN tblinstructor i ON sec.instructor_id = i.instructor_id
                                WHERE e.student_id = $student_id AND e.is_deleted = 0";
                        $result = $conn->query($sql);
                        
                        if($result->num_rows > 0){
                            while ($row = $result->fetch_assoc()) {
                                $time = date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time']));
                                echo "<tr>
                                        <td class='fw-bold'>{$row['course_code']}</td>
                                        <td>{$row['course_title']}</td>
                                        <td>{$row['section_code']}</td>
                                        <td>{$row['day_pattern']}</td>
                                        <td>{$time}</td>
                                        <td>{$row['room_code']}</td>
                                        <td>{$row['instructor']}</td>
                                      </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='7' class='text-center text-muted'>You are not enrolled in any classes yet.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>