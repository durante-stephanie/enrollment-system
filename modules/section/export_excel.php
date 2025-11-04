<?php
// Include the database connection
include '../../includes/db.php';

// Set the timezone to get the correct date and time
date_default_timezone_set('Asia/Manila');

// Set headers to force download of an Excel file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=sections_list.xls");

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
        <td colspan="7" style="font-size:12px; text-align:center;">List of Sections</td>
      </tr>';
echo '<tr><td colspan="7">&nbsp;</td></tr>'; // Spacer row

// Table Header
echo '<tr>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Section Code</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Course</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Term</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Instructor</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Room</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Schedule</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Max Capacity</th>
      </tr>';

// Fetch data for active sections only
$sql = "SELECT s.section_code, c.course_code, t.term_code, 
               CONCAT(i.last_name, ', ', i.first_name) AS instructor_name,
               r.room_code, s.day_pattern, s.start_time, s.end_time, s.max_capacity
        FROM tblsection s
        JOIN tblcourse c ON s.course_id = c.course_id
        JOIN tblterm t ON s.term_id = t.term_id
        JOIN tblinstructor i ON s.instructor_id = i.instructor_id
        JOIN tblroom r ON s.room_id = r.room_id
        WHERE s.is_deleted = 0
        ORDER BY t.term_code DESC, s.section_code ASC";
$result = $conn->query($sql);

$i = 0;
while ($row = $result->fetch_assoc()) {
    // Format the schedule string
    $schedule = $row['day_pattern'] . ' ' . date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time']));
    // Alternate row colors
    $bg = ($i % 2 === 0) ? '#ffffff' : '#f2f2f2';
    echo '<tr style="background-color: '.$bg.';">
            <td style="padding: 5px;">' . htmlspecialchars($row['section_code']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['course_code']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['term_code']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['instructor_name']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['room_code']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($schedule) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['max_capacity']) . '</td>
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