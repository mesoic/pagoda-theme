<?php
/**
  * The template for displaying pages.
  *
  *  @package    WordPress
  *  @subpackage pagoda
  */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php get_header(); ?>

	<?php $ID = instantiate_theme()->page_tree->get_toplevel( get_the_ID() );?>

	<div id="primary" class="content-area content-area-<?php echo esc_html($ID) ?>">
	
		<main id="main" class="site-main" itemscope itemtype="http://schema.org/WebPageElement" itemprop="mainContentOfPage">

			<div class="pagoda-page-wrap pagoda-page-wrap-<?php echo esc_html($ID)?>">
	
				<div class="pagoda-page-content-area"> 

					<div class="pagoda-page-content-wrap 
								pagoda-page-content-wrap-<?php echo esc_html($ID)?>">

					<?php 
						while ( have_posts() ) : 
							the_post();
							get_template_part( 'template-parts/content', 'page' );

							// If comments are open or we have at least one comment
							// load up the comment template.
							if ( comments_open() || get_comments_number() ) {
								comments_template();
							}
						endwhile;
					?>
					
					</div>
	
				</div>
				
			</div>	
	
		</main>
	
	</div>		

<?php get_footer(); ?>