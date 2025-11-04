<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include '../../includes/db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'read':
        $sql = "SELECT e.enrollment_id, e.student_id, e.section_id, e.status, e.letter_grade,
                       CONCAT(s.last_name, ', ', s.first_name) AS student_name,
                       sec.section_code
                FROM tblenrollment e
                JOIN tblstudent s ON e.student_id = s.student_id
                JOIN tblsection sec ON e.section_id = sec.section_id
                WHERE e.is_deleted = 0
                ORDER BY e.enrollment_id DESC";
        $result = $conn->query($sql);
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
        break;

    case 'create':
        $student_id = $_POST['student_id'] ?? 0;
        $section_id = $_POST['section_id'] ?? 0;

        // Duplicate check: Prevent enrolling the same student in the same section
        $check = $conn->prepare("SELECT COUNT(*) FROM tblenrollment WHERE student_id=? AND section_id=? AND is_deleted=0");
        $check->bind_param("ii", $student_id, $section_id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO tblenrollment (student_id, section_id, status, letter_grade, date_enrolled, is_deleted)
                                VALUES (?, ?, ?, ?, CURDATE(), 0)");
        $stmt->bind_param("iiss",
            $student_id,
            $section_id,
            $_POST['status'],
            $_POST['final_grade']
        );
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'update':
        $stmt = $conn->prepare("UPDATE tblenrollment SET student_id=?, section_id=?, status=?, letter_grade=? WHERE enrollment_id=? AND is_deleted=0");
        $stmt->bind_param("iissi",
            $_POST['student_id'],
            $_POST['section_id'],
            $_POST['status'],
            $_POST['final_grade'],
            $_POST['enrollment_id']
        );
        if ($stmt->execute()) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'delete': // This is now a soft-delete (archive)
        $stmt = $conn->prepare("UPDATE tblenrollment SET is_deleted=1 WHERE enrollment_id=?");
        $stmt->bind_param("i", $_POST['enrollment_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'students':
        $result = $conn->query("SELECT student_id AS id, CONCAT(last_name, ', ', first_name) AS name FROM tblstudent ORDER BY last_name ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'sections':
        $result = $conn->query("SELECT s.section_id AS id, CONCAT(s.section_code, ' - ', c.course_title) AS name 
                                FROM tblsection s
                                JOIN tblcourse c ON s.course_id = c.course_id
                                ORDER BY s.section_code ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>