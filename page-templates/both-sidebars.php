<?php
/**
 *	Template name: Both Sidebars
 *	Template Post Type: page
 *	The template for displaying Custom Page?Post Template: Both Sidebars.
 *
 *	@package WordPress
 *	@subpackage pagoda
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php get_header(); ?>

<?php  $ID = instantiate_theme()->pages->get_page_class( get_the_ID() ); ?>

<div id="primary" class="content-area content-area-<?php echo esc_html($ID)?>">
	
	<main id="main" class="site-main" itemscope itemtype="http://schema.org/WebPageElement" 
			itemprop="mainContentOfPage">

		<div class="pagoda-page-wrap pagoda-page-wrap-<?php echo esc_html($ID)?>">
		
			<?php instantiate_theme()->sidebars->write_html_sidebar('left');  ?>
			<?php instantiate_theme()->sidebars->write_html_sidebar('right'); ?>

			<div class="pagoda-page-content-area"> 
			
				<div class="pagoda-page-content-wrap pagoda-page-content-wrap-<?php echo esc_html($ID)?>">
			
				<?php 
					while ( have_posts() ) : 
				
						the_post();
				
						get_template_part( 'template-parts/content', 'page' );
				
					endwhile;
				?>
				
				</div>

			</div>

		</div>

	</main>

</div>
	
<?php get_footer(); ?>