<?php

	// functions.php is the very first thing that is happens in wordpress
	if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	// Define max content width 
	if ( ! isset( $content_width ) ) {
			$content_width = 640;
	}	

	// Theme path. This is for accessing things iternally 
	if ( !defined( 'PAGODA_THEME_PATH' ) ) {
		define( 'PAGODA_THEME_PATH', get_template_directory().'/' );
	} 

	// Theme URI. This is used for acessing things externally
	if ( !defined( 'PAGODA_THEME_URI' ) ){
		define( 'PAGODA_THEME_URI', get_template_directory_uri() .'/' );
	}

	// Method to return local file with output buffer (for JSON)
	function file_local_contents($_file){
		ob_start(); 
		include($_file);
		$_data .= ob_get_contents();
		ob_end_clean();
		return $_data;
	}

	// Initialize WP Filesystem API (for autogen)
	global $wp_filesystem;
	if (empty($wp_filesystem)) {
	   	require_once (ABSPATH . '/wp-admin/includes/file.php');
	    WP_Filesystem();
	}

	// This is a completely generic autoloader for $class_name. 
	// 1) If $class_name conforms to: $class_slug_<class_ext> 
	// 2) Load the corresponding class-slug-<class-ext>.php
	// 3) Which should be located in one of: $array_paths  
	spl_autoload_register( function ( $class_name ) {
	
		// An array of class slugs we will check against 
		$class_slugs = array(
			'pagoda'	=> false,
			'Nablafire' => false,
			'TGM'		=> false,
		);
		// Otherwise scan these directories for the class file 			
		$array_paths = array(   
        	
 			// Classes
        	'inc/',
        	'inc/classes/',
			'inc/classes/pagoda-tgmpa/',
        	'inc/classes/pagoda-admin/',
 			
 			// Customizer 	
 			'inc/customizer/',
 			
 			// Utilities
        	'inc/nablafire-utils/data-utils/',
        	'inc/nablafire-utils/theme-utils/'
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
        			$class_path = PAGODA_THEME_PATH . $path . $class_file . '.php';
					if ( file_exists( $class_path ) ) {include $class_path; return;}
				} 
			}
		}
	} ); // Close autoloader
	
	// Bootstrap theme
	if ( ! function_exists( 'instantiate_theme' ) ) {		
		function instantiate_theme() {			
			$theme = pagoda::get_instance();
			return $theme;
		}
	}
	$theme = instantiate_theme();

?>