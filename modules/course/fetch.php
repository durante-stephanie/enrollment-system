<?php
include '../../includes/db.php';
header('Content-Type: application/json');

$sql = "SELECT c.*, d.dept_name, d.dept_code
        FROM tblcourse c
        JOIN tbldepartment d ON c.dept_id = d.dept_id
        ORDER BY c.course_id DESC";
 
$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);
?>