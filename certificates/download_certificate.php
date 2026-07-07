<?php
session_start();

include("../config.php");
require(__DIR__ . '/fpdf/fpdf.php');

if(!isset($_GET['id'])){
    die("Invalid Certificate");
}

if(!isset($_SESSION['user_id'])){
    die("Please login first");
}

$certificate_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// FETCH CERTIFICATE DATA (KEPT EXACTLY UNCHANGED)
$query = mysqli_query($conn,"
SELECT 
certificates.*,
users.fullname AS user_name,
quizzes.Title AS quiz_title
FROM certificates
JOIN users ON certificates.user_id = users.id
JOIN quizzes ON certificates.quiz_id = quizzes.ID
WHERE certificates.id='$certificate_id'
AND certificates.user_id='$user_id'
");

if(mysqli_num_rows($query) == 0){
    die("Certificate Not Found");
}

$data = mysqli_fetch_assoc($query);

// =================================================================
// EXTENDED FPDF CLASS FOR PREMIUM VECTOR GRAPHICS & LOGO DESIGNS
// =================================================================
class QuizifyPremiumCertificate extends FPDF {
    
    // Hex to RGB Array Convertor
    function hex2rgb($hex) {
        $hex = str_replace("#", "", $hex);
        if(strlen($hex) == 3) {
            $r = hexdec(substr($hex,0,1).substr($hex,0,1));
            $g = hexdec(substr($hex,1,1).substr($hex,1,1));
            $b = hexdec(substr($hex,2,1).substr($hex,2,1));
        } else {
            $r = hexdec(substr($hex,0,2));
            $g = hexdec(substr($hex,2,2));
            $b = hexdec(substr($hex,4,2));
        }
        return array($r, $g, $b);
    }

    // Dynamic Vector Generator for Quizify Logo (Q + Trophy + Cap)
    function drawQuizifyLogo($x, $y, $scale = 1.0, $isWatermark = false) {
        if ($isWatermark) {
            // Highly muted, transparent tint simulations for crisp watermarking
            $blue = array(242, 246, 255);
            $gold = array(254, 251, 238);
        } else {
            $blue = $this->hex2rgb('#0D6EFD');
            $gold = $this->hex2rgb('#D4AF37');
        }

        // --- Trophy Structure / Base ---
        $this->SetFillColor($blue[0], $blue[1], $blue[2]);
        $this->Rect($x - (10 * $scale), $y + (14 * $scale), 20 * $scale, 4 * $scale, 'F'); // Plinth
        $this->Rect($x - (3 * $scale), $y + (5 * $scale), 6 * $scale, 9 * $scale, 'F');   // Stem

        // Trophy Cup Main Frame
        $this->SetDrawColor($blue[0], $blue[1], $blue[2]);
        $this->SetLineWidth(2.5 * $scale);
        $this->Line($x - (11 * $scale), $y - (8 * $scale), $x - (11 * $scale), $y + (3 * $scale));
        $this->Line($x + (11 * $scale), $y - (8 * $scale), $x + (11 * $scale), $y + (3 * $scale));
        $this->Line($x - (11 * $scale), $y + (3 * $scale), $x + (11 * $scale), $y + (3 * $scale));
        
        // Distinct Q Tail Intersecting Bottom Right
        $this->SetDrawColor($gold[0], $gold[1], $gold[2]);
        $this->SetLineWidth(3.0 * $scale);
        $this->Line($x + (5 * $scale), $y + (2 * $scale), $x + (14 * $scale), $y + (11 * $scale));

        // --- Ceremonial Graduation Cap Accent ---
        $this->SetFillColor($gold[0], $gold[1], $gold[2]);
        $this->SetDrawColor($gold[0], $gold[1], $gold[2]);
        $this->SetLineWidth(0.5 * $scale);
        
        // Render Diamond Mortarboard
        $polyX = array($x, $x + (16 * $scale), $x, $x - (16 * $scale));
        $polyY = array($y - (17 * $scale), $y - (12 * $scale), $y - (7 * $scale), $y - (12 * $scale));
        for ($i = 0; $i < 4; $i++) {
            $next = ($i == 3) ? 0 : $i + 1;
            $this->Line($polyX[$i], $polyY[$i], $polyX[$next], $polyY[$next]);
        }
        
        // Internal Cap Foundation
        $this->SetFillColor($blue[0], $blue[1], $blue[2]);
        $this->Rect($x - (7 * $scale), $y - (10 * $scale), 14 * $scale, 3 * $scale, 'F');
    }

    // Dynamic Vectors for Hand-Drawn Fluid Signatures
    function drawDigitalSignature($x, $y, $signee) {
        $this->SetDrawColor(25, 45, 110); // Professional rich blue ink
        $this->SetLineWidth(0.4);
        
        if (strtolower($signee) == 'arjab') {
            // Elegant sweeping continuous glyph curves for Arjab
            $this->Line($x, $y + 1, $x + 7, $y - 7);
            $this->Line($x + 7, $y - 7, $x + 13, $y + 4);
            $this->Line($x + 13, $y + 4, $x + 21, $y - 4);
            $this->Line($x + 21, $y - 4, $x + 27, $y + 2);
            $this->Line($x + 3, $y + 4, $x + 38, $y - 2); // Dynamic sweeping bottom line
        } else {
            // Scripted loops and sharp geometric ascenders for Yash
            $this->Line($x, $y - 3, $x + 5, $y + 5);
            $this->Line($x + 5, $y + 5, $x + 11, $y - 9);
            $this->Line($x + 11, $y - 9, $x + 16, $y + 3);
            $this->Line($x + 16, $y + 3, $x + 23, $y - 1);
            $this->Line($x + 23, $y - 1, $x + 40, $y + 4); // Trailing text loop line
        }
    }
}

// =================================================================
// INITIALIZE LANDSCAPE A4 DOCUMENT
// =================================================================
$pdf = new QuizifyPremiumCertificate('L', 'mm', 'A4');
$pdf->SetMargins(0, 0, 0);
$pdf->SetAutoPageBreak(false);
$pdf->AddPage();

$w = $pdf->GetPageWidth();  // 297mm
$h = $pdf->GetPageHeight(); // 210mm

// Hex color assignments
$royalBlue = $pdf->hex2rgb('#0D6EFD');
$goldTheme = $pdf->hex2rgb('#D4AF37');

// 1. Solid Clean White Base Canvas
$pdf->SetFillColor(255, 255, 255);
$pdf->Rect(0, 0, $w, $h, 'F');

// 2. Large Central Watermark Logo
$pdf->drawQuizifyLogo($w / 2, $h / 2 + 10, 3.8, true);

// =================================================================
// PREMIUM CORNER & DOUBLE OUTLINE ARCHITECTURE
// =================================================================
// Outer Royal Blue Border Line
$pdf->SetDrawColor($royalBlue[0], $royalBlue[1], $royalBlue[2]);
$pdf->SetLineWidth(1.5);
$pdf->Rect(10, 10, $w - 20, $h - 20);

// Inner Premium Gold Border Line
$pdf->SetDrawColor($goldTheme[0], $goldTheme[1], $goldTheme[2]);
$pdf->SetLineWidth(0.6);
$pdf->Rect(13, 13, $w - 26, $h - 26);

// Intricate Geometric Corner Elements 
$corners = array(
    array(13, 13, 1, 1),        // Top-Left Point
    array($w - 13, 13, -1, 1),  // Top-Right Point
    array(13, $h - 13, 1, -1),  // Bottom-Left Point
    array($w - 13, $h - 13, -1, -1) // Bottom-Right Point
);

foreach ($corners as $c) {
    $pdf->SetDrawColor($goldTheme[0], $goldTheme[1], $goldTheme[2]);
    $pdf->SetLineWidth(1.2);
    // Draw Axis Components
    $pdf->Line($c[0], $c[1], $c[0] + ($c[2] * 16), $c[1]);
    $pdf->Line($c[0], $c[1], $c[0], $c[1] + ($c[3] * 16));
}

// =================================================================
// BRAND HEADER SECTIONS (TOP-LEFT PLATFORM BLOCK)
// =================================================================
$headerX = 25;
$headerY = 28;

// Place Vector Identity Emblem
$pdf->drawQuizifyLogo($headerX, $headerY, 0.75, false);

// Site Name Styling
$pdf->SetTextColor($royalBlue[0], $royalBlue[1], $royalBlue[2]);
$pdf->SetFont('Helvetica', 'B', 15);
$pdf->SetXY($headerX + 14, $headerY - 5);
$pdf->Cell(100, 5, "QUIZIFY", 0, 0, 'L');

// Principal Title Header
$pdf->SetTextColor($royalBlue[0], $royalBlue[1], $royalBlue[2]);
$pdf->SetFont('Times', 'B', 24);
$pdf->SetXY($headerX + 14, $headerY + 1);
$pdf->Cell(200, 8, "CERTIFICATE OF ACHIEVEMENT", 0, 0, 'L');

// Sub-Heading Clarification Note
$pdf->SetTextColor(110, 110, 110);
$pdf->SetFont('Helvetica', 'I', 10);
$pdf->SetXY($headerX + 14, $headerY + 9);
$pdf->Cell(200, 5, "Awarded for Outstanding Performance and Successful Completion", 0, 0, 'L');

// =================================================================
// CENTER RECIPIENT LAYOUT CONTENT
// =================================================================
// Introduction Statement Text
$pdf->SetTextColor(90, 90, 90);
$pdf->SetFont('Helvetica', '', 12);
$pdf->SetY(78);
$pdf->Cell($w, 6, "This Certificate is Proudly Presented To", 0, 1, 'C');

// Prominent Recipient Typography 
$pdf->SetTextColor($goldTheme[0], $goldTheme[1], $goldTheme[2]);
$pdf->SetFont('Times', 'B', 30);
$pdf->SetY(89);
$pdf->Cell($w, 14, $data['user_name'], 0, 1, 'C');

// Ribbon/Rule Divider Line Below Name
$pdf->SetDrawColor($goldTheme[0], $goldTheme[1], $goldTheme[2]);
$pdf->SetLineWidth(0.5);
$pdf->Line($w / 2 - 50, 106, $w / 2 + 50, 106);

// Sub-Context Affirmation String
$pdf->SetTextColor(90, 90, 90);
$pdf->SetFont('Helvetica', '', 12);
$pdf->SetY(112);
$pdf->Cell($w, 6, "For Successfully Completing the Quiz", 0, 1, 'C');

// Title Of Target Finished Quiz
$pdf->SetTextColor($royalBlue[0], $royalBlue[1], $royalBlue[2]);
$pdf->SetFont('Helvetica', 'B', 16);
$pdf->SetY(121);
$pdf->Cell($w, 8, $data['quiz_title'], 0, 1, 'C');

// =================================================================
// VISUAL PREMIUM SCORE METRIC BADGE
// =================================================================
$boxW = 55;
$boxH = 9;
$boxX = ($w / 2) - ($boxW / 2);
$boxY = 135;

// Build Solid Filled Contrast Badge
$pdf->SetFillColor($royalBlue[0], $royalBlue[1], $royalBlue[2]);
$pdf->Rect($boxX, $boxY, $boxW, $boxH, 'F');

// Display Score Values Safely inside Badge Block
$pdf->SetTextColor(255, 255, 255);
$pdf->SetFont('Helvetica', 'B', 11);
$pdf->SetY($boxY + 1.2);
$pdf->Cell($w, 7, "SCORE: " . $data['score'] . "%", 0, 1, 'C');

// =================================================================
// DETAILED ENDORSEMENT SIGNATURE SYSTEM
// =================================================================
$sigLineY = 168;

// --- Left Endorsement: Arjab Jain ---
$leftSigX = 40;
$pdf->drawDigitalSignature($leftSigX + 5, $sigLineY - 6, 'arjab');

$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.3);
$pdf->Line($leftSigX, $sigLineY, $leftSigX + 50, $sigLineY); // Base rule

$pdf->SetTextColor(120, 120, 120);
$pdf->SetFont('Helvetica', '', 8.5);
$pdf->SetXY($leftSigX, $sigLineY + 2);
$pdf->Cell(50, 4, "Quizify Authorized Member", 0, 0, 'C');

$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetXY($leftSigX, $sigLineY + 6);
$pdf->Cell(50, 5, "Arjab Jain", 0, 0, 'C');

$pdf->SetTextColor(120, 120, 120);
$pdf->SetFont('Helvetica', 'I', 8.5);
$pdf->SetXY($leftSigX, $sigLineY + 11);
$pdf->Cell(50, 4, "Technical Lead", 0, 0, 'C');


// --- Right Endorsement: Yash Gupta ---
$rightSigX = $w - 90;
$pdf->drawDigitalSignature($rightSigX + 5, $sigLineY - 6, 'yash');

$pdf->SetDrawColor(200, 200, 200);
$pdf->SetLineWidth(0.3);
$pdf->Line($rightSigX, $sigLineY, $rightSigX + 50, $sigLineY); // Base rule

$pdf->SetTextColor(120, 120, 120);
$pdf->SetFont('Helvetica', '', 8.5);
$pdf->SetXY($rightSigX, $sigLineY + 2);
$pdf->Cell(50, 4, "Quizify Authorized Member", 0, 0, 'C');

$pdf->SetTextColor(50, 50, 50);
$pdf->SetFont('Helvetica', 'B', 10);
$pdf->SetXY($rightSigX, $sigLineY + 6);
$pdf->Cell(50, 5, "Yash Gupta", 0, 0, 'C');

$pdf->SetTextColor(120, 120, 120);
$pdf->SetFont('Helvetica', 'I', 8.5);
$pdf->SetXY($rightSigX, $sigLineY + 11);
$pdf->Cell(50, 4, "Technical Lead", 0, 0, 'C');

// =================================================================
// LOWER METADATA BAR AND SECURE ENCRYPTION FOOTER
// =================================================================
$pdf->SetTextColor(130, 130, 130);
$pdf->SetFont('Courier', '', 9);

// Unique Verification Identifier Left Side
$pdf->SetXY(25, $h - 22);
$pdf->Cell(120, 5, "Certificate No: " . $data['certificate_no'], 0, 0, 'L');

// Timestamp / Issue Date Tracking Right Side
$pdf->SetXY($w - 145, $h - 22);
$pdf->Cell(120, 5, "Issue Date: " . $data['issue_date'], 0, 0, 'R');

// System Integrity Verification Statement Footer
$pdf->SetTextColor(150, 150, 150);
$pdf->SetFont('Helvetica', 'I', 8);
$pdf->SetY($h - 16);
$pdf->Cell($w, 5, "This certificate is digitally verified by Quizify.", 2, 3, 'MIDLLE');

// =================================================================
// SEND TO BROWSER FOR AUTOMATIC FORCE DOWNLOAD ('D')
// =================================================================
$pdf->Output('D', 'Quizify_Certificate_' . $data['certificate_no'] . '.pdf');
exit;
?>