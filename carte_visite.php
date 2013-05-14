<?php
	session_start();
	include("include/classeForms.php");
	include("include/classePopup.php"); 
	include("include/qrcode.php");
	
	$f = New Forms;
	$f->frm_Init(false,"250px");
	//$f->frm_Protection();
	definition_des_champs();
	$ret = $f->frm_Aiguiller();

	switch ( $ret ) {
	// MODIF 1ER APPEL 	#############################################################
	case "A0" :
		$generation = 0;
		$f->frm_LibBoutons("Générer PDF","Quitter","Rétablir");
	break;

	// MODIF RE-ENTRANT #############################################################
	case "A1" :
		$generation = 1;
		$f->frm_ChampsRecopier();
		$f->frm_LibBoutons("Générer PDF","Quitter","Rétablir");
	break;

	default:
		header('location: carte_visite.php');
	break;

}

//include("theme_inc.php");
$f->frm_InitPalette(4);
$f->frm_ActiverBtnValider();
//appliquer_theme_sans_menu(4);
?>

<html>
<head>
<title>Configuration de la génération de la carte de visite</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link rel="shortcut icon" href="fpdfcvisite.ico">
<link href="css/style.css" rel="stylesheet" type="text/css">

</head>
<body > 
<br>
<blockquote>
<?php
	print "<span class=titre1>Génération de cartes de visite</span>";
	$f->frm_Ouvrir();
	$pw = New PopupWindows; //	création d'un nouvel objet "fenetre"
	$pw->popup_cascade(600,50,25); //permet de fixer la position du postit
	$pw->popup_skin(5);	//force le theme de couleurs
	$pw->popup_style(1); // force le style
	$pw->popup_new( array(	'title' => "Conseil : Format des images",
								'content' => "Le logo doit être une image <b>carrée</b> : dimension conseillée <b>120x120 pixels</b><br>
								L'image de fond doit être <b>rectangulaire</b> pour éviter les déformations (ratio L/H=1.63) : dimension conseillée <b>900x550 pixels</b>",
								'status' => "pour l'onglet Génération de carte",
								 ),
						array());
	//$pw2 = New PopupWindows; //	création d'un nouvel objet "fenetre"
	$pw->popup_cascade(600,210,0);
	$pw->popup_skin(0);	//force le theme de couleurs
	$pw->popup_style(1); // force le style
	$pw->popup_new( array(	'title' => "Attention!!!",
				'content' => "<b>L'utilisation des images ralentit considérablement la génération des cartes de visites. Ne soyez pas surpris!</b>",
				'status' => "PHP est lent!",
				 ),
			array());
	$f->frm_Fermer();
	
	if ($generation == 1)
		creation_carte();
?>
</blockquote>
</body>
</html>
<?php 
function creation_carte()
{
	define("FPDF_FONTPATH","fpdf/font/");
	require("fpdf/fpdf.php");
	require_once("fpdf/PDF_CVisite.php");

	$prenom = utf8_decode($_POST["PRENOM"]);
	$nom = utf8_decode($_POST["NOM"]);
	$profession1 = utf8_decode($_POST["PROFESSION_1"]);
	$profession2 = utf8_decode($_POST["PROFESSION_2"]);
	$adresse = utf8_decode($_POST["ADRESSE"]);
	$codepostal = utf8_decode($_POST["CP"]);
	$ville = utf8_decode($_POST["VILLE"]);
	$telephone = utf8_decode($_POST["TELEPHONE"]);
	$portable = utf8_decode($_POST["PORTABLE"]);
	$fax = utf8_decode($_POST["FAX"]);
	$mail = utf8_decode($_POST["EMAIL"]);
	$web = utf8_decode($_POST["WEB"]);

	$nb_carte = $_POST["NB_CARTE_VISITE"];
	$coupe_on = $_POST["TRAIT_COUPE_ON"];
	$largeur_coupe = $_POST["LARGEUR_TRAIT_COUPE"];
	$entourage_on = $_POST["ENTOURAGE_CARTE_ON"];
	$type_entourage = $_POST["TYPE_ENTOURAGE"];
	$logo_on = 	$_POST["LOGO_ON"];
	$file_logo = $_POST["LOGO"];
	$img_fond_on = $_POST["IMG_FOND_ON"];		
	$img_fond_transparence = 1-($_POST["IMG_FOND_TRANSPARENCE"]/100);
	$file_img_fond = $_POST["IMG_FOND"];
	$qrcode_web = $_POST["QRCODE_WEB_ON"];

	$pdf = new PDF_CVisite(array(	'name'=>'cvisite',	
		'paper-size'=>'A4',
		'metric'=>'mm',
		'marginLeft'=>10, 	//marge superieure
		'marginTop'=>10,	//marge inférieure
 		'NX'=>2,			//nombre de colonne
		'NY'=>5,			//nombre de ligne
		'SpaceX'=>0,		//espace horiz entre les cartes
		'SpaceY'=>0,		//epsace vert entre les cartes
		'width'=>90,		//largeur de la carte
		'height'=>55,		//hauteur de la carte
		'font-size'=>9),	//taille par défaut de la police
		 'mm', 1, 1);		//1,1 veut dire que l'on commence à) générer les cartes de la première lignes première colonnes

	$pdf->Open();
	// On imprime les étiquettes
	for($i=1;$i<=$nb_carte;$i++)
	if ($profession2 != "")
	{
	$pdf->Add_PDF_CVisite("<nom>".$prenom." ".$nom."</nom>
<prof>".$profession1."</prof>
<prof2>".$profession2."</prof2>
<adr>".$adresse."
".$codepostal." ".$ville."</adr>",$telephone,$fax,$portable,$mail,$web,$qrcode_web,$coupe_on,$largeur_coupe,$entourage_on,$type_entourage,$img_fond_on,$file_img_fond,$img_fond_transparence,$logo_on,$file_logo);
	}
	else
	{
$pdf->Add_PDF_CVisite("<nom>".$prenom." ".$nom."</nom>
<prof>".$profession1."</prof>
<adr>".$adresse."
".$codepostal." ".$ville."</adr>",$telephone,$fax,$portable,$mail,$web,$qrcode_web,$coupe_on,$largeur_coupe,$entourage_on,$type_entourage,$img_fond_on,$file_img_fond,$img_fond_transparence,$logo_on,$file_logo);
	}
	$pdf->Close();
	$filename = utf8_encode($prenom)."_".utf8_encode($nom)."_".sprintf("%05s", mt_rand(1,99999))."_carte_visite.pdf";
	$pdf->Output("pdfs/".$filename,"F");
	$url = "pdfs/".$filename;
	
//$pdf->Output("pdfs/".utf8_encode($prenom)."_".utf8_encode($nom)."_carte_visite.pdf","F");
//	$url = "pdfs/".utf8_encode($prenom)."_".utf8_encode($nom)."_carte_visite.pdf";
//	$filename = utf8_encode($prenom)."_".utf8_encode($nom)."_carte_visite.pdf";
	
		print "<br>
		<table width=\"525px\">
		<tr><td>
		<center>
		<span class=titre1><a href=\"".$url."\" target=\"_new\">Téléchargez votre carte de visite</a></span></center>
		</td></tr></table>";
}

// SECTION DE DEFINITION DES OBJETS CHAMPS
function definition_des_champs() {
	global $f;
	// ATTENTION NE PAS OUBLIER DE "GLOBALISER" TOUTES LES VARIABLES NECESSAIRES A CETTE FONCTION
	
	$tableau_nb_carte = array(	"1" => "1",
							"10" => "10", 
							"20" => "20", 
							"30" => "30",
							"40" => "40",
							"50" => "50");

	$tableau_entourage = array(	"carre" => "Coins carré", 
							"rond" => "Coins arrondis");


	$tableau_trait_coupe = array(	"1" => "1",
							"2" => "2", 
							"3" => "3",
							"4" => "4",
							"5" => "5");



	//Definition d'un onglet
	$f->frm_OngletDefinir( array("width" => "525px", "height" => "470px","default" => "Paramètres Personnels" ) );

		
	///////////////////////////////////////////////////////////////////////
	///// ONGLET PERE /////////////////////////////////////////////////////
	///////////////////////////////////////////////////////////////////////
	$f->frm_SautLignes();
	$f->frm_OngletNouveau('Paramètres Personnels');
	$f->frm_SautLignes();

	$f->frm_ObjetChampTexte("PRENOM", array( "label" => "Prénom",
											 "width"  => "250px",
						                                         "attrib" => "R",
											 "default" => "Jean",
											 "help" => "Saisir votre prénom")
											 );

	$f->frm_ObjetChampTexte("NOM", array( "label" => "Nom",
											 "width"  => "250px",
	                                         					"attrib" => "R",
											 "default" => "Dupond",
											 "help" => "Saisir votre nom")
											 );

	$f->frm_ObjetChampTexte("PROFESSION_1", array( "label" => "Profession",
											 "width"  => "250px",
	                                         					"attrib" => "R",
											 "default" => "Assistant Professor",
											 "help" => "Saisir votre profession")
											 );
	$f->frm_ObjetChampTexte("PROFESSION_2", array( "label" => "",
											 "width"  => "250px",
	                                         					//"attrib" => "U",
											 "default" => "LCOMS - Lorraine University",
											 
											 "help" => "Saisir votre profession")
											 );
	$f->frm_SautLignes();

	$f->frm_ObjetChampTexte("ADRESSE", array( "label" => "Adresse",
											 "width"  => "250px",
	                                         					"attrib" => "R",
											 "default" => "7, rue Marconi",
											 "help" => "Saisir votre adresse")
											 );

	$f->frm_ObjetChampTexte("CP", array( "label" => "Code Postal",
											 "width"  => "80px",
	                                         					"attrib" => "R",
											 "default" => "F57000",
											 "mask" => "F#####",
											 "help" => "Saisir votre code postal")
											 );

	$f->frm_ObjetChampTexte("VILLE", array( "label" => "Ville",
	                                         					"attrib" => "R",
											 "default" => "Ville",
											 "help" => "Saisir la ville")
											 );

	$f->frm_SautLignes();

	$f->frm_ObjetChampTexte("TELEPHONE",   
									array( "attrib" => "R",
									       "label"  => "Téléphone",
									       "width"  => "120px",
									       "help"   => "Téléphone",
									       "default" => "+33 (0)1 02 03 04 05",
										   "mask"   => "+## (#)# ## ## ## ##")
										);

	$f->frm_ObjetChampTexte("PORTABLE",   
									array( "attrib" => "U",
									       "label"  => "Portable",
									       "width"  => "120px",
									       "help"   => "Portable",
									       "default" => "+33 (0)6 07 08 09 10",
										   "mask"   => "+## (#)# ## ## ## ##")
										);


	$f->frm_ObjetChampTexte("FAX",   
									array( "attrib" => "U",
									       "label"  => "Fax",
									       "width"  => "120px",
									       "help"   => "Fax",
									       "default" => "+33 (0)1 02 03 04 05",
										   "mask"   => "+## (#)# ## ## ## ##")
										);

	$f->frm_ObjetChampTexte("EMAIL",   
									array( "attrib" => "M",
									       "label"  => "Email",
									       "width"  => "275px",
									       "default" => "prenom.nom@univ-lorraine.fr",
									       "help"   => "Email",)
										);

	$f->frm_ObjetChampTexte("WEB",   
									array( "attrib" => "L",
									       "label"  => "Site Web",
									       "width"  => "275",
									       "help"   => "Site web ",
									       "default" => "www.myhome.com")
										);
				
	$f->frm_OngletNouveau('Génération carte de visite');


	$f->frm_ObjetListe("NB_CARTE_VISITE",   
									array( "attrib"  => "R",
										   "title"	=> "-- Choisir un nombre --",
									       "label"   => "Nombre de cartes (10 par pages)",
									       "default" => "1",
									       "help"    => "Choisir le nombre de cartes",
									       "width"   => "100px",)
									, $tableau_nb_carte
									);
	// DEFINITION D'UN CHAMP CASE A COCHER
	$f->frm_ObjetCoche("TRAIT_COUPE_ON",   
									array( "label"    => "Afficher les traits de découpe",
									       "title"    => "Oui/Non",
									       "default"  => "1",
									       "help"     => "Cocher pour Oui, Décocher pour Non",
									       "valueon"  => "1",
									       "valueoff" => "0",
										"activation" => array("LARGEUR_TRAIT_COUPE") )
									);
	$f->frm_ObjetListe("LARGEUR_TRAIT_COUPE",   
									array( "attrib"  => "",
										   "title"	=> "-- Choisir un nombre --",
									       "label"   => "Largeur du trait de coupe en mm",
									       "default" => "1",
									       "help"    => "Choisir la largeur du trait de coupe",
									       "width"   => "100px",)
									, $tableau_trait_coupe
									);

	$f->frm_SautLignes();

	// DEFINITION D'UN CHAMP CASE A COCHER
	$f->frm_ObjetCoche("ENTOURAGE_CARTE_ON",   
									array( "label"    => "Afficher le contour de la carte",
									       "title"    => "Oui/Non",
									       "default"  => "1",
									       "help"     => "Cocher pour Oui, Décocher pour Non",
									       "valueon"  => "1",
									       "valueoff" => "0",
										   "activation" => array("TYPE_ENTOURAGE") )
									);
	$f->frm_ObjetListe("TYPE_ENTOURAGE",   
									array( "attrib"  => "",
										   "title"	=> "-- Choisir un nombre --",
									       "label"   => "Type de contour",
									       "default" => "carre",
									       "help"    => "Choisir le type de tour",
									       "width"   => "100px",)
									, $tableau_entourage
									);

	$f->frm_SautLignes();

	// DEFINITION D'UN CHAMP CASE A COCHER
	$f->frm_ObjetCoche("LOGO_ON",   
									array( "label"    => "Afficher le logo sur la carte de visite",
									       "title"    => "Oui/Non",
									       "default"  => "1",
									       "help"     => "Cocher pour Oui, Décocher pour Non",
									       "valueon"  => "1",
									       "valueoff" => "0",
   										   "activation" => array("LOGO") )
										);


	// DEFINITION D'UN CHAMP SELECTEUR D'ICONES
	$f->frm_ObjetChampIcone("LOGO",   
									array( "label"     => "Sélectionnez le logo de la carte de visite",
									       "winwidth"  => "800",
									       "winheight" => "600",
										   "width" => "64",
										   "height" => "64",
									       "url"       => "icones_popup.php",
									       "path"      => "logos/",
									       "default"   => "logos/cliquer2.png",
										   "help"   => "Sélectionnez une image png")
										);
								
	 $f->frm_ObjetUploader("UPLOAD_LOGO_CARTE",       array(
										"attrib" => "",
										"label" => "Télécharger une photo/image",
										"url" => "upload_logo_called.php",
										"default" => "",
										"title" => "Télécharger une photo",
										"extensions" => "GIF|PNG|JPG|JPEG",
										"overwrite" => true,
//										"delete" => true,
										"filter" => true,
										"prefix" => "logo_".sprintf("%05s", mt_rand(1,99999)).'_',
//										"multifiles" => true,
//										"multifilesmax" => -1,
										"target" => 'logos/',
										"preview" => true,
										"help" => "Télécharger des photos/images",
										"width" => "150px",
										"size" => "4" )
										  );

	// DEFINITION D'UN CHAMP CASE A COCHER
	$f->frm_ObjetCoche("IMG_FOND_ON",   		array( "label"    => "Afficher l'image de fond sur la carte de visite",
									       "title"    => "Oui/Non",
									       "default"  => "1",
									       "help"     => "Cocher pour Oui, Décocher pour Non",
									       "valueon"  => "1",
									       "valueoff" => "0",
   										"activation" => array("IMG_FOND_TRANSPARENCE","IMG_FOND") )
										);

	$f->frm_ObjetSlider("IMG_FOND_TRANSPARENCE",	array(	"label" => "Transparence image de fond en % ",
									"orientation" => "H",
									"width" => "150px",
									"mini"=> "0",
									"maxi"=>"100",
									"default" => "0",
									"size" => "3",
									"help" => "choisir la transparence de l'image de fond")
									);

	// DEFINITION D'UN CHAMP SELECTEUR D'ICONES
	$f->frm_ObjetChampIcone("IMG_FOND",   array( "label"     => "Sélectionnez l'image du fond de la carte de visite",
									       "winwidth"  => "800",
									       "winheight" => "600",
										"width" => "64",
										"height" => "64",
									       "url"       => "icones_popup.php",
									       "path"      => "images/",
									       "default"   => "images/cliquer2.png",
										   "help"   => "Sélectionnez une image png")
										);
	 $f->frm_ObjetUploader("UPLOAD_IMAGE_CARTE",       array(
										"attrib" => "",
										"label" => "Télécharger une photo/image",
										"url" => "upload_image_called.php",
										"default" => "",
										"title" => "Télécharger une photo",
										"extensions" => "GIF|PNG|JPG|JPEG",
										"overwrite" => true,
//										"delete" => true,
										"filter" => true,
										"prefix" => "image_".sprintf("%05s", mt_rand(1,99999)).'_',
//										"multifiles" => true,
//										"multifilesmax" => -1,
										"target" => 'images/',
										"preview" => true,
										"help" => "Télécharger des photos/images",
										"width" => "150px",
										"size" => "4" )
										  );
										  
	$f->frm_ObjetCoche("QRCODE_WEB_ON",   
									array( "label"    => "Afficher le QR code du site web",
									       "title"    => "Oui/Non",
									       "default"  => "1",
									       "help"     => "Cocher pour Oui, Décocher pour Non",
									       "valueon"  => "1",
									       "valueoff" => "0")
									);
										  

	}
?>
