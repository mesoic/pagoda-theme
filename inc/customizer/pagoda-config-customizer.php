<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly	

	class pagoda_config_customizer 
	{
		function __construct($defaults, $sidebars, $page_tree){

			$this->defaults  = $defaults;  // From JSON Options Table
			$this->page_tree = $page_tree; // For Category Headers
			$this->sidebars  = $sidebars->get_sidebars(); // For Frontpage Sidebar Select

			// Instantiate our sanitize and register classes
			$this->sanitize_data = new Nablafire_Customize_Data_Sanitize();
			$this->register_data = new Nablafire_Customize_Data_Register($this->sanitize_data);

			add_action( 'customize_register',  array( $this, 'register_options' ) );
		}


		function register_options( $wp_customize ) {

 			// Main Panel
			$section = 'pagoda-customizer-configuration';
	 		$wp_customize->add_section( $section, array(
				'title'		=> esc_html__( 'Pagoda Customizer Panels', 'pagoda' ),
				'priority'	=> 20,
			) ); 

	 		$this->register_cusotmize_config_options($wp_customize, $section , $this->defaults['panels']); 
	 		$this->update_static_front_page($wp_customize,'static_front_page', $this->defaults['display']);
		}	

		function register_cusotmize_config_options($wp_customize, $section, $defaults){

			// Label 
			$data_group = array(
				'id'	=> 'label',
				'group' => 'pagoda_customize_show_',
				'label'	=> false,
				'desc'	=> false,
				'color'	=> 'red',
			);
			$data_keys = array( 
				'label'=> array(
						'type'	  => 'label',
						'sanitize' => 'sanitize_pass',
						'label'	  => esc_html__('Customizer Selective Loading','pagoda'), 
						'desc' 	  => __('This panel allows you to selectively enable and disable loading of Pagoda\'s customizer panels. Disabling customizer panels that you are not actively working with will dramatically <strong> improve the loading time of </strong> Pagoda\'s customizer. <br><br> Note that all panels are disabled by default, and <strong>no data is lost by selectively enabling or disabling panels.</strong>', 'pagoda' )	
			) );
			$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

			// Customizer Switches
			$data_group = array(
				'id'	=> 'data',
				'group' => 'pagoda_customize_show_',
				'label'	=> esc_html__('Customizer Panel Display Switches', 'pagoda'),
				'desc'	=> esc_html__('Please refresh the customizer for settings to take effect', 
					'pagoda' ),
				'color'	=> 'blue',
			);
			$data_keys = array(
				"header" 	=> array(
					'type'	=> 'checkbox',
					'sanitize' => 'sanitize_checkbox',
					'label'	=> esc_html__( 'Show Header Panel?', 'pagoda' ),
				),
				"navbar" 	=> array(
					'type'	=> 'checkbox',
					'sanitize' => 'sanitize_checkbox',
					'label'	=> esc_html__( 'Show Navbar Panel?', 'pagoda' ),
				),			
				"sections"  => array(
					'type'	=> 'checkbox',
					'sanitize' => 'sanitize_checkbox',
					'label'	=> esc_html__( 'Show Sections Panel?', 'pagoda' ),
				),
				"footer" 	=> array(
					'type'	=> 'checkbox',
					'sanitize' => 'sanitize_checkbox',
					'label'	=> esc_html__( 'Show Footer Panel?', 'pagoda' ),
				),
				"sidebars" 	=> array(
					'type'	=> 'checkbox',
					'sanitize' => 'sanitize_checkbox',
					'label'	=> esc_html__( 'Show Sidebars Panel?', 'pagoda' ),
				),
				"pages"		=> array(
					'type'	=> 'checkbox',
					'sanitize' => 'sanitize_checkbox',
					'label'	=> esc_html__( 'Show Pages Panel?', 'pagoda' ),
				),
				"posts"		=> array(
					'type'	=> 'checkbox',
					'sanitize' => 'sanitize_checkbox',
					'label'	=> esc_html__( 'Show Posts Panel?', 'pagoda' ),
				),
				"mobile"	=> array(
					'type'	=> 'checkbox',
					'sanitize' => 'sanitize_checkbox',
					'label'	=> esc_html__( 'Show Responsive Panel?', 'pagoda' ),
			) );
			$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
		}

		// Note that 'static_front_page' is a default customizer panel. It is defined in 
		// wp-includes/class-wp-customize-manager.php
		public function update_static_front_page($wp_customize, $section, $defaults){

			$data_group = array(
				'id'	=> 'group',
				'group' => 'pagoda_customize_home_',
				'label' => esc_html__('Front Page Display Options','pagoda'),
				'desc'	=> __('These settings apply when rendring the <strong>Front page(latest posts)</strong> or <strong>Posts Page(static page)</strong>', 'pagoda'),
				'color' => 'blue'
			);
			$data_keys = array( 
				
				'excerpt' 	=> array(
					'type'	=> 'checkbox',
					'sanitize' => 'sanitize_checkbox',
					'label'	=> esc_html__( 'Show Post Excerpts?', 'pagoda' ),
					'desc'	=> __( 'Show the full post or post excerpts?', 'pagoda')	
				),
				'featured' 	=> array(
					'type'	=> 'checkbox',
					'sanitize' => 'sanitize_checkbox',
					'label'	=> esc_html__( 'Show Featured Images?', 'pagoda' ),
					'desc'	=> __( 'Featured images will be displayed if they have been assigned', 'pagoda')	
				),
				'sidebar' 	=> array(
					'type'	=> 'checkbox',
					'sanitize' => 'sanitize_checkbox',
					'label'	=> esc_html__( 'Show Home Sidebar?', 'pagoda' ),
					'desc'	=> false
			) );
			$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
		}
	}