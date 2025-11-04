<?php
include '../../includes/db.php';
header('Content-Type: application/json');

$result = $conn->query("SELECT * FROM tblterm ORDER BY term_id DESC");
$data = [];

while ($row = $result->fetch_assoc()) {
  $data[] = $row;
}

echo json_encode($data);
?>