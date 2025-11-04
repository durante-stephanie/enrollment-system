<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

include '../../includes/db.php';

$sql = "SELECT e.*, 
               CONCAT(s.last_name, ', ', s.first_name) AS student_name,
               sec.section_code
        FROM tblenrollment e
        JOIN tblstudent s ON e.student_id = s.student_id
        JOIN tblsection sec ON e.section_id = sec.section_id
        WHERE e.is_deleted = 0
        ORDER BY e.enrollment_id DESC";

$result = $conn->query($sql);

// Add error checking for the query
if (!$result) {
    http_response_code(500);
    echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
    exit;
}

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode($data);
?>