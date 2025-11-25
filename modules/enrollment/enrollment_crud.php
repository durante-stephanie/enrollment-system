<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
header('Content-Type: application/json');
include '../../includes/db.php';

// --- ✅ START: Reusable Prerequisite Check Function ---
/**
 * Checks if a student has met the prerequisites for a given course.
 *
 * @param mysqli $conn The database connection
 * @param int $student_id The student's ID
 * @param int $course_id The course ID to check against
 * @return array ['status' => 'success'] or ['status' => 'prereq_failed', 'missing' => ['CODE1', 'CODE2']]
 */
function checkPrerequisites($conn, $student_id, $course_id) {
    // 1. Find all required prerequisite course IDs for this course
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

    // 2. If no prerequisites, student is clear
    if (empty($required_prereqs)) {
        return ['status' => 'success'];
    }

    // 3. Get all course_ids the student has 'Completed'
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

    // 4. Find which prerequisites are missing
    $missing_prereqs = [];
    foreach ($required_prereqs as $req_id) {
        if (!in_array($req_id, $completed_courses)) {
            $missing_prereqs[] = $req_id;
        }
    }

    // 5. If any are missing, block and report them
    if (!empty($missing_prereqs)) {
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

    // 6. All prerequisites are met
    return ['status' => 'success'];
}
// --- ✅ END: Reusable Prerequisite Check Function ---

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

    case 'create': // This is now for IRREGULAR (single subject)
        $student_id = $_POST['student_id'] ?? 0;
        $section_id = $_POST['section_id'] ?? 0;

        // --- ✅ START: PREREQUISITE VALIDATION ---
        $course_stmt = $conn->prepare("SELECT course_id FROM tblsection WHERE section_id = ? AND is_deleted = 0");
        $course_stmt->bind_param("i", $section_id);
        $course_stmt->execute();
        $course_result = $course_stmt->get_result();
        $course_row = $course_result->fetch_assoc();
        $course_id = $course_row['course_id'] ?? 0;
        $course_stmt->close();

        if ($course_id === 0) {
            echo json_encode(['status' => 'error', 'message' => 'Selected section is invalid.']);
            exit;
        }

        // Use the reusable function
        $prereq_check = checkPrerequisites($conn, $student_id, $course_id);
        if ($prereq_check['status'] === 'prereq_failed') {
            echo json_encode($prereq_check);
            exit;
        }
        // --- ✅ END: PREREQUISITE VALIDATION ---

        // Original Duplicate Check
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

        // Original Insert Statement
        $stmt = $conn->prepare("INSERT INTO tblenrollment (student_id, section_id, status, letter_grade, date_enrolled, is_deleted)
                                VALUES (?, ?, ?, ?, CURDATE(), 0)");
        $stmt->bind_param("iiss", $student_id, $section_id, $_POST['status'], $_POST['final_grade']);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => $stmt->error]);
        }
        break;

    // --- ✅ START: NEW BLOCK ENROLLMENT CASE ---
    case 'create_block':
        $student_id = $_POST['student_id'] ?? 0;
        $selected_section_id = $_POST['section_id'] ?? 0; // The one section user picked
        $status = $_POST['status'] ?? 'Enrolled';
        // Grade is null on block enroll, use empty string for bind_param
        $final_grade = $_POST['final_grade'] ?? ''; 

        // 1. Get the block code (section_code) and term_id from the selected section
        $block_stmt = $conn->prepare("SELECT section_code, term_id FROM tblsection WHERE section_id = ? AND is_deleted = 0");
        $block_stmt->bind_param("i", $selected_section_id);
        $block_stmt->execute();
        $block_result = $block_stmt->get_result();
        $block_row = $block_result->fetch_assoc();
        
        if (!$block_row) {
            echo json_encode(['status' => 'error', 'message' => 'Invalid block section selected.']);
            exit;
        }
        $section_code = $block_row['section_code'];
        $term_id = $block_row['term_id'];
        $block_stmt->close();

        // 2. Find all sections in this block (same code, same term)
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
            $sections_to_enroll[] = $row; // Store both section_id and course_id
        }
        $all_sections_stmt->close();

        if (empty($sections_to_enroll)) {
            echo json_encode(['status' => 'error', 'message' => 'No sections found for this block.']);
            exit;
        }

        // 3. Find sections student is ALREADY enrolled in
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

        // 4. Start transaction
        $conn->begin_transaction();
        $enrolled_count = 0;
        $failed_prereqs = [];

        try {
            $insert_stmt = $conn->prepare("
                INSERT INTO tblenrollment (student_id, section_id, status, letter_grade, date_enrolled, is_deleted)
                VALUES (?, ?, ?, ?, CURDATE(), 0)
            ");

            foreach ($sections_to_enroll as $section) {
                $s_id = $section['section_id'];
                $c_id = $section['course_id'];

                // 5. Skip if already enrolled
                if (in_array($s_id, $existing_enrollments)) {
                    continue;
                }

                // 6. Check prerequisites
                $prereq_check = checkPrerequisites($conn, $student_id, $c_id);
                if ($prereq_check['status'] === 'prereq_failed') {
                    // Don't exit immediately, collect all failures
                    $failed_prereqs = array_merge($failed_prereqs, $prereq_check['missing']);
                    continue; // Skip this course
                }

                // 7. Insert new enrollment
                $insert_stmt->bind_param("iiss", $student_id, $s_id, $status, $final_grade);
                $insert_stmt->execute();
                $enrolled_count++;
            }

            if (!empty($failed_prereqs)) {
                // If some courses failed prereqs, roll back the ones that succeeded
                $conn->rollback();
                echo json_encode([
                    'status' => 'prereq_failed',
                    'missing' => array_unique($failed_prereqs)
                ]);
                exit;
            }

            // 8. If all good, commit
            $conn->commit();
            echo json_encode(['status' => 'success_block', 'enrolled_count' => $enrolled_count]);

        } catch (mysqli_sql_exception $exception) {
            $conn->rollback();
            echo json_encode(['status' => 'error', 'message' => $exception->getMessage()]);
        }
        break;
    // --- ✅ END: NEW BLOCK ENROLLMENT CASE ---

    case 'update':
        // Only updates status and grade, not student/section
        $stmt = $conn->prepare("UPDATE tblenrollment SET status=?, letter_grade=? WHERE enrollment_id=? AND is_deleted=0");
        $stmt->bind_param("ssi",
            $_POST['status'],
            $_POST['final_grade'],
            $_POST['enrollment_id']
        );
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

    case 'sections':
        $course_id = isset($_GET['course_id']) ? (int)$_GET['course_id'] : 0;
        
        if ($course_id > 0) {
            // Irregular: Filter by specific course
            $stmt = $conn->prepare("SELECT s.section_id AS id, 
                                           CONCAT(s.section_code, ' (', s.day_pattern, ' ', TIME_FORMAT(s.start_time, '%H:%i'), '-', TIME_FORMAT(s.end_time, '%H:%i'), ')') AS name 
                                    FROM tblsection s
                                    WHERE s.course_id = ? AND s.is_deleted = 0 
                                    ORDER BY s.section_code ASC");
            $stmt->bind_param("i", $course_id);
        } else {
            // Regular: Show all sections
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