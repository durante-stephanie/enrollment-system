<?php
include '../includes/session.php';
checkLogin('faculty');
include '../includes/db.php';

$instructor_id = $_SESSION['user_id'];
$sql = "SELECT s.*, c.course_code, c.course_title, r.room_code 
        FROM tblsection s 
        JOIN tblcourse c ON s.course_id = c.course_id 
        LEFT JOIN tblroom r ON s.room_id = r.room_id 
        WHERE s.instructor_id = $instructor_id AND s.is_deleted = 0 
        ORDER BY s.day_pattern, s.start_time";
$result = $conn->query($sql);
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
        <?php $activePage = 'schedule'; include 'sidebar.php'; ?>
        <div class="content flex-grow-1 p-4">
            <h3 class="fw-bold mb-4">My Teaching Schedule</h3>
            <div class="card p-4 shadow-sm border-0">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>Section</th>
                            <th>Subject</th>
                            <th>Days</th>
                            <th>Time</th>
                            <th>Room</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): 
                             $time = date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time']));
                        ?>
                        <tr>
                            <td class="fw-bold"><?php echo $row['section_code']; ?></td>
                            <td><?php echo $row['course_code']; ?> <br> <small class="text-muted"><?php echo $row['course_title']; ?></small></td>
                            <td><?php echo $row['day_pattern']; ?></td>
                            <td><?php echo $time; ?></td>
                            <td><?php echo $row['room_code']; ?></td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>