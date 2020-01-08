<?php
/**
 * The template for displaying the footer.
 *
 *  @package    WordPress
 *  @subpackage pagoda
 */	
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

		<footer id="footer">			

			<?php 
				instantiate_theme()->footer->write_html_footer(); 
				wp_footer(); 
			?>

		</footer>

	</body>
	
</html>