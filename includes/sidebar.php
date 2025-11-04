<?php
// Sidebar include. Expect $activePage to be set before including.
?>
<div class="sidebar">
    <h4>📋 SIS</h4>
    <a href="../dashboard.php" class="<?= ($activePage == 'dashboard') ? 'active' : '' ?>">
        <i class="fas fa-th-large"></i> Dashboard
    </a>
    <a href="../department/index.php" class="<?= ($activePage == 'departments') ? 'active' : '' ?>">
        <i class="fas fa-building"></i> Departments
    </a>
    <a href="../program/index.php" class="<?= ($activePage == 'programs') ? 'active' : '' ?>">
        <i class="fas fa-graduation-cap"></i> Programs
    </a>
    <a href="../student/index.php" class="<?= ($activePage == 'students') ? 'active' : '' ?>">
        <i class="fas fa-user-graduate"></i> Students
    </a>
    <a href="../instructor/index.php" class="<?= ($activePage == 'instructors') ? 'active' : '' ?>">
        <i class="fas fa-chalkboard-teacher"></i> Instructors
    </a>
    <a href="../term/index.php" class="<?= ($activePage == 'terms') ? 'active' : '' ?>">
        <i class="fas fa-calendar-alt"></i> Terms
    </a>
    <a href="../room/index.php" class="<?= ($activePage == 'rooms') ? 'active' : '' ?>">
        <i class="fas fa-door-open"></i> Rooms
    </a>
    <a href="../course/index.php" class="<?= ($activePage == 'courses') ? 'active' : '' ?>">
        <i class="fas fa-book"></i> Courses
    </a>
    <a href="../prerequisite/index.php" class="<?= ($activePage == 'prerequisites') ? 'active' : '' ?>">
        <i class="fas fa-link"></i> Prerequisites
    </a>
    <a href="../section/index.php" class="<?= ($activePage == 'sections') ? 'active' : '' ?>">
        <i class="fas fa-book-open"></i> Sections
    </a>
    <a href="../enrollment/index.php" class="<?= ($activePage == 'enrollments') ? 'active' : '' ?>">
        <i class="fas fa-clipboard-list"></i> Enrollments
    </a>
</div>