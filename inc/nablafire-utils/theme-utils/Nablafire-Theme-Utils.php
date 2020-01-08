<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Nablafire_Theme_Utils {

	function __construct($utils_path, $utils_uri){
		
		// For autoloader
		if (!defined('THEME_UTILS_PATH')) {
			define( 'THEME_UTILS_PATH', $utils_path );
		} 

		$this->utils_path = $utils_path;
		$this->utils_uri  = $utils_uri;

		$this->font_gen   = 
			new Nablafire_Customize_Font_Autogen(
				$this->utils_path, 
				$this->utils_uri
			);
		$this->font_ajax  = 
			new Nablafire_Customize_Font_AJAX($this->font_gen);	 

		// Customizer enqueue scripts hook
		add_action('customize_controls_enqueue_scripts', array($this, 'enqueue') );

		// Register AJAX callback for font control. 		
		add_action('wp_ajax_update_font_variants', 
			array( $this->font_ajax, 'update_font_variants') );

	}

	public function enqueue() {

		// Check version number
		if(version_compare(get_bloginfo('version'),'4.9', '>=') ) {
			$sub_directory = '4.9/';
		}
		else {
			$sub_directory = '4.8/';
		}

		// Enqueue wp-color-picker
		wp_enqueue_style('wp-color-picker');

		// Colopicker css and js
		wp_enqueue_style(
			'nablafire-customize-color-control',
			trailingslashit($this->utils_uri) . 
				'color-control/css/' . $sub_directory . 'nablafire-customize-color-control.css',
			array( 'wp-color-picker' ),
			'1.0.0'
		);

		// Note that we need to put this in the footer.
		wp_enqueue_script(
			'nablafire-customize-color-control',
			trailingslashit($this->utils_uri) . 
				'color-control/js/' . $sub_directory . 'nablafire-customize-color-control.js',
			array( 'jquery', 'wp-color-picker' ),
			'1.0.0',
			true
		);

		// Enqueue the AJAX js and localize
		wp_enqueue_script(
			'nablafire-customize-font-ajax',
			trailingslashit($this->utils_uri) . 
				'font-control/fontgen/js/ajax/nablafire-customize-font-ajax.js',
			array( 'jquery'),
			'1.0.0'
		);

		// Need to expose ajaxurl for processing AJAX requests. Localize 
		// script sends the AJAX url in an array to the DOM variable. 
		// NABLAFIRE_CUSTOMIZE_FONT_AJAX.  
		wp_localize_script('nablafire-customize-font-ajax', 
							'NABLAFIRE_CUSTOMIZE_FONT_AJAX', array(
							'ajaxurl'  => admin_url('admin-ajax.php'),
		));

	}
}

// Theme Utils Autoloader
spl_autoload_register( function ( $class_name ) {

	// An array of class slugs we will check against 
	$class_slugs = array(
		'Nablafire' => false,
	);

	// Otherwise scan these directories for the class file 			
	$array_paths = array(   
   	  	'data-control/',
		'font-control/',
		'font-control/fontgen/',
 	);

	// If our class_name starts with something in the above array, then set its
	// is_class to true. Note that strpos returns int if substring is found and 
	// bool(false) if substring is not found. 
	foreach($class_slugs as $slug => $is_class){ 

		// If the class name is in the list above, set its value to true
		$class_slugs[$slug] = is_int(strpos($class_name, $slug)) ? true : false;       

		// If the value was just set to true, then do the following
		if ( $class_slugs[$slug] ){
			// If the class exists, then simply return
			if ( class_exists( $class_name ) ) {return;}
			// Otherwise, search array paths for the classfile, include, return
			foreach($array_paths as $path){  
				$class_file = str_replace( '_', '-', $class_name );             
 				$class_path = THEME_UTILS_PATH . $path . $class_file . '.php';
				if ( file_exists( $class_path ) ) {include $class_path; return;}
			} 
		}
	}
} );