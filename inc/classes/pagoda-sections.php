<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class pagoda_sections{

	public function __construct($settings, $query) {

		// Cache the theme defaults. Note that the defaults are generated by 
		// nabla-pagoda-customizer.php.
		$this->settings = $settings;  // From settings page	
		$this->query    = $query;     // The single instance of Nablafire_Query_Option()
	
		$this->initialize_sections();  
		if ( user_can( wp_get_current_user() , 'administrator' ) ) {
			$this->write_css_sections();  
		}
		// Register all plugin sidebars. These will be wigitized areas
		add_action( 'widgets_init',  array( $this, 'register_sidebars' ) );

		// Add width selector to widgets. 
		add_action( 'in_widget_form', array( $this, 'generate_widget_layout' ), 4, 3 );

		// When widget_update_callbacks are called, nabla_in_widget_form_update will also be 
		// called. This will add our width to the corresponding widget options table in the db. 
		add_filter( 'widget_update_callback', array( $this, 'update_widget_layout' ), 4, 3 );

		// Inject width styling into <div class="nabla-pagoda-widget" --HERE-- >. Note that this div 
		// wraps all widgets and is prepared in register sidebar. When dynamic_sidebar is called, 
		// this filter calls nabla_dynamic_sidebar_params. Priority is set to 5 to ensure that this
		// filter is added first ... This way we will not conflict with other plugins ...
        add_filter( 'dynamic_sidebar_params', array( $this, 'dynamic_sidebar_layout' ), 5 );
	}
		
	public function initialize_sections(){

		// Cast settings as range arrays and process selectors
		$n_sections = range(1, $this->settings['n_sections']);	

		// Expand all section data
		$this->data = array(
			'config'  => $this->process_selectors($n_sections),
			'mobile'  => $this->query->subtable( array('mobile'  , 'sections'), $n_sections ),
			'display' => $this->query->subtable( array('sections', 'display' ), $n_sections ),
		);
	}

	public function process_selectors($n_sections){
		
		// Extract Data
		$config_i = $this->query->subtable(array('sections', 'config'), $n_sections);
	
		// Prepare Array
		$config = array();
		foreach ($config_i as $id => $section_config) {
			$config[$id] = array(
				"id"		=> absint($id),
				"show"		=> $this->query->option($section_config, '_show_'), 
				"html_id"	=> ltrim( $this->query->option($section_config, '_html_id_' ) , '#'), 
				"priority"  => absint( $this->query->option($section_config, '_priority_') )
			);
		}

		// multisort on priority sub-array 
		$priority = array();
		foreach ($config as $id => $section_config) {
		    $priority[$id] = $section_config['priority'];
		}

		// Note: the order of items in $config is be importnat 
		// regarding how items of the same priority are sorted. 
		array_multisort($priority, SORT_ASC, $config);
		return $config;
	}


	//////////////////////////////////////////////////////////////////////////////////////
	//								 	HTML METHODS									//
	//////////////////////////////////////////////////////////////////////////////////////
	// This function will be called from the header.php template file. Because we want this to be 
	// called without arguments, all options will be stored as class data members.
	public function write_html_sections(){ 
		
		if ($this->settings['n_sections'] < 1){ 
			return; 
		} 
		else {
			foreach ($this->data['config'] as $id => $section) {
				if ( $section['show'] ){ $this->html_generate_frame($section); }			
			}
		}
	}
	public function html_generate_frame($section){?>

		<div class="pagoda-section-<?php echo esc_html( $section['id'] )?>"
			 id="<?php echo esc_html( $section['html_id'] )?>">
				<div class="pagoda-section-frame-<?php echo esc_html($section['id'])?>">
					<?php if(is_active_sidebar('pagoda-section-' . $section['id']) ): ?>
						<?php dynamic_sidebar( 'pagoda-section-' . $section['id'] ); ?>
					<?php endif; ?>
				</div>	
		</div>
	
	<?php }

	//////////////////////////////////////////////////////////////////////////////////////
	//								 	CSS METHODS										//
	//////////////////////////////////////////////////////////////////////////////////////
	public function write_css_sections(){

		$css_autogen = new Nablafire_CSS_Autogen();

		$this->css = $css_autogen->comment('Pagoda Sections Machine Generated CSS');

		if ($this->settings['n_sections'] > 0) {
			$this->css .= $this->css_generate_sections($this->data, $css_autogen);	
		}

		update_option('pagoda_sections_styles', serialize( $css_autogen->minify($this->css) ));
	}

	//////////////////////////////////////////////////////////////////////////////////////
	//								  SECTIONS AUTOGEN 									//
	//////////////////////////////////////////////////////////////////////////////////////

	public function css_generate_sections($data, $_css){
		
		$css    = '';
		foreach ($data['display'] as $id => $section) {
		 	
			// Set up some basic CSS (remove all margins). 
			$zero_margins = array(
				'[class*="pagoda-section"] h1',
				'[class*="pagoda-section"] h2',
				'[class*="pagoda-section"] h3',
				'[class*="pagoda-section"] h4',
				'[class*="pagoda-section"] h5',
				'[class*="pagoda-section"] h6',
				'[class*="pagoda-section"] ul',
			);
			$css .= $_css->begin_array_rule($zero_margins);
				$css .= $_css->add_rule('margin', '0');
			$css .= $_css->end_rule();


			$height = $this->query->option($section, '_height_');
			$img    = $this->query->option($section, '_img_');
			// We must have control flow for parallax versus static. We will only
			// generate parallax CSS if an image has been selected.

			// Section wrap
			$css .= $_css->begin_rule('.pagoda-section-'.$id);
				$css .= $_css->add_rule('background-color', $this->query->option($section, '_color_'));		
				$css .= ( $height != 0 ? $_css->add_rule('height' , $height) : '');
				$css .= $_css->add_rule('max-width'	 , '100%');
				$css .= $_css->add_rule('overflow-x' , 'hidden');
				$css .= $_css->add_rule('margin' 	 , '0');
			$css .= $_css->end_rule();	
			
			if ($this->query->option($section, '_parallax_') && ($img != '')){

				// Set up parallax effect 
				$css .= $_css->begin_rule('.pagoda-section-' . $id . ' [class*="-frame-' . $id . '"]');
					$css .= $_css->add_rule('position'				, 'relative');
					$css .= $_css->add_rule('background-attachment'	, 'fixed');
					$css .= $_css->add_rule('background-position'	, 'top center');
				$css .= $_css->end_rule();
				
				// Finish parallax effect
				$css .= $_css->begin_rule('.pagoda-section-frame-' . $id);
					$css .= $_css->add_background_image( $img );

					$contain = $this->query->option($section, '_contain'); 
					if ( strcmp( $contain, "1" ) == 0 ){
						$css .= $_css->add_rule('background-size'	, '100% 100%');
					}
					else{
						$css .= $_css->add_rule('background-size'	, 'auto 100%');	
						$css .=	$_css->add_rule('background-position',
									$this->query->option($section, '_displace') . '%');	
					}

					$css .= $_css->add_rule('padding'	, $this->query->option($section, '_padding_'));
					$css .= $_css->add_rule('box-sizing', 'border-box');
					$css .= $_css->add_rule('height'	, 'inherit');
					$css .= $_css->add_rule('width'		, 'inherit');
				$css .= $_css->end_rule();
			}

			else{		

				// Only write image CSS if there is an image
				$css .= $_css->begin_rule('.pagoda-section-frame-'.$id);	
					$css .= $_css->add_rule('padding'	, $this->query->option($section, '_padding_'));
					$css .= $_css->add_rule('box-sizing', 'border-box');
					$css .= $_css->add_rule('height'	, 'inherit');
					$css .= $_css->add_rule('width'		, 'inherit');
					
					if ($img != ''){
						$css .= $_css->add_background_image( $img );						
						$contain = $this->query->option($section, '_contain'); 
						if ( strcmp( $contain, "1" ) == 0 ){
							$css .= $_css->add_rule('background-size'	, '100% 100%');
						}
						else{
							$css .= $_css->add_rule('background-size'	, 'auto 100%');	
							$css .=	$_css->add_rule('background-position',
										$this->query->option($section, '_displace') . '%');	
						}
					}

				$css .= $_css->end_rule();	
			}

			/////////////////////////////////////////////////////////////
			// Responsive Rules 
			//				
			// 1) Reposition background for mobile(phones)
			$css .= $_css->begin_media('(max-width: 768px) and (max-height: 768px)');				
				$css .= $_css->begin_rule( '.pagoda-section-frame-' . $id );
					$css .= $_css->add_rule('background-size' 	, 'auto 100%');
					$css .= $_css->add_rule('background-position', '50%');
				$css .= $_css->end_rule();
			$css .= $_css->end_media();

			// 2) Reposition background for mobile(tablet)
			$css .= $_css->begin_media('(min-width: 768px) and (min-height: 980px)');
				$css .= $_css->begin_rule( '.pagoda-section-frame-' . $id );
					$css .= $_css->add_rule('height'			, 'auto');
					$css .= $_css->add_rule('background-size' 	, '100% 100%');
					$css .= $_css->add_rule('background-position', '0%');
				$css .= $_css->end_rule();			
			$css .= $_css->end_media();
		}

		// Section mobile display rules
		$css .= $_css->comment(" ------ Sections Mobile Display ------ ");

		foreach ($data['mobile'] as $id => $mobile) {

			$css .= $_css->begin_media('all and (max-width:980px)');
			$show_id = $this->query->option($mobile, '_m_show');
			if ( strcmp($show_id, "") == 0 ){
				$css .= $_css->begin_rule('.pagoda-section-'.$id); 
					$css .= $_css->add_rule('display'	, 'none');
				$css .= $_css->end_rule();
			}
			$css .= $_css->end_media();

		}

		// Append widget mobile display rules
		$css .= $_css->comment(" ------ Widgets Mobile Display ------ ");

		$css .= $_css->begin_media('all and (max-width:980px)');
			$css .= $_css->begin_rule('.widget-mobile-none');
				$css .= $_css->add_rule('display', 'none');
			$css .= $_css->end_rule();			
		$css .= $_css->end_media();
		return $css;
	}

	//////////////////////////////////////////////////////////////////////////////////////////////
	//								  GENERATE WIGETIZED AREAS 									//
	//////////////////////////////////////////////////////////////////////////////////////////////
	public function register_sidebars() {
		// Register sidebar for each id
 		for ($id = 1; $id<=$this->settings['n_sections']; $id++){
			
			// Register Sidebar for Each Section
			register_sidebar( 
				array(
					'name'          => esc_html__( 'Pagoda Section', 'pagoda' ) . ' ' . $id,
					'id'            => "pagoda-section-$id",
					'description'   => esc_html__( 'save new widgets to activate layout options', 
						'pagoda'),
					'before_widget' => '<div id="%1$s" class="pagoda-section-widget %2$s">',
					'after_widget'  => '</div>',
					'before_title'  => '<h2><span>',
					'after_title'   => '</span></h2>',
				) 
			);
		}
	}

	//////////////////////////////////////////////////////////////////////////////////////////////
	//									WIDGET FORM METHODS 									//
	//////////////////////////////////////////////////////////////////////////////////////////////
	// 
	// This method will inject a 'Pagoda Grid Layout' section into every widget form. This will 
	// be connected to two options that will control the widget width and format respectively. 
	// The format parameter will determine the behaviour of the div injector which will block 
	// off our widgets into rows.  
	//
	public function generate_widget_layout( $widget, $return, $instance ){ 

		// First we need to find the sidebar associated with the widget. We can 
		// achieve this via a call to wp_get_sidebars_widgets(). Note we need 
		// the !== false here because array_search returns the key which holds 
		// widget->id. If the widget's key == 0 then if(array_search()) will 
		// give an ambiguous result
		$sidebars = wp_get_sidebars_widgets();
		foreach ($sidebars as $sidebar_id => $sidebar) {
			if (array_search( $widget->id, $sidebar ) !== false ){
				break;
			}
		}

		// If we are NOT in a section, then we do not render these in_widget_forms. 
		// Note that we will render the grid layout 
	   	if ( strpos($sidebar_id, "section") == false ) { 
	   		return array( $widget, $return, $instance ); 
	   	}

		// Add a format parameter to widget's db options.
		$instance = wp_parse_args( (array) $instance, array( 'widget_format' => '') );
	    if ( !isset( $instance['widget_format'] ) ) $instance['widget_format'] = null; 

   		// Add a width parameter to widget's db options.
    	$instance = wp_parse_args( (array) $instance, array( 'widget_width' => '') );
	    if ( !isset( $instance['widget_width'] ) ) $instance['widget_width'] = null; 

		$instance = wp_parse_args( (array) $instance, array( 'widget_mobile' => '') );
	    if ( !isset( $instance['widget_mobile'] ) ) $instance['widget_mobile'] = ''; 

	    // A list of formats and widths to choose from ... see below
	    $formats = $this->get_widget_formats();
	    $widths  = $this->get_widget_widths();
		?>

		<?php // Render the backend html. We will also enqueue a stylesheet to style the in widget form
			  // field, and a js functino which will toggle the settings. ?> 
		<div class="pagoda-admin-widget-fields">
		<h3 class="pagoda-admin-widget-toggle pagoda-admin-widget-toggle-1">
			<?php _e( 'Pagoda Grid Layout', 'pagoda' ); ?></h3>
    	<div class="pagoda-admin-widget-field pagoda-admin-widget-field-1" style="display: none;">
		
			<!--  The Format -->
			<p> 
			<label for="<?php echo $widget->get_field_id('widget_format'); ?>">
				<?php _e( 'Row Formatting', 'pagoda' ); ?>
			</label>

			<select class="widefat" 
					id="<?php echo $widget->get_field_id('widget_format'); ?>" 
					name="<?php echo $widget->get_field_name('widget_format'); ?>">

					<?php foreach( $formats as $key => $value ) { ?>
                        <option <?php selected( $instance['widget_format'], $key ); ?>value="<?php echo $key; ?>">
                        	<?php echo $value; ?>    	
                        </option>
                    <?php } ?>
	        </select>
            <span><em><?php _e( 'Format this widget', 'pagoda'  ); ?></em></span>	
			</p>

       		<!--  The Width -->
       		<p> 
			<label for="<?php echo $widget->get_field_id('widget_width'); ?>">
				<?php _e( 'Widget Width','pagoda' ); ?>
			</label>

			<select class="widefat" 
					id="<?php echo $widget->get_field_id('widget_width'); ?>" 
					name="<?php echo $widget->get_field_name('widget_width'); ?>">
                        
					 <?php foreach( $widths as $key => $value ) { ?>
                        <option <?php selected( $instance['widget_width'], $key ); ?>value="<?php echo $key; ?>">
                        	<?php echo $value; ?>    	
                        </option>
                    <?php } ?>
	        </select>
            <span><em><?php _e( 'Set the widget width', 'pagoda' ); ?></em></span>	
			</p>
		</div>
		</div>

		<div class="pagoda-admin-widget-fields">
		<h3 class="pagoda-admin-widget-toggle pagoda-admin-widget-toggle-2">
			<?php _e( 'Show on Mobile', 'pagoda' ); ?></h3>
    	<div class="pagoda-admin-widget-field pagoda-admin-widget-field-2" style="display: none;">

    		<p> 
    		<label for="<?php echo $widget->get_field_id('widget_mobile'); ?>">
				<?php _e( 'Show this widget on mobile?', 'pagoda' ); ?>
			</label>
    		</p>
    		<input class="checkbox" 
                   id="<?php echo $widget->get_field_id( 'widget_mobile' ); ?>"
                   name="<?php echo $widget->get_field_name( 'widget_mobile' ); ?>" 
                   type="checkbox" <?php checked( $instance[ 'widget_mobile' ], 'on' ); ?> />

            <p>Note that all widgets are displayed at 100% width on mobile platforms.</p>       
    	</div>
		</div>
		<?php $return = null;
        return array( $widget, $return, $instance );
	}
	    
	// A callback which we access when we return from the above function. This will update our new
	// parameters for the the widget in the db. 
    public function update_widget_layout($instance, $new_instance, $old_instance ) {
     	$instance['widget_format'] = $new_instance['widget_format'];
	 	$instance['widget_width']  = $new_instance['widget_width'];
		$instance['widget_mobile'] = $new_instance['widget_mobile'];
	 	return $instance;
    }

    public function get_widget_formats(){

    	$formats = array(
    		'1' => __( 'Full Width Widget' 	, 'pagoda' ),
	        '2' => __( 'Begin Widget Row' 	, 'pagoda' ),
    	    '3' => __( 'Widget in Row' 		, 'pagoda' ),
    		'4' => __( 'End Widget Row' 	, 'pagoda' ),            		
    	);
        return $formats;
    }

    public function get_widget_widths(){

    	$formats = array(
 			'widget-100-percent' => __( '100%' , 'pagoda' ),
			'widget-20-percent'  => __( '20%'  , 'pagoda' ),
			'widget-25-percent'  => __( '25%'  , 'pagoda' ),
			'widget-30-percent'  => __( '30%'  , 'pagoda' ),
			'widget-33-percent'  => __( '33%'  , 'pagoda' ),
		 	'widget-40-percent'  => __( '40%'  , 'pagoda' ),
			'widget-50-percent'  => __( '50%'  , 'pagoda' ),
			'widget-60-percent'  => __( '60%'  , 'pagoda' ),
			'widget-66-percent'  => __( '66%'  , 'pagoda' ),
			'widget-70-percent'  => __( '70%'  , 'pagoda' ),
			'widget-75-percent'  => __( '75%'  , 'pagoda' ),
			'widget-80-percent'  => __( '80%'  , 'pagoda' ),
			'widget-90-percent'  => __( '90%'  , 'pagoda' ),
    	);
      	return $formats;
    }


	//////////////////////////////////////////////////////////////////////////////////////////////
	//									DIV INJECTOR METHOD 									//
	//////////////////////////////////////////////////////////////////////////////////////////////
	//
	// This method will modify the dynamic sidebar params which are set in by register_sidebars().
	// In particular, we are intrested in the 'before_widget' and 'after_widget' parameters. In 
	// this method we want to accomplish two things:
	//
	//	1) Add an additional class which we can style to set the widget width 
	//  2) Wrap widget groups in <div> tags in order to create rows (i.e. grid layout)
	//
	// Our primary tool to accomplish this task will be to use preg_replace on the 'before_widget'
	// and 'after_widget' parameters. This method will be called by calling 'add_filter' on the 
	// wp 'dynamic_sidebar_params' hook. This will allow us to update the sidebar_params every 
	// time a widget is rendered. 
	//  
    // This will transform the default behaviour for 'dynamic_sidebar':  
	//	<div class = "widget">
	//	<div class = "widget">
	//	<div class = "widget">
	//
	// Into the following behaviour for 'dynamic_sidebar':	
    //
	// <div class = "widget-row">
	//		<div class = "widget widget-30-percent">
	//		<div class = "widget widget-40-percent">
	//		<div class = "widget widget-30-percent">
	// </div> 
	//
    public function dynamic_sidebar_layout( $params ) {
		
		// First we must check if we are in a 'section' sidebar. All IDs for section sidebars
		// contain the string 'section'. Also we must return params - we cannot simply return.
		// Note that the 'in_widget_form' function must contain a similar statement ...  
    	if (strpos($params[0]['id'], "section") == false) { return $params; }

    	// If we are in a section, then we can begin to alter the widget form ...
		global $wp_registered_widgets;

        $widget_id  = $params[0]['widget_id'];
        $widget_obj = $wp_registered_widgets[$widget_id];
        $widget_opt = get_option( $widget_obj['callback'][0]->option_name );
        $widget_num = $widget_obj['params'][0]['number'];
        //////////////////////////////////////////////////////////////////////////////////////////////
        // Step 1: The width injector
        //
        // Note that we need to do this first in order for the resulting HTML to come out ok after 
        // regex replace. This will add a class attribue to each widget which we can then write some 
        // corresponding CSS for. Note the before and after states (a and b respectively)
        //
        // a) 'before_widget' : <div class="pagoda-section-widget">
        // b) 'before_widget' : <div class="pagoda-section-widget widget-**-percent">
        //
        if(isset( $widget_opt[$widget_num]['widget_width']) && !empty( $widget_opt[$widget_num]['widget_width']))
        {
        	$_before = ' '. $widget_opt[$widget_num]['widget_width'] .'"'.' >';
    		$params[0]['before_widget'] = preg_replace( '/"\\s*>/', $_before, $params[0]['before_widget'], 1 );
        }

        //////////////////////////////////////////////////////////////////////////////////////////////
        // Step 2: The widget-mobile-none injector
        //
       	// Our strategy for this is to parse the checkbox value and to add an additional class to the 
       	// widget if it is NOT checked. We will write a single @media CSS rule to target all widgets 
       	// with this additional class to display : 'none'; PHP wise, this is identical to what occurs 
       	// above. Note that hide on mobile (unchecked box) is the default behaviour. To show on mobile 
       	// we check the box and thus we do not write the additional class 'widget-mobile-none'. 
		//
		// a) 'before_widget' : <div class="pagoda-section-widget widget-**-percent">	
		// b) 'before_widget' : <div class="pagoda-section-widget widget-**-percent widget-mobile-none">
		//
        if ( !isset($widget_opt[$widget_num]['widget_mobile']) )
        {
        	$_before = ' '. 'widget-mobile-none' .'"'.' >';
			$params[0]['before_widget'] = preg_replace( '/"\\s*>/', $_before, $params[0]['before_widget'], 1 );
        }

        //////////////////////////////////////////////////////////////////////////////////////////////
        // Step 2:  The div injector.
    	//
    	// This will wrap the widget groups in <div class="widget-row"></div> according to what 
    	// is set on the backend widget forms (within each section). Note the before and after 
    	// states (a and b respectively)
    	//
        // a) 'before_widget' : <div class="section-widget widget-NN-percent">
        // b) 'before_widget' : <div class="widget-row"><div class="section-widget widget-NN-percent">
        //
        // a) 'after_widget'  : </div>
        // b) 'after_widget'  : </div></div>
        //
        if(isset( $widget_opt[$widget_num]['widget_format']) && !empty( $widget_opt[$widget_num]['widget_format'])){
        	$key = $widget_opt[$widget_num]['widget_format'] ;

        	if ($key == "1"){
        		$_before = '<div class="pagoda-widget-row-wrap"><div class="pagoda-widget-row"><';
        		$_after  = '></div></div>';
		   		$params[0]['before_widget'] = preg_replace( '/</', $_before, $params[0]['before_widget'], 1 );
    			$params[0]['after_widget'] = preg_replace( '/>/', $_after, $params[0]['after_widget'], 1 );
        	}
       		if ($key == "2"){
           		$_before = '<div class="pagoda-widget-row-wrap"><div class="pagoda-widget-row"><';
       			$params[0]['before_widget'] = preg_replace( '/</', $_before, $params[0]['before_widget'], 1 );
           	}
           	if ($key == "4"){
           		$_after  = '></div></div>';
    			$params[0]['after_widget'] = preg_replace( '/>/', $_after, $params[0]['after_widget'], 1 );
           	}
        }

	return $params;
    }

} // End Class
