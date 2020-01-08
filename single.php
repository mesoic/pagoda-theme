<?php
/**
 * The Template for displaying all single posts. This is the 'default' post template
 *
 *  @package WordPress
 *  @subpackage pagoda
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php get_header(); ?>

	<?php $ID = get_post_meta( get_the_ID(), 'category_styles', true ); ?>

	<div id="primary" class="content-area content-area-<?php echo esc_html($ID)?>">

		<main id="main" class="site-main" itemscope itemtype="http://schema.org/WebPageElement" itemprop="mainContentOfPage">

			<div class="pagoda-post-wrap 
						pagoda-post-wrap-<?php echo esc_html($ID)?>">

				<div class="pagoda-post-content-area">

					<div class="pagoda-post-content-wrap 
								pagoda-post-content-wrap-<?php echo esc_html($ID)?>">

					<?php 
					
						while ( have_posts() ):
					
							the_post();
							// Include the post content template.
							get_template_part( 'template-parts/content', 'post' );

						endwhile; 
						
						wp_reset_query(); 
					?>
				
					</div>
				
				</div>

			</div>
						
		</main>

	</div>

<?php get_footer(); ?>
