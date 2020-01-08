<?php
/**
 * The template for displaying search forms
 *
 *  @package    WordPress
 *  @subpackage pagoda
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div class="pagoda-search-wrap">
    
    <form role="search" 
            method="get" 
            class="search-form pagoda-search-form" 
            action="<?php echo home_url( '/' ); ?>">
        
        <div class="pagoda-search-field-wrap">

            <input type="search" 
                class="search-field pagoda-search-field"
                placeholder="<?php echo esc_attr_x( 'Search ...', 'placeholder' , 'pagoda' ) ?>"
                value="<?php echo get_search_query() ?>" 
                name="s"
                title="<?php echo esc_attr_x( 'Search for:', 'label' , 'pagoda' ) ?>" />
        </div>
        
        <div class="pagoda-search-submit-wrap">

        	<input type="submit" 
        			class="search-submit pagoda-search-submit"
    	    	    value="<?php echo esc_attr_x( 'Search', 'submit button' , 'pagoda' ) ?>" />
    	</div>
    
    </form>

</div>