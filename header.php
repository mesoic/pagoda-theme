<?php
/**
 *    The template for displaying the header.
 *
 * @package    WordPress
 * @subpackage pagoda
 */	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly	
?>

<?php $pagoda = instantiate_theme(); ?>

<!DOCTYPE html>
<html <?php language_attributes(); ?>> 

	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		
		<?php if ( is_singular() && pings_open( get_queried_object() ) ) : ?>
			<link rel="pingback" href="<?php bloginfo( 'pingback_url' ); ?>">
		<?php endif; ?>

		<?php wp_head(); ?>
	</head>

	<body <?php body_class(); ?> >

	<header id="header">

		<?php 

			// Static Front Page
			if( get_page_uri() == 'home' ){
				$pagoda->header->write_html_header();
			}					
			// Your Latest Posts		
			else if ( is_home() ){
				$pagoda->posts->write_html_header( 'home' );
			}
			// Pages
			else if( is_page( get_the_ID() ) ){
				$pagoda->pages->write_html_header(  get_the_ID() );
			}
			// Posts, Search, Archive, 404   				 
			else  {
				$pagoda->posts->write_html_header(  get_the_ID() );
			} 

		?>
		
	</header>