<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly	

class pagoda_posts_customizer {
	
	public function __construct($defaults, $font_gen, $page_tree){

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

    	$panel = 'pagoda-post-settings';
 		$wp_customize->add_panel( $panel, array(
    		'title'			=> esc_html__('Pagoda Post Settings', 'pagoda' ),
    		'priority'		=> 30,	
		) ); 

 		$this->register_display_options($wp_customize, $panel, $this->defaults['display']);
		$this->register_data_options($wp_customize, $panel, $this->defaults['data']);
 	}

 	//////////////////////////////////////////////////////////////////////////////////////
	//					 		POST MAIN DISPLAY SETTINGS								//
	//////////////////////////////////////////////////////////////////////////////////////
	//
	// "display" : {
	// 		"pagoda_post_display_mn_height"		: "200px",
	// 		"pagoda_post_display_mn_padding"	: "20px 50px",
	// 		"pagoda_post_display_mn_content_a"	: "20px 10px",
	// 		"pagoda_post_display_mn_content_w"	: "40px 0px",
	// 		"pagoda_post_display_mn_article"	: "10px 60px",
	// 		"pagoda_post_display_mn_align"		: "left",
	// 		"pagoda_post_display_h_font_fam"	: "Montserrat",
	// 		"pagoda_post_display_h_font_var"	: "regular",
	//		"pagoda_post_display_p_font_fam"	: "Montserrat",
	//		"pagoda_post_display_p_font_var"	: "regular",
	//		"pagoda_post_display_a_font_var"	: "700",
	//		"pagoda_post_display_b_font_var"	: "700",
	//		"pagoda_post_display_p_font_size"	: "18",
	//		"pagoda_post_display_p_meta_size"	: "14"
	// },
 	//
	public function register_display_options($wp_customize, $panel, $defaults){

		$section = 'post-display-settings';
		$wp_customize->add_section( $section, array(
			'title'         => esc_html__( 'Post Display Settings', 'pagoda'  ),
			'priority'      => 30,
			'panel'  		=> $panel,
		) ); 

			/////////////////////////////////////////////////////////////////////
			// Content Area Layout
			$data_group = array(
				'id'	=> 'layout',
				'group'	=> 'pagoda_post_display_mn_',
				'label' => esc_html__('Content Area Layout', 'pagoda'),
				'desc'  => false,
				'color'	=> 'blue',
			);	
			$data_keys = array( 

				'height' 	=>	array(
					'type' 	=> 'text',
					'sanitize' => 'sanitize_css_unit_value',
					'label'	=> esc_html__( 'Set Post Header Height (CSS Units)', 'pagoda' ),
					'desc'  => esc_html__( 'This setting will apply to all posts', 'pagoda'),
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
					'label' => esc_html__('Post Wrap Padding (CSS Shorthand)','pagoda'), 
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
					'label' => esc_html__('Inter-Post Padding (CSS Shorthand)','pagoda'), 
					'desc'	=> esc_html__('The padding assigned the post\'s <article> tags. This setting is useful on pages with multiple posts (i.e. archive, search, etc.)', 'pagoda') 
				),
				'align'		 => array(
					'type'	 => 'select',
					'values' => array('left', 'center', 'right', 'justify'),
					'sanitize'	=> 'sanitize_dropdown', 
					'label'	 => esc_html__( 'Text Alignment', 'pagoda' ),
					'desc'	 => esc_html__('Alignment will apply to all items within the content area', 
						'pagoda') 	
			) );
			$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
			
			/////////////////////////////////////////////////////////////////////
			// h tag font control
			$data_group  = array(
			 	'id'	=> 'fonts',
			 	'group'	=> 'pagoda_post_display_h_',
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
				'id'	=> 'fonts',
				'group'	=> 'pagoda_post_display_',
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
	//					 POST DATA SETTINGS (HEADER & CONTENT AREA)						//
	//////////////////////////////////////////////////////////////////////////////////////
	//	
	// "header" : {
	// 		"pagoda_post_styles_activate" 	: "",
	// 		"pagoda_post_header_inherit_" 	: "",
	// 		"pagoda_post_header_img_"   	: "",     
	// 		"pagoda_post_header_bg_color_"  : "#000"
	// },
	//
	// "navbar" : {
	// 		"pagoda_post_navbar_nv_color_"  : "#666",
	// 		"pagoda_post_navbar_sc_color_"  : "#000",
	// 		"pagoda_post_navbar_sr_color_"  : "#000"
	// },
	//
	// "scroll" : {
	// 		"pagoda_post_scroll_bg_color_"  : "#000",       
	// 		"pagoda_post_scroll_bg_hover_"  : "#666", 
	// 		"pagoda_post_scroll_bd_color_"  : "#999", 
	// 		"pagoda_post_scroll_fa_color_"  : "#fff"
	// },
	//
	// "border" : {
	// 		"pagoda_post_border_show_"		: "",
	// 		"pagoda_post_border_inset_"		: "",
	// 		"pagoda_post_border_frames_"	: "",
	// 		"pagoda_post_border_radius_"	: "0px",
	// 		"pagoda_post_border_size_"		: "5px",
	// 		"pagoda_post_border_fade_"		: "5px",
	// 		"pagoda_post_border_color_"		: "#eee"
	// },
	//
	// "content" : {
	// 		"pagoda_post_content_bg_color_" : "#eee",
	// 		"pagoda_post_content_mn_color_"	: "#fff",
	// 		"pagoda_post_content_h_color_"	: "#444",
	// 		"pagoda_post_content_p_color_"	: "#666",
	// 		"pagoda_post_content_a_color_"	: "#000",
	// 		"pagoda_post_content_a_hover_"	: "#666",
	// 		"pagoda_post_content_b_color_"	: "#999",
	//		"pagoda_post_content_in_color_"	: "#000",
	//		"pagoda_post_content_in_hover_"	: "#666"
	// }
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
	public function register_data_options( $wp_customize, $panel, $defaults ){

		// If the per-category styles option is activated and we have less than 12
		// toplevel categories, then assign $categories to the array of toplevel 
		// categories. Note that this is an array of WP_Term Objects
		if ( ( strcmp( get_option('pagoda_post_styles_activate' ) , "1" ) == 0)  &&  
			 ( sizeof($this->page_tree->get_all_toplevel()) <= 12) ) {

			// Shift "uncategoized" to beginning of array (for defaults)
			$_categories = $this->page_tree->get_categories();
			foreach ($_categories as $_ => $category) {

				if (strcmp( $category->slug, "uncategorized" ) == 0 ){
				
					$uncategorized = $category; unset($_categories[$_]);

				}
			} 
			// Push a pseudo category for homepage styles (recent-posts)
			$home = (object)array( 
				'name' => 'Home',
				'slug' => 'home',
			);
			// Build categories array
			$categories = array_merge( array($uncategorized), array($home), $_categories );
		}
		// Otherwise loop through toplevel categories and pick out "Uncategorized". 
		// This will represent the default case. 
		else {
			foreach ( $this->page_tree->get_categories() as $_ => $category ) {
				if (strcmp( $category->slug, "uncategorized" ) == 0 ){
					$categories = array($category); break;
				}
			}
		}

		// Here we enter the loop
		foreach ($categories as $_ => $category){
				
			// Now we need some control flow to determine wether we are in the 
			// per page or default case. This will allow us to set the section
			// to which we will add our options. 
			if ( strcmp( $category->slug , "uncategorized" ) == 0 ){
				$section = "post-default-styles";	
				$title   = "Post Default Styles"; 
				$id 	 = str_replace('-', '_', $category->slug);	
			}
			else if ( strcmp( $category->slug , "home" ) == 0 ){
				$section = "home-default-styles";	
				$title   = "Recent Posts Styles"; 
				$id 	 = str_replace('-', '_', $category->slug);	
			}
			else {
				$section = preg_replace('/\s+/', '-', strtolower($category->slug)) . '-styles';
				$title   = $category->name . ' ' . esc_html__( '(Category Styles)' , 'pagoda' ); 
				$id 	 = str_replace('-', '_', $category->slug);	
			}

			$wp_customize->add_section( $section, array(
				'title'		=> $title,
				'panel'   	=> $panel,
				'priority'	=> 30,
			) ); 

			/////////////////////////////////////////////////////////////////////
			// Inherit Settings (per-category). And Activate Category Styles
			if ( strcmp( $id , "uncategorized" ) == 0 ){

				$data_group = array(
					'id'	=> 'layout',
					'group'	=> 'pagoda_post_styles_',
					'label' => false,
					'desc'	=> false,
					'color'	=> 'red',
				);
				$data_keys  = array(
					'activate'	=> array(
						'type'	=> 'checkbox',
						'sanitize' => 'sanitize_checkbox',
						'label'	=> esc_html__( 'Post Category Styles', 'pagoda' ),
						'desc' 	=> __( '<p> If checked, then one will be able to define a unique style for each toplevel post category. <strong> (max 12 toplevel categories)</strong>. Category styles can be assigned via the <strong>category styles metabox</strong> when creating and updating posts.</p>', 'pagoda'),
				) );
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['header']);
			}
			else {
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'inherit_' . $id,
					'group'	=> 'pagoda_post_header_',
					'label' => false,
					'desc'  => false,
					'color'	=> 'red',
				);	
				$data_keys = array(
					'inherit'=> array(
						'type'	  => 'checkbox',
						'sanitize'=> 'sanitize_checkbox',
						'label'	  => esc_html__('Inherit Defaults?','pagoda'),
						'desc' 	  => __('If selected, then all style data will be inherited from the <strong>post default styles</strong> <br><br> <strong>Note:</strong> Customizer refresh is required after changing this option.', 
							'pagoda')
				) );	
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['header']);
				if ( get_option('pagoda_post_header_inherit_'.$id) ){continue;}
			}

				/////////////////////////////////////////////////////////////////////
				// Background Image Settings (per-page)
				$option = 'pagoda_post_header_img_';
				$wp_customize->add_setting( $option.$id, array(
					'type'		  => 'option',
   					'transport'	 => 'postMessage',
	   		 		'default'		=> $defaults['header'][$option],
	   		 		'sanitize_callback' => array($this->sanitize_data, 'sanitize_image')
				) );
				$wp_customize->add_control( new WP_Customize_Image_Control( 
					$wp_customize, $option.$id, 
						array(
							'label'	   => esc_html__( 'Post Header Image', 'pagoda' ),
							'description' => esc_html__( 'Select post header background image. If none is selected, then the background color will be used', 'pagoda'),
							'section'	 => $section,
							'priority'	  => 2,
						)
				) );

				/////////////////////////////////////////////////////////////////////
				// Header Background Color Palette (per-page)
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'post_header_' . $id,
					'group' => 'pagoda_post_header_',
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
					'id'	=> 'post_navbar_' . $id,
					'group' => 'pagoda_post_navbar_',
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
					'id'	=> 'post_scroll_' . $id,
					'group' => 'pagoda_post_scroll_',
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
					'id'	=> 'post_content_' . $id,
					'group' => 'pagoda_post_content_',
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
					'id'	=> 'post_border_' . $id,
					'group' => 'pagoda_post_border_',
					'label' => esc_html__( 'Content Area Border', 'pagoda' ),
					'desc'	=> false,
					'color' => 'yellow'
				);
				$data_keys = array( 
					'show'		=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Show Border Elements?', 'pagoda'),
						'desc'	=> false,
					),
					'inset'		=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Render Border as Inset?', 'pagoda'),
						'desc'	=> false,
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
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['border']);

				/////////////////////////////////////////////////////////////////////
				// Font Color Palette
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'post_colors_' . $id,
					'group' => 'pagoda_post_content_',
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
						'desc'	  => esc_html__('Defines <a> color', 'pagoda' ),	
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
					'id'	=> 'post_frames_' . $id,
					'group' => 'pagoda_post_frames_',
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

	} // Close Post Iterated Options

} // End Class