<?php
/**
 *    The template for dispalying the content. Used on your recent posts page
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

					// Title
					if( get_post_meta( get_the_ID(), 'show_title', true) ){
						instantiate_theme()->posts->templates->title_template();
					}

					// Metadata
					instantiate_theme()->posts->templates->meta_template(); 

					// Thumbnail
					if ( get_option( 'pagoda_customize_home_featured' ) && has_post_thumbnail() ){			
						instantiate_theme()->posts->templates->thumbnail_template(
							get_post_meta( get_the_ID(), '_size_featured_image', true) );
					}

				?>

			</div>

			<?php instantiate_theme()->posts->templates->content_template(); ?>

		</div>

	</article>

<?php endif; ?>

