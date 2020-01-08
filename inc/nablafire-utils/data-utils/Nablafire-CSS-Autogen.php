<?php 

if ( class_exists( 'Nablafire_CSS_Autogen' ) ) {return;}

class Nablafire_CSS_Autogen {

	public $indent = false; 
	function __construct(){
		;
	}
	
	// Set indent (for @media formatting)
	function set_indent($bool){
		$this->indent = ($bool) ? true : false;
	}

	// Begin general rule
	function begin_rule($target, $id=''){
		return (($this->indent) ? "\t" : "") . $target.$id."{\n";
	}

	// Add general rule
	function add_rule($property, $value){
		return (($this->indent) ? "\t" : "") . "\t".$property.": ".$value.";\n";
	}

	// Write literal CSS (font control)
	function _literal($property){
		if($property != ''){
			return (($this->indent) ? "\t" : "") . "\t".$property."\n";
		}
		else{
			return '';
		} 
	}

	// End rule
	function end_rule() {
		return (($this->indent) ? "\t" : "") . "}\n";
	}

	// Begin media rule
	function begin_media($target){
		$this->indent = true; 
		return '@media ' . $target . "{\n";
	}

	// End media rule
	function end_media(){
		$this->indent = false;
		return "}\n";
	}

	// Add Comment
	function comment($target){
		return "\n/* ". $target ." */\n";
	}

	// Array rule
	function begin_array_rule($targets){
		$css = '';
		foreach ($targets as $_ => $target) {
			$css .= (($this->indent) ? "\t" : "") . $target . ",\n";
		}
		return substr($css, 0, -2) . " {\n";
	}

	// Function to add and correctly format background image
	function add_background_image($image){ 
		return (($this->indent) ? "\t" : "") . "\t" . 'background-image' . ": url(\"" . $image . "\")" . ";\n";
	}

	// Functions to automate prepend to broser specific rules 
	function browser_rules($property, $value){
		$prefix = array('-webkit-', '-moz-', '-o-','-ms-', ''); $css='';
		foreach ($prefix as $_){$css .= (($this->indent) ? "\t" : "") ."\t".$_.$property.": ".$value.";\n";}
		return $css;
	}

	// Horrendously ugly function for generating keyframe CSS
	function browser_keyframes($keyframes, $animation){
		$prefix = array('-webkit-', '-moz-', '-o-','-ms-', ''); $css='';
		foreach ($prefix as $_) {
			$css .= $this->begin_rule('@'.$_.'keyframes '.$animation);
			foreach ($keyframes as $key => $property){ 
				$___  = $property[1] ? (';'. $_ . $property[1] . ';') : '';
				$css .= "\t".$key.'{'.$property[0].$___."}\n";
			}$css .= $this->end_rule();
		}return $css;
	}

	// Minify
	function minify($css) {
		$css = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $css);
		$css = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $css);
		$css = str_replace('{ ', '{', $css);
		$css = str_replace(' }', '}', $css);
		$css = str_replace('; ', ';', $css);

		return $css;
	}


	function border_properties( $options ){

		// Query for border size and fade 
		$_regex = "/^0(em|vw|vh|cm|mm|in|px|pt)?$/";
		$_none  = preg_match($_regex, $options['size']) ? true : false;
		$_solid = preg_match($_regex, $options['fade']) ? true : false;

		if ($_none) {return "";}
		else{$css = "";}

		// Parse input atts
		if ( isset($options['input_atts']) && is_array($options['input_atts']) ){
			$_targets  = array_key_exists('targets', $options['input_atts']) ?
				$options['input_atts']['targets'] : false;

			$_position = array_key_exists('position', $options['input_atts']) ?
				$options['input_atts']['position'] : false;	

			$_squeeze  = array_key_exists('squeeze', $options['input_atts']) ?
				$options['input_atts']['squeeze'] : '-'.$options['size'];

			$_mode  = array_key_exists('mode', $options['input_atts']) ?
				$options['input_atts']['mode'] : 'default';				
		}
		else {
			$_targets  = false; 
			$_position = false; 
			$_squeeze  = '-'.$options['size'];
			$_mode 	   = 'default';
 		}

		if ($_targets !== false){$css .= $this->begin_array_rule( $_targets );}
		// If the border has no fade property, then render with css
		// border. This makes border radius look clean. 	
	 	if ( $_solid ) {

	 		switch ($_position) {
	 			
	 			case "top": 
	 				$_ = "border-top";
	 				break;
	 			
	 			case "bottom": 
	 				$_ = "border-bottom";
	 				break;
	 			
	 			case "left": 
	 				$_ = "border-left";
	 				break;
	 			
	 			case "right": 
	 				$_ = "border-right";
	 				break;
	 			
	 			default:
	 				$_ = "border";
	 		}		
		 	$css .= $this->add_rule( $_, implode(' ', array('solid', $options['size'], $options['color'])));
		} 

		// Otherwise render with box-shadow radius property
		else {
			switch ($_position) {

	 			case "top": 
	 				$_ = array('0','-'.$options['size'],$options['fade'],$_squeeze,$options['color']); 
	 				$c = array('0',$options['size'],$options['fade'],$_squeeze,$options['color']);
	 				break;
	 			
	 			case "bottom": 
	 				$_ = array('0',$options['size'],$options['fade'],$_squeeze,$options['color']);
		 			$c = array('0','-'.$options['size'],$options['fade'],$_squeeze,$options['color']); 
	 				break;
	 		
	 			case "left": 
					$_ = array('-'.$options['size'],'0',$options['fade'],$_squeeze, $options['color']); 
	 				$c = array($options['size'],'0',$options['fade'],$_squeeze, $options['color']);
	 				break;

	 			case "right": 
	 				$_ = array($options['size'],'0',$options['fade'],$_squeeze, $options['color']);
	 				$c = array('-'.$options['size'],'0',$options['fade'],$_squeeze, $options['color']); 
	 				break;

	 			default:
	 				$_ = array('0 0',$options['fade'],$options['size'],$options['color']); 
	 				$c = array('0 0',$options['fade'],$options['size'],$options['color']);
	 		}		

	 		$modes = array(
	 			'default' => implode(' ', $_),
	 			'inset'	  => implode(' ', array_merge($c, array('inset')) ),
	 		);

		 	$css .= $this->add_rule( 'box-shadow', $modes[$_mode]);
		}	
		if ($_targets !== false){$css .= $this->end_rule();}
		return $css; 
	}
}
?>