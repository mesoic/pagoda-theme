<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

// The Nablafire post templating engine. It is implemented as a container 
// class which holds templates for various page elemets 
class Nablafire_Templates { 

	public function __construct(){
		;
	}

	//////////////////////////////////////////////////
	// Header Templates (navigation, thumbnail, title)

	// Post navigation wrapper
	public function navigation_template(){ ?>

		<div class="entry-content-nav">
			<?php $this->the_post_navigation(); ?>
		</div>	

	<?php }

	// Renders inter-post navigation 
	public function the_post_navigation(){ 

		// Carets
		$_left  = '<i class="fa fa-caret-left" aria-hidden="true"></i>'; 
		$_right = '<i class="fa fa-caret-right" aria-hidden="true"></i>'; 
				
		// Post navigation
		if ( is_singular( 'post' ) ) {
				 	
			$next = get_adjacent_post( false, '', false );
			$prev = ( is_attachment() ) ? get_post( get_post()->post_parent ) 
					: get_adjacent_post( false, '', true ); ?>

			<?php if ( $next || $prev ): ?>
				
				<nav class="navigation" role="navigation">
					<div class="post-nav-links"> 
				
					<?php if($prev): ?>
						<div class="post-nav-prev">
							<?php  previous_post_link('%link', $_left . ' ' . get_the_title( $prev ) ); ?>
						</div>	
					<?php endif; ?>

					<?php if($next): ?>
						<div class="post-nav-next">
							<?php next_post_link('%link', get_the_title( $next ) . ' ' . $_right ); ?>
						</div>
					<?php endif; ?> 

					</div>
				</nav>
	
			<?php endif; ?>
	
		<?php }
	}

	// Thumbnail (Featured Image). Provide ability to echo dynamic style
	public function thumbnail_template( $_style = false ) { ?>
		
		<div class="entry-content-thumbnail" >
			<div class="entry-content-img-wrap" 
				<?php echo $_style ? $_style : '' ?> >
				<?php the_post_thumbnail(); ?>
			</div>
		</div>

	<?php }

	// Title
	public function title_template() { ?>

		<div class="entry-content-title">
			<h1> <?php the_title(); ?></h1>
		</div>

	<?php }

	////////////////////////////////////////////////
	// Content Templates (content, exerpt)	
	
	// The content wrap
	public function content_template(){ ?>

		<div class="entry-content-main" >
			<?php the_content(); ?>			
		</div>
	
	<?php }

	// Post exerpt
	public function exerpt_template(){ ?>

		<div class="entry-content-main" >
			<?php the_excerpt(); ?>
		</div>

	<?php }

	////////////////////////////////////////////////
	// Footer Templates (tags, pagination, comments)

	// Post tags
	public function tags_template() {?>
	
		<div class="entry-content-tags">
			<?php the_tags('<span><strong>Tags: </strong></span>', ', ', '<br />'); ?>
		</div>
	
	<?php }

	// Intra-page pagination (for posts with multiple pages). This method 
	// allows for a unique class to be  appended to link_pages containers
	public function link_pages_template($_class = false) { 

		// Main container
		$_before = $_class ? 
			'<div class="entry-content-pgn entry-content-pgn-' . $_class . '">' :
			'<div class="entry-content-pgn">';

		// Link containers
		$_links  = $_class ? 
			'<div class="entry-content-pgn-item entry-content-pgn-item-' . $_class . '">' :
			'<div class="entry-content-pgn-item">';
		
		// Close containers		
		$_after  = '</div>';

		// Assign pagination args and link pages 
		$args = array(
			'before'           => $_before,
			'after'            => $_after,
			'link_before'      => $_links,
			'link_after'       => $_after,
			'next_or_number'   => 'number',
			'separator'        => '',
			'nextpagelink'     => __( 'Next page', 'pagoda' ),
			'previouspagelink' => __( 'Previous page', 'pagoda' ),
			'pagelink'         => '%',
			'echo'             => 1
		);
		wp_link_pages($args);
	}

	// Comments
	public function comments_template() {
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	}

	////////////////////////////////////////////////
	// Frontpage/Archive Templates (meta, pagination)
	
	// Meta template (for archive page)
	public function meta_template() { ?>

		<div class="entry-content-meta">

			<span class="pagoda-post-date"><i class="fa fa-clock-o"></i>
				<?php the_time( get_option('date_format') );?>
			</span>
		
			<?php if ( !post_password_required() && 
					 ( comments_open() || '0' != get_comments_number() ) ) : ?>
				
				<span class="pagoda-post-comments">
				
					<i class="fa fa-comments-o"></i>
				
					<?php comments_popup_link( 
						esc_html__( 'Leave a comment', 'pagoda' ), 
						esc_html__( '1 Comment'		 , 'pagoda' ), 
						esc_html__( '% Comments'	 , 'pagoda' ) ); ?>
				
				</span>
			
			<?php endif; ?>	

		</div>

	<?php }

	// Pagination for archive and recent posts pages (paginate_links) This 
	// method allows for a unique class to be appended to item containers
	public function pagination_template( $_class = false ) { 

		$div  = $_class ? 
			'<div class="entry-content-pgn-item entry-content-pgn-item-' . $_class . '">' :
			'<div class="entry-content-pgn-item">';

		$_div = '</div>'
	
		?>

		<article id="post-pagination" <?php post_class('blog-post'); ?> >
	
			<div class="entry-content-post entry-content-post-<?php echo $_class; ?>">

				<div class="entry-content-pgn entry-content-pgn-<?php echo $_class; ?>">

				<?php 
					
					// For proper behaviour 
					$_paged = is_page() ? 'page' : 'paged';

					// Carets
					$_left  = '<i class="fa fa-caret-left" aria-hidden="true"></i>'; 
					$_right = '<i class="fa fa-caret-right" aria-hidden="true"></i>'; 

					// Set up the pagination
					$big   = 999999999; // need an unlikely integer
					$_args = array(
						'base' 		=> 
							str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
						'format' 	=> '?' . $_paged . '=%#%',
						'current'	=> max( 1, get_query_var( $_paged ) ),
						'show_all'	=> false,
						'end_size'	=> 1,
						'mid_size'	=> 1,
						'prev_next'	=> true,
						'prev_text'	=> $div . $_left . ' ' . __( 'Previous', 'pagoda' ) . $_div,
						'next_text'	=> $div . __( 'Next', 'pagoda' ) . ' ' . $_right . $_div,
						'type'		=> 'plain',
						'add_args'	=> false,
						'add_fragment'		=> '',
						'before_page_number'=> $div,
						'after_page_number'	=> $_div
					);
					echo paginate_links( $_args );
				?>	

				</div>

			</div>

		</article>

	<?php }

}