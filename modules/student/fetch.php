<?php
include '../../includes/db.php';
header('Content-Type: application/json');

$sql = "SELECT 
          s.student_id,
          s.student_no,
          s.last_name,
          s.first_name,
          s.email,
          s.gender,
          s.birthdate,
          s.year_level,
          p.program_name,
          p.program_code,
          d.dept_name,
          d.dept_code
        FROM tblstudent s
        JOIN tblprogram p ON s.program_id = p.program_id
        JOIN tbldepartment d ON p.dept_id = d.dept_id
        ORDER BY s.student_id DESC";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);
?>