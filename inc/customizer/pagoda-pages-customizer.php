<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly	

class pagoda_pages_customizer {

	 public function __construct($defaults, $font_gen, $page_tree) {

		$this->defaults = $defaults;	// From JSON Options Table
		$this->font_gen = $font_gen;	// The single instance of nabla_pagoda_fonts_autogen()

		// Data Control 
		$this->sanitize_data = new Nablafire_Customize_Data_Sanitize();
		$this->register_data = new Nablafire_Customize_Data_Register($this->sanitize_data);
		
		// Font control
		$this->sanitize_font = new Nablafire_Customize_Font_Sanitize($this->font_gen);
		$this->register_font = new Nablafire_Customize_Font_Register($this->sanitize_font);


		// And page_tree
		$this->page_tree  = $page_tree;

		// Register our customizer
		add_action( 'customize_register',  array( $this, 'register_options' ) );
	}

	public function register_options($wp_customize){

		$panel = 'pagoda-page-settings';
 		$wp_customize->add_panel( $panel, array(
			'title'			=> esc_html__('Pagoda Page Settings', 'pagoda' ),
			'priority'		=> 30,	
		) ); 
 		
 		// Register display and data options
		$this->register_display_options($wp_customize, $panel, $this->defaults['display']);
		$this->register_data_options( $wp_customize, $panel, $this->defaults['data']);
	
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//					 		PAGE MAIN DISPLAY SETTINGS								//
	//////////////////////////////////////////////////////////////////////////////////////
	//
	//	"display" : {
	//		"pagoda_page_display_mn_height"		: "200px",
	//		"pagoda_page_display_mn_padding"	: "20px 50px",
	//		"pagoda_page_display_mn_content_a"	: "20px 10px",
	//		"pagoda_page_display_mn_content_w"	: "40px 0px",
	//		"pagoda_page_display_mn_article"	: "10px 60px",
	//		"pagoda_page_display_mn_align"		: "left",
	//		"pagoda_page_display_h_font_fam"	: "Montserrat",
	//		"pagoda_page_display_h_font_var"	: "regular",
	//		"pagoda_page_display_p_font_fam"	: "Montserrat",
	//		"pagoda_page_display_p_font_var"	: "regular",
	//		"pagoda_page_display_a_font_var"	: "700",
	//		"pagoda_page_display_b_font_var"	: "700",
	//		"pagoda_page_display_p_font_size"	: "18",
	//		"pagoda_page_display_p_meta_size"	: "14"
	// },
	//
	public function register_display_options($wp_customize, $panel, $defaults){

 		$section = 'page-display-settings';
		$wp_customize->add_section( $section, array(
			'title'		 => esc_html__( 'Page Display Settings', 'pagoda'  ),
			'priority'	  => 30,
			'panel'  		=> $panel,
		) ); 

			/////////////////////////////////////////////////////////////////////
			// Content Area Layout
			$data_group = array(
				'id'	=> 'layout',
				'group'	=> 'pagoda_page_display_mn_',
				'label' => esc_html__('Content Area Layout', 'pagoda'),
				'desc'  => false,
				'color'	=> 'blue',
			);	
			$data_keys = array( 

				'height' 	=>	array(
					'type' 	=> 'text',
					'sanitize' => 'sanitize_css_unit_value',
					'label'	=> esc_html__( 'Set Page Header Height (CSS Units)', 'pagoda' ),
					'desc'  => esc_html__( 'This setting will apply to all pages', 'pagoda'),
				),
				'padding'	=> array( 
					'type'	=> 'text',
					'sanitize' => 'sanitize_css_padding_shorthand',
					'label' => esc_html__('Main Padding (CSS Shorthand)','pagoda'), 
					'desc'	=> esc_html__('This setting controls how far the content area and sidebar(s) appear from the header(top), viewport edges(sides), and footer(bottom) of the page.', 'pagoda'), 
				),
				'content_a'	=> array( 
					'type'	=> 'text',
					'sanitize' => 'sanitize_css_padding_shorthand',
					'label' => esc_html__('Page Wrap Padding (CSS Shorthand)','pagoda'), 
					'desc'	=> esc_html__('The padding of the entire content area relative to its neighbors (e.g sidebars)', 'pagoda'), 
				),
				'content_w'	=> array(
					'type'	=> 'text',
					'sanitize' => 'sanitize_css_padding_shorthand',
					'label' => esc_html__('Content Padding (CSS Shorthand)','pagoda'), 
					'desc'	=> esc_html__('The padding of your content relative to the content area.', 'pagoda') 
				),
				'article'	=> array( 
					'type'	=> 'text',
					'sanitize' => 'sanitize_css_padding_shorthand',
					'label' => esc_html__('Inter-Page Padding (CSS Shorthand)','pagoda'), 
					'desc'	=> esc_html__('The padding assigned the page\'s <article> tags. This setting is useful on pages with multiple posts (i.e. archive, search, etc.)', 'pagoda') 
				),
				'align'		 => array(
					'type'	 => 'select',
					'values' =>array('left', 'center', 'right', 'justify'),
					'sanitize'	=> 'sanitize_dropdown', 
					'label'	 => esc_html__( 'Text Alignment', 'pagoda' ),
					'desc'	=> esc_html__('Alignment will apply to all items within the content area', 
						'pagoda') 	
			) );
			$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
			
			/////////////////////////////////////////////////////////////////////
			// h tag font control
			$data_group  = array(
				'id'	=> 'font',
				'group'	=> 'pagoda_page_display_h_',
				'label'	=> esc_html__( 'Header Font Control', 'pagoda' ),
			 	'desc'	=> esc_html__( 'This controls the font for all html header tags within the page content area (h1, h2, h3, h4, h5, h6)', 'pagoda'),
			);
			$data_keys   = array(
				'font_fam' 	=> array(
					'type'	=> 'font',
					'label' => esc_html__('Header Font Family', 'pagoda'),
					'sanitize' => 'sanitize_font_family',
				),
				'font_var'	=> array(
					'type'	=> 'variant',
					'font'	=> 'font_fam',
					'label' => esc_html__('Header Font Variant', 'pagoda'),
					'sanitize' => 'sanitize_font_variant'
			) );
			$this->register_font->control($wp_customize, $section, $data_group, $data_keys, $defaults);

			
			/////////////////////////////////////////////////////////////////////
			// p tag font control
			$data_group  = array(
				'id'	=> 'font',
				'group'	=> 'pagoda_page_display_',
				'label'	=> esc_html__( 'Text Font Control', 'pagoda' ),
				'desc' 	=> __( 'All available variants will be enqueued for the selected font. Use your own CSS to apply additional variants.', 'pagoda'),
			);
			$data_keys   = array(
				'p_font_fam' 	=> array(
					'type'	=> 'font',
					'label' => esc_html__('Text Font Family', 'pagoda'),
					'sanitize' => 'sanitize_font_family',
				),
				'p_font_var'	=> array(
					'type'	=> 'variant',
					'font'	=> 'p_font_fam',
					'label' => esc_html__('Text Font Variant', 'pagoda'),
					'sanitize' => 'sanitize_font_variant'
				),
				'a_font_var'=> array(
					'type'	=> 'variant',
					'font'	=> 'p_font_fam',
					'label' => esc_html__('Link Font Variant', 'pagoda'),
					'sanitize' => 'sanitize_font_variant'
				),
				'b_font_var'=> array(
					'type'	=> 'variant',
					'font'	=> 'p_font_fam',
					'label' => esc_html__('Bold Font Variant', 'pagoda'),
					'sanitize' => 'sanitize_font_variant'
				),
				'p_font_size'	=> array(
					'type'	=> 'size',
					'range'	=> array('min' => 8, 'max'=> 72), 
					'label'	=> esc_html__('Text Font Size (px)', 'pagoda'),
					'sanitize' => 'sanitize_font_size',
				),
				'p_blqt_size'=> array(
					'type'	=> 'size',
					'range'	=> array('min' => 8, 'max'=> 72), 
					'label'	=> esc_html__('Blockquote Font Size (px)', 'pagoda'),
					'sanitize' => 'sanitize_font_size',
				),
				'p_meta_size'	=> array(
				 	'type'	=> 'size',
					'range'	=> array('min' => 8, 'max'=> 72),
				 	'label'	=> esc_html__('Metadata Font Size (px)','pagoda'),
				 	'sanitize' => 'sanitize_font_size',
			) );
			$this->register_font->control($wp_customize, $section, $data_group, $data_keys, $defaults);	
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//					 PAGE DATA SETTINGS (HEADER & CONTENT AREA)						//
	//////////////////////////////////////////////////////////////////////////////////////
	//
	// 	"header" : {
	// 	 	"pagoda_page_styles_activate"	: "",
	// 		"pagoda_page_header_inherit_"	: "",
	// 		"pagoda_page_header_img_"		: "",			
	// 		"pagoda_page_header_bg_color_"	: "#000"
	// 	},
	//
	// 	"navbar" : {
	// 		"pagoda_page_navbar_nv_color_"	: "#666",
	// 		"pagoda_page_navbar_sc_color_" 	: "#000",
	// 		"pagoda_page_navbar_sr_color_" 	: "#000"
	// 	},
	//
	// 	"scroll" : {
	// 		"pagoda_page_scroll_bg_color_"	: "#000",				
	// 		"pagoda_page_scroll_bg_hover_"	: "#666", 
	// 		"pagoda_page_scroll_bd_color_"	: "#999",	
	// 		"pagoda_page_scroll_fa_color_"	: "#fff" 	
	// 	},
	//
	// 	"border" : {
	// 		"pagoda_page_border_show_"		: "",
	// 		"pagoda_page_border_inset_"		: "",
	// 		"pagoda_page_border_radius_"	: "0px",
	// 		"pagoda_page_border_size_"		: "5px",
	// 		"pagoda_page_border_fade_"		: "5px",
	// 		"pagoda_page_border_color_"		: "#eee"
	// 	},
	//
	// 	"content" : {
	// 		"pagoda_page_content_bg_color_" : "#eee",
	// 		"pagoda_page_content_mn_color_"	: "#fff",
	// 		"pagoda_page_content_h_color_"	: "#444",
	// 		"pagoda_page_content_p_color_"	: "#666",
	// 		"pagoda_page_content_a_color_"	: "#000",
	// 		"pagoda_page_content_a_hover_"	: "#666",
	// 		"pagoda_page_content_b_color_"	: "#999",
	//		"pagoda_page_content_in_color_"	: "#fff",
	//		"pagoda_page_content_in_hover_"	: "#eee"
	// 	}
	// 
	// "frames" : {
	//		"pagoda_post_frames_image_"		: "",
	//		"pagoda_post_frames_input_"		: "",
	//		"pagoda_post_frames_inset_"		: "",
	//		"pagoda_post_frames_radius_"	: "0px",
	//		"pagoda_post_frames_size_"		: "5px",
	//		"pagoda_post_frames_fade_"		: "5px",
	//		"pagoda_post_frames_color_"		: "#eee"
	// }
	//
	public function register_data_options($wp_customize, $panel, $defaults){
		
		// If the per-page data display option is activated and we have less than 
		// 12 toplevel pages, then assign $pages to the array of toplevel pages. 
		if ( ( strcmp( get_option('pagoda_page_styles_activate' ) , "1" ) == 0)  &&  
			 ( sizeof($this->page_tree->get_all_toplevel()) <= 12) ) {

			$pages = $this->page_tree->get_all_toplevel();
		}
		// Otherwise assign pages to "0". This will represent the default case. Note
		// that the zero setting should also apply to all auxillary pages e.g. 404 
		else {
   			$pages = array("default" => "0");	
		}
		
		// Here we enter the loop
		foreach ($pages as $_ => $id) {
				
			// Now we need some control flow to determine wether we are in the 
			// per page or default case. This will allow us to set the section
			// to which we will add our options. 
			if ( strcmp( $id , "0" ) == 0 ){
				$section = "page-default-styles";	
				$title   = "Page Default Styles"; 
			}
			else {
				$page 	 = get_post($id); // Get the WP_Post object by ID.
				if (strtolower($page->post_title) == 'home'){continue;}
				$section = preg_replace('/\s+/', '-', strtolower($page->post_title)) . '-header';
				$title   = $page->post_title . ' ' . esc_html__( '(Toplevel Styles)' , 'pagoda' ); 
			}

			$wp_customize->add_section( $section, array(
				'title'		=> $title,
				'panel'  	=> $panel,
				'priority'	=> 30,
			) ); 

			/////////////////////////////////////////////////////////////////////
			// Inherit Settings (per-page). Only register this option if we are 
			// not on the zero page. Otherwise register the activate checkbox
			if ( strcmp( $id , "0" ) == 0 ){

				$data_group = array(
					'id'	=> 'activate',
					'group' => 'pagoda_page_styles_',
					'label' => false, 
					'desc'  => false,
					'color'	=> 'red'
				);
				$data_keys  = array(
					'activate'	=> array(
						'type'	=> 'checkbox',
						'sanitize' => 'sanitize_checkbox',
						'label'	=> esc_html__('Page Toplevel Styles', 'pagoda'),
						'desc' 	=> __( '<p> If this option is selected, then one will be able to customize the headers and content area all toplevel pages. <strong>(max 12 toplevel pages)</strong></p><p> Note that <strong> child pages will automatically inherit the header properties defined for their respective toplevel page</strong>. Please refresh the customizer after changing this option. </p>', 'pagoda'),
				) );
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['header']);	
			}
			else {
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'inherit_' . $id,
					'group'	=> 'pagoda_page_header_',
					'label' => false,
					'desc'  => false,
					'color'	=> 'red',
				);	
				$data_keys = array(
					'inherit'=> array(
						'type'	  => 'checkbox',
						'sanitize'=> 'sanitize_checkbox',
						'label'	  => esc_html__('Inherit Defaults?','pagoda'),
						'desc' 	  => __('If selected, then header data for this page group will be inherited from the default page styles. <br><br> Note: Customizer refresh is required after changing per-page header inheritance options.', 
							'pagoda')
				) );	
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['header']);
				if ( get_option('pagoda_page_header_inherit_'.$id) ){continue;}
			}

				/////////////////////////////////////////////////////////////////////
				// Background Image Settings (per-page)
				$option = 'pagoda_page_header_img_';
				$wp_customize->add_setting( $option.$id, array(
					'type'		  => 'option',
   					'transport'	 => 'postMessage',
	   		 		'default'		=> $defaults['header'][$option],
	   		 		'sanitize_callback' => array($this->sanitize_data, 'sanitize_image')
				) );				
				$wp_customize->add_control( new WP_Customize_Image_Control( 
					$wp_customize, $option.$id, 
						array(
							'label'	   => esc_html__( 'Page Header Image', 'pagoda' ),
							'description' => esc_html__( 'Select page header background image.', 
								'pagoda'),
							'section'	 => $section,
						)
				) );

				/////////////////////////////////////////////////////////////////////
				// Header Background Color Palette (per-page)
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'page_header_' . $id,
					'group' => 'pagoda_page_header_',
					'label' => false,
					'desc'	=> false,
					'color'	=> 'blue',
				);
				$data_keys = array(
					'bg_color'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Header Background Color','pagoda'),
						'desc'	  => esc_html__('This header background will default to this color when an image is not selected', 'pagoda') 
				) );	
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['header']);

				/////////////////////////////////////////////////////////////////////
				// Header Background Color Palette (per-page)
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'page_navbar_' . $id,
					'group' => 'pagoda_page_navbar_',
					'label' => esc_html__( 'Navbar Color Palette', 'pagoda' ),
					'desc'	=> esc_html__('Select the navbar color, navbar scroll color, and search field color.', 'pagoda' ),
					'color'	=> 'green',
				);
				$data_keys = array(
					'nv_color'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Navbar Color','pagoda'), 
					),
					'sc_color'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Navbar Color on Scroll','pagoda'), 
					),
					'sr_color'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Search Field Color','pagoda'), 
				) );
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['navbar']);

				/////////////////////////////////////////////////////////////////////
				// Header Background Color Palette (per-page)
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'page_scroll_' . $id,
					'group' => 'pagoda_page_scroll_',
					'label' => esc_html__('Scroll to Top Icon Color Palette', 'pagoda'),
					'desc'	=> esc_html__('Select the color palette for the scroll to top icon.', 
						'pagoda' ),
					'color'	=> 'green',
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
					'bd_color' => array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Border Color','pagoda'), 
					),
					'fa_color' => array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Arrow Element Color','pagoda'), 

				) );
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['scroll']);

				/////////////////////////////////////////////////////////////////////
				// Content Area Color Palette 
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'page_page_content_' . $id,
					'group' => 'pagoda_page_content_',
					'label' => esc_html__( 'Content Area Color Palette', 'pagoda' ),
					'desc'	=> esc_html__('Select the default background colors for the page and content area.', 'pagoda' ),
					'color'	=> 'green',
				);
				$data_keys = array(
					'bg_color' => array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
							'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Page Background Color','pagoda'), 
					),
					'mn_color' => array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Content Area Color','pagoda'), 
				) );
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['content']);

				/////////////////////////////////////////////////////////////////////
				// Content Wrap Border
				$data_group = array(	
					'iterator' => $id,
					'id'	=> 'page_border_' . $id,
					'group' => 'pagoda_page_border_',
					'label' => esc_html__( 'Content Area Border', 'pagoda' ),
					'desc'	=> false,
					'color' => 'yellow'
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
				)  );

				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['border']);

				/////////////////////////////////////////////////////////////////////
				// Font Color Palette
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'page_colors_' . $id,
					'group' => 'pagoda_page_content_',
					'label' => esc_html__( 'Text Color Palette', 'pagoda' ),
					'desc'	=> esc_html__( 'Select color palette for page text, various html elements, and links.', 'pagoda' ),
					'color'	=> 'purple',
				);
				$data_keys = array(
					'h_color'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Header Font Color','pagoda'), 
						'desc'	  => esc_html__('Defines <h1> ... <h6> color', 'pagoda'),
					),
					'p_color'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Text Font Color','pagoda'), 
						'desc'	  => esc_html__('Defines <p> <ul> <ol> <li> color', 'pagoda'),
					),	
					'a_color'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Link Font Color','pagoda'), 
						'desc'	  => esc_html__('Defines <a> color', 'pagoda'),	
					),
					'a_hover'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Link Hover Color','pagoda'), 
						'desc'	  => esc_html__('Defines a:hover color', 'pagoda'),	
					),	
					'b_color'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Bold Font Color','pagoda'), 
						'desc'	  => esc_html__('Defines <b> <strong> color', 'pagoda' ),	
					),
					'in_color'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Input Color','pagoda'), 
						'desc'	  => esc_html__('Defines <input> background color', 'pagoda' ),	
					),
					'in_hover'=> array(
						'type'	  => 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	  => esc_html__('Submit Hover Color','pagoda'), 
						'desc'	  => esc_html__('Defines button <input>:hover color', 'pagoda'),
				) );		
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['content']);

				/////////////////////////////////////////////////////////////////////
				// Image/Input Border
				$data_group = array(	
					'iterator' => $id,
					'id'	=> 'page_frames_' . $id,
					'group' => 'pagoda_page_frames_',
					'label' => esc_html__( 'Content Area Frames', 'pagoda' ),
					'desc'	=> esc_html__( 'Select Border Properties for <img>, <input>, and <textarea> tags', 'pagoda'),
					'color' => 'yellow'
				);
				$data_keys = array( 
					'image'		=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Frame <img>?', 'pagoda'),
						'desc'	=> false,
					),
					'input'		=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Frame <input>?', 'pagoda'),
						'desc'	=> false,
					),
					'inset'		=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Frame <input> as Inset?', 'pagoda'),
					),
					'radius'	=> array(
						'type'	=> 'text',
						'sanitize' =>'sanitize_css_unit_value',
						'label'	=> esc_html__('Frame Radius (CSS Units)', 'pagoda'),
					),
					'size' 		=> array(
						'type'	=> 'text',						
						'label'	=> esc_html__('Frame Size (CSS Units)', 'pagoda'),
						'sanitize'=>'sanitize_css_unit_value',
					),
					'fade'		=> array(
						'type'	=> 'text',
						'label' => esc_html__('Frame Fade (CSS Units)' ,'pagoda'),
						'sanitize'=>'sanitize_css_unit_value',	
					),
					'color'		=> array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Frame Color','pagoda'),
				) );
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['frames']);

		} // Close foreach

	} // Close Page Iterated Options

} // End Class