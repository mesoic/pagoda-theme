<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class pagoda_footer
{
	
	function __construct($query, $font_gen)
	{
		$this->font_gen = $font_gen;  // The single instance of Nablafire_Fonts_Autogen()
		$this->query    = $query;     // The single instance of pagoda_query_option()
		$this->font_enqueue = array();	

		$this->initialize_footer(); // Initializes Query Option
		if ( user_can( wp_get_current_user() , 'administrator' ) ) {
			$this->write_css_footer();  // Initializes CSS Autogen 
		}
	}

	public function initialize_footer(){

		$this->data = array(
			'display' => $this->query->subtable( array('footer', 'display') ),
			'socials' => $this->query->subtable( array('footer', 'socials') ),
		);		
	}

	///////////////////////////////////////////
	// Render Footer
	//
	// "pagoda_footer_social_display" : "between",
	// "pagoda_footer_social_control" : "",
	//
	public function write_html_footer(){?>
		
		<div class="pagoda-footer-wrap">
			<?php $display = $this->query->option($this->data['socials'], '_display' ) ?>
			<?php $_master = $this->query->option($this->data['socials'], '_control' ) ?>
			<?php if ($display == 'above'): ?>
				<?php if($_master): ?>
					<?php $this->html_generate_social();?>
				<?php endif; ?>
				<?php $this->html_generate_footer_main();?>
				<?php $this->html_generate_footer_sub();?>
			<?php endif; ?>

			<?php if ($display == 'between'): ?>
				<?php $this->html_generate_footer_main();?>
				<?php if($_master): ?>
					<?php $this->html_generate_social();?>
				<?php endif; ?>
				<?php $this->html_generate_footer_sub();?>
			<?php endif; ?>
			
			<?php if ($display == 'below'): ?>
				<?php $this->html_generate_footer_main();?>
				<?php $this->html_generate_footer_sub();?>
				<?php if($_master): ?>
					<?php $this->html_generate_social();?>
				<?php endif; ?>
			<?php endif; ?>
		</div>			
	<?php }

	///////////////////////////////////////////
	// Render Footer Text
	//
	// "pagoda_footer_tl_text" :
	// "pagoda_footer_sb_text" :
	//
	public function html_generate_footer_main(){ ?>
		<div class="pagoda-footer-main">
			<p><?php echo esc_html($this->query->option($this->data['display'], '_tl_text'))?></p>
		</div>	
	<?php }

	public function html_generate_footer_sub(){ ?>
		<div class="pagoda-footer-sub">
			<p><?php echo esc_html($this->query->option($this->data['display'], '_sb_text'))?></p>
		</div>	
	<?php }

	///////////////////////////////////////////
	// Render Social Icons
	// 
	// "pagoda_footer_socials_array_show_" : 	
	// "pagoda_footer_socials_array_link_" : 
	//
	public function html_generate_social(){ ?>

		<div class="pagoda-footer-social">		
			<div class="pagoda-footer-social-container">
			<?php foreach ($this->data['socials']['data'] as $id => $data): ?>				
				<?php $icon_show = get_option('pagoda_footer_socials_array_show_'.$data['label']) ?>   
				<?php $icon_show = $icon_show ? $icon_show : $data['show'] // Only one DB query ?>
				<?php if($icon_show): ?>  			   	
					<?php $icon_link = get_option('pagoda_footer_socials_array_link_'.$data['label']) ?>   
					<?php $icon_link = $icon_link ? $icon_link : $data['link'] // Only one DB query ?>
					<div class="pagoda-footer-social-icon">
						<a href="<?php echo $icon_link?>">
							<i class="fa <?php echo $data['icon']?>" aria-hidden="true"></i>
						</a>
					</div>		
				<?php endif; ?>	
			<?php endforeach ?>
			</div>
		</div>	
	<?php }

	//////////////////////////////////////////////////////////////////////////////////////////////
	//									 CSS  METHODS 											//
	//////////////////////////////////////////////////////////////////////////////////////////////
	// This function will autogenerate the stylesheets for all buttons based on our options 
	// defined in the WP database. These are set by the customizer class. If the option is 
	// not set then we will render the CSS via values in the defaults table ...  				
	public function write_css_footer(){ 
		$css_autogen = new Nablafire_CSS_Autogen();
			
		$this->css  = $css_autogen->comment('Pagoda Footer Machine Generated CSS');
		$this->css .= $this->css_generate_footer($this->data, $css_autogen);
		$this->css .= $this->css_generate_social($this->data, $css_autogen);
	
		update_option('pagoda_footer_styles', serialize( $css_autogen->minify($this->css)));
		update_option('pagoda_footer_fonts' , serialize( $this->font_enqueue));
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//							 FOOTER CSS AUTOGEN METHODS 							//
	//////////////////////////////////////////////////////////////////////////////////////

	public function css_generate_footer($data, $_css){
		
		$css  = $_css->comment(" ------ Footer ------ ");

		// Footer div and background CSS
		$css .= $_css->begin_rule('.pagoda-footer-wrap');
			$css .= $_css->add_rule('overflow'		  , 'auto');
			$css .= $_css->add_rule('background-color', $this->query->option( $data['display'], '_bg_color'));
			if ($this->query->option($data['display'] , '_bg_image') != ''){	
				$css .= $_css->add_background_image( $this->query->option($data['display'], '_bg_image'));
				$css .= $_css->add_rule('background-size' , 'cover');
			}
		$css .= $_css->end_rule();

		// Main title row container
		$css .= $_css->begin_rule('.pagoda-footer-main');
			$css .= $_css->add_rule('width'		, '100%');
			$css .= $_css->add_rule('text-align', 'center');
			$css .= $_css->add_rule('float'		, 'left');
			$css .= $_css->add_rule('margin'	, '0');
			$css .= $_css->add_rule('padding'	, 
				$this->query->option($data['display'], '_tl_padding'));
		$css .= $_css->end_rule();

		// Main title font enqueue
		$font_fam	= $this->query->option($data['display'], '_tf_font_fam');
		$font_var	= $this->query->option($data['display'], '_tf_font_var');
		array_push($this->font_enqueue, 
			array('font_fam' => $font_fam, 'font_var' => $font_var));

		// Main title display
		$css .= $_css->begin_rule('.pagoda-footer-main > p');
			$css .= $_css->_literal($this->font_gen->css_fontfamily($font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_tf_font_size', 'px'));
			$css .= $_css->add_rule('color'		, 
				$this->query->option($data['display'], '_tf_font_color'));
			$css .= $_css->add_rule('margin' 	, '0');
		$css .= $_css->end_rule();

		// Subtitle row container
		$css .= $_css->begin_rule('.pagoda-footer-sub');
			$css .= $_css->add_rule('width'		, '100%');
			$css .= $_css->add_rule('text-align', 'center');
			$css .= $_css->add_rule('float'		, 'left');
			$css .= $_css->add_rule('margin'	, '0');
			$css .= $_css->add_rule('padding'	, 
				$this->query->option($data['display'], '_sb_padding'));
		$css .= $_css->end_rule();

		// Subtitle font enqueue 
		$font_fam	= $this->query->option($data['display'], '_sf_font_fam');
		$font_var	= $this->query->option($data['display'], '_sf_font_var');
		array_push($this->font_enqueue, 
			array('font_fam' => $font_fam, 'font_var' => $font_var));

		// Subtitle display
		$css .= $_css->begin_rule('.pagoda-footer-sub > p');
			$css .= $_css->_literal($this->font_gen->css_fontfamily($font_fam));
			$css .= $_css->_literal($this->font_gen->css_fontstyle($font_var));
			$css .= $_css->add_rule('font-size'	, 
				$this->query->option($data['display'], '_sf_font_size', 'px'));
			$css .= $_css->add_rule('color'		, 
				$this->query->option($data['display'], '_sf_font_color'));
			$css .= $_css->add_rule('margin' 	, '0');
		$css .= $_css->end_rule();
		return $css;
	}	

	//////////////////////////////////////////////////////////////////////////////////////
	//						 SOCIAL CSS AUTOGEN METHODS 								//
	//////////////////////////////////////////////////////////////////////////////////////

	public function css_generate_social($data, $_css){
		$css  = $_css->comment(" ------ Socials ------ ");

		// Social icon row
		$css .= $_css->begin_rule('.pagoda-footer-social');
			$css .= $_css->add_rule('width'		, '100%');
			$css .= $_css->add_rule('height'	, $this->query->option( $data['socials'], '_block_size'));
			$css .= $_css->add_rule('float'		, 'left');
			$css .= $_css->add_rule('display'	, 'block');
			$css .= $_css->add_rule('text-align', 'center');

			$css .= $_css->add_rule('padding'	, $this->query->option( $data['socials'], '_padding'));
		$css .= $_css->end_rule();

		// Social icon container block
		$css .= $_css->begin_rule('.pagoda-footer-social-container');
			$css .= $_css->add_rule('margin'	, '0 auto');
			$css .= $_css->add_rule('display'	, 'inline-block');
		$css .= $_css->end_rule();

		// Individual icon containers
		$css .= $_css->begin_rule('.pagoda-footer-social-icon');
			$css .= $_css->add_rule('width'		 , $this->query->option( $data['socials'], '_block_size'));
			$css .= $_css->add_rule('height'	 , $this->query->option( $data['socials'], '_block_size'));
			$css .= $_css->add_rule('line-height', $this->query->option( $data['socials'], '_block_size'));
			$css .= $_css->add_rule('float'		 , 'left');
			$css .= $_css->add_rule('text-align' , 'center');
		$css .= $_css->end_rule();

		// Font awesome styling
		$css .= $_css->begin_rule('.pagoda-footer-social-icon > a');
			$css .= $_css->add_rule('color'		, $this->query->option( $data['socials'], '_color'));
			$css .= $_css->add_rule('text-decoration', 'none');
			$css .= $_css->add_rule('font-size'	, $this->query->option( $data['socials'], '_font_size'));
			$css .= $_css->add_rule('display', 'block'); // so it fills the div
		$css .= $_css->end_rule();

		// Font awesome:hover styling
		$css .= $_css->begin_rule('.pagoda-footer-social-icon:hover > a');
			$css .= $_css->add_rule('color'		, $this->query->option( $data['socials'], '_hover'));
			$css .= $_css->browser_rules('transition'	, 'all 250ms ease-in-out');
		$css .= $_css->end_rule();

		return $css;
	}	
}