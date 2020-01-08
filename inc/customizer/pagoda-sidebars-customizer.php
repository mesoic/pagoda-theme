<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly	

	class pagoda_sidebars_customizer {

		public function __construct($defaults, $font_gen, $sidebars) {

			$this->defaults = $defaults;	// From JSON Options Table
	    	$this->font_gen = $font_gen;	// The single instance of nabla_pagoda_fonts_autogen()
			$this->sidebars = $sidebars->get_sidebars(); 	// Sidebars defined in pagoda_sidebars
	    	
	    	// Data Control 
			$this->sanitize_data = new Nablafire_Customize_Data_Sanitize();
			$this->register_data = new Nablafire_Customize_Data_Register($this->sanitize_data);
			
			// Font control
			$this->sanitize_font = new Nablafire_Customize_Font_Sanitize($this->font_gen);
			$this->register_font = new Nablafire_Customize_Font_Register($this->sanitize_font);

	    	// Register our customizer
			add_action( 'customize_register',  array( $this, 'register_options' ) );
	    }

	     // This will register a main panel and a panel for every sidebar
	    public function register_options($wp_customize){

	    	$panel = 'pagoda-sidebar-settings';
	 		$wp_customize->add_panel( $panel, array(
        		'title'             => esc_html__('Pagoda Sidebar Settings', 'pagoda' ),
        		'priority'          => 30,
    			
    		) ); 
			$this->register_sidebars_font_options($wp_customize, $panel, $this->defaults['fonts']);
	 		$this->register_sidebars_main_options($wp_customize, $panel, $this->defaults['main']);
	 		$this->register_sidebars_data_options($wp_customize, $panel, $this->defaults['data']);
		}



		//////////////////////////////////////////////////////////////////////////////////////
		//						   SIDEBAR UNITERATED FONT OPTIONS  						//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "fonts"	: {
		//	"pagoda_sidebar_h_font_fam"		: "Montserrat",
		//	"pagoda_sidebar_h_font_var"		: "regular",
		//	"pagoda_sidebar_h_font_size"	: "32",
		//	"pagoda_sidebar_p_font_fam"		: "Montserrat",
		//	"pagoda_sidebar_p_font_var"		: "regular",
		//	"pagoda_sidebar_p_meta_var"		: "regular",
		//	"pagoda_sidebar_p_font_size"	: "18"
		// },
		//
		public function register_sidebars_font_options($wp_customize, $panel, $defaults ){
			
			/////////////////////////////////////////////////////////////////////
			// Title Group Options
	 		$section = 'sidebar-font-settings';
			$wp_customize->add_section( $section, array(
        		'title'         => esc_html__( 'Sidebar Font Settings', 'pagoda'  ),
        		'priority'      => 30,
        		'panel'  		=> $panel,
    		) ); 

				/////////////////////////////////////////////////////////////////////
				// Informational Label
				$data_group  = array(
					'id'	=> 'label',
					'group' => 'pagoda_sidebar_info_',
					'label' => false,
					'desc'	=> false,
					'color' => 'green'
				);
				$data_keys = array( 
					'text'=> array(
						'type'	  => 'label',
						'sanitize' => 'sanitize_pass',
						'label'	  => esc_html__('Sidebar Font Colors','pagoda'), 
						'desc' 	  => esc_html__('Font colors can be assinged individually for each sidebar in its associated customizer panel','pagoda' )	
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				/////////////////////////////////////////////////////////////////////
				// h tag font control
				$data_group  = array(
					'id'	=> 'font',
					'group'	=> 'pagoda_sidebar_h_',
					'label' => esc_html__( 'Header Font Control', 'pagoda' ),
					'desc'  => esc_html__( 'This controls the font for all html header tags within sidebars (h1, h2, h3, h4, h5, h6)', 'pagoda'),
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
					),
					'font_size'	=> array(
							'type'	=> 'size',
							'range'	=> array('min' => 8, 'max'=> 72), 
							'label'	=> esc_html__('Header Font Size (px)', 'pagoda'),
							'sanitize' => 'sanitize_font_size',
				) );
				$this->register_font->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				/////////////////////////////////////////////////////////////////////
				// p tag font control
				$data_group  = array(
					'id'	=> 'fonts',
					'group'	=> 'pagoda_sidebar_p_',
					'label'	=> esc_html__( 'Text Font Control', 'pagoda' ),
					'desc' 	=> __( 'All available variants will be enqueued for the selected font. Use your own CSS to apply additional variants.', 'pagoda'),
				);
				$data_keys   = array(
					'font_fam' 	=> array(
						'type'	=> 'font',
						'label' => esc_html__('Text Font Family', 'pagoda'),
						'sanitize' => 'sanitize_font_family',
					),
					'font_var'	=> array(
						'type'	=> 'variant',
						'font'	=> 'font_fam',
						'label' => esc_html__('Text Font Variant', 'pagoda'),
						'sanitize' => 'sanitize_font_variant'
					),
					'meta_var'	=> array(
						'type'	=> 'variant',
						'font'	=> 'font_fam',
						'label' => esc_html__('Link Font Variant', 'pagoda'),
						'sanitize' => 'sanitize_font_variant'
					),
					'font_size'	=> array(
						'type'	=> 'size',
						'range'	=> array('min' => 8, 'max'=> 72), 
						'label'	=> esc_html__('Text Font Size (px)', 'pagoda'),
						'sanitize' => 'sanitize_font_size',
			) );
			$this->register_font->control($wp_customize, $section, $data_group, $data_keys, $defaults);
		}


		//////////////////////////////////////////////////////////////////////////////////////
		//						  	 	SIDEBAR STYLE SELECTORS  							//
		//////////////////////////////////////////////////////////////////////////////////////
		//			
		// "main"  : {
		// 		"pagoda_sidebar_styles_activate"	: "", 
		// 		"pagoda_sidebar_mn_title_"			: "Sidebar",
		// 		"pagoda_sidebar_mn_style_"			: "0"
		// },
		//
		public function  register_sidebars_main_options($wp_customize, $panel, $defaults ){

			$section = 'sidebar-style-settings';
			$wp_customize->add_section( $section, array(
				'title'         => esc_html__( 'Assign Sidebar Styles', 'pagoda'  ),
				'priority'      => 30,
				'panel'  		=> $panel,
			) ); 

			// Activate Sidebar Styles
			$data_group = array(
				'id'	=> 'layout',
				'group'	=> 'pagoda_sidebar_styles_',
				'label' => false,
				'desc'	=> false,
				'color'	=> 'red',
			);
			$data_keys  = array(
				'activate'	=> array(
					'type'	=> 'checkbox',
					'sanitize' => 'sanitize_checkbox',
					'label'	=> esc_html__( 'Activate Sidebar Styles?', 'pagoda' ),
					'desc' 	=> __( 'If checked, then one will be able to define multiple sidebar styles. Each sidebar can then be assigned a unique style via the data control below. <strong>customizer refresh required</strong>', 'pagoda'),
			) );
			$this->register_data->control(
				$wp_customize, $section, $data_group, $data_keys, $defaults);

			// Set up a values array ...
			$values = array( 0 => 'Default Styles' );
			if ( strcmp( get_option('pagoda_sidebar_styles_activate' ) , "1" ) == 0){
				for ($id = 1; $id <= sizeof( $this->sidebars ); $id++) { 
					$values[$id] = esc_html__('Sytle', 'pagoda') . ' ' . $id; 	
				}
			}

			// Loop through array and set data attributes
			foreach ($this->sidebars as $id => $_) {

				$data_group = array(
					'iterator' => $id,
					'id'	=> 'styles_' . $id,
					'group'	=> 'pagoda_sidebar_mn_',
					'label' => false,
					'desc'  => false,
					'color' => 'blue'
				);

				$data_keys = array(

					'title'	=> array(
						'type'	=> 'text',
						'sanitize' => 'sanitize_text_field',
						'label'	=> $_['nice'] . ' ' . esc_html__('Sidebar Title', 'pagoda'),
					),
					'style' 	 => array(
						'type'	 => 'select',
						'values' => $values,
						'keyed'	 => true, 
						'sanitize'	=> 'sanitize_dropdown_keyed', 
						'label'	 => $_['nice'] . ' ' . esc_html__( 'Sidebar Styles', 'pagoda' ),
				) );
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults);
			}
		}

		//////////////////////////////////////////////////////////////////////////////////////
		//						  	 SIDEBAR ITERATED DATA OPTIONS 							//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "display" : {
		// 		"pagoda_sidebar_sb_width_"	  	: "25%",
		// 		"pagoda_sidebar_sb_padding_"	: "15px 10px 20px",
		// 		"pagoda_sidebar_sb_widgets_"	: "10px 10px 10px",
		// 		"pagoda_sidebar_sb_tmargin_"	: "0",
		// 		"pagoda_sidebar_sb_cmargin_"	: "0",
		// 		"pagoda_sidebar_sb_color_"		: "#fff"
		// },
		//
		// "border" : {
		// 		"pagoda_sidebar_bd_show_"		: "1",
		// 		"pagoda_sidebar_bd_fused_"		: "1",
		// 		"pagoda_sidebar_bd_inset_"		: "",
		// 		"pagoda_sidebar_bd_render_"		: "",
		// 		"pagoda_sidebar_bd_radius_"		: "0px",
		// 		"pagoda_sidebar_bd_size_"		: "5px",
		// 		"pagoda_sidebar_bd_fade_"		: "10px",
		//	 	"pagoda_sidebar_bd_color_"		: "#eee"
		// },
		//
		// "title" : {
		// 		"pagoda_sidebar_tl_show_"		: "1",
		// 		"pagoda_sidebar_tl_text_"		: "Sidebar",
		// 		"pagoda_sidebar_tl_align_"		: "left",
		// 		"pagoda_sidebar_tl_padding_"	: "10px",
		// 		"pagoda_sidebar_tl_color_"		: "#666",
		// 		"pagoda_sidebar_tf_font_fam_"	: "Montserrat",
		// 		"pagoda_sidebar_tf_font_var_"	: "regular",
		// 		"pagoda_sidebar_tf_font_size_"	: "24",
		//	 	"pagoda_sidebar_tf_font_color_"	: "#fff"
		// },
		//
		// "content" : {
		// 		"pagoda_sidebar_h_font_color_"	: "#444",
		// 		"pagoda_sidebar_p_font_color_"	: "#999",
		// 		"pagoda_sidebar_a_font_color_"	: "#666",
		// 		"pagoda_sidebar_a_font_hover_"	: "#999"
		// }
		//	
   		public function register_sidebars_data_options($wp_customize, $panel, $defaults){

			// Set up a values array ... Looks as above 
			$values = array( 0 => esc_html__('Sidebar Default Styles' , 'pagoda') );
			if ( strcmp( get_option('pagoda_sidebar_styles_activate' ) , "1" ) == 0){
				for ($id = 1; $id <= sizeof( $this->sidebars ); $id++) { 
					$values[$id] = esc_html__('Sidebar Sytles', 'pagoda') . ' ' . $id; 	
				}
			}
			
			// Loop through values array: $_ => section_title
			foreach ($values as $id => $_) {

	   			/////////////////////////////////////////////////////////////////////
				// Title Group Options
			 	$section =  $id . '-sidebar-title-settings';
				$wp_customize->add_section( $section, array(
	    	    	'title'		=> $_,
    	    		'priority'  => 30,
    	    		'panel'  	=> $panel,
    			) );

				/////////////////////////////////////////////////////////////////////
				// Sidebar Layout Control
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'layout_' . $id,
					'group'	=> 'pagoda_sidebar_sb_',
					'label' => esc_html__( 'Sidebar Layout Control', 'pagoda' ),
					'desc'	=> false,
					'color' => 'blue'
				);
				$data_keys  = array( 
					'width'		 => array(
						'type'	 => 'select',
						'values' =>array('5%', '10%', '15%', '20%', '25%', '30%', '33%', '40%', '50%'),
						'sanitize'	=> 'sanitize_dropdown', 
						'label'	 => esc_html__( 'Sidebar Width', 'pagoda' ),
						'desc'   => esc_html__( 'Note that the widths as indicated include the sidebar\'s padding. This is the total width that the sidebar element will consume.', 'pagoda'),	
					),
					'padding'	=> array(
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_padding_shorthand',
						'label'	=> esc_html__('Sidebar Padding (CSS shorthand)', 'pagoda'),
						'desc'	=> esc_html__('Relative placement of the entire sidebar with respect to its neighboring elements', 'pagoda')	
					),
					'content' 	=> array( 
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_padding_shorthand',
						'label'	=> esc_html__('Content Padding (CSS shorthand)', 'pagoda'),
						'desc'	=> esc_html__('The padding of sidebar content relative to inner edges of the sidebar.', 'pagoda'),
					),
					'widget' 	=> array( 
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_padding_shorthand',
						'label'	=> esc_html__('Widget Padding (CSS shorthand)', 'pagoda'),
						'desc'	=> esc_html__('The padding applied to individual widgets within the sidebar.', 'pagoda'),
					),
					'tmargin' 	=> array( 
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_margin_shorthand',
						'label'	=> esc_html__('Title Margin (CSS shorthand)', 'pagoda'),
						'desc'	=> esc_html__('Defines margins for the sidebar title area (optional).', 'pagoda'),
					),
					'cmargin' 	=> array( 
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_margin_shorthand',
						'label'	=> esc_html__('Content Margin (CSS shorthand)', 'pagoda'),
						'desc'	=> esc_html__('Defines margins for the sidebar content area (optional).', 'pagoda'),
					),
					'color'		=>	array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Widget Area Background Color','pagoda'), 
					),
					'crgba'		=> array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Aside Background Color','pagoda'), 
						'desc'	=> esc_html__('Defines a backgroud color for the sidebar container for rgba color compositing', 'pagoda')
				) );
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['display']);

			
				/////////////////////////////////////////////////////////////////////
				// Sidebar border properties
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'style_' . $id,
					'group'	=> 'pagoda_sidebar_bd_',
					'label' => esc_html__( 'Sidebar Border Styles', 'pagoda' ),
					'desc'	=> false,
					'color' => 'yellow'
				);
				$data_keys = array( 

					'show'		=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Show Border Elements?', 'pagoda'),
						'desc'	=> esc_html__('To display only the border radius property, set the border size and border fade to 0px.', 'pagoda'),
					),
					'fused'		=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Fused Sidebar?', 'pagoda'),
						'desc'	=> esc_html__('When checked, internal corners between the title and widget area will remain square providing a fused sidebar look.','pagoda'),
					),				
					'render'	=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Render on Elements?', 'pagoda'),
						'desc'	=> esc_html__('When checked, border properties will be rendered on the sidebar title and content area independently.','pagoda'),
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
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['border']);
			
				/////////////////////////////////////////////////////////////////////
				// Title Div and Text Settings
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'title'.'_'.$id,
					'group'	=> 'pagoda_sidebar_tl_',
					'label'	=> esc_html__( 'Sidebar Title Styles', 'pagoda' ),
					'desc'	=> false,
					'color' => 'red'
				);

				$data_keys = array( 
					'show'	=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Show Title Block?', 'pagoda'),
						'desc'	=> false,
					),
					'align'		 => array(
						'type'	 => 'select',
						'values' =>array('left', 'center', 'right', 'justify'),
						'sanitize'	=> 'sanitize_text_field', 
						'label'	 => esc_html__( 'Title Alignment', 'pagoda' ),	
					),
					'padding'	=> array(
						'type'	=> 'text',
						'sanitize' => 'sanitize_css_padding_shorthand',
						'label'	=> esc_html__('Title Padding (CSS Shorthand)', 'pagoda')
					),
					'color'		=>	array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Title Background Color','pagoda'), 
				) );
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['title']);

				/////////////////////////////////////////////////////////////////////
				// Title font control
				$data_group  = array(
					'iterator' => $id,
					'id'	=> 'font_' . $id,
					'group' => 'pagoda_sidebar_tf_',
					'label'	=> esc_html__( 'Title Font Control', 'pagoda' ),
					'desc'	=> false
				);
				$data_keys   = array(

					'font_fam' 	=> array(
						'type'	=> 'font',
						'label' => esc_html__('Title Font Family', 'pagoda'),
						'sanitize' => 'sanitize_font_family',
					),
					'font_var'	=> array(
						'type'	=> 'variant',
						'font'	=> 'font_fam',
						'label' => esc_html__('Title Font Variant', 'pagoda'),
						'sanitize' => 'sanitize_font_variant'
					),
					'font_size'	=> array(
						'type'	=> 'size',
						'range'	=> array('min' => 8, 'max'=> 120), 
						'label'	=> esc_html__('Text Font Size (px)', 'pagoda'),
						'sanitize' => 'sanitize_font_size',
					),
					'font_color'=> array(
						'type'  => 'color',
						'label' => esc_html__('Text Font Color','pagoda'),
						'sanitize' => 'sanitize_font_color',
						'default' => false, // Assigned in loop
          		) );				
				$this->register_font->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['title']);
				
				/////////////////////////////////////////////////////////////////////
				// Fonts Color Palette
				$data_group = array(
					'iterator' => $id,
					'id'	=> 'font_palette' . '_' . $id,
					'group'	=> 'pagoda_sidebar_',
					'label'	=> esc_html__( 'Sidebar Text Color Palette', 'pagoda' ),
					'desc'	=> esc_html__( 'Select the color palette to be used for fonts in this sidebar.', 'pagoda' ),
					'color'	=> 'green'
				);
				$data_keys  = array( 
					'h_color'	=>	array(
						'type'		=> 'color',
						'default' 	=> false, // Assigned in loop
						'sanitize'	=> 'sanitize_alpha_color',
						'label'		=> esc_html__('Header Font Color','pagoda'), 
					),
					'p_color'	=>	array(
						'type'		=> 'color',
						'default' 	=> false, // Assigned in loop
						'sanitize'	=> 'sanitize_alpha_color',
						'label'		=> esc_html__('Text Font Color','pagoda'),
					),
					'a_color'	=>	array(
						'type'		=> 'color',
						'default' 	=> false, // Assigned in loop
						'sanitize'	=> 'sanitize_alpha_color',
						'label'		=> esc_html__('Link Font Color','pagoda'), 
					),
					'a_hover'	=>	array(
						'type'		=> 'color',
						'default' 	=> false, // Assigned in loop
						'sanitize'	=> 'sanitize_alpha_color',
						'label'		=> esc_html__('Link Hover Color','pagoda'), 	 
					),
					'in_color'	=>	array(
						'type'		=> 'color',
						'default' 	=> false, // Assigned in loop
						'sanitize'	=> 'sanitize_alpha_color',
						'label'		=> esc_html__('Input Color','pagoda'), 
					),
					'in_hover'	=>	array(
						'type'		=> 'color',
						'default' 	=> false, // Assigned in loop
						'sanitize'	=> 'sanitize_alpha_color',
						'label'		=> esc_html__('Submit Hover Color','pagoda'), 	
				) );
				$this->register_data->control(
					$wp_customize, $section, $data_group, $data_keys, $defaults['content']);


				/////////////////////////////////////////////////////////////////////
				// Image and Input Borders
				$data_group = array(	
					'iterator' => $id,
					'id'	=> 'sidebar_frames_' . $id,
					'group' => 'pagoda_sidebar_frames_',
					'label' => esc_html__( 'Sidebar Frames', 'pagoda' ),
					'desc'	=> esc_html__( 'Select Border Properties for <img>, <input>, and <textarea> tags', 'pagoda'),
					'color' => 'yellow'
				);
				$data_keys = array( 
					'show'		=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Show Frames?', 'pagoda'),
						'desc'	=> false,
					),
					'inset'		=> array(
						'type'	=> 'checkbox',	
						'sanitize'=>'sanitize_checkbox',
						'label'	=> esc_html__('Render as Inset?', 'pagoda'),
						'desc'	=> esc_html__('Applies only to <input> and <textarea>', 'pagoda'),
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

  			} 
		}
	}