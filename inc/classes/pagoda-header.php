<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class pagoda_header { 

	public function __construct($query, $font_gen, $settings, $navbar) {
		 
		$this->font_gen = $font_gen;  // The single instance of Nablafire_Fonts_Autogen()
		$this->query    = $query;     // The single instance of pagoda_query_option()
		$this->navbar   = $navbar;	  // The single instance of pagoda_navbar()	
		$this->settings = $settings;  // The config options from settings page	
	
		$this->font_enqueue = array();

		$this->initialize_header();   // Expands options via pagoda_query_option()
		if ( user_can( wp_get_current_user() , 'administrator' ) ) {
			$this->write_css_header();    // Initializes CSS Autogen and wites CSS data
		}
	}
	
	//////////////////////////////////////////////////////////////////////////////////////
	//									INIT METHODS 									//
	//////////////////////////////////////////////////////////////////////////////////////
	public function initialize_header(){

		// Cast settings as range arrays
		$n_images  = range(1, $this->settings['n_images'] );
		$n_buttons = range(1, $this->settings['n_buttons'] );

		// Expand all header options
		$this->data = array(

			'u'    => array(
				'display' => $this->query->subtable( array('header', 'display', 'u' ) ),
				'buttons' => $this->query->subtable( array('header', 'buttons', 'u' ) ),
				'banner'  => $this->query->subtable( array('header', 'banner' ) ),
				'mobile'  => $this->query->subtable( array('mobile', 'header' ) ),
			),
			'i'	  => array(
				'display' => $this->query->subtable( array('header' , 'display', 'i' ), $n_images),
				'buttons' => $this->query->subtable( array('header' , 'buttons', 'i' ), $n_buttons),
		) );
		
		// Append configuration elemets to data array
		$this->data['config'] = $this->process_selectors();
	}

	///////////////////////////////////////////
	// Process Backround Selectors
	//
	public function process_selectors(){
	

		// Background mode selectors
		$bg_animate  = $this->query->option( $this->data['u']['display'], '_animate' );
		$bg_parallax = $this->query->option( $this->data['u']['display'], '_parallax' ); 

		// Set background mode
		if ($bg_animate) { $mode = 'animated'; }
		elseif ($bg_parallax) { $mode = 'parallax'; }	
		else { $mode = 'static'; }

		// Set background image
		foreach ($this->data['i']['display'] as $_ => $options) {
		 	if ($this->query->option($options, '_switch')){
		  		$img = $this->query->option($options, '_image'); break;
		  	}
		} 

		// Write configuration data
		$config = array(
			'img'   => isset($img) ? $img : '',
			'mode'  => $mode,
			'show'  => array(
				'image'		=> $this->query->option($this->data['u']['display'], '_show_image'),
				'title'		=> $this->query->option($this->data['u']['display'], '_show_title'),
				'subtitle'	=> $this->query->option($this->data['u']['display'], '_show_subtitle'),
				'buttons'	=> $this->query->option($this->data['u']['display'], '_show_buttons'),
		) );
		return $config;
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//									HTML METHODS 									//
	//////////////////////////////////////////////////////////////////////////////////////
	// This function will be called from the header.php template file. Because we want 
	// this method to be called without arguments, all options will be stored as class 
	// data members.
	public function write_html_header(){

		if ( strcmp($this->data['config']['mode'], 'animated') == 0 ){
			$this->html_generate_animated();
		}

		if ( strcmp($this->data['config']['mode'], 'parallax') == 0 ){
			$this->html_generate_parallax();
		}

		if ( strcmp($this->data['config']['mode'], 'static')   == 0 ){
			$this->html_generate_static();	  			
		}
	}	

	// For Static Background Image
	private function html_generate_static(){ ?>
	
		<div class="pagoda-header-wrap">
			<?php $this->navbar->html_generate_navbar('', 'home');?>
			<div class="pagoda-header-static">
				<div class="pagoda-hedaer-content">
					<?php $this->html_generate_banner(); ?>
					<?php $this->html_generate_buttons(); ?>
				</div>
			</div>
		</div>
	<?php }

	// For Parallax Background Image
	private function html_generate_parallax(){ ?>
	
		<div class="pagoda-header-wrap">
			<?php $this->navbar->html_generate_navbar('', 'home');?>
			<div class="pagoda-header-parallax">
				<div class="pagoda-hedaer-content">
					<?php $this->html_generate_banner(); ?>
					<?php $this->html_generate_buttons(); ?>
				</div>
			</div>
		</div>

	<?php }

	// For Animated Backgrounds. Note we need to push the animation down if logged in .. 
	private function html_generate_animated(){ ?>	
		<div class="pagoda-header-wrap">
			<?php $this->navbar->html_generate_navbar('', 'home');?>
			<div class="pagoda-header-animated">
				<div class="pagoda-header-slideshow" 
					<?php if ( user_can( wp_get_current_user() , 'administrator' ) ){
						echo "style=\"margin-top: 32px\""; }?> >
					<ul>
					<?php  // echo images
						for($id=1; $id<= $this->settings['n_images']; $id++): 
							echo "<li><span></span></li>";
						endfor; 
					?>
					</ul>	
				</div>

				<div class="pagoda-hedaer-content">		
					<?php $this->html_generate_banner(); ?>
					<?php $this->html_generate_buttons(); ?>
				</div>
			</div>
		</div>	
	<?php }


	///////////////////////////////////////////
	// The banner
	//
	// "pagoda_banner_mn_text"	: "Pagoda",
	// "pagoda_banner_sb_text"	: "Engineered by Nablafire",
	// 
	private function html_generate_banner(){ ?>
	
		<?php if( $this->data['config']['show']['image'] && 
				$this->query->option($this->data['u']['banner'],'_img_source')): ?>
			<div class ="pagoda-header-banner-img">
				<div></div>
			</div>	
		<?php endif; ?>	

		<?php if( $this->data['config']['show']['title'] ): ?>
			<div class ="pagoda-header-banner-main">
				<p><?php echo esc_html($this->query->option($this->data['u']['banner'], '_mn_text'))?></p>
			</div>
		<?php endif; ?>	
			
		<?php if( $this->data['config']['show']['subtitle'] ): ?>
			<div class ="pagoda-header-banner-sub">
				<p><?php echo esc_html($this->query->option($this->data['u']['banner'], '_sb_text'))?></p>
			</div>
		<?php endif; ?>		
	<?php }

	///////////////////////////////////////////
	// The buttons
	//
	// "pagoda_button_data_label_" 	: "Button ", 
	// "pagoda_button_data_link_" 	: "#",		
	//
	private function html_generate_buttons(){ ?>

		<?php if ($this->settings['n_buttons'] < 1) {return;} ?>

		<?php if( $this->data['config']['show']['buttons'] ): ?>

			<div class="pagoda-header-buttons">
				<?php foreach($this->data['i']['buttons'] as $id => $button): ?>
				<a href="<?php echo $this->query->option($button,'_data_link_'); ?>" 
					class="ps2id pagoda-header-button pagoda-header-button-<?php echo $id ?>">
					<?php echo $this->query->option($button,'_data_label_'); ?>
				</a>
				<?php endforeach; ?>
			</div>

		<?php endif; ?>

	<?php }

	//////////////////////////////////////////////////////////////////////////////////////
	//									CSS METHODS 									//
	//////////////////////////////////////////////////////////////////////////////////////
	// This function will autogenerate the stylesheets based on our options defined in the 
	// WP database. All pagoda options are set by the customizer class. If the option is 
	// not set then we will render the CSS via values in the defaults table (JSON) ...  				
	private function write_css_header(){ 
		$css_autogen = new Nablafire_CSS_Autogen();
		
		$this->css = $css_autogen->comment('Pagoda Machine Generated CSS');
	
		// Background	
		if ( strcmp($this->data['config']['mode'], 'animated') == 0 ){
			$this->css .= $this->css_generate_bg_animated( $this->data, $css_autogen); 
		}

		if ( strcmp($this->data['config']['mode'], 'parallax') == 0 ){
			$this->css .= $this->css_generate_bg_parallax( $this->data, $css_autogen); 
		}

		if ( strcmp($this->data['config']['mode'], 'static')   == 0 ){
			$this->css .= $this->css_generate_bg_static( $this->data, $css_autogen); 
		}

		// Banner and buttons
		$this->css .= $this->css_generate_banner($this->data, $css_autogen);			
		if ($this->settings['n_buttons'] > 0) {
			$this->css .= $this->css_generate_buttons_u($this->data, $css_autogen);
			$this->css .= $this->css_generate_buttons_i($this->data, $css_autogen);
		}		

		update_option('pagoda_header_styles', serialize( $css_autogen->minify($this->css)));
		update_option('pagoda_header_fonts', serialize($this->font_enqueue));
	}


	//////////////////////////////////////////////////////////////////////////////////////
	//							BACKGROUND CSS AUTOGEN METHODS 							//
	//////////////////////////////////////////////////////////////////////////////////////
	
 	// This method applies to static backgrounds
	private function css_generate_bg_static($data, $_css){

		$css  = $_css->comment(" ------ Background Static ------ ");

		// Header wrap
		$css .= $_css->begin_rule('.pagoda-header-wrap');
			$css .= $_css->add_rule('background-color'	, $this->query->option($data['u']['display'], '_color'));
			$css .= $_css->add_rule('overflow'		, 'auto');	
		$css .= $_css->end_rule();
		
		// Static Background
		$css .= $_css->begin_rule('.pagoda-header-static');
			$css .= $_css->add_rule('height'	, $this->query->option($data['u']['display'], '_height'));
			$css .= $_css->add_rule('overflow'	, 'auto');	

			// Align all banner items to center of this div. 
			$css .= $_css->add_rule('display' 		, 'flex');
			$css .= $_css->add_rule('justify-content', 'center');
  			$css .= $_css->add_rule('align-items' 	, 'center'); 

			// Finally, take care of the background image
			$css .= $_css->add_background_image( $data['config']['img'] );
			$contain = $this->query->option($data['u']['display'], '_contain'); 
			if ( strcmp( $contain, "1" ) == 0 ){
				$css .= $_css->add_rule('background-size'	, '100% 100%');

			}
			else{
				$css .= $_css->add_rule('background-size'	, 'auto 100%');	
				$css .=	$_css->add_rule('background-position',
					$this->query->option($data['u']['display'], '_displace') . '%');	
			}
		$css .= $_css->end_rule();
		
		$css .= $this->header_mobile_rules( '.pagoda-header-static' , $_css );

		return $css;
	}

	// This function applies to parallax backgrounds
	private function css_generate_bg_parallax($data, $_css){

		$css = $_css->comment(" ------ Background Parallax ------ ");

		// Header wrap
		$css .= $_css->begin_rule('.pagoda-header-wrap');
			$css .= $_css->add_rule('background-color', $this->query->option($data['u']['display'], '_color'));
		$css .= $_css->end_rule();
		// Set up parallax effect 
		$css .= $_css->begin_rule('.pagoda-header-wrap [class*="-parallax"]');
			$css .= $_css->add_rule('position'		, 'relative');
			$css .= $_css->add_rule('min-height'	, $this->query->option($data['u']['display'], '_height'));
			$css .= $_css->add_rule('background-attachment'	, 'fixed');			
			$css .= $_css->add_rule('overflow'		, 'auto');	
		$css .= $_css->end_rule();

		// Finish parallax effect
		$css .= $_css->begin_rule('.pagoda-header-parallax');				
		
			$css .= $_css->add_background_image( $data['config']['img'] );
			$contain = $this->query->option($data['u']['display'], '_contain'); 
			if ( strcmp( $contain, "1" ) == 0 ){
				$css .= $_css->add_rule('background-size'	, '100% 100%');

			}
			else{
				$css .= $_css->add_rule('background-size'	, 'auto 100%');	
				$css .=	$_css->add_rule('background-position',
										$this->query->option($data['u']['display'], '_displace') . '%');	
			}
		
			// Align all banner items to center of this div. 
			$css .= $_css->add_rule('display' 		, 'flex');
			$css .= $_css->add_rule('justify-content', 'center');
  			$css .= $_css->add_rule('align-items' 	, 'center'); 	

		$css .= $_css->end_rule();

		$css .= $this->header_mobile_rules( '.pagoda-header-parallax' , $_css );

		return $css;
	}

	// This function applies to animated backgrounds
	private function css_generate_bg_animated($data, $_css){

		$css = $_css->comment(" ------ Background Animated ------ ");

		// Header wrap. We will make the background color transparent here to set up for animation. 
		$css .= $_css->begin_rule('.pagoda-header-wrap');
			$css .= $_css->add_rule('background-color'	, 'transparent');
			$css .= $_css->add_rule('overflow','auto');
		$css .= $_css->end_rule();
	
		$css .= $_css->begin_rule('.pagoda-header-animated');
			// Background color and height to transparent
			$css .= $_css->add_rule('background-color'	, 'transparent');
			$css .= $_css->add_rule('height', $this->query->option($data['u']['display'], '_height'));
			$css .= $_css->add_rule('width' , '100%');

			// Align all banner items to center of this div. 
			$css .= $_css->add_rule('display' 		, 'flex');
			$css .= $_css->add_rule('justify-content', 'center');
  			$css .= $_css->add_rule('align-items' 	, 'center'); 
		$css .= $_css->end_rule();

		// This is the div which contains the ul which we will animate. The ul itself contains 
		// absolutely positioned spans which we will place our background images in. This div
		// will be absolutely positioned on the page and is given a fixed height and width. It 
		// will also get the background color as opposed to .pagoda-header-wrap. The most 
		// important rule is the z-index:-1. This will push the pagoda-header-slideshow div 
		// layer behind our other elements (title, nav, buttons). Otherwise our animated 
		// spans will cover our other elements.   
		$css .= $_css->begin_rule('.pagoda-header-slideshow');
			$css .= $_css->add_rule('position'	, 'absolute');
			$css .= $_css->add_rule('top'		, '0');	
			$css .= $_css->add_rule('left'		, '0');
			$css .= $_css->add_rule('width'		, 'inherit');
			$css .= $_css->add_rule('height'	, $this->query->option($data['u']['display'], '_height'));
			$css .= $_css->add_rule('background-color', '#000');
			$css .= $_css->add_rule('z-index'	, '-1');
		$css .= $_css->end_rule();

		// This is the ul which will contain the animation. We will set list-style to none in 
		// order to hide the default bullet points. All we want to show are the <span> elements 
		// which contain the background images 
		$css .= $_css->begin_rule('.pagoda-header-slideshow > ul');
			$css .= $_css->add_rule('list-style', 'none');
		$css .= $_css->end_rule();

		// Set up animation properties for the spans.
		$timestep  = (int)$this->query->option($data['u']['display'], '_delta'); // (ms) per image
		$timecycle = (int)$timestep*$this->settings['n_images']; // Total time
        $animation = 'imageAnimation '.$timecycle.'ms linear infinite 0s'; //Animation string

		// Here are our spans. They will be absoultely positioned identically (i.e. they can be 
		// imagined as one on top of the other). The background in all our spans is set to cover
		// and will be centered vertically as well as horizontally. We will set to opacity to 
		// zero to start (so we should get the div's background color ... #000). Then the 
		// animation will set keyframs on the opacity to show and hide the images
		$css .= $_css->begin_rule('.pagoda-header-slideshow > ul > li > span');
		 	$css .= $_css->add_rule('position'	,'absolute' );
			$css .= $_css->add_rule('min-height', $this->query->option($data['u']['display'], '_height'));
		 	$css .= $_css->add_rule('width'		,'100%' );		 	
		 	$css .= $_css->add_rule('top'		,'0px');
		 	$css .= $_css->add_rule('left'		,'0px');

		 	// Finally, take care of the background image
			$contain = $this->query->option($data['u']['display'], '_contain'); 
			if ( strcmp( $contain, "1" ) == 0 ){
				$css .= $_css->add_rule('background-size'	, '100% 100%');

			}
			else{
				$css .= $_css->add_rule('background-size'	, 'auto 100%');	
				$css .=	$_css->add_rule('background-position',
										$this->query->option($data['u']['display'], '_displace') . '%');	
			}

		 	$css .=	$_css->add_rule('opacity'	,'0');

		 	// Brower specific rules to set animation property. 		 	
		 	$css .=	$_css->add_rule('-webkit-backface-visibility', 'hidden');
		 	$css .= $_css->browser_rules('animation', $animation);
		$css .= $_css->end_rule();
		
		$css .= $this->header_mobile_rules( '.pagoda-header-slideshow > ul > li > span', $_css );

		// Now ... we will loop through the spans and set their background image individually. We will 
		// also set the animation-delay property on each span. If the image timestep is 5s, then the 
		// animation delay will be 5s 10s 15s ... etc. for each span. 
		foreach ($this->data['i']['display'] as $_ => $options) {
		 	$css .= $_css->begin_rule('.pagoda-header-slideshow > ul > li:nth-child('.$_.') > span');
				$css .= $_css->add_background_image( $this->query->option($options, '_image') ); 
				if ($_ != 1):
					$timestring = $timestep*($_-1);
					$css .= $_css->browser_rules('animation-delay'	, $timestring.'ms');
				endif;
			$css .=	$_css->end_rule();	
		} 

		// Keyframes. Here we need to calculate the percentages based on the timestep and timecycle.
		// Note that 0% begins at animation-delay.
		$opacity = (int)$this->query->option($data['u']['display'], '_opacity' ) / 100 ;
		$keyframes  = array(
			'0%' => array('opacity : 0.0','animation-timing-function: ease-in'),
			ceil( (50*$timestep)/$timecycle).'%'=> 
				array('opacity : ' . $opacity, 'animation-timing-function: ease-out'),
			ceil((100*$timestep)/$timecycle).'%'=> array('opacity : ' . $opacity, ''),
			ceil((150*$timestep)/$timecycle).'%'=> array('opacity : 0.0', ''),
			'100%' => array('opacity : 0.0', ''),
		);
		$css .= $_css->browser_keyframes($keyframes, 'imageAnimation');

		return $css;
	}

	// Mobile header rules for tablet and phone 
	public function header_mobile_rules( $target, $_css ) {

		// Reposition backgrounds for mobile(phones)
		$css = $_css->begin_media('(max-width: 768px) and (max-height: 768px)');
 			
			$css .= $_css->begin_rule( $target );
				$css .= $_css->add_rule('height' , $this->query->option($this->data['u']['mobile'],  '_phone') );
				$css .= $_css->add_rule('background-size' 	, 'auto 100%');
				$css .= $_css->add_rule('background-position', '50%');
			$css .= $_css->end_rule();
		
 		$css .= $_css->end_media();

		// Reposition backgrounds for mobile(tablet)
		$css .= $_css->begin_media('(min-width: 768px) and (min-height: 980px)');
		
			$css .= $_css->begin_rule( $target );
				$css .= $_css->add_rule('height' , $this->query->option($this->data['u']['mobile'],  '_tablet') );
				$css .= $_css->add_rule('background-size' 	, '100% 100%');
				$css .= $_css->add_rule('background-position', '0%');
			$css .= $_css->end_rule();

		$css .= $_css->end_media();

		return $css;
	} 

	//////////////////////////////////////////////////////////////////////////////////////
	//							 BANNER CSS AUTOGEN METHODS 							//
	//////////////////////////////////////////////////////////////////////////////////////
	
	private function css_generate_banner($data, $_css){
		$css  = $_css->comment(" ------ Banner ------ ");

		// Header wrap
		$css .= $_css->begin_rule('.pagoda-header-banner-wrap');
			$css .= $_css->add_rule('width'			,'100%');
			$css .= $_css->add_rule('padding-top'	,'0'); 
			$css .= $_css->add_rule('margin'		,'0 auto');
			$css .= $_css->add_rule('overflow'		,'auto');
		$css .= $_css->end_rule();

		// Banner Image Container
		$css .= $_css->begin_rule('.pagoda-header-banner-img');
			$css .= $_css->add_rule('width'		,'100%');
			$css .= $_css->add_rule('margin'	,'0 auto');
			$css .= $_css->add_rule('float'		,'left'); // Push structure down from navbar
		$css .= $_css->end_rule();

		// Banner Image Inner Div 
		$css .= $_css->begin_rule('.pagoda-header-banner-img > div');
			$css .= $_css->add_background_image( 
				$this->query->option($data['u']['banner'],'_img_source') );
			$css .= $_css->add_rule('background-size'		, 'contain');
			$css .= $_css->add_rule('background-repeat'		, 'no-repeat');
			$css .= $_css->add_rule('background-position'	, 'center');
			$css .= $_css->add_rule('float' 				, 'left');
			$css .= $_css->add_rule('width'					, '100%');
			$css .= $_css->add_rule('height', $this->query->option($data['u']['banner'], '_img_height'));
		$css .= $_css->end_rule();		

		// Adjust image height for mobile
		$css .= $_css->begin_media('all and (max-width:768px)');
			$css .= $_css->begin_rule('.pagoda-header-banner-img > div');
				$css .= $_css->add_rule('height', 
					$this->query->option($data['u']['mobile'], '_m_img_height'));
			$css .= $_css->end_rule();
		$css .= $_css->end_media();	

		// Main title container
		$css .= $_css->begin_rule('.pagoda-header-banner-main');
			$css .= $_css->add_rule('width'		, '100%');
			$css .= $_css->add_rule('text-align', 'center');	
			$css .= $_css->add_rule('float'		, 'left');
			$css .= $_css->add_rule('margin'	, $this->query->option($data['u']['banner'], '_mn_margin'));
		$css .= $_css->end_rule();

		// Main title font enqueue
		$font_fam	= $this->query->option($data['u']['banner'], '_mn_font_fam');
		$font_var	= $this->query->option($data['u']['banner'], '_mn_font_var');
		array_push($this->font_enqueue, 
			array('font_fam' => $font_fam, 'font_var' => $font_var));

		// Main title
		$css .= $_css->begin_rule('.pagoda-header-banner-main > p');
			$css .= $_css->_literal($this->font_gen->css_fontfamily($font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($font_var));
			$css .= $_css->add_rule('font-size'	, $this->query->option($data['u']['banner'], '_mn_font_size', 'px'));
			$css .= $_css->add_rule('color'		, $this->query->option($data['u']['banner'], '_mn_font_color'));
			$css .= $_css->add_rule('margin'	, '0');
		$css .= $_css->end_rule();

		// Subtitle container
		$css .= $_css->begin_rule('.pagoda-header-banner-sub');
			$css .= $_css->add_rule('width'		,'100%');
			$css .= $_css->add_rule('text-align','center');
			$css .= $_css->add_rule('float'		,'left');
			$css .= $_css->add_rule('margin'	, $this->query->option($data['u']['banner'], '_sb_margin'));
		$css .= $_css->end_rule();

		// Subtitle font enqueue
		$font_fam	= $this->query->option($data['u']['banner'], '_sb_font_fam');
		$font_var	= $this->query->option($data['u']['banner'], '_sb_font_var');
		array_push($this->font_enqueue, 
			array('font_fam' => $font_fam, 'font_var' => $font_var));

		// Subtitle
		$css .= $_css->begin_rule('.pagoda-header-banner-sub > p');
			$css .= $_css->_literal($this->font_gen->css_fontfamily($font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($font_var));
			$css .= $_css->add_rule('font-size'	, $this->query->option($data['u']['banner'], '_sb_font_size', 'px'));
			$css .= $_css->add_rule('color'		, $this->query->option($data['u']['banner'], '_sb_font_color'));
			$css .= $_css->add_rule('margin'	, '0');
		$css .= $_css->end_rule();
		return $css;
	}

	//////////////////////////////////////////////////////////////////////////////////////////////
	//						    BUTTON CSS AUTOGEN METHODS (UNITERATED)							//
	//////////////////////////////////////////////////////////////////////////////////////////////
	
	private function css_generate_buttons_u($data, $_css){

		$css  = $_css->comment(" ------ Buttons Uniterated ------ ");

		// CSS for button container
		$css .= $_css->begin_rule('.pagoda-header-buttons');
			$css .= $_css->add_rule('text-align'	,'center');
			$css .= $_css->add_rule('width'			,'100%');
			$css .= $_css->add_rule('float'			,'left');
		$css .= $_css->end_rule();

		// Enqueue button font
		$font_fam = $this->query->option($data['u']['buttons'], '_font_fam');
		$font_var = $this->query->option($data['u']['buttons'], '_font_var');
		array_push($this->font_enqueue, 
			array('font_fam' => $font_fam, 'font_var' => $font_var));

		// Style the button
		$css .= $_css->begin_rule('.pagoda-header-button');
			$css .= $_css->add_rule('display'	, 'inline-block');
			$css .= $_css->add_rule('text-decoration', 'none');
			$css .= $_css->_literal($this->font_gen->css_fontfamily($font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($font_var));
			$css .= $_css->add_rule('font-size'	, $this->query->option($data['u']['buttons'], '_font_size', 'px'));
			$css .= $_css->add_rule('color'	,  $this->query->option($data['u']['buttons'], '_font_color'));
			$css .= $_css->add_rule('padding', $this->query->option($data['u']['buttons'], '_mn_padding'));
			$css .= $_css->add_rule('margin' , $this->query->option($data['u']['buttons'], '_mn_margin'));	
			
			if( $this->query->option( $data['u']['buttons'], '_show' ) ):
				$css .= $_css->add_rule('border-radius', $this->query->option($data['u']['buttons'], '_radius'));
				$_border = array(
					'fade'  => $this->query->option($data['u']['buttons'], '_fade'),
					'size'  => $this->query->option($data['u']['buttons'], '_size'), 
					'color' => $this->query->option($data['u']['buttons'], '_color'),
					'input_atts' => array(
						'mode' => $this->query->option($data['u']['buttons'], '_inset' ) ? 'inset' : 'default'
				) );
				$css .= $_css->border_properties($_border);
			endif;

			$css .= $_css->add_rule('line-height', $this->query->option($data['u']['buttons'], '_mn_height'));
			if ($this->query->option($data['u']['buttons'], '_width') != 0)
				$css .= $_css->add_rule('width', $this->query->option($data['u']['buttons'], '_mn_width'));
		$css .= $_css->end_rule();

		// Hover properties and transition
		$hover_speed = $this->query->option($data['u']['buttons'], '_mn_delta', 'ms');
		$css .= $_css->begin_rule('.pagoda-header-button:hover');
			$css .= $_css->add_rule('cursor', 'pointer');
			$css .= $_css->browser_rules('transition', 'background-color '.$hover_speed.' ease-out');
		$css .= $_css->end_rule();
		return $css;
	}

	//////////////////////////////////////////////////////////////////////////////////////////////
	//						    BUTTON CSS AUTOGEN METHODS (ITERATED)							//
	//////////////////////////////////////////////////////////////////////////////////////////////

	private function css_generate_buttons_i($data, $_css){

		$css  = $_css->comment(" ------ Buttons Iterated ------ ");

		foreach ($data['i']['buttons'] as $id => $button) {
			$css .= $_css->begin_rule('.pagoda-header-button-'.$id);
				$css .= $_css->add_rule('background-color',$this->query->option($button, '_bg_color_'));
			$css .= $_css->end_rule();

			$css .= $_css->begin_rule('.pagoda-header-button-'.$id.':hover');
				$css .= $_css->add_rule('background-color', $this->query->option($button, '_bg_hover'));
			$css .= $_css->end_rule();
		}
		return $css;
	}
}