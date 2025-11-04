<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include '../../includes/db.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'read':
        $sql = "SELECT s.section_id, s.section_code, s.course_id, s.term_id, s.instructor_id, s.room_id, s.day_pattern, s.start_time, s.end_time, s.max_capacity,
                       c.course_code, t.term_code, 
                       CONCAT(i.last_name, ', ', i.first_name) AS instructor_name,
                       r.room_code
                FROM tblsection s
                JOIN tblcourse c ON s.course_id = c.course_id
                JOIN tblterm t ON s.term_id = t.term_id
                JOIN tblinstructor i ON s.instructor_id = i.instructor_id
                JOIN tblroom r ON s.room_id = r.room_id
                WHERE s.is_deleted = 0
                ORDER BY s.section_id DESC";
        $result = $conn->query($sql);
        if (!$result) {
            http_response_code(500);
            echo json_encode(['error' => 'Database query failed: ' . $conn->error]);
            exit;
        }
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'create':
        $section_code = trim($_POST['section_code']);
        $term_id = $_POST['term_id'];
        // Check for duplicate section code within the same term
        $check = $conn->prepare("SELECT COUNT(*) FROM tblsection WHERE section_code=? AND term_id=? AND is_deleted=0");
        $check->bind_param("si", $section_code, $term_id);
        $check->execute();
        $check->bind_result($count);
        $check->fetch();
        $check->close();

        if ($count > 0) {
            echo json_encode(['status' => 'duplicate']);
            exit;
        }

        $stmt = $conn->prepare("INSERT INTO tblsection (section_code, course_id, term_id, instructor_id, room_id, day_pattern, start_time, end_time, max_capacity, is_deleted)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
        $stmt->bind_param("siiiisssi", $section_code, $_POST['course_id'], $term_id, $_POST['instructor_id'], $_POST['room_id'], $_POST['day_pattern'], $_POST['start_time'], $_POST['end_time'], $_POST['max_capacity']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'update':
        $stmt = $conn->prepare("UPDATE tblsection SET section_code=?, course_id=?, term_id=?, instructor_id=?, room_id=?, day_pattern=?, start_time=?, end_time=?, max_capacity=? WHERE section_id=?");
        $stmt->bind_param("siiiisssii", $_POST['section_code'], $_POST['course_id'], $_POST['term_id'], $_POST['instructor_id'], $_POST['room_id'], $_POST['day_pattern'], $_POST['start_time'], $_POST['end_time'], $_POST['max_capacity'], $_POST['section_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'delete':
        $stmt = $conn->prepare("UPDATE tblsection SET is_deleted=1 WHERE section_id=?");
        $stmt->bind_param("i", $_POST['section_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    // Cases for populating dropdowns
    case 'courses':
        $result = $conn->query("SELECT course_id AS id, course_code AS name FROM tblcourse WHERE is_deleted = 0 ORDER BY course_code ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;
    case 'terms':
        $result = $conn->query("SELECT term_id AS id, term_code AS name FROM tblterm WHERE is_deleted = 0 ORDER BY term_code DESC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;
    case 'instructors':
        $result = $conn->query("SELECT instructor_id AS id, CONCAT(last_name, ', ', first_name) AS name FROM tblinstructor WHERE is_deleted = 0 ORDER BY last_name ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;
    case 'rooms':
        $result = $conn->query("SELECT room_id AS id, room_code AS name FROM tblroom WHERE is_deleted = 0 ORDER BY room_code ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>