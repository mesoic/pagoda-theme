<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	class pagoda_header_customizer { 
	   	
	    public function __construct($defaults, $font_gen, $settings) {	    

	   		$this->defaults = $defaults; // From JSON Options Table
	    	$this->settings = $settings; // Produced on settings page
	    	$this->font_gen = $font_gen; // The single instance of nabla_pagoda_fonts_autogen()

			// Data Control 
			$this->sanitize_data = new Nablafire_Customize_Data_Sanitize();
			$this->register_data = new Nablafire_Customize_Data_Register($this->sanitize_data);
			
			// Font control
			$this->sanitize_font = new Nablafire_Customize_Font_Sanitize($this->font_gen);
			$this->register_font = new Nablafire_Customize_Font_Register($this->sanitize_font);

	    	//register our customizer
			add_action( 'customize_register',  array( $this, 'register_options' ) );
	    }

		function register_options( $wp_customize ) {

 			// Main Panel
			$panel = 'pagoda-header-settings';
	 		$wp_customize->add_panel( $panel, array(
        		'title'             => esc_html__( 'Pagoda Header Settings', 'pagoda' ),
        		'priority'          => 30,
    			
    		) ); 
	 		// Submethods to register sections
			$this->register_display_u_options($wp_customize, $panel, $this->defaults['display']['u']);
			$this->register_display_i_options($wp_customize, $panel, $this->defaults['display']['i']);
			$this->register_banner_a_options($wp_customize, $panel, $this->defaults['banner']);      
			$this->register_button_u_options($wp_customize, $panel, $this->defaults['buttons']['u']);
			$this->register_button_i_options($wp_customize, $panel, $this->defaults['buttons']['i']);
		}

		//////////////////////////////////////////////////////////////////////////////////////
		//					  		DISPLAY CUSTOMIZER METHODS								//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		//	"display" : {
		//		"u" : {
 		//			"pagoda_header_show_image"		: "1",
 		//			"pagoda_header_show_title"		: "1",
 		//			"pagoda_header_show_subtitle"	: "1",
 		//			"pagoda_header_show_buttons"	: "1",
		//			"pagoda_splash_color" 			: "#666",
		//			"pagoda_splash_height"			: "100vh",
		//			"pagoda_splash_displace"		: "0",
		//			"pagoda_splash_opacity"			: "100",
 		//			"pagoda_splash_contain"			: "",
 		//			"pagoda_splash_parallax"		: "",
 		//			"pagoda_splash_animate"			: "",
 		//			"pagoda_splash_delta"			: "8000"
		//	},
		//
		function register_display_u_options($wp_customize, $panel, $defaults){

			// Background Settings
	 		$section = 'header-configuration';
			$wp_customize->add_section( $section, array(
        		'title'         => esc_html__( 'Header Configuration', 'pagoda'  ),
        		'priority'      => 30,
        		'panel'  		=> $panel,
    		) ); 

				/////////////////////////////////////////////////////////////////////
				// Background Display Elements
				$data_group = array( 
					'id'	=> 'switches',
					'group'	=> 'pagoda_header_show_',
					'label'	=> esc_html__('Front Page Header Display Options', 'pagoda'), 
					'desc'	=> esc_html__('Select the elements you would like to display within the front page header', 'pagoda'), 
					'color'	=> 'red',
				);
				$data_keys = array(
					
					'image'		=> array(
						'type'	=> 'checkbox', 
						'sanitize' => 'sanitize_checkbox',
						'label'	=> esc_html__( 'Show Banner Image?', 'pagoda' ),

					),
					'title' 	=> array(
						'type'	=> 'checkbox', 
						'sanitize' => 'sanitize_checkbox',
						'label'	=> esc_html__( 'Show Main Title?', 'pagoda' ),
					),	
					'subtitle'  => array(
						'type'	=> 'checkbox', 
						'sanitize' => 'sanitize_checkbox',
						'label'	=> esc_html__( 'Show Subtitle?', 'pagoda' ),
					),
					'buttons'  	=> array(
						'type'	=> 'checkbox', 
						'sanitize' => 'sanitize_checkbox',
						'label'	=> esc_html__( 'Show Buttons?', 'pagoda' ),
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				/////////////////////////////////////////////////////////////////////
				// Background Main Setup Switches
				$data_group = array( 
					'id'	=> 'background',
					'group'	=> 'pagoda_splash_',
					'label'	=> false, 
					'desc'	=> false, 
					'color'	=> 'blue',
				); 
				$data_keys = array(
					'color' 	  => array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Background Color','pagoda'), 
						'desc'	  => esc_html__('When no header image settings are assigned, the header background defaults to this color', 'pagoda')
					), 
					'height'=> array(
						'type'	=> 'text',
						'sanitize'	=> 'sanitize_css_unit_value',
						'label'	=> esc_html__('Front Page Header Height', 'pagoda'),
						'desc'	=> esc_html__('Adjust front page header height. This may require adjustment of the banner section placement (see Title and Banner panel)', 
							'pagoda')
					),
					'displace' => array(
						'type'	=> 'number',
						'range' => array ( 'min' => 0, 'max' => 100 ),
						'sanitize' => 'sanitize_number_range', 
						'label'	=> esc_html__('Image Positioning (%)', 'pagoda'),
						'desc'	=> __('Adjust the horizontal positioning of your background images <em>(0-100%)</em>.', 'pagoda'),
					),
					'contain' => array(
						'type'	=> 'checkbox', 
						'sanitize' => 'sanitize_checkbox',
						'label'	=> esc_html__( 'Contain Images?', 'pagoda' ),
						'desc'	=> esc_html__( 'Scale background images to fill header area?', 
							'pagoda'),	
					), 	
					'parallax' => array(
						'type'	=> 'checkbox', 
						'sanitize' => 'sanitize_checkbox',
						'label'	=> esc_html__( 'Parallax Effect?', 'pagoda' ),
						'desc'	=> esc_html__( 'Parallax effect will not be applied to animated image sets.', 'pagoda'),	
					), 
					'animate' => array(					
						'type'	=> 'checkbox', 
						'sanitize' => 'sanitize_checkbox',
						'label'	=> esc_html__( 'Animate Image Set?', 'pagoda' ),
						'desc'	=> esc_html__( 'The animated background has the highest priority in background rendering. If this is selected all static and parallax settings assigned in the Background Settings and Background Images panels will be ignored', 'pagoda')
					),
					'delta'		=> array( 
						'type'	=> 'number',
						'range' => array ( 'min' => 500, 'max' => 20000 ),
						'sanitize' => 'sanitize_number_range',
						'label'	=> esc_html__( 'Animation Timestep', 'pagoda' ),
						'desc'	=> esc_html__( 'Number of milliseconds per image (ms)', 
							'pagoda'),
					),
					'opacity' => array(					
						'type'	=> 'number', 
						'sanitize' => 'sanitize_number_range',
						'range' => array ( 'min' => 5, 'max' => 100 ),
						'label'	=> esc_html__( 'Animation Opacity (%)', 'pagoda' ),
						'desc'	=> esc_html__( 'This determines the header image opacity for animated image sets.', 'pagoda')
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
		}
			
		/////////////////////////////////////////////////////////////////////
		// Iterated Background options (Image/Static Switch)
		//
		// "i" : {				
		//		"pagoda_splash_image_" 		: "",
 		//		"pagoda_splash_switch_" 	: ""
 		// }
 		//
		function register_display_i_options($wp_customize, $panel, $defaults){

			$section = 'background-images';
			$wp_customize->add_section( $section, array(
    	   		'title'         => esc_html__( 'Background Images', 'pagoda' ),
    	   		'priority'      => 30,
    	   		'panel'  		=> $panel,
    		) ); 

		 		for ($id = 1; $id <= $this->settings['n_images']; $id++) {
				
					/////////////////////////////////////////////////////////////////////
					// Background Images
					$option = 'pagoda_splash_image_';
					$wp_customize->add_setting( $option.$id, array(
        				'type'          => 'option',
       					'transport'     => 'postMessage',
       	 				'default'		=> $defaults[$option],
       	 				'sanitize_callback' => array($this->sanitize_data, 'sanitize_image')
	    			) );
					$wp_customize->add_control( new WP_Customize_Image_Control( 
						$wp_customize, $option.$id, 
						array(
							'label'       => esc_html__( 'Select Background Image', 'pagoda' ) . ' ' . $id,
							'description' => esc_html__( 'Background Image', 'pagoda'),
							'section'     => $section,
							'priority'	  => 2,
						)
					) );

					/////////////////////////////////////////////////////////////////////
					// Static Image Switches	
					$data_group = array(
						'iterator'	=> $id, 
						'id'	=> 'image_switch_' . $id, 
						'group'	=> 'pagoda_splash_',
						'label' => false, 
						'desc'	=> false,
						'color' => 'green',
						'priority' => 2
					);
					$data_keys = array(
						'switch'	=> array(
							'type'	=> 'checkbox', 
							'sanitize' => 'sanitize_checkbox', 
							'label'	=> esc_html__( 'Static Image?', 'pagoda' ),
							'desc'	=> esc_html__( 'Use this image as static or parallax background', 
								'pagoda')
					) );
					$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
				} // Close for
		}

		//////////////////////////////////////////////////////////////////////////////////////
		//						      BANNER CUSTOMIZER METHOD 								//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "banner" : {
		//		"pagoda_banner_img_source"		: "",				
		//		"pagoda_banner_img_height"		: "130px",
		//		"pagoda_banner_img_push"		: "25vh",
		//		"pagoda_banner_mn_text"			: "Pagoda",
		//		"pagoda_banner_mn_margin"		: "0px 0px 0px 0px",
		//		"pagoda_banner_mn_font_fam"		: "Montserrat",
		//		"pagoda_banner_mn_font_var"		: "regular",
		//		"pagoda_banner_mn_font_size"	: "32",
		//		"pagoda_banner_mn_font_color"	: "#fff",
		//		"pagoda_banner_sb_text"			: "Engineered by Nablafire",
		//		"pagoda_banner_sb_margin"		: "0px 0px 10px 0px",
		//		"pagoda_banner_sb_font_fam"		: "Montserrat",
		//		"pagoda_banner_sb_font_var"		: "regular",
		//		"pagoda_banner_sb_font_size"	: "16",
		//		"pagoda_banner_sb_font_color"	: "#fff"
		// },
		//
		function register_banner_a_options($wp_customize, $panel, $defaults){

			// Title Group Options
	 		$section = 'title-and-banner-settings';
			$wp_customize->add_section( $section, array(
        		'title'         => esc_html__( 'Title and Banner Settings', 'pagoda'  ),
        		'description'   => esc_html__( 'Title, Image, and Subtitle Settings', 'pagoda'  ),
        		'priority'      => 30,
        		'panel'  		=> $panel,
    		) ); 
		
				/////////////////////////////////////////////////////////////////////	
				// Banner Image
				$option = 'pagoda_banner_img_source';
				$wp_customize->add_setting( $option, array(
        			'type'          => 'option',
       				'transport'     => 'postMessage',
       	 			'default'		=> $defaults[$option],
       	 			'sanitize_callback' => array($this->sanitize_data, 'sanitize_image')
	    		) );
				$wp_customize->add_control( new WP_Customize_Image_Control( 
					$wp_customize, $option, 
					array(
						'label'       => esc_html__( 'Select Banner Image ', 'pagoda' ),
						'description' => esc_html__( 'The banner image will be center aligned', 
							'pagoda'),
						'section'     => $section,
						'priority'	  => 2,
					)
				) );

				/////////////////////////////////////////////////////////////////////	
				// Banner Layout Properties (includes page push)
				$data_group = array(
					'id'	=> 'layout',
					'group'	=> 'pagoda_banner_img_', 
					'label'	=> false, 
					'desc'	=> false, 
					'color' => 'blue',
					'priority' => 2,
				);
				$data_keys = array(
					'height' 	=> array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label' =>  esc_html__( 'Banner Image Height (CSS Units)', 'pagoda' )
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				/////////////////////////////////////////////////////////////////////	
				// Title Data
				$data_group = array(
					'id'	=> 'title',
					'group'	=> 'pagoda_banner_mn_',
					'label'	=> false, 
					'desc'	=> false,
					'color' => 'orange',
					'priority' =>10
				);
				$data_keys = array(
					'text' => array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_text_field',
						'label'	=> esc_html__( 'Main Title', 'pagoda' ),
					),
					'margin' => array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_margin_shorthand',
						'label'	=> esc_html__( 'Main Title Margin', 'pagoda' ),
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				/////////////////////////////////////////////////////////////////////	
				// Title Font Control
				$data_group  = array(
					'id'	=> 'font',
					'group'	=> 'pagoda_banner_mn_',
					'label'	=> esc_html__( 'Title Font Control', 'pagoda' ),
					'desc'	=> false,
					'priority' =>10
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
				// Subtitle Data
				$data_group = array(
					'id'	=> 'title',
					'group'	=> 'pagoda_banner_sb_',
					'label'	=> false, 
					'desc'	=> false,
					'color' => 'orange',
					'priority' =>20
				);
				$data_keys = array(
					'text' => array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_text_field',
						'label'	=> esc_html__( 'Subtitle', 'pagoda' ),
					),
					'margin' => array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_margin_shorthand',
						'label'	=> esc_html__( 'Subtitle Margin', 'pagoda' ),
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				/////////////////////////////////////////////////////////////////////	
				// Title Font Control
				$data_group  = array(
					'id'	=> 'font',
					'group'	=> 'pagoda_banner_sb_',
					'label'	=> esc_html__( 'Subtitle Font Control', 'pagoda' ),
					'desc'	=> false,
					'priority' =>20
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
		//						 	 BUTTON CUSTOMIZER METHODS								//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "buttons": {
		//		"u" : {
		//			"pagoda_button_mn_padding"	: "10px 10px 10px 10px",
 		//			"pagoda_button_mn_margin"	: "10px 10px 10px 10px",
		//			"pagoda_button_mn_height"	: "50px",
		//			"pagoda_button_mn_width"	: "100px",
		//			"pagoda_button_mn_delta" 	: "500",
		//			"pagoda_button_bd_show"		: "1",
		//			"pagoda_button_bd_inset"	: "",
		//			"pagoda_button_bd_radius"	: "0px",
		//			"pagoda_button_bd_size"		: "2px",
		//			"pagoda_button_bd_fade"		: "0px",
		//			"pagoda_button_bd_color"	: "#eee",
 		//			"pagoda_button_font_fam"	: "Montserrat",
		//			"pagoda_button_font_var"	: "regular",
		//			"pagoda_button_font_size"	: "16",
		//			"pagoda_button_font_color"	: "#fff"
		//		},
		//
		function register_button_u_options($wp_customize, $panel, $defaults){

			// If we have zero buttons, do not render this at all 
			if ($this->settings['n_buttons'] < 1){return;}

			// Button Group Options
	 		$section = 'button-settings';
			$wp_customize->add_section( $section, array(
        		'title'         => esc_html__( 'Button Settings' , 'pagoda'  ),
        		'priority'      => 30,
        		'panel'  		=> $panel,
    		) ); 

				/////////////////////////////////////////////////////////////////////
				// Button Layout Options
				$data_group = array(
					'id' 	=> 'layout',
					'group' => 'pagoda_button_mn_',
					'label'	=> esc_html__( 'Button Layout Settings' , 'pagoda'), 					
					'desc'	=> esc_html__( 'These settings will be applied to all buttons', 
						'pagoda'),
					'color' => 'blue',
				);
				$data_keys = array( 
					'padding'	=> array(
						'type' 	=> 'text', 
						'sanitize' => 'sanitize_css_padding_shorthand',
						'label'	=> esc_html__( 'Button Padding (CSS shorthand)', 'pagoda' ),
					),
					'margin'	=> array(
						'type' 	=> 'text', 
						'sanitize' => 'sanitize_css_margin_shorthand',
						'label'	=> esc_html__( 'Button Margin (CSS shorthand)', 'pagoda' ),
					),
					'height' 	=> array(
						'type' 	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value', 
						'label'	=> esc_html__( 'Button Height (CSS Units)', 'pagoda' ),
						'desc'	=> esc_html__( 'This property also determines the button text line height. All labels will be vertically centered in their containers', 'pagoda' ) 
					),
					'width'	=> array(
						'type' => 'text', 
						'sanitize' => 'sanitize_css_unit_value', 
						'label'	=> esc_html__( 'Button Width (CSS Units)', 'pagoda' ),
						'desc'	=> esc_html__( 'This option can be used to fix the button width. If 0 is entered, then the width will be calculated based on the label text.', 
							'pagoda' )
					),
					'delta'	=> array(
						'type' 	=> 'number',
						'sanitize'	=> 'sanitize_number_range',
						'range'	=> array( 'min' => 100, 'max' => 1000), 	
						'label'	=> esc_html__( 'Button Transition Time (ms)' ,'pagoda' ), 
						'desc'	=> esc_html__( 'This value determines the transition speed betwen the default and hover color palettes. Color palettes can be individually assigned for each button.' , 'pagoda'),
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				/////////////////////////////////////////////////////////////////////
				// Button Style Options
				$data_group = array(
					'id'	=> 'style',
					'group' => 'pagoda_button_bd_',
					'label'	=> esc_html__('Button Style Properties', 'pagoda'),  
					'desc'	=> false, 
					'color'	=> 'yellow'
				);
				$data_keys = array( 
					
					'show'		=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Show Border Elements?', 'pagoda'),
						'desc'	=> esc_html__('To display only the border radius property, set border size to 0px.','pagoda'),
					),
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

				/////////////////////////////////////////////////////////////////////
				// Button Layout Options
				$data_group  = array(
					'id'	=> 'font',
					'group'	=> 'pagoda_button_',
					'label'	=> esc_html__( 'Button Font Control', 'pagoda' ),
					'desc'	=> esc_html__( 'This font will be applied to all buttons', 'pagoda')
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

		/////////////////////////////////////////////////////////////////////
		// Button Iterated Properties 
		//
		// "i" : {
		//		"pagoda_button_data_label_" : "Button ",
		//		"pagoda_button_data_link_" 	: "#",	
		//		"pagoda_button_bg_color_"	: "#666",
		//		"pagoda_button_bg_hover_"	: "#000"
		//	}
		//
		function register_button_i_options($wp_customize, $panel, $defaults){

			// If we have zero buttons, do not render this at all 
			if ($this->settings['n_buttons'] < 1){return;}
	 	
	 		for ($id = 1; $id <= $this->settings['n_buttons']; $id++) {
	 			// Background Settings
	 			$section = 'button-'.$id.'-data';
				$wp_customize->add_section($section, array(
        			'title'         => esc_html__( 'Button' , 'pagoda'  ) . 
        				' ' . $id . ' ' . esc_html__( 'Data' , 'pagoda'  ),
        			'priority'      => 30,
        			'panel'  		=> $panel,
    			) ); 

    				/////////////////////////////////////////////////////////////////////
					// Button Data Options (label/link)	
					$data_group = array( 
						'iterator' => $id, 
						'id'	=> $id, 
						'group'	=> 'pagoda_button_data_',
						'label' => false,
						'desc'	=> false, 
						'color'	=> 'grey'
					);
					$data_keys = array(
						'label' 	=> array( 
							'type'	=> 'text', 
							'sanitize'	=> 'sanitize_text_field', 
							'label'	=>  esc_html__('Button', 'pagoda') . ' ' . $id . ' ' . 
								esc_html__('Label','pagoda'),
						),
						'link'		=> array(
							'type'	=> 'text',
							'sanitize'	=> 'sanitize_url',
							'label'	=>  esc_html__('Button', 'pagoda') . ' ' . $id . ' ' . 
								esc_html__('Link','pagoda'),
					) );
					$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);


					/////////////////////////////////////////////////////////////////////	
					// Background Color 
					$data_group = array( 
						'iterator' => $id, 
						'id'	=> 'color' . $id, 
						'group'	=> 'pagoda_button_',
						'label' => esc_html__('Button', 'pagoda') . ' ' . $id . ' ' . 
							esc_html__(' Color Palette','pagoda'),
						'desc' 	=> false, 	 
						'color'	=> 'green'
					);
					$data_keys = array(
						'bg_color' => array( 
							'type'	  => 'color',
							'default' => false, // Assigned in loop
							'sanitize'=> 'sanitize_alpha_color',
							'label'	  => esc_html__('Background Color','pagoda'), 
						),
						'bg_hover' => array(
							'type'	  => 'color',
							'default' => false, // Assigned in loop
							'sanitize'=> 'sanitize_alpha_color',
							'label'	  => esc_html__('Color on Hover','pagoda'), 
					) );
					$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
	 		} // Close for
		}	
	} // End Class 
?>