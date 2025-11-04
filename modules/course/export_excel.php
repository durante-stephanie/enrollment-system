<?php
// Include the database connection
include '../../includes/db.php';

// Set the timezone to get the correct date and time
date_default_timezone_set('Asia/Manila');

// Set headers to force download of an Excel file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=courses_list.xls");

// Get the current date and time in the desired format
$dateTime = date('m/d/y h:i A');

// Start echoing the HTML table with some basic styling
echo '<table border="1" style="border-collapse: collapse; width:100%; font-family: Arial, sans-serif;">';

// Main Header
echo '<tr>
        <td colspan="6" style="font-size:16px; font-weight:bold; text-align:center; background-color:#800000; color:#fff;">Polytechnic University of the Philippines - Taguig Campus</td>
      </tr>';
echo '<tr>
        <td colspan="6" style="font-size:12px; text-align:center;">List of Courses</td>
      </tr>';
echo '<tr><td colspan="6">&nbsp;</td></tr>'; // Spacer row

// Table Header
echo '<tr>
        <th style="background-color:#333; color:#fff; font-weight:bold;">Course Code</th>
        <th style="background-color:#333; color:#fff; font-weight:bold;">Title</th>
        <th style="background-color:#333; color:#fff; font-weight:bold;">Units</th>
        <th style="background-color:#333; color:#fff; font-weight:bold;">Lecture</th>
        <th style="background-color:#333; color:#fff; font-weight:bold;">Lab</th>
        <th style="background-color:#333; color:#fff; font-weight:bold;">Department</th>
      </tr>';

// Fetch data from the database
$sql = "SELECT c.course_code, c.course_title, c.units, c.lecture_hours, c.lab_hours, d.dept_name
        FROM tblcourse c
        JOIN tbldepartment d ON c.dept_id = d.dept_id
        WHERE c.is_deleted = 0
        ORDER BY d.dept_name, c.course_code ASC";
$result = $conn->query($sql);

$i = 0;
while ($row = $result->fetch_assoc()) {
    // Set alternating row colors
    $bg = ($i % 2 === 0) ? '#ffffff' : '#f2f2f2';
    echo '<tr style="background-color: ' . $bg . ';">
            <td>' . htmlspecialchars($row['course_code']) . '</td>
            <td>' . htmlspecialchars($row['course_title']) . '</td>
            <td>' . htmlspecialchars($row['units']) . '</td>
            <td>' . htmlspecialchars($row['lecture_hours']) . '</td>
            <td>' . htmlspecialchars($row['lab_hours']) . '</td>
            <td>' . htmlspecialchars($row['dept_name']) . '</td>
          </tr>';
    $i++;
}

// Footer
echo '<tr><td colspan="6">&nbsp;</td></tr>'; // Spacer row
echo '<tr>
        <td colspan="6" style="font-size:10px; text-align:right;">Printed on: ' . $dateTime . '</td>
      </tr>';

echo '</table>';

$conn->close();
?>