/*----------------------------------------------------------------------------\
|        Extension classePopup pour la gestion des fenetres par cookie        |
\----------------------------------------------------------------------------*/


function cPopupCookie(cookiename,cookievalue) {

	this.nomcoche = '';
	this.cookiename  = cookiename;
	this.cookievalue = cookievalue;
	this.cookielife  = 365;
	// L'identifiant du cookie de la fenetre se compose du prefixe+valeur

}

cPopupCookie.prototype.MM_findObj = function (n, d) {
	// function MM_findObj(n, d) { //v4.01 
	var p,i,x;  if(!d) d=document; if((p=n.indexOf("?"))>0&&parent.frames.length) { 
	d=parent.frames[n.substring(p+1)].document; n=n.substring(0,p);} 
	if(!(x=d[n])&&d.all) x=d.all[n]; for (i=0;!x&&i<d.forms.length;i++) x=d.forms[i][n]; 
	for(i=0;!x&&d.layers&&i<d.layers.length;i++) x=MM_findObj(n,d.layers[i].document); 
	if(!x && d.getElementById) x=d.getElementById(n); return x; 
} 

cPopupCookie.prototype.PressOnCheckBox = function (checkboxname) {
	var obj = this.MM_findObj(checkboxname); 
	this.nomcoche = checkboxname;
	if (obj) {
	 	if (obj.checked) {
			this.DontShow();
			return false;
		} else {
			this.EraseCookie();
			return true;
		}
	}
}

cPopupCookie.prototype.DontShow = function () {
		// Enregistre le cookie pour une durée de X jours
		var pathname=location.pathname;
		var myDomain=pathname.substring(0,pathname.lastIndexOf('/')) +'/';
		var date_exp = new Date();
		date_exp.setTime(date_exp.getTime()+(this.cookielife*24*3600*1000)); // 1 an
		SetCookie(this.cookiename,this.cookievalue,date_exp,myDomain);
		// alert(myDomain);
}

cPopupCookie.prototype.EraseCookie = function () {
		var pathname=location.pathname;
		var myDomain=pathname.substring(0,pathname.lastIndexOf('/')) +'/';
		var date_exp = new Date();
		date_exp.setTime(date_exp.getTime()-(1000)); // Heure déjà expirée
		SetCookie(this.cookiename,"",date_exp,myDomain);
		// alert("Le cookie '"+quellecoche+"' est vidé.\n Vous pouvez recharger la page pour voir le popup...")
}

cPopupCookie.prototype.TestBeforeOpen = function () {
	// Vérifie que le cookie n'est pas présent
	return (GetCookie(this.cookiename)==null);
}


//---------------------------------------------------------------------------------------




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






