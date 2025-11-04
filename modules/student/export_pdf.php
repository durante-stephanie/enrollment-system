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
        $this->Cell(0, 8, 'List of Students', 0, 1, 'C');
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
$pdf->SetTitle('Student List');
$pdf->SetMargins(10, 30, 10);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);

// 3. Add a page
$pdf->AddPage();

// 4. Build the HTML table
$html = '<table border="1" cellpadding="4" cellspacing="0" style="width: 100%; font-size: 9px;">';
$html .= '<tr style="background-color:#800000; color:#ffffff; font-weight:bold;">
            <th style="width: 13%;">Student No</th>
            <th style="width: 22%;">Name</th>
            <th style="width: 22%;">Email</th>
            <th style="width: 8%;">Gender</th>
            <th style="width: 12%;">Birthdate</th>
            <th style="width: 8%;">Year</th>
            <th style="width: 15%;">Program</th>
          </tr>';

// Fetch data from the database
$sql = "SELECT s.student_no, s.last_name, s.first_name, s.email, s.gender, s.birthdate, s.year_level, p.program_code
        FROM tblstudent s
        JOIN tblprogram p ON s.program_id = p.program_id
        WHERE s.is_deleted = 0
        ORDER BY s.last_name ASC, s.first_name ASC";
$result = $conn->query($sql);

$i = 0;
while ($row = $result->fetch_assoc()) {
    $bg = ($i % 2 === 0) ? '#FFFFFF' : '#F9F9F9'; // Alternating row colors
    $html .= '<tr style="background-color:' . $bg . ';">
                <td>' . htmlspecialchars($row['student_no']) . '</td>
                <td>' . htmlspecialchars($row['last_name'] . ', ' . $row['first_name']) . '</td>
                <td>' . htmlspecialchars($row['email']) . '</td>
                <td>' . htmlspecialchars($row['gender']) . '</td>
                <td>' . htmlspecialchars($row['birthdate']) . '</td>
                <td>' . htmlspecialchars($row['year_level']) . '</td>
                <td>' . htmlspecialchars($row['program_code']) . '</td>
              </tr>';
    $i++;
}

$html .= '</table>';

// 5. Output the HTML content to the PDF
$pdf->SetFont('helvetica', '', 9);
$pdf->writeHTML($html, true, false, true, false, '');

// 6. Close and output the PDF document
$pdf->Output('students_list.pdf', 'I');

$conn->close();
?>