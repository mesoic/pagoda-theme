<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Nablafire_Customize_Font_Register {

	function __construct($sanitize_fonts){
		$this->sanitize_fonts = $sanitize_fonts;	
	}

	public function control($wp_customize ,$section, $data_group, $data_keys, $defaults){

 		$settings = array(); $options = array(); 		
		foreach ($data_keys as $key => $data) {

			// Ternary for iterated options
			$iterate = array_key_exists('iterator', $data_group ); 
			$_['option']  = ($iterate) ? '_'.$data_group['iterator'] : '';
			$_['default'] = ($iterate) ? '_' : '';

			// Construct $option  = group_key<_id>	
			$option  = $data_group['group'] . $key . $_['option'];  // group_key<_id> 
			$default = $data_group['group'] . $key . $_['default']; // group_key<_>

			// Assign data_keys['default'] to its value if 'default' key exists in 
			// the data_keys array. Only used for color options since this is the 
			// only case where we to need explicitly pass the default value (for js).
			if ( array_key_exists('default', $data_keys[$key] ) ){
				$data_keys[$key]['default'] = $defaults[$default];
			}	

			// Assign priority if needed
			$priority = array_key_exists('priority', $data_group) ? $data_group['priority'] : 10;

			// Add the setting. 
			$wp_customize->add_setting( new Nablafire_Customize_Font_Setting(
				$wp_customize, $option, 
				array(
					'type'			=> 'option',
					'transport'	 	=> 'postMessage',
					'default'		=>  $defaults[$default],
					'sanitize_callback' => array($this->sanitize_fonts, $data['sanitize'])
				),
				array(
					'data'		=> $data,
					'slug'		=> $data_group['group'], 
					'control'	=> $data_group['group'] . $data_group['id'],
			) ) );
			$settings[$key] = $option;
		}  

 		// Build the control object 
		$wp_customize->add_control( new Nablafire_Customize_Font_Control( 
			$wp_customize, $data_group['group'] . $data_group['id'], 	
		 	array(
	 			'label'	  	  => $data_group['label'],
				'description' => $data_group['desc'],
				'section' 	  => $section,
				'settings'	  => $settings,
				'priority'	  => $priority,
			), 
			$data_keys, $this->sanitize_fonts->font_gen
		) ); 
	}
}