<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
//
// This is a container class for the font control AJAX behaviour. Note that wp_enqueue_scripts 
// and wp_localize_script both appear in the WP_Customize_Font_Control classfile. This class 
// contains one method
//
// update_font_variants()  : The AJAX callback for WP_Customize_Font_Control.
//
class Nablafire_Customize_Font_AJAX {

	public function __construct($font_gen) {
		$this->font_gen = $font_gen;
	}

	public function update_font_variants(){
		// Data that has been AJAXed back 
		$font_val = (string)$_POST['data']['font_val'];
    	$font_opt = (string)$_POST['data']['font_opt'];
		$var_opts = (string)$_POST['data']['var_opts'];

		// Unpack font variants options
		$var_opts_json = str_replace("\\", "", $var_opts);
		$var_opts_data = json_decode($var_opts_json);

		// We need to set the font and variant in the options table so 
		// it matches what we produce in the font controller. 
		update_option($font_opt, $font_val);
		foreach ($var_opts_data as $key => $option) {
			update_option($option, 'regular'); // Default to regular
		}
				
	    $_json = json_encode( $this->font_gen->get_variants($font_val));
	    if ( $_json != false ) {
		  	wp_send_json_success($_json);
		}
		else {
			wp_send_json_error('AJAX error');
		}
		
		// wp_die() for AJAX scripts
		wp_die();
	}
}