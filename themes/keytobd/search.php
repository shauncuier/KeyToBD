<?php
/**
 * Search results.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();

get_template_part( 'template-parts/page-hero', null, array(
	/* translators: %s search query */
	'title'  => sprintf( __( 'Search: %s', 'keytobd' ), get_search_query() ),
	'crumbs' => array( __( 'Search', 'keytobd' ) => '' ),
) );
?>
<main id="content" class="site-main">
	<div class="container">
		<div style="max-width:560px;margin:0 auto 32px;"><?php get_search_form(); ?></div>
		<?php if ( have_posts() ) : ?>
			<div class="posts-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'post-card reveal' ); ?>>
						<a href="<?php the_permalink(); ?>" class="post-card__media">
							<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'keytobd-card' ); } ?>
						</a>
						<div class="post-card__body">
							<span class="post-card__date"><?php echo esc_html( get_post_type() ); ?></span>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<div class="pagination"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<p style="text-align:center;"><?php esc_html_e( 'No results found. Try a different keyword or browse our packages.', 'keytobd' ); ?></p>
			<p style="text-align:center;"><a class="btn btn--accent" href="<?php echo esc_url( keytobd_cat_link( 'tour-packages' ) ); ?>"><?php esc_html_e( 'Browse packages', 'keytobd' ); ?></a></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
