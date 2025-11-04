<?php
// Include the database connection and Composer's autoloader
include '../../includes/db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

// Set the timezone to ensure the correct date and time
date_default_timezone_set('Asia/Manila');

// Extend the TCPDF class to create a custom Header and Footer
class MYPDF extends TCPDF {

    // Page header
    public function Header() {
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'Polytechnic University of the Philippines - Taguig Campus', 0, 1, 'C');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 8, 'List of Sections', 0, 1, 'C');
        $this->Ln(5);
    }

    // Page footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        
        // Format the date and time as MM/DD/YY HH:MM AM/PM
        $dateTime = date('m/d/y h:i A');
        
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, 0, 'L');
        $this->Cell(0, 10, 'Printed on: ' . $dateTime, 0, 0, 'R');
    }
}

// --- SCRIPT START ---

// 1. Create a new PDF document in Landscape orientation
$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// 2. Set document information and properties
$pdf->SetCreator('Enrollment System');
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Section List');
$pdf->SetMargins(10, 30, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);

// 3. Add a page
$pdf->AddPage();

// 4. Build the HTML table
$html = '<table border="1" cellpadding="4" cellspacing="0" style="width: 100%; font-size: 9px;">'; // smaller font size
$html .= '<tr style="background-color:#800000; color:#ffffff; font-weight:bold;">
            <th style="width: 10%;">Code</th>
            <th style="width: 10%;">Course</th>
            <th style="width: 10%;">Term</th>
            <th style="width: 20%;">Instructor</th>
            <th style="width: 10%;">Room</th>
            <th style="width: 25%;">Schedule</th>
            <th style="width: 15%;">Max Capacity</th>
          </tr>';

// Fetch data from the database
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
    $schedule = $row['day_pattern'] . ' ' . date('h:i A', strtotime($row['start_time'])) . ' - ' . date('h:i A', strtotime($row['end_time']));
    $bg = ($i % 2 === 0) ? '#FFFFFF' : '#F9F9F9'; // Alternating row colors
    $html .= '<tr style="background-color:' . $bg . ';">
                <td>' . htmlspecialchars($row['section_code']) . '</td>
                <td>' . htmlspecialchars($row['course_code']) . '</td>
                <td>' . htmlspecialchars($row['term_code']) . '</td>
                <td>' . htmlspecialchars($row['instructor_name']) . '</td>
                <td>' . htmlspecialchars($row['room_code']) . '</td>
                <td>' . htmlspecialchars($schedule) . '</td>
                <td>' . htmlspecialchars($row['max_capacity']) . '</td>
              </tr>';
    $i++;
}

$html .= '</table>';

// 5. Output the HTML content to the PDF
$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTML($html, true, false, true, false, '');

// 6. Close and output the PDF document
$pdf->Output('sections_list.pdf', 'I');

$conn->close();
?>