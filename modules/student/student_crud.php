<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include '../../includes/db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'read':
        $sql = "SELECT s.*, p.program_code AS program_name 
                FROM tblstudent s
                JOIN tblprogram p ON s.program_id = p.program_id
                WHERE s.is_deleted = 0
                ORDER BY s.student_id DESC";
        $result = $conn->query($sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
            exit;
        }
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'create':
        $student_no = trim($_POST['student_no'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $check = $conn->prepare("SELECT COUNT(*) FROM tblstudent WHERE (student_no=? OR email=?) AND is_deleted=0");
        $check->bind_param("ss", $student_no, $email);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO tblstudent (student_no, last_name, first_name, email, gender, birthdate, year_level, program_id, is_deleted)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("ssssssii", $student_no, $_POST['last_name'], $_POST['first_name'], $email, $_POST['gender'], $_POST['birthdate'], $_POST['year_level'], $_POST['program_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'update':
        $id = $_POST['student_id'] ?? 0;
        $student_no = trim($_POST['student_no'] ?? '');
        $email = trim($_POST['email'] ?? '');

        $check = $conn->prepare("SELECT COUNT(*) FROM tblstudent WHERE (student_no=? OR email=?) AND student_id<>? AND is_deleted=0");
        $check->bind_param("ssi", $student_no, $email, $id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE tblstudent SET student_no=?, last_name=?, first_name=?, email=?, gender=?, birthdate=?, year_level=?, program_id=? WHERE student_id=?");
        $stmt->bind_param("ssssssiii", $student_no, $_POST['last_name'], $_POST['first_name'], $email, $_POST['gender'], $_POST['birthdate'], $_POST['year_level'], $_POST['program_id'], $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'delete':
        $stmt = $conn->prepare("UPDATE tblstudent SET is_deleted=1 WHERE student_id=?");
        $stmt->bind_param("i", $_POST['student_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'programs':
        $result = $conn->query("SELECT program_id AS id, program_code AS name FROM tblprogram WHERE is_deleted=0 ORDER BY program_code ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>