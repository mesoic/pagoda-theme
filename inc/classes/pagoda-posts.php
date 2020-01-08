<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class pagoda_posts {
	
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
		$this->page_tree  = $page_tree;

		// Create an array that will store the fontstrings.   
		$this->initialize_posts(); // Initializes Query Option
		if ( user_can( wp_get_current_user() , 'administrator' ) ) {
			$this->write_css_posts();  // Initializes CSS Autogen 
		}
		
		// Post metaboxes 
		add_action( 'load-post.php'		, array($this, 'metabox_init' ) );
		add_action( 'load-post-new.php'	, array($this, 'metabox_init' ) );

		// Filter comment author to show display name	
		add_filter( 'get_comment_author', array($this, 'post_comment_author'), 10, 1 );

		// Do not use default gallery styles
		add_filter( 'use_default_gallery_style', '__return_false' );
	
		// Wrap to all images in containers 
		add_filter( 'the_content', array($this, 'post_format_images' ), 10, 1) ;
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//									INIT METHODS 									//
	//////////////////////////////////////////////////////////////////////////////////////
	private function initialize_posts(){
		
		$this->topLevel = array('home');
		foreach ($this->page_tree->get_categories() as $_ => $category) {
			array_push($this->topLevel, str_replace('-', '_', $category->slug) );			
		}
		
		// Display layout and scroll to top icon
		$this->data = array(
			'display' => $this->query->subtable( array('posts' , 'display') ),
			//'comment' => $this->query->subtable( array('posts' , 'comment') ),
			'scroll'  => $this->query->subtable( array('navbar', 'scroll' ) ),
		);

		// Iterated post options. Initialized outside main array as we 
		// will also pack height and class into this data structure.  
		$this->data['posts'] = $this->process_posts($this->data);
	}

	private function process_posts($data){

		// Obtain Posts Subtable 
		$data_i = $this->query->subtable(array('posts', 'data'), $this->topLevel );  

		// Assign perCategory and height
		$perCategory = $this->query->option($data_i["uncategorized"]["header"], 'styles_activate');
		$height 	 = $this->query->option($data['display'], 'height');	

		$post_data = array();

		// Extract categories and define a pseudo element for 'recent posts'
		$categories = $this->page_tree->get_categories();
		$pseudo_cat = (object)array('name' => 'Home', 'slug' => 'home');

		// Loop through all pages and assign the corresponding options/class
		foreach( array_merge($categories, array($pseudo_cat)) as $_ => $category ){

			// Assign page_data keys to category slugs
			$ID    = str_replace('-', '_', $category->slug);
			$class = $category->slug; 	 
			$post_data[$ID] = array();
		
			$inherit = ( strcmp($ID, "uncategorized" ) != 0 ) ? 
				$this->query->option($data_i[$ID]["header"], 'inherit' ) : true;

			// Need to add height and class to post data
			if ($perCategory && !$inherit && count($this->topLevel) < 12 ){
				$post_data[$ID]	= $data_i[$ID]; 
				$post_data[$ID]['header']['pagoda_post_display_mn_height'] = $height;
				$post_data[$ID]['class'] = $class; 
			}
			else {
				$post_data[$ID]	= $data_i['uncategorized'];
				$post_data[$ID]['header']['pagoda_post_display_mn_height'] = $height;
				$post_data[$ID]['class']	= $class;
			}
		}

		return $post_data;
	}

	public function get_post_class($_ID) {

		// Sticky Posts. This is an array of sticky posts. The first 
		// item should be the most recent sticky post. 
		$sticky = get_option( 'sticky_posts' );

		// Your Latest Posts (Category Styles)
		if ( strcmp($_ID , 'home') == 0 && empty($sticky) ){
			$post_class = 'home';			
		}

		// Your Latest Posts (Default Styles)
		else if ( strcmp($_ID , 'uncategorized') == 0 && empty($sticky) ){
			$post_class = 'uncategorized';
		}

		// For sticky posts we will use index.php (Homepage)
		else if ( strcmp($_ID , 'home') == 0 && !empty($sticky) ){
			$post_class = ( strcmp( get_post_meta($sticky[0], 'styles_activate', true), "1" ) == 0 ) ? 
				get_post_meta($_ID, 'category_styles', true) : 'uncategorized';
		}

		// Search, Archive, 404 
		else if ( is_search() || is_archive() || is_404() ) {
			$post_class = "uncategorized";
		}

		// Nothing found
		else if ( strcmp($_ID , 'none') == 0){
			$post_class = "uncategorized";
		}

		// Posts
		else {
			$post_class = ( strcmp( get_post_meta($_ID, 'styles_activate', true), "1" ) == 0 )? 
				get_post_meta($_ID, 'category_styles', true) : 'uncategorized';
		}

		return $post_class;
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//									HTML METHODS 									//
	//////////////////////////////////////////////////////////////////////////////////////
	
	// Write the category header
	public function write_html_header($_ID){ ?>

		<?php $post_class = $this->get_post_class($_ID); ?>
		<?php $post_type  = is_home() ? 'home' : 'post'; ?>

		<div class="pagoda-post-header-wrap-<?php echo $post_class ?>">
		
			<div class="pagoda-post-header-frame-<?php echo $post_class ?>">
		
				<?php $this->navbar->html_generate_navbar($post_class, $post_type); ?>
		
			</div>
		
		</div>

	<?php }

	// Write the post. We will call this from content-post.php
	public function write_html_post($_ID){ ?>

		<?php $post_class = $this->get_post_class($_ID); ?>

		<article id="post-<?php the_ID(); ?>" <?php post_class('blog-post'); ?>>
		
			<div class="entry-content-post entry-content-post-<?php echo $post_class ?>">
		
				<?php 
					$this->write_content_header($_ID);
					$this->templates->content_template($_ID);
					$this->write_content_footer($_ID);
				?>
		
			</div>
		
		</article>

	<?php }

	public function write_html_none($_ID){ ?>

		<?php $post_class = $this->get_post_class($_ID); ?>

		<article id="post-none" class="blog-post post">
		
			<div class="entry-content-post entry-content-post-<?php echo $post_class ?>">
		
				<div class="entry-content-main" >
	
					<h1><?php esc_html_e('Nothing Found ...', 'pagoda'); ?></h1>
		
				</div>

			</div>
		
		</article>

	<?php }

	// Post Header: Navigation(inter-post), Thumbnail, Title  
	public function write_content_header($_ID){ ?>

		<div class="entry-content-header">
			
		<?php 
			
			// Navigation 
			if( get_post_meta($_ID, 'show_navigation', true) ) {
				$this->templates->navigation_template();
			} 

			// Featured Image
			if ( has_post_thumbnail() && 
				get_post_meta($_ID, 'show_featured_image', true)){

				$this->templates->thumbnail_template(
					get_post_meta( get_the_ID(), '_size_featured_image', true) );
			}

			// Title
			if( get_post_meta($_ID, 'show_title', true) ){
				$this->templates->title_template($_ID);
			}

		?>

		</div>

	<?php }

	// Post Footer: Tags, Pagination (intra-post), Comments
	public function write_content_footer($_ID){ ?>
	
		<?php $post_class = $this->get_post_class($_ID); ?>

		<div class="entry-content-footer">

		<?php 

			// Tags
			if( get_post_meta($_ID, 'show_tags', true) ){
				$this->templates->tags_template();
			}

			// Pagination
			$this->templates->link_pages_template($post_class);

			// Comments
			if( get_post_meta($_ID, 'show_comments', true) ){
				$this->templates->comments_template();
			}

		?>

		</div>

	<?php }

	//////////////////////////////////////////////////////////////////////////////////////
	//									CSS METHODS 									//
	//////////////////////////////////////////////////////////////////////////////////////
	private function write_css_posts(){ 
		$css_autogen = new Nablafire_CSS_Autogen();		
	
		$this->css  = $css_autogen->comment('Pagoda Posts Machine Generated CSS');
		$this->css .= $this->css_generate_headers_a($this->data, $css_autogen);

		$this->css .= $this->css_generate_layout_u($this->data, $css_autogen);
		$this->css .= $this->css_generate_layout_i($this->data, $css_autogen);
		
		$this->css .= $this->css_generate_content_u($this->data, $css_autogen);
		$this->css .= $this->css_generate_content_i($this->data, $css_autogen);

		$this->css .= $this->css_generate_meta_b($this->data, $css_autogen);
		$this->css .= $this->css_generate_meta_u($this->data, $css_autogen);
		$this->css .= $this->css_generate_meta_i($this->data, $css_autogen);

		update_option('pagoda_posts_styles', serialize($css_autogen->minify($this->css)));
		update_option('pagoda_posts_fonts' , serialize($this->font_enqueue));
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
		foreach($data['posts'] as $ID => $_) {
			
			if ( in_array($_['class'], $is_written) ){continue;}
	
			else{
				array_push($is_written, $_['class']);

				// Write CSS for header height and background color
				$css .= $_css->begin_rule('.pagoda-post-header-frame-' . $_['class']);
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

		$css = $_css->comment(" ------ Post Layout ------ ");		

		// This is the toplevel container. It applies to pages and posts. Here we only 
		// set so the footer does not collapse onto the header for a complely empty 
		// page or post. Note that this CSS rule is not needed in the posts class.
		$css .= $_css->begin_rule('.content-area');
			$css .= $_css->add_rule('min-height', '80vh');
		$css .= $_css->end_rule();

		// .pagoda-post-wrap contains both the content area and sidebar.
		$css .= $_css->begin_rule('.pagoda-post-wrap');
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

		$css .= $_css->begin_rule('.pagoda-post-content-area');
			$css .= $_css->add_rule('box-sizing', 'border-box');
			// Trigger Block Formatting Context. Content area container will 
			// behave inline-block and take remaining width with overflow:hidden;
			$css .= $_css->add_rule('overflow'	, 'hidden'); 
			$css .= $_css->add_rule('padding' 	, $this->query->option($data['display'], '_mn_content_a') );
		$css .= $_css->end_rule();	

		// This is a wrapper for all article tags comments will also appear in the 
		// content wrap container.
		$css .= $_css->begin_rule('.pagoda-post-content-wrap');
			$css .= $_css->add_rule('float'		, 'left');
			$css .= $_css->add_rule('width'		, '100%');
			$css .= $_css->add_rule('box-sizing', 'border-box');
			$css .= $_css->add_rule('padding'	, $this->query->option($data['display'], '_mn_content_w'));
		$css .= $_css->end_rule();	
		
		// This is the <article> tag. The padding values here push the actual text 
		// and images in from the edge of the content are wrap.  
		$css .= $_css->begin_rule('.blog-post.post');
			$css .= $_css->add_rule('float'		, 'left');
			$css .= $_css->add_rule('width'		, '100%');
			$css .= $_css->add_rule('min-height', '5vh');
			$css .= $_css->add_rule('box-sizing', 'border-box');
			$css .= $_css->add_rule('padding'	, $this->query->option($data['display'], '_mn_article'));
		$css .= $_css->end_rule();	

		// Remove these paddings on mobile. All positioning will be controlled by one value
		$css .= $_css->begin_media('all and (max-width:980px)');

	 		$css .= $_css->begin_rule('.pagoda-post-content-wrap');
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
	 		$css .= $_css->begin_rule('.pagoda-post-wrap');
	 			$css .= $_css->add_rule('display' 	, 'table');
	 			$css .= $_css->add_rule('padding' 	, '20px 20px');
	 		$css .= $_css->end_rule();

	 		// Here render the content area in the table header group so the 
	 		// content-area is rendered at the top of the page (i.e. in the 
	 		// 'header' of the table). An analogous declaration will appear 
	 		// in the sidebars class (i.e. display:table-footer-group). 
	 		$css .= $_css->begin_rule('.pagoda-post-content-area');	
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
		foreach($data['posts'] as $ID => $_) {

			if ( in_array($_['class'], $is_written) ){continue;}
	
			else{
				array_push($is_written, $_['class']);
		
				$css .= $_css->begin_rule('.content-area-'.$ID);
					$css .= $_css->add_rule('background-color',$this->query->option($_['content'], '_bg_color'));
				$css .= $_css->end_rule();

				$css .= $_css->begin_rule('.pagoda-post-content-wrap-' . $ID);
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

		$css .= $_css->begin_rule('.entry-content-post');
			$css .= $_css->add_rule('display'	, 'inline-block');
			$css .=	$_css->add_rule('width'		, '100%');	
			$css .= $_css->add_rule('text-align', $_align);
			$css .= $_css->add_rule('overflow-x', 'hidden');
			$css .= $_css->add_rule('overflow-y', 'auto');
		$css .= $_css->end_rule();		

		// Left align text on mobile (small vertical screens) if justified for 
		// improved readability.   
		$css .= $_css->begin_media('all and (max-width:480px)'); 
			$css .= $_css->begin_rule('.entry-content-post');	
			$css .= $_css->add_rule('text-align', 
				strcmp($_align, 'justify') == 0  ? 'left' : $_align );
			$css .= $_css->end_rule();
		$css .= $_css->end_media();		

		// Content headers (also apples to pages)
		$targets = array(
			'.entry-content-header',
			'.entry-content-main',
			'.entry-content-footer',
			'.entry-content-meta',
		);
		$css .= $_css->begin_array_rule($targets); 
			$css .= $_css->add_rule('width', 'inherit');
			$css .= $_css->add_rule('overflow-x', 'hidden');
			$css .= $_css->add_rule('overflow-y', 'auto');
		$css .= $_css->end_rule();

		$css .= $_css->begin_rule('.entry-content-meta');
			$css .= $_css->add_rule('padding-bottom', '15px');
		$css .= $_css->end_rule();

		// <h> tags font enqueue
		$h_font_fam	= $this->query->option($data['display'], '_h_font_fam');
		$h_font_var	= $this->query->option($data['display'], '_h_font_var');

		array_push($this->font_enqueue, 
			array('font_fam' => $h_font_fam, 'font_var' => $h_font_var));

		// <h> tags CSS (h1, ... h6). 
		$h_tags = array(
			".entry-content-post h1",
			".entry-content-post h2",
			".entry-content-post h3",
			".entry-content-post h4",
			".entry-content-post h5",
			".entry-content-post h6",
		);
		$css .= $_css->begin_array_rule($h_tags);
			$css .= $_css->_literal($this->font_gen->css_fontfamily($h_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($h_font_var));
			$css .= $_css->add_rule('text-align', 
				strcmp($_align, 'justify') == 0  ? 'left' : $_align );
		$css .= $_css->end_rule();	

		// For <p> tags we will enqueue ALL variants. This will give the user some 
		// more flexibility when it comes to formatting their content. Here we also 
		// push Monserrat for editor styles. 
		$p_font_fam = $this->query->option($data['display'], '_p_font_fam');
		$p_font_var = $this->query->option($data['display'], '_p_font_var');
		$a_font_var = $this->query->option($data['display'], '_a_font_var');
		$b_font_var = $this->query->option($data['display'], '_b_font_var');

		$fontVars 	= $this->font_gen->get_variants($p_font_fam);
		foreach ($fontVars as $_ => $_var) {
			array_push($this->font_enqueue, 
				array('font_fam' => $p_font_fam, 'font_var' => $_var));
		}
		array_push($this->font_enqueue, 
			array('font_fam' =>'Montserrat', 'font_var'=>'regular'));

		// <p> <ol> <ul> <li> <a> <span> tags CSS
		$p_tags = array(
			".entry-content-post p",
			".entry-content-post ol",
			".entry-content-post ul",
			".entry-content-post li",
			".entry-content-post span",
			'.entry-content-post textarea',
			'.entry-content-post input', 
			'.entry-content-post select', 
		);
		$css .= $_css->begin_array_rule($p_tags);
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($p_font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_font_size', 'px'));
		$css .= $_css->end_rule();

		// Add line height for <p> <ol> <ul> <li>
		$p_tags = array(
			".entry-content-post p",
			".entry-content-post ol",
			".entry-content-post ul",
			".entry-content-post li",
		);
		$css .= $_css->begin_array_rule($p_tags);
			$css .= $_css->add_rule('line-height', '1.5em');
		$css .= $_css->end_rule();
			
		// Style link font and weight
		$css .= $_css->begin_rule(".entry-content-post a");
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($a_font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_font_size', 'px'));
		$css .= $_css->end_rule();

		// And link hover transitions
		$css .= $_css->begin_rule('.entry-content-post a:hover');
			$css .= $_css->browser_rules('transition', 'color 250ms ease-out');
		$css .= $_css->end_rule();

		// Style <b> <strong> fonts
		$targets = array(
			".entry-content-post b",
			".entry-content-post strong",
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($b_font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_font_size', 'px'));
		$css .= $_css->end_rule();

		// Blockquote font size
		$targets = array(
			".entry-content-post blockquote p",
			".entry-content-post blockquote a",
			".entry-content-post blockquote b",
			".entry-content-post blockquote strong",
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_blqt_size', 'px'));
		$css .= $_css->end_rule();
		
		//////////////////
		// Archive Meta //
		//////////////////

		// For archive page (meta elements)
		$targets = array(
			'.entry-content-meta',
			'.entry-content-meta a',
			'.entry-content-meta span'
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($p_font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_meta_size') . 'px');
		$css .= $_css->end_rule();

		// Font Awesome Icons
		$css .= $_css->begin_rule('.entry-content-meta i');
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_meta_size') . 'px');
		$css .= $_css->end_rule();

		// Date and Comment Spans		
		$css .= $_css->begin_rule('.entry-content-meta span');
			$css .= $_css->add_rule('padding-right', '5px');
		$css .= $_css->end_rule();

		// For archive page (read more to right) 
		$css .= $_css->begin_rule('.entry-content-meta > span.pagoda-post-more');	
			$css .= $_css->add_rule('float'			, 'right');
			$css .= $_css->add_rule('padding-right'	, '100px');
		$css .= $_css->end_rule();	

		$css .= $_css->begin_media('all and (max-width:980px)');
			$css .= $_css->begin_rule('.entry-content-meta > span.pagoda-post-more');	
				$css .= $_css->add_rule('float'		, 'left');
			$css .= $_css->end_rule();			
		$css .= $_css->end_rule();

		$css .= $_css->begin_rule('.entry-content-pagination');
			$css .= $_css->add_rule('text-align',	'center');
		$css .= $_css->end_rule();		

		$css .= $_css->begin_rule('.page-numbers.next');
			$css .= $_css->add_rule('padding-left', '10px');
		$css .= $_css->end_rule();

		$css .= $_css->begin_rule('.page-numbers.prev');
			$css .= $_css->add_rule('padding-right', '10px');
		$css .= $_css->end_rule();

		///////////////////
		// Gallery Posts //
		///////////////////
		$css .= $_css->begin_rule('.gallery');
			$css .= $_css->add_rule('margin', '0 auto 18px');
			$css .= $_css->add_rule('text-align', 'center');
			$css .= $_css->add_rule('width', '100%');
		$css .= $_css->end_rule();
		
		$css .= $_css->begin_rule('.gallery img:hover');
			$css .= $_css->add_rule('opacity', '0.5');
		$css .= $_css->end_rule();

		$css .= $_css->begin_rule('.gallery .gallery-item');
			$css .= $_css->add_rule('display', 'inline-block');
			$css .= $_css->add_rule('padding', '10px');
			$css .= $_css->add_rule('text-align', 'center');
			$css .= $_css->add_rule('box-sizing', 'border-box');
		$css .= $_css->end_rule();

		$css .= $_css->begin_rule('.gallery-columns-1 .gallery-item');
			$css .= $_css->add_rule('width','100%');
		$css .= $_css->end_rule();

		$css .= $_css->begin_rule('.gallery-columns-2 .gallery-item');
			$css .= $_css->add_rule('width','50%');
		$css .= $_css->end_rule();
		
		$css .= $_css->begin_rule('.gallery-columns-3 .gallery-item');
			$css .= $_css->add_rule('width','33%');
		$css .= $_css->end_rule();

		$css .= $_css->begin_rule('.gallery-columns-4 .gallery-item');
			$css .= $_css->add_rule('width','25%');
		$css .= $_css->end_rule();

		$css .= $_css->begin_rule('.gallery-columns-5 .gallery-item');
			$css .= $_css->add_rule('width','20%');
		$css .= $_css->end_rule();

		// Force two column responsive display (tablets)
		$css .= $_css->begin_media('all and (max-width:980px)'); 
			$targets = array(
				'.gallery-columns-2' . ' ' . '.gallery-item',
				'.gallery-columns-3' . ' ' . '.gallery-item',
				'.gallery-columns-4' . ' ' . '.gallery-item',
				'.gallery-columns-5' . ' ' . '.gallery-item',
			);
			$css .= $_css->begin_array_rule($targets);
				$css .= $_css->add_rule('width', '50%');
			$css .= $_css->end_rule();
		$css .= $_css->end_media();

		// Force one column responsive display (phones)
		$css .= $_css->begin_media('all and (max-width:480px)'); 
			$targets = array(
				'.gallery-columns-2' . ' ' . '.gallery-item',
				'.gallery-columns-3' . ' ' . '.gallery-item',
				'.gallery-columns-4' . ' ' . '.gallery-item',
				'.gallery-columns-5' . ' ' . '.gallery-item',
			);
			$css .= $_css->begin_array_rule($targets);
				$css .= $_css->add_rule('width', '100%');
			$css .= $_css->end_rule();
		$css .= $_css->end_media();

		// Galley fonts
		$css .= $_css->begin_rule('.gallery-caption');
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($a_font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_font_size') . 'px');
			$css .= $_css->add_rule('margin' , '0');
		$css .= $_css->end_rule();

		$targets = array(
			'.gallery dl',
			'.gallery dt'
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->add_rule('margin' , '0');
		$css .= $_css->end_rule();

		$css .= $_css->begin_rule('.gallery br+br');
			$css .= $_css->add_rule('display', 'none');
		$css .= $_css->end_rule();

		return $css; 
	}

	private function css_generate_content_i($data, $_css){

		$is_written = array();
		$css = $_css->comment(" ------ Per Cetegory Styles ------ ");

		foreach($data['posts'] as $ID => $_) {

			if ( in_array($_['class'], $is_written) ){continue;}
	
			else{
				array_push($is_written, $_['class']);
		
				// Header colors
				$h_tags = array(
					'.entry-content-post-'. $ID . ' h1',
					'.entry-content-post-'. $ID . ' h2',
					'.entry-content-post-'. $ID . ' h3',
					'.entry-content-post-'. $ID . ' h4',
					'.entry-content-post-'. $ID . ' h5',
					'.entry-content-post-'. $ID . ' h6'
				);
				$css .= $_css->begin_array_rule($h_tags);
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_h_color'));
				$css .= $_css->end_rule();

				// Text colors
				$p_tags = array(
					'.entry-content-post-'. $ID . ' p',
					'.entry-content-post-'. $ID . ' q',
					'.entry-content-post-'. $ID . ' b',
					'.entry-content-post-'. $ID . ' ol',
					'.entry-content-post-'. $ID . ' ul',
					'.entry-content-post-'. $ID . ' li',
					'.entry-content-post-'. $ID . ' span',
					'.entry-content-post-'. $ID . ' blockquote:before',
					'.entry-content-post-'. $ID . ' .gallery-caption'
				);
				$css .= $_css->begin_array_rule($p_tags);
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_p_color'));
				$css .= $_css->end_rule();

				// Link colors
				$css .= $_css->begin_rule('.entry-content-post-' . $ID . ' a');
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_a_color'));
				$css .= $_css->end_rule();

				$css .= $_css->begin_rule('.entry-content-post-' . $ID . ' a:hover');
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_a_hover'));
				$css .= $_css->end_rule();

				// Bold colors
				$targets = array(
					'.entry-content-post-' . $ID . ' b',
					'.entry-content-post-' . $ID . ' strong',
				);
				$css .= $_css->begin_array_rule($targets);
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_b_color'));
				$css .= $_css->end_rule();

				// Blockquote Border 
				$css .= $_css->begin_rule('.entry-content-post-' . $ID . ' blockquote');
					$css .= $_css->add_rule('border-left', 
						'5px' . ' ' . 
						'solid' . ' ' . 
						$this->query->option($_['content'], '_p_color')
					);
				$css .= $_css->end_rule();
				
				// Style input and textarea
				$targets = array(
					'.entry-content-post-' . $ID . ' textarea',
					'.entry-content-post-' . $ID . ' input', 
				);
				
				$css .= $_css->begin_array_rule($targets);
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_p_color'));
					$css .= $_css->add_rule('background-color', 
						$this->query->option($_['content'], '_in_color'));
				$css .= $_css->end_rule();

				$targets = array(
					'.entry-content-post-' . $ID . ' input#submit:hover', 
					'.entry-content-post-' . $ID . ' input[type="submit"]:hover'
				);
				$css .= $_css->begin_array_rule($targets);
					$css .= $_css->add_rule('background-color', 
						$this->query->option($_['content'], '_in_hover'));
					$css .= $_css->add_rule('cursor', 'pointer');
					$css .= $_css->browser_rules('transition', 'background-color 500ms ease-out');

				$css .= $_css->end_rule();	

				// Archive comment meta
				$css .= $_css->begin_rule('.entry-content-post-' . $ID . ' .pagoda-post-comments');
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_a_color'));
				$css .= $_css->end_rule();


				$css .= $_css->begin_rule('.entry-content-post-' . $ID . ' .pagoda-post-comments:hover');
					$css .= $_css->add_rule('color', $this->query->option($_['content'], '_a_hover'));
				$css .= $_css->end_rule();

				// Image frames				
				if( $this->query->option( $_['frames'], '_image' ) ):

					// Set up regex to determine minimum padding
					$_regex = "/^(\d+)(em|vw|vh|cm|mm|in|px|pt)?$/";
					preg_match( $_regex, $this->query->option($_['frames'], '_fade'), $_fade );
					preg_match( $_regex, $this->query->option($_['frames'], '_size'), $_size );

					// Add padding to make room for box shadow frames
					if ( !empty($_fade) && (int)$_fade[1] != 0 && 
						 !empty($_size) && (int)$_fade[1] != 0 ){

						$css .= $_css->begin_rule('.entry-content-post-' . $ID . ' .entry-content-img-wrap');
							$_padding = max( $_fade[1], $_size[1], 5 );
							$css .= $_css->add_rule( 'padding', '5px' . ' ' . $_padding . 'px' );
						$css .= $_css->end_rule();
					}

					$targets = array(
						'.entry-content-post-' . $ID . ' .entry-content-thumbnail img',
						'.entry-content-post-' . $ID . ' .entry-content-header img', 
						'.entry-content-post-' . $ID . ' .entry-content-main img',
					);
					$css .= $_css->begin_array_rule($targets);
						$css .= $_css->add_rule('border-radius', 
							$this->query->option($_['frames'], '_radius'));
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
						'.entry-content-post-' . $ID . ' textarea',
						'.entry-content-post-' . $ID . ' input', 
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

	//////////////////////////////////////////////////////////////////////////////////////
	//							  	 META LAYOUT PROPERTIES								//
	//////////////////////////////////////////////////////////////////////////////////////
	// This base CSS applies to posts and pages  
	private function css_generate_meta_b($data, $_css){

		$css = '';
		/***************************** CONTENT AREA HEADER *****************************/
		///////////////
		// Thumbnail //
		///////////////
		$css .= $_css->begin_rule('.entry-content-thumbnail');
			$css .= $_css->add_rule('display' 	, 'block');
			$css .= $_css->add_rule('width' 	, '100%');
			$css .= $_css->add_rule('padding'	, '20px 10px');
			$css .= $_css->add_rule('box-sizing', 'border-box');
			$css .= $_css->add_rule('overflow'	, 'auto');
		$css .= $_css->end_rule();
		
		$css .= $_css->begin_rule('.entry-content-img-wrap');
			// Width is assigned in metabox added inline 
			$css .= $_css->add_rule('margin'	, '0 auto');
			$css .= $_css->add_rule('box-sizing', 'border-box');
		$css .= $_css->end_rule();

		// Override in responsive mode for full width images. Need 
		// !important to override the inline thumbnail style. 
		$css .= $_css->begin_media('all and (max-width:480px)'); 
			$css .= $_css->begin_rule('.entry-content-img-wrap');
				$css .= $_css->add_rule('width', '100% !important');
			$css .= $_css->end_rule();		
		$css .= $_css->end_media();

		///////////
		// Title //
		///////////
		// No CSS needed

		////////////////
		// Navigation //
		////////////////

		$css .= $_css->begin_rule('.entry-content-nav');
			$css .= $_css->add_rule('display' 	, 'block');
			$css .= $_css->add_rule('width' 	, '100%');
			$css .= $_css->add_rule('padding'	, '10px 0px');
			$css .= $_css->add_rule('box-sizing', 'border-box');
			$css .= $_css->add_rule('overflow'	, 'auto');
		$css .= $_css->end_rule();

		$css .= $_css->begin_rule('.post-nav-prev');
			$css .= $_css->add_rule('float'		, 'left');
		$css .= $_css->end_rule();
		
		$css .= $_css->begin_rule('.post-nav-next');
			$css .= $_css->add_rule('float'		, 'right');
		$css .= $_css->end_rule();	

		// Phones
		$css .= $_css->begin_media('all and (max-width:480px)');
	 		$css .= $_css->begin_rule('.entry-content-nav');
	 			$css .= $_css->add_rule('display', 'none');
		 	$css .= $_css->end_rule();
		$css .= $_css->end_media();

		/***************************** CONTENT AREA FOOTER *****************************/
		//////////
		// Tags //
		//////////
		$_align = $this->query->option($data['display'], '_mn_align');

		$css .= $_css->begin_rule('.entry-content-tags');
			$css .= $_css->add_rule('display' ,	'block');
			$css .= $_css->add_rule('overflow', 'auto');
			$css .= $_css->add_rule('width'	  ,  '100%');
			$css .= $_css->add_rule('padding-bottom', '10px');
			$css .= $_css->add_rule('box-sizing', 'border-box');
			$css .= $_css->add_rule('text-align', 
				strcmp($_align, 'justify') == 0  ? 'left' : $_align );		
		$css .= $_css->end_rule();

		/////////////////////
		// Post Pagination //
		/////////////////////

		$css .= $_css->begin_rule('.entry-content-pgn');
			$css .= $_css->add_rule('display'	, 'block');
			$css .=	$_css->add_rule('width'		, '100%');
			$css .= $_css->add_rule('padding'	, '15px 0px');
			$css .= $_css->add_rule('box-sizing', 'border-box');	
			$css .= $_css->add_rule('text-align', 'center');
		$css .= $_css->end_rule();

		$targets = array(
			'.entry-content-pgn > .entry-content-pgn-item',
			'.entry-content-pgn > a',
			'.entry-content-pgn > span',
			'span > .entry-content-pgn-item'
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->add_rule('min-width'	, '30px');
			$css .= $_css->add_rule('height'	, 'inherit');
			$css .= $_css->add_rule('line-height', '30px');
			$css .= $_css->add_rule('display'	, 'inline-block');
		$css .= $_css->end_rule();

		//////////////
		// Comments //
		//////////////

		// Main div layout  
		$css .= $_css->begin_rule('.comments-area');
			$css .= $_css->add_rule('display'	, 'inline-block');
			$css .= $_css->add_rule('box-sizing', 'border-box');
			$css .= $_css->add_rule('float'		, 'left');
			$css .= $_css->add_rule('width'		, '100%');
		$css .= $_css->end_rule();	

		// Comment form padding
		$css .= $_css->begin_rule('.comment-form');
			$css .= $_css->add_rule('padding', '10px 10px');
			$css .= $_css->add_rule('text-align', 'left');
			$css .= $_css->add_rule('overflow', 'auto');
			$css .= $_css->add_rule('width'	 , '480px' );
		$css .= $_css->end_rule();

		// Set font sizes
		$targets = array(
			'.comments-area p',
			'.vcard > cite.fn',
			'.vcard > span.says',
			'.comment-meta > a',
			'.reply > a',
			'.comment-respond p',
			'.comment-respond a',
			'span#email-notes',
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->add_rule('display' ,'inline');
		$css .= $_css->end_rule();	

		// No bullet points
		$css .= $_css->begin_rule('.comments-area ul');
			$css .= $_css->add_rule('list-style'	, 'none');
		$css .= $_css->end_rule();	

		// Set width
		$css .= $_css->begin_rule('.comments-area > ul');
			$css .= $_css->add_rule('width'		, '80%');
		$css .= $_css->end_rule();	

		$css .= $_css->begin_media('all and (max-width:980px)');
			$css .= $_css->begin_rule('.comments-area ul');
				$css .= $_css->add_rule('width'	, '100%');
			$css .= $_css->end_rule();	
		$css .= $_css->end_media();	

		// Style the li
		$css .= $_css->begin_rule('.comments-area .bypostauthor');
			$css .= $_css->add_rule('padding', '0px');
			$css .= $_css->add_rule('width', '100%');
		$css .= $_css->end_rule();

		// Comment body layout
		$css .= $_css->begin_rule('.comment-body');
			$css .= $_css->add_rule('padding-bottom', '20px');
		$css .= $_css->end_rule();

		// Smaller margins on comment text/respond
		$targets = array(
			'.comment-body > p',
			'.comment-respond p',
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->add_rule('margin'	, '5px 0px');
		$css .= $_css->end_rule();

		$css .= $_css->begin_rule('.comment-form label');
			$css .= $_css->add_rule('padding'	, '5px');
		$css .= $_css->end_rule();

		// Block display reply container
		$css .= $_css->begin_rule('.comment-form-comment');
			$css .= $_css->add_rule('display', 'block');
		$css .= $_css->end_rule();

		// Display none the 'comments' label
		$css .= $_css->begin_rule('.comment-form-comment > label');
			$css .= $_css->add_rule('display'	,	'none');
		$css .= $_css->end_rule();

		// Text Area layout 
		$css .= $_css->begin_rule('.comment-form textarea#comment');
			$css .= $_css->add_rule('padding', '10px 10px');
			$css .= $_css->add_rule('max-width'	 , '100%' );
			$css .= $_css->add_rule('height' , '160px' );
			$css .= $_css->add_rule('float'  , 'left'  );
			$css .= $_css->add_rule('box-sizing', 'border-box');
		$css .= $_css->end_rule();

		// Submit container display block
		$css .= $_css->begin_rule('.comment-form .form-submit');
			$css .= $_css->add_rule('display' , 'block');
		$css .= $_css->end_rule();

		// Input fields and submit button
		$css .= $_css->begin_rule('.comment-form input');			
			$css .= $_css->add_rule('padding', '10px 20px');
			$css .= $_css->add_rule('margin', '5px 0px');
		$css .= $_css->end_rule();

		// Bold submit button
		$css .= $_css->begin_rule('.comment-form input#submit');
			$css .= $_css->add_rule('font-weight', 'bold');
		$css .= $_css->end_rule();

		// Comments Responsive rules 
		// (comments area)
		$css .= $_css->begin_media('all and (max-width:480px)'); 
			$css .= $_css->begin_rule('.comment-form');
				$css .= $_css->add_rule('width', '100% !important');
			$css .= $_css->end_rule();		
		$css .= $_css->end_media();

		$css .= $_css->begin_media('all and (max-width:980px)');
			$css .= $_css->begin_rule('.comments-area');	
				$css .= $_css->add_rule('padding'	, '20px 0');
			$css .= $_css->end_rule();			
			
			$css .= $_css->begin_rule('.comments-area ul');
					$css .= $_css->add_rule('padding'	, '0');
			$css .= $_css->end_rule();				
				
			$css .= $_css->begin_rule('.comments-area ul.children');
					$css .= $_css->add_rule('padding-left'	, '5vw');
			$css .= $_css->end_rule();				
		$css .= $_css->end_media();	

		// (text area)
		$css .= $_css->begin_media('all and (max-width:980px)');
			$css .= $_css->begin_rule('.comment-form textarea#comment');
				$css .= $_css->add_rule('padding'	, '5px 10px');
				$css .= $_css->add_rule('width'		, '90%');
			$css .= $_css->end_rule();

			$css .= $_css->begin_rule('.comment-form input');
				$css .= $_css->add_rule('padding'	, '5px 10px');
				$css .= $_css->add_rule('width'		, '90%');
			$css .= $_css->end_rule();				

			$css .= $_css->begin_rule('.comment-form input#submit');
				$css .= $_css->add_rule('padding'	, '5px 10px');
				$css .= $_css->add_rule('margin'	, '5px 0px');
				$css .= $_css->add_rule('width'		, '40%');
			$css .= $_css->end_rule();
		$css .= $_css->end_media();	

		// (submit button)
		$css .= $_css->begin_media('all and (max-width:440px)');
			$css .= $_css->begin_rule('.comment-form input#submit');
				$css .= $_css->add_rule('padding'	, '5px 10px');
				$css .= $_css->add_rule('margin'	, '5px 0px');
				$css .= $_css->add_rule('width'		, '70%');
			$css .= $_css->end_rule();
		$css .= $_css->end_media();	

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
			'.entry-content-post .entry-content-pgn > .entry-content-pgn-item',
			'.entry-content-post .entry-content-pgn > a',
			'.entry-content-post .entry-content-pgn > span',
			'.entry-content-post span > .entry-content-pgn-item'
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($a_font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_font_size', 'px') );
		$css .= $_css->end_rule();

		// Set font sizes in comments area
		$targets = array(
			'.entry-content-post .comments-area p',
			'.entry-content-post .vcard > cite.fn',
			'.entry-content-post .vcard > span.says',
			'.entry-content-post .comment-meta > a',
			'.entry-content-post .reply > a',
			'.entry-content-post .comment-respond p',
			'.entry-content-post .comment-respond a',
			'.entry-content-post span#email-notes',
		);
		$css .= $_css->begin_array_rule($targets);
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_p_meta_size') . 'px');
		$css .= $_css->end_rule();	

		// Text area font (comments)
		$css .= $_css->begin_rule('.entry-content-post .comment-form textarea#comment');
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
		$css .= $_css->end_rule();	

		// Input fields (comments) 
		$css .= $_css->begin_rule('.entry-content-post .comment-form input');
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
		foreach($data['posts'] as $ID => $_) {

			if ( in_array($_['class'], $is_written) ){continue;}
	
			else{
				array_push($is_written, $_['class']);

				/***************************** CONTENT AREA FOOTER *****************************/
				/////////////////////
				// Post Pagination //
				/////////////////////
				$targets = array(
					'.entry-content-post .entry-content-pgn-' . $ID . ' > .entry-content-pgn-item-' . $ID,
					'.entry-content-post .page-numbers.current' . ' > .entry-content-pgn-item-' . $ID
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

	//////////////////////////////////////////////////////////////////////////////////////
	//								FILTERS AND METABOXES 								//
	//////////////////////////////////////////////////////////////////////////////////////
	// Wordpress will call these methods as filter functions so they should be 'public'

	// A filter function to display the author name as display_name in comments.
	public function post_comment_author( $author = '' ) {
	    $comment = get_comment( $comment_ID );

    	if ( !empty( $comment->comment_author ) ) {
			if ( $comment->user_id && $user = get_userdata( $comment->user_id ) )
				$author = $user->display_name;
			else
				$author = __('Anonymous', 'pagoda');
		} else {
			$author = __('Anonymous', 'pagoda');
		}
	    return $author;
	}

	// A filter function to wrap all images in container div (pages/posts). This 
	// filter is called when running 'the_content()'. 
	public function post_format_images( $content ){

		// run preg_replace() on the $content to inject div
		return preg_replace( 
			'/(<img([^>]*)>)/i',  
			'<div class="entry-content-img-wrap">$1</div>',
			$content 
		);
	}

	// Setup the category header metabox
	public function metabox_init() {
		add_action( 'add_meta_boxes', array($this, 'add_metaboxes' ) );
		add_action( 'save_post'		, array($this, 'save_metaboxes'), 10, 2 );
	}

	// Add the metaboxes
	public function add_metaboxes() {

		// Filter for the default wordpress featured image metabox
		add_filter( 'admin_post_thumbnail_html', array($this, 'featured_image_metabox') , 10, 2 );

		// remove the default page-attributes metabox
    	// We will add our own and pack catgory styles
	    remove_meta_box(
    	    'pageparentdiv',
        	'post',
        	'normal'
    	);
 		add_meta_box(
    		'post-attributes', 	    				// Unique ID
    		esc_html__( 'Post Attributes', 'pagoda' ),  // Title
    		array($this,'post_attributes_metabox'), // Callback function
    		'post',         						// Admin page (or post type)
    		'normal',         						// Context
    		'default'         						// Priority
  		);
 		add_meta_box(
    		'post-properties', 	 		   			// Unique ID
    		esc_html__( 'Post Properties', 'pagoda' ), // Title
    		array($this,'post_properties_metabox'), // Callback function
    		'post',         						// Admin page (or post type)
    		'normal',         						// Context
    		'default'								// Priority
  		);

	}

	///////////////////////////////////////////
	// Callbacks for rendering metabox html. //
	///////////////////////////////////////////
	
	// Category styles metabox 
	public function post_attributes_metabox( $post ) { ?>

		<?php wp_nonce_field( basename( __FILE__ ), 'post_attributes_nonce' ); ?>

		<?php page_attributes_meta_box($post);?>

		<?php $perCategory = get_option('pagoda_post_styles_activate'); ?>

		<p>

		<?php 
			_e( '<strong>Post Category Styles: </strong>', 'pagoda');
		
			if ( $perCategory ){
				_e( 'Select the category style to apply when rendering content for this post.' ,'pagoda' );
			}		
			else{
				_e( 'per-Category styles are not active. To activate, see the posts panel in the theme customizer', 'pagoda');
			}
		?>

		<br/>

		<select class="pagoda-metabox-select widefat"
				id="pagoda-metabox-<?php echo $post->ID ?>"
				name="category_styles_<?php echo $post->ID ?>"
				autocomplete="off" <?php echo $perCategory ? '' : 'disabled'; ?> >

		<?php $_category = get_post_meta($post->ID, 'category_styles', true); ?>
		
		<?php if ( $perCategory ) :?>

				<?php foreach($this->page_tree->get_categories() as $_ => $WP_Term): ?>
					<option value="<?php echo $WP_Term->slug; ?>"
					<?php echo (strcmp($_category, $WP_Term->slug) == 0) ? "selected='selected'": '' ?> >
					<?php echo (strcmp($WP_Term->slug, "uncategorized") == 0) ? "Default" : $WP_Term->name ?> 
					</option>
				<?php endforeach; ?>

		<?php else: ?>
			<option>Default</option>

		<?php endif; ?>		

        </select>
  		</p>

	<?php }

	// Post properties metabox
	public function post_properties_metabox( $post ) { ?>
		<?php wp_nonce_field( basename( __FILE__ ), 'post_properties_nonce' ); ?>

		<p>
		<input class="pagoda-metabox-checkbox" 
				id="pagoda-metabox-checkbox-title-<?php echo $post->ID ?>"
				name="show_title_<?php echo $post->ID ?>"
				value="enable"
				type="checkbox"   
				<?php if( get_post_meta($post->ID, 'show_title', true) ) echo 'checked="checked"'; ?>
		/>
		<?php _e('<strong>Show Post Title?</strong>','pagoda'); ?>
		</p>

		<p>
		<input class="pagoda-metabox-checkbox" 
				id="pagoda-metabox-checkbox-nav-<?php echo $post->ID ?>"
				name="show_navigation_<?php echo $post->ID ?>"
				value="enable"
				type="checkbox"   
				<?php if( get_post_meta($post->ID, 'show_navigation', true) ) echo 'checked="checked"'; ?>
		/>
		<?php _e('<strong>Show Post Navigation?</strong>','pagoda'); ?>
		</p>

		<p>
		<input class="pagoda-metabox-checkbox" 
				id="pagoda-metabox-checkbox-comments-<?php echo $post->ID ?>"
				name="show_comments_<?php echo $post->ID ?>"
				value="enable"
				type="checkbox"   
				<?php if( get_post_meta($post->ID, 'show_comments', true) ) echo 'checked="checked"'; ?>
		/>
		<?php _e('<strong>Show Post Comments?</strong>', 'pagoda'); ?>
		</p>

		<p>
		<input class="pagoda-metabox-checkbox" 
				id="pagoda-metabox-checkbox-tags-<?php echo $post->ID ?>"
				name="show_tags_<?php echo $post->ID ?>"
				value="enable"
				type="checkbox"
				<?php if( get_post_meta($post->ID, 'show_tags', true) ) echo 'checked="checked"'; ?>
			/>
			<?php _e('<strong>Show Post Tags?</strong>', 'pagoda'); ?>
		</p>

		<p>
		<?php _e( 'Note that when disabling comments, existing comments will not be deleted.' , 'pagoda'); ?>
		</p>
		<?php //var_dump( get_metadata('post', $post->ID) )?>

	<?php }

	// Add a numeric size field to the featured image metabox. 
	// Luckily there is a filter for this (see constructor) 
	public function featured_image_metabox( $content, $post_id ){ 

		// Initialize output buffer
		ob_start(); ?>

		<p>
		<?php _e('<strong>Featured Image Width (%):</strong>', 'pagoda'); ?>
		<?php $width = get_post_meta($post_id, 'size_featured_image', true); ?>
		<input class="pagoda-metabox-input-image-size widefat" 
				id="pagoda-metabox-size-<?php echo $post_id ?>"
				name="size_featured_image_<?php echo $post_id ?>"
				type="number" min="0" max="100" step="1"
				value="<?php echo $width ? $width : '85' ?>" />
		<?php _e('Featured images will be displayed at full width on mobile devices', 'pagoda'); ?>
		</p>

		<p>
		<input class="pagoda-metabox-checkbox"
				id="pagoda-metabox-checkbox-image-show-<?php echo $post_id ?>"
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

		// Append output buffer ony if we are on a post
		return get_post_type($post_id) == 'post' ? $content . $output : $content;  
	}

	// Save metabox info. 
	public function save_metaboxes( $post_id, $post ) {

		// Verify metabox nonce
		if ( !isset( $_POST['post_attributes_nonce'] ) || 
			 !wp_verify_nonce( $_POST['post_attributes_nonce'], basename( __FILE__ ) ) ) {
				return $post_id;
		}

		if ( !isset( $_POST['post_properties_nonce'] ) || 
			 !wp_verify_nonce( $_POST['post_properties_nonce'], basename( __FILE__ ) ) ) {
				return $post_id;
		}

		// Verify admin permissions
		$post_type = get_post_type_object( $post->post_type );
		if ( !current_user_can( $post_type->cap->edit_post, $post_id ) ){
			return $post_id;
		}

		// Setup array for metabox data
		$meta_box = array(

			'post_attributes' => array(
				'category_styles' => array(
					'type' => 'select'
			) ),
			
			'post_properties' => array(
				'show_navigation' => array(
					'type' => 'checkbox'
				), 	
				'show_comments'	  => array(
					'type' => 'checkbox'
				),	
				'show_title'=> array(
					'type' => 'checkbox'
				),	
				'show_tags' => array(
					'type' => 'checkbox'
			) ),
			
			'featured_image'  => array(
				'size_featured_image' => array(
					'type'  => 'css_width', 
					'range' => array('min' => 0, 'max' => 100)
				),
				'show_featured_image' => array(
					'type' => 'checkbox'
		) ) );

		// Loop through metaboxes and assign values. 
		// This loop also sanitizes user input.		
		foreach ( $meta_box as $_ => $meta_keys ) {

			// For each metabox, loop through meta_keys
			foreach ($meta_keys as $meta_key => $meta_data){
				
				$meta_val = get_post_meta( $post_id, $meta_key, true );
				
				// Select category header field. 
				if ( strcmp($meta_data['type'], 'select') == 0 ){
				 	$meta_new =	!empty( $_POST[$meta_key . '_' . $post_id] ) ? 
				 		sanitize_text_field( $_POST[$meta_key . '_' . $post_id] ) : false;

				 	if ( $meta_new != false ){
						update_post_meta( $post_id, $meta_key, $meta_new);				 		
				 		update_post_meta( $post_id, 'styles_activate', "1");
				 	}
				 	else {
				 		if ( metadata_exists('post', $post_id, 'category_styles') == false ){
							update_post_meta( $post_id, $meta_key, 'uncategorized'); // New posts
				 		}
				 		update_post_meta( $post_id, 'styles_activate', "");	
				 	}
				}

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
}