<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	class pagoda_mobile_customizer{ 

		function __construct($defaults, $settings, $sidebars){

			// Only need defaults table for responsive
	   		$this->defaults = $defaults; // From JSON Options Table
	   		$this->settings = $settings;
	    	$this->sidebars = $sidebars->get_sidebars();

	    	// Instantiate our sanitize and register classes
			$this->sanitize_data = new Nablafire_Customize_Data_Sanitize();
			$this->register_data = new Nablafire_Customize_Data_Register($this->sanitize_data);

			add_action( 'customize_register',  array( $this, 'register_options' ) );	
		}

		function register_options($wp_customize){
			// An additional panel for responsive settings. 
			$panel = 'pagoda-responsive-settings';
	 		$wp_customize->add_panel( $panel, array(
        		'title'             => esc_html__( 'Pagoda Responsive Settings', 'pagoda' ),
        		'priority'          => 30,    			
    		) ); 
			$this->register_mobile_header_options($wp_customize, $panel, $this->defaults['header']);
			$this->register_mobile_navbar_options($wp_customize, $panel, $this->defaults['navbar']);
			$this->register_mobile_section_options($wp_customize, $panel, $this->defaults['sections']);
			$this->register_mobile_sidebar_options($wp_customize, $panel, $this->defaults['sidebars']);

		}

		//////////////////////////////////////////////////////////////////////////////////////
		//								  HEADER MOBILE										//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "header" : {
		//	 	"pagoda_banner_m_phone"		: "100vh",
		// 		"pagoda_banner_m_tablet"	: "70vh",
		// 		"pagoda_banner_m_img_height": "110px"
		// },
		//
		function register_mobile_header_options($wp_customize, $panel, $defaults){
		
			// This will eventually get its own panel. It contains both banner and navbar options
	 		$section = 'header-responsive-options';
			$wp_customize->add_section( $section, array(
        		'title'         => esc_html__( 'Header Responsive Options' , 'pagoda'  ),
        		'priority'      => 30,
	       		'panel'  		=> $panel,
    		) ); 	

				$data_group = array(
					'id'	=> 'responsive',
					'group'	=> 'pagoda_banner_m_',
					'label'	=> esc_html__('Header Responsive Options', 'pagoda'),
					'desc'  => false, 
					'color'	=> 'blue'
				);	
				$data_keys = array(
					'phone' => array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__('Phone Header Height (CSS Units)', 'pagoda' ),
				        'desc' 	=> esc_html__('Adjust the header height for phones', 'pagoda'),
					),
					'tablet' => array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__('Tablet Header Height (CSS Units)', 'pagoda' ),
				        'desc' 	=> esc_html__('Adjust the header height for tablets', 'pagoda'),
					),
					'img_height' => array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__('Adjust Image Height (CSS Units)', 'pagoda' ),
				        'desc' 	=> esc_html__('Adjust the height of the banner image for small screens', 
				        	'pagoda'),
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

		}


		//////////////////////////////////////////////////////////////////////////////////////
		//								   NAVBAR MOBILE									//
		//////////////////////////////////////////////////////////////////////////////////////
		// 	
		// "navbar" : {
		// 		"pagoda_scroll_m_show_home"	: "1",
		// 		"pagoda_scroll_m_show_page"	: "1",
		// 		"pagoda_scroll_m_show_post"	: "1",
		// 		"pagoda_navbar_m_height"	: "50px",
		// 		"pagoda_navbar_m_width"		: "100px",
		// 		"pagoda_navbar_m_subwidth"	: "120px",
		// 		"pagoda_navbar_m_fontsize"	: "16px",
		// 		"pagoda_navbar_m_subfsize"	: "16px",
		// 		"pagoda_navbar_m_mn_color"	: "#000",
		// 		"pagoda_navbar_m_mn_hover"	: "#999",
		// 		"pagoda_navbar_m_sb_color"	: "#000",
		// 		"pagoda_navbar_m_sb_hover"	: "#999"
		// },
		//
		function register_mobile_navbar_options($wp_customize, $panel, $defaults){

	 		$section = 'navbar-responsive-options';
			$wp_customize->add_section( $section, array(
        		'title'         => esc_html__( 'Navbar Responsive Options' , 'pagoda'  ),
        		'priority'      => 30,
	       		'panel'  		=> $panel,
    		) ); 	

				// Scroll to icon mobile (for additional flexibility)
				$data_group = array(
					'id'	=> 'responsive',
					'group'	=> 'pagoda_scroll_m_show_',
					'label'	=> esc_html__('Show Scroll Icon (mobile)', 'pagoda'),
					'desc'  => __('This setting is dependent of the <strong>show scroll icon</strong> setting assigned for the homepage, pages, and posts when checked.', 'pagoda'), 
					'color'	=> 'red'
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

				// Responsive Navbar Layout Options
				$data_group = array(
					'id'	=> 'data',
					'group'	=> 'pagoda_navbar_m_',
					'label'	=> esc_html__('Navbar Responsive Options', 'pagoda'),
					'desc'  => esc_html__('Note that in responsive mode, menu list items will be replaced by a menu icon' , 'pagoda'), 
					'color'	=> 'blue'
				);	
				$data_keys = array(
					'height' 	=> array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Navbar Line Height (CSS Units)', 'pagoda' )

					),
					'width' 	=> array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Navbar Main Width (CSS Units)', 'pagoda' )

					),
					'fontsize'	=> array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Main Font Size (CSS Units)', 'pagoda' ),
						'desc'	=> esc_html__( 'Display a different font size for navbar menu items on mobile devices', 'pagoda'),
					),
					'subwidth'	=> array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Navbar Submenu Width (CSS Units)', 'pagoda' ),
						'desc'	=> esc_html__( 'In responsive mode, navbar submenus will appear to the right of the main navbar dropdown', 'pagoda'),
					),
					'subfsize'	=> array(
						'type'	=> 'text', 
						'sanitize' => 'sanitize_css_unit_value',
						'label'	=> esc_html__( 'Submenu Font Size (CSS Units)', 'pagoda' ),
						'desc'	=> esc_html__( 'Display a different font size for navbar submenus on mobile devices', 'pagoda'),
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
						
				// Responsive Navbar Color Palette
				$data_group = array(
					'id'	=> 'colors',
					'group'	=> 'pagoda_navbar_m_',
					'label'	=> esc_html__('Navbar Responsive Color Palette', 'pagoda'),
					'desc'  => false, 
					'color'	=> 'green'
				);	
				$data_keys = array(
					'mn_color' => array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Menu Background Color','pagoda'), 
					),
					'mn_hover' => array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Menu Hover Color','pagoda'), 
					),
					'sb_color' => array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Submenu Background Color','pagoda'), 
					),
					'sb_hover' => array(
						'type'	=> 'color',
						'default' => false, // Assigned in loop
						'sanitize'=> 'sanitize_alpha_color',
						'label'	=> esc_html__('Submenu Hover Color','pagoda'), 
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
		}

		//////////////////////////////////////////////////////////////////////////////////////
		//								   SECTION  MOBILE									//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "sections" : {
		//		"pagoda_sections_m_show_"	: "1"
		// }
		//
		function register_mobile_section_options($wp_customize, $panel, $defaults){

			$section = 'section-responsive-options';
			$wp_customize->add_section( $section, array(
        		'title'		=> esc_html__( 'Section Responsive Options' , 'pagoda'  ),
        		'priority'	=> 30,
	       		'panel'		=> $panel,
    		) ); 	

				$data_group = array(
					'id' 	=> 'sidebars_',
					'group' => 'pagoda_sections_m_pass',
					'color'	=> 'red',
					'label' => false, 
					'desc'  => false
				); 
				$data_keys = array(
					'label' => array(
						'type'  => 'label',
						'sanitize' => 'sanitize_pass',
						'label' => esc_html__('Responsive Sections', 'pagoda'),
						'desc'	=> esc_html__('Select which sections you would like to display on mobile devices. This option is useful when a given section displays widgets in fullscreen mode, but does not display widgets in responsive mode.', 
							'pagoda'), 
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);


	 			for ($id = 1; $id<=$this->settings['n_sections']; $id++){
				
					$data_group = array(
						'iterator' => $id,
						'id'	=> 'data_' . $id,
						'group'	=> 'pagoda_sections_m_',
						'label' => false,
						'desc'	=> false,
						'color' => 'grey',
					);
					$data_keys = array(
						'show'		=> array(
							'type'	=> 'checkbox',
							'sanitize' => 'sanitize_checkbox',
							'label' => esc_html__('Show Section', 'pagoda') . ' ' . $id . ' ' .esc_html__(' (mobile)?', 'pagoda'),
					) );
					$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

				}
		}


		//////////////////////////////////////////////////////////////////////////////////////
		//								   SIDEBAR MOBILE									//
		//////////////////////////////////////////////////////////////////////////////////////
		//
		// "sidebars" : {
		//		"pagoda_sidebars_m_show_l"	: "1",
		//		"pagoda_sidebars_m_show_r"	: "1",
		//		"pagoda_sidebars_m_show_b"	: "1",
		//		"pagoda_sidebars_m_show_p"	: "1",
		//		"pagoda_sidebars_m_show_a"	: "1"
		// },
		//
		function register_mobile_sidebar_options($wp_customize, $panel, $defaults){

			$section = 'sidebar-responsive-options';
			$wp_customize->add_section( $section, array(
        		'title'     => esc_html__( 'Sidebar Responsive Options' , 'pagoda'  ),
        		'priority'  => 30,
	       		'panel'  	=> $panel,
    		) ); 	

				$data_group = array(
					'id' 	=> 'sidebars_',
					'group' => 'pagoda_sidebars_m_',
					'label' => esc_html__('Responsive Sidebars', 'pagoda'),
					'desc'	=> __('Select which sidebars you would like to display on mobile devices. On mobile devices your sidebars will always be rendered <strong>below</strong> the content area at full width.', 'pagoda'), 
					'color'	  => 'grey',
				); 
				$data_keys = array();
	   			foreach ($this->sidebars as $id => $_) {
					$data_keys[ 'show_' . $_['slug'] ] = array(
						'type'  => 'checkbox',
						'sanitize' => 'sanitize_checkbox',
						'label'	=> esc_html__('Show ', 'pagoda') . $_['nice'] .  esc_html__(' Sidebar (mobile)?', 'pagoda'),
							'desc'	=> false,
					);
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
		}		
	}
}