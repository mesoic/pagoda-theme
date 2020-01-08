<?php
/**
 *    The template wrapper the frontpage. Used in index.php and front-page.php
 *	  		  
 * @package    WordPress
 * @subpackage pagoda
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php

	// content-home.php is responsible for sticky posts as well as 'your recent posts'. 
	// To achieve this dual behaviour, we will set up two different WP_Query Objects 
	// depeding on whether a sticky post has been assigned. 
	$sticky = get_option( 'sticky_posts' );

	if ( isset( $sticky[0]) ){

		// Define post class
		$post_class = get_option( 'pagoda_post_styles_activate' ) ? 
			get_post_meta($sticky[0], 'category_styles', true) : 'uncategorized';

		// Setup WP_Query 
		$args = array(
			'posts_per_page' => 1,
			'post__in'  => $sticky,
			'ignore_sticky_posts' => 1
		);

		$query = new WP_Query( $args );

	}
	else {

		// Define post class
		$post_class = get_option('pagoda_post_styles_activate'  ) ? 'home' : 'uncategorized';
		$posts_num  = 5; // Should agree with Settings->Reading->Blog pages show at most

		// Setup pagination and WP_Query 

		$_paged = is_page() ? 'page' : 'paged';
		$paged  = ( get_query_var( $_paged ) ) ? get_query_var( $_paged ) : 1;
		$args   = array(
			'posts_per_page' => $posts_num,
			'paged'          => $paged
		);
		$query = new WP_Query( $args ); 
	}

?>

<div id="primary" class="content-area 
						 content-area-<?php echo esc_html($post_class) ?>">

	<main id="main" class="site-main" itemscope itemtype="http://schema.org/WebPageElement" itemprop="mainContentOfPage">

		<div class="pagoda-post-wrap pagoda-post-wrap-<?php echo esc_html($post_class) ?>">

			<?php 

				// If we have sticky posts, query the post metadata to determine which
				// template is in use and render the appropriate sidebar. 
				if ( isset( $sticky[0] ) ){

					$_template = get_post_meta( get_the_ID(), "_wp_page_template");
					$templates = array(
						'blog',
						'post'
					);
					foreach ($templates as $_ => $slug) {
						if ( strpos( $_template[0] , $slug ) ){
							instantiate_theme()->sidebars->write_html_sidebar($slug);
						}  
					}
				}

				// Otherwise render the home sidebar 					
				else {
					if ( get_option('pagoda_customize_home_sidebar') ) {
						instantiate_theme()->sidebars->write_html_sidebar('home');
					}
				}

			?>

			<div class="pagoda-post-content-area">

				<div class="pagoda-post-content-wrap 
							pagoda-post-content-wrap-<?php echo esc_html($post_class) ?>">

					<?php

						while ( $query->have_posts() ){
				
							$query->the_post();
							
							// If sticky posts, then we want to have an exact copy of the 
							// post on the homepage (styles, sidebar, etc)
							if ( isset($sticky[0] ) ) {
								
								// If sticky post, then load up the posts template
								get_template_part( 'template-parts/content', 'post' );
							}

							// Otherwise load up an archive style index page.
							else {

								if ( get_option('pagoda_customize_home_excerpt') ) {
									get_template_part( 'template-parts/content', 'arxiv' );
								}

								else {
									get_template_part( 'template-parts/content', '' );
								}			
							}	
						} 
					
						wp_reset_query(); 

					?>

					<?php instantiate_theme()->posts->templates->pagination_template( $post_class ); ?>

				</div>
				
			</div>	
			
		</div>
		
	</main>

</div>