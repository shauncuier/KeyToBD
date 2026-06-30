<?php
/**
 * Blog index / fallback archive.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();

get_template_part( 'template-parts/page-hero', null, array(
	'title'    => is_home() ? __( 'Travel Guide', 'keytobd' ) : get_the_archive_title(),
	'subtitle' => __( 'Tips, itineraries and inspiration for your next Bangladesh trip.', 'keytobd' ),
	'crumbs'   => array( __( 'Blog', 'keytobd' ) => '' ),
) );
?>
<main id="content" class="site-main">
	<div class="container">
		<?php if ( have_posts() ) : ?>
			<div class="posts-grid">
				<?php while ( have_posts() ) : the_post(); ?>
					<article <?php post_class( 'post-card reveal' ); ?>>
						<a href="<?php the_permalink(); ?>" class="post-card__media">
							<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'keytobd-card' ); } ?>
						</a>
						<div class="post-card__body">
							<span class="post-card__date"><?php echo esc_html( get_the_date() ); ?></span>
							<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 22 ) ); ?></p>
							<a href="<?php the_permalink(); ?>" class="service-card__link"><?php esc_html_e( 'Read more', 'keytobd' ); ?> <?php keytobd_icon( 'arrow', 16 ); ?></a>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<div class="pagination"><?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => __( 'Previous', 'keytobd' ), 'next_text' => __( 'Next', 'keytobd' ) ) ); ?></div>
		<?php else : ?>
			<p><?php esc_html_e( 'No posts yet — check back soon.', 'keytobd' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
