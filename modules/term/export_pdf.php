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
        $this->Cell(0, 8, 'List of Terms', 0, 1, 'C');
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

// 1. Create a new PDF document in Portrait orientation
$pdf = new MYPDF('P', 'mm', 'A4', true, 'UTF-8', false);

// 2. Set document information and properties
$pdf->SetCreator('Enrollment System');
$pdf->SetAuthor('Admin');
$pdf->SetTitle('Term List');
$pdf->SetMargins(15, 30, 15);
$pdf->SetHeaderMargin(10);
$pdf->SetFooterMargin(10);
$pdf->SetAutoPageBreak(TRUE, 15);

// 3. Add a page
$pdf->AddPage();

// 4. Build the HTML table
$html = '<table border="1" cellpadding="5" cellspacing="0" style="width: 100%;">';
$html .= '<tr style="background-color:#800000; color:#ffffff; font-weight:bold;">
            <th style="width: 30%;">Term Code</th>
            <th style="width: 35%;">Start Date</th>
            <th style="width: 35%;">End Date</th>
          </tr>';

// Fetch data from the database
$sql = "SELECT term_code, start_date, end_date 
        FROM tblterm 
        WHERE is_deleted = 0 
        ORDER BY start_date DESC";
$result = $conn->query($sql);

$i = 0;
while ($row = $result->fetch_assoc()) {
    $bg = ($i % 2 === 0) ? '#FFFFFF' : '#F9F9F9'; // Alternating row colors
    $html .= '<tr style="background-color:' . $bg . ';">
                <td>' . htmlspecialchars($row['term_code']) . '</td>
                <td>' . htmlspecialchars(date('F d, Y', strtotime($row['start_date']))) . '</td>
                <td>' . htmlspecialchars(date('F d, Y', strtotime($row['end_date']))) . '</td>
              </tr>';
    $i++;
}

$html .= '</table>';

// 5. Output the HTML content to the PDF
$pdf->SetFont('helvetica', '', 10);
$pdf->writeHTML($html, true, false, true, false, '');

// 6. Close and output the PDF document
$pdf->Output('terms_list.pdf', 'I');

$conn->close();
?>