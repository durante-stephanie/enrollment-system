<?php
include '../includes/session.php';
checkLogin('student');
include '../includes/db.php';

$student_id = $_SESSION['user_id'];

// 1. Fetch Student Info
// We use a LEFT JOIN to get the program code.
$stmt = $conn->prepare("SELECT s.*, p.program_code 
                        FROM tblstudent s 
                        LEFT JOIN tblprogram p ON s.program_id = p.program_id 
                        WHERE s.student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

// FIX: Handle missing 'status' column safely
$student_status = isset($student['status']) ? $student['status'] : 'Regular'; 

// 2. Calculate Total Units Enrolled
$units_sql = "SELECT SUM(c.units) as total_units 
              FROM tblenrollment e 
              JOIN tblsection s ON e.section_id = s.section_id 
              JOIN tblcourse c ON s.course_id = c.course_id 
              WHERE e.student_id = $student_id AND e.is_deleted = 0";
$units_res = $conn->query($units_sql);
$total_units = $units_res->fetch_assoc()['total_units'] ?? 0;

// 3. Get Today's Schedule
$current_day_short = date('D'); // Mon, Tue, Wed...
$day_map = [
    'Mon' => 'M', 'Tue' => 'T', 'Wed' => 'W', 'Thu' => 'Th', 'Fri' => 'F', 'Sat' => 'S'
];
$today_code = $day_map[$current_day_short] ?? '';

$sched_sql = "SELECT c.course_code, c.course_title, s.start_time, s.end_time, s.day_pattern, r.room_code
              FROM tblenrollment e
              JOIN tblsection s ON e.section_id = s.section_id
              JOIN tblcourse c ON s.course_id = c.course_id
              LEFT JOIN tblroom r ON s.room_id = r.room_id
              WHERE e.student_id = $student_id AND e.is_deleted = 0
              ORDER BY s.start_time ASC";
$sched_res = $conn->query($sched_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Student Portal | Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .welcome-banner {
            background: linear-gradient(135deg, var(--navy) 0%, var(--teal) 100%);
            color: white;
            border-radius: var(--radius-lg);
            padding: 30px;
            position: relative;
            overflow: hidden;
        }
        .welcome-banner::after {
            content: '\f501'; /* FontAwesome Icon */
            font-family: "Font Awesome 6 Free";
            font-weight: 900;
            position: absolute;
            right: 20px;
            bottom: -20px;
            font-size: 8rem;
            opacity: 0.1;
            color: white;
        }
        .schedule-card {
            border-left: 5px solid var(--teal);
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php $activePage = 'dashboard'; include 'sidebar.php'; ?>

        <div class="content flex-grow-1 p-4">
            
            <div class="welcome-banner mb-4 shadow-sm">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h2 class="fw-bold mb-1">Welcome back, <?php echo $student['first_name']; ?>! 👋</h2>
                        <p class="mb-0 opacity-75"><?php echo $student['program_code']; ?> | Year <?php echo $student['year_level']; ?></p>
                    </div>
                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                        <h4 class="mb-0 fw-bold" id="clock">00:00:00</h4>
                        <small id="date">Monday, Jan 1, 2024</small>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-md-3">
                    <div class="card p-3 h-100 border-0 shadow-sm text-center">
                        <div class="text-muted mb-2"><i class="fas fa-book fa-2x text-primary"></i></div>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $total_units; ?></h3>
                        <small class="text-muted">Units Enrolled</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 h-100 border-0 shadow-sm text-center">
                        <div class="text-muted mb-2"><i class="fas fa-graduation-cap fa-2x text-success"></i></div>
                        <h3 class="fw-bold text-dark mb-0"><?php echo $student_status; ?></h3>
                        <small class="text-muted">Academic Status</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3 h-100 border-0 shadow-sm text-center">
                        <div class="text-muted mb-2"><i class="fas fa-calendar-check fa-2x text-warning"></i></div>
                        <h3 class="fw-bold text-dark mb-0">1st Sem</h3>
                        <small class="text-muted">Current Term</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <a href="cor.php" class="text-decoration-none">
                        <div class="card p-3 h-100 border-0 shadow-sm text-center bg-light hover-shadow">
                            <div class="text-muted mb-2"><i class="fas fa-print fa-2x text-danger"></i></div>
                            <h5 class="fw-bold text-dark mb-0 mt-2">Print COR</h5>
                            <small class="text-muted">Click to view</small>
                        </div>
                    </a>
                </div>
            </div>

            <div class="row mt-4 g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h5 class="fw-bold mb-0 text-navy">📅 Today's Schedule</h5>
                            <a href="schedule.php" class="btn btn-sm btn-outline-primary">View Full</a>
                        </div>
                        <div class="card-body">
                            <?php 
                            $has_class_today = false;
                            if($sched_res->num_rows > 0):
                                while($row = $sched_res->fetch_assoc()):
                                    if(strpos($row['day_pattern'], $today_code) !== false): 
                                        $has_class_today = true;
                                        $start = date('h:i A', strtotime($row['start_time']));
                                        $end = date('h:i A', strtotime($row['end_time']));
                            ?>
                                <div class="card mb-3 schedule-card shadow-sm">
                                    <div class="card-body d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-bold mb-1"><?php echo $row['course_code']; ?> - <?php echo $row['course_title']; ?></h6>
                                            <small class="text-muted"><i class="fas fa-clock me-1"></i> <?php echo $start . ' - ' . $end; ?></small>
                                        </div>
                                        <div class="text-end">
                                            <span class="badge-soft"><?php echo $row['room_code']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                    endif;
                                endwhile;
                            endif;

                            if(!$has_class_today):
                            ?>
                                <div class="text-center py-5 text-muted">
                                    <i class="fas fa-mug-hot fa-3x mb-3 opacity-25"></i>
                                    <p>No classes scheduled for today.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateClock() {
            const now = new Date();
            document.getElementById('clock').innerText = now.toLocaleTimeString();
            document.getElementById('date').innerText = now.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>