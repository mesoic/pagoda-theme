<?php
/**
 *	Template name: No Sidebars
 *	Template Post Type: post, page
 *	The template for displaying Custom Page/Post Template: Without Sidebars.
 *
 *	@package WordPress
 *	@subpackage pagoda
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php get_header(); ?>

<?php 
	if ( get_post_type() == "post"){
		$ID = instantiate_theme()->posts->get_post_class( get_the_ID() ); 
	}
	else {
		$ID = instantiate_theme()->pages->get_page_class( get_the_ID() );
	}
?>

<div id="primary" class="content-area content-area-<?php echo esc_html($ID) ?>">
			
	<main id="main" class="site-main" itemscope itemtype="http://schema.org/WebPageElement" itemprop="mainContentOfPage">

		<div class="pagoda-<?php echo get_post_type();?>-wrap pagoda-<?php echo get_post_type();?>-wrap-<?php echo esc_html($ID) ?>">

			<div class="pagoda-<?php echo get_post_type();?>-content-area">

				<div class="pagoda-<?php echo get_post_type();?>-content-wrap pagoda-<?php echo get_post_type();?>-content-wrap-<?php echo esc_html($ID) ?>">

				<?php 
					while ( have_posts() ) : 
	
						the_post();
			
						if( get_post_type() == "post" ) {
							get_template_part( 'template-parts/content', 'post' );
						} 
						else {
							get_template_part( 'template-parts/content', 'page' );
						}

					endwhile;
				?>
				</div>
		
			</div>
		
		</div>

	</main>
	
</div>

<?php get_footer(); ?>