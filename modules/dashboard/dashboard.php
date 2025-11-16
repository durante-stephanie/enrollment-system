<?php 
// ✅ FIX: Go up two levels to find db.php
include '../../includes/db.php'; 
$activePage = 'dashboard'; 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIS Dashboard</title>
    
    <!-- CSS Libraries -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- ✅ FIX: Go up two levels to find style.css -->
    <link rel="stylesheet" href="../../css/style.css">
    
    <style>
        /* Dashboard Specific Styles */
        .stat-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            overflow: hidden;
            position: relative;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-md);
        }
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            background-color: rgba(86, 124, 141, 0.1);
            color: var(--teal);
        }
        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            color: var(--navy);
            margin-bottom: 0;
        }
        .stat-label {
            color: #777;
            font-size: 0.9rem;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .chart-container {
            position: relative;
            height: 300px;
            width: 100%;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <div class="sidebar">
        <h4>📋 SIS</h4>
        <!-- ✅ FIX: Adjusted links to point to sibling folders using '../' -->
        <a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
        <a href="../department/index.php"><i class="fas fa-building"></i> Departments</a>
        <a href="../program/index.php"><i class="fas fa-graduation-cap"></i> Programs</a>
        <a href="../student/index.php"><i class="fas fa-user-graduate"></i> Students</a>
        <a href="../instructor/index.php"><i class="fas fa-chalkboard-teacher"></i> Instructors</a>
        <a href="../term/index.php"><i class="fas fa-calendar-alt"></i> Terms</a>
        <a href="../room/index.php"><i class="fas fa-door-open"></i> Rooms</a>
        <a href="../course/index.php"><i class="fas fa-book"></i> Courses</a>
        <a href="../prerequisite/index.php"><i class="fas fa-link"></i> Prerequisites</a>
        <a href="../section/index.php"><i class="fas fa-book-open"></i> Sections</a>
        <a href="../enrollment/index.php"><i class="fas fa-clipboard-list"></i> Enrollments</a>
    </div>

    <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold">Dashboard Overview</h2>
                <p class="text-muted">Welcome back, Administrator.</p>
            </div>
            <div class="date-display text-end">
                <h5 class="fw-bold mb-0 text-primary" id="currentDate"></h5>
                <small class="text-muted" id="currentTime"></small>
            </div>
        </div>

        <!-- 1. Statistics Cards Row -->
        <div class="row g-4 mb-4">
            <!-- Students Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card p-3 h-100 border-0">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3">
                            <i class="fas fa-user-graduate"></i>
                        </div>
                        <div>
                            <h3 class="stat-value" id="totalStudents">0</h3>
                            <span class="stat-label">Total Students</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Instructors Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card p-3 h-100 border-0">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="background-color: rgba(47, 65, 86, 0.1); color: var(--navy);">
                            <i class="fas fa-chalkboard-teacher"></i>
                        </div>
                        <div>
                            <h3 class="stat-value" id="totalInstructors">0</h3>
                            <span class="stat-label">Instructors</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Courses Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card p-3 h-100 border-0">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="background-color: rgba(200, 217, 230, 0.3); color: #4a6fa5;">
                            <i class="fas fa-book"></i>
                        </div>
                        <div>
                            <h3 class="stat-value" id="totalCourses">0</h3>
                            <span class="stat-label">Active Courses</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sections Card -->
            <div class="col-md-6 col-lg-3">
                <div class="card stat-card p-3 h-100 border-0">
                    <div class="d-flex align-items-center">
                        <div class="stat-icon me-3" style="background-color: rgba(245, 239, 235, 1); color: #d4a373;">
                            <i class="fas fa-layer-group"></i>
                        </div>
                        <div>
                            <h3 class="stat-value" id="totalSections">0</h3>
                            <span class="stat-label">Active Sections</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Charts Row -->
        <div class="row g-4">
            <!-- Program Distribution Chart -->
            <div class="col-lg-8">
                <div class="card p-4 h-100 border-0 shadow-sm">
                    <h5 class="fw-bold mb-3">Student Distribution by Program</h5>
                    <div class="chart-container">
                        <canvas id="programChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Year Level Chart -->
            <div class="col-lg-4">
                <div class="card p-4 h-100 border-0 shadow-sm">
                    <h5 class="fw-bold mb-3">Students by Year Level</h5>
                    <div class="chart-container">
                        <canvas id="yearLevelChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js Library -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- ✅ FIX: Go up two levels to find the JS folder -->
    <script src="../../js/dashboard.js"></script>

    <script>
        // Simple Date Time Script
        function updateTime() {
            const now = new Date();
            const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
            document.getElementById('currentDate').innerText = now.toLocaleDateString('en-US', options);
            document.getElementById('currentTime').innerText = now.toLocaleTimeString('en-US');
        }
        setInterval(updateTime, 1000);
        updateTime();
    </script>
</body>
</html>