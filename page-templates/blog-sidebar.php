<?php
/**
 *	Template name: Blog Sidebar
 *	Template Post Type: post
 *	The template for displaying Custom Post Template: Blog Sidebar.
 *
 *	@package WordPress
 *	@subpackage pagoda
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php get_header(); ?>

<?php $ID = instantiate_theme()->posts->get_post_class( get_the_ID() ); ?>

<div id="primary" class="content-area content-area-<?php echo esc_html($ID)?>">
	
	<main id="main" class="site-main" itemscope itemtype="http://schema.org/WebPageElement" 
			itemprop="mainContentOfPage">

		<div class="pagoda-post-wrap pagoda-post-wrap-<?php echo esc_html($ID)?>">
		
			<?php instantiate_theme()->sidebars->write_html_sidebar('blog'); ?>	

			<div class="pagoda-post-content-area">

				<div class="pagoda-post-content-wrap pagoda-post-content-wrap-<?php echo esc_html($ID)?>">

				<?php 
					while ( have_posts() ): 

						the_post();
					
						get_template_part( 'template-parts/content', 'post' );

					endwhile;
				?>

				</div>

			</div>
				
		</div>

	</main>

</div>

<?php get_footer(); ?>