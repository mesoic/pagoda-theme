<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class pagoda_navbar{
	
	public function __construct($query, $font_gen) {
	 
		$this->font_gen = $font_gen;  // The single instance of Nablafire_Fonts_Autogen()
		$this->query    = $query;     // The single instance of pagoda_query_option()
		$this->font_enqueue = array();

		$this->initialize_navbar();   	  // Expands options via pagoda_query_option()
		if ( user_can( wp_get_current_user() , 'administrator' ) ) {
			$this->write_css_navbar();    // Initializes CSS Autogen and wites CSS data			
		}
	}

	private function initialize_navbar(){

		// Expand all navbar options.
		$this->data = array(
			'main'	 => $this->query->subtable( array('navbar', 'main'   ) ),
			'fonts'  => $this->query->subtable( array('navbar', 'fonts'  ) ),	
			'search' => $this->query->subtable( array('navbar', 'search' ) ),
			'scroll' => $this->query->subtable( array('navbar', 'scroll' ) ),
			'mobile' => $this->query->subtable( array('mobile', 'navbar' ) ),
		); 

		// Process show element selectors
		$this->show = $this->process_selectors();
	}

	private	function process_selectors(){

		// Set up default case 
		$selectors = array(
			'default' => array(
				'home'=> true, 
				'page'=> true, 
				'post'=> true
			)
		);

		// Process selectors
		foreach ( array('home', 'page', 'post') as $_ => $key ){
			$selectors[$key] = array(
				'navbar' => $this->query->option($this->data['main']  , 'show_' . $key),
				'search' => $this->query->option($this->data['search'], 'show_' . $key),
				'scroll' => $this->query->option($this->data['scroll'], 'show_' . $key)
			);
		}
		return $selectors;
	}

	//////////////////////////////////////////////////////////////////////////////
	//								HTML METHODS 								//
	//////////////////////////////////////////////////////////////////////////////	
	public function html_generate_navbar($post_class = '', $post_type = '') {

		// Show element selectors and classes for per-category/per-page
		$_show  = ($post_type  != '') ? $this->show[ $post_type ] : $this->show['none'];		
		$_class = array(
			'navbar' => ($post_class != '') ? 'pagoda-navbar-' . $post_class : '',
			'scroll' => ($post_class != '') ? 'pagoda-scroll-button-' . $post_class : ''
		);	
		// Call templates
		if ( $_show['navbar'] ){ $this->get_navbar_template($_class, $_show); } 
		if ( $_show['scroll'] ){ $this->get_scroll_template($_class, $post_type); } 
	}

	// Navbar template
	public function get_navbar_template($_class, $_show) { ?>

		<div class = "pagoda-navbar <?php echo $_class['navbar'] ?>">

			<div class="pagoda-navbar-elements">
				
				<?php $_img =  $this->query->option($this->data['main'], '_logo_img'); ?>
				
				<?php if ( $_img ): ?>
					<div class="pagoda-logo-wrap">
						<img class="pagoda-logo" src="<?php echo $_img?>"> 
					</div>				
				<?php endif;?>

				<?php if( $_show['search'] ){ get_search_form(true); } ?>

				<div class="pagoda-menu-wrap">
					<?php 
						wp_nav_menu( array(
							'theme_location' => 'primary',
							'menu_id'		 => 'pagoda-nav-primary',	
							'container' 	 => false
						) ); 
					?>
				</div>

				<div class="pagoda-navbar-after">
					<i class="fa fa-bars" aria-hidden="true"></i>
				</div>

			</div>
		</div>

	<?php }

	// Scroll To top icon template
	public function get_scroll_template($_class, $_type) { ?>

		<div class = "pagoda-scroll-button <?php echo $_class['scroll'] ?>">
			<div id = "pagoda-scroll-mobile-<?php echo $_type ?>"></div>
			<div class = "pagoda-scroll-icon">
				<i class="fa fa-caret-up" aria-hidden="true"></i>
			</div>
		</div>
	
	<?php }


	//////////////////////////////////////////////////////////////////////////////////////
	//									CSS METHODS 									//
	//////////////////////////////////////////////////////////////////////////////////////	
	private function write_css_navbar(){ 

		$css_autogen = new Nablafire_CSS_Autogen();

		$this->css  = $css_autogen->comment('Pagoda Machine Generated CSS');
		$this->css .= $this->css_generate_navbar_main($this->data, $css_autogen);
		$this->css .= $this->css_generate_navbar_menu($this->data, $css_autogen);
		$this->css .= $this->css_generate_scroll_icon($this->data, $css_autogen);
		$this->css .= $this->css_generate_navbar_search($this->data, $css_autogen); 
		$this->css .= $this->css_generate_navbar_mobile($this->data, $css_autogen);

		update_option('pagoda_navbar_styles', serialize( $css_autogen->minify($this->css)));
		update_option('pagoda_navbar_fonts', serialize($this->font_enqueue));
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//									NAVBAR MAIN 									//
	//////////////////////////////////////////////////////////////////////////////////////	
	private function css_generate_navbar_main($data, $_css) {

		$css  = $_css->comment(" ------ Navbar Main ------ ");
		
		// Main Navigation div
		$css .= $_css->begin_rule('.pagoda-navbar');
			$css .= $_css->add_rule('background-color'	, $this->query->option($data['main'], '_bg_color'));
			$css .= $_css->add_rule('width' 			, '100%');
			$css .= $_css->add_rule('position'			, 'fixed');
			$css .= $_css->add_rule('padding-top'		, '10px');
			$css .= $_css->add_rule('padding-bottom'	, '10px');
			$css .= $_css->add_rule('z-index'			, '1'); // Important
			$css .= $_css->add_rule('box-sizing' 		, 'border-box');

			$_border = array(
				'size'  => $this->query->option($data['main'], '_bd_size'), 
				'fade'  => $this->query->option($data['main'], '_bd_fade'),
				'color' => $this->query->option($data['main'], '_bd_color'),
				'input_atts' => array(
					'position' => 'bottom',
					'mode'	   => 'default',
			) );
			$css .= $_css->border_properties($_border);

		$css .= $_css->end_rule();

		// Elements container
		$css .= $_css->begin_rule('.pagoda-navbar-elements');
			$css .= $_css->add_rule('margin', '0px' . ' ' . $this->query->option($data['main'], '_mn_squeeze')); 
		$css .= $_css->end_rule();

		// After scroll state
		$css .= $_css->begin_rule('.pagoda-navbar-scroll');
			$css .= $_css->add_rule('background-color' 	, $this->query->option($data['main'], '_sc_color'));
			$css .= $_css->add_rule('padding-top'		,'0px');
			$css .= $_css->add_rule('padding-bottom'	,'0px');	
		$css .= $_css->end_rule();

		// Add transition elements to initial state
		$css .= $_css->begin_rule('.pagoda-navbar');
			$css .= $_css->browser_rules('transition'	, 'all 500ms ease-in-out');
		$css .= $_css->end_rule();

		// Logo container. This CSS is only rendered if image is defined
		if ( $this->query->option($this->data['main'], '_logo_img') ){		
			$css .= $_css->begin_rule('.pagoda-logo-wrap');
				$css .= $_css->add_rule('height' 	, $this->query->option($data['main'], '_logo_height'));
				$css .= $_css->add_rule('float'	 	, 'left');		
				$css .= $_css->add_rule('display'	, 'inline');
			$css .= $_css->end_rule();

			$css .= $_css->begin_rule('.pagoda-logo-wrap > .pagoda-logo');
				$css .= $_css->add_rule('height'	, 'inherit');
			$css .= $_css->end_rule();			
		}

		// Menu container
		$css .= $_css->begin_rule('.pagoda-menu-wrap');

			// If no logo image is specified, then float navbar left
			if ( $this->query->option($this->data['main'], '_logo_img') ){
				$css .= $_css->add_rule('float'	  , 'right' );
				$css .= $_css->add_rule('padding' , '0px 0px 0px 20px');
			} 	
			else {
				$css .= $_css->add_rule('float'	  , 'left' );
				$css .= $_css->add_rule('padding' , '0px 20px 0px 0px');
			}

			$css .= $_css->add_rule('height'		,   $this->query->option($data['main'], '_logo_height'));
			$css .= $_css->add_rule('line-height'	,  $this->query->option($data['main'], '_logo_height'));
		$css .= $_css->end_rule();
		
		// Search containers 
		$css .= $_css->begin_rule('.pagoda-search-wrap');
			$css .= $_css->add_rule('float'			, 'right');
			$css .= $_css->add_rule('padding-left'  , '20px');
			$css .= $_css->add_rule('display'		, 'inline-flex');
			$css .= $_css->add_rule('height'		,  $this->query->option($data['main'], '_logo_height'));
			$css .= $_css->add_rule('line-height'	,  $this->query->option($data['main'], '_logo_height'));
		$css .= $_css->end_rule();

		$targets = array(
			'.pagoda-search-field-wrap',
			'.pagoda-search-submit-wrap',
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->add_rule('display' , 'inline');
		$css .= $_css->end_rule();		

		// Turn off search and float navlinks right for smaller screens
		$css .= $_css->begin_media('all and (max-width:1280px)');
		 	
		 	$css .= $_css->begin_rule('.pagoda-search-wrap');
		 		$css .= $_css->add_rule('display',	'none');
		 	$css .= $_css->end_rule(); 
		
		$css .= $_css->end_media();

		// Responsive hamburger icon. Turn off in laptop mode
		$css .= $_css->begin_rule('.pagoda-navbar-after');
			$css .= $_css->add_rule('display'		,'none');
			$css .= $_css->add_rule('width'			,'0%');
		$css .= $_css->end_rule();
		return $css;
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//									NAVBAR MENU 									//
	//////////////////////////////////////////////////////////////////////////////////////
	// Navbar Main Options + Fonts + Search
	//
	private function css_generate_navbar_menu($data, $_css) {
	
		$css  = $_css->comment(" ------ Navbar Menu ------ ");

		// Menu layout
		$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu');
			$css .= $_css->add_rule('list-style', 'none');
			$css .= $_css->add_rule('line-height', $this->query->option($data['main'], '_logo_height'));
			$css .= $_css->add_rule('height'	,  $this->query->option($data['main'], '_logo_height'));
			$css .= $_css->add_rule('margin'	, '0');// set ul line height = logo height ...^ 
			$css .= $_css->add_rule('padding'	, '0'); 
			$css .= $_css->add_rule('float'		, 'left');
		$css .= $_css->end_rule();
									   	
		// Menu li block layout
		$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li');
			$css .= $_css->add_rule('display'	 , 'inline-block');
			$css .= $_css->add_rule('height'	 , 'inherit');
			$css .= $_css->add_rule('line-height', 'inherit');
			$css .= $_css->add_rule('list-style-position', 'inside');
		$css .= $_css->end_rule();	

		// Menu li:hover accent line
		$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li:hover');
	
			$_border = array(
				'size'  => $this->query->option($data['fonts'], '_ac_size'), 
				'fade'  => $this->query->option($data['fonts'], '_ac_fade'),
				'color' => $this->query->option($data['fonts'], '_ac_color'),

				'input_atts' => array(
					'position' => 'bottom',
					'mode'	   => 'inset'
				)
			);
			$css .= $_css->border_properties($_border);
		$css .= $_css->end_rule();		

		// Menu font enqueue
		$font_fam	= $this->query->option($data['fonts'], '_mn_font_fam');
		$font_var	= $this->query->option($data['fonts'], '_mn_font_var');
		array_push($this->font_enqueue, 
			array('font_fam' => $font_fam, 'font_var' => $font_var));
		
		// Menu link styling
		$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li > a');
			$css .= $_css->add_rule('display'		, 'block');	
			$css .= $_css->add_rule('height'		, 'inherit');
			$css .= $_css->add_rule('text-decoration', 'none');
			$css .= $_css->_literal($this->font_gen->css_fontfamily($font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($font_var));
			$css .= $_css->add_rule('font-size'		, $this->query->option($data['fonts'], '_mn_font_size', 'px'));
			$css .= $_css->add_rule('color'			, $this->query->option($data['fonts'], '_mn_font_color'));
			$css .= $_css->add_rule('padding-left' 	, $this->query->option($data['main'] , '_mn_spacing'));
			$css .= $_css->add_rule('padding-right'	, $this->query->option($data['main'] , '_mn_spacing'));
			$css .= $_css->add_rule('padding-top'	, '3px');
		$css .= $_css->end_rule();

		// Make a hover pad so we do not lose the submenu on scrollover
		$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li.menu-item-has-children > a');
			$css .= $_css->add_rule('padding-bottom'	, '10px');
		$css .= $_css->end_rule();

		// Add downward wedge on menu items with submenus with after pseruo element
		$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li.menu-item-has-children > a:after');
			$css .= $_css->add_rule('display'		, 'inline-block');
			$css .= $_css->add_rule('vertical-align', 'middle');
			$css .= $_css->add_rule('font-family'	, 'FontAwesome');
			$css .= $_css->add_rule('content'		, '"\f0d7"'); //Need double quotes
			$css .= $_css->add_rule('padding-left'	, '5px');
			$css .= $_css->add_rule('font-size'		, '15px');
		$css .= $_css->end_rule();	

		// Set up the positioning of the dropdown
		$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li.menu-item-has-children > ul'); 
			$css .= $_css->add_rule('position'		,'absolute');
			$css .= $_css->add_rule('list-style'	,'none');
			$css .= $_css->add_rule('display'		,'none');
			$css .= $_css->add_rule('width'			,  $this->query->option($data['main'], '_sb_width'));
			$css .= $_css->add_rule('background-color'	, $this->query->option($data['main'], '_sb_color'));
			
			// Set up some reasonable submenu styles if navbar is faded
			$_regex = "/^0(em|vw|vh|cm|mm|in|px|pt)?$/";
			$_fade  = preg_match($_regex, $this->query->option($data['main'], '_bd_fade')) ? false : true;
			$_sz  	= $_fade ? '1px' : $this->query->option($data['main'], '_bd_size'); 			
			$_px  	= $_fade ? '2px' : '0px';

			// Add padding in the faded border case
			$css .= $_css->add_rule('padding'		, $_px);
			$_border = array(
				'size'  => $_sz, 
				'fade'  => $_px,
				'color' => $this->query->option($data['main'], '_bd_color'),
				'input_atts' => array(
					'position' => 'default',
					'mode'	   => 'inset',
			) );
			$css .= $_css->border_properties($_border);
		$css .= $_css->end_rule();
		
		// Display block dropdown on li:hover
		$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li.menu-item-has-children:hover > ul'); 
			$css .= $_css->add_rule('display'		,'block');
		$css .= $_css->end_rule();	

		// Submenu font enqueue
		$font_fam	= $this->query->option($data['fonts'] , '_sb_font_fam');
		$font_var	= $this->query->option($data['fonts'] , '_sb_font_var');
		array_push($this->font_enqueue, 
			array('font_fam' => $font_fam, 'font_var' => $font_var));
	
		// Submenu link styling 
		$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li.menu-item-has-children > ul > li > a'); 
			$css .= $_css->add_rule('text-decoration', 'none');
			$css .= $_css->_literal($this->font_gen->css_fontfamily($font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($font_var));
			$css .= $_css->add_rule('font-size'		, $this->query->option($data['fonts'], '_sb_font_size', 'px'));
			$css .= $_css->add_rule('color'			, $this->query->option($data['fonts'], '_sb_font_color'));
			$css .= $_css->add_rule('padding-left'	, $this->query->option($data['main'], '_mn_spacing'));
			$css .= $_css->add_rule('padding-right'	, $this->query->option($data['main'], '_mn_spacing'));
			$css .= $_css->add_rule('display'  		, 'block');
		$css .= $_css->end_rule();	

		// Submenu link:hover styling
		$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li.menu-item-has-children > ul > li > a:hover'); 
			$css .= $_css->add_rule('background-color' 	, $this->query->option($data['main'], '_sb_hover'));
		$css .= $_css->end_rule();	

		return $css;
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//									NAVBAR SEARCH 									//
	//////////////////////////////////////////////////////////////////////////////////////

	private function css_generate_navbar_search($data, $_css){

		$css 	= $_css->comment(" ------ Navbar Search ------ ");

		// Style Search field
		$css .= $_css->begin_rule('.pagoda-search-field');
			$css .= $_css->add_rule('background-color', $this->query->option($data['search'], 'tx_color'));
			$css .= $_css->add_rule('color'		, $this->query->option($data['search'], 'tx_font_color'));	
			$css .= $_css->add_rule('height'	, '35px');
			$css .= $_css->add_rule('padding'	, '0px 10px');
			$css .= $_css->add_rule('vertical-align', 'middle');
			
			// Set up some reasonable submenu styles if navbar is faded
			$_regex = "/^0(em|vw|vh|cm|mm|in|px|pt)?$/";
			$_fade  = preg_match($_regex, $this->query->option($data['main'], '_bd_fade')) ? false : true;		
			$_sz  	= $_fade ? '1px' : $this->query->option($data['main'], '_bd_size'); 			
			$_px  	= $_fade ? '2px' : '0px';

			$_border = array(
				'size'  => $_sz, 
				'fade'  => $_px,
				'color' => $this->query->option($data['main'], '_bd_color'),
				'input_atts' => array(
					'position' => 'default',
					'mode'	   => 'inset',
			) );
			$css .= $_css->border_properties($_border);

		$css .= $_css->end_rule();

		// Style Search Submit Button
		$font_fam	= $this->query->option($data['search'], '_bt_font_fam');
		$font_var	= $this->query->option($data['search'], '_bt_font_var');
		array_push($this->font_enqueue, 
			array('font_fam' => $font_fam, 'font_var' => $font_var));
		
		$css .= $_css->begin_rule('.pagoda-search-submit');
			$css .= $_css->add_rule('background-color', $this->query->option($data['search'], 'bt_color'));
			$css .= $_css->_literal($this->font_gen->css_fontfamily($font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($font_var));
			$css .= $_css->add_rule('font-size'	, $this->query->option($data['search'], '_bt_font_size') . 'px');
			$css .= $_css->add_rule('color'		, $this->query->option($data['search'], '_bt_font_color'));
			$css .= $_css->add_rule('height'	, '35px');
			$css .= $_css->add_rule('padding'	, '0px 20px');
			$css .= $_css->add_rule('vertical-align', 'middle');

			$_border = array(
				'size'  => $_sz, 
				'fade'  => $_px,
				'color' => $this->query->option($data['main'], '_bd_color'),
				'input_atts' => array(
					'position' => 'default',
					'mode'	   => 'inset',
			) );
			$css .= $_css->border_properties($_border);

		$css .= $_css->end_rule();

		$css .= $_css->begin_rule('.pagoda-search-submit:hover');
			$css .= $_css->add_rule('cursor', 'pointer');
			$css .= $_css->add_rule('background-color', $this->query->option($data['search'], 'bt_hover'));
			$css .= $_css->browser_rules('transition', 'background-color 300ms ease-out');
		$css .= $_css->end_rule();

		return $css;
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//								RESPONSIVE NAVBAR 									//
	//////////////////////////////////////////////////////////////////////////////////////
	// Here we redefine everything. This is a completely different navbar behaviour. We 
	// Introduce a few more mobile options. 
	//
	private function css_generate_navbar_mobile($data, $_css) {

		$css  = $_css->comment(" ------ Responsive Navbar ------ ");

		$css .= $_css->begin_media('all and (max-width:980px)');

		 	// Set up some variables
			$m_height   = $this->query->option($data['mobile'], '_m_height');	
			$m_width    = $this->query->option($data['mobile'], '_m_width');
			$m_subwidth = $this->query->option($data['mobile'], '_m_subwidth');
			$m_left     = ((int)$this->query->option($data['mobile'], '_m_width') - 15).'px';
			
			// Reduce navbar 'squeeze parameter' on mobile
			$css .= $_css->begin_rule('.pagoda-navbar-elements');
				$css .= $_css->add_rule('overflow'	, 'hidden'); 	// Remove all y-scroll overflow 
				$css .= $_css->add_rule('margin'	, '0px 20px');  // Fixed Margins
			$css .= $_css->end_rule();

			// For mobile we will generate a fixed height 50px navbar
			$css .= $_css->begin_rule('.pagoda-navbar');
				$css .= $_css->add_rule('padding-top'		,'0px');
				$css .= $_css->add_rule('padding-bottom'	,'0px');
				$css .= $_css->add_rule('line-height'		,'50px');
				$css .= $_css->add_rule('height'			,'50px');
				$css .= $_css->add_rule('z-index'			,'1'); // Important
			$css .= $_css->end_rule();

			// Swing the logo onto the right side of the page
			$css .= $_css->begin_rule('.pagoda-logo-wrap');
				$css .= $_css->add_rule('height'	, '50px');				
				$css .= $_css->add_rule('float'		, 'right');
				$css .= $_css->add_rule('margin'	, '0px');
				$css .= $_css->add_rule('padding'	, '0px');
			$css .= $_css->end_rule();

			// Collapse the nav-pages div and float left
			$css .= $_css->begin_rule('.pagoda-navbar-pages');
				$css .= $_css->add_rule('margin'	, '0px');
				$css .= $_css->add_rule('padding'	, '0px');
				$css .= $_css->add_rule('width'		, '0px');
				$css .= $_css->add_rule('float'		, 'left');
			$css .= $_css->end_rule();

			// Display block the nav-after div and float left and style 
			// the hamburger icon to match the font color.  
			$css .= $_css->begin_rule('.pagoda-navbar-after');
				$css .= $_css->add_rule('display'	, 'block');
				$css .= $_css->add_rule('height'	, 'inherit');
				$css .= $_css->add_rule('width'		, 'auto');
				$css .= $_css->add_rule('float'		, 'left');		
				$css .= $_css->add_rule('color'		, $this->query->option($data['fonts'], '_mn_font_color'));
				$css .= $_css->add_rule('font-size'	, '25px');
			$css .= $_css->end_rule();

			// Previously menu was in .pagoda-nav-pages. Now we must position absolutely on the 
			// right side of the phone. Note that we also must assign a background color to this 
			// absolutely positioned area. 
			$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu');
				$css .= $_css->add_rule('position'		,'absolute');
				$css .= $_css->add_rule('top'			,'50px');  // align with bottom of navbar
    			$css .= $_css->add_rule('left'			,'0px');   // place on left side of phone
				$css .= $_css->add_rule('padding-left'	,'0px');   // zero the padding
				$css .= $_css->add_rule('margin'		,'0px');   // zero the margins
				$css .= $_css->add_rule('display'		,'none');  
				// Will use jQuery to toggle this to display:block
				$css .= $_css->add_rule('height'		,'auto');  
				// Allow the height to expand to contain all <li>
				$css .= $_css->add_rule('width'			, $m_width); // The width
				$css .= $_css->add_rule('background-color', $this->query->option($data['mobile'], '_mn_color'));

				$_regex = "/^0(em|vw|vh|cm|mm|in|px|pt)?$/";
				$_fade  = preg_match($_regex,$this->query->option($data['main'], '_bd_fade')) ? false : true;
				$_sz  	= $_fade ? '1px' : $this->query->option($data['main'], '_bd_size'); 			
				$_px  	= $_fade ? '2px' : '0px';

				// Add padding in the faded border case
				$css .= $_css->add_rule('padding'		, $_px);
				$_border = array(
					'size'  => $_sz, 
					'fade'  => $_px,
					'color' => $this->query->option($data['main'], '_bd_color'),
					'input_atts' => array(
						'position' => 'default',
						'mode'	   => 'inset',
				) );
				$css .= $_css->border_properties($_border);

			$css .= $_css->end_rule();

			// Responsive Menu will behave as a dropdown 
			$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li');
				$css .= $_css->add_rule('display'		, 'block'); // display block 
				// push all li in from the screen edge a bit. Note that this needs to be here
				// for li hover to fill entire area. We also need to zero the other values. The
				// width will determined by the containing area .menu
				$css .= $_css->add_rule('padding'		, '0px 0px 0px 15px'); 
				$css .= $_css->add_rule('margin'		, '0px');   
				$css .= $_css->add_rule('text-align'	, 'left');
				$css .= $_css->add_rule('line-height'  	, $m_height);  // For vertical centering
				$css .= $_css->add_rule('height'  		, $m_height);
			$css .= $_css->end_rule();
			
			// Set up hover behaviour
			$targets = array(
				'ul#pagoda-nav-primary.menu > li:hover',
				'ul#pagoda-nav-primary.menu > li:focus'
			);
			$css .= $_css->begin_array_rule($targets);
				$css .= $_css->add_rule('border-bottom','0');
				$css .= $_css->add_rule('box-shadow'		, 'none');
				$css .= $_css->browser_rules('transition'	, 'all 250ms ease-in-out');
				$css .= $_css->add_rule('background-color'	, $this->query->option($data['mobile'], '_mn_hover'));
			$css .= $_css->end_rule();
			
			// Similar to the li rules. We also want to inherit the height here from 
			// the li definition
			$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li > a');
				$css .= $_css->add_rule('display'		, 'block' );
				$css .= $_css->add_rule('padding'		, '0px 5px 0px 5px' );
				$css .= $_css->add_rule('margin'		, '0px' );
    			$css .= $_css->add_rule('font-size'		,  
    									 $this->query->option($data['mobile'], '_m_fontsize'));
				$css .= $_css->add_rule('height'  		, 'inherit');	
			$css .= $_css->end_rule();
			
			// Here we need to zero the padding on the li with children to remove the
			// 'hover pad'. We also want to turn off click events so this does not 
			// behave as a link on mobile. 
			$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li.menu-item-has-children > a');
				$css .= $_css->add_rule('padding-bottom' 	, '0px');
				$css .= $_css->add_rule('box-shadow'		, 'none');
				$css .= $_css->add_rule('pointer-events'	, 'none');
			$css .= $_css->end_rule();		

			// For the submenus we must set up another 'absolutely positioned' area to 
			// the right of the li. however it should be positioned 'relative' to its li.
			// we need move this item up by the line height to align with the top of the 
			// li (rather than the bottom) and to the right so it aligns with the right 
			// ends of the dropdown. (width - (left padding = 15px))
			$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li.menu-item-has-children > ul'); 
				$css .= $_css->add_rule('display'	, 'none');  // Submenu is hidden
				$css .= $_css->add_rule('position'	, 'relative'); 
				$css .= $_css->add_rule('top'		, '-'.$m_height); // SHOULD BE -1*LINE HEIGHT
				$css .= $_css->add_rule('left'		, $m_left);       // SHOULD BE THE WIDTH - padding 
				$css .= $_css->add_rule('padding'	, '0px');
				$css .= $_css->add_rule('margin'	, '0px');
				$css .= $_css->add_rule('width'		, $m_subwidth);      // SHOULD BE THE SUBWIDTH 
				$css .= $_css->add_rule('background-color', $this->query->option($data['mobile'], '_sb_color'));	
			
				$_regex = "/^0(em|vw|vh|cm|mm|in|px|pt)?$/";
				$_fade  = preg_match($_regex,$this->query->option($data['main'], '_bd_fade')) ? false : true;
				$_sz  	= $_fade ? '1px' : $this->query->option($data['main'], '_bd_size'); 			
				$_px  	= $_fade ? '2px' : '0px';

				// Add padding in the faded border case
				$css .= $_css->add_rule('padding'		, $_px);
				$_border = array(
					'size'  => $_sz, 
					'fade'  => $_px,
					'color' => $this->query->option($data['main'], '_bd_color'),
					'input_atts' => array(
						'position' => 'default',
						'mode'	   => 'inset',
				) );
				$css .= $_css->border_properties($_border);

			$css .= $_css->end_rule();

			// When we hover on an li with children we want to display block its ul.  
			// to show the submenu
			$targets = array(
				'ul#pagoda-nav-primary.menu > li.menu-item-has-children:hover > ul',
				'ul#pagoda-nav-primary.menu > li.menu-item-has-children:focus > ul'
			);
			$css .= $_css->begin_array_rule($targets); 
				$css .= $_css->add_rule('display'	,'block');
			$css .= $_css->end_rule();
			
			// Set up the submenu li behaviour 
			$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li.menu-item-has-children > ul > li');
				$css .= $_css->add_rule('display'		, 'block');
				$css .= $_css->add_rule('padding'		, '0px 0px 0px 15px');
				$css .= $_css->add_rule('margin'		, '0px');
				$css .= $_css->add_rule('text-align'	, 'left');
				$css .= $_css->add_rule('line-height'	, $m_height);
				$css .= $_css->add_rule('height'  		, $m_height);	
			$css .= $_css->end_rule();
	
			// Set up hover behaviour (same as before)
			$targets = array(
				'ul#pagoda-nav-primary.menu > li.menu-item-has-children > ul > li:hover',
				'ul#pagoda-nav-primary.menu > li.menu-item-has-children > ul > li:focus'
			);
			$css .= $_css->begin_array_rule($targets);
				$css .= $_css->add_rule('border-bottom','0');
				$css .= $_css->add_rule('box-shadow','none');
				$css .= $_css->browser_rules('transition'	, 'all 250ms ease-in-out');
				$css .= $_css->add_rule('background-color'	, $this->query->option($data['mobile'], '_sb_hover'));
			$css .= $_css->end_rule();

			// This is identical to the top level a styling 
			$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li.menu-item-has-children > ul > li > a');
				$css .= $_css->add_rule('display' 	, 'block');
				$css .= $_css->add_rule('padding'	, '0px 5px 0px 5px');
				$css .= $_css->add_rule('margin'	, '0' );
				$css .= $_css->add_rule('font-size'	, $this->query->option($data['mobile'], '_m_subfsize'));
				$css .= $_css->add_rule('height'	,'inherit');
			$css .= $_css->end_rule();

			// Need to 'undo' the hover behaviour from the laptop version.
			$css .= $_css->begin_rule('ul#pagoda-nav-primary.menu > li.menu-item-has-children > ul > li > a:hover');
				$css .= $_css->add_rule('background-color', 'transparent');
			$css .= $_css->end_rule();

		 $css .= $_css->end_media();
		 return $css;
	}		

	//////////////////////////////////////////////////////////////////////////////////////
	//								SCROLL TO TOP ICON 									//
	//////////////////////////////////////////////////////////////////////////////////////

	private function css_generate_scroll_icon($data, $_css){

		$css = $_css->comment(" ------ Scroll Button ------ ");
		// Note that this element needs to have a 'fixed' position. As we want it in the viewport
		// in the same place as we scroll down. We will use some jQuery to fade-in/fade-out on 
		// scrolling, as well as to setup the click behaviour to scroll to the top of the page.    
		//
		// fixed 	= relative to the viewport, 
		// absolute = relative to highest container 
		// relative = relative to the parent element.
		//

		// Style the div
		$css .= $_css->begin_rule('.pagoda-scroll-button');
			$css .= $_css->add_rule('position'		, 'fixed'); 
			$css .= $_css->add_rule('background-color', $this->query->option($data['scroll'], '_bg_color')); 
			$css .= $_css->add_rule('width'			, $this->query->option($data['scroll'], '_ic_size'));
			$css .= $_css->add_rule('height'		, $this->query->option($data['scroll'], '_ic_size'));
			$css .= $_css->add_rule('bottom'		, $this->query->option($data['scroll'], '_position'));
			$css .= $_css->add_rule('right'			, $this->query->option($data['scroll'], '_position'));
			$css .= $_css->add_rule('border-radius'	, $this->query->option($data['scroll'], '_bd_radius'));
			
			$_border = array(
				'fade'  => $this->query->option($data['scroll'], '_bd_fade'),
				'size'  => $this->query->option($data['scroll'], '_bd_size'), 
				'color' => $this->query->option($data['scroll'], '_bd_color'),
				'input_atts' => array(
						'mode' => $this->query->option($data['scroll'], '_bd_inset' ) ? 'inset' : 'default'
			) );
			$css .= $_css->border_properties($_border);

			$css .= $_css->add_rule('z-index'		, '1');	   // Place in top layer
			$css .= $_css->add_rule('display'		, 'none'); // Toggle with jQuery on scroll down 
		$css .= $_css->end_rule();

		// Set up hover behaviour
		$css .= $_css->begin_rule('.pagoda-scroll-button:hover');
			$css .= $_css->add_rule('background-color'	, $this->query->option($data['scroll'] , '_bg_hover'));
			$css .= $_css->add_rule('cursor'			, 'pointer');
			$css .= $_css->browser_rules('transition'	, 'background-color 250ms ease-in-out');
		$css .= $_css->end_rule();

		// Style the arrow element
		$css .= $_css->begin_rule('.pagoda-scroll-icon');
			$css .= $_css->add_rule('height'		, $this->query->option($data['scroll'], '_ic_size'));
			$css .= $_css->add_rule('line-height'	, $this->query->option($data['scroll'], '_ic_size'));
			$css .= $_css->add_rule('color'			, $this->query->option($data['scroll'], '_fa_color'));
			$css .= $_css->add_rule('font-size'		, $this->query->option($data['scroll'], '_fa_size'));
			$css .= $_css->add_rule('text-align'	, 'center');
		$css .= $_css->end_rule();
	
		// Implement 'modernizer' approach for scroll to top mobile toggle. In the scroll icon tempalte 
		// we have attached an empty <div id = pagoda-scroll-mobile-post> etc. We then use some jQuery 
		// to conditionally check the CSS display property of this div. Start out by defining the 
		// default state on these divs with display:inline. When turn icons off in in mobile mode via 
		// the customizer, we set display:none on these divs in an @media query. jQuery will look for 
		// this div and see if display == inline before doing fadeIn() fadeOut(). e.g.
		//
		//   if( $("#pagoda-scroll-mobile-post").length && 
        //   	 $("#pagoda-scroll-mobile-post").css('display') == 'inline'){
        //			$(".pagoda-scroll-button").fadeIn();
        //		}
		// 
		$responsive_scroll = array(
			"#pagoda-scroll-mobile-home",
			"#pagoda-scroll-mobile-page",
			"#pagoda-scroll-mobile-post",
		);
		$css .= $_css->begin_array_rule($responsive_scroll);
			$css .= $_css->add_rule('display', 'inline');
		$css .= $_css->end_rule();

		$css .= $_css->begin_media('all and (max-width:768px)');

			$css .= $_css->begin_rule('#pagoda-scroll-mobile-home');		 		
				$css .= ( strcmp($this->query->option($data['mobile'], 'show_home'), "1" ) == 0 ) ?
							$_css->add_rule('display', 'inline') : 
							$_css->add_rule('display', 'none');
			$css .= $_css->end_rule();

			$css .= $_css->begin_rule('#pagoda-scroll-mobile-page');		 		
				$css .= ( strcmp($this->query->option($data['mobile'], 'show_page'), "1" ) == 0 ) ?
							$_css->add_rule('display', 'inline') : 
							$_css->add_rule('display', 'none');
			$css .= $_css->end_rule();

			$css .= $_css->begin_rule('#pagoda-scroll-mobile-post');		 		
				$css .= ( strcmp($this->query->option($data['mobile'], 'show_post'), "1" ) == 0 ) ?
							$_css->add_rule('display', 'inline') : 
							$_css->add_rule('display', 'none');
			$css .= $_css->end_rule();
		
		$css .= $_css->end_media();

		return $css;
	}
}