<html>
<head>
<title>Choisir une icone</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="shortcut icon" href="fpdfcvisite.ico">
<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<BODY> 
<?php
	include('include/classeForms.php');		
	$f = New Forms;
    $f->frm_Init();
	$f->frm_icone_popup_called();
	$f->frm_InitPalette(4);
?>
</body>
</html>
