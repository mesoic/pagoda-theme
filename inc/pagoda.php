<?php

	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	class pagoda {

	    public static $instance = null;

		private function __construct() {

			//////////////////////////////////////////////////////////////////////////
			//				  	       	 	PAGODA INIT				 				//
			//////////////////////////////////////////////////////////////////////////
			// 0) Generate Settings Page if admin and plugin activation
			if(is_admin()){$this->settings_page = new pagoda_settings();}
			if(is_admin()){$this->tgmpa 		= new pagoda_tgmpa();}

			// 1) Theme settings which are set via settings page. 
			$this->settings = get_option('pagoda_settings');
			
			// 2) Theme defaults which are stored in the options table (JSON)
			$defaults_path  = 'inc/classes/pagoda-admin/js/json/pagoda-options-table.json'; 
			$defaults_json  = file_local_contents( PAGODA_THEME_PATH . $defaults_path );
			$this->defaults = json_decode($defaults_json, true);
			if($this->defaults === null){echo "JSON Error. Check Options Table"; die();}
			if(is_admin()){$this->settings_page->pagoda_sync_options($this->defaults);}

			// 3) Query_Option() and Page_Tree() for JSON options tables and parsing pages	
			$this->query 	 = new Nablafire_Query_Option($this->defaults, true);
			$this->page_tree = new Nablafire_Page_Tree();

			// 4) Nablafire Utilities
			$utils_path   = PAGODA_THEME_PATH . 'inc/nablafire-utils/theme-utils/';
			$utils_uri	  = PAGODA_THEME_URI  . 'inc/nablafire-utils/theme-utils/';
			$this->utils  = new Nablafire_Theme_Utils($utils_path, $utils_uri); 

			// 5) Initialize the theme
			$this->pagoda_initialize();

			// 6) Initialize the customizer
			$this->pagoda_customizer();

			//////////////////////////////////////////////////////////////////////////
			//				  	       		WP HOOKS 		 					  	//
			//////////////////////////////////////////////////////////////////////////
			add_action( 'wp_head' , array($this, 'pagoda_enqueue_fonts'));

			add_action( 'wp_enqueue_scripts', array($this, 'pagoda_enqueue_scripts'));

			add_action( 'admin_enqueue_scripts', array( $this, 'pagoda_enqueue_admin_scripts' ));

			add_action( 'after_setup_theme' , array($this, 'pagoda_theme_support'));
		}
		
		//////////////////////////////////////////////////////////////////////////
		//				  	       	 GET INSTANCE		 					  	//
		//////////////////////////////////////////////////////////////////////////
		// Access the single instance of this class. This function is called by 
		// functions.php and is the second step in bootstraping the theme. 
		public static function get_instance() {

			if ( self::$instance==null ) {
				self::$instance = new pagoda();
			}
			return self::$instance;
		}

		//////////////////////////////////////////////////////////////////////////
		//				  	       	AUTOGEN CLASSES		 					  	//
		//////////////////////////////////////////////////////////////////////////	
		private function pagoda_initialize() {

			$this->navbar = 
				new pagoda_navbar(
					$this->query, 
					$this->utils->font_gen
				);

			$this->header = 
				new pagoda_header(
					$this->query, 
					$this->utils->font_gen, 
					$this->settings, 
					$this->navbar
				);
	
			$this->sections = 
				new pagoda_sections(
					$this->settings, 
					$this->query
			   	);

			$this->footer = 
				new pagoda_footer(
					$this->query, 
					$this->utils->font_gen
				);
			
			$this->sidebars = 
				new pagoda_sidebars(
					$this->query, 
					$this->utils->font_gen
				);

			$this->pages = 
				new pagoda_pages(
					$this->query, 
					$this->utils->font_gen, 
					$this->page_tree, 
					$this->navbar				
				);

			$this->posts = 
				new pagoda_posts(
					$this->query, 
					$this->utils->font_gen, 
					$this->page_tree, 
					$this->navbar
				);

			$this->pagoda_styles();  	
		}

		//////////////////////////////////////////////////////////////////////////
		//				  	       CUSTOMIZER CLASSES		 					//
		//////////////////////////////////////////////////////////////////////////
		private function pagoda_customizer() {

			$customize_config = $this->defaults['config']['panels'];
			$this->config_customizer = 
				new pagoda_config_customizer(
					$this->defaults['config'], 
					$this->sidebars,
					$this->page_tree
				);

			if ( $this->query->option($customize_config, 'header'))	
				$this->header_customizer = 
					new pagoda_header_customizer(
			 			$this->defaults['header'], 
			 			$this->utils->font_gen, 
			 			$this->settings
			 		);

			if ( $this->query->option($customize_config, 'navbar'))	
				$this->navbar_customizer = 
			 		new pagoda_navbar_customizer(
			 			$this->defaults['navbar'], 
			 			$this->utils->font_gen
			 		);

			if ( $this->query->option($customize_config, 'sections'))	 
				$this->sections_customizer = 
			 		new pagoda_sections_customizer(
			 			$this->defaults['sections'], 
			 			$this->settings
			 		);

			if ( $this->query->option($customize_config, 'footer'))	
				$this->footer_customizer = 
			 		new pagoda_footer_customizer(
			 			$this->defaults['footer'], 
			 			$this->utils->font_gen
			 		);

			if ( $this->query->option($customize_config, 'sidebars'))	
				$this->sidebars_customizer = 
			 		new pagoda_sidebars_customizer(
			 			$this->defaults['sidebars'], 
			 			$this->utils->font_gen, 
			 			$this->sidebars
			 		);	

 			if ( $this->query->option($customize_config, 'pages'))		
				$this->pages_customizer = 
					new pagoda_pages_customizer(
						$this->defaults['pages'], 
						$this->utils->font_gen, 
						$this->page_tree
					);

			if ( $this->query->option($customize_config, 'posts'))
				$this->posts_customizer = 
					new pagoda_posts_customizer(
						$this->defaults['posts'], 
						$this->utils->font_gen, 
						$this->page_tree
					);

			if ( $this->query->option($customize_config, 'mobile'))	
				$this->mobile_customizer = 
					new pagoda_mobile_customizer(
						$this->defaults['mobile'],
			 			$this->settings,
			 			$this->sidebars
					);  	
		}

		private function pagoda_styles() {

			// New CSS is only written if the user is admin.
			if ( user_can( wp_get_current_user() , 'administrator' ) ) {

				// Access filesystem API (for autogen)					
				global $wp_filesystem;	

				// Retrieve CSS data from the DB
				$css_path = PAGODA_THEME_PATH . 'css/' . 'pagoda-autogen.min.css';
				$css_data = array(
					'navbar'	=> unserialize( get_option( 'pagoda_navbar_styles' ) ),
					'header'	=> unserialize( get_option( 'pagoda_header_styles' ) ),
					'sections'	=> unserialize( get_option( 'pagoda_sections_styles') ),
					'footer' 	=> unserialize( get_option( 'pagoda_footer_styles' ) ),
					'sidebars'	=> unserialize( get_option( 'pagoda_sidebars_styles' ) ),
					'pages'		=> unserialize( get_option( 'pagoda_pages_styles' ) ),
					'posts'		=> unserialize( get_option( 'pagoda_posts_styles' ) ),
				);

				// Write to file
				if(!$wp_filesystem->put_contents( $css_path, implode( $css_data ), 0644) ) {
				    return __('Failed to generate CSS file', 'pagoda');
				}
			}
		}	


		public function pagoda_theme_support() {

			load_theme_textdomain( 'pagoda', get_template_directory() . '/languages' );
			
			add_theme_support( 'title-tag' );

			add_theme_support( 'post-thumbnails' ); 

			add_theme_support( 'automatic-feed-links' );

			add_editor_style( PAGODA_THEME_URI . 
				'inc/classes/pagoda-admin/css/pagoda-editor-styles.css' );

    		register_nav_menu( 'primary', __( 'Pagoda Navbar', 'pagoda') );
    	}

    	public function pagoda_enqueue_fonts(){ 	
    		// Each of these options is set in the css_autogen method in the 
    		// each respective section. css_autogen only occurs on is_admin
    		// so we only update these options if we are an administrator
	    	$pagoda_fonts = array_merge(
	    		unserialize( get_option('pagoda_navbar_fonts'	, true ) ),
 				unserialize( get_option('pagoda_header_fonts' 	, true ) ),		
		  		unserialize( get_option('pagoda_footer_fonts'	, true ) ),
   		    	unserialize( get_option('pagoda_pages_fonts' 	, true ) ),
   				unserialize( get_option('pagoda_posts_fonts'	, true ) ),
   				unserialize( get_option('pagoda_sidebars_fonts'	, true ) )
			);
			$this->utils->font_gen->enqueue_fonts($pagoda_fonts);
		}

		// Note that we enqueue scripts AFTER we are done autogenerating CSS. 
		public function pagoda_enqueue_scripts() { 

			wp_enqueue_style('pagoda-main-css'  	, PAGODA_THEME_URI . 'css/pagoda-main.css');
			wp_enqueue_style('pagoda-autogen-css'	, PAGODA_THEME_URI . 'css/pagoda-autogen.min.css');
			wp_enqueue_style('font-awesome-css'		, PAGODA_THEME_URI . 'css/dev/font-awesome.min.css');
			wp_enqueue_style('pagoda-user-css'  	, PAGODA_THEME_URI . 'css/pagoda-user.css');

			wp_enqueue_script('comment-reply');
			wp_enqueue_script(
				'pagoda-main-js',
				PAGODA_THEME_URI . 'inc/classes/pagoda-admin/js/pagoda-main.js', 
				array('jquery'),
				'1.0.0', 
				true
			);
		}
		
		public function pagoda_enqueue_admin_scripts() { 

			$this->pagoda_enqueue_fonts();
			
			wp_enqueue_style(
				'pagoda-sections-admin-css',
				PAGODA_THEME_URI . 'inc/classes/pagoda-admin/css/pagoda-sections-admin.css'
			);

			wp_enqueue_script( 
				'pagoda-sections-admin-js',
				PAGODA_THEME_URI . 'inc/classes/pagoda-admin/js/pagoda-sections-admin.js', 
				array('jquery'),
				'1.0.0', 
				true 
			);
		}
	}
