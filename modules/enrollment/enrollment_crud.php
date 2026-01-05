<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include '../../includes/db.php';

// --- Helper Function: Check Prerequisites (Logic is here but will be bypassed below) ---
function checkPrerequisites($conn, $student_id, $course_id) {
    // 1. Find all required prerequisite course IDs
    $prereq_stmt = $conn->prepare("
        SELECT prereq_course_id 
        FROM tblcourse_prerequisite 
        WHERE course_id = ? AND is_deleted = 0
    ");
    $prereq_stmt->bind_param("i", $course_id);
    $prereq_stmt->execute();
    $prereq_result = $prereq_stmt->get_result();
    $required_prereqs = [];
    while ($row = $prereq_result->fetch_assoc()) {
        $required_prereqs[] = $row['prereq_course_id'];
    }
    $prereq_stmt->close();

    if (empty($required_prereqs)) {
        return ['status' => 'success'];
    }

    // 2. Get all course_ids the student has 'Completed'
    $completed_stmt = $conn->prepare("
        SELECT DISTINCT sec.course_id
        FROM tblenrollment e
        JOIN tblsection sec ON e.section_id = sec.section_id
        WHERE e.student_id = ? 
          AND e.status = 'Completed' 
          AND e.is_deleted = 0
    ");
    $completed_stmt->bind_param("i", $student_id);
    $completed_stmt->execute();
    $completed_result = $completed_stmt->get_result();
    $completed_courses = [];
    while ($row = $completed_result->fetch_assoc()) {
        $completed_courses[] = $row['course_id'];
    }
    $completed_stmt->close();

    // 3. Find missing
    $missing_prereqs = [];
    foreach ($required_prereqs as $req_id) {
        if (!in_array($req_id, $completed_courses)) {
            $missing_prereqs[] = $req_id;
        }
    }

    if (!empty($missing_prereqs)) {
        // Fetch codes for error message
        $codes_to_fetch = implode(',', array_fill(0, count($missing_prereqs), '?'));
        $types = str_repeat('i', count($missing_prereqs));
        $codes_stmt = $conn->prepare("SELECT course_code FROM tblcourse WHERE course_id IN ($codes_to_fetch) AND is_deleted = 0");
        $codes_stmt->bind_param($types, ...$missing_prereqs);
        $codes_stmt->execute();
        $codes_result = $codes_stmt->get_result();
        $missing_codes = [];
        while ($row = $codes_result->fetch_assoc()) {
            $missing_codes[] = $row['course_code'];
        }
        $codes_stmt->close();

        return [
            'status' => 'prereq_failed', 
            'missing' => $missing_codes 
        ];
    }

    return ['status' => 'success'];
}

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'read':
        $sql = "SELECT e.enrollment_id, e.student_id, e.section_id, e.status, e.letter_grade,
                       CONCAT(s.last_name, ', ', s.first_name, ' (', s.student_no, ')') AS student_name,
                       sec.section_code, c.course_title,
                       CONCAT(sec.section_code, ' - ', c.course_title) AS section_name
                FROM tblenrollment e
                JOIN tblstudent s ON e.student_id = s.student_id
                JOIN tblsection sec ON e.section_id = sec.section_id
                JOIN tblcourse c ON sec.course_id = c.course_id
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

    case 'create': // IRREGULAR (Single Subject Enrollment)
        $student_id = $_POST['student_id'] ?? 0;
        $section_id = $_POST['section_id'] ?? 0;

        // 1. Validate Prerequisite (DISABLED)
        // We comment this out to allow enrollment regardless of prereq status
        /*
        $course_stmt = $conn->prepare("SELECT course_id FROM tblsection WHERE section_id = ? AND is_deleted = 0");
        $course_stmt->bind_param("i", $section_id);
        $course_stmt->execute();
        $c_res = $course_stmt->get_result()->fetch_assoc();
        $course_id = $c_res['course_id'] ?? 0;
        $course_stmt->close();

        $prereq_check = checkPrerequisites($conn, $student_id, $course_id);
        if ($prereq_check['status'] === 'prereq_failed') {
            echo json_encode($prereq_check);
            exit;
        }
        */
        // --- End Prerequisite Check ---

        // 2. Duplicate Check
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

        // 3. Insert Enrollment
        $stmt = $conn->prepare("INSERT INTO tblenrollment (student_id, section_id, status, letter_grade, date_enrolled, is_deleted)
                                VALUES (?, ?, ?, ?, CURDATE(), 0)");
        $stmt->bind_param("iiss", $student_id, $section_id, $_POST['status'], $_POST['final_grade']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'create_block': // REGULAR (Block Section Enrollment)
        $student_id = $_POST['student_id'] ?? 0;
        $selected_section_id = $_POST['section_id'] ?? 0; 
        $status = $_POST['status'] ?? 'Enrolled';
        $final_grade = $_POST['final_grade'] ?? ''; 

        // 1. Get block info (section_code & term)
        $block_stmt = $conn->prepare("SELECT section_code, term_id FROM tblsection WHERE section_id = ? AND is_deleted = 0");
        $block_stmt->bind_param("i", $selected_section_id);
        $block_stmt->execute();
        $block_res = $block_stmt->get_result()->fetch_assoc();
        
        if (!$block_res) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid block section selected.']);
            exit;
        }
        $section_code = $block_res['section_code'];
        $term_id = $block_res['term_id'];
        $block_stmt->close();

        // 2. Find all sections (subjects) in this block
        $all_sections_stmt = $conn->prepare("
            SELECT section_id, course_id 
            FROM tblsection 
            WHERE section_code = ? AND term_id = ? AND is_deleted = 0
        ");
        $all_sections_stmt->bind_param("si", $section_code, $term_id);
        $all_sections_stmt->execute();
        $all_sections_result = $all_sections_stmt->get_result();
        $sections_to_enroll = [];
        while ($row = $all_sections_result->fetch_assoc()) {
            $sections_to_enroll[] = $row;
        }
        $all_sections_stmt->close();

        if (empty($sections_to_enroll)) {
            echo json_encode(['status' => 'error', 'message' => 'No sections found for this block.']);
            exit;
        }

        // 3. Check existing enrollments to avoid duplicates
        $section_ids = array_column($sections_to_enroll, 'section_id');
        $placeholders = implode(',', array_fill(0, count($section_ids), '?'));
        $types = str_repeat('i', count($section_ids));
        
        $existing_stmt = $conn->prepare("
            SELECT section_id 
            FROM tblenrollment 
            WHERE student_id = ? AND section_id IN ($placeholders) AND is_deleted = 0
        ");
        $existing_stmt->bind_param("i" . $types, $student_id, ...$section_ids);
        $existing_stmt->execute();
        $existing_result = $existing_stmt->get_result();
        $existing_enrollments = [];
        while ($row = $existing_result->fetch_assoc()) {
            $existing_enrollments[] = $row['section_id'];
        }
        $existing_stmt->close();

        // 4. Enroll Loop
        $conn->begin_transaction();
        $enrolled_count = 0;
        
        try {
            $insert_stmt = $conn->prepare("
                INSERT INTO tblenrollment (student_id, section_id, status, letter_grade, date_enrolled, is_deleted)
                VALUES (?, ?, ?, ?, CURDATE(), 0)
            ");

            foreach ($sections_to_enroll as $section) {
                $s_id = $section['section_id'];
                $c_id = $section['course_id'];

                // Skip if already enrolled
                if (in_array($s_id, $existing_enrollments)) {
                    continue;
                }

                // --- PREREQUISITE CHECK DISABLED ---
                /*
                $prereq_check = checkPrerequisites($conn, $student_id, $c_id);
                if ($prereq_check['status'] === 'prereq_failed') {
                    // Logic to skip or stop would go here. 
                    // We disabled it so it proceeds to enroll.
                }
                */
                // -----------------------------------

                $insert_stmt->bind_param("iiss", $student_id, $s_id, $status, $final_grade);
                $insert_stmt->execute();
                $enrolled_count++;
            }

            $conn->commit();
            echo json_encode(['status' => 'success_block', 'enrolled_count' => $enrolled_count]);

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $exception->getMessage()]);
        }
        break;

    case 'update':
        $stmt = $conn->prepare("UPDATE tblenrollment SET status=?, letter_grade=? WHERE enrollment_id=? AND is_deleted=0");
        $stmt->bind_param("ssi", $_POST['status'], $_POST['final_grade'], $_POST['enrollment_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    case 'delete':
        $stmt = $conn->prepare("UPDATE tblenrollment SET is_deleted=1 WHERE enrollment_id=?");
        $stmt->bind_param("i", $_POST['enrollment_id']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'deleted']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    // --- Dropdown Actions ---

    case 'students':
        $result = $conn->query("SELECT student_id AS id, CONCAT(last_name, ', ', first_name, ' (', student_no, ')') AS name FROM tblstudent WHERE is_deleted=0 ORDER BY last_name ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'courses':
        $result = $conn->query("SELECT course_id AS id, CONCAT(course_code, ' - ', course_title) AS name FROM tblcourse WHERE is_deleted=0 ORDER BY course_code ASC");
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    case 'blocks':
        // Returns distinct block names for "Regular" dropdown
        $sql = "SELECT MIN(s.section_id) as id, 
                       s.section_code, 
                       t.term_code 
                FROM tblsection s
                JOIN tblterm t ON s.term_id = t.term_id
                WHERE s.is_deleted = 0
                GROUP BY s.section_code, s.term_id
                ORDER BY s.section_code ASC";
        $result = $conn->query($sql);
        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = [
                'id' => $row['id'],
                'name' => $row['section_code'] . ' (' . $row['term_code'] . ')'
            ];
        }
        echo json_encode($data);
        break;

    case 'sections':
        $course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
        if ($course_id > 0) {
            $stmt = $conn->prepare("SELECT s.section_id AS id, 
                                           CONCAT(s.section_code, ' (', s.day_pattern, ' ', TIME_FORMAT(s.start_time, '%H:%i'), '-', TIME_FORMAT(s.end_time, '%H:%i'), ')') AS name 
                                    FROM tblsection s
                                    WHERE s.course_id = ? AND s.is_deleted = 0 
                                    ORDER BY s.section_code ASC");
            $stmt->bind_param("i", $course_id);
        } else {
            $stmt = $conn->prepare("SELECT s.section_id AS id, CONCAT(s.section_code, ' - ', c.course_title) AS name 
                                    FROM tblsection s
                                    JOIN tblcourse c ON s.course_id = c.course_id
                                    WHERE s.is_deleted = 0
                                    ORDER BY s.section_code ASC");
        }
        $stmt->execute();
        $result = $stmt->get_result();
        echo json_encode($result->fetch_all(MYSQLI_ASSOC));
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
}
?>