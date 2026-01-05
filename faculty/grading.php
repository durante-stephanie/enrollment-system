<?php
include '../includes/session.php';
checkLogin('faculty');
include '../includes/db.php';

$instructor_id = $_SESSION['user_id'];
// Fetch sections assigned to this instructor
$sql = "SELECT s.*, c.course_code, c.course_title, 
        (SELECT COUNT(*) FROM tblenrollment e WHERE e.section_id = s.section_id AND e.is_deleted=0) as enrolled_count
        FROM tblsection s 
        JOIN tblcourse c ON s.course_id = c.course_id 
        WHERE s.instructor_id = $instructor_id AND s.is_deleted = 0";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Grading & Class Lists</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="d-flex">
        <?php $activePage = 'grading'; include 'sidebar.php'; ?>
        <div class="content flex-grow-1 p-4">
            <h3 class="fw-bold mb-4">Grading & Class Lists</h3>
            
            <?php if($result->num_rows > 0): ?>
            <div class="row g-4">
                <?php while($row = $result->fetch_assoc()): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-body">
                            <h5 class="fw-bold text-navy"><?php echo $row['section_code']; ?></h5>
                            <p class="text-muted mb-2"><?php echo $row['course_code']; ?> - <?php echo $row['course_title']; ?></p>
                            <hr>
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-info text-dark">Students: <?php echo $row['enrolled_count']; ?></span>
                                <a href="submit_grade.php?section_id=<?php echo $row['section_id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Manage Grades
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
            <?php else: ?>
                <div class="alert alert-info">You have no assigned sections yet.</div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>