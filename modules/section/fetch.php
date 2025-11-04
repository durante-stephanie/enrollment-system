<?php
include '../../includes/db.php';
header('Content-Type: application/json');

$sql = "SELECT s.*, 
               c.course_code, 
               t.term_code, 
               CONCAT(i.last_name, ', ', i.first_name) AS instructor_name,
               r.room_code
        FROM tblsection s
        JOIN tblcourse c ON s.course_id = c.course_id
        JOIN tblterm t ON s.term_id = t.term_id
        JOIN tblinstructor i ON s.instructor_id = i.instructor_id
        JOIN tblroom r ON s.room_id = r.room_id
        ORDER BY s.section_id DESC";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);
?>