<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include '../../includes/db.php';

$action = $_GET['action'] ?? '';

switch ($action) {

    case 'read':
        $sql = "SELECT c.*, d.dept_name
                FROM tblcourse c
                JOIN tbldepartment d ON c.dept_id = d.dept_id
                WHERE c.is_deleted = 0
                ORDER BY c.course_id DESC";
        $result = $conn->query($sql);
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'create':
        $code = trim($_POST['course_code'] ?? '');
        $title = trim($_POST['course_title'] ?? '');

        // ✅ Robust, case-insensitive check for duplicates
        $check = $conn->prepare("SELECT COUNT(*) FROM tblcourse WHERE (LOWER(course_code) = LOWER(?) OR LOWER(course_title) = LOWER(?)) AND is_deleted = 0");
        $check->bind_param("ss", $code, $title);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO tblcourse (course_code, course_title, units, lecture_hours, lab_hours, dept_id, is_deleted) VALUES (?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("ssdiii", $code, $title, $_POST['units'], $_POST['lecture_hours'], $_POST['lab_hours'], $_POST['dept_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'update':
        $id = $_POST['course_id'] ?? 0;
        $code = trim($_POST['course_code'] ?? '');
        $title = trim($_POST['course_title'] ?? '');

        $check = $conn->prepare("SELECT COUNT(*) FROM tblcourse WHERE (LOWER(course_code) = LOWER(?) OR LOWER(course_title) = LOWER(?)) AND course_id <> ? AND is_deleted = 0");
        $check->bind_param("ssi", $code, $title, $id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE tblcourse SET course_code=?, course_title=?, units=?, lecture_hours=?, lab_hours=?, dept_id=? WHERE course_id=?");
        $stmt->bind_param("ssdiiii", $code, $title, $_POST['units'], $_POST['lecture_hours'], $_POST['lab_hours'], $_POST['dept_id'], $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'delete':
        $stmt = $conn->prepare("UPDATE tblcourse SET is_deleted=1 WHERE course_id=?");
        $stmt->bind_param("i", $_POST['course_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'departments':
        $result = $conn->query("SELECT dept_id AS id, dept_name AS name FROM tbldepartment ORDER BY dept_name ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>