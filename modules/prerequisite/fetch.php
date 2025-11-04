<?php
include '../../includes/db.php';
header('Content-Type: application/json');

$course_id = $_GET['course_id'] ?? 0;
$result = $conn->query("SELECT prereq_course_id FROM tblcourse_prerequisite WHERE course_id = $course_id");
$prereqs = [];

while ($row = $result->fetch_assoc()) {
  $prereqs[] = $row['prereq_course_id'];
}

echo json_encode($prereqs);
?>