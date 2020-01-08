<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Nablafire_Customize_Font_Autogen {

	public function __construct($utils_path, $utils_uri){
        // This is the first class invoked. It is passed to all other nablafire-fontgen 
        // It will hold the path and uri to fontgen directory so we can reference later.
		
		$this->fontgen_path = $utils_path . 'font-control/fontgen/';  
		$this->fontgen_uri  = $utils_uri  . 'font-control/fontgen/'; 

		// Get the JSON object which contains the fonts and their variants. Note that file
		// local contents is defined in functions.php. This replaces file_get_contents and 
		// uses an output buffer: ob_start(); include($_file); $_data .= ob_get_contents();
		// ob_end_clean(); return $_data;
        $fonts_json   = file_local_contents( $this->fontgen_path .'js/json/nablafire-google-fonts.json');
   	    $this->fonts  = (array)json_decode( $fonts_json, true );
		if($this->fonts === null){echo "JSON Error. Check Options Table"; die();}
	}

	// Return fontgen paths
	public function get_fontgen_path(){return $this->fontgen_path;}
	public function get_fontgen_uri() {return $this->fontgen_uri;}

	public function get_variants($_){
		return ( array_key_exists($_, $this->fonts) ? $this->fonts[$_]['variants'] : false );
	}	
	
	public function get_category($_){
		return ( array_key_exists($_, $this->fonts) ? $this->fonts[$_]['category'] : false );
	}

	public function get_fontlist(  ){return array_keys($this->fonts);}

	public function link_single($_font, $_variant){
		$link  = "<link rel=\"stylesheet\" href=\"'//fonts.googleapis.com/css?family='";
		$link .= str_replace(' ', '+', $this->fonts[$_font]).':';
		$link .= $_variant. "\"";
		return $link;
	}

	public function link_array($_fonts){
		$link  = "<link rel=\"stylesheet\" href=\"//fonts.googleapis.com/css?family=";
		
		foreach ($_fonts as $font => $_variants) {
			$link .= str_replace(' ', '+', $font).':';
			foreach ($_variants as $_ => $v) {$link .= $v.",";}
			$link = substr($link, 0, -1)."|";
		}
		$link = substr($link, 0, -1)."\">";
		return $link;
	}

	public function enqueue_fonts($_fonts) {	
	    $enqueue = array();
		// Merge down all fonts into a single array of the unique fonts
		foreach ($_fonts as $key => $_) {
			if(!in_array($_['font_fam'],array_keys( $enqueue))) {
				$enqueue[$_['font_fam']] = array($_['font_var']);
			}
			else {
				if(!in_array($_['font_var'], $enqueue[$_['font_fam']])){
					array_push($enqueue[$_['font_fam']], $_['font_var']);
		    	}
		    }    
		}
		// Build single linkstring and echo into header
		echo $this->link_array($enqueue);
	}

	public function css_fontfamily($_font){
		$css = "font-family:'".$_font."',".$this->get_category($_font).";";
		return $css;
	}

	public function css_fontstyle($_variant){
		$css = ''; // 'regular' returns empty string
		if( preg_match("/\d{3}/" , $_variant, $_ )){$css .= "font-weight:".$_[0].";";} 
		if( preg_match("/italic/", $_variant, $_ )){$css .= "font-style:italic;";}
		return $css;	
	}

}