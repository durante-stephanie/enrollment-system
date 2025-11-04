<?php
// Include the database connection
include '../../includes/db.php';

// Set the timezone to get the correct date and time
date_default_timezone_set('Asia/Manila');

// Set headers to force download of an Excel file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=students_list.xls");

// Get the current date and time in the desired format
$dateTime = date('m/d/y h:i A');

// --- Start of HTML content ---

// Start table without a width property to allow auto-sizing
echo '<table border="1" style="border-collapse: collapse; font-family: Arial, sans-serif;">';

// Main Header
echo '<tr>
        <td colspan="7" style="font-size:16px; font-weight:bold; text-align:center; background-color:#800000; color:#fff;">Polytechnic University of the Philippines - Taguig Campus</td>
      </tr>';
echo '<tr>
        <td colspan="7" style="font-size:12px; text-align:center;">List of Students</td>
      </tr>';
echo '<tr><td colspan="7">&nbsp;</td></tr>'; // Spacer row

// Table Header
echo '<tr>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Student No</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Name</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Email</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Gender</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Birthdate</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Year Level</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Program</th>
      </tr>';

// Fetch data for active students only
$sql = "SELECT s.student_no, s.last_name, s.first_name, s.email, s.gender, s.birthdate, s.year_level, p.program_code
        FROM tblstudent s
        JOIN tblprogram p ON s.program_id = p.program_id
        WHERE s.is_deleted = 0
        ORDER BY s.last_name ASC, s.first_name ASC";
$result = $conn->query($sql);

$i = 0;
while ($row = $result->fetch_assoc()) {
    // Alternate row colors
    $bg = ($i % 2 === 0) ? '#ffffff' : '#f2f2f2';
    echo '<tr style="background-color: '.$bg.';">
            <td style="mso-number-format:\'\@\'; text-align:left; padding: 5px;">' . htmlspecialchars($row['student_no']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['last_name'] . ', ' . $row['first_name']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['email']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['gender']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['birthdate']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['year_level']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['program_code']) . '</td>
          </tr>';
    $i++;
}

// Footer
echo '<tr><td colspan="7">&nbsp;</td></tr>'; // Spacer row
echo '<tr>
        <td colspan="7" style="font-size:10px; text-align:right;">Printed on: ' . $dateTime . '</td>
      </tr>';

echo '</table>';

$conn->close();
?>