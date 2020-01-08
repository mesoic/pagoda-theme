<?php
/**
 * The template for displaying 404 pages (Not Found).
 *
 *  @package	   WordPress
 *  @subpackage pagoda
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php get_header(); ?>

	<div id="primary" class="content-area content-area-uncategorized">
		
		<main id="main" class="site-main" itemscope itemtype="http://schema.org/WebPageElement" itemprop="mainContentOfPage">
		
			<div class="pagoda-post-wrap pagoda-post-wrap-uncategorized">
		
				<?php instantiate_theme()->sidebars->write_html_sidebar('arxiv'); ?>	
		
				<div class="pagoda-post-content-area">
	
					<div class="pagoda-post-content-wrap pagoda-post-content-wrap-uncategorized">

						<article  id="post-none" class="blog-post post"); ?>
							
							<div class="entry-content-post">
						
								<h1><?php esc_html_e('404'			 , 'pagoda'); ?></h1>
								<h3><?php esc_html_e('page not found', 'pagoda'); ?></h3>
						
							</div>
						
						</article>
	
					</div>

				</div>
	
			</div>
	
		</main>
	
	</div>

<?php get_footer(); ?>