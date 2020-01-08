<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// A container class to hold sanitize functions for WP_Customize_Font_Control. 
//
// In all callbacks, we pass both the user input string, as well as the instance of 
// WP_Customize_Setting which calling this callback. We can access the default value 
// the setting object by issuing $setting->default.  
//
// @param input 		       	$input
// @param WP_Customize_Setting 	$setting 
// @return 						$output
//					 
// Since the structure of the WP_Customize_Font_Control is a bit different than standard 
// settings/controls in the customizer, we need to make a serparate customizer class for 
// it. Note that WP_Sanitize_Font_Control will hold the single instance of the font_gen 
// class nabla_pagoda_fonts_autogen()
//
// Debug: 
//
//	ob_flush();
//	ob_start();
//	var_dump($var);
//	file_put_contents("/var/www/html/dump.txt", ob_get_flush());
//
class Nablafire_Customize_Font_Sanitize {

	function __construct($font_gen){
		$this->font_gen = $font_gen;
	}

	// Font Family. Straightforward 
	function sanitize_font_family($input, $setting){
		return (array_key_exists($input, $this->font_gen->fonts) ? $input : $settings->default); 
	}

	// Font Variant. Does not exactly need to be sanitized as it is set dynamically via an AJAX 
	// Call. However, it is prudent to make sure it checks out. Note that the AJAX call actually
	// sets the font option so it is possible to check variant options to verify that everything 
	// works out.  
	function sanitize_font_variant($input, $setting){

		// First, we need to pull some data out of the setting metadata variable. 
		// This allows us to reconstruct the font option associated with this variant. 
		$_font = (string)$setting->meta['data']['font'];
		$_slug = (string)$setting->meta['slug'];		

		// Next, we query the option in the DB. Note that this is always set by the 
		// AJAX call via update_option() so the font option will always reflect the 
		// available variants. 
		$font  = get_option( $_slug .  $_font );

		// In the case where the option has not yet been set, we neet to set font to the
		// default value. Otherwise the next call to get_variants could fail. This only
		// applies in the case of a 'new option'.
		if ($font == false){ 
			$font = $setting->manager->get_setting( $_slug . $_font )->default;
		}

		// Now we can grab the available variants array for this font. 
		$vars = $this->font_gen->get_variants($font);

		// Finally check if $input is in the list. If so then return input, otherwise 
		// return the default value (which should be 'regular').
		return ( in_array($input, $vars) ? $input : $setting->default );
	}

	// Font Color. Straightforward 
	function sanitize_font_color($input, $setting){
		// Check for rgba, #000, #000000	
		if(preg_match("/^rgba\((([0-9]\s*|[1-9][0-9]\s*|1[0-9][0-9]\s*|2[0-4][0-9]\s*|25[0-5]\s*),){3}0(\.\d{1,2})?\s*\)$|^#[\d|a-f|A-F]{3}$|^#[\d|a-f|A-F]{6}$/", $input, $_input)){
				return $input;
		}
		else{
			return $setting->default;
		}
	}

	// Font Size: Similar approach to font variant
	function sanitize_font_size( $input, $setting ) {
	
		$number = absint( $input );

		// Note that $settig is here a Nablafire_Customize_Setting object which 
		// is identical to WP_Customize_Font_Setting with the exception that it 
		// is initialized with one additional data memeber ... an array of its 
		// associated data. 
		$_range = $setting->meta['data']['range'];
		$_min = ( isset( $_range['min'] ) ? $_range['min'] : $number );
		$_max = ( isset( $_range['max'] ) ? $_range['max'] : $number );
	
		// If the number is within the valid range, return it; otherwise, return the default. 
		// If min and max are not set in input_attrs, then $number=absint($input) is returned. 
		return ( ( $_min <= $number && $number <= $_max ) ? $number : $setting->default);
	}
}

?>