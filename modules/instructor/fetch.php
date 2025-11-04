<?php
include '../../includes/db.php';
header('Content-Type: application/json');

$sql = "SELECT 
          i.instructor_id,
          i.last_name,
          i.first_name,
          i.email,
          d.dept_name,
          d.dept_code
        FROM tblinstructor i
        JOIN tbldepartment d ON i.dept_id = d.dept_id
        ORDER BY i.instructor_id DESC";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);
?>