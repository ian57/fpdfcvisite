<html>
<head>
<title>Choisir une photo/image</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="shortcut icon" href="fpdfcvisite.ico">
<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<BODY> 
<?php
	include('include/classeForms.php');		
	$f = New Forms;

    $f->frm_Init();
	$f->frm_InitPalette(4);

	$f->frm_uploader( array(	'target'      => 'images/',
								'maxfilesize' => 2048*1024,
								'delete'      => true,
								'space'       => '_',
								'filter'      => true,
								"extensions" => "GIF|PNG|JPG|JPEG",
//								'overwrite' => true,
//								'width' => '150px',
							) 
					 );
?>
</body>
</html>
