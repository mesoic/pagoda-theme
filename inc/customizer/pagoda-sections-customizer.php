<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly	

	class pagoda_sections_customizer 
	{

		function __construct($defaults, $settings){

			$this->settings = $settings; // Produced on settings page
	   		$this->defaults = $defaults; // From JSON Options Table

	   		// Instantiate our sanitize classes (do not need font control)
			$this->sanitize_data = new Nablafire_Customize_Data_Sanitize();
			$this->register_data = new Nablafire_Customize_Data_Register($this->sanitize_data);

	   		// Register our customizer
			add_action( 'customize_register',  array( $this, 'register_options' ) );
		}

		function register_options( $wp_customize ) {

 			// Main Panel
			$panel = 'pagoda-section-settings';
	 		$wp_customize->add_panel( $panel, array(
        		'title'             => esc_html__( 'Pagoda Section Settings', 'pagoda' ),
        		'description'       => esc_html__( 'Note that all sections are widgetized areas which can be further customized by adding stock or custom widgets', 'pagoda'),
        		'priority'          => 30,
    			
    		) ); 
	 		$this->register_sections_config_options($wp_customize, $panel, $this->defaults['config']); 
	 		$this->register_sections_display_options($wp_customize, $panel, $this->defaults['display']); 
	 	}

	 	//////////////////////////////////////////////////////////////////////////////////////
		//					  	  	 SECTIONS CONFIG METHODS								//
	 	//////////////////////////////////////////////////////////////////////////////////////
	 	//
	 	// "config" : {
		//		"pagoda_section_data_priority_"		: "10",
		//		"pagoda_section_data_html_id_"		: "#", 
		//		"pagoda_section_data_show_" 		: "1"	
		// },
	 	//
	 	function register_sections_config_options($wp_customize, $panel, $defaults){
	 
	 		// Title Group Options
	 		$section = 'section-data';
			$wp_customize->add_section($section,					
				array(
        			'title'		=> esc_html__('Section Data Settings','pagoda'),
        			'priority'	=> 30,
        			'panel'		=> $panel,
    		) ); 

			$data_group = array(
				'id'	=> 'label',
				'group' => 'pagoda_sections_data_',
				'label'	=> false,
				'desc'	=> false,
				'color'	=> 'red',
			);
			$data_keys = array( 
				'label'=> array(
						'type'	  => 'label',
						'sanitize' => 'sanitize_pass',
						'label'	  => esc_html__('Section Data Options','pagoda'), 
						'desc' 	  => __('This panel allows you to selectively enable and disable frontpage sections, set section <strong>HTML IDs</strong>, as well as select your <strong>section order</strong>. Each section is assigned a priority and sections will be displayed in order of increasing priority. Note that sections with identical priority will be displayed sequentially.', 'pagoda' )	
			) );
			$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

	 		for ($id = 1; $id<=$this->settings['n_sections']; $id++){

	 			$data_group = array(
					'iterator' => $id,
					'id'	=> 'display_' . $id,
					'group'	=> 'pagoda_section_data_',
					'label' => false,
					'desc'	=> false,
					'color' => 'grey',
				);
	 			$data_keys = array(
	 				'html_id' => array(
						'type'	=> 'text',
						'sanitize' => 'sanitize_html_id',
						'label' => esc_html__( 'HTML id for Section', 'pagoda') . ' '.$id,
			        	'desc' 	=> false,
					), 
					'priority'	=> array( 
						'type'	=> 'number',
						'sanitize' => 'sanitize_number_range',
						'range'	=> array('min' => 0, 'max'	=> 120),
						'label'	=> esc_html__( 'Section Priority', 'pagoda' ),
						'desc'	=> false,
					),
					'show'		=> array(
						'type'	=> 'checkbox',
						'sanitize' => 'sanitize_checkbox',
						'label' => esc_html__('Show this Section?', 'pagoda'),
				) );
				$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);
	 		}
    	}

	 	//////////////////////////////////////////////////////////////////////////////////////
		//					  		SECTIONS CUSTOMIZER METHODS								//
	 	//////////////////////////////////////////////////////////////////////////////////////
    	//
		// "display" : {	
		// 		"pagoda_section_display_height_"	: "0",
		// 		"pagoda_section_display_padding_"	: "50px 10vw",	
		// 		"pagoda_section_display_color_"		: "#fff",
		// 		"pagoda_section_display_displace_"	: "0",
		// 		"pagoda_section_display_contain_"	: "1",
		// 		"pagoda_section_display_parallax_"	: "",
		// 		"pagoda_section_display_img_"		: ""
		// }
    	//
	 	function register_sections_display_options($wp_customize, $panel, $defaults){

	 		for ($id = 1; $id<=$this->settings['n_sections']; $id++){

	 			// Title Group Options
	 			$section = 'section-display-'.$id;
				$wp_customize->add_section($section,					
					array(
        				'title'		=> esc_html__('Section','pagoda') . 
        					' ' . $id . ' ' . esc_html__('Data', 'pagoda'),
        				'priority'	=> 30,
        				'panel'		=> $panel,
    			) ); 


					/////////////////////////////////////////////////////////////////////
					// Section Layout 
					$data_group = array(
						'iterator' => $id,
						'id'	=> 'layout_' . $id,
						'group'	=> 'pagoda_section_display_',
						'label' => esc_html__('Section Layout', 'pagoda'),
						'desc'	=> false,
						'color' => 'blue'
					);
					$data_keys = array(
						
						'height' => array(
							'type'  => 'text',
							'sanitize' => 'sanitize_css_unit_value',
							'label'	=> esc_html__( 'Section Height (CSS Units)', 'pagoda' ),
							'desc'	=> esc_html__( 'Select a fixed height for this section units (0=auto).',
								'pagoda')
						),
						'padding' => array(
							'type'  => 'text',
							'sanitize' => 'sanitize_css_padding_shorthand',
							'label'	=> esc_html__( 'Section Padding (CSS Units)', 'pagoda' ),
							'desc'	=> esc_html__( 'Select padding values this section. For horizontal padding, it is recommended to use responsive units (vw)', 
								'pagoda')	
					) );
					$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);


					/////////////////////////////////////////////////////////////////////
					// Background Propteries
					$data_group = array(
						'iterator' => $id,
						'id'	=> 'background_' . $id,
						'group'	=> 'pagoda_section_display_',
						'label' => esc_html__('Section Display Settings', 'pagoda'),
						'desc'	=> false,
						'color' => 'green'
					);
					$data_keys = array(
						'color'		=> array(
							'type'	=> 'color',
							'default' => false, // Assigned in loop
							'sanitize'=> 'sanitize_alpha_color',
							'label'	=> esc_html__('Section Background Color','pagoda'),
							'desc'	=> esc_html__('If no background image is selected then the background will default to this color', 'pagoda')
						),
						'displace' => array(
							'type'	=> 'number',
							'sanitize' => 'sanitize_pass', 
							'label'	=> esc_html__('Image Positioning (%)', 'pagoda'),
							'desc'	=> __('Adjust the horizontal positioning of your background images <em>(0-100%)</em>.', 'pagoda'),
						),
						'contain' => array(
							'type'	=> 'checkbox', 
							'sanitize' => 'sanitize_checkbox',
							'label'	=> esc_html__( 'Contain Background?', 'pagoda' ),
							'desc'	=> esc_html__( 'Scale background images to fill section area?', 
								'pagoda'),	
						), 	
						'parallax'	=> array(
							'type'	=> 'checkbox',
							'sanitize' => 'sanitize_checkbox',
							'label' => esc_html__( 'Parallax Effect', 'pagoda' ),
				 	    	'desc' 	=> esc_html__( 'Enable parallax effect for this section\'s background image?', 'pagoda'),						
					) );
					$this->register_data->control($wp_customize, $section, $data_group, $data_keys, $defaults);

					// Background Image
					$option = 'pagoda_section_display_img_';
					$wp_customize->add_setting( $option.$id, array(
        				'type'          => 'option',
       					'transport'     => 'refresh',
       	 				'default'		=> $defaults[$option],
       	 				'sanitize_callback' => array($this->sanitize_data, 'sanitize_image')
	    			) );
					$wp_customize->add_control( new WP_Customize_Image_Control( 
						$wp_customize, $option.$id, 
						array(
							'label'       => esc_html__( 'Select Background Image', 'pagoda' ),
							'section'     => $section,
							'priority'	  => 2,
						)
					) );
			} // Endfor 
	 	} // End Sections

	}// End Class