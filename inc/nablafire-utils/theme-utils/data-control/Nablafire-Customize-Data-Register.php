<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Nablafire_Customize_Data_Register {
	
	public function __construct($sanitize_data){
		$this->sanitize_data = $sanitize_data;
	}

	public function control($wp_customize ,$section, $data_group, $data_keys, $defaults){

		$settings = array();  
		foreach ($data_keys as $key => $data) {

			// Ternary for iterated options
			$iterate = array_key_exists('iterator', $data_group ); 
			$_['option']  = ($iterate) ? '_'.$data_group['iterator'] : '';
			$_['default'] = ($iterate) ? '_' : '';

			// Construct $option  = group_key<_id>	
			$option  = $data_group['group'] . $key . $_['option']; 
							
			// Construct $default = key || group_key<_> 
			$keyed   =  array_key_exists( 'keyed' , $data_group ) ? $data_group['keyed'] : false; 
			$default = ($keyed) ? $key :  $data_group['group'] . $key . $_['default']; 

			// Construct the setting defaut. In the case of 'label' there is no default since 
			// its template has no associated input field. Note that for 'label' we should 
			// also set $data['sanitize'] to 'sanitize_pass' in the customizer class.  
			$setting_default = ( $data['type'] != 'label') ? $defaults[$default] : false;
			
			// Priority (optional)
			$priority = array_key_exists('priority', $data_group) ? $data_group['priority'] : 10;

			// Assign data_keys['default'] to its value if 'default' key exists in 
			// the data_keys array. Only used for color options since this is the 
			// only case where we to need explicitly pass the default value (for js).
			if ( array_key_exists('default', $data_keys[$key] ) ){
				$data_keys[$key]['default'] = $setting_default;
			}		

			// Add the setting 	
			$wp_customize->add_setting( new Nablafire_Customize_Data_Setting(
				$wp_customize, $option, 
				array(
					'type'			=> 'option',
					'transport'	 	=> 'postMessage',
					'default'		=>  $setting_default,
					'sanitize_callback' => array($this->sanitize_data, $data['sanitize'])
				),
				array(
					'data'		=> $data,
					'slug'		=> $data_group['group'], 
					'control'	=> $data_group['group'] . $data_group['id'],
			) ) );
						
			// Assign settings array for add_control
			$settings[$key] = $option; 
			
			// Get color of the customize data control box
			$color = array_key_exists( 'color' , $data_group ) ? $data_group['color'] : 'none';
		}

		// Build the control object (i.e. call render content and link)
		$wp_customize->add_control( new Nablafire_Customize_Data_Control(
			$wp_customize, $data_group['group'] . $data_group['id'],	
			array(
				'label'	  	=> $data_group['label'],
				'description'	=> $data_group['desc'],
				'section' 	=> $section,
				'settings'	=> $settings,
				'priority'	=> $priority
			), 
			$data_keys, $color
		) ); 
 	}
}