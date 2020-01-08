<?php
/**
 *    The template for dispalying the content. Used on the archive page
 *
 * @package    WordPress
 * @subpackage pagoda
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php if ( get_the_content() ): ?>

<?php $post_class = is_home() && 
		get_option('pagoda_post_styles_activate') ? 'home' : 'uncategorized' ?>

	<article id="post-<?php the_ID(); ?>" <?php post_class('blog-post'); ?> >

		<div class="entry-content-post entry-content-post-<?php echo $post_class; ?>">
			
			<div class="entry-content-header">

				<?php 

					instantiate_theme()->posts->templates->title_template();
					instantiate_theme()->posts->templates->meta_template(); 

					if ( get_option( 'pagoda_customize_home_featured' ) && has_post_thumbnail() ){			
						instantiate_theme()->posts->templates->thumbnail_template(
							get_post_meta( get_the_ID(), '_size_featured_image', true) );
					}

				?>
			
			</div>

			<?php 

				// Only display post excerpts for search.
				if ( is_search() ) {
					instantiate_theme()->posts->templates->exerpt_template();
				} 

				// Default archive behaviour
				else {
				
					if ( get_the_excerpt() != "" ) {
						instantiate_theme()->posts->templates->exerpt_template();
					}
						
					else {
						instantiate_theme()->posts->templates->content_template();
					}
				}

			?>
			
			<?php if( ! is_single() ) : ?>
				
				<div class="entry-content-meta">
					
					<span class="pagoda-post-more">
					
						<a href="<?php the_permalink(); ?>" class="btn">
							<?php esc_html_e( 'Continue Reading ...', 'pagoda' ); ?>
						</a>
					
					<span>
				
				</div>
			
			<?php endif; ?>
		
		</div>

	</article>

<?php endif; ?>