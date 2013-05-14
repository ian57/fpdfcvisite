<HTML><HEAD>
	<TITLE>Ferant DHTML Windows</TITLE>
	</HEAD>
	<BODY>
<script language="javascript" src="../inc/FerantLib.js" type="text/javascript"></script>

<SCRIPT LANGUAGE="JavaScript">
// D'autres scripts et des tutoriaux sur http://www.toutjavascript.com
function MM_findObj(n, d) { //v4.01 
  var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) { 
    d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);} 
  if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n]; 
  for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document); 
  if(!x && d.getElementById) x=d.getElementById(n); return x; 
} 

function PeutOnOuvrirPopup(quellecoche) {
	// Vérifie que le cookie "pop1fois" n'est pas présent
	return (GetCookie(quellecoche)==null);
}

function PressionSurCoche(quellecoche) {
	var obj = MM_findObj(quellecoche); 
	if (obj) {
	 	if (obj.checked) {
			NePlusAfficher(quellecoche);
			return false;
		} else {
			ViderCookie(quellecoche);
			return true;
		}
	}
}


function NePlusAfficher(quellecoche) {		
		// Enregistre le cookie pour une durée d'un an
		var pathname=location.pathname;
		var myDomain=pathname.substring(0,pathname.lastIndexOf('/')) +'/';
		var date_exp = new Date();
		date_exp.setTime(date_exp.getTime()+(365*24*3600*1000)); // 1 an
		SetCookie(quellecoche,"noView",date_exp,myDomain);
		// alert(myDomain);
}

function ViderCookie(quellecoche) {
		var pathname=location.pathname;
		var myDomain=pathname.substring(0,pathname.lastIndexOf('/')) +'/';
		var date_exp = new Date();
		date_exp.setTime(date_exp.getTime()-(1000)); // Heure déjà expirée
		SetCookie(quellecoche,"",date_exp,myDomain);
		// alert("Le cookie '"+quellecoche+"' est vidé.\n Vous pouvez recharger la page pour voir le popup...")
}

function SetCookie (name, value) {
	var argv=SetCookie.arguments;
	var argc=SetCookie.arguments.length;
	var expires=(argc > 2) ? argv[2] : null;
	var path=(argc > 3) ? argv[3] : null;
	var domain=(argc > 4) ? argv[4] : null;
	var secure=(argc > 5) ? argv[5] : false;
	document.cookie=name+"="+escape(value)+
		((expires==null) ? "" : ("; expires="+expires.toGMTString()))+
		((path==null) ? "" : ("; path="+path))+
		((domain==null) ? "" : ("; domain="+domain))+
		((secure==true) ? "; secure" : "");
}

function getCookieVal(offset) {
	var endstr=document.cookie.indexOf (";", offset);
	if (endstr==-1)
      		endstr=document.cookie.length;
	return unescape(document.cookie.substring(offset, endstr));
}

function GetCookie (name) {
	var arg=name+"=";
	var alen=arg.length;
	var clen=document.cookie.length;
	var i=0;
	while (i<clen) {
		var j=i+alen;
		if (document.cookie.substring(i, j)==arg)
			return getCookieVal (j);
		i=document.cookie.indexOf(" ",i)+1;
		if (i==0) break;
	}
	return null;
}
</SCRIPT>


<script language="javascript" type="text/javascript">		
<!--
if (!PeutOnOuvrirPopup("t1_checkbox")) {
	checked_YesNo = ' checked';
} else {
	checked_YesNo = '';
}
var params = 
{	
	BorderWidth : 2, 
	ContentColor : '#F9EBE6', 
	ContentHTML : '<br>A dreamer is one who can only find his way by moonlight, and his punishment is that he sees the dawn before the rest of the world. ', 
	ContentPadding : 13, 
	Height : 150, 
	InnerBorderColor : '#cccccc', 
	InnerBorderWidth : 0, 
	OuterBorderColor : '#9C0000',
	ResizeBoxSrc : 'img/resize7blue.gif', 
	ResizeBoxWidth: 7,
	ResizeBoxHeight : 7,  
	StatusBarHeight : 25, 
	TitleBarTextMargin : 10,
	StatusBarHTML : '<form action="#" onclick="PressionSurCoche(\'t1_checkbox\');" method="post"><input type="checkbox" name="t1_checkbox" value="checkbox"'+checked_YesNo+'>&nbsp;Ne plus afficher cette fenêtre</form>', 
	StatusColor : '#F9EBE6', 
	TitleBarText : 'Oscar Wilde', 
	TitleColor : '#9C0000', 
	TitleFontSize : 12, 
	Shadow: true,
	ContentFontColor : '#9C0000',
			CloseBoxSrc : 'img/effacer3.gif', 
	Id  : 't1'
}
var t1  = new FerantDHTMLWindow(params);
if (PeutOnOuvrirPopup("t1_checkbox")) {
	t1.OpenWindow(); // Appel à la gestion de l'affichage du popup
}		
// -->  
</script>

<FORM>
	<INPUT type=button value="Vider le cookie" onClick="ViderCookie('t1_checkbox')"><BR><BR>
	<INPUT type=button value="Recharger la page" onClick="window.location=document.location"><BR><BR>
	<INPUT type=button value="Ouvrir la fenetre quelque soit la valeur du cookie" onClick="t1.StatusBarHTML='ooo';t1.OpenWindow()"><BR>
</FORM>

<?php
	print_r($_COOKIE);
?>
	</BODY>
</HTML>
