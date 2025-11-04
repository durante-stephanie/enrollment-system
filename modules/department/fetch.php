<?php
include '../../includes/db.php';
$result = $conn->query("SELECT * FROM tbldepartment");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data);
?>