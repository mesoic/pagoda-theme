<?php
/**
 *	The template for displaying the page content.
 *
 *	@package 	WordPress
 *	@subpackage pagoda
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

instantiate_theme()->pages->write_html_page( get_the_ID() );