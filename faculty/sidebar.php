<div class="sidebar">
    <h4>👨‍🏫 Faculty</h4>
    <a href="dashboard.php" class="<?= ($activePage == 'dashboard') ? 'active' : '' ?>">
        <i class="fas fa-th-large"></i> Dashboard
    </a>
    <a href="schedule.php" class="<?= ($activePage == 'schedule') ? 'active' : '' ?>">
        <i class="fas fa-calendar-alt"></i> My Schedule
    </a>
    <a href="grading.php" class="<?= ($activePage == 'grading') ? 'active' : '' ?>">
        <i class="fas fa-marker"></i> Grading & Students
    </a>
    <a href="profile.php" class="<?= ($activePage == 'profile') ? 'active' : '' ?>">
        <i class="fas fa-user-cog"></i> Profile
    </a>
    <a href="../logout.php" class="mt-auto text-danger">
        <i class="fas fa-sign-out-alt"></i> Logout
    </a>
</div>