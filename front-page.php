<?php
/**
 * The template for displaying the front page.
 *
 *	@package 	WordPress
 *	@subpackage pagoda
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php get_header();?>

	<?php $pagoda = instantiate_theme(); ?>	

	<?php // Static Front Page ?>
	<?php if ( is_front_page() && is_page() ): ?>
		
		<div class="pagoda-page-wrapper">
		
			 <div class="site-content" id="content">
		
				<?php $pagoda->sections->write_html_sections(); ?>
		
			</div>
		
		</div>
	
	<?php endif; ?>


	<?php // Latest Posts ?>
	<?php if ( is_front_page() && is_home() ): ?>

		<?php get_template_part( 'template-parts/content', 'home' ); ?>

	<?php endif; ?>

<?php get_footer(); ?>