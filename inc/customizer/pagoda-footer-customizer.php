<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly	

	class pagoda_footer_customizer 
	{

		 public function __construct($defaults, $font_gen) {   

	   		$this->defaults = $defaults; // From JSON Options Table
			$this->font_gen = $font_gen; // The single instance of nabla_pagoda_fonts_autogen()

			// Instantiate our sanitize and register classes
			$this->sanitize_data = new Nablafire_Customize_Data_Sanitize();
			$this->register_data = new Nablafire_Customize_Data_Register($this->sanitize_data);
			
			// Font control
			$this->sanitize_font = new Nablafire_Customize_Font_Sanitize($this->font_gen);
			$this->register_font = new Nablafire_Customize_Font_Register($this->sanitize_font);

			// Register our customizer
			add_action( 'customize_register',  array( $this, 'register_options' ) );
			
		}

		function register_options( $wp_customize ) {

 			// Main Panel
			$panel = 'pagoda-footer-settings';
	 		$wp_customize->add_panel( $panel, array(
				'title'		=> esc_html__( 'Pagoda Footer Settings', 'pagoda' ),
				'priority'	=> 30,
				
			) ); 
	 		$this->register_footer_display_options($wp_customize, $panel, $this->defaults['display']); 
	 		$this->register_social_display_options($wp_customize, $panel, $this->defaults['socials']);
			$this->register_social_data_options($wp_customize, $panel, $this->defaults['socials']['data']); 
	 	}

		//////////////////////////////////////////////////////////////////////////////////////
		//						 FOOTER DISPAY CUSTOMIZER METHOD							//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "display" : {
		//		"pagoda_footer_bg_color"		: "#000",				
		//		"pagoda_footer_bg_image"		: "",				
		//		"pagoda_footer_tl_text"			: "Pagoda - Designed in Scandinavia",
		//		"pagoda_footer_tl_padding"		: "0px 0px 0px 0px",
		//		"pagoda_footer_tf_font_fam"		: "Montserrat",
		//		"pagoda_footer_tf_font_var"		: "regular",
		//		"pagoda_footer_tf_font_size"	: "20",
		//		"pagoda_footer_tf_font_color"	: "#fff",
		//		"pagoda_footer_sb_text"			: "©2017 by Nablafire",
		//		"pagoda_footer_sb_padding"		: "0px 0px 0px 0px",
		//		"pagoda_footer_sf_font_fam"		: "Montserrat",
		//		"pagoda_footer_sf_font_var"		: "regular",
		//		"pagoda_footer_sf_font_size"	: "16",
		//		"pagoda_footer_sf_font_color"	: "#fff"
		// },
	 	//
		function register_footer_display_options($wp_customize, $panel, $defaults){

			// Title Group Options
	 		$section = 'footer-display-settings';
			$wp_customize->add_section( $section, array(
				'title'			=> esc_html__( 'Footer Display Settings' , 'pagoda'  ),
				'priority'		=> 30,
				'panel'			=> $panel,
			) ); 

				/////////////////////////////////////////////////////////////////////
				// Background Color
				$data_group = array(
					'id'	=> 'color_group',
					'group' => 'pagoda_footer_bg_',
					'label'	=> false,
					'desc'	=> false,
					'color'	=> 'green',
				);
				$data_keys = array( 
					'color'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Footer Default Background Color','pagoda'), 
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				/////////////////////////////////////////////////////////////////////
				// Background Image
				$option = 'pagoda_footer_bg_image';
				$wp_customize->add_setting( $option, array(
					'type'			=> 'option',
	   				'transport'		=> 'postMessage',
	   	 			'default'		=> $defaults[$option],
			 		'sanitize_callback' => array($this->sanitize_data, 'sanitize_image')
				) );
				$wp_customize->add_control( new WP_Customize_Image_Control( 
					$wp_customize, $option, 
					array(
						'label'			=> esc_html__( 'Select Background Image', 'pagoda' ),
						'description'	=> esc_html__( 'If no image is selected then the background will default to the background color', 'pagoda'),
						'section'		=> $section,
						'priority'		=> 2,
					)
				) );

   				/////////////////////////////////////////////////////////////////////
				// Title Properties 
				$data_group = array(
					'id'	=> 'color_group',
					'group' => 'pagoda_footer_tl_',
					'label'	=> false,
					'desc'	=> false,
					'color'	=> 'red',
				);
				$data_keys  = array(
					'text' => array(
						'label'	  =>  esc_html__('Footer Title','pagoda'), 
						'type'	  => 'text',
						'sanitize'=> 'sanitize_text_field'
					),
					'padding' => array(
						'label'	  =>  esc_html__('Title Padding (CSS shorthand)','pagoda'), 
						'type'	  => 'text',
						'sanitize'=> 'sanitize_css_padding_shorthand'
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
				
				// Title Font Control 
				$data_group  = array(
					'id'	=> 'font',
					'group'	=> 'pagoda_footer_tf_',
					'label'	=> esc_html__( 'Title Font Control', 'pagoda' ),
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
				// Subtitle Properties 
				$data_group = array(
					'id'	=> 'color_group',
					'group' => 'pagoda_footer_sb_',
					'label'	=> false,
					'desc'	=> false,
					'color'	=> 'red',
				);
				$data_keys  = array(
					'text' => array(
						'label'	  =>  esc_html__('Footer Subtitle','pagoda'), 
						'type'	  => 'text',
						'sanitize'=> 'sanitize_text_field'
					),
					'padding' => array(
						'label'	  =>  esc_html__('Subtitle Padding (CSS shorthand)','pagoda'), 
						'type'	  => 'text',
						'sanitize'=> 'sanitize_css_padding_shorthand'
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
				
				// Subtitle Font Control 
				$data_group  = array(
					'id'	=> 'font',
					'group'	=> 'pagoda_footer_sf_',
					'label'	=> esc_html__( 'Subtitle Font Control', 'pagoda' ),
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
		//					  	SOCIALS DISPAY CUSTOMIZER METHOD							//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "socials" : {
		//		"pagoda_footer_social_control"		: "",
		//		"pagoda_footer_social_block_size"	: "30px",			
		//		"pagoda_footer_social_font_size"	: "20px",
		//		"pagoda_footer_social_padding"		: "0px 0px 0px 0px",
		//		"pagoda_footer_social_display"		: "between",
		//		"pagoda_footer_social_color"		: "#fff",
		//		"pagoda_footer_social_hover"		: "#666",
		// }
		//
		function register_social_display_options($wp_customize, $panel, $defaults){
			// Title Group Options
	 		$section = 'social-icon-display-settings';
			$wp_customize->add_section( $section, array(
				'title'			=> esc_html__( 'Social Icon Display Settings' , 'pagoda'  ),
				'priority'		=> 30,
				'panel'			=> $panel,
			) ); 


				/////////////////////////////////////////////////////////////////////
				// Social Icon Properties 				
				$data_group = array(
					'id'	=> 'control',	
					'group' => 'pagoda_footer_social_',
					'label' => false,
					'desc'  => false,
					'color'	=> 'red',
				);
				$data_keys = array( 
					'control'=> array( // Want this in a BIG RED BOX
						'type'	=> 'checkbox',
						'sanitize' => 'sanitize_checkbox',
						'label'	=> esc_html__( 'Social Icons Master Switch', 'pagoda' ),
						'desc'	=> esc_html__( 'This is a global switch which will turn on or off all social media icons in the footer regardless of settings.', 'pagoda'), 
				) );	
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				/////////////////////////////////////////////////////////////////////
				// Social Icon Size Data in Value Control
				$data_group = array(
					'id'	=> 'layout',
					'group'	=> 'pagoda_footer_social_',
					'label' => esc_html__('Social Icon Layout' , 'pagoda'),
					'desc'	=> false,
					'color' => 'blue'
				);
				$data_keys = array( 
					'block_size'=> array( 
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_unit_value',
						'label' => esc_html__('Social Icon Block Size','pagoda'), 
						'desc'	=> esc_html__('This value sets the size of the container that individual social media icons are rendered in.', 'pagoda'), 
					),
					'font_size'=> array( 
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_unit_value',
						'label' => esc_html__('Social Icon Font Size','pagoda'), 
						'desc'	=> esc_html__('Social Icons will be vertically centered within their block containers.', 'pagoda') 
					),
					'display' => array(
						'type'	 => 'select',
						'values' => array('above', 'between', 'below'),
						'sanitize'	=> 'sanitize_dropdown', 
						'label'	=> esc_html__('Social Icon Positioning', 'pagoda'),
						'desc'	=> esc_html__('Select the positioning of the social media icons relative to the footer text and subtext', 'pagoda')
					),
					'padding' => array(
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_padding_shorthand',
						'label' => esc_html__('Container Padding', 'pagoda'),
						'desc'	=> esc_html__('Select the padding of the social icon container', 
							'pagoda')
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
				
				/////////////////////////////////////////////////////////////////////
				// Social icons Color Palette
				$data_group = array(
					'id'	=> 'color_palette',
					'group' => 'pagoda_footer_social_',
					'label'	=> esc_html__( 'Social Icon Color Palette', 'pagoda' ),
					'desc'	=> esc_html__('Select social icon color and color on hover. This color palette applies to all icons', 'pagoda'),
					'color'	=> 'green'
				);
				$data_keys = array( 
					'color'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Icon Color','pagoda'), 
					),
					'hover'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Hover Color','pagoda'), 
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
		}

		//////////////////////////////////////////////////////////////////////////////////////
		//				    	    SOCIALS DATA CUSTOMIZER METHODS							//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "Facebook" : {"show" : "1", "link" : "#", "label" : "facebook", "icon" : "fa-facebook-square"},
		// "Twitter"  : {"show" : "1", "link" : "#", "label" : "twitter" , "icon" : "fa-twitter-square" },
		//  ...
		//
		function register_social_data_options($wp_customize, $panel, $defaults){

			// Title Group Options
	 		$section = 'social-icon-data-settings';
			$wp_customize->add_section( $section, array(
				'title'			=> esc_html__( 'Social Icon Data Settings' , 'pagoda'),
				'priority'		=> 30,
				'panel'			=> $panel,
			) ); 

			foreach( $defaults as $id => $_data ){

				$data_group = array(
					'iterator' => $_data['label'],
					'id'	=> $_data['label'],
					'group' => 'pagoda_footer_socials_array_',
					'label'	=> false, // No label
					'desc'	=> false, // No description
					'keyed' => true,  // We store defaults in an array 
					'color' => 'grey' // 	e.g. $_data['link']
				);
				$data_keys = array( 
					'link' => array(
						'type'	  => 'text',
						'sanitize'=> 'sanitize_url', 
						'label' 	  => esc_html__( 'Link to your', 'pagoda') . 
				 			' ' . $id . ' ' . esc_html__( 'Page', 'pagoda'),
					),
					'show' => array(
						'type'	  => 'checkbox',
						'sanitize'=> 'sanitize_checkbox',
						'label'	  => esc_html__('Show This Icon?','pagoda'), 
				) );	
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $_data);

			} // Close socials loop
		}// Close function 
	}// End Class
?>