<?php
////////////////////////////////////////////////////
// PDF_CVisite 
//
// Classe afin d'éditer au format PDF des cartes de visite
// au format Avery ou personnalisé
//
// Yann MORERE 2006
//
// Basée sur la classe PDF_Label 
// Copyright (C) 2003 Laurent PASSEBECQ (LPA)
// Basé sur les fonctions de Steve Dillon : steved@mad.scientist.com
//
// et sur les classes Cell Tags, Vcell, Alpha....
//-------------------------------------------------------------------
// VERSIONS :
// 1.0  : Initial release
// 1.1  : +	: Added unit in the constructor
//        + : Now Positions start @ (1,1).. then the first image @top-left of a page is (1,1)
//        + : Added in the description of a label : 
//				font-size	: defaut char size (can be changed by calling Set_Char_Size(xx);
//				paper-size	: Size of the paper for this sheet (thanx to Al Canton)
//				metric		: type of unit used in this description
//							  You can define your label properties in inches by setting metric to 'in'
//							  and printing in millimiter by setting unit to 'mm' in constructor.
//			  Added some labels :
//				5160, 5161, 5162, 5163,5164 : thanx to Al Canton : acanton@adams-blake.com
//				8600 						: thanx to Kunal Walia : kunal@u.washington.edu
//        + : Added 3mm to the position of labels to avoid errors 
// 1.2  : + : Added Set_Font_Name method
//        = : Bug of positionning
//        = : Set_Font_Size modified -> Now, just modify the size of the font
//        = : Set_Char_Size renamed to Set_Font_Size
////////////////////////////////////////////////////

/**
 * PDF_Label - PDF label editing
 * @package PDF_Label
 * @author Laurent PASSEBECQ <lpasseb@numericable.fr>
 * @copyright 2003 Laurent PASSEBECQ
**/

require_once('fpdf.php');

// Needed by Celltag class
require_once("class.string_tags.php");

class PDF_Cvisite extends FPDF {

	// Propriétés privées
	var $_Avery_Name	= '';				// Nom du format de l'étiquette
	var $_Margin_Left	= 0;				// Marge de gauche de l'étiquette
	var $_Margin_Top	= 0;				// marge en haut de la page avant la première étiquette
	var $_X_Space 		= 0;				// Espace horizontal entre 2 bandes d'étiquettes
	var $_Y_Space 		= 0;				// Espace vertical entre 2 bandes d'étiquettes
	var $_X_Number 		= 0;				// Nombre d'étiquettes sur la largeur de la page
	var $_Y_Number 		= 0;				// Nombre d'étiquettes sur la hauteur de la page
	var $_Width 		= 0;				// Largeur de chaque étiquette
	var $_Height 		= 0;				// Hauteur de chaque étiquette
	var $_Char_Size		= 10;				// Hauteur des caractères
	var $_Line_Height	= 10;				// Hauteur par défaut d'une ligne
	var $_Metric 		= 'mm';				// Type of metric for labels.. Will help to calculate good values
	var $_Metric_Doc 	= 'mm';				// Type of metric for the document
	var $_Font_Name		= 'Arial';			// Name of the font

	var $_COUNTX = 1;
	var $_COUNTY = 1;
	//variable de pdfalpha
    var $extgstates;
	
	// Listing of labels size
	var $_Avery_Labels = array (
		'5160'=>array('name'=>'5160',	'paper-size'=>'letter',	'metric'=>'mm',	'marginLeft'=>1.762,	'marginTop'=>10.7,		'NX'=>3,	'NY'=>10,	'SpaceX'=>3.175,	'SpaceY'=>0,	'width'=>66.675,	'height'=>25.4,		'font-size'=>8),
		'5161'=>array('name'=>'5161',	'paper-size'=>'letter',	'metric'=>'mm',	'marginLeft'=>0.967,	'marginTop'=>10.7,		'NX'=>2,	'NY'=>10,	'SpaceX'=>3.967,	'SpaceY'=>0,	'width'=>101.6,		'height'=>25.4,		'font-size'=>8),
		'5162'=>array('name'=>'5162',	'paper-size'=>'letter',	'metric'=>'mm',	'marginLeft'=>0.97,		'marginTop'=>20.224,	'NX'=>2,	'NY'=>7,	'SpaceX'=>4.762,	'SpaceY'=>0,	'width'=>100.807,	'height'=>35.72,	'font-size'=>8),
		'5163'=>array('name'=>'5163',	'paper-size'=>'letter',	'metric'=>'mm',	'marginLeft'=>1.762,	'marginTop'=>10.7, 		'NX'=>2,	'NY'=>5,	'SpaceX'=>3.175,	'SpaceY'=>0,	'width'=>101.6,		'height'=>50.8,		'font-size'=>8),
		'5164'=>array('name'=>'5164',	'paper-size'=>'letter',	'metric'=>'in',	'marginLeft'=>0.148,	'marginTop'=>0.5, 		'NX'=>2,	'NY'=>3,	'SpaceX'=>0.2031,	'SpaceY'=>0,	'width'=>4.0,		'height'=>3.33,		'font-size'=>12),
		'8600'=>array('name'=>'8600',	'paper-size'=>'letter',	'metric'=>'mm',	'marginLeft'=>7.1, 		'marginTop'=>19, 		'NX'=>3, 	'NY'=>10, 	'SpaceX'=>9.5, 		'SpaceY'=>3.1, 	'width'=>66.6, 		'height'=>25.4,		'font-size'=>8),
		'L7163'=>array('name'=>'L7163',	'paper-size'=>'A4',		'metric'=>'mm',	'marginLeft'=>5,		'marginTop'=>15, 		'NX'=>2,	'NY'=>7,	'SpaceX'=>25,		'SpaceY'=>0,	'width'=>99.1,		'height'=>38.1,		'font-size'=>9)
	);

/////////////////////////////////////////////////////////////
// Fonction RoundRect pour les coin arrondi

    function RoundedRect($x, $y, $w, $h,$r, $style = '')
    {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' or $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2f %.2f m',($x+$r)*$k,($hp-$y)*$k ));
        $xc = $x+$w-$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2f %.2f l', $xc*$k,($hp-$y)*$k ));

        $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
        $xc = $x+$w-$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2f %.2f l',($x+$w)*$k,($hp-$yc)*$k));
        $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
        $xc = $x+$r ;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2f %.2f l',$xc*$k,($hp-($y+$h))*$k));
        $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
        $xc = $x+$r ;
        $yc = $y+$r;
        $this->_out(sprintf('%.2f %.2f l',($x)*$k,($hp-$yc)*$k ));
        $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
    {
        $h = $this->h;
        $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c ', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
//////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////

////////////////////////////////////////////////////////
// fournit une extension de la méthode MultiCell permettant de formater le texte par des balises
////////////////////////////////////////////////////////

var $wt_Current_Tag;
var $wt_FontInfo;//tags font info
var $wt_DataInfo;//parsed string data info
var $wt_DataExtraInfo;//data extra INFO


    function _wt_Reset_Datas(){
        $this->wt_Current_Tag = "";
        $this->wt_DataInfo = array();
        $this->wt_DataExtraInfo = array(
            "LAST_LINE_BR" => "",        //CURRENT LINE BREAK TYPE
            "CURRENT_LINE_BR" => "",    //LAST LINE BREAK TYPE
            "TAB_WIDTH" => 10            //The tab WIDTH IS IN mm
        );

        //if another measure unit is used ... calculate your OWN
        $this->wt_DataExtraInfo["TAB_WIDTH"] *= (72/25.4) / $this->k;
        /*
            $this->wt_FontInfo - do not reset, once read ... is OK!!!
        */
    }//function _wt_Reset_Datas(){

    /**
        Sets current tag to specified style
        @param        $tag - tag name
                    $family - text font family
                    $style - text style
                    $size - text size
                    $color - text color
        @return     nothing
    */
    function SetStyle($tag,$family,$style,$size,$color)
    {

        if ($tag == "ttags") $this->Error (">> ttags << is reserved TAG Name.");
        if ($tag == "") $this->Error ("Empty TAG Name.");

        //use case insensitive tags
        $tag=trim(strtoupper($tag));
        $this->TagStyle[$tag]['family']=trim($family);
        $this->TagStyle[$tag]['style']=trim($style);
        $this->TagStyle[$tag]['size']=trim($size);
        $this->TagStyle[$tag]['color']=trim($color);
    }//function SetStyle


    /**
        Sets current tag style as the current settings
            - if the tag name is not in the tag list then de "DEFAULT" tag is saved.
            This includes a fist call of the function SaveCurrentStyle()
        @param        $tag - tag name
        @return     nothing
    */
    function ApplyStyle($tag){

        //use case insensitive tags
        $tag=trim(strtoupper($tag));

        if ($this->wt_Current_Tag == $tag) return;

        if (($tag == "") || (! isset($this->TagStyle[$tag]))) $tag = "DEFAULT";

        $this->wt_Current_Tag = $tag;

        $style = & $this->TagStyle[$tag];

        if (isset($style)){
            $this->SetFont($style['family'], $style['style'], $style['size']);
            //this is textcolor in FPDF format
            if (isset($style['textcolor_fpdf'])) {
                $this->TextColor = $style['textcolor_fpdf'];
                $this->ColorFlag=($this->FillColor!=$this->TextColor);
            }else
            {
                if ($style['color'] <> ""){//if we have a specified color
                    $temp = explode(",", $style['color']);
                    $this->SetTextColor($temp[0], $temp[1], $temp[2]);
                }//fi
            }
            /**/
        }//isset
    }//function ApplyStyle($tag){

    /**
        Save the current settings as a tag default style under the DEFAUTLT tag name
        @param        none
        @return     nothing
    */
    function SaveCurrentStyle(){
        //*
        $this->TagStyle['DEFAULT']['family'] = $this->FontFamily;;
        $this->TagStyle['DEFAULT']['style'] = $this->FontStyle;
        $this->TagStyle['DEFAULT']['size'] = $this->FontSizePt;
        $this->TagStyle['DEFAULT']['textcolor_fpdf'] = $this->TextColor;
        $this->TagStyle['DEFAULT']['color'] = "";
        /**/
    }//function SaveCurrentStyle

    /**
        Divides $this->wt_DataInfo and returnes a line from this variable
        @param        $w - Width of the text
        @return     $aLine = array() -> contains informations to draw a line
    */
    function MakeLine($w){

        $aDataInfo = & $this->wt_DataInfo;
        $aExtraInfo = & $this->wt_DataExtraInfo;

        //last line break >> current line break
        $aExtraInfo['LAST_LINE_BR'] = $aExtraInfo['CURRENT_LINE_BR'];
        $aExtraInfo['CURRENT_LINE_BR'] = "";

        if($w==0)
            $w=$this->w - $this->rMargin - $this->x;

        $wmax = ($w - 2*$this->cMargin) * 1000;//max width

        $aLine = array();//this will contain the result
        $return_result = false;//if break and return result
        $reset_spaces = false;

        $line_width = 0;//line string width
        $total_chars = 0;//total characters included in the result string
        $space_count = 0;//numer of spaces in the result string
        $fw = & $this->wt_FontInfo;//font info array

        $last_sepch = ""; //last separator character

        foreach ($aDataInfo as $key => $val){

            $s = $val['text'];

            $tag = &$val['tag'];

            $s_lenght=strlen($s);

            #if($s_lenght>0 and $s[$s_lenght-1]=="\n") $s_lenght--;

            $i = 0;//from where is the string remain
            $j = 0;//untill where is the string good to copy -- leave this == 1->> copy at least one character!!!
            $str = "";
            $s_width = 0;    //string width
            $last_sep = -1; //last separator position
            $last_sepwidth = 0;
            $last_sepch_width = 0;
            $ante_last_sep = -1; //ante last separator position
            $spaces = 0;


            //parse the whole string
            while ($i < $s_lenght){
                $c = $s[$i];

                   if($c == "\n"){//Explicit line break
                       $i++; //ignore/skip this caracter
                    $aExtraInfo['CURRENT_LINE_BR'] = "BREAK";
                    $return_result = true;
                    $reset_spaces = true;
                    break;
                }

                //space
                   if($c == " "){
                    $space_count++;//increase the number of spaces
                    $spaces ++;
                }

                //    Font Width / Size Array
                if (!isset($fw[$tag]) || ($tag == "")){
                    //if this font was not used untill now,
                    $this->ApplyStyle($tag);
                    $fw[$tag]['w'] = $this->CurrentFont['cw'];//width
                    $fw[$tag]['s'] = $this->FontSize;//size
                }

                $char_width = $fw[$tag]['w'][$c] * $fw[$tag]['s'];

                //separators
                if(is_int(strpos(" ,.:;",$c))){

                    $ante_last_sep = $last_sep;
                    $ante_last_sepch = $last_sepch;
                    $ante_last_sepwidth = $last_sepwidth;
                    $ante_last_sepch_width = $last_sepch_width;

                    $last_sep = $i;//last separator position
                    $last_sepch = $c;//last separator char
                    $last_sepch_width = $char_width;//last separator char
                    $last_sepwidth = $s_width;

                }

                if ($c == "\t"){
                    $c = $s[$i] = "";
                    $char_width = $aExtraInfo['TAB_WIDTH'] * 1000;
                }


                $line_width += $char_width;


                if($line_width > $wmax){//Automatic line break

                    $aExtraInfo['CURRENT_LINE_BR'] = "AUTO";

                    if ($total_chars == 0) {
                        /* This MEANS that the $w (width) is lower than a char width...
                            Put $i and $j to 1 ... otherwise infinite while*/
                        $i = 1;
                        $j = 1;
                        $return_result = true;//YES RETURN THE RESULT!!!
                        break;
                    }//fi

                    if ($last_sep <> -1){
                        //we have a separator in this tag!!!
                        //untill now there one separator
                        if (($last_sepch == $c) && ($last_sepch != " ") && ($ante_last_sep <> -1)){
                            /*    this is the last character and it is a separator, if it is a space the leave it...
                                Have to jump back to the last separator... even a space
                            */
                            $last_sep = $ante_last_sep;
                            $last_sepch = $ante_last_sepch;
                            $last_sepwidth = $ante_last_sepwidth;
                        }

                        if ($last_sepch == " "){
                            $j = $last_sep;//just ignore the last space (it is at end of line)
                            $i = $last_sep + 1;
                            if ( $spaces > 0 ) $spaces --;
                            $s_width = $last_sepwidth;
                        }else{
                            $j = $last_sep + 1;
                            $i = $last_sep + 1;
                            $s_width = $last_sepwidth + $last_sepch_width;
                        }

                    }elseif(count($aLine) > 0){
                        //we have elements in the last tag!!!!
                        if ($last_sepch == " "){//the last tag ends with a space, have to remove it

                            $temp = & $aLine[ count($aLine)-1 ];

                            if ($temp['text'][strlen($temp['text'])-1] == " "){

                                $temp['text'] = substr($temp['text'], 0, strlen($temp['text']) - 1);
                                $temp['width'] -= $fw[ $temp['tag'] ]['w'][" "] * $fw[ $temp['tag'] ]['s'];
                                $temp['spaces'] --;

                                //imediat return from this function
                                break 2;
                            }else{
                                #die("should not be!!!");
                            }//fi
                        }//fi
                    }//fi else

                    $return_result = true;
                    break;
                }//fi - Auto line break

                //increase the string width ONLY when it is added!!!!
                $s_width += $char_width;

                $i++;
                $j = $i;
                $total_chars ++;
            }//while

            $str = substr($s, 0, $j);

            $sTmpStr = & $aDataInfo[$key]['text'];
            $sTmpStr = substr($sTmpStr, $i, strlen($sTmpStr));

            if (($sTmpStr == "") || ($sTmpStr === FALSE))//empty
                array_shift($aDataInfo);

            if ($val['text'] == $str){
            }

            //we have a partial result
            array_push($aLine, array(
                'text' => $str,
                'tag' => $val['tag'],
                'href' => $val['href'],
                'width' => $s_width,
                'spaces' => $spaces
            ));

            if ($return_result) break;//break this for

        }//foreach

        // Check the first and last tag -> if first and last caracters are " " space remove them!!!"

        if ((count($aLine) > 0) && ($aExtraInfo['LAST_LINE_BR'] == "AUTO")){
            //first tag
            $temp = & $aLine[0];
            if ( (strlen($temp['text']) > 0) && ($temp['text'][0] == " ")){
                $temp['text'] = substr($temp['text'], 1, strlen($temp['text']));
                $temp['width'] -= $fw[ $temp['tag'] ]['w'][" "] * $fw[ $temp['tag'] ]['s'];
                $temp['spaces'] --;
            }

            //last tag
            $temp = & $aLine[count($aLine) - 1];
            if ( (strlen($temp['text'])>0) && ($temp['text'][strlen($temp['text'])-1] == " ")){
                $temp['text'] = substr($temp['text'], 0, strlen($temp['text']) - 1);
                $temp['width'] -= $fw[ $temp['tag'] ]['w'][" "] * $fw[ $temp['tag'] ]['s'];
                $temp['spaces'] --;
            }
        }

        if ($reset_spaces){//this is used in case of a "Explicit Line Break"
            //put all spaces to 0 so in case of "J" align there is no space extension
            for ($k=0; $k< count($aLine); $k++) $aLine[$k]['spaces'] = 0;
        }//fi


        return $aLine;
    }//function MakeLine

    /**
        Draws a MultiCell with TAG recognition parameters
        @param        $w - with of the cell
                    $h - height of the cell
                    $pData - string or data to be printed
                    $border - border
                    $align    - align
                    $fill - fill
                    $pDataIsString - true if $pData is a string
                                   - false if $pData is an array containing lines formatted with $this->MakeLine($w) function
                                        (the false option is used in relation with StringToLines, to avoid double formatting of a string

                    These paramaters are the same and have the same behavior as at Multicell function
        @return     nothing
    */
    function MultiCellTag($w, $h, $pData, $border=0, $align='J', $fill=0, $pDataIsString = true){

        //save the current style settings, this will be the default in case of no style is specified
        $this->SaveCurrentStyle();
        $this->_wt_Reset_Datas();
        
        //if data is string
        if ($pDataIsString === true) $this->DivideByTags($pData);

        $b = $b1 = $b2 = $b3 = '';//borders

        //save the current X position, we will have to jump back!!!!
        $startX = $this -> GetX();

        if($border)
        {
            if($border==1)
            {
                $border = 'LTRB';
                $b1 = 'LRT';//without the bottom
                $b2 = 'LR';//without the top and bottom
                $b3 = 'LRB';//without the top
            }
            else
            {
                $b2='';
                if(is_int(strpos($border,'L')))
                    $b2.='L';
                if(is_int(strpos($border,'R')))
                    $b2.='R';
                $b1=is_int(strpos($border,'T')) ? $b2 . 'T' : $b2;
                $b3=is_int(strpos($border,'B')) ? $b2 . 'B' : $b2;
            }

            //used if there is only one line
            $b = '';
            $b .= is_int(strpos($border,'L')) ? 'L' : "";
            $b .= is_int(strpos($border,'R')) ? 'R' : "";
            $b .= is_int(strpos($border,'T')) ? 'T' : "";
            $b .= is_int(strpos($border,'B')) ? 'B' : "";
        }

        $first_line = true;
        $last_line = false;
        
        if ($pDataIsString === true){
            $last_line = !(count($this->wt_DataInfo) > 0);
        }else {
            $last_line = !(count($pData) > 0);
        }
                                                                      
        while(!$last_line){
            if ($fill == 1){
                //fill in the cell at this point and write after the text without filling
                $this->Cell($w,$h,"",0,0,"",1);
                $this->SetX($startX);//restore the X position
            }

            if ($pDataIsString === true){
                //make a line
                $str_data = $this->MakeLine($w);
                //check for last line
                $last_line = !(count($this->wt_DataInfo) > 0);
            }else {
                //make a line
                $str_data = array_shift($pData);
                //check for last line
                $last_line = !(count($pData) > 0);
            }

            if ($last_line && ($align == "J")){//do not Justify the Last Line
                $align = "L";
            }

            //outputs a line
            $this->PrintLine($w, $h, $str_data, $align);


            //see what border we draw:
            if($first_line && $last_line){
                //we have only 1 line
                $real_brd = $b;
            }elseif($first_line){
                $real_brd = $b1;
            }elseif($last_line){
                $real_brd = $b3;
            }else{
                $real_brd = $b2;
            }

            if ($first_line) $first_line = false;

            //draw the border and jump to the next line
            $this->SetX($startX);//restore the X
            $this->Cell($w,$h,"",$real_brd,2);
        }//while(! $last_line){

        //APPLY THE DEFAULT STYLE
        $this->ApplyStyle("DEFAULT");

        $this->x=$this->lMargin;
    }//function MultiCellExt


    /**
        This method divides the string into the tags and puts the result into wt_DataInfo variable.
        @param        $pStr - string to be printed
        @return     nothing
    */
    
    function DivideByTags($pStr, $return = false){

        $pStr = str_replace("\t", "<ttags>\t</ttags>", $pStr);
        $pStr = str_replace("\r", "", $pStr);

        //initialize the String_TAGS class
        $sWork = new String_TAGS(5);

        //get the string divisions by tags
        $this->wt_DataInfo = $sWork->get_tags($pStr);
        if ($return) return $this->wt_DataInfo;
    }//function DivideByTags($pStr){
    
    /**
        This method parses the current text and return an array that contains the text information for
        each line that will be drawed.
        @param        $w - with of the cell
                    $pStr - String to be parsed
        @return     $aStrLines - array - contains parsed text information.
    */
    function StringToLines($w = 0, $pStr){

        //save the current style settings, this will be the default in case of no style is specified
        $this->SaveCurrentStyle();
        $this->_wt_Reset_Datas();
        
        $this->DivideByTags($pStr);
             
        $last_line = !(count($this->wt_DataInfo) > 0);
        
        $aStrLines = array();

        while (!$last_line){

            //make a line
            $str_data = $this->MakeLine($w);
            array_push($aStrLines, $str_data);

            //check for last line
            $last_line = !(count($this->wt_DataInfo) > 0);
        }//while(! $last_line){

        //APPLY THE DEFAULT STYLE
        $this->ApplyStyle("DEFAULT");

        return $aStrLines;
    }//function StringToLines    

    
    /**
        Draws a line returned from MakeLine function
        @param        $w - with of the cell
                    $h - height of the cell
                    $aTxt - array from MakeLine
                    $align - text align
        @return     nothing
    */
    function PrintLine($w, $h, $aTxt, $align='J'){

        if($w==0)
            $w=$this->w-$this->rMargin - $this->x;

        $wmax = $w; //Maximum width

        $total_width = 0;    //the total width of all strings
        $total_spaces = 0;    //the total number of spaces

        $nr = count($aTxt);//number of elements

        for ($i=0; $i<$nr; $i++){
            $total_width += ($aTxt[$i]['width']/1000);
            $total_spaces += $aTxt[$i]['spaces'];
        }

        //default
        $w_first = $this->cMargin;

        switch($align){
            case 'J':
                if ($total_spaces > 0)
                    $extra_space = ($wmax - 2 * $this->cMargin - $total_width) / $total_spaces;
                else $extra_space = 0;
                break;
            case 'L':
                break;
            case 'C':
                $w_first = ($wmax - $total_width) / 2;
                break;
            case 'R':
                $w_first = $wmax - $total_width - $this->cMargin;;
                break;
        }

        // Output the first Cell
        if ($w_first != 0){
            $this->Cell($w_first, $h, "", 0, 0, "L", 0);
        }

        $last_width = $wmax - $w_first;

        while (list($key, $val) = each($aTxt)) {
            //apply current tag style
            $this->ApplyStyle($val['tag']);

            //If > 0 then we will move the current X Position
            $extra_X = 0;

            //string width
            $width = $this->GetStringWidth($val['text']);
            $width = $val['width'] / 1000;

            if ($width == 0) continue;// No width jump over!!!

            if($align=='J'){
                if ($val['spaces'] < 1) $temp_X = 0;
                else $temp_X = $extra_space;

                $this->ws = $temp_X;

                $this->_out(sprintf('%.3f Tw', $temp_X * $this->k));

                $extra_X = $extra_space * $val['spaces'];//increase the extra_X Space

            }else{
                $this->ws = 0;
                $this->_out('0 Tw');
            }//fi

            //Output the Text/Links
            $this->Cell($width, $h, $val['text'], 0, 0, "C", 0, $val['href']);

            $last_width -= $width;//last column width

            if ($extra_X != 0){
                $this -> SetX($this->GetX() + $extra_X);
                $last_width -= $extra_X;
            }//fi

        }

        // Output the Last Cell
        if ($last_width != 0){
            $this->Cell($last_width, $h, "", 0, 0, "", 0);
        }//fi
    }//function PrintLine(


//////////////////////////////////////////////////////////
//////////////////////////////////////////////////////////


////////////////////////////////////////////////////////
// le support de la transparence
////////////////////////////////////////////////////////

/* constructeur devenu inutile après fusion de la classe
    function AlphaPDF($orientation='P',$unit='mm',$format='A4')
    {
        parent::FPDF($orientation, $unit, $format);
        $this->extgstates = array();
    }
*/
    // alpha: real value from 0 (transparent) to 1 (opaque)
    // bm:    blend mode, one of the following:
    //          Normal, Multiply, Screen, Overlay, Darken, Lighten, ColorDodge, ColorBurn,
    //          HardLight, SoftLight, Difference, Exclusion, Hue, Saturation, Color, Luminosity
    function SetAlpha($alpha, $bm='Normal')
    {
        // set alpha for stroking (CA) and non-stroking (ca) operations
        $gs = $this->AddExtGState(array('ca'=>$alpha, 'CA'=>$alpha, 'BM'=>'/'.$bm));
        $this->SetExtGState($gs);
    }

    function AddExtGState($parms)
    {
        $n = count($this->extgstates)+1;
        $this->extgstates[$n]['parms'] = $parms;
        return $n;
    }

    function SetExtGState($gs)
    {
        $this->_out(sprintf('/GS%d gs', $gs));
    }

    function _enddoc()
    {
        if(!empty($this->extgstates) && $this->PDFVersion<'1.4')
            $this->PDFVersion='1.4';
        parent::_enddoc();
    }

    function _putextgstates()
    {
        for ($i = 1; $i <= count($this->extgstates); $i++)
        {
            $this->_newobj();
            $this->extgstates[$i]['n'] = $this->n;
            $this->_out('<</Type /ExtGState');
            foreach ($this->extgstates[$i]['parms'] as $k=>$v)
                $this->_out('/'.$k.' '.$v);
            $this->_out('>>');
            $this->_out('endobj');
        }
    }

    function _putresourcedict()
    {
        parent::_putresourcedict();
        $this->_out('/ExtGState <<');
        foreach($this->extgstates as $k=>$extgstate)
            $this->_out('/GS'.$k.' '.$extgstate['n'].' 0 R');
        $this->_out('>>');
    }

    function _putresources()
    {
        $this->_putextgstates();
        parent::_putresources();
    }

////////////////////////////////////////////////////////
////////////////////////////////////////////////////////

////////////////////////////////////////////////////////
// Function Image pour gestion canal alpha des images
////////////////////////////////////////////////////////
var $tmpFiles = array();

///////////////////////////////////////////////////////////////////////////////
//                                                                             //
//                               Public methods                                 //
//                                                                              //
///////////////////////////////////////////////////////////////////////////////
function Image($file,$x,$y,$w=0,$h=0,$type='',$link='', $isMask=false, $maskImg=0)
{
    //Put an image on the page
    if(!isset($this->images[$file]))
    {
        //First use of image, get info
        if($type=='')
        {
            $pos=strrpos($file,'.');
            if(!$pos)
                $this->Error('Image file has no extension and no type was specified: '.$file);
            $type=substr($file,$pos+1);
        }
        $type=strtolower($type);
        $mqr=get_magic_quotes_runtime();
 //       set_magic_quotes_runtime(0);
 ini_set("magic_quotes_runtime", 0);
        if($type=='jpg' || $type=='jpeg')
            $info=$this->_parsejpg($file);
        elseif($type=='png'){
            $info=$this->_parsepng($file);
            if($info=='alpha')
                return $this->ImagePngWithAlpha($file,$x,$y,$w,$h,$link);
        }
        else
        {
            //Allow for additional formats
            $mtd='_parse'.$type;
            if(!method_exists($this,$mtd))
                $this->Error('Unsupported image type: '.$type);
            $info=$this->$mtd($file);
        }
        //set_magic_quotes_runtime($mqr);
        ini_set("magic_quotes_runtime", $mqr);
        
        if($isMask){
            if(in_array($file,$this->tmpFiles))
                $info['cs']='DeviceGray'; //hack necessary as GD can't produce gray scale images
            if($info['cs']!='DeviceGray')
                $this->Error('Mask must be a gray scale image');
            if($this->PDFVersion<'1.4')
                $this->PDFVersion='1.4';
        }
        $info['i']=count($this->images)+1;
        if($maskImg>0)
            $info['masked'] = $maskImg;
        $this->images[$file]=$info;
    }
    else
        $info=$this->images[$file];
    //Automatic width and height calculation if needed
    if($w==0 && $h==0)
    {
        //Put image at 72 dpi
        $w=$info['w']/$this->k;
        $h=$info['h']/$this->k;
    }
    if($w==0)
        $w=$h*$info['w']/$info['h'];
    if($h==0)
        $h=$w*$info['h']/$info['w'];
        
    if(!$isMask)
        $this->_out(sprintf('q %.2f 0 0 %.2f %.2f %.2f cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
    if($link)
        $this->Link($x,$y,$w,$h,$link);
        
    return $info['i'];
}

// needs GD 2.x extension
// pixel-wise operation, not very fast
function ImagePngWithAlpha($file,$x,$y,$w=0,$h=0,$link='')
{
    $tmp_alpha = tempnam('.', 'mska');
    $this->tmpFiles[] = $tmp_alpha;
    $tmp_plain = tempnam('.', 'mskp');
    $this->tmpFiles[] = $tmp_plain;

    list($wpx, $hpx) = getimagesize($file);
    $img = imagecreatefrompng($file);
    $alpha_img = imagecreate( $wpx, $hpx );

    // generate gray scale pallete
    for($c=0;$c<256;$c++)
        ImageColorAllocate($alpha_img, $c, $c, $c);

    // extract alpha channel
    $xpx=0;
    while ($xpx<$wpx){
        $ypx = 0;
        while ($ypx<$hpx){
            $color_index = imagecolorat($img, $xpx, $ypx);
            $col = imagecolorsforindex($img, $color_index);
            imagesetpixel($alpha_img, $xpx, $ypx, $this->_gamma( (127-$col['alpha'])*255/127) );
            ++$ypx;
        }
        ++$xpx;
    }

    imagepng($alpha_img, $tmp_alpha);
    imagedestroy($alpha_img);

    // extract image without alpha channel
    $plain_img = imagecreatetruecolor ( $wpx, $hpx );
    imagecopy($plain_img, $img, 0, 0, 0, 0, $wpx, $hpx );
    imagepng($plain_img, $tmp_plain);
    imagedestroy($plain_img);
    
    //first embed mask image (w, h, x, will be ignored)
    $maskImg = $this->Image($tmp_alpha, 0,0,0,0, 'PNG', '', true);
    
    //embed image, masked with previously embedded mask
    $this->Image($tmp_plain,$x,$y,$w,$h,'PNG',$link, false, $maskImg);
}

function Close()
{
    parent::Close();
    // clean up tmp files
    foreach($this->tmpFiles as $tmp)
        @unlink($tmp);
}

///////////////////////////////////////////////////////////////////////////////
//                                                                              //
//                               Private methods                                //
//                                                                              ///////////////////////////////////////////////////////////////////////////////
function _putimages()
{
    $filter=($this->compress) ? '/Filter /FlateDecode ' : '';
    reset($this->images);
    while(list($file,$info)=each($this->images))
    {
        $this->_newobj();
        $this->images[$file]['n']=$this->n;
        $this->_out('<</Type /XObject');
        $this->_out('/Subtype /Image');
        $this->_out('/Width '.$info['w']);
        $this->_out('/Height '.$info['h']);

        if(isset($info['masked']))
            $this->_out('/SMask '.($this->n-1).' 0 R');

        if($info['cs']=='Indexed')
            $this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
        else
        {
            $this->_out('/ColorSpace /'.$info['cs']);
            if($info['cs']=='DeviceCMYK')
                $this->_out('/Decode [1 0 1 0 1 0 1 0]');
        }
        $this->_out('/BitsPerComponent '.$info['bpc']);
        if(isset($info['f']))
            $this->_out('/Filter /'.$info['f']);
        if(isset($info['parms']))
            $this->_out($info['parms']);
        if(isset($info['trns']) && is_array($info['trns']))
        {
            $trns='';
            for($i=0;$i<count($info['trns']);$i++)
                $trns.=$info['trns'][$i].' '.$info['trns'][$i].' ';
            $this->_out('/Mask ['.$trns.']');
        }
        $this->_out('/Length '.strlen($info['data']).'>>');
        $this->_putstream($info['data']);
        unset($this->images[$file]['data']);
        $this->_out('endobj');
        //Palette
        if($info['cs']=='Indexed')
        {
            $this->_newobj();
            $pal=($this->compress) ? gzcompress($info['pal']) : $info['pal'];
            $this->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
            $this->_putstream($pal);
            $this->_out('endobj');
        }
    }
}

// GD seems to use a different gamma, this method is used to correct it again
function _gamma($v){
    return pow ($v/255, 2.2) * 255;
}


/*

function _parsepng($file)
{
	//Extract info from a PNG file
	$f=fopen($file,'rb');
	if(!$f)
		$this->Error('Can\'t open image file: '.$file);
	//Check signature
	if($this->_readstream($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
		$this->Error('Not a PNG file: '.$file);
	//Read header chunk
	$this->_readstream($f,4);
	if($this->_readstream($f,4)!='IHDR')
		$this->Error('Incorrect PNG file: '.$file);
	$w=$this->_readint($f);
	$h=$this->_readint($f);
	$bpc=ord($this->_readstream($f,1));
	if($bpc>8)
		$this->Error('16-bit depth not supported: '.$file);
	$ct=ord($this->_readstream($f,1));
	if($ct==0)
		$colspace='DeviceGray';
	elseif($ct==2)
		$colspace='DeviceRGB';
	elseif($ct==3)
		$colspace='Indexed';
	else
		$this->Error('Alpha channel not supported: '.$file);
	if(ord($this->_readstream($f,1))!=0)
		$this->Error('Unknown compression method: '.$file);
	if(ord($this->_readstream($f,1))!=0)
		$this->Error('Unknown filter method: '.$file);
	if(ord($this->_readstream($f,1))!=0)
		$this->Error('Interlacing not supported: '.$file);
	$this->_readstream($f,4);
	$parms='/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>';
	//Scan chunks looking for palette, transparency and image data
	$pal='';
	$trns='';
	$data='';
	do
	{
		$n=$this->_readint($f);
		$type=$this->_readstream($f,4);
		if($type=='PLTE')
		{
			//Read palette
			$pal=$this->_readstream($f,$n);
			$this->_readstream($f,4);
		}
		elseif($type=='tRNS')
		{
			//Read transparency info
			$t=$this->_readstream($f,$n);
			if($ct==0)
				$trns=array(ord(substr($t,1,1)));
			elseif($ct==2)
				$trns=array(ord(substr($t,1,1)), ord(substr($t,3,1)), ord(substr($t,5,1)));
			else
			{
				$pos=strpos($t,chr(0));
				if($pos!==false)
					$trns=array($pos);
			}
			$this->_readstream($f,4);
		}
		elseif($type=='IDAT')
		{
			//Read image data block
			$data.=$this->_readstream($f,$n);
			$this->_readstream($f,4);
		}
		elseif($type=='IEND')
			break;
		else
			$this->_readstream($f,$n+4);
	}
	while($n);
	if($colspace=='Indexed' && empty($pal))
		$this->Error('Missing palette in '.$file);
	fclose($f);
	return array('w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'FlateDecode', 'parms'=>$parms, 'pal'=>$pal, 'trns'=>$trns, 'data'=>$data);
}

*/


// this method overwriing the original version is only needed to make the Image method support PNGs with alpha channels.
// if you only use the ImagePngWithAlpha method for such PNGs, you can remove it from this script.

function _parsepng($file)
{
    //Extract info from a PNG file
    $f=fopen($file,'rb');
    if(!$f)
        $this->Error('Can\'t open image file: '.$file);
    //Check signature
    if(fread($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
        $this->Error('Not a PNG file: '.$file);
    //Read header chunk
    fread($f,4);
    if(fread($f,4)!='IHDR')
        $this->Error('Incorrect PNG file: '.$file);
	$w=$this->_readint($f);
	$h=$this->_readint($f);        
//    $w=$this->_freadint($f);
//    $h=$this->_freadint($f);
    $bpc=ord(fread($f,1));
    if($bpc>8)
        $this->Error('16-bit depth not supported: '.$file);
    $ct=ord(fread($f,1));
    if($ct==0)
        $colspace='DeviceGray';
    elseif($ct==2)
        $colspace='DeviceRGB';
    elseif($ct==3)
        $colspace='Indexed';
    else {
        fclose($f);      // the only changes are
        return 'alpha';  // made in those 2 lines
    }
    if(ord(fread($f,1))!=0)
        $this->Error('Unknown compression method: '.$file);
    if(ord(fread($f,1))!=0)
        $this->Error('Unknown filter method: '.$file);
    if(ord(fread($f,1))!=0)
        $this->Error('Interlacing not supported: '.$file);
    fread($f,4);
    $parms='/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>';
    //Scan chunks looking for palette, transparency and image data
    $pal='';
    $trns='';
    $data='';
    do
    {
    	$n=$this->_readint($f);
//        $n=$this->_freadint($f);
        $type=fread($f,4);
        if($type=='PLTE')
        {
            //Read palette
            $pal=fread($f,$n);
            fread($f,4);
        }
        elseif($type=='tRNS')
        {
            //Read transparency info
            $t=fread($f,$n);
            if($ct==0)
                $trns=array(ord(substr($t,1,1)));
            elseif($ct==2)
                $trns=array(ord(substr($t,1,1)),ord(substr($t,3,1)),ord(substr($t,5,1)));
            else
            {
                $pos=strpos($t,chr(0));
                if($pos!==false)
                    $trns=array($pos);
            }
            fread($f,4);
        }
        elseif($type=='IDAT')
        {
            //Read image data block
            $data.=fread($f,$n);
            fread($f,4);
        }
        elseif($type=='IEND')
            break;
        else
            fread($f,$n+4);
    }
    while($n);
    if($colspace=='Indexed' && empty($pal))
        $this->Error('Missing palette in '.$file);
    fclose($f);
    return array('w'=>$w,'h'=>$h,'cs'=>$colspace,'bpc'=>$bpc,'f'=>'FlateDecode','parms'=>$parms,'pal'=>$pal,'trns'=>$trns,'data'=>$data);
}

//////////////////////////////////////
// pour mettre du texte à 45° par ex
// vient de la classe invoice
function Rotate($angle,$x=-1,$y=-1)
{
	if($x==-1)
		$x=$this->x;
	if($y==-1)
		$y=$this->y;
	if($this->angle!=0)
		$this->_out('Q');
	$this->angle=$angle;
	if($angle!=0)
	{
		$angle*=M_PI/180;
		$c=cos($angle);
		$s=sin($angle);
		$cx=$x*$this->k;
		$cy=($this->h-$y)*$this->k;
		$this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
	}
}
//////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////

//////////////////////////////////////////////////////////
// Version étendue de Cell
//////////////////////////////////////////////////////////
function VCell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0)
{
    //Output a cell
    $k=$this->k;
    if($this->y+$h>$this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak())
    {
        $x=$this->x;
        $ws=$this->ws;
        if($ws>0)
        {
            $this->ws=0;
            $this->_out('0 Tw');
        }
        $this->AddPage($this->CurOrientation);
        $this->x=$x;
        if($ws>0)
        {
            $this->ws=$ws;
            $this->_out(sprintf('%.3f Tw',$ws*$k));
        }
    }
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $s='';
// begin change Cell function
    if($fill==1 or $border>0)
    {
        if($fill==1)
            $op=($border>0) ? 'B' : 'f';
        else
            $op='S';
        if ($border>1) {
            $s=sprintf(' q %.2f w %.2f %.2f %.2f %.2f re %s Q ',$border,
                        $this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
        }
        else
            $s=sprintf('%.2f %.2f %.2f %.2f re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
    }
    if(is_string($border))
    {
        $x=$this->x;
        $y=$this->y;
        if(is_int(strpos($border,'L')))
            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
        else if(is_int(strpos($border,'l')))
            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
            
        if(is_int(strpos($border,'T')))
            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
        else if(is_int(strpos($border,'t')))
            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
        
        if(is_int(strpos($border,'R')))
            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        else if(is_int(strpos($border,'r')))
            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        
        if(is_int(strpos($border,'B')))
            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        else if(is_int(strpos($border,'b')))
            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
    }
    if(trim($txt)!='')
    {
        $cr=substr_count($txt,"\n");
        if ($cr>0) { // Multi line
            $txts = explode("\n", $txt);
            $lines = count($txts);
            for($l=0;$l<$lines;$l++) {
                $txt=$txts[$l];
                $w_txt=$this->GetStringWidth($txt);
                if ($align=='U')
                    $dy=$this->cMargin+$w_txt;
                elseif($align=='D')
                    $dy=$h-$this->cMargin;
                else
                    $dy=($h+$w_txt)/2;
                $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
                if($this->ColorFlag)
                    $s.='q '.$this->TextColor.' ';
                $s.=sprintf('BT 0 1 -1 0 %.2f %.2f Tm (%s) Tj ET ',
                    ($this->x+.5*$w+(.7+$l-$lines/2)*$this->FontSize)*$k,
                    ($this->h-($this->y+$dy))*$k,$txt);
                if($this->ColorFlag)
                    $s.=' Q ';
            }
        }
        else { // Single line
            $w_txt=$this->GetStringWidth($txt);
            $Tz=100;
            if ($w_txt>$h-2*$this->cMargin) {
                $Tz=($h-2*$this->cMargin)/$w_txt*100;
                $w_txt=$h-2*$this->cMargin;
            }
            if ($align=='U')
                $dy=$this->cMargin+$w_txt;
            elseif($align=='D')
                $dy=$h-$this->cMargin;
            else
                $dy=($h+$w_txt)/2;
            $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
            if($this->ColorFlag)
                $s.='q '.$this->TextColor.' ';
            $s.=sprintf('q BT 0 1 -1 0 %.2f %.2f Tm %.2f Tz (%s) Tj ET Q ',
                        ($this->x+.5*$w+.3*$this->FontSize)*$k,
                        ($this->h-($this->y+$dy))*$k,$Tz,$txt);
            if($this->ColorFlag)
                $s.=' Q ';
        }
    }
// end change Cell function
    if($s)
        $this->_out($s);
    $this->lasth=$h;
    if($ln>0)
    {
        //Go to next line
        $this->y+=$h;
        if($ln==1)
            $this->x=$this->lMargin;
    }
    else
        $this->x+=$w;
}

function Cell($w,$h=0,$txt='',$border=0,$ln=0,$align='',$fill=0,$link='')
{
    //Output a cell
    $k=$this->k;
    if($this->y+$h>$this->PageBreakTrigger and !$this->InFooter and $this->AcceptPageBreak())
    {
        $x=$this->x;
        $ws=$this->ws;
        if($ws>0)
        {
            $this->ws=0;
            $this->_out('0 Tw');
        }
        $this->AddPage($this->CurOrientation);
        $this->x=$x;
        if($ws>0)
        {
            $this->ws=$ws;
            $this->_out(sprintf('%.3f Tw',$ws*$k));
        }
    }
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $s='';
// begin change Cell function 12.08.2003
    if($fill==1 or $border>0)
    {
        if($fill==1)
            $op=($border>0) ? 'B' : 'f';
        else
            $op='S';
        if ($border>1) {
            $s=sprintf(' q %.2f w %.2f %.2f %.2f %.2f re %s Q ',$border,
                $this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
        }
        else
            $s=sprintf('%.2f %.2f %.2f %.2f re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
    }
    if(is_string($border))
    {
        $x=$this->x;
        $y=$this->y;
        if(is_int(strpos($border,'L')))
            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
        else if(is_int(strpos($border,'l')))
            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
            
        if(is_int(strpos($border,'T')))
            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
        else if(is_int(strpos($border,'t')))
            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
        
        if(is_int(strpos($border,'R')))
            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        else if(is_int(strpos($border,'r')))
            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        
        if(is_int(strpos($border,'B')))
            $s.=sprintf('%.2f %.2f m %.2f %.2f l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
        else if(is_int(strpos($border,'b')))
            $s.=sprintf('q 2 w %.2f %.2f m %.2f %.2f l S Q ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
    }
    if (trim($txt)!='') {
        $cr=substr_count($txt,"\n");
        if ($cr>0) { // Multi line
            $txts = explode("\n", $txt);
            $lines = count($txts);
            //$dy=($h-2*$this->cMargin)/$lines;
            for($l=0;$l<$lines;$l++) {
                $txt=$txts[$l];
                $w_txt=$this->GetStringWidth($txt);
                if($align=='R')
                    $dx=$w-$w_txt-$this->cMargin;
                elseif($align=='C')
                    $dx=($w-$w_txt)/2;
                else
                    $dx=$this->cMargin;

                $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
                if($this->ColorFlag)
                    $s.='q '.$this->TextColor.' ';
                $s.=sprintf('BT %.2f %.2f Td (%s) Tj ET ',
                    ($this->x+$dx)*$k,
                    ($this->h-($this->y+.5*$h+(.7+$l-$lines/2)*$this->FontSize))*$k,
                    $txt);
                if($this->underline)
                    $s.=' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
                if($this->ColorFlag)
                    $s.=' Q ';
                if($link)
                    $this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$w_txt,$this->FontSize,$link);
            }
        }
        else { // Single line
            $w_txt=$this->GetStringWidth($txt);
            $Tz=100;
            if ($w_txt>$w-2*$this->cMargin) { // Need compression
                $Tz=($w-2*$this->cMargin)/$w_txt*100;
                $w_txt=$w-2*$this->cMargin;
            }
            if($align=='R')
                $dx=$w-$w_txt-$this->cMargin;
            elseif($align=='C')
                $dx=($w-$w_txt)/2;
            else
                $dx=$this->cMargin;
            $txt=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
            if($this->ColorFlag)
                $s.='q '.$this->TextColor.' ';
            $s.=sprintf('q BT %.2f %.2f Td %.2f Tz (%s) Tj ET Q ',
                        ($this->x+$dx)*$k,
                        ($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,
                        $Tz,$txt);
            if($this->underline)
                $s.=' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
            if($this->ColorFlag)
                $s.=' Q ';
            if($link)
                $this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$w_txt,$this->FontSize,$link);
        }
    }
// end change Cell function 12.08.2003
    if($s)
        $this->_out($s);
    $this->lasth=$h;
    if($ln>0)
    {
        //Go to next line
        $this->y+=$h;
        if($ln==1)
            $this->x=$this->lMargin;
    }
    else
        $this->x+=$w;
}

/////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////

	// convert units (in to mm, mm to in)
	// $src and $dest must be 'in' or 'mm'
	function _Convert_Metric ($value, $src, $dest) {
		if ($src != $dest) {
			$tab['in'] = 39.37008;
			$tab['mm'] = 1000;
			return $value * $tab[$dest] / $tab[$src];
		} else {
			return $value;
		}
	}

	// Give the height for a char size given.
	function _Get_Height_Chars($pt) {
		// Tableau de concordance entre la hauteur des caractères et de l'espacement entre les lignes
		$_Table_Hauteur_Chars = array(6=>2, 7=>2.5, 8=>3, 9=>4, 10=>5, 11=>6, 12=>7, 13=>8, 14=>9, 15=>10);
		if (in_array($pt, array_keys($_Table_Hauteur_Chars))) {
			return $_Table_Hauteur_Chars[$pt];
		} else {
			return 100; // There is a prob..
		}
	}

	function _Set_Format($format) {
		$this->_Metric 		= $format['metric'];
		$this->_Avery_Name 	= $format['name'];
		$this->_Margin_Left	= $this->_Convert_Metric ($format['marginLeft'], $this->_Metric, $this->_Metric_Doc);
		$this->_Margin_Top	= $this->_Convert_Metric ($format['marginTop'], $this->_Metric, $this->_Metric_Doc);
		$this->_X_Space 	= $this->_Convert_Metric ($format['SpaceX'], $this->_Metric, $this->_Metric_Doc);
		$this->_Y_Space 	= $this->_Convert_Metric ($format['SpaceY'], $this->_Metric, $this->_Metric_Doc);
		$this->_X_Number 	= $format['NX'];
		$this->_Y_Number 	= $format['NY'];
		$this->_Width 		= $this->_Convert_Metric ($format['width'], $this->_Metric, $this->_Metric_Doc);
		$this->_Height	 	= $this->_Convert_Metric ($format['height'], $this->_Metric, $this->_Metric_Doc);
		$this->Set_Font_Size($format['font-size']);
	}

	function PDF_CVisite ($format, $unit='mm', $posX=1, $posY=1) {
		//récupéré du constructeur de PDFalpha
		$this->extgstates = array();
		if (is_array($format)) {
			// Si c'est un format personnel alors on maj les valeurs
			$Tformat = $format;
		} else {
			// Si c'est un format avery on stocke le nom de ce format selon la norme Avery. 
			// Permettra d'aller récupérer les valeurs dans le tableau _Avery_Labels
			$Tformat = $this->_Avery_Labels[$format];
		}

		parent::FPDF('P', $Tformat['metric'], $Tformat['paper-size']);
		$this->_Set_Format($Tformat);
		$this->Set_Font_Name('Arial');
		$this->SetMargins(0,0); 
		$this->SetAutoPageBreak(false); 

		$this->_Metric_Doc = $unit;
		// Permet de commencer l'impression à l'étiquette désirée dans le cas où la page a déjà servi
		if ($posX > 1) $posX--; else $posX=0;
		if ($posY > 1) $posY--; else $posY=0;
		if ($posX >=  $this->_X_Number) $posX =  $this->_X_Number-1;
		if ($posY >=  $this->_Y_Number) $posY =  $this->_Y_Number-1;
		$this->_COUNTX = $posX;
		$this->_COUNTY = $posY;
	}

	// Méthode qui permet de modifier la taille des caractères
	// Cela modifiera aussi l'espace entre chaque ligne
	function Set_Font_Size($pt) {
		if ($pt > 3) {
			$this->_Char_Size = $pt;
			$this->_Line_Height = $this->_Get_Height_Chars($pt);
			$this->SetFontSize($this->_Char_Size);
		}
	}

	// Method to change font name
	function Set_Font_Name($fontname) {
		if ($fontname != '') {
			$this->_Font_Name = $fontname;
			$this->SetFont($this->_Font_Name);
		}
	}

	// On imprime une étiqette
	function Add_PDF_CVisite($texte,$telephone,$fax,$portable,$mail,$web,$qrcode_web,$traitcoupe,$long_traitcoupe=5 ,$encadre, $type_encadre,$image_fond,$file_image_fond,$alpha,$logo,$file_logo) {
		// We are in a new page, then we must add a page
		if (($this->_COUNTX ==0) and ($this->_COUNTY==0)) {
			$this->AddPage();
		}

		$_PosX = $this->_Margin_Left+($this->_COUNTX*($this->_Width+$this->_X_Space));
		$_PosY = $this->_Margin_Top+($this->_COUNTY*($this->_Height+$this->_Y_Space));
		$this->SetXY($_PosX+3, $_PosY+3);

		if ($traitcoupe)
			{
			//seulement si colonne 1 et pas d'espacement en X

			if (($this->_COUNTX==0) || ($this->_X_Space!=0))
			$this->Line($_PosX, $_PosY, $_PosX - $long_traitcoupe, $_PosY);// horiz sup gauche
			//seulement si ligne 1 et pas d'espacement en Y
			if (($this->_COUNTY==0) || ($this->_Y_Space!=0))
			$this->Line($_PosX, $_PosY, $_PosX , $_PosY-$long_traitcoupe); // vert sup gauche

			//seulement si colonne 1 et et pas d'espacement en X
			if (($this->_COUNTX==0) || ($this->_X_Space!=0))
			$this->Line($_PosX, $_PosY+$this->_Height, $_PosX - $long_traitcoupe, $_PosY+$this->_Height); //horiz inf gauche
			//seulement si ligne 1 et pas d'espacement en Y

			if (($this->_COUNTY == $this->_Y_Number-1) || ($this->_Y_Space!=0))
			$this->Line($_PosX, $_PosY+$this->_Height, $_PosX , $_PosY+$this->_Height+$long_traitcoupe); //vert inf gauche

			//seulement sur la dernière colonne et et pas d'espacement en X
			if (($this->_COUNTX == $this->_X_Number-1) || ($this->_X_Space!=0))
			$this->Line($_PosX+$this->_Width, $_PosY, $_PosX+$this->_Width+$long_traitcoupe , $_PosY); // horiz sup droit

			//seulement sur la dernière ligne et et pas d'espacement en Y
			if (($this->_COUNTY == 0) || ($this->_Y_Space!=0))
			$this->Line($_PosX+$this->_Width, $_PosY, $_PosX+$this->_Width , $_PosY-$long_traitcoupe); // vert sup droit

			//seulement sur la dernière colonne et et pas d'espacement en X
			if (($this->_COUNTX == $this->_X_Number-1) || ($this->_X_Space!=0))
			$this->Line($_PosX+$this->_Width, $_PosY+$this->_Height , $_PosX+$this->_Width+$long_traitcoupe , $_PosY+$this->_Height); // horiz inf droit

			//seulement sur la dernière ligne et et pas d'espacement en Y
			if (($this->_COUNTY == $this->_Y_Number-1) || ($this->_Y_Space!=0))
			$this->Line($_PosX+$this->_Width, $_PosY+$this->_Height, $_PosX+$this->_Width , $_PosY+$this->_Height + $long_traitcoupe); // vert inf droit

			}

		if ($encadre==1)
		{
		switch ($type_encadre)
			{
			case "rond":
				$this->RoundedRect($_PosX, $_PosY, $this->_Width, $this->_Height,5, $style = '');

			break;
			case "carre":
			default:
				$this->Rect($_PosX, $_PosY, $this->_Width, $this->_Height,$style = '');
			}
		}

		// affiche une image en transparence sur le fond à $alpha * 100 %
		if ($image_fond==1)
		{
		$this->SetAlpha($alpha);
		$this->Image($file_image_fond,$_PosX, $_PosY, $this->_Width, $this->_Height);
		//plus de transparence pour le texte
		$this->SetAlpha(1);
		}

		if ($logo)
		{
		$this->SetXY($_PosX,$_PosY);
		$this->SetAlpha(1);
		$this->Image($file_logo,$_PosX + 2 , $_PosY + 2 , 24, 24);
		$this->SetStyle("nom","helvetica","B",14,"0,0,0");
		$this->SetStyle("prof","helvetica","I",12,"0,0,0");
		$this->SetStyle("prof2","helvetica","",12,"0,0,0");
		$this->SetStyle("adr","helvetica","",10,"0,0,0");

		$this->SetXY($_PosX + 20 ,$_PosY+3);
		$this->MultiCellTag($this->_Width - 20, $this->_Line_Height+1, $texte, 0, "C", 0); 

		}
		else
		{
		$this->SetStyle("nom","helvetica","B",14,"0,0,0");
		$this->SetStyle("prof","helvetica","I",12,"0,0,0");	
		$this->SetStyle("prof2","helvetica","",12,"0,0,0");
		$this->SetStyle("adr","helvetica","",10,"0,0,0");

		$this->SetXY($_PosX,$_PosY+5);
		$this->MultiCellTag($this->_Width, $this->_Line_Height, $texte, 0, "C", 0); 
		}
		$_PosY = $_PosY+1;
		$this->SetLineWidth(0.5);
		$this->Line($_PosX+2, $_PosY+($this->_Height)/2, $_PosX+$this->_Width-2, $_PosY+($this->_Height)/2);
		$this->SetLineWidth(0.25);

		if ($telephone!="")
		{
		$this->Image("fpdf/img/phone_black.png",$_PosX+5, $_PosY+($this->_Height)/2+1.5, 4, 4);
		$this->SetXY($_PosX+9,$_PosY+($this->_Height)/2+0);
		$this->Cell($this->_Width/2-15,8," ".$telephone);
		}

		if ($fax!="")
		{
		$this->Image("fpdf/img/fax_black.png",$_PosX+10+$this->_Width/2-5, $_PosY+($this->_Height)/2+1.5, 4, 4);
		$this->SetXY($_PosX+14+$this->_Width/2-5,$_PosY+($this->_Height)/2+0);
		$this->Cell($this->_Width/2-15,8," ".$fax);
		}

		if ($portable!="")
		{
		$this->Image("fpdf/img/mobile_black.png",$_PosX+5, $_PosY+($this->_Height)/2+7.5, 4, 4);
		$this->SetXY($_PosX+9,$_PosY+($this->_Height)/2+6);
		$this->Cell($this->_Width/2-15,8," ".$portable);
		}

		if ($mail!="")
		{
		$this->Image("fpdf/img/mail_black.png",$_PosX+5, $_PosY+($this->_Height)/2+13.5, 4, 4);
		$this->SetXY($_PosX+9,$_PosY+($this->_Height)/2+12);
		$this->Cell($this->_Width-15,8," ".$mail);
		}

		if ($web!="")
		{
		$this->Image("fpdf/img/web_black.png",$_PosX+5, $_PosY+($this->_Height)/2+19.5, 4, 4);
		$this->SetXY($_PosX+9,$_PosY+($this->_Height)/2+18);
		$this->Cell($this->_Width-15,8," ".$web);
		if ($qrcode_web)
			{
			$a = new QR($web,0);
			$qrcode_filename = utf8_encode('qrcode/qr_code')."_".sprintf("%05s", mt_rand(1,99999)).".png";
			file_put_contents($qrcode_filename,$a->image(4));
			//put the qrcode into the card
			$this->SetXY($_PosX,$_PosY);
			//$this->SetAlpha(1);
			$this->Image($qrcode_filename,$_PosX + 62 , $_PosY + 34 , 18, 18);
			}
		
		}
		$this->_COUNTY++;

		if ($this->_COUNTY == $this->_Y_Number) {
			// Si on est en bas de page, on remonte le 'curseur' de position
			$this->_COUNTX++;
			$this->_COUNTY=0;
		}

		if ($this->_COUNTX == $this->_X_Number) {
			// Si on est en bout de page, alors on repart sur une nouvelle page
			$this->_COUNTX=0;
			$this->_COUNTY=0;
		}
	}

}
?>
