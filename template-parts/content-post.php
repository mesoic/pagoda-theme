<?php
/**
 *	The template for displaying the post content.
 *
 *	@package 	WordPress
 *	@subpackage pagoda
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

instantiate_theme()->posts->write_html_post( get_the_ID() );
	