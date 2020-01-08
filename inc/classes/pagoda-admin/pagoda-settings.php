<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// Pagoda Settings Page
class pagoda_settings{

	// Class constructor
	public function __construct()
	{  			
		// If theme options are not set, then we can set a default 
		// value to appear in our selector and update the DB. This 
		// is important as these values must be defined by in order 
		// for theme classes to function properly
		if ( !get_option( 'pagoda_settings' ) ){
			$this->options = array(
				'n_buttons' => '4',
				'n_images' 	=> '4',
				'n_sections'=> '5',
			);
			update_option( 'pagoda_settings' , $this->options );
		}
		else {
			$this->options = get_option( 'pagoda_settings' );
		}

		add_action( 'wp_ajax_pagoda_db_destroy', array( $this, 'pagoda_db_destroy') );

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );

		add_action( 'admin_menu', array( $this, 'pagoda_settings_page' ) );

		add_action( 'admin_init', array( $this, 'settings_page_init' ) );
	}

	public function enqueue(){
		wp_enqueue_style(
			'pagoda-settings-admin-css', 
			PAGODA_THEME_URI . 'inc/classes/pagoda-admin/css/pagoda-settings-admin.css'
		);

		wp_enqueue_script(
			'pagoda-settings-admin-js',
			PAGODA_THEME_URI . 'inc/classes/pagoda-admin/js/pagoda-settings-admin.js', 
			array('jquery'),
			'1.0.0', 
			true
		);

		wp_localize_script(
			'pagoda-settings-admin-js', 
			'SETTINGS_AJAX', 
			array('ajaxurl' => admin_url('admin-ajax.php'))
		);
	}

	// This function the an AJAX callback for the db_destroy button in the settings
	// page. It works with an internal option (pagoda_db_destroy) which is a counter
	// for the button. We will use some JS/CSS to style this button and take care of 
	// the AJAX. Note that we are not actually AJAXing anything useful back other 
	// than the click event itself via a simple counter. 
	public function pagoda_db_destroy(){
		
		$dialogue = (string)$_POST['data']['dialogue']; // Dummy. Need $_POST  
		$counter  = get_option( 'pagoda_db_destroy' );  // Get counter from DB

		if ($counter < 4){
			// If counter is less than 4 then increment counter.
			$counter = (int)$counter+1;  // Increment as int
			update_option( 'pagoda_db_destroy', (string)$counter ); // Set option	
			$option  = (string)$counter;
		}
		else {
			// Otherwise wipe DB of pagoda's options. Note we need to keep
			// theme_mod_pagoda in the DB to keep wordpress alive. 
			foreach (wp_load_alloptions() as $key => $value) {
				if (preg_match("/^pagoda_\w*$/", $key)){
					delete_option($key);
				}
			}

			// Settings page options to defaults
			$settings = array(
				'n_buttons'  => '4',
				'n_images'   => '4',
				'n_sections' => '5'
			);
			update_option('pagoda_settings', $settings);		  

			// Reset counter to zero
			$counter = 0;
			update_option( 'pagoda_db_destroy', "0" );   
			$option  = (string)$counter;
		}
		$_json = json_encode($option, true);
		wp_send_json_success($_json);
		wp_die();
	}

	// Method to sync pagoda's options table to what is in the JSON options table.
	// This is called when on the admin page, and it ensures no rogue or orphan 
	// options related to pagoda are in the DB.  
	public function pagoda_sync_options($defaults){

		$_options = array_keys( iterator_to_array( 
			new RecursiveIteratorIterator( new RecursiveArrayIterator($defaults)), true) );

		$_orphans = array();								
		$_exclude = array(
			'pagoda_settings', 
			'pagoda_db_destroy',
			'pagoda_navbar_fonts',
			'pagoda_header_fonts',
			'pagoda_footer_fonts',
			'pagoda_sidebars_fonts',
			'pagoda_pages_fonts',
			'pagoda_posts_fonts',
		);

		foreach (wp_load_alloptions() as $key => $value) {
			
			if (preg_match("/^pagoda_\w*$/", $key)){
				
				$key_exists = false;
				foreach( $_options as $_ => $option ) {
					$key_exists = strpos( $key, $option );			
					if ($key_exists !== false ){break;}
				}
				if ( $key_exists === false &&  !in_array( $key, $_exclude) ){
					delete_option( $key );
				}
			}
		}
	}

	// Add plugin page to the settings menu 
	public function pagoda_settings_page(){
		// To add a section to Settings menu use add_options_page
		// To add a section to Toplevel menu use add_menu_page
		// To add a section to Apperance menu use add_theme_page
		add_theme_page(
			'Pagoda Settings',					// Page Title
			'Pagoda Settings',					// Menu Title
			'manage_options',					// Capability
			'pagoda-settings',					// Menu Slug
			array( $this, 'create_admin_page' )	// Callback 
		);
	}

	// Options page callback
	public function create_admin_page() { 

		update_option('pagoda_db_destroy', "0"); ?>

		<div class="wrap">
			<h1>Pagoda Theme Settings</h1>
			<form method="post" action="options.php">
			<?php
				// This prints out all hidden setting fields
				settings_fields( 'pagoda_settings_group' );
				do_settings_sections( 'pagoda' );
				submit_button();
			?>
			</form>
		</div>
		<?php
	}

	// Register our new option 'sections_options' and add a page 
	// to the wordpress backend settings menu. 
	public function settings_page_init(){		
		// Register a setting in the wordpress DB. This will give 
		// us access to get_option('Option name in DB'). 
		register_setting(
			'pagoda_settings_group', 	// Option group
			'pagoda_settings',	   		// Option name in DB		   
			array( $this, 'sanitize' )	// Sanitize Callback
		);

		// Add a settings section and indicate which function to 
		// call to print it out.
		$section = 'pagoda_settings';
		add_settings_section(
			$section,							// Section ID
			'Pagoda Settings',					// Title
			array( $this, 'print_settings_header' ), // Callback
			'pagoda'					  		// Domain
		);  
		// Create our option and indicate which section it should 
		// appear in as well as the function to call to print it out.  
		add_settings_field(
			'n_images',							 // Option 
			esc_html__('Frontpage Images', 'pagoda'),  // Title 
			array( $this, 'n_images_callback' ),	// Callback
			'pagoda',					 		// Text Domain
			$section							// Section ID	
		);	  

		// -----------------  More fields below ---------------------
		add_settings_field(
			'n_buttons',						// Option 
			esc_html__('Frontpage Buttons', 'pagoda'), // Title 
			array( $this, 'n_buttons_callback' ),   // Callback
			'pagoda',					 		// Text Domain
			$section							// Section ID		   
		);  

		add_settings_field(
			'n_sections',						// Option 
			esc_html__('Frontpage Sections', 'pagoda'), // Title 
			array( $this, 'n_sections_callback' ),  // Callback
			'pagoda',					 		// Text Domain
			$section							// Section ID	
		);	
		add_settings_field(
			'pagoda_db_destroy',				// Option 
			'Restore Pagoda Defaults',			// Title 
			array( $this, 'db_destroy_callback' ),  // Callback
			'pagoda',					 		// Text Domain
			$section							// Section ID	
		);	

	}

	// Sanatize user input (i.e. it should be an int)
	public function sanitize( $input ){
		// Here we only have three settings, but if we have 
		// more then we can add more sanatize clauses here.
		$new_input = array();
		// Sanitize input data for the wp database. Want settings values to be positive and to be 
		// between 0 and some reasonable number. This is done in order to protect the db (not to 
		// have 1000 fields in the customizer in case someone should enter 1000 here). 
		if( isset( $input['n_buttons'] ) ){
			$new_input['n_buttons'] = absint( $input['n_buttons'] );
			$new_input['n_buttons'] = $new_input['n_buttons'] > 10 ? 10 : $new_input['n_buttons'];
			$new_input['n_buttons'] = $new_input['n_buttons'] < 0  ? 0  : $new_input['n_buttons'];
		}
		if( isset( $input['n_images'] ) ) {   
			$new_input['n_images']  = absint( $input['n_images'] );
			$new_input['n_images'] = $new_input['n_images'] > 10 ? 10 : $new_input['n_images'];
			$new_input['n_images'] = $new_input['n_images'] < 1  ? 1  : $new_input['n_images'];
		}

		if( isset( $input['n_sections'] ) ) {   
			$new_input['n_sections'] = absint( $input['n_sections'] );
			$new_input['n_sections'] = $new_input['n_sections'] > 20 ? 20 : $new_input['n_sections'];
			$new_input['n_sections'] = $new_input['n_sections'] < 0  ? 0  : $new_input['n_sections'];
		}
		return $new_input;
	}

	// Print the Section text
	public function print_settings_header(){
		print 'Enter your settings below:';
	}

	// Set our plugin option. On submit, our input field will update nabla_sections_options['n_sections'] 
	// in the DB via options.php. This is a builtin wordpress fuction which handles the SQL Query to the 
	// wp database. 
	public function n_images_callback(){ ?>

		<?php // Get current setting value | generate a default setting ?>
		<?php $_value = isset( $this->options['n_images']) ? esc_attr( $this->options['n_images']) : '1';?>

		<?php // Write the input field ?>
		<p><?php _e('Select the number of images that you would like for the homepage header image set. This is the number of images that will cycle through in the case of an animated background. If you would prefer a non-animated background, you will be able to select from any one of these images as a static image, or an image with parallax effect. The background can be configured in the <em>Pagoda Header Settings</em> customizer panel.','pagoda')?>
		</p>
		<input id="pagoda-settings-2" 
				type="number" min="1" max="10" step="1" 
				name="pagoda_settings[n_images]" 
				value="<?php echo $_value; ?>" 
		/>
		
	<?php }

	public function n_buttons_callback(){ ?>

		<?php // Get current setting value | generate a default setting ?>
		<?php $_value = isset( $this->options['n_buttons']) ? esc_attr( $this->options['n_buttons']) : '1';?>

		<?php // Write the input field ?>

		<p><?php _e('Select the number of buttons that you would like in the homepage header. Customizer sections will automatically be generated for this number of buttons in the <em>Pagoda Header Settings</em> section.', 'pagoda'); ?>
		</p>
		<input id="pagoda-settings-1" 
				type="number" min="0" max="10" step="1" 
				name="pagoda_settings[n_buttons]" 
				value="<?php echo $_value; ?>" 
		/>

	<?php }

	public function n_sections_callback(){ ?>

		<?php // Get current setting value | generate a default setting ?>
		<?php $_value = isset( $this->options['n_sections']) ? esc_attr( $this->options['n_sections']) : '4';?>

		<?php // Write the input field ?>
		<p><?php _e('Select the number of sections that you would like to have in your homepage. Customizer options will automatically be generated for this number of sections in the <em>Pagoda Sections Settings</em> customizer panel. To fill your sections check out the \'widgets\' area in the <em>appearance section</em> of the wordpress backend. Pagoda is \'onepage theme\', so your sections will display vertically in sequence down the page at 100% width.', 'pagoda'); ?>	  
		</p>
		<input id="pagoda-settings-3" 
			type="number" min="0" max="20" step="1" 
			name="pagoda_settings[n_sections]" 
			value="<?php echo $_value; ?>" 
		/>
		<p><em style="font-weight: 600;">
		<?php echo esc_html__('Every section you create in Pagoda is a widgtized area which supports.',
		'pagoda');?>
		</em></p> 
		
		<div style="margin-left: 20px;">
		<ul>
			<li type="square"> Fixed or auto height</li>
			<li type="square"> Grid Layouts for widgets</li>
			<li type="square"> RGBA colors everywhere </li>
			<li type="square"> Parallax or Static Backgrounds</li>
			<li type="square"> Google Fonts Integration</li>
			<li type="square"> and more ... </li>
		</ul> 
		</div>
	<?php }


	public function db_destroy_callback() { ?>

		<div>
		<p style="margin-bottom:10px"><?php _e('<em style="color:red; font-weight:900">WARNING:</em> This will remove all options associated with Pagoda from your wordpress DB. Note that your post and page content will not be deleted. This button strongly recommends you <em>back up your database</em> before continuing. You must click this button <em style="font-weight:900">five times</em> in order to reset Pagoda.', 'pagoda') ?>
		</p>
		
		<input id="pagoda-db-destroy-input" 
				class="pagoda-db-destroy" 
				value="Restore Defaults" 
				type="submit">
		<p id="pagoda-db-destroy-p" style="font-weight:900"></p>
		</div>
	
	<?php }

} 