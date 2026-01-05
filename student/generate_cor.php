<?php
require_once '../vendor/autoload.php'; // Adjust path to vendor folder
include '../includes/session.php';
checkLogin('student');
include '../includes/db.php';

$student_id = $_SESSION['user_id'];

// 1. Fetch Data again for the PDF
$student_sql = "SELECT s.*, p.program_name FROM tblstudent s 
                JOIN tblprogram p ON s.program_id = p.program_id 
                WHERE s.student_id = $student_id";
$s_res = $conn->query($student_sql);
$student = $s_res->fetch_assoc();

$enroll_sql = "SELECT c.course_code, c.course_title, c.units, sec.section_code, sec.day_pattern, sec.start_time, sec.end_time
               FROM tblenrollment e
               JOIN tblsection sec ON e.section_id = sec.section_id
               JOIN tblcourse c ON sec.course_id = c.course_id
               WHERE e.student_id = $student_id AND e.is_deleted = 0";
$subjects = $conn->query($enroll_sql);

// 2. Setup TCPDF
$pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
$pdf->SetCreator('SIS System');
$pdf->SetAuthor('Registrar');
$pdf->SetTitle('COR - ' . $student['student_no']);
$pdf->SetMargins(15, 15, 15);
$pdf->AddPage();

// 3. Create Content HTML
$html = '
<div style="text-align: center;">
    <h2>Polytechnic University of the Philippines</h2>
    <p>Taguig Campus</p>
    <h3>CERTIFICATE OF REGISTRATION</h3>
</div>
<br><br>
<table cellpadding="4">
    <tr>
        <td><strong>Name:</strong> ' . $student['last_name'] . ', ' . $student['first_name'] . '</td>
        <td><strong>Date:</strong> ' . date('F d, Y') . '</td>
    </tr>
    <tr>
        <td><strong>Student No:</strong> ' . $student['student_no'] . '</td>
        <td><strong>Program:</strong> ' . $student['program_name'] . '</td>
    </tr>
</table>
<br><br>
<table border="1" cellpadding="5" cellspacing="0">
    <tr style="background-color:#eee; font-weight:bold;">
        <th width="15%">Code</th>
        <th width="35%">Description</th>
        <th width="15%">Section</th>
        <th width="10%">Units</th>
        <th width="25%">Schedule</th>
    </tr>';

$total_units = 0;
while($row = $subjects->fetch_assoc()) {
    $total_units += $row['units'];
    $time = date('h:i A', strtotime($row['start_time'])) . '-' . date('h:i A', strtotime($row['end_time']));
    
    $html .= '<tr>
                <td>'.$row['course_code'].'</td>
                <td>'.$row['course_title'].'</td>
                <td>'.$row['section_code'].'</td>
                <td>'.$row['units'].'</td>
                <td>'.$row['day_pattern'].' '.$time.'</td>
              </tr>';
}

$html .= '
    <tr>
        <td colspan="3" style="text-align:right;"><strong>Total Units:</strong></td>
        <td colspan="2"><strong>'.$total_units.'</strong></td>
    </tr>
</table>
<br><br>
<p style="font-size: 10px;">This is a computer-generated document. No signature required.</p>
';

// 4. Output PDF
$pdf->writeHTML($html, true, false, true, false, '');
$pdf->Output('COR_' . $student['student_no'] . '.pdf', 'I'); // 'I' = Inline view, 'D' = Download
?>