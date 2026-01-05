<?php
// Increase memory and time limit for large imports
ini_set('memory_limit', '512M');
set_time_limit(300);

include '../../includes/db.php';

$action = $_GET['action'] ?? '';

// --- BACKUP FUNCTIONALITY ---
if ($action == 'backup') {
    $tables = [];
    $result = $conn->query("SHOW TABLES");
    while ($row = $result->fetch_row()) {
        $tables[] = $row[0];
    }

    $sqlScript = "-- Enrollment System Backup\n";
    $sqlScript .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
    $sqlScript .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

    foreach ($tables as $table) {
        // Get Create Table logic
        $row2 = $conn->query("SHOW CREATE TABLE $table")->fetch_row();
        $sqlScript .= "\n\n" . $row2[1] . ";\n\n";

        // Get Data
        $result = $conn->query("SELECT * FROM $table");
        $num_fields = $result->field_count;

        while ($row = $result->fetch_row()) {
            $sqlScript .= "INSERT INTO $table VALUES(";
            for ($j = 0; $j < $num_fields; $j++) {
                if (isset($row[$j])) {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n", "\\n", $row[$j]);
                    $sqlScript .= '"' . $row[$j] . '"';
                } else {
                    $sqlScript .= '""';
                }
                if ($j < ($num_fields - 1)) {
                    $sqlScript .= ',';
                }
            }
            $sqlScript .= ");\n";
        }
    }
    
    $sqlScript .= "\nSET FOREIGN_KEY_CHECKS=1;";

    // Force Download
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename=db_backup_' . date('Y-m-d_His') . '.sql');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header('Content-Length: ' . strlen($sqlScript));
    echo $sqlScript;
    exit;
}

// --- RESTORE FUNCTIONALITY ---
if ($action == 'restore') {
    header('Content-Type: application/json');

    if (!isset($_FILES['sql_file']) || $_FILES['sql_file']['error'] != UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'File upload failed.']);
        exit;
    }

    $file = $_FILES['sql_file']['tmp_name'];
    $sql_contents = file_get_contents($file);
    
    // Remove comments
    $lines = explode("\n", $sql_contents);
    $clean_sql = "";
    foreach ($lines as $line) {
        if (substr(trim($line), 0, 2) == '--' || $line == '') continue;
        $clean_sql .= $line;
    }

    // Split by semicolon (naive approach, usually sufficient for simple dumps)
    $queries = explode(";", $clean_sql);

    $conn->begin_transaction();
    try {
        $conn->query("SET FOREIGN_KEY_CHECKS=0");
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $conn->query($query);
            }
        }
        $conn->query("SET FOREIGN_KEY_CHECKS=1");
        $conn->commit();
        echo json_encode(['status' => 'success']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
    exit;
}
?>