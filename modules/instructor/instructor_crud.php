<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include '../../includes/db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'read':
        $sql = "SELECT i.instructor_id, i.last_name, i.first_name, i.email, i.dept_id, d.dept_name
                FROM tblinstructor i
                JOIN tbldepartment d ON i.dept_id = d.dept_id
                WHERE i.is_deleted = 0
                ORDER BY i.last_name ASC";
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
        $email = trim($_POST['email'] ?? '');
        $check = $conn->prepare("SELECT COUNT(*) FROM tblinstructor WHERE email=? AND is_deleted=0");
        $check->bind_param("s", $email);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO tblinstructor (last_name, first_name, email, dept_id, is_deleted) VALUES (?, ?, ?, ?, 0)");
        $stmt->bind_param("sssi", $_POST['last_name'], $_POST['first_name'], $email, $_POST['dept_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'update':
        $id = $_POST['instructor_id'] ?? 0;
        $email = trim($_POST['email'] ?? '');
        $check = $conn->prepare("SELECT COUNT(*) FROM tblinstructor WHERE email=? AND instructor_id<>? AND is_deleted=0");
        $check->bind_param("si", $email, $id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE tblinstructor SET last_name=?, first_name=?, email=?, dept_id=? WHERE instructor_id=?");
        $stmt->bind_param("sssii", $_POST['last_name'], $_POST['first_name'], $email, $_POST['dept_id'], $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'delete':
        $stmt = $conn->prepare("UPDATE tblinstructor SET is_deleted=1 WHERE instructor_id=?");
        $stmt->bind_param("i", $_POST['instructor_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'departments':
        $result = $conn->query("SELECT dept_id, dept_name FROM tbldepartment WHERE is_deleted = 0 ORDER BY dept_name ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>