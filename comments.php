<?php
/**
 * The template for displaying Comments
 *
 *  @package 	WordPress
 *	@subpackage pagoda
 */

/*
 * If the current post is protected by a password and the visitor has not yet
 * entered the password then return without loading comments.
 */
if ( post_password_required() ) {
	return;
}

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<div id="comments" class="comments-area">

	<?php if ( have_comments() ) : ?>
		<h3> <?php esc_html_e('Comments', 'pagoda' )?> </h3>
		<ul>
		<?php 
		wp_list_comments( array(
						'style'       => 'ul',
						'short_ping'  => true,
						'avatar_size' => 32,
					) );	
		?>
		</ul>
		<?php if ( get_comment_pages_count() > 1 ) : ?>
			<nav id="comment-nav-below" class="navigation comment-navigation" role="navigation">
				<h1 class="screen-reader-text">
					<?php esc_html_e( 'Comment navigation', 'pagoda' ); ?>
				</h1>
				<div class="nav-previous">
					<?php previous_comments_link( esc_html( '&larr; Older Comments', 'pagoda' ) ); ?>
				</div>
				<div class="nav-next">
					<?php next_comments_link( esc_html( 'Newer Comments &rarr;', 'pagoda' ) ); ?>
				</div>
			</nav>
		<?php endif; // Check for comment navigation. ?>

		<?php if ( ! comments_open() ) : ?>
			<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'pagoda' ); ?></p>
		<?php endif; ?>

	<?php endif; // have_comments() ?>

	<?php // The comment form ?>
	<?php 

		$commenter = wp_get_current_commenter();
		$req = get_option( 'require_name_email' );
		$aria_req = ( $req ? " aria-required='true'" : '' );
		
		$fields =  array(
		  'author' =>
    		'<p class="comment-data comment-form-author">' .
    		'<input id="author" name="author" type="text" value="' . 
	    		esc_attr( $commenter['comment_author'] ) . '" size="25"' . $aria_req . 
	    		'placeholder="' . __( 'name*', 'pagoda' ) . '"/>' .
			'</p>',

		  'email' =>
    		'<p class="comment-data comment-form-email">' .
    		'<input id="email" name="email" type="text" value="' . 
    			esc_attr(  $commenter['comment_author_email'] ) . '" size="25"' . $aria_req . 
    			'placeholder="' . __( 'email*', 'pagoda' ) . '"/>' .
			'</p>',

		  'url' =>
 			'<p class="comment-data comment-form-url">' . 
 			'<input id="url" name="url" type="text" value="' . 
 				esc_attr( $commenter['comment_author_url'] ) . '" size="25"' . 
 				'placeholder="' . __( 'website', 'pagoda' ) . '"/>' .
 			'</p>',
		);
		$args = array(
		  'fields' => apply_filters( 'comment_form_default_fields', $fields ),
		);
		comment_form($args);
	?>

</div><!-- #comments -->
