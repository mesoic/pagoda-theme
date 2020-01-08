<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//
// A container class (i.e. namespace) to hold sanitize callbacks. Sanitize callbacks
// are filter functions which check for sanity in input. Customizer values are stored
// as options in the wordpress DB, so it is wise to check that everything we are putting
// into the DB is safe. 
//
// In all callbacks, we pass both the user input string, as well as the instance of 
// WP_Customize_Setting which calling this callback. We can access the default value 
// the setting object by issuing $setting->default.  
//
// @param input 		       	$input
// @param WP_Customize_Setting 	$setting 
// @return 						$output
//
// In some cases we must access the WP_Customize_Control object which is associated with
// the setting. This is accomplished by: 
// 
//		$atts = $setting->manager->get_control($setting->id)->input_attrs;
// 
// This allows us to pass values (though obliquely) to the callback function upon which 
// we can filter our input. Here we have two examples of this in sanitize_number_range
// and sanitize_dropdown. 'input_attrs' is set when we initialize the control, and it 
// should be an array that contains the values that we want to check against. 
// 
// 		sanitize_number_range 	: 'input_attrs' => array('min' => $min, 'max' => $max)
//		sanitize_dropdown 		: 'input_attrs' => (array)$select_options
//
class Nablafire_Customize_Data_Sanitize {

	function __construct(){
		;
	}

	// Pass	
	function sanitize_pass($input){
		return $input;
	}

	// Text field
	function sanitize_text_field($input){
		return sanitize_text_field( $input );	
	}
	
	// URL
	function sanitize_url( $input ) {
		return esc_url_raw( $input );
	}
	
	// Checkbox. Note that checkbox values in WP are stored as '1' and '' presumably
	// because get_option returns bool(false) if the option in question does not exist 
	// in the DB. If checkbox values were stored as bool(true) and bool(false) then 
	// the result of get_option would be ambiguous in the case of a checkbox.
	function sanitize_checkbox( $input, $setting ) {
		return ( $input === true || $input === '1' ) ? '1' : '';
	}

	// Allow for word characters and -. Also optionally incude the # token. If the
	// user does not add it, we will add it for them here. 
	function sanitize_html_id($input, $setting){
		
		if(preg_match("/^#?(\w|-)*$/", $input, $_input)){			
			return 
			((strcmp(substr($_input[0][0], 0, 1) , "#")) != 0) ? '#' . $input : $input;
		}
		else{
			return $setting->default;
		}
	}

	// kill evil scripts 
	function sanitize_kses($input){
		$args = array(
  		  	//formatting
    		'strong' => array(),
    		'em'     => array(),
    		'b'      => array(),
    		'i'      => array(),

		    //links
    		'a'     => array(
        		'href' => array()
    		)
		);
		return wp_kses( $input, $args );
	}

	// Image paths
	function sanitize_image( $input, $setting ) {
	    $mimes = array(
    	    'jpg|jpeg|jpe' => 'image/jpeg',
    	    'gif'          => 'image/gif',
    	    'png'          => 'image/png',
    	    'bmp'          => 'image/bmp',
       		'tif|tiff'     => 'image/tiff',
        	'ico'          => 'image/x-icon'
    	);
		// Return an array with file extension and mime_type.
    	$file = wp_check_filetype( $input, $mimes );
		// If $image has a valid mime_type, return it; otherwise, return the default.
    	return ( $file['ext'] ? $input : $setting->default );
	}

	// Sanitize dropdown 
	function sanitize_dropdown($input, $setting){
		$values = $setting->meta['data']['values'];
		return ( in_array( $input, $values ) ? $input : $setting->default );
	}

	// Sanitize keyed dropdown 
	function sanitize_dropdown_keyed($input, $setting){
		$values = array_keys($setting->meta['data']['values']);
		return ( in_array( $input, $values ) ? $input : $setting->default );
	}

	// Sanitize number range
	function sanitize_number_range($input, $setting){
		$number = absint( $input );

		$_range = $setting->meta['data']['range'];
		$_min = ( isset( $_range['min'] ) ? $_range['min'] : $number );
		$_max = ( isset( $_range['max'] ) ? $_range['max'] : $number );
	
		// If the number is within the valid range, return it; otherwise, return the default. 
		// If min and max are not set in input_attrs, then $number=absint($input) is returned. 
		return ( ( $_min <= $number && $number <= $_max ) ? $number : $setting->default);
	}

	// regex sanitizers ... 
	function sanitize_alpha_color($input, $setting){
		// Check for rgba, #000, #000000	
		if(preg_match("/^rgba\((([0-9]\s*|[1-9][0-9]\s*|1[0-9][0-9]\s*|2[0-4][0-9]\s*|25[0-5]\s*),){3}0(\.\d{1,2})?\s*\)$|^#[\d|a-f|A-F]{3}$|^#[\d|a-f|A-F]{6}$/", $input, $_input)){
				return $input;
		}
		else{
			return $setting->default;
		}
	}

	function sanitize_hex_color($input, $setting){
		// Check for hex color #000, #000000	
		if(preg_match("/^#[\d|a-f|A-F]{3}$|^#[\d|a-f|A-F]{6}$/", $input, $_input)) {
			return $input;
		}
		else {
			return $setting->default;
		}
	}

	// Matches CSS value with single unit
	function sanitize_css_unit_value($input, $setting){
		if(preg_match("/^\s*(-?\d{1,3}(\.\d{1,3})?(em|vw|vh|cm|mm|in|px|pt))\s*$/", $input, $_input)) {
			return $input;
		}
		else {
			return $setting->default;
		}
	}

	// Matches the above OR zero (for 0=auto settings). If 0 then strip whitespace
	function sanitize_css_unit_value_auto($input, $setting){
		if(preg_match("/^\s*(-?\d{1,3}(\.\d{1,3})?(em|vw|vh|cm|mm|in|px|pt))\s*$|\s*(0)\s*/", $input, $_input)) {
			// Note that zero is in fourth capture group ...
			return ($_input[4]=='0') ? $_input[4] : $input;
		}
		else {
			return $setting->default;
		}

	}

	// Matches margin shorthand for CSS/allows negative values various units and decimals. 
	function sanitize_css_margin_shorthand($input, $setting){
		if(preg_match("/^(\s*-?\d{1,3}(\.\d{1,3})?(em\s*|vw\s*|vh\s*|cm\s*|mm\s*|in\s*|px\s*|pt\s*)){1,4}$/", $input, $_input)){
			return $input;	
		}
		else{
			return $setting->default;
		}
	}

	// This is the same as above, but padding cannot be negative so we omit the minus sign.
	function sanitize_css_padding_shorthand($input, $setting){
		if(preg_match("/^(\s*\d{1,3}(\.\d{1,3})?(em\s*|vw\s*|vh\s*|cm\s*|mm\s*|in\s*|px\s*|pt\s*)){1,4}$/", $input, $_input)){
			return $input;	
		}
		else{
			return $setting->default;
		}
	}
}