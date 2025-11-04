<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include '../../includes/db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    // 🔹 Fetch active departments only
    case 'read':
        $result = $conn->query("SELECT dept_id, dept_code, dept_name FROM tbldepartment WHERE is_deleted = 0 ORDER BY dept_id DESC");
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        break;

    // 🔹 Create new department (with duplicate check)
    case 'create':
        $code = trim($_POST['dept_code'] ?? '');
        $name = trim($_POST['dept_name'] ?? '');

        $check = $conn->prepare("SELECT COUNT(*) FROM tbldepartment WHERE (dept_code=? OR dept_name=?) AND is_deleted=0");
        $check->bind_param("ss", $code, $name);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO tbldepartment (dept_code, dept_name, is_deleted) VALUES (?, ?, 0)");
        $stmt->bind_param("ss", $code, $name);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    // 🔹 Update department (with duplicate check)
    case 'update':
        $id   = $_POST['dept_id'] ?? 0;
        $code = trim($_POST['dept_code'] ?? '');
        $name = trim($_POST['dept_name'] ?? '');

        $check = $conn->prepare("SELECT COUNT(*) FROM tbldepartment WHERE (dept_code=? OR dept_name=?) AND dept_id<>? AND is_deleted=0");
        $check->bind_param("ssi", $code, $name, $id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE tbldepartment SET dept_code=?, dept_name=? WHERE dept_id=? AND is_deleted=0");
        $stmt->bind_param("ssi", $code, $name, $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    // 🔹 Soft delete (archive)
    case 'delete':
        $id = $_POST['dept_id'] ?? 0;
        $stmt = $conn->prepare("UPDATE tbldepartment SET is_deleted=1 WHERE dept_id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}