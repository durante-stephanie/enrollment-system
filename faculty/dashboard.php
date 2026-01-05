<?php
include '../includes/session.php';
checkLogin('faculty');
include '../includes/db.php';

$instructor_id = $_SESSION['user_id'];
$current_day_short = date('D'); // E.g., "Mon", "Tue"
$day_map = ['Mon' => 'M', 'Tue' => 'T', 'Wed' => 'W', 'Thu' => 'Th', 'Fri' => 'F', 'Sat' => 'S'];
$today_code = $day_map[$current_day_short] ?? '';

// 1. Get Instructor Details
$stmt = $conn->prepare("SELECT i.*, d.dept_name FROM tblinstructor i 
                        LEFT JOIN tbldepartment d ON i.dept_id = d.dept_id 
                        WHERE i.instructor_id = ?");
$stmt->bind_param("i", $instructor_id);
$stmt->execute();
$faculty = $stmt->get_result()->fetch_assoc();

// 2. Get Today's Schedule
$sched_sql = "SELECT s.*, c.course_code, c.course_title, r.room_code 
              FROM tblsection s 
              JOIN tblcourse c ON s.course_id = c.course_id 
              LEFT JOIN tblroom r ON s.room_id = r.room_id 
              WHERE s.instructor_id = $instructor_id AND s.is_deleted = 0 
              ORDER BY s.start_time ASC";
$all_sections = $conn->query($sched_sql);

// 3. Get Grading Progress (Ungraded Students count)
$grading_sql = "SELECT s.section_code, 
                (SELECT COUNT(*) FROM tblenrollment e WHERE e.section_id = s.section_id AND e.is_deleted = 0) as total,
                (SELECT COUNT(*) FROM tblenrollment e WHERE e.section_id = s.section_id AND e.letter_grade IS NOT NULL AND e.letter_grade != '' AND e.is_deleted = 0) as graded
                FROM tblsection s 
                WHERE s.instructor_id = $instructor_id AND s.is_deleted = 0";
$grading_res = $conn->query($grading_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Faculty Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .welcome-card {
            background: linear-gradient(135deg, var(--navy), #4a6fa5);
            color: white;
            border-radius: var(--radius-lg);
            position: relative;
            overflow: hidden;
        }
        .welcome-card::before {
            content: '';
            position: absolute;
            top: -50px; right: -50px;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.1);
            border-radius: 50%;
        }
        .progress-xs { height: 7px; border-radius: 5px; }
        .schedule-item {
            border-left: 4px solid var(--teal);
            background: #f8f9fa;
            transition: 0.2s;
        }
        .schedule-item:hover { transform: translateX(5px); background: #fff; box-shadow: var(--shadow-sm); }
        .icon-box {
            width: 45px; height: 45px;
            display: flex; align-items: center; justify-content: center;
            border-radius: 10px;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php $activePage = 'dashboard'; include 'sidebar.php'; ?>
        <div class="content flex-grow-1 p-4">
            
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card welcome-card p-4 h-100 border-0 shadow-sm">
                        <div class="d-flex justify-content-between align-items-center position-relative z-1">
                            <div>
                                <h2 class="fw-bold mb-1">Hello, <?php echo $faculty['first_name']; ?>! 👋</h2>
                                <p class="mb-0 opacity-75">Department of <?php echo $faculty['dept_name']; ?></p>
                            </div>
                            <div class="text-end d-none d-md-block">
                                <h2 class="fw-bold mb-0" id="clock">00:00</h2>
                                <small id="date"><?php echo date('l, F j, Y'); ?></small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-4">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0 text-navy"><i class="fas fa-calendar-day me-2 text-warning"></i> Classes for Today</h5>
                        </div>
                        <div class="card-body pt-0">
                            <?php 
                            $has_class = false;
                            if($all_sections->num_rows > 0) {
                                while($sec = $all_sections->fetch_assoc()) {
                                    // Check if today matches the pattern (e.g. if Today is 'M' and pattern is 'M/Th')
                                    if(strpos($sec['day_pattern'], $today_code) !== false) {
                                        $has_class = true;
                                        $start = date('h:i A', strtotime($sec['start_time']));
                                        $end = date('h:i A', strtotime($sec['end_time']));
                                        
                                        // Highlight if class is currently happening
                                        $now_time = date('H:i:s');
                                        $is_now = ($now_time >= $sec['start_time'] && $now_time <= $sec['end_time']);
                            ?>
                                <div class="schedule-item p-3 mb-3 rounded <?php echo $is_now ? 'bg-primary bg-opacity-10' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h6 class="fw-bold mb-1"><?php echo $sec['course_code']; ?> <span class="badge bg-secondary text-light ms-2"><?php echo $sec['section_code']; ?></span></h6>
                                            <small class="text-muted"><?php echo $sec['course_title']; ?></small>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-dark"><?php echo $start; ?> - <?php echo $end; ?></div>
                                            <small class="text-primary"><i class="fas fa-map-marker-alt"></i> <?php echo $sec['room_code']; ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php 
                                    }
                                }
                            }
                            if(!$has_class): 
                            ?>
                                <div class="text-center py-5">
                                    <div class="icon-box bg-light rounded-circle mx-auto mb-3" style="width:60px; height:60px;">
                                        <i class="fas fa-coffee text-muted"></i>
                                    </div>
                                    <h6 class="text-muted">No classes scheduled for today.</h6>
                                    <small>Enjoy your free time!</small>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="fw-bold mb-0 text-navy"><i class="fas fa-chart-line me-2 text-success"></i> Grading Progress</h5>
                        </div>
                        <div class="card-body pt-0">
                            <?php if($grading_res->num_rows > 0): ?>
                                <?php while($g = $grading_res->fetch_assoc()): 
                                    $percent = ($g['total'] > 0) ? round(($g['graded'] / $g['total']) * 100) : 0;
                                    $color = ($percent == 100) ? 'bg-success' : 'bg-primary';
                                ?>
                                <div class="mb-4">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="fw-bold small"><?php echo $g['section_code']; ?></span>
                                        <span class="small text-muted"><?php echo $g['graded']; ?>/<?php echo $g['total']; ?> Graded</span>
                                    </div>
                                    <div class="progress progress-xs">
                                        <div class="progress-bar <?php echo $color; ?>" role="progressbar" style="width: <?php echo $percent; ?>%"></div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-4">No sections assigned yet.</p>
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
            document.getElementById('clock').innerText = now.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
        }
        setInterval(updateClock, 1000);
        updateClock();
    </script>
</body>
</html>