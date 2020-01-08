<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class pagoda_pages{

	public function __construct($query, $font_gen, $page_tree, $navbar){
	
		$this->font_gen = $font_gen;  // The single instance of Nablafire_Fonts_Autogen()
		$this->navbar   = $navbar;	  // The single instance of pagoda_navbar()	
		$this->query    = $query;     // The single instance of pagoda_query_option()
		$this->font_enqueue = array();	
		
		// Initialize templating engine. This is a container class which holds templates
		// for various items that are called when rendering the content area. 
		$this->templates = new Nablafire_Templates();

		// In this class, we will be using the ID field of each top level page to generate 
		// unique classes for each toplevel page. All child pages of a given toplevel page 
		// will carry this class id. 	
		$this->page_tree = $page_tree;
		$this->topLevel  = $this->page_tree->get_all_toplevel();

		// Create an array that will store the fontstrings.   
		$this->initialize_pages(); // Initializes Query Option
		if ( user_can( wp_get_current_user() , 'administrator' ) ) {
			$this->write_css_pages();  // Initializes CSS Autogen 
		}

		// Post metaboxes 
		add_action( 'load-post.php'		, array($this, 'metabox_init' ) );
		add_action( 'load-post-new.php'	, array($this, 'metabox_init' ) );
	}
	

	//////////////////////////////////////////////////////////////////////////////////////
	//									INIT METHODS 									//
	//////////////////////////////////////////////////////////////////////////////////////

	// Query DB to check for existence of page
	private function check_slug_exists($post_name) {		
		global $wpdb;
		$query  = "SELECT post_name FROM wp_posts WHERE post_name = '" . $post_name . "'";
		$result = $wpdb->get_row( $query , 'ARRAY_A');

		if( $result !== NULL && strcmp( $post_name, 'home' ) != 0) {
			return true;
		} else {
			return false;
		}
	}

	// Wrapper for wp_insert_post
	private function generate_page( $page ){

		$page_check = get_page_by_title( $page['post_title'] );
		if( !isset($page_check->ID) && !$this->check_slug_exists($page['post_slug'])){
			$ID = wp_insert_post($page);
			return $ID;
		}
		else {
			$_post = get_page_by_title( $page['post_title'] );
			return $_post->ID;
		}
	}

	// Initialize Themerun. Generate 'home' and 'blog' pages upon theme activation
	private function initialize_themerun(){

		// Use a static front page
		$home_page = $this->generate_page( array(
			'post_type' 	=> 'page',
			'post_title' 	=> 'Home',
			'post_content'	=> '',
			'post_status' 	=> 'publish',
			'post_author'	=> 1,
			'post_slug' 	=> 'home'
		) );
		update_option( 'page_on_front', $home_page );
		update_option( 'show_on_front', 'page' );

		// Set the blog page
		$blog_page = $this->generate_page( array(
			'post_type' 	=> 'page',
			'post_title' 	=> 'Blog',
			'post_content' 	=> '',
			'post_status' 	=> 'publish',
			'post_author' 	=> 1,
			'post_slug' 	=> 'blog'
		) );	
		update_option( 'page_for_posts', $blog_page );
	}

	private function initialize_pages(){

		// Setup homepages on activate
		if (isset($_GET['activated']) && is_admin()){
			$this->initialize_themerun();
		}

		// Expand all page options.
		$this->data = array(
			'display' => $this->query->subtable( array('pages' , 'display') ),
			'scroll'  => $this->query->subtable( array('navbar', 'scroll' ) ),
		);

		// Iterated page options. Initialized outside main array as we 
		// will also pack height and class into this data structure.  
		$this->data['pages'] = $this->process_pages($this->data);
	}

	private function process_pages($data){

		// Obtain Pages Subtable 
		$data_i  = $this->query->subtable( array('pages', 'data'), $this->topLevel );  

		// For each toplevel page, we will create an array which will hold the header css
		// class as well as the relevant customizer options. The key will be the toplevel 
		// ID, and the there will be three values. 
		//
		//	['options'] = the options associate with the header (iterated our uniterated).
		//	['class']   = the CSS class to assign to the page. 

		// 1) First, check if per page headres have been activated for all pages. This 
		//    can be outside the loop in order to to avoid unnecessary DB queries. 
		$perPage = $this->query->option($data_i[0]['header'], 'styles_activate');
		$height  = $this->query->option($data['display'], 'height');	
		$page_data = array();

		// Loop through all pages and assign the corresponding options/class
		foreach($this->topLevel as $_ => $ID){
			
			// 2) Create the array to hold our options for a given toplevel page. 
			$page_data[$ID] = array();

			// 3) Check if we want to inerit uniterated options for a given page. 
			$inherit = ($ID != 0 ) ? $this->query->option($data_i[$ID]['header'], 'inherit' ) : true;

			// If we DO want per page headers (activate = true) and we DO NOT want to inherit
			// from general settings ($inherit = false), then use iterated options. Otherwise 
			// use general options. 
			if ($perPage && !$inherit && count($this->topLevel) < 12 ){
				$page_data[$ID]	= $data_i[$ID]; // Need to add height ...
				$page_data[$ID]['header']['pagoda_page_display_mn_height'] = $height;
				$page_data[$ID]['class'] = $ID; 
			}
			else {
				$page_data[$ID]	= $data_i["0"]; // Need to add height ...
				$page_data[$ID]['header']['pagoda_page_display_mn_height'] = $height;
				$page_data[$ID]['class'] = $ID; 
			}	
		}	
		return $page_data;
	}

	// Cache toplevel page ID: Note that $_ID is the return value of get_the_ID().
	// If this is not a WP_Post object then get_the_ID() returns bool(false). 
	// Note that, a call to get_toplevel(false) will return 0, such that the
	// the classes will be zero terminated (e.g. pagoda-page-header-wrap-0). 
	public function get_page_class($_ID) {
		$top_level  = $this->page_tree->get_toplevel($_ID);  	
		return $this->data['pages'][$top_level]['class']; 	
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//									HTML METHODS 									//
	//////////////////////////////////////////////////////////////////////////////////////
	// Pages are implemented as a minilaistic version of posts. Pages only display the 
	// page thumbnail and intra-page pagination. They are designed to be staic elements
	public function write_html_header($_ID){ 

		$page_class = $this->get_page_class($_ID); ?>	

		<div class="pagoda-page-header-wrap-<?php echo $page_class?>">
	
			<div class="pagoda-page-header-frame-<?php echo $page_class?>">
	
				<?php $this->navbar->html_generate_navbar($page_class, 'page') ?>
	
			</div>
	
		</div>

	<?php }

	public function write_html_page($_ID){ ?>

		<?php $page_class = $this->get_page_class($_ID); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class('blog-post'); ?>>
		
			<div class="entry-content-page entry-content-page-<?php echo $page_class?>">
		
				<?php 
					$this->write_content_header($_ID);
					$this->templates->content_template();
					$this->write_content_footer($_ID);
				?>
		
			</div>
		
		</article>

	<?php }

	public function write_content_header($_ID){ ?>

		<div class="entry-content-header">
			
		<?php 
			// Featured Image
			if ( has_post_thumbnail() && 
				 get_post_meta($_ID, 'show_featured_image', true)){
	
				$this->templates->thumbnail_template(
					get_post_meta( get_the_ID(), '_size_featured_image', true) );
			}
		?>

		</div>

	<?php }

	public function write_content_footer($_ID){ ?>

		<?php $page_class = $this->get_page_class($_ID); ?>

		<div class="entry-content-footer">

			<?php $this->templates->link_pages_template($page_class); ?>

		</div>

	<?php }

	//////////////////////////////////////////////////////////////////////////////////////
	//									 CSS  METHODS 									//
	//////////////////////////////////////////////////////////////////////////////////////
	// This function will autogenerate the stylesheets for all buttons based on our options 
	// defined in the WP database. These are set by the customizer class. If the option is 
	// not set then we will render the CSS via values in the defaults table ...  				
	
	public function write_css_pages(){ 
		$css_autogen = new Nablafire_CSS_Autogen();		
	
		$this->css  = $css_autogen->comment('Pagoda Posts Machine Generated CSS');
		$this->css .= $this->css_generate_headers_a($this->data, $css_autogen);

		$this->css .= $this->css_generate_layout_u($this->data, $css_autogen);
		$this->css .= $this->css_generate_layout_i($this->data, $css_autogen);
		
		$this->css .= $this->css_generate_content_u($this->data, $css_autogen);
		$this->css .= $this->css_generate_content_i($this->data, $css_autogen);
	
		$this->css .= $this->css_generate_meta_u($this->data, $css_autogen);
		$this->css .= $this->css_generate_meta_i($this->data, $css_autogen);

		update_option('pagoda_pages_styles', serialize($css_autogen->minify($this->css)));
		update_option('pagoda_pages_fonts' , serialize($this->font_enqueue));	
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//							 PER PAGE HEADERS PROPERTIES							//
	//////////////////////////////////////////////////////////////////////////////////////

	private function css_generate_headers_a($data, $_css){

		$is_written = array();
		$css = $_css->comment(" ------ Per Category Headers ------ ");

		// Now we want to have a small a CSS file as possible, so we will only write the CSS
		// that we need. Some of the pages carry the same class, so as we loop through the 
		// pages we will need to check if the corresponding CSS has already been written. 
		// Thus we will have some control flow on the $is_written array in our foreach loop.
		foreach($data['pages'] as $ID => $_) {
			
			if ( in_array($_['class'], $is_written) ){continue;}
	
			else{
				array_push($is_written, $_['class']);

				// Write CSS for header height and background color
				$css .= $_css->begin_rule('.pagoda-page-header-frame-' . $_['class']);
				$css .= $_css->add_rule('height'		  , $this->query->option($_['header'], '_mn_height'));
				$css .= $_css->add_rule('background-color', $this->query->option($_['header'], '_bg_color'));
			
				// Only write backgrond image rules if a background has been selected
				$_img = $this->query->option($_['header'] , '_img' );
				if ($_img != ''){
					$css .= $_css->add_background_image($_img);
					$css .= $_css->add_rule('background-size'  , 'cover');
				}
				$css .= $_css->end_rule();

				// Here we use css 'chain selectors' (no spaces between classes) to target 
				// the specific page navbar by multiple classes. The same applies to styling 
				// the behaviour when scrolling.
				$css .= $_css->begin_rule('.pagoda-navbar.pagoda-navbar-'.$_['class']);
				$css .= $_css->add_rule('background-color',	$this->query->option($_['navbar'], '_nv_color'));
				$css .= $_css->end_rule();
		
				// For submenus
				$css .= $_css->begin_rule(
					'.pagoda-navbar-' . $_['class'] . ' ul > li.menu-item-has-children > ul');
				$css .= $_css->add_rule('background-color',	$this->query->option($_['navbar'], '_nv_color'));
				$css .= $_css->end_rule();		
		
				// The navbar color on scroll
				$css .= $_css->begin_rule(
					'.pagoda-navbar.pagoda-navbar-'. $_['class'] . '.pagoda-navbar-scroll');
				$css .= $_css->add_rule('background-color',	$this->query->option($_['navbar'], '_sc_color'));
				$css .= $_css->end_rule();
			
				// The search box input field (must target from header-frame)
				$css .= $_css->begin_rule(
					'.pagoda-navbar.pagoda-navbar-'. $_['class'] . ' .pagoda-search-field');
				$css .= $_css->add_rule('background-color',	$this->query->option($_['navbar'], '_sr_color'));
				$css .= $_css->end_rule();
			
				// Repeat the same procedure for the scroll to top icon
				$css .= $_css->begin_rule(
					'.pagoda-scroll-button.pagoda-scroll-button-' . $_['class']);
	
					$css .= $_css->add_rule('background-color',	$this->query->option($_['scroll'], '_bg_color'));	

					// Need to actually rewrite the whole border rule to change the color. 
					// size and fade come from the navbar settings.
					$_border = array(
						'size'  => $this->query->option($this->data['scroll'], '_bd_size'), 
						'fade'  => $this->query->option($this->data['scroll'], '_bd_fade'),
						'color' => $this->query->option($_['scroll']   , '_bd_color'),
						'input_atts' => array(
							'mode' => $this->query->option($this->data['scroll'], '_bd_inset' ) ? 'inset' : 'default'
					) );
					$css .= $_css->border_properties($_border);

				$css .= $_css->end_rule();

				$css .= $_css->begin_rule(
					'.pagoda-scroll-button.pagoda-scroll-button-'.$_['class'].':hover');
				$css .= $_css->add_rule('background-color',	$this->query->option($_['scroll'], '_bg_hover'));
				$css .= $_css->end_rule();

				$css .= $_css->begin_rule(
					'.pagoda-scroll-button.pagoda-scroll-button-'.$_['class'] .' > .pagoda-scroll-icon');
				$css .= $_css->add_rule('color'			  ,	$this->query->option($_['scroll'], '_fa_color'));
				$css .= $_css->end_rule();
			}//End else
		} // End foreach 

		return $css;
	}


	//////////////////////////////////////////////////////////////////////////////////////
	//							CONTENT AREA LAYOUT AND DISPLAY 						//
	//////////////////////////////////////////////////////////////////////////////////////
	
	private function css_generate_layout_u($data, $_css){

		$css = $_css->comment(" ------ Page Layout ------ ");		

		// This is the toplevel container. It applies to pages and posts. Here we only 
		// set so the footer does not collapse onto the header for a complely empty 
		// page or post. Note that this CSS rule is not needed in the posts class.
		$css .= $_css->begin_rule('.content-area');
			$css .= $_css->add_rule('min-height', '80vh');
		$css .= $_css->end_rule();

		// .pagoda-page-wrap contains both the content area and sidebar.
		$css .= $_css->begin_rule('.pagoda-page-wrap');
			$css .= $_css->add_rule('display'	, 'block');

			// include padding in width calculation w/box-sizing declaration. Box 
			// sizing tells the browser to include padding in width calculations. 
			$css .= $_css->add_rule('box-sizing', 'border-box');
			$css .= $_css->add_rule('overflow'	, 'auto'); // Auto height
			$css .= $_css->add_rule('width'		, '100%'); // Full width
			$css .= $_css->add_rule('min-height', '80vh'); // min-height
			// Allows us to push all content inwards in content area
			$css .= $_css->add_rule('padding' 	, $this->query->option($data['display'], '_mn_padding')); 
		$css .= $_css->end_rule();
	
		$css .= $_css->comment(" ------ Content Area ------ ");

		$css .= $_css->begin_rule('.pagoda-page-content-area');
			$css .= $_css->add_rule('box-sizing', 'border-box');
			// Trigger Block Formatting Context. Content area container will 
			// behave inline-block and take remaining width with overflow:hidden;
			$css .= $_css->add_rule('overflow'	, 'hidden'); 
			$css .= $_css->add_rule('padding' 	, $this->query->option($data['display'], '_mn_content_a') );
		$css .= $_css->end_rule();	

		// This is a wrapper for all article tags comments will also appear in the 
		// content wrap container.
		$css .= $_css->begin_rule('.pagoda-page-content-wrap');
			$css .= $_css->add_rule('float'		, 'left');
			$css .= $_css->add_rule('width'		, '100%');
			$css .= $_css->add_rule('box-sizing', 'border-box');
			$css .= $_css->add_rule('padding'	, $this->query->option($data['display'], '_mn_content_w'));
		$css .= $_css->end_rule();	
		
		// This is the <article> tag. The padding values here push the actual text 
		// and images in from the edge of the content are wrap.  
		$css .= $_css->begin_rule('.blog-post.page');
			$css .= $_css->add_rule('float'		, 'left');
			$css .= $_css->add_rule('width'		, '100%');
			$css .= $_css->add_rule('min-height', '5vh');
			$css .= $_css->add_rule('box-sizing', 'border-box');
			$css .= $_css->add_rule('padding'	, $this->query->option($data['display'], '_mn_article'));
		$css .= $_css->end_rule();	

		// Remove these paddings on mobile. All positioning will be controlled by one value
		$css .= $_css->begin_media('all and (max-width:980px)');

	 		$css .= $_css->begin_rule('.pagoda-page-content-wrap');
	 			$css .= $_css->add_rule('width'		, 'inherit');
	 			$css .= $_css->add_rule('padding'	, '0px 5px');
	 		$css .= $_css->end_rule();

	 		// Use table display: In the templates, the sidebars are BEFORE the
	 		// content-area to achieve for overflow:hidden. This is done so
	 		// that the content area takes the remaning space relative to the 
	 		// sidebar. (The DOM needs to know the width of the sidebar before 
	 		// it can calculate remaining space for overflow hidden.  
	 		//
	 		// In mobile mode, we cannot fit this much content on horizontally on
	 		// the screen, but we want sidebars to appear AFTER the content-area
	 		// despite thier divs being BEFORE the content in the markup. For this 
	 		// we will need to use a display:table. In this case the DOM looks
	 		// at the table declaration and parses all further divs looking for 
	 		// table layout options before rendering content. 
	 		$css .= $_css->begin_rule('.pagoda-page-wrap');
	 			$css .= $_css->add_rule('display' 	, 'table');
	 			$css .= $_css->add_rule('padding' 	, '20px 20px');
	 		$css .= $_css->end_rule();

	 		// Here render the content area in the table header group so the 
	 		// content-area is rendered at the top of the page (i.e. in the 
	 		// 'header' of the table). An analogous declaration will appear 
	 		// in the sidebars class (i.e. display:table-footer-group). 
	 		$css .= $_css->begin_rule('.pagoda-page-content-area');	
		 		$css .= $_css->add_rule('display'	, 'table-header-group');
		 		$css .= $_css->add_rule('padding' 	, '20px 10px'); 			 		
			$css .= $_css->end_rule();	
	 		
	 		$css .= $_css->begin_rule('.blog-post.post');	
		 		$css .= $_css->add_rule('padding' 	, '20px 20px'); 			 		
			$css .= $_css->end_rule();

		$css .= $_css->end_media();	
		return $css;
	}

	private function css_generate_layout_i($data, $_css){

		$is_written = array(); $css =''; 
		foreach($data['pages'] as $ID => $_) {

			if ( in_array($_['class'], $is_written) ){continue;}
	
			else{
				array_push($is_written, $_['class']);
		
				$css .= $_css->begin_rule('.content-area-'.$ID);
					$css .= $_css->add_rule('background-color',$this->query->option($_['content'], '_bg_color'));
				$css .= $_css->end_rule();

				$css .= $_css->begin_rule('.pagoda-page-content-wrap-' . $ID);
					$css .= $_css->add_rule('background-color',$this->query->option($_['content'], '_mn_color'));
					
					if( $this->query->option( $_['border'], '_show' ) ):
						$css .= $_css->add_rule('border-radius', $this->query->option($_['border'], '_radius'));
						$_border = array(
							'fade'  => $this->query->option($_['border'], '_fade'),
							'size'  => $this->query->option($_['border'], '_size'), 
							'color' => $this->query->option($_['border'], '_color'),
							'input_atts' => array(
								'mode' => $this->query->option( $_['border'], '_inset' ) ? 'inset' : 'default'

						) );
						$css .= $_css->border_properties($_border);
					endif;
				
				$css .= $_css->end_rule();
			}	
		}
		return $css;
	}


	//////////////////////////////////////////////////////////////////////////////////////
	//								ENTRY CONTENT DISPLAY 								//
	//////////////////////////////////////////////////////////////////////////////////////

	private function css_generate_content_u($data, $_css){

		$css = $_css->comment(" ------ Entry Content Display ------ ");		

		// The content itself. The page html that we prepare in the WP backend 
		// will appear within this div with no padding or margin properties.  
		$_align = $this->query->option($data['display'], '_mn_align');

		$css .= $_css->begin_rule('.entry-content-page');
			$css .= $_css->add_rule('display'	, 'inline-block');
			$css .=	$_css->add_rule('width'		, '100%');	
			$css .=	$_css->add_rule('max-width'	, '100%');
			$css .= $_css->add_rule('text-align', $_align);
			$css .= $_css->add_rule('overflow-x', 'hidden');
			$css .= $_css->add_rule('overflow-y', 'auto');
		$css .= $_css->end_rule();		

		// Left align text on mobile (small vertical screens) if justified for 
		// improved readability.   
		$css .= $_css->begin_media('all and (max-width:480px)'); 
			$css .= $_css->begin_rule('.entry-content-page');	
			$css .= $_css->add_rule('text-align', 
				strcmp($_align, 'justify') == 0  ? 'left' : $_align );
			$css .= $_css->end_rule();
		$css .= $_css->end_media();		


		// Content containers entry-content-header(main,footer,meta) CSS 
		// --> implemented in posts class

		// <h> tags font enqueue
		$h_font_fam	= $this->query->option($data['display'], '_h_font_fam');
		$h_font_var	= $this->query->option($data['display'], '_h_font_var');

		array_push($this->font_enqueue, 
			array('font_fam' => $h_font_fam, 'font_var' => $h_font_var));

		// <h> tags CSS (h1, ... h6). 
		$h_tags = array(
			".entry-content-page h1",
			".entry-content-page h2",
			".entry-content-page h3",
			".entry-content-page h4",
			".entry-content-page h5",
			".entry-content-page h6",
		);
		$css .= $_css->begin_array_rule($h_tags);
			$css .= $_css->_literal($this->font_gen->css_fontfamily($h_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($h_font_var));
			$css .= $_css->add_rule('text-align', 
				strcmp($_align, 'justify') == 0  ? 'left' : $_align );
		$css .= $_css->end_rule();	

		// For <p> tags we will enqueue ALL variants. This will give the user some more 
		// flexibility when it comes to formatting their content.  
		$p_font_fam = $this->query->option($data['display'], '_p_font_fam');
		$p_font_var = $this->query->option($data['display'], '_p_font_var');
		$a_font_var = $this->query->option($data['display'], '_a_font_var');
		$b_font_var = $this->query->option($data['display'], '_b_font_var');

		$fontVars 	= $this->font_gen->get_variants($p_font_fam);
		foreach ($fontVars as $_ => $_var) {
		 	array_push($this->font_enqueue, 
		 		array('font_fam' => $p_font_fam, 'font_var' => $_var));
		} 

		// <p> <ol> <ul> <li> <a> <span> tags CSS
		$p_tags = array(
			".entry-content-page p",
			".entry-content-page ol",
			".entry-content-page ul",
			".entry-content-page li",
			".entry-content-page span",
			'.entry-content-page textarea',
			'.entry-content-page input', 
			'.entry-content-page select', 
		);
		$css .= $_css->begin_array_rule($p_tags);
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($p_font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_font_size', 'px'));
		$css .= $_css->end_rule();

		// Add line height for <p> <ol> <>ul> <li>
 		$p_tags = array(
			".entry-content-page p",
			".entry-content-page ol",
			".entry-content-page ul",
			".entry-content-page li",
		);
		$css .= $_css->begin_array_rule($p_tags);
			$css .= $_css->add_rule('line-height', '1.5em');
		$css .= $_css->end_rule();

		// Style link font and weight
		$css .= $_css->begin_rule(".entry-content-page a");
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($a_font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_font_size', 'px'));
		$css .= $_css->end_rule();

		// And link hover transitions
		$css .= $_css->begin_rule('.entry-content-page a:hover');
			$css .= $_css->browser_rules('transition', 'color 250ms ease-out');
		$css .= $_css->end_rule();

		// Style <b> <strong> fonts
		$targets = array(
			".entry-content-page b",
			".entry-content-page strong",
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($b_font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_font_size', 'px'));
		$css .= $_css->end_rule();

		// Blockquote font size
		$targets = array(
			".entry-content-page blockquote p",
			".entry-content-page blockquote a",
			".entry-content-page blockquote b",
			".entry-content-page blockquote strong",
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_blqt_size', 'px'));
		$css .= $_css->end_rule();


		///////////////////
		// Gallery Pages //
		///////////////////
		// --> CSS Implemented via posts class
		return $css; 
	}

	private function css_generate_content_i($data, $_css){

		$is_written = array();
		$css = $_css->comment(" ------ Per Cetegory Styles ------ ");

		foreach($data['pages'] as $ID => $_) {

			if ( in_array($_['class'], $is_written) ){continue;}
	
			else{
				array_push($is_written, $_['class']);
		
				// Header colors
				$h_tags = array(
					'.entry-content-page-'. $ID . ' h1',
					'.entry-content-page-'. $ID . ' h2',
					'.entry-content-page-'. $ID . ' h3',
					'.entry-content-page-'. $ID . ' h4',
					'.entry-content-page-'. $ID . ' h5',
					'.entry-content-page-'. $ID . ' h6'
				);
				$css .= $_css->begin_array_rule($h_tags);
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_h_color'));
				$css .= $_css->end_rule();

				// Text colors
				$p_tags = array(
					'.entry-content-page-'. $ID . ' p',
					'.entry-content-page-'. $ID . ' q',
					'.entry-content-page-'. $ID . ' ol',
					'.entry-content-page-'. $ID . ' ul',
					'.entry-content-page-'. $ID . ' li',
					'.entry-content-page-'. $ID . ' span',
					'.entry-content-page-'. $ID . ' blockquote:before',
					'.entry-content-page-'. $ID . ' .gallery-caption'
				);
				$css .= $_css->begin_array_rule($p_tags);
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_p_color'));
				$css .= $_css->end_rule();

				// Link colors
				$css .= $_css->begin_rule('.entry-content-page-' . $ID . ' a');
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_a_color'));
				$css .= $_css->end_rule();

				$css .= $_css->begin_rule('.entry-content-page-' . $ID . ' a:hover');
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_a_hover'));
				$css .= $_css->end_rule();

				// Bold colors
				$targets = array(
					'.entry-content-page-' . $ID . ' b',
					'.entry-content-page-' . $ID . ' strong',
				);
				$css .= $_css->begin_array_rule($targets);
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_b_color'));
				$css .= $_css->end_rule();

				// Blockquote Border 
				$css .= $_css->begin_rule('.entry-content-page-' . $ID . ' blockquote');
					$css .= $_css->add_rule('border-left', 
						'5px' . ' ' . 
						'solid' . ' ' . 
						$this->query->option($_['content'], '_p_color')
					);
				$css .= $_css->end_rule();

				// Style input and textarea
				$targets = array(
					'.entry-content-page-' . $ID . ' textarea',
					'.entry-content-page-' . $ID . ' input', 
				);
				
				$css .= $_css->begin_array_rule($targets);
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_p_color'));
					$css .= $_css->add_rule('background-color', 
						$this->query->option($_['content'], '_in_color'));
				$css .= $_css->end_rule();

				$targets = array(
					'.entry-content-page-' . $ID . ' input#submit:hover', 
					'.entry-content-page-' . $ID . ' input[type="submit"]:hover'
				);
				$css .= $_css->begin_array_rule($targets);
					$css .= $_css->add_rule('background-color', 
						$this->query->option($_['content'], '_in_hover'));
					$css .= $_css->add_rule('cursor', 'pointer');
					$css .= $_css->browser_rules('transition', 'background-color 500ms ease-out');

				$css .= $_css->end_rule();	

				// Archive comment meta
				// --> Defined in posts class				
				// Image frames				
				if( $this->query->option( $_['frames'], '_image' ) ):

					$targets = array(
						'.entry-content-page-' . $ID . ' .entry-content-thumbnail img',
						'.entry-content-page-' . $ID . ' .entry-content-header img', 
						'.entry-content-page-' . $ID . ' .entry-content-main img',
					);
					$css .= $_css->begin_array_rule($targets);
						$css .= $_css->add_rule('border-radius', $this->query->option($_['frames'], '_radius'));
						$_border = array(
							'fade'  => $this->query->option($_['frames'], '_fade'),
							'size'  => $this->query->option($_['frames'], '_size'), 
							'color' => $this->query->option($_['frames'], '_color'),
							'input_atts' => array(
								'mode' => 'default'

						) );
						$css .= $_css->border_properties($_border);
					$css .= $_css->end_rule();

				endif;	

				// Input frames
				if( $this->query->option( $_['frames'], '_input' ) ):
	
					$targets = array(
						'.entry-content-page-' . $ID . ' textarea',
						'.entry-content-page-' . $ID . ' input', 
					);
					$css .= $_css->begin_array_rule($targets);
						$css .= $_css->add_rule('border-radius', $this->query->option($_['frames'], '_radius'));
						$_border = array(
							'fade'  => $this->query->option($_['frames'], '_fade'),
							'size'  => $this->query->option($_['frames'], '_size'), 
							'color' => $this->query->option($_['frames'], '_color'),
							'input_atts' => array(
								'mode' => $this->query->option( $_['frames'], '_inset' ) ? 'inset' : 'default'

						) );
						$css .= $_css->border_properties($_border);
					$css .= $_css->end_rule();

				endif;
			}
		}
		return $css;
	}
	
	// Uniterated meta options (fonts) 
	private function css_generate_meta_u($data, $_css){

		$css = '';
		// Grab fonts from general page options
		$p_font_fam = $this->query->option($data['display'], '_p_font_fam');
		$p_font_var = $this->query->option($data['display'], '_p_font_var');
		$a_font_var = $this->query->option($data['display'], '_a_font_var');

		// Style post pagination
		$targets = array(
			'.entry-content-page .entry-content-pgn > .entry-content-pgn-item',
			'.entry-content-page .entry-content-pgn > a',
			'.entry-content-page .entry-content-pgn > span',
			'.entry-content-page span > .entry-content-pgn-item'
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($a_font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_font_size', 'px') );
		$css .= $_css->end_rule();

		// Set font sizes in comments area
		$targets = array(
			'.entry-content-page .comments-area p',
			'.entry-content-page .vcard > cite.fn',
			'.entry-content-page .vcard > span.says',
			'.entry-content-page .comment-meta > a',
			'.entry-content-page .reply > a',
			'.entry-content-page .comment-respond p',
			'.entry-content-page .comment-respond a',
			'.entry-content-page span#email-notes',
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_meta_size') . 'px');
		$css .= $_css->end_rule();	

		// Text area font (comments)
		$css .= $_css->begin_rule('.entry-content-page .comment-form textarea#comment');
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
		$css .= $_css->end_rule();	

		// Input fields (comments) 
		$css .= $_css->begin_rule('.entry-content-page .comment-form input');
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($p_font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_font_size', 'px'));
		$css .= $_css->end_rule();	

		return $css; 
	}


	// Iterated meta options (color palette) 
	private function css_generate_meta_i($data, $_css){

		$is_written = array(); $css = '';
		foreach($data['pages'] as $ID => $_) {

			if ( in_array($_['class'], $is_written) ){continue;}
	
			else{
				array_push($is_written, $_['class']);

				/***************************** CONTENT AREA FOOTER *****************************/
				/////////////////////
				// Post Pagination //
				/////////////////////
				$targets = array(
					'.entry-content-page .entry-content-pgn-' . $ID . ' > .entry-content-pgn-item-' . $ID,
					'.entry-content-page .page-numbers.current' . ' > .entry-content-pgn-item-' . $ID
				);
				$css .= $_css->begin_array_rule($targets);
				
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_p_color'));
					$css .= $_css->add_rule('border-radius', $this->query->option($_['border'], '_radius'));	
					// Set up some reasonable border styles
					$_regex = "/^0(em|vw|vh|cm|mm|in|px|pt)?$/";
					$_fade  = preg_match($_regex, 
						$this->query->option($_['border'], '_fade')) ? false : true;
					$_sz  	= $_fade ? '1px' : $this->query->option($_['border'], '_size'); 			
					$_px  	= $_fade ? '2px' : '0px';

					// Add padding in the faded border case
					$css .= $_css->add_rule('padding'		, $_px);
					$_border = array(
						'size'  => $_sz, 
						'fade'  => $_px,
						'color' => $this->query->option($_['border'], '_color'),
						'input_atts' => array(
							'position' => 'default',
						'mode'	   => 'inset',
					) );
					$css .= $_css->border_properties($_border);
				$css .= $_css->end_rule();
			}
		}
		return $css;
	}

	// Define metabox behaviour
	public function metabox_init() {
		add_action( 'add_meta_boxes', array($this, 'add_metaboxes' ) );
		add_action( 'save_post'		, array($this, 'save_metaboxes'), 10, 2 );
	}

	// 'Add' metaboxes
	public function add_metaboxes(){

		// Filter for the default wordpress featured image metabox
		add_filter( 'admin_post_thumbnail_html', array($this, 'featured_image_metabox') , 10, 2 );

		// Remove comments metabox
		remove_meta_box(
			'commentsdiv',
			'page',
			'normal'
		);
	}

	// Filter content for thumbnail metabox (pages)
	public function featured_image_metabox( $content, $post_id ){ 

		// Initialize output buffer
		ob_start(); ?>

		<p>
		<?php _e('<strong>Featured Image Width (%):</strong>', 'pagoda'); ?>
		<?php $width = get_post_meta($post_id, 'size_featured_image', true); ?>
		<input class="pagoda-entry-content-metabox-input-image-size widefat" 
				id="pagoda-entry-content-metabox-size-<?php echo $post_id ?>"
				name="size_featured_image_<?php echo $post_id ?>"
				type="number" min="0" max="100" step="1"
				value="<?php echo $width ? $width : '85' ?>" />
		<?php _e('Featured images will be displayed at full width on mobile devices', 'pagoda'); ?>
		</p>

		<p>
		<input class="pagoda-entry-content-metabox-checkbox"
				id="pagoda-entry-content-metabox-checkbox-image-show-<?php echo $post_id ?>"
				name="show_featured_image_<?php echo $post_id ?>"
				value="enable"
				type="checkbox"
				<?php if( get_post_meta($post_id, 'show_featured_image', true) ){ 
						echo 'checked="checked"';} ?> 
				/>
				<?php _e('<strong>Show Featured Image?</strong>', 'pagoda'); ?>
		</p>

		<?php 

		// Get the contents off the output buffer
		$output .= ob_get_contents(); 
		ob_end_clean();

		// Append output buffer ony if we are on a page
		return get_post_type($post_id) == 'page' ? $content . $output : $content;  
	}

	// Save metabox info. 
	public function save_metaboxes( $post_id, $post ) {
		
		// Verify admin permissions
		$post_type = get_post_type_object( $post->post_type );
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ){
			return $post_id;
		}

		// Only featured image metabox
		$meta_box = array(
			'featured_image'  => array(
				'size_featured_image' => array(
					'type'  => 'css_width', 
					'range' => array('min' => 0, 'max' => 100)
				),
				'show_featured_image' => array(
					'type' => 'checkbox'
		) ) );

		// Loop through metaboxes and assign values. 
		// This loop also sanitizes user input 		
		foreach ( $meta_box as $_ => $meta_keys ) {

			// For each metabox, loop through meta_keys
			foreach ($meta_keys as $meta_key => $meta_data){
				
				$meta_val = get_post_meta( $post_id, $meta_key, true );
				
				// CSS Width Field (featured image size)
				if ( strcmp($meta_data['type'], 'css_width') == 0 ){
				 	$meta_new =	!empty( $_POST[$meta_key . '_' . $post_id] ) ? 
				 		absint( $_POST[$meta_key . '_' . $post_id] ) : $meta_data['range']['max'];
					
				 	// Sanitize number against hardcoded range
				 	$meta_new = ( ( $meta_data['range']['min'] <= $meta_new && 
				 		$meta_new <= $meta_data['range']['max'] ) ? $meta_new : $meta_data['range']['max']);
					
					update_post_meta( $post_id, $meta_key, $meta_new);
					
					// Compute css string for html and store as metadata					
					$css_width = "style=\"width:" . $meta_new . "%\""; 
					update_post_meta( $post_id, "_" . $meta_key , $css_width);
				}

				// Checkbox
				if ( strcmp($meta_data['type'], 'checkbox') == 0 ) {

					// Empty checkboxes are not POSTed ... if unchecked then POST 
					// will be empty and we set the corresponding meta to an empty string. 
					$meta_new = ( !empty( $_POST[$meta_key . '_' . $post_id] ) ) ? '1' : ''; 
					update_post_meta( $post_id, $meta_key, $meta_new);

				}
			}	
		}
	}
} // End Class

?> 