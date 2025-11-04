<?php
// Include the database connection
include '../../includes/db.php';

// Set the timezone to get the correct date and time
date_default_timezone_set('Asia/Manila');

// Set headers to force download of an Excel file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=rooms_list.xls");

// Get the current date and time in the desired format
$dateTime = date('m/d/y h:i A');

// --- Start of HTML content ---

// Start table without a width property to allow auto-sizing
echo '<table border="1" style="border-collapse: collapse; font-family: Arial, sans-serif;">';

// Main Header
echo '<tr>
        <td colspan="3" style="font-size:16px; font-weight:bold; text-align:center; background-color:#800000; color:#fff;">Polytechnic University of the Philippines - Taguig Campus</td>
      </tr>';
echo '<tr>
        <td colspan="3" style="font-size:12px; text-align:center;">List of Rooms</td>
      </tr>';
echo '<tr><td colspan="3">&nbsp;</td></tr>'; // Spacer row

// Table Header
echo '<tr>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Building</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Room Code</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Capacity</th>
      </tr>';

// Fetch data for active rooms only
$sql = "SELECT building, room_code, capacity 
        FROM tblroom 
        WHERE is_deleted = 0 
        ORDER BY building ASC, room_code ASC";
$result = $conn->query($sql);

$i = 0;
while ($row = $result->fetch_assoc()) {
    // Alternate row colors
    $bg = ($i % 2 === 0) ? '#ffffff' : '#f2f2f2';
    echo '<tr style="background-color: '.$bg.';">
            <td style="padding: 5px;">' . htmlspecialchars($row['building']) . '</td>
            
            <td style="mso-number-format:\'\@\'; text-align:left; padding: 5px;">' . htmlspecialchars($row['room_code']) . '</td>
            
            <td style="padding: 5px;">' . htmlspecialchars($row['capacity']) . '</td>
          </tr>';
    $i++;
}

// Footer
echo '<tr><td colspan="3">&nbsp;</td></tr>'; // Spacer row
echo '<tr>
        <td colspan="3" style="font-size:10px; text-align:right;">Printed on: ' . $dateTime . '</td>
      </tr>';

echo '</table>';

$conn->close();
?>