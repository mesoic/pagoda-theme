<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	class pagoda_navbar_customizer{ 

		function __construct($defaults, $font_gen){

			// Need defaults and fontgen for navbar 
	   		$this->defaults = $defaults; // From JSON Options Table
	    	$this->font_gen = $font_gen; // The single instance of nabla_pagoda_fonts_autogen()

	    	// Data Control 
			$this->sanitize_data = new Nablafire_Customize_Data_Sanitize();
			$this->register_data = new Nablafire_Customize_Data_Register($this->sanitize_data);
			
			// Font control
			$this->sanitize_font = new Nablafire_Customize_Font_Sanitize($this->font_gen);
			$this->register_font = new Nablafire_Customize_Font_Register($this->sanitize_font);

			add_action( 'customize_register',  array( $this, 'register_options' ) );	
		}

		function register_options($wp_customize){

 			// Main Panel
			$panel = 'pagoda-navbar-settings';
	 		$wp_customize->add_panel( $panel, array(
        		'title'             => esc_html__( 'Pagoda Navbar Settings', 'pagoda' ),
        		'priority'          => 30,    			
    		) ); 

			$this->register_navbar_main_options($wp_customize, $panel, $this->defaults['main']);    
			$this->register_navbar_font_options($wp_customize, $panel, $this->defaults['fonts']); 
			$this->register_search_all_options($wp_customize, $panel, $this->defaults['search']);
			$this->register_scroll_all_options($wp_customize, $panel, $this->defaults['scroll']);
		}

		//////////////////////////////////////////////////////////////////////////////////////
		//									NAVBAR DISPLAY 									//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "main" : {
		//		"pagoda_navbar_show_home"	: "1",
		//		"pagoda_navbar_show_page"	: "1",
		//		"pagoda_navbar_show_post"	: "1",
		//		"pagoda_navbar_logo_img"	: "",
		//		"pagoda_navbar_logo_height"	: "50px",
		//		"pagoda_navbar_logo_width"	: "100px",
		//		"pagoda_navbar_mn_squeeze"	: "100px",
		//		"pagoda_navbar_mn_spacing"	: "15px",
		//		"pagoda_navbar_sb_width"	: "150px",
		//		"pagoda_navbar_bd_size"		: "2px",			
		//		"pagoda_navbar_bd_fade"		: "0px",			
		//		"pagoda_navbar_bd_color"	: "#999",
		//		"pagoda_navbar_bg_color"	: "#666",
		//		"pagoda_navbar_sc_color"	: "#000",
		//		"pagoda_navbar_sb_color"	: "#666",
		//		"pagoda_navbar_sb_hover"	: "#000"
		// },
		//
		function register_navbar_main_options($wp_customize, $panel, $defaults){

	 		$section = 'navbar-display-settings';
			$wp_customize->add_section( $section, array(
        		'title'         => esc_html__( 'Navbar Display Settings' , 'pagoda'  ),
        		'priority'      => 30,
        		'panel'  		=> $panel,
    		) ); 

				// Show navbar Checkboxes
				$data_group = array(
					'id'	=> 'data',
					'group' => 'pagoda_navbar_show_',
					'label'	=> esc_html__('Show Navbar', 'pagoda'),
					'desc'	=> esc_html__('Select whether you would like to show the navbar on the homepage, pages, and posts respectively', 'pagoda' ),
					'color'	=> 'red',
					'priority' => 2
				);
				$data_keys = array(
					'home' 			=> array(
						'type'		=> 'checkbox',
						'sanitize'	=> 'sanitize_checkbox',
						'label'		=> esc_html__( 'Show on Home?', 'pagoda' ),
					),
					'page' 			=> array(
						'type'		=> 'checkbox',
						'sanitize'	=> 'sanitize_checkbox',
						'label'		=> esc_html__( 'Show on Pages?', 'pagoda' ),
					),			
					'post' 			=> array(
						'type'		=> 'checkbox',
						'sanitize'	=> 'sanitize_checkbox',
						'label'		=> esc_html__( 'Show on Posts?', 'pagoda' ),
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);


    			/////////////////////////////////////////////////////////////////////	
				// Logo Image
    			$option = 'pagoda_navbar_logo_img';
				$wp_customize->add_setting( $option, array(
        			'type'          => 'option',
       				'transport'     => 'postMessage',
       	 			'default'		=> $defaults[$option],
       	 			'sanitize_callback' => array($this->sanitize_data, 'sanitize_image')
	    		) );
				$wp_customize->add_control( new WP_Customize_Image_Control( 
					$wp_customize, $option, 
					array(
						'label'       => esc_html__( 'Select Site Logo', 'pagoda' ),
						'section'     => $section,
						'priority'	  => 2,
					)
				) );

				/////////////////////////////////////////////////////////////////////
				// Navar Layout
				$data_group = array(
					'id'	=> 'layout',
					'group'	=> 'pagoda_navbar_',
					'label'	=> esc_html__('Navbar Layout', 'pagoda'),
					'desc'	=> false,
					'color'	=> 'blue'
				);
				$data_keys = array(
					'logo_height'=> array(
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Logo Height (CSS Units)', 'pagoda' ),
						'desc'	=> esc_html__( 'This value also determines the line-height for menu items in the navigation bar', 'pagoda')
					),
					'mn_spacing'=> array(
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Navbar Element Spacing (CSS Units)', 'pagoda' ),
						'desc'	=> esc_html__( 'Horizontal spacing between navigation menu items', 
							'pagoda')
					),
					'sb_width'=> array(
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Navbar Submenu Width (CSS Units)', 'pagoda' ),
						'desc'	=> esc_html__( 'The width of navbar dropdown submenus', 
							'pagoda')
					),		
					'mn_squeeze'=> array(
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Navbar Squeeze (CSS Units)', 'pagoda' ),
						'desc'	=> esc_html__( 'This value allows you to push the logo and menu items symmetrically towards the center of the page by the specified amount', 
							'pagoda')
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);


				/////////////////////////////////////////////////////////////////////
				// Navbar Border Properties 
				$data_group = array(
					'id'	=> 'border',
					'group'	=> 'pagoda_navbar_',
					'label'	=> esc_html__('Navbar Border Properties', 'pagoda'),
					'desc'	=> false,
					'color'	=> 'yellow'
				);
				$data_keys = array(
					'bd_size' 	=> array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Navbar Border Thickness', 'pagoda' ),
						'desc'	=> esc_html__( 'Set the thickness of the bottom border of the navbar', 
							'pagoda')
					),
					'bd_fade'	=> array(
						'type'	=> 'text',
						'label' => esc_html__('Border Fade (CSS Units)' ,'pagoda'),
						'sanitize'=>'sanitize_css_unit_value',	
					),
					'bd_color' 	=> array( 
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Border Color','pagoda'), 
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);


				/////////////////////////////////////////////////////////////////////
				// Navbar Color Palette
				$data_group = array(
					'id'	=> 'color',
					'group'	=> 'pagoda_navbar_',
					'label'	=> esc_html__('Navbar Color Palette', 'pagoda'),
					'desc'	=> false,
					'color'	=> 'green'
				);

				$data_keys = array(
					'bg_color'	=> array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Background Color','pagoda'), 
					),
					'sc_color'	=> array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Background Color on Scroll','pagoda'), 
						'desc'	=> esc_html__( 'The background color that will appear when scrolling beyond the top of the page', 'pagoda'),

					),
					'sb_color'	=> array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Submenu Background Color','pagoda'),
						'desc'	=> esc_html__( 'The background color for dropdown submenus', 
							'pagoda'),
					
					),
					'sb_hover'	=> array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__( 'Submenu Item Color on Hover','pagoda'),
						'desc'	=> esc_html__( 'The background color for submenu list items when hovering over them', 'pagoda'), 
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
		}


		//////////////////////////////////////////////////////////////////////////////////////
		//									NAVBAR FONTS									//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "fonts" : {
		//		"pagoda_navbar_mn_font_fam"		: "Montserrat",
		//		"pagoda_navbar_mn_font_var"		: "regular",
		//		"pagoda_navbar_mn_font_size"	: "16",
		//		"pagoda_navbar_mn_font_color"	: "#fff",
		//		"pagoda_navbar_ac_size"			: "2px",
		//		"pagoda_navbar_ac_fade"			: "2px",
		//		"pagoda_navbar_ac_color"		: "#fff",
		//		"pagoda_navbar_sb_font_fam"		: "Montserrat",
		//		"pagoda_navbar_sb_font_var"		: "regular",
		//		"pagoda_navbar_sb_font_size"	: "14",
		//		"pagoda_navbar_sb_font_color"	: "#ccc"
		// },
		//
		function register_navbar_font_options($wp_customize, $panel, $defaults){

			$section = 'navbar-font-settings';
			$wp_customize->add_section( $section, array(
        		'title'         => esc_html__( 'Navbar Font Settings' , 'pagoda'  ),
        		'priority'      => 30,
        		'panel'  		=> $panel,
    		) ); 


				/////////////////////////////////////////////////////////////////////
				// Navbar Font Control 
				$data_group  = array(
					'id'	=> 'font_control',
					'group'	=> 'pagoda_navbar_mn_',
					'label'	=> esc_html__( 'Navbar Font Control', 'pagoda' ),
					'desc'	=> false,
				);
				$data_keys   = array(
					'font_fam' 	=> array(
						'type'	=> 'font',
						'sanitize' => 'sanitize_font_family',
					),
					'font_var'	=> array(
						'type'	=> 'variant',
						'font'	=> 'font_fam',
						'sanitize' => 'sanitize_font_variant'
					),
					'font_size'	=> array(
						'type'	=> 'size',
						'range'	=> array('min' => 8, 'max'=> 120), 
						'sanitize' => 'sanitize_font_size',
					),
					'font_color'=> array(
						'type'  => 'color',
						'sanitize' => 'sanitize_font_color',
						'default' => false, // Assigned in loop
				) );
				$this->register_font->control($wp_customize, $section, $data_group, $data_keys, $defaults);	


				/////////////////////////////////////////////////////////////////////
				// Navbar Font Accent
				$data_group = array(
					'id'	=> 'font_accent',
					'group'	=> 'pagoda_navbar_ac_',
					'label'	=> esc_html__('Navbar Item Accents', 'pagoda'),
					'desc'  => false, 
					'color'	=> 'purple'
				);
				$data_keys = array(
					'size' => array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Accent Thickness', 'pagoda' ),
						'desc'	=> esc_html__( 'Set the thickness of the bottom border for menu item accents', 'pagoda')
					),
					'fade' => array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Accent Fade', 'pagoda' ),
						'desc'	=> esc_html__( 'Fade menu item accents? (0px = solid)', 'pagoda')	
					),
					'color'	=> array( 
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__( 'Accent Color','pagoda'),
						'desc'	=> esc_html__( 'The color of the underline accent when hovering over navbar items', 'pagoda'),
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				/////////////////////////////////////////////////////////////////////
				// Navbar Submenu Font Control
				$data_group  = array(
					'id'	=> 'font_control',
					'group'	=> 'pagoda_navbar_sb_',
					'label'	=> esc_html__( 'Navbar Submenu Font Control', 'pagoda' ),
					'desc'	=> false,
				);
				$data_keys   = array(
					'font_fam' 	=> array(
						'type'	=> 'font',
						'sanitize' => 'sanitize_font_family',
					),
					'font_var'	=> array(
						'type'	=> 'variant',
						'font'	=> 'font_fam',
						'sanitize' => 'sanitize_font_variant'
					),
					'font_size'	=> array(
						'type'	=> 'size',
						'range'	=> array('min' => 8, 'max'=> 120), 
						'sanitize' => 'sanitize_font_size',
					),
					'font_color'=> array(
						'type'  => 'color',
						'sanitize' => 'sanitize_font_color',
						'default' => false, // Assigned in loop
				) );
				$this->register_font->control($wp_customize, $section, $data_group, $data_keys, $defaults);	
		}

		//////////////////////////////////////////////////////////////////////////////////////
		//									NAVBAR SEARCH									//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "search" : {
		//		"pagoda_search_show_home"		: "1",
		//		"pagoda_search_show_page"		: "1",
		//		"pagoda_search_show_post"		: "1",						
		//		"pagoda_search_tx_color"		: "#fff",
		//		"pagoda_search_tx_font_color"	: "#ccc",
		//		"pagoda_search_bt_color"		: "#000",
		//		"pagoda_search_bt_hover"		: "#666",
		//		"pagoda_search_bt_font_fam"		: "Montserrat",
		//		"pagoda_search_bt_font_var"		: "regular",
		//		"pagoda_search_bt_font_size"	: "20",
		//		"pagoda_search_bt_font_color"	: "#fff"
		// },	
		//
		function register_search_all_options($wp_customize, $panel, $defaults){
		
			$section = 'navbar-search-settings';
			$wp_customize->add_section( $section, array(
        		'title'         => esc_html__( 'Navbar Search Settings', 'pagoda'  ),
        		'priority'      => 30,
        		'panel'  		=> $panel,
    		) ); 

				/////////////////////////////////////////////////////////////////////
				// Show Searchbar Checkboxes
				$data_group = array(
					'id'	=> 'data',
					'group' => 'pagoda_search_show_',
					'label'	=> esc_html__('Show Search in Navbar', 'pagoda'),
					'desc'	=> esc_html__('Select whether you would like to show the search form on the homepage, pages, and posts respectively', 'pagoda' ),
					'color'	=> 'red',
					'priority' => 2
				);
				$data_keys = array(
					'home' 			=> array(
						'type'		=> 'checkbox',
						'sanitize'	=> 'sanitize_checkbox',
						'label'		=> esc_html__( 'Show on Home?', 'pagoda' ),
					),
					'page' 			=> array(
						'type'		=> 'checkbox',
						'sanitize'	=> 'sanitize_checkbox',
						'label'		=> esc_html__( 'Show on Pages?', 'pagoda' ),
					),			
					'post' 			=> array(
						'type'		=> 'checkbox',
						'sanitize'	=> 'sanitize_checkbox',
						'label'		=> esc_html__( 'Show on Posts?', 'pagoda' ),
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
		
				/////////////////////////////////////////////////////////////////////
				// User Input Styles 
				$data_group = array(
					'id'	=> 'styles',
					'group' => 'pagoda_search_',
					'label'	=> esc_html__('Search From Styles' , 'pagoda'),
					'desc'	=> false,
					'color'	=> 'blue',
				);
				$data_keys = array(				
					'tx_color' 		=> array(
						'type'		=> 'color',
						'default' 	=> false, // Assigned in loop
						'sanitize'	=> 'sanitize_alpha_color',
						'label'		=> esc_html__('Input Background Color','pagoda'),
					),
					'tx_font_color' => array(
						'type'		=> 'color',
						'default' 	=> false, // Assigned in loop
						'sanitize'	=> 'sanitize_alpha_color',
						'label'		=> esc_html__('Input Font Color','pagoda'), 
					),
					'bt_color' 		=> array(
						'type'		=> 'color',
						'default' 	=> false, // Assigned in loop
						'sanitize'	=> 'sanitize_alpha_color',
						'label'		=> esc_html__('Search Button Color','pagoda'),
					),
					'bt_hover'			=> array(
						'type'		=> 'color',
						'default' 	=> false, // Assigned in loop
						'sanitize'	=> 'sanitize_alpha_color',
						'label'		=> esc_html__('Search Button Color on Hover','pagoda')
				) );				
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
		

				/////////////////////////////////////////////////////////////////////
				// Submit Button Font Control
				$data_group  = array(
					'id'	=> 'font_control',
					'group'	=> 'pagoda_search_bt_',
					'label'	=> esc_html__( 'Search Font Control', 'pagoda' ),
					'desc'	=> false,
				);
				$data_keys   = array(
					'font_fam' 	=> array(
						'type'	=> 'font',
						'sanitize' => 'sanitize_font_family',
					),
					'font_var'	=> array(
						'type'	=> 'variant',
						'font'	=> 'font_fam',
						'sanitize' => 'sanitize_font_variant'
					),
					'font_size'	=> array(
						'type'	=> 'size',
						'range'	=> array('min' => 8, 'max'=> 120), 
						'sanitize' => 'sanitize_font_size',
					),
					'font_color'=> array(
						'type'  => 'color',
						'sanitize' => 'sanitize_font_color',
						'default' => false, // Assigned in loop
				) );
				$this->register_font->control($wp_customize, $section, $data_group, $data_keys, $defaults);		
		}

		//////////////////////////////////////////////////////////////////////////////////////
		//								  SCROLL TO TOP ICON 								//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "scroll" : {
		//		"pagoda_scroll_show_home"	: "1",
		//		"pagoda_scroll_show_page"	: "1",
		//		"pagoda_scroll_show_post"	: "1",	
		//		"pagoda_scroll_position"	: "20px",
		//		"pagoda_scroll_ic_size"		: "50px",
		//		"pagoda_scroll_fa_size"		: "24px",
		//		"pagoda_scroll_bg_color"	: "#000",				
		//		"pagoda_scroll_bg_hover"	: "#666", 
		//		"pagoda_scroll_fa_color"	: "#fff",
		//		"pagoda_scroll_bd_inset"	: "",
		//		"pagoda_scroll_bd_radius"	: "0px",
		//		"pagoda_scroll_bd_size"		: "5px",	
		//		"pagoda_scroll_bd_fade"		: "5px",
		//		"pagoda_scroll_bd_color"	: "#eee"
		// }
		//
		function register_scroll_all_options($wp_customize, $panel, $defaults){
		
			$section = 'scroll-to-top-icon-settings';
			$wp_customize->add_section( $section, array(
        		'title'         => esc_html__( 'Scroll to Top Icon Settings', 'pagoda'  ),
        		'priority'      => 30,
        		'panel'  		=> $panel,
    		) ); 

				/////////////////////////////////////////////////////////////////////
				// Show Scroll Checkboxes
				$data_group = array(
					'id'	=> 'data',
					'group' => 'pagoda_scroll_show_',
					'label'	=> esc_html__('Show Scroll Icon', 'pagoda'),
					'desc'	=> esc_html__('Select whether you would like to show the scroll to top icon on the homepage, pages, and posts respectively', 'pagoda' ),
					'color'	=> 'red',
					'priority' => 2
				);
				$data_keys = array(
					'home' 			=> array(
						'type'		=> 'checkbox',
						'sanitize'	=> 'sanitize_checkbox',
						'label'		=> esc_html__( 'Show on Home?', 'pagoda' ),
					),
					'page' 			=> array(
						'type'		=> 'checkbox',
						'sanitize'	=> 'sanitize_checkbox',
						'label'		=> esc_html__( 'Show on Pages?', 'pagoda' ),
					),			
					'post' 			=> array(
						'type'		=> 'checkbox',
						'sanitize'	=> 'sanitize_checkbox',
						'label'		=> esc_html__( 'Show on Posts?', 'pagoda' ),
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);



				/////////////////////////////////////////////////////////////////////
				// Icon Layout
				$data_group = array(
					'id' 	=> 'layout',
					'group'	=> 'pagoda_scroll_',
					'label'	=> esc_html__('Scroll to Top Layout Settings','pagoda'),
					'desc'	=> false,
					'color'	=> 'blue'
				);
				$data_keys = array(
					'position'	=> array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Position (CSS units)', 'pagoda' ),
				        'desc'	=> esc_html__( 'How far from the bottom right corner the icon appears.' , 
					       	'pagoda' ),
					),
					'ic_size' => array( 
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
				        'label'	=> esc_html__( 'Icon Size', 'pagoda' ),
					),
					'fa_size' => array( 
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Font Size', 'pagoda' ),
			        	'desc'	=> __( 'This determines the size of arrow element within the icon.', 
			        		'pagoda'),				
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				/////////////////////////////////////////////////////////////////////
				// Icon Color Palette
				$data_group = array(
					'id'	=> 'color_palette',
					'group' => 'pagoda_scroll_',
					'label'	=> esc_html__('Scroll to Top Icon Color Palette', 'pagoda'),
					'desc'	=> false,
					'color'	=> 'green'
				);
				$data_keys = array(
					'bg_color' => array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Background Color','pagoda'), 
					),
					'bg_hover' => array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Background Color on Hover','pagoda'), 
					),
					'fa_color' => array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Arrow Element Color','pagoda'), 
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				/////////////////////////////////////////////////////////////////////
				// Icon Border
				$data_group = array(
					'id' 	=> 'border',
					'group'	=> 'pagoda_scroll_bd_',
					'label'	=> esc_html__('Icon Border Settings','pagoda'),
					'desc'	=> false,
					'color'	=> 'yellow'
				);
				$data_keys = array(

					'inset'		=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Render Border as Inset?', 'pagoda'),
						'desc'	=> esc_html__('Note that this has no effect for borders when the fade property is set to 0px.', 'pagoda'),
					),
					'radius'	=> array(
						'type'	=> 'text',
						'sanitize' =>'sanitize_css_unit_value',
						'label'	=> esc_html__('Border Radius (CSS Units)', 'pagoda'),
					),
					'size' 		=> array(
						'type'	=> 'text',						
						'label'	=> esc_html__('Border Size (CSS Units)', 'pagoda'),
						'sanitize'=>'sanitize_css_unit_value',
					),
					'fade'		=> array(
						'type'	=> 'text',
						'label' => esc_html__('Border Fade (CSS Units)' ,'pagoda'),
						'sanitize'=>'sanitize_css_unit_value',	
					),
					'color'		=> array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Border Color','pagoda'),
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

		}
	}