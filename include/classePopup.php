<?php	

// version 1.03 du 18/12/2006

require_once('_classePath.php');

// Chargement du code de couleur par défaut du site
require_once('_classeSkin.php');

// PARAMETRAGE :
DEFINE('CHEMINRESSOURCES_PP',INCLUDEPATH.'classePopup/');

# Popup :
# ------------------------------------------
#   ->popup_new()            :	Définition d'une nouvelle fenetre
#   ->popup_skin()           :	Définition du theme de couleur
#   ->popup_style()          :	Définition du style de la prochaine fenetre
#   ->popup_cookie()         :	Gestion de l'apparition des fenetres par cookie (apparition 1 seule fois, selection "ne plus afficher")
#   ->popup_cascade()        :	Fixe le point de depart et le "PAS" d'un positionnement en cascade


DEFINE('OUVRIR_FENETRE_UNE_FOIS',  1);
DEFINE('NE_PLUS_AFFICHER',         2);
DEFINE('VALEUR_COOKIE',     'noView');

class PopupWindows
{
	var $pp_deja_lance  = false;
	var $pp_cpt = 0;
	var $popup_style = 1;
	var $prefixobjet = "";
	
	var $popup_start_x = -1;
	var $popup_start_y = -1;
	var $popup_step    = 50;
	
	var $cookie_pp  = false;
	var $cookie_prefixe = "";
	var $cookie_domaine = "";
	var $cookie_mode_defaut = 1;
	var $cookie_mode = -1;
	var $cookie_idfenetre = -1;
	var $numskin = -1;
	var $skin_origine = "LA VALEUR PAR DEFAUT DE LA CLASSE";

	# FONCTIONS PUBLICS 
	function popup_new( $params=array(),  $attrib=array() )
	{
	
		// RECUPERATION DES PARAMETRES DE BASE
		$this->popup_title = "";
		if (!empty($params['title'])) {
			$this->popup_title = addslashes($params['title']);
		}
		$this->popup_content = "";
		if (!empty($params['content'])) {
			// ELIMINATION DES SAUTS DE LIGNE QUI PERTURBENT LE JAVASCRIPT
			$this->popup_content =  preg_replace("/[\n,\x0A,\x0D,\x0C]/","",$params['content']);
			$this->popup_content = addslashes($this->popup_content);
		}
		$this->popup_status = "";
		if (!empty($params['status'])) {
			$this->popup_status = addslashes($params['status']);
		}
		$this->cookie_mode = -1;	
		if (!empty($params['cookiemode'])) {
			$this->cookie_mode = $params['cookiemode'];
		}
		$this->cookie_idfenetre = -1;	
		if (!empty($params['id'])) {
			$this->cookie_idfenetre = $params['id'];
		}
		$this->noloadifcookie = false;	
		if (!empty($params['noloadifcookie'])) {
			$this->noloadifcookie = $params['noloadifcookie'];
		}
		
		
		
		// SI LES COOKIES ONT ETE DEFINI
		if ($this->cookie_pp) {
			// SI LE MODE "COOKIE" N'EST PAS SPECIFIE ALORS ON PREND LE MODE PAR DEFAUT
			if ($this->cookie_mode==-1) {
				$this->cookie_mode=$this->cookie_mode_defaut;
			}
		} else {		
			$this->cookie_mode=-1;
		}

		
		// ON AFFICHE LE CODE JS QU'UNE SEULE FOIS ET AVANT LA 1ERE FENETRE
		if (!$this->pp_deja_lance) {
			$this->pp_deja_lance = true;
			$this->popup_js_init();
		}

		$this->attrib_in = attrib;
		$this->attrib_out = array();

		/* ORDRE DE PRIORITE DES COULEURS DU THEME EST LE SUIVANT :
		   --------------------------------------------------------
		   
		   0) L'APPEL A LA FONCTION popup_skin()
		   1) LA VARIABLE DE SESSION 'DEFAULT_SKIN'
		   2) LE COOKIE 'DEFAULT_SKIN'
		   3) LA FONCTION frm_InitPalette DEFINIE DANS LE CODE
		   4) LA CONSTANTE 'DEFAULT_SKIN' DANS _classeSkin.php
		   
		*/		
		if ( $this->numskin == -1 ) {
			if ( isset($_SESSION['DEFAULT_SKIN']) ) {
				$this->numskin = $_SESSION['DEFAULT_SKIN'];
				$this->skin_origine="SESSION";
			} elseif ( isset($_COOKIE['DEFAULT_SKIN'])) {
				$this->numskin = $_COOKIE['DEFAULT_SKIN'];
				$this->skin_origine="COOKIE";
			} elseif ( defined('DEFAULT_SKIN') ) {
				$this->numskin = DEFAULT_SKIN;
				$this->skin_origine="LA CONSTANTE 'DEFAULT_SKIN' DANS _classeSkin.php";
			}
		}
		$this->popup_skin_init();
		// UN FOIS LE THEME DEFINI ON PEUT DEFINIR LE STYLE DE FENETRE
		$this->popup_defstyle();

		// SI DES PARAMETRES ONT ETE PASSE ON LES AJOUTE AU STYLE PRE-DEFINI
		if ( count($attrib)>0 ) {
			foreach ( $attrib as $valeur => $libelle) {
				$this->attrib_out[$valeur] = $libelle;
			}
		}
		// SI PAS DE MESSAGE POUR LA BARRE D'ETAT ELLE EST REDUITE A ZERO
		if ( empty($this->popup_status) && $this->cookie_mode!=NE_PLUS_AFFICHER) {
			$this->attrib_out['StatusColor'] = $this->couleurchampnormal;
			$this->attrib_out['InnerBorderColor'] = $this->couleurchampnormal;
		}
		// SI LA CASCADE A ETE DEFINIE
		if ($this->popup_start_x != -1) {
			$this->attrib_out['Left'] = $this->popup_start_x + $this->popup_step*$this->pp_cpt;
			$this->attrib_out['Top']  = $this->popup_start_y + $this->popup_step*$this->pp_cpt;
		}
		$this->nomobjjs = "classePopup_".$this->pp_cpt;
		$this->nomcookie = $this->prefixobjet.'_'.$this->cookie_idfenetre;

		$this->popup_output();
		$this->pp_cpt++;

	}

	function popup_style( $styledef=1 )
	{
		$this->popup_style = $styledef;
	}

	// DEFINITION DU COMPORTEMENT 
	function popup_cookie( $cook_prefixe="", $cook_domaine="", $cook_mode_defaut=1 )
	{
		// SANS PREFIXE PAS DE COOKIE !
		if (empty($cook_prefixe)) {
			$this->cookie_pp   = false;
			print "// ATTENTION LES COOKIES N'ONT PAS DE PREFIXE ET SONT DONC DESACTIVES\n";
		} else {
			$this->cookie_pp   = true;
		}
		$this->cookie_mode_defaut = $cook_mode_defaut;	
		if ( !empty($cook_prefixe) )  {
			$this->cookie_prefixe = $cook_prefixe;
			$this->prefixobjet    = $cook_prefixe;
		}
		if ( !empty($cook_domaine) )  $this->cookie_domaine = "";		
	}
	
	function popup_skin($numero=-1) {
		// INITIALISE LE NUMERO DU SKIN
		$this->numskin  = $numero;
	}

	function popup_cascade($px=50,$py=50,$step_xy=30) {
		// INITIALISE LE POINT DE DEPART
		$this->popup_start_x = $px;
		$this->popup_start_y = $py;
		$this->popup_step    = $step_xy;
	}

	function popup_opencloseall($nomfonctionopen='Popup_OpenAll',$nomfonctionclose='Popup_CloseAll') {
		// GENERATION D'UNE FONCTION JAVASCRIPT DESTINEE A FERMER TOUTES LES FENETRES D'UN SEUL COUP
		print "\n<script language=\"javascript\" type=\"text/javascript\">\n<!--\n";
		print "\t// FONCTIONS GENEREES AUTOMATIQUEMENT POUR OUVRIR OU FERMER D'UN SEUL COUP TOUTES LES FENETRES\n";
		if (!empty($nomfonctionopen) && ($this->pp_cpt>1)) {
			print "\tfunction ".$nomfonctionopen."() {\n\t\t";
			for ($i=0;$i<$this->pp_cpt;$i++) {
				print 'classePopup_'.$i.".OpenWindow(); ";
			}
			print "\n\t}\n";
		}
		if (!empty($nomfonctionopen) && ($this->pp_cpt>1)) {
			print "\tfunction ".$nomfonctionclose."() {\n\t\t";
			for ($i=0;$i<$this->pp_cpt;$i++) {
				print 'classePopup_'.$i.".CloseWindow(); ";
			}
			print "\n\t}\n";
		}

		print "\n// -->\n</script>\n";	

	}


	# FONCTIONS PRIVEES --------------------------------------------------------------------------------
	
	function popup_output() {

		print "\n<script language=\"javascript\" type=\"text/javascript\">\n<!--\n";	
		if ($this->cookie_pp) {
			if ($this->cookie_idfenetre==-1) {
				print "// ATTENTION : LES COOKIES ONT ETE DEFINI MAIS NE S'APPLIQUERONT PAS A CETTE FENETRE SON id N'A PAS ETE DEFINI\n";
			} else {
				print "// L'ID DE LA FENETRE ETANT ".$this->cookie_idfenetre.", LE COOKIE DE REFERENCE SE NOMMERA \"".$this->nomcookie."\"\n";
			}
		} else {
			print "// CETTE FENETRE NE GERE PAS LES COOKIES\n";
		}
		if ($this->cookie_mode!=-1) {
			if ($this->cookie_idfenetre!=-1) {
				if ($this->cookie_mode==2) {
					if ( empty($this->popup_status) ) $this->popup_status = 'Ne plus afficher cette fenêtre';
					$checkedlib = (isset($_COOKIE[$this->nomcookie]) ) ? " checked" : "";
					$this->popup_status = "<form onclick=\"".$this->nomcookie.".PressOnCheckBox(\'".$this->nomcookie."_checkbox\');\" method=\"post\">&nbsp;<input type=\"checkbox\" name=\"".$this->nomcookie."_checkbox\" value=\"noView\"".$checkedlib.">&nbsp;" . $this->popup_status. '</form>';
				}
				print "// CREATION DE L'OBJET QUI GERE LES COOKIES\n";
				print "var ".$this->nomcookie." = new cPopupCookie('".$this->nomcookie."','".VALEUR_COOKIE."');\n\n";
			}
		}
		if ($this->noloadifcookie) {
			if ( isset($_COOKIE[$this->nomcookie]) ) {
				print "// LA FENETRE ID=".$this->cookie_idfenetre." N'EST PAS DEFINIE CAR UN COOKIE EXISTE\n";
				print "// -->\n</script>\n";	
				return;
			}
		}


		print "// LE THEME DE COULEUR DE LA FENETRE CI-DESSOUS EST DEFINI PAR : ".$this->skin_origine."\n";
		print "var attrib = {\n";
		// SORTIE DE TOUS LES PARAMETRES ENTRE QUOTE OU NON (NUMERIQUE OU BOOLEEN)
        foreach ( $this->attrib_out as $valeur => $libelle) {
			print "\t$valeur : ";	
			if ( is_int($libelle) || $libelle=='true') {
				print $libelle.",\n";
			} else {
				print "'".$libelle."',\n";
			}	
		}
		print "\tTitleBarText  : '".$this->popup_title."',\n";
		print "\tContentHTML   : '".$this->popup_content."',\n";
		print "\tStatusBarHTML : '".$this->popup_status."',\n";
		print "\tId : '".$this->nomobjjs."'\n";
		print "}\n";
		print "var ".$this->nomobjjs." = new FerantDHTMLWindow(attrib);\n";
		if ($this->cookie_mode!=-1) {
			print "if (".$this->nomcookie.".TestBeforeOpen()) {\n";
			print "\t".$this->nomobjjs.".OpenWindow();\n";
			// EN MODE "UNE SEULE FOIS" ON ECRIT LE COOKIE JUSTE APRES AVOIR OUVERT LA FENETRE
			if ($this->cookie_mode==1) {
				print "\t".$this->nomcookie.".DontShow();\n";
			}
			print "}\n";
		} else {
			print $this->nomobjjs.".OpenWindow();\n";
		}
		print "// -->\n</script>\n";	
	}


	// INITIALISATION DE LA TABLE EN SORTIE DE PARAMETRES POUR OBTENIR UN STYLE PRE-DEFINI
	function popup_defstyle() {
		switch ($this->popup_style) {
			case 2:
				$this->attrib_out = array( 
					'Height' => 150,
					'Width'  => 300,
					'MinHeight' => 150,
					'MinWidth'  => 300,
					'BorderColor'  => $this->couleurchamperreur,
					'BorderWidth'  => 2,
					'InnerBorderColor'  => 'white',
					'OuterBorderColor'  => $this->couleurtitre,
					'CloseBoxHeight'  => 11,
					'CloseBoxWidth'  => 11,
					'CloseBoxSrc'  => CHEMINRESSOURCES_PP."inc/close_transparent.gif",
					'ContentColor'  => $this->couleurchampnormal,
					//'ContentNoWrap'  => 'nowrap',
					'ContentFontSize'  => 11,
					'ContentPadding'  => 13,
					'TitleBarHeight'  => 23,
					'TitleColor'  => $this->couleurtitre,
					'TitleFontColor'  => 'white',
					'TitleFontSize'  => 12,
					'StatusColor'  => $this->couleurchampobligatoire,
					'StatusBarHeight'  => 17,
					'StatusBarAlign' => 'left',
					'ResizeBoxSrc'  => CHEMINRESSOURCES_PP."inc/resize_blue.gif",
					'ResizeBoxWidth'  => 7,
					'ResizeBoxHeight'  => 7,
					'Shadow'  => 'true'
				);
				break;
				
			default:
				$this->attrib_out = array( 
					'Height' => 150,
					'Width'  => 300,
					'MinHeight' => 150,
					'MinWidth'  => 300,
					'BorderColor'  => $this->couleurchamperreur,
					'BorderWidth'  => 4,
					'InnerBorderColor'  => 'White',
					'OuterBorderColor'  => '#999999',
					'CloseBoxHeight'  => 11,
					'CloseBoxWidth'  => 11,
					'CloseBoxSrc'  => CHEMINRESSOURCES_PP."inc/close_transparent.gif",
					'ContentColor'  => $this->couleurchampnormal,
					//'ContentNoWrap'  => 'nowrap',
					'ContentFontSize'  => 11,
					'TitleBarHeight'  => 23,
					'TitleColor'  => $this->couleurchamperreur,
					'TitleFontColor'  => $this->couleurtitre,
					'StatusColor'  => $this->couleurchamperreur,
					'ResizeBoxSrc'  => CHEMINRESSOURCES_PP."inc/resize_white.gif",
					'StatusFontColor'  => $this->couleurtitre,
					'StatusBarAlign' => 'left'
				);
				break;

		}

	}
	



	function popup_js_init() {	
				if ($this->codeenvoye) return;
				print "\n<!------------------------------ CODE JAVASCRIPT EXTERNE necessaire pour les fenetres POPUP ------------------------->\n";
				print "<script language=\"javascript\" type=\"text/javascript\" src=\"".CHEMINRESSOURCES_PP."inc/FerantLib.js\"></script>\n\n";
				if ($this->cookie_pp) {
					print "<script language=\"javascript\" src=\"".CHEMINRESSOURCES_PP."classePopupCookie.js\"></script>\n";
				}

	}


	// INITIALISE UNE PALETTE PRE-DEFINIE
	function popup_skin_init() {
		// DU + CLAIR AU + FONCE
		switch ($this->numskin) {
			// PALETTE BLEUE
			case 1:
				$this->couleurchampnormal      = "#E8F3FD";
				$this->couleurchampobligatoire = "#C1DEF9";   
				$this->couleurchamperreur      = "#9CCCF8"; 
				$this->couleurtitre            = "#146DB6";
				break;

			// PALETTE GRISE
			case 2:
				$this->couleurchampnormal      = "#EBEBEB";
				$this->couleurchampobligatoire = "#DDDDDD";   
				$this->couleurchamperreur      = "#CECECE"; 
				$this->couleurtitre            = "#333333";
				break;
				
			// PALETTE JAUNE
			case 3:
				$this->couleurchampnormal      = "#FFFFCC";
				$this->couleurchampobligatoire = "#F7EAAE";   
				$this->couleurchamperreur      = "#F0DC7B"; 
				$this->couleurtitre            = "#7C690E";
				break;

			// PALETTE VERTE
			case 4:
				$this->couleurchampnormal      = "#EAFFE1";
				$this->couleurchampobligatoire = "#ACFF8C";   
				$this->couleurchamperreur      = "#66CC00"; 
				$this->couleurtitre            = "#009900";
				break;

			// PALETTE ORANGE
			case 5:
				$this->couleurchampnormal      = "#FFE1C4";
				$this->couleurchampobligatoire = "#FFD5AA";   
				$this->couleurchamperreur      = "#FFC58A"; 
				$this->couleurtitre            = "#400000";
				break;
								
			// PALETTE ROUGE PAR DEFAUT
			default:
				$this->couleurchampnormal      = "#FAF0ED";
				$this->couleurchampobligatoire = "#F5DED6";   
				$this->couleurchamperreur      = "#E9BDAD";   // UTILISE POUR LES CHAMPS EN ERREUR
				$this->couleurtitre            = "#9C0000";
		}
	}


} // Fin de la classe POPUP


?>
