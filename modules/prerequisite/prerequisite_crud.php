<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include '../../includes/db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'read':
        // ✅ FIX: Added "cp.is_deleted = 0" to only show active links
        $sql = "SELECT cp.course_id, cp.prereq_course_id,
                       c1.course_code AS course_code,
                       c2.course_code AS prereq_code
                FROM tblcourse_prerequisite cp
                JOIN tblcourse c1 ON cp.course_id = c1.course_id
                JOIN tblcourse c2 ON cp.prereq_course_id = c2.course_id
                WHERE c1.is_deleted = 0 AND c2.is_deleted = 0 AND cp.is_deleted = 0
                ORDER BY c1.course_code, c2.course_code";
        $result = $conn->query($sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
            exit;
        }
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'create':
        $course_id = $_POST['course_id'] ?? 0;
        $prereq_id = $_POST['prereq_course_id'] ?? 0;

        if ($course_id === $prereq_id) {
            echo json_encode(['status' => 'self_prereq']);
            exit;
        }

        // ✅ FIX: Check for active duplicates only
        $check = $conn->prepare("SELECT COUNT(*) FROM tblcourse_prerequisite WHERE course_id=? AND prereq_course_id=? AND is_deleted = 0");
        $check->bind_param("ii", $course_id, $prereq_id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();
        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }
        
        // ✅ FIX: Explicitly set is_deleted to 0
        $stmt = $conn->prepare("INSERT INTO tblcourse_prerequisite (course_id, prereq_course_id, is_deleted) VALUES (?, ?, 0)");
        $stmt->bind_param("ii", $course_id, $prereq_id);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'update':
        $new_course_id = $_POST['course_id'] ?? 0;
        $new_prereq_id = $_POST['prereq_course_id'] ?? 0;
        $old_course_id = $_POST['old_course_id'] ?? 0;
        $old_prereq_id = $_POST['old_prereq_course_id'] ?? 0;

        $conn->begin_transaction();
        try {
            // ✅ FIX: Soft-delete the old record
            $delete_stmt = $conn->prepare("UPDATE tblcourse_prerequisite SET is_deleted = 1 WHERE course_id=? AND prereq_course_id=?");
            $delete_stmt->bind_param("ii", $old_course_id, $old_prereq_id);
            $delete_stmt->execute();

            // Insert the new record
            $insert_stmt = $conn->prepare("INSERT INTO tblcourse_prerequisite (course_id, prereq_course_id, is_deleted) VALUES (?, ?, 0)");
            $insert_stmt->bind_param("ii", $new_course_id, $new_prereq_id);
            $insert_stmt->execute();

            $conn->commit();
            echo json_encode(['status' => 'updated']);

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $exception->getMessage()]);
        }
        break;

    case 'delete':
        // ✅ FIX: Changed from DELETE to UPDATE for soft-delete
        $stmt = $conn->prepare("UPDATE tblcourse_prerequisite SET is_deleted = 1 WHERE course_id=? AND prereq_course_id=?");
        $stmt->bind_param("ii", $_POST['course_id'], $_POST['prereq_course_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'courses':
        $result = $conn->query("SELECT course_id AS id, course_code AS name FROM tblcourse WHERE is_deleted = 0 ORDER BY course_code ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>