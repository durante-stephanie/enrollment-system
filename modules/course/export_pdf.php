<?php
// Include the database connection
include '../../includes/db.php';

// Include the Composer autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// ✅ **FIX 1: Set the timezone to your location to ensure correct date and time**
date_default_timezone_set('Asia/Manila');

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends TCPDF {

    // Page header
    public function Header() {
        $this->SetFont('helvetica', 'B', 12);
        $this->Cell(0, 10, 'Polytechnic University of the Philippines - Taguig Campus', 0, 1, 'C');
        $this->SetFont('helvetica', '', 10);
        $this->Cell(0, 8, 'List of Courses', 0, 1, 'C');
        $this->Ln(5);
    }

    // Page footer
    public function Footer() {
        $this->SetY(-15);
        $this->SetFont('helvetica', 'I', 8);
        
        // ✅ **FIX 2: Change the date and time format**
        // 'm/d/y' gives you MM/DD/YY format
        // 'h:i A' gives you the time in 12-hour format with AM/PM
        $dateTime = date('m/d/y h:i A');
        
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().' of '.$this->getAliasNbPages(), 0, 0, 'L');
        $this->Cell(0, 10, 'Printed on: ' . $dateTime, 0, 0, 'R');
    }
}

// --- SCRIPT START ---

// Create new PDF document
$pdf = new MYPDF('L', 'mm', 'A4', true, 'UTF-8', false);

// Set document information and properties
$pdf->SetCreator('Enrollment System');
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Course List');
$pdf->SetMargins(10, 30, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);

// Add a page
$pdf->AddPage();

// Build the HTML table
$html = '<table border="1" cellpadding="4" cellspacing="0" style="width: 100%;">';
$html .= '<tr style="background-color:#800000; color:#ffffff; font-weight:bold;">
            <th style="width: 15%;">Code</th>
            <th style="width: 30%;">Title</th>
            <th style="width: 8%;">Units</th>
            <th style="width: 10%;">Lecture</th>
            <th style="width: 10%;">Lab</th>
            <th style="width: 27%;">Department</th>
          </tr>';

// Fetch data from the database
$sql = "SELECT c.course_code, c.course_title, c.units, c.lecture_hours, c.lab_hours, d.dept_name
        FROM tblcourse c
        JOIN tbldepartment d ON c.dept_id = d.dept_id
        WHERE c.is_deleted = 0
        ORDER BY d.dept_name, c.course_code ASC";
$result = $conn->query($sql);

// Table rows
$i = 0;
while ($row = $result->fetch_assoc()) {
    $bg = ($i % 2 === 0) ? '#FFFFFF' : '#F9F9F9';
    $html .= '<tr style="background-color:' . $bg . ';">
                <td>' . htmlspecialchars($row['course_code']) . '</td>
                <td>' . htmlspecialchars($row['course_title']) . '</td>
                <td>' . htmlspecialchars($row['units']) . '</td>
                <td>' . htmlspecialchars($row['lecture_hours']) . '</td>
                <td>' . htmlspecialchars($row['lab_hours']) . '</td>
                <td>' . htmlspecialchars($row['dept_name']) . '</td>
              </tr>';
    $i++;
}
$html .= '</table>';

// Output the HTML content
$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output('course_list.pdf', 'I');

$conn->close();
?>