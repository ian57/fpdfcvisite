<?php
require('fpdf/fpdf.php');
$pdf = new FPDF();
$pdf->AddPage();
$pdf->SetFont('Arial','B',16);
$pdf->Cell(60,10,'Hello World !');
$pdf->Cell(60,10,'Powered by FPDF.',0,1,'C');
$pdf->SetFont('Symbol','B',25);
$pdf->Cell(80,20,'Made by Yann.',0,1,'C');
$pdf->SetFont('ZapfDingbats','B',25);
$pdf->Cell(140,15,'&é"(-è_çà)=$ù*:;,',0,1,'R');
$pdf->AddFont('Calligrapher','','calligra.php');
$pdf->SetFont('Calligrapher','',35);
$pdf->Cell(100,30,'Changez de police avec FPDF !',0,1,'L');
$pdf->AddFont('Amandine','','Amandine.php');
$pdf->SetFont('Amandine','',20);
$pdf->Write(15,'Bonjour Linux Pratique. Visitez mon site web...');
$pdf->SetTextColor(0,0,255);
$pdf->SetFont('','U');
$pdf->Write(15,'http://www.morere.eu','http://www.morere.eu');
$pdf->Output();
?>
