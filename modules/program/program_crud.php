<?php
// Report all errors and ensure the output is JSON
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');

include __DIR__ . '/../../includes/db.php';

// Check if the database connection failed
if (!$conn) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed. Check includes/db.php']);
    exit;
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'read':
        $sql = "SELECT p.program_id, p.program_code, p.program_name, p.dept_id, d.dept_name
                FROM tblprogram p
                JOIN tbldepartment d ON p.dept_id = d.dept_id
                WHERE p.is_deleted = 0
                ORDER BY p.program_id DESC";
        $result = $conn->query($sql);

        // THIS IS THE CRITICAL PART: It will catch the SQL error
        if (!$result) {
            http_response_code(500); // Internal Server Error
            echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
            exit;
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = $row;
        }
        echo json_encode($data);
        break;

    // --- The rest of your cases are likely fine ---
    case 'create':
        $code = trim($_POST['program_code'] ?? '');
        $name = trim($_POST['program_name'] ?? '');
        $dept_id = $_POST['dept_id'] ?? 0;
        $check = $conn->prepare("SELECT COUNT(*) FROM tblprogram WHERE (program_code=? OR program_name=?) AND is_deleted=0");
        $check->bind_param("ss", $code, $name);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();
        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }
        $stmt = $conn->prepare("INSERT INTO tblprogram (program_code, program_name, dept_id, is_deleted) VALUES (?, ?, ?, 0)");
        $stmt->bind_param("ssi", $code, $name, $dept_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;
    case 'update':
        $id      = $_POST['program_id'] ?? 0;
        $code    = trim($_POST['program_code'] ?? '');
        $name    = trim($_POST['program_name'] ?? '');
        $dept_id = $_POST['dept_id'] ?? 0;
        $check = $conn->prepare("SELECT COUNT(*) FROM tblprogram WHERE (program_code=? OR program_name=?) AND program_id<>? AND is_deleted=0");
        $check->bind_param("ssi", $code, $name, $id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();
        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }
        $stmt = $conn->prepare("UPDATE tblprogram SET program_code=?, program_name=?, dept_id=? WHERE program_id=? AND is_deleted=0");
        $stmt->bind_param("ssii", $code, $name, $dept_id, $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;
    case 'delete':
        $id = $_POST['program_id'] ?? 0;
        $stmt = $conn->prepare("UPDATE tblprogram SET is_deleted=1 WHERE program_id=?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;
    case 'departments':
        $result = $conn->query("SELECT dept_id, dept_name FROM tbldepartment WHERE is_deleted = 0 ORDER BY dept_name ASC");
        $options = [];
        while ($row = $result->fetch_assoc()) {
            $options[] = ['id' => $row['dept_id'], 'name' => $row['dept_name']];
        }
        echo json_encode($options);
        break;
    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>