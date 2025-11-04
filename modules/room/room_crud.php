<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include '../../includes/db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'read':
        $sql = "SELECT room_id, building, room_code, capacity FROM tblroom WHERE is_deleted = 0 ORDER BY building ASC, room_code ASC";
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
        $room_code = trim($_POST['room_code'] ?? '');
        $check = $conn->prepare("SELECT COUNT(*) FROM tblroom WHERE room_code=? AND is_deleted=0");
        $check->bind_param("s", $room_code);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO tblroom (building, room_code, capacity, is_deleted) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("ssi", $_POST['building'], $room_code, $_POST['capacity']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'update':
        $id = $_POST['room_id'] ?? 0;
        $room_code = trim($_POST['room_code'] ?? '');
        $check = $conn->prepare("SELECT COUNT(*) FROM tblroom WHERE room_code=? AND room_id<>? AND is_deleted=0");
        $check->bind_param("si", $room_code, $id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }
        
        $stmt = $conn->prepare("UPDATE tblroom SET building=?, room_code=?, capacity=? WHERE room_id=?");
        $stmt->bind_param("ssii", $_POST['building'], $room_code, $_POST['capacity'], $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'delete':
        $stmt = $conn->prepare("UPDATE tblroom SET is_deleted=1 WHERE room_id=?");
        $stmt->bind_param("i", $_POST['room_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;
        
    case 'buildings': // For the filter dropdown
        $result = $conn->query("SELECT DISTINCT building FROM tblroom WHERE is_deleted = 0 ORDER BY building ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>