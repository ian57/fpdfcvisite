<html>
<head>
<title>ClasseTableau : sample01_tableau1.php</title>
<style type="text/css">
<!--
.style1 {font-family: Arial, Helvetica, sans-serif}
body {
	font-family: "Courier New", Courier, mono;
	font-size: 16px;
}
-->
</style>
</head>

<body>
<script type="text/javascript" src="/popmenu/menu_src.js"></script>	
<script type="text/javascript" src="/popmenu/menu_style_orange.js"></script>	
<script type="text/javascript" src="/_datamenu.js"></script>	
<br>
<br>
<br>
        <blockquote>
          <p><a href="index.htm"><img src="new4-167.gif" width="16" height="16" border="0"></a> <a href="javascript:alert('Début des exemples');"><img src="fleche_.gif" width="16" height="16" border="0"></a><a href="sample02_tableau_skins.php"><img src="fleche.gif" width="16" height="16" border="0"></a> <span class="titre1 style1"><strong>TABLEAU GENERE PAR UNE BOUCLE </strong></span><br>
              <?php		
		include('classePopup.php');

		$fen = New Popup;
		$fen->popup_new('TITRE1');
		
?>
</p>
<script language="javascript" type="text/javascript">		
<!--

var params = 
{	
	Height : 300, 
	Width : 330, 
	BorderColor : '#E9BDAD', 
	BorderWidth : 4, 
	InnerBorderColor : 'White', 
	OuterBorderColor : '#999999', 
	CloseBoxHeight : 11, 
	CloseBoxSrc : 'img/close11transparent.gif', 
	CloseBoxWidth : 11, 
	ContentColor : '#F9EBE6', 
	ContentNoWrap : 'nowrap', 
	ContentFontSize : 11, 
	TitleBarHeight : 23, 
	TitleBarText : 'ROMEO AND JULIET', 
	TitleColor : '#E9BDAD', 
	TitleFontColor : '#9C0000', 
	StatusBarText : 'William Shakespeare', 
	//StatusBarHeight : 0, 
	StatusColor : '#E9BDAD', 
	StatusFontColor : '#9C0000',
	ResizeBoxSrc : 'img/resize7blue.gif', 
	Id  : 't1'
}
var t1  = new FerantDHTMLWindow(params);
t1.OpenWindow();
var texte = 'Two <b>households</b>, both alike in dignity,<br> In fair Verona, where we lay our scene,<br> From ancient grudge break to new mutiny,<br> Where civil blood makes civil hands unclean.<br> From forth the fatal loins of these two foes<br> A pair of star-cross\'d lovers take their life;<br> Whole misadventured piteous overthrows<br> Do with their death bury their parents\' strife.<br> The fearful passage of their death-mark\'d love,<br> And the continuance of their parents\' rage,<br> Which, but their children\'s end, nought could remove,<br> Is now the two hours\' traffic of our stage;<br> The which if you with patient ears attend,<br> What here shall miss, our toil shall strive to mend.<br> ';
t1.UpdateContentHTML(texte);
		
// -->  
</script>


</blockquote>
</body>
</html>
