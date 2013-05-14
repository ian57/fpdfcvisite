<?php
require('qrcode.php');
$a = new QR('Bonjour le monde!',0);
	//Image Output
	//header('Content-Type: image/gif');echo $a->image(4);
file_put_contents('img/bonjour.png',$a->image(4));
	//Text Output
	//echo $a->text(true);//Console (white on black)
	//echo $a->text(false);//Document (black on white)
?>
