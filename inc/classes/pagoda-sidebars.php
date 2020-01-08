<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class pagoda_sidebars{

	public function __construct($query, $font_gen){
	
		$this->font_gen = $font_gen;  // The single instance of Nablafire_Fonts_Autogen()
		$this->query    = $query;     // The single instance of pagoda_query_option()
		$this->font_enqueue = array();	
	
		// Sidebars to register: This array also includes data which is useful for 
		// the customizer. We would ideally make this a JSON object but sidebar data 
		// contains translatable strings.  
		$this->sidebars = array(
			'home'      => array(
				'slug'	=> 'home',			// Iterated option slug
				'id' 	=> 'home-sidebar',	// dynamic_sidebar()
				'nice'	=> esc_html__( 'Home' , 'pagoda'),// For customizer
				'name'	=> esc_html__( 'Pagoda Home Sidebar', 'pagoda' ),				
				'data'  => false
			),
			'left' 		=> array( 
				'slug'	=> 'left',			
				'id' 	=> 'left-sidebar',	
				'nice'	=> esc_html__( 'Left' , 'pagoda'),
				'name'	=> esc_html__( 'Pagoda Left Sidebar', 'pagoda' ),
				'data'  => false
			),
			'right' 	=> array( 
				'slug'	=> 'right',
				'id' 	=> 'right-sidebar',
				'nice'	=> esc_html__( 'Right' , 'pagoda'),
				'name'	=> esc_html__( 'Pagoda Right Sidebar', 'pagoda' ),
				'data'  => false
			),
			'post' 	=> array( 
				'slug'	=> 'post',
				'id' 	=> 'post-sidebar',
				'nice'	=> esc_html__( 'Post' , 'pagoda'),
				'name'	=> esc_html__( 'Pagoda Post Sidebar', 'pagoda' ),
				'data'  => false
			),
			'blog' 		=> array( 
				'slug'	=> 'blog',
				'id' 	=> 'blog-sidebar',
				'nice'	=> esc_html__( 'Blog' , 'pagoda'),
				'name'	=> esc_html__( 'Pagoda Blog Sidebar', 'pagoda' ),
				'data'  => false
			),	
			'arxiv' 	=> array( 
				'slug'	=> 'arxiv',
				'id' 	=> 'arxiv-sidebar',
				'nice'	=> esc_html__( 'Archive' , 'pagoda'),
				'name'	=> esc_html__( 'Pagoda Archive Sidebar', 'pagoda' ),
				'data'  => false
		) );

		// Create an array that will store the fontstrings.   
		$this->initialize_sidebars(); // Initializes Query Option
		if ( user_can( wp_get_current_user() , 'administrator' ) ) {
			$this->write_css_sidebars();  // Initializes CSS Autogen 
		}
		// Register theme sidebars. These will be wigitized areas
		add_action( 'widgets_init',  array( $this, 'register_sidebars' ) );
	}

	public function register_sidebars() {
		foreach($this->sidebars as $_ => $sidebar) {
			register_sidebar( 
			array(
				'name'          => $sidebar['name'],
				'id'            => $sidebar['id'],
	            'before_widget' => '<div id="%1$s" class="pagoda-sidebar-widget %2$s">',
	            'after_widget' => '</div>',
				'before_title' => '<h2><span>',
				'after_title'  => '</span></h2>',
			) );
		}		
	}

	// Sidebars initialize options
	public function initialize_sidebars(){

		// Expand sidebar options. In this case we expand the iterated 'data' options first and 
		// then we pack pointers of the uniterated options into the iterated array. This way,
		// each sidebar 'object' will contain a complete set of its data. Note that we expand 
		// the data attributes against the style keys: 

		// Assign ['data'] object for each sidebar
		foreach ($this->sidebars as $id => $_) {

			// Expand main options against slug. 
			$this->sidebars[$id]['main'] = $this->query->subtable( array( 'sidebars', 'main' ), $_['slug'] );	

			// Assign sidebar style
			$_style = ( strcmp( $this->query->option($this->sidebars[$id]['main'], '_activate') , "1") == 0 ) ?
				$this->query->option($this->sidebars[$id]['main'], 'style_' . $_['slug']) : '0' ;

			// Pack Sidebar Data
			$this->sidebars[$id]['data'] = $this->query->subtable( array( 'sidebars', 'data' ), $_style );
			$this->sidebars[$id]['data']['fonts']  = $this->query->subtable( array( 'sidebars', 'fonts'   ) );
			$this->sidebars[$id]['data']['mobile'] = $this->query->subtable( array( 'mobile'  , 'sidebars') );
		}
	}

	// Getter method for customizer
	public function get_sidebars(){ return $this->sidebars; }

	// How this method interacts with template files: In 'page-templates/left-sidebar.php 
	// is a function call to this method
	//
	// 	  instantiate_theme()->sidebars->write_html_sidebar('left');
	//
	public function write_html_sidebar( $id ){ 
		$sidebar = $this->sidebars[$id];
		$show  	 = $this->query->option($sidebar['data']['title'], '_tl_show');
		$title   = $this->query->option($sidebar['main'], '_mn_title');
		?>

		<?php if ( is_active_sidebar( $sidebar['id'] ) ) :?>
		<div class="pagoda-sidebar-wrap-<?php echo $sidebar['slug'] ?>">
			<aside class="sidebar pagoda-sidebar-<?php echo $sidebar['slug'] ?>">
				<?php if($show): ?>
					<div class="sidebar pagoda-sidebar-title-area-<?php echo $sidebar['slug'] ?>">
						<p class="sidebar pagoda-sidebar-title-data-<?php echo $sidebar['slug'] ?>">
							<?php echo esc_html($title) ?>
						</p>
					</div>	
				<?php endif; ?>		
				<div class="pagoda-sidebar-widget-area pagoda-sidebar-widget-area-<?php echo $sidebar['slug']?>">
					<?php dynamic_sidebar( $sidebar['id'] ); ?>
				</div>
			</aside>
		</div>
		<?php endif; ?>
	<?php }

	public function write_css_sidebars(){ 
		$css_autogen = new Nablafire_CSS_Autogen();
		
		$this->css  = $css_autogen->comment('Pagoda Sidebars Machine Generated CSS');
		$this->css .= $this->css_generate_sidebars_a($this->sidebars, $css_autogen);

		update_option('pagoda_sidebars_styles', serialize($css_autogen->minify($this->css)));
		update_option('pagoda_sidebars_fonts', serialize($this->font_enqueue));
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//							   	ALL SIDEBAR PROPERTIES								//
	//////////////////////////////////////////////////////////////////////////////////////

	public function css_generate_sidebars_a($sidebars, $_css){


		$css = $_css->comment(" ------ Sidebar Fonts ------ ");

		// These fonts are uniterated properties so we simply borrow the
		// data from the left sidebar (any sidebar will suffice).   
		$_   = $sidebars['left']['data'];

		// <h> tags font enqueue
		$h_font_fam	= $this->query->option($_['fonts'], '_h_font_fam');
		$h_font_var	= $this->query->option($_['fonts'], '_h_font_var');
		array_push($this->font_enqueue, 
			array('font_fam' => $h_font_fam, 'font_var' => $h_font_var));

		// <h> tags CSS
		$h_tags = array(
			".pagoda-sidebar-widget-area h1",
			".pagoda-sidebar-widget-area h1 > span",	
			".pagoda-sidebar-widget-area h2",
			".pagoda-sidebar-widget-area h2 > span",	
			".pagoda-sidebar-widget-area h3",
			".pagoda-sidebar-widget-area h3 > span",
			".pagoda-sidebar-widget-area h4",
			".pagoda-sidebar-widget-area h4 > span",
			".pagoda-sidebar-widget-area h5",
			".pagoda-sidebar-widget-area h5 > span",
			".pagoda-sidebar-widget-area h6",
			".pagoda-sidebar-widget-area h6 > span",
		);
		$css .= $_css->begin_array_rule($h_tags);
			$css .= $_css->_literal($this->font_gen->css_fontfamily($h_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($h_font_var));
			$css .= $_css->add_rule('font-size'	, $this->query->option($_['fonts'], '_h_font_size') . 'px');
		$css .= $_css->end_rule();		

		// <p> tags font enqueue 

		$p_font_fam = $this->query->option($_['fonts'], '_p_font_fam');
		$p_font_var = $this->query->option($_['fonts'], '_p_font_var');
		$p_meta_var = $this->query->option($_['fonts'], '_p_meta_var');

		$fontVars 	= $this->font_gen->get_variants($p_font_fam);
		foreach ($fontVars as $_key => $_var) {
		 	array_push($this->font_enqueue, 
		 		array('font_fam' => $p_font_fam, 'font_var' => $_var));
		} 

		// <p> <ol> <ul> <li> <a> <span> <input> tags CSS
		$p_tags = array(
			".pagoda-sidebar-widget-area p",
			".pagoda-sidebar-widget-area q",
			".pagoda-sidebar-widget-area ol",
			".pagoda-sidebar-widget-area ul",
			".pagoda-sidebar-widget-area li",
			".pagoda-sidebar-widget-area a",
			".pagoda-sidebar-widget-area span",
			'.pagoda-sidebar-widget-area textarea',
			'.pagoda-sidebar-widget-area input',
		);
		$css .= $_css->begin_array_rule($p_tags);
			$css .= $_css->_literal($this->font_gen->css_fontfamily($p_font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($p_font_var));
			$css .= $_css->add_rule('font-size'	, $this->query->option($_['fonts'], '_p_font_size') . 'px');
		$css .= $_css->end_rule();

		// Add line height for <p> <ol> <ul> <li>
		$p_tags = array(
			".pagoda-sidebar-widget-area p",
			".pagoda-sidebar-widget-area ol",
			".pagoda-sidebar-widget-area ul",
			".pagoda-sidebar-widget-area li",
		);
		$css .= $_css->begin_array_rule($p_tags);
			$css .= $_css->add_rule('line-height', '1.5em');
		$css .= $_css->end_rule();

		// Style link font weight
		$css .= $_css->begin_rule(".pagoda-sidebar-widget-area a");
		 	$css .= $_css->_literal($this->font_gen->css_fontstyle($p_meta_var));
		$css .= $_css->end_rule();		

		// And link hover transitions
		$css .= $_css->begin_rule('.pagoda-sidebar-widget-area a:hover');
		 	$css .= $_css->browser_rules('transition', 'color 250ms ease-out');
		$css .= $_css->end_rule();
		
		$css .= $_css->comment(" ------ Sidebar Properties ------ ");	

		// We will loop through several ids
		foreach ( $sidebars  as $id => $sidebar ) {
	
			// Extract sidebar slug and data
			$_slug = $sidebar['slug'];
			$_     = $sidebar['data'];

			// The sidebar wrapper
			$css .= $_css->begin_rule('.pagoda-sidebar-wrap-' . $_slug);	
				$css .= $_css->add_rule('display'	, 'inline-block');
				$css .= $_css->add_rule('box-sizing', 'border-box');	
				$css .= ($_slug == 'left') ? $_css->add_rule('float', 'left') 
										   : $_css->add_rule('float', 'right');
				$css .= $_css->add_rule('width'		, $this->query->option($_['display'], '_sb_width_'));
				$css .= $_css->add_rule('padding' 	, $this->query->option($_['display'], '_sb_padding_'));
				$css .= $_css->add_rule('overflow'	, 'auto');
			$css .= $_css->end_rule();		

			// Sidebar mobile display behaviour.
			$css .= $_css->begin_media('all and (max-width:980px)');
			 	
				$show_sb = $this->query->option($_['mobile'], 'show_' . $_slug);
				if ( strcmp($show_sb, "1") == 0 ){
					// For an explanation of this CSS see the posts class (line 351)
						$css .= $_css->begin_rule('.pagoda-sidebar-wrap-' . $_slug);
						$css .= $_css->add_rule('display'	, 'table-footer-group');
							$css .= $_css->add_rule('padding' 	, '20px 0px 0px');
							$css .= $_css->add_rule('width'		, '100%');
							$css .= $_css->add_rule('overflow'	, 'unset');
					$css .= $_css->end_rule();
				}		 			
				else {
						$css .= $_css->begin_rule('.pagoda-sidebar-wrap-' . $_slug);
							$css .= $_css->add_rule('display'	, 'none');
						$css .= $_css->end_rule();
				}

	 		$css .= $_css->end_media();
		
	 		// The aside. Give a background color for proper behaviour using transparent 
	 		// title and widget background colors (compositing). 
	 		$css .= $_css->begin_rule('.pagoda-sidebar-' . $_slug); 
	 			$css .= $_css->add_rule('background-color',  
	 				$this->query->option($_['display'], '_crgba_'));
	 		$css .= $_css->end_rule();

			// Title font properties
			$font_fam	= $this->query->option($_['title'], '_tf_font_fam');
			$font_var	= $this->query->option($_['title'], '_tf_font_var');
			array_push($this->font_enqueue, 
				array('font_fam' => $font_fam, 'font_var' => $font_var));

			// Title Block CSS
			$css .= $_css->begin_rule('.pagoda-sidebar-title-area-' . $_slug);
			$css .= $_css->add_rule('margin'	, $this->query->option($_['display'], '_sb_tmargin_'));		
				$css .= $_css->add_rule('text-align', $this->query->option($_['title'], '_tl_align_'));
				$css .= $_css->add_rule('padding'	, $this->query->option($_['title'], '_tl_padding'));
				$css .= $_css->add_rule('background-color', $this->query->option($_['title'], '_tl_color_'));
			$css .= $_css->end_rule();

			// Title text
			$css .= $_css->begin_rule('.pagoda-sidebar-title-data-' . $_slug);
				$css .= $_css->add_rule('margin', '0');
				$css .= $_css->add_rule('background-color', 'inherit');
				$css .= $_css->_literal($this->font_gen->css_fontfamily($font_fam));
				$css .= $_css->_literal($this->font_gen->css_fontstyle($font_var));
				$css .= $_css->add_rule('font-size'	, $this->query->option($_['title'], '_tf_font_size_', 'px'));
				$css .= $_css->add_rule('color'	   	, $this->query->option($_['title'], '_tf_font_color_'));
			$css .= $_css->end_rule();

			// The widget area
			$css .= $_css->begin_rule('.pagoda-sidebar-widget-area-' . $_slug);
				$css .= $_css->add_rule('background-color', $this->query->option($_['display'], '_sb_color_'));
				$css .= $_css->add_rule('padding'	, $this->query->option($_['display'], '_sb_content_'));
				$css .= $_css->add_rule('margin'	, $this->query->option($_['display'], '_sb_cmargin_'));
				$css .= $_css->add_rule('box-sizing', 'border-box');
			$css .= $_css->end_rule();

			// Widgets padding
			$css .= $_css->begin_rule('.pagoda-sidebar-widget-area-' . $_slug . ' > .pagoda-sidebar-widget');
				$css .= $_css->add_rule('padding'	, $this->query->option($_['display'], '_sb_widget_'));
				$css .= $_css->add_rule('box-sizing', 'border-box');
			$css .= $_css->end_rule();

			// Logic for border elements. This needs to cover several cases. The code is 
			// rather complex as 'box-shadow' and 'border' behave differently.  
			if($this->query->option( $_['border'], '_bd_show_' ) ){

				// Border configuration properties. Assign one by one for readability.
				$_config = array();
				
				// 1) Border radius
				$_config['radius'] = 
					$this->query->option($_['border'], '_bd_radius_');
			
				// 2) Title block display
				$_config['title'] = 
					(strcmp( $this->query->option($_['title'], '_tl_show'),  "1" ) ==  0) ? true : false;
	
				// 3) Fuse title and content blocks
				$_config['fused'] = 
				 	(strcmp( $this->query->option($_['border'], '_bd_fused_'), "1" ) == 0) ? true : false;

				// 4) How to render borders (elements / container) 
				$_config['render'] = 
					(strcmp( $this->query->option($_['border'], '_bd_render_'), "1" ) == 0) ? true : false;
				
				// 5) Switch for 'border box'		
				$_config['solid'] = 
					preg_match("/^0(em|vw|vh|cm|mm|in|px|pt)?$/", 
						$this->query->option($_['border'], '_bd_fade')) ? true : false;

				// CSS targets for varying cases
			 	$_targets = array(
			 		"el" => array(
			 			'.pagoda-sidebar-title-area-'  . $_slug,
			 			'.pagoda-sidebar-widget-area-' . $_slug	
			 		),
			 		"sb" => array(
			 			'.pagoda-sidebar-' . $_slug
			 	) );

			 	// Border Properties for CSS methods. Note that input_atts is 
			 	// set on a case by case basis
	 			$_border = array(
					'fade'  => $this->query->option($_['border'], '_bd_fade'),
					'size'  => $this->query->option($_['border'], '_bd_size'), 
					'color' => $this->query->option($_['border'], '_bd_color'),
					'input_atts' => array(
						'mode' => $this->query->option( $_['border'], '_inset' ) ? 'inset' : 'default'
				) );

			 	// Case of a title block
			 	if ( $_config['title'] === true ){

			 		// Fused sidebar and render border on elements
			 		if ( $_config['fused'] === true && $_config['render'] === true ){

			 			// If solid border, take advantage of overflow hidden
						if ( $_config['solid'] )	{
							$css .= $_css->begin_array_rule( $_targets['sb'] );
				 				$css .= $_css->add_rule('border-radius' , $_config['radius'] );
				 				$css .= $_css->add_rule('overflow'		, 'hidden');
			 				$css .= $_css->end_rule();
						}

						// Otherwise round corners for box shadow
						else {
							$css .= $_css->begin_rule('.pagoda-sidebar-title-area-' . $_slug);
							$css .= $_css->add_rule('border-radius' , 
								$_config['radius'] . ' ' . $_config['radius'] . ' 0px 0px');
							$css .= $_css->end_rule();
						
							$css .= $_css->begin_rule('.pagoda-sidebar-widget-area-' . $_slug);
							$css .= $_css->add_rule('border-radius' , 
								'0px 0px ' . $_config['radius'] . ' ' . $_config['radius'] );
							$css .= $_css->end_rule();
						}
						$_border['input_atts']['targets'] = $_targets['el'];
		 				$css .= $_css->border_properties( $_border );
		 			}

			 		// Fused sidebar and render border on container
			 		if ( $_config['fused'] === true && $_config['render'] === false ){


			 			$css .= $_css->begin_array_rule( $_targets['sb'] );
			 				$css .= $_css->add_rule('border-radius' , $_config['radius'] );
			 				$css .= $_css->add_rule('overflow'		, 'hidden');
			 			$css .= $_css->end_rule();


			 			$_border['input_atts']['targets'] = $_targets['sb'];			 			 
		 				$css .= $_css->border_properties( $_border );
			 		}

			 		// If not fused then we always render borders on elements
			  		if ( $_config['fused'] === false ){

			  			$css .= $_css->begin_array_rule( $_targets['el'] );
			 				$css .= $_css->add_rule('border-radius' , $_config['radius'] );
			 			$css .= $_css->end_rule();

	 					$_border['input_atts']['targets'] = $_targets['el'];
		 				$css .= $_css->border_properties( $_border );
			  		}
			 	}

			 	// If there is no title block. Then simply render on the sidebar
			 	if ( $_config['title'] === false ){

			  			$css .= $_css->begin_array_rule( $_targets['el'] );
			 				$css .= $_css->add_rule('border-radius' , $_config['radius'] );
			 				$css .= $_css->add_rule('overflow'		, 'hidden');
			 			$css .= $_css->end_rule();

		 				$_border['input_atts']['targets'] = $_targets['el'];
		 				$css .= $_css->border_properties( $_border );
			 	}
			}

			// <h> tags font color (per sidebar)
			$h_tags = array(
				'.pagoda-sidebar-widget-area-' . $_slug . ' h1',
				'.pagoda-sidebar-widget-area-' . $_slug . ' h1 > span',
				'.pagoda-sidebar-widget-area-' . $_slug . ' h2',
				'.pagoda-sidebar-widget-area-' . $_slug . ' h2 > span',
				'.pagoda-sidebar-widget-area-' . $_slug . ' h3',
				'.pagoda-sidebar-widget-area-' . $_slug . ' h3 > span',
				'.pagoda-sidebar-widget-area-' . $_slug . ' h4',
				'.pagoda-sidebar-widget-area-' . $_slug . ' h4 > span',
				'.pagoda-sidebar-widget-area-' . $_slug . ' h5',
				'.pagoda-sidebar-widget-area-' . $_slug . ' h5 > span',
				'.pagoda-sidebar-widget-area-' . $_slug . ' h6',
				'.pagoda-sidebar-widget-area-' . $_slug . ' h6 > span',

			);
			$css .= $_css->begin_array_rule($h_tags);
				$css .= $_css->add_rule('color', $this->query->option($_['content'], '_h_color_') );
			$css .= $_css->end_rule();		

			// <p> tags font color (per sidebar)
			$p_tags = array(
				'.pagoda-sidebar-widget-area-' . $_slug . ' p',
				'.pagoda-sidebar-widget-area-' . $_slug . ' span',
			);
			$css .= $_css->begin_array_rule($p_tags);
				$css .= $_css->add_rule('color', $this->query->option($_['content'], '_p_color_') );
			$css .= $_css->end_rule();	

			// <a> tags font color (per sidebar)
			$a_tags = array(
				'.pagoda-sidebar-widget-area-' . $_slug . ' a',
				'.pagoda-sidebar-widget-area-' . $_slug . ' ul',
				'.pagoda-sidebar-widget-area-' . $_slug . ' ol',
			);
			$css .= $_css->begin_array_rule($a_tags);
				$css .= $_css->add_rule('color', $this->query->option($_['content'], '_a_color_') );
			$css .= $_css->end_rule();

			$css .= $_css->begin_rule('.pagoda-sidebar-widget-area-' . $_slug . ' a:hover');
				$css .= $_css->add_rule('color', $this->query->option($_['content'], '_a_hover_') );
			$css .= $_css->end_rule();	

			$targets = array(
				'.pagoda-sidebar-widget-area-' . $_slug . ' textarea',
				'.pagoda-sidebar-widget-area-' . $_slug . ' input', 
			);
			
			$css .= $_css->begin_array_rule($targets);
				$css .= $_css->add_rule('color', $this->query->option($_['content'], '_p_color'));
				$css .= $_css->add_rule('background-color', $this->query->option($_['content'], '_in_color'));
			$css .= $_css->end_rule();

			$targets = array(
				'.pagoda-sidebar-widget-area-' . $_slug . ' input#submit:hover', 
				'.pagoda-sidebar-widget-area-' . $_slug . ' input[type="submit"]:hover'
			);
			$css .= $_css->begin_array_rule($targets);
				$css .= $_css->add_rule('background-color', $this->query->option($_['content'], '_in_hover'));
				$css .= $_css->add_rule('cursor', 'pointer');
				$css .= $_css->browser_rules('transition', 'background-color 500ms ease-out');

			$css .= $_css->end_rule();	

			if( $this->query->option( $_['frames'], '_show' ) ):

				// Image frames
				$targets = array(
					'.pagoda-sidebar-widget-area-' . $_slug . ' img', 
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

				// Input frames
				$targets = array(
					'.pagoda-sidebar-widget-area-' . $_slug . ' textarea',
					'.pagoda-sidebar-widget-area-' . $_slug . ' input', 
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
		return $css;
	}
}