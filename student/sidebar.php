<div class="sidebar">
    <h4>🎓 Student Portal</h4>
    <a href="dashboard.php" class="<?= ($activePage == 'dashboard') ? 'active' : '' ?>">
        <i class="fas fa-th-large"></i> Dashboard
    </a>
    <a href="enrollment.php" class="<?= ($activePage == 'enrollment') ? 'active' : '' ?>">
        <i class="fas fa-clipboard-check"></i> Enrollment
    </a>
    <a href="schedule.php" class="<?= ($activePage == 'schedule') ? 'active' : '' ?>">
        <i class="fas fa-calendar-day"></i> My Schedule
    </a>
    <a href="grades.php" class="<?= ($activePage == 'grades') ? 'active' : '' ?>">
        <i class="fas fa-star"></i> My Grades
    </a>
    <a href="cor.php" class="<?= ($activePage == 'cor') ? 'active' : '' ?>">
        <i class="fas fa-file-pdf"></i> View COR
    </a>
    <a href="profile.php" class="<?= ($activePage == 'profile') ? 'active' : '' ?>">
        <i class="fas fa-user-cog"></i> Profile & Security
    </a>
    
    <a href="../logout.php" class="mt-auto text-danger">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>