<?php
/**
  * The template for dispalying the index page.
  *
  *  @package    WordPress
  *  @subpackage pagoda
  */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php get_header(); ?>

<?php get_template_part( 'template-parts/content', 'home' ); ?>

<?php get_footer(); ?>