<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include '../../includes/db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'read':
        $sql = "SELECT term_id, term_code, start_date, end_date FROM tblterm WHERE is_deleted = 0 ORDER BY start_date DESC";
        $result = $conn->query($sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
            exit;
        }
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'create':
        $term_code = trim($_POST['term_code'] ?? '');
        $check = $conn->prepare("SELECT COUNT(*) FROM tblterm WHERE term_code=? AND is_deleted=0");
        $check->bind_param("s", $term_code);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO tblterm (term_code, start_date, end_date, is_deleted) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("sss", $term_code, $_POST['start_date'], $_POST['end_date']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'update':
        $id = $_POST['term_id'] ?? 0;
        $term_code = trim($_POST['term_code'] ?? '');
        $check = $conn->prepare("SELECT COUNT(*) FROM tblterm WHERE term_code=? AND term_id<>? AND is_deleted=0");
        $check->bind_param("si", $term_code, $id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("UPDATE tblterm SET term_code=?, start_date=?, end_date=? WHERE term_id=?");
        $stmt->bind_param("sssi", $term_code, $_POST['start_date'], $_POST['end_date'], $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'delete':
        $stmt = $conn->prepare("UPDATE tblterm SET is_deleted=1 WHERE term_id=?");
        $stmt->bind_param("i", $_POST['term_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>