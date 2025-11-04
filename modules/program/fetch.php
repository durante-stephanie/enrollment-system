<?php
include '../../includes/db.php';
header('Content-Type: application/json');

$sql = "SELECT p.program_id, p.program_code, p.program_name, d.dept_name
        FROM tblprogram p
        JOIN tbldepartment d ON p.dept_id = d.dept_id";

$result = $conn->query($sql);
$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);
?>