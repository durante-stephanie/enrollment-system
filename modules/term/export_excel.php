<?php
// Include the database connection
include '../../includes/db.php';

// Set the timezone to get the correct date and time
date_default_timezone_set('Asia/Manila');

// Set headers to force download of an Excel file
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=terms_list.xls");

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
        <td colspan="3" style="font-size:12px; text-align:center;">List of Terms</td>
      </tr>';
echo '<tr><td colspan="3">&nbsp;</td></tr>'; // Spacer row

// Table Header
echo '<tr>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Term Code</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">Start Date</th>
        <th style="background-color:#333; color:#fff; font-weight:bold; text-align:left; padding: 5px;">End Date</th>
      </tr>';

// Fetch data for active terms only
$sql = "SELECT term_code, start_date, end_date 
        FROM tblterm 
        WHERE is_deleted = 0 
        ORDER BY start_date DESC";
$result = $conn->query($sql);

$i = 0;
while ($row = $result->fetch_assoc()) {
    // Alternate row colors
    $bg = ($i % 2 === 0) ? '#ffffff' : '#f2f2f2';
    echo '<tr style="background-color: '.$bg.';">
            <td style="mso-number-format:\'\@\'; text-align:left; padding: 5px;">' . htmlspecialchars($row['term_code']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['start_date']) . '</td>
            <td style="padding: 5px;">' . htmlspecialchars($row['end_date']) . '</td>
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