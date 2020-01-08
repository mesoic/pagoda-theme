<?php
/**
 * The template for dispalying the search page.
 *
 *  @package    WordPress
 *  @subpackage pagoda
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php

	// Setup pagination and WP_Query
	$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
	
	$args = array(
  		'posts_per_page' => 5,
  		'paged'          => $paged,
  		's' 			 => get_search_query()
	);

	$query = new WP_Query( $args ); 
	$_none = $query->have_posts() ? false : true;
?>

<?php get_header(); ?>

	<div id="primary" class="content-area content-area-uncategorized">
	
		<main id="main" class="site-main" itemscope itemtype="http://schema.org/WebPageElement" itemprop="mainContentOfPage">
	
			<div class="pagoda-post-wrap pagoda-post-wrap-uncategorized">

				<?php instantiate_theme()->sidebars->write_html_sidebar('arxiv'); ?>	

				<div class="pagoda-post-content-area">

					<div class="pagoda-post-content-wrap pagoda-post-content-wrap-uncategorized">

					<?php						

						if ( $query->have_posts() ){
							
							while ( $query->have_posts() ){
							
								$query->the_post();
									
								get_template_part( 'template-parts/content', 'arxiv' );
	
							}
						}
						else{

							get_template_part( 'template-parts/content', 'none' );  
						}
						wp_reset_query();
					
					?>

					<?php	

						if ( !$_none ){
							instantiate_theme()->posts->templates->pagination_template( 'uncategorized' );
						}

					?>

					</div>

				</div>

			</div>
	
		</main>
	
	</div>

<?php get_footer(); ?>