<?php
header('Content-Type: application/json');
include '../../includes/db.php';

$response = [
    'total_students' => 0,
    'total_instructors' => 0,
    'total_courses' => 0,
    'total_sections' => 0,
    'program_distribution' => [],
    'student_year_level' => []
];

//Count Active Students
$sql = "SELECT COUNT(*) as count FROM tblstudent WHERE is_deleted = 0";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    $response['total_students'] = $row['count'];
}

//Count Active Instructors
$sql = "SELECT COUNT(*) as count FROM tblinstructor WHERE is_deleted = 0";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    $response['total_instructors'] = $row['count'];
}

//Count Active Courses
$sql = "SELECT COUNT(*) as count FROM tblcourse WHERE is_deleted = 0";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    $response['total_courses'] = $row['count'];
}

//Count Active Sections
$sql = "SELECT COUNT(*) as count FROM tblsection WHERE is_deleted = 0";
$result = $conn->query($sql);
if ($row = $result->fetch_assoc()) {
    $response['total_sections'] = $row['count'];
}

//Chart Data: Students per Program
$sql = "SELECT p.program_code, COUNT(s.student_id) as count 
        FROM tblprogram p 
        LEFT JOIN tblstudent s ON p.program_id = s.program_id AND s.is_deleted = 0
        WHERE p.is_deleted = 0
        GROUP BY p.program_id";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $response['program_distribution'][] = [
        'label' => $row['program_code'],
        'count' => $row['count']
    ];
}

//Chart Data: Students per Year Level
$sql = "SELECT year_level, COUNT(student_id) as count 
        FROM tblstudent 
        WHERE is_deleted = 0 
        GROUP BY year_level 
        ORDER BY year_level";
$result = $conn->query($sql);
while ($row = $result->fetch_assoc()) {
    $response['student_year_level'][] = [
        'label' => 'Year ' . $row['year_level'],
        'count' => $row['count']
    ];
}

echo json_encode($response);
?>