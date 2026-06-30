<?php
/**
 * Archive — also handles the Destination taxonomy (posts side).
 * WooCommerce product archives use WooCommerce's own templates + inc/woocommerce.php wrappers.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();

$subtitle = '';
if ( is_tax( 'destination' ) ) {
	$term     = get_queried_object();
	$subtitle = $term->description ? $term->description : __( 'Explore trips, stays and guides for this destination.', 'keytobd' );
}

get_template_part( 'template-parts/page-hero', null, array(
	'title'    => get_the_archive_title(),
	'subtitle' => $subtitle,
	'crumbs'   => array( wp_strip_all_tags( get_the_archive_title() ) => '' ),
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
							<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 20 ) ); ?></p>
						</div>
					</article>
				<?php endwhile; ?>
			</div>
			<div class="pagination"><?php the_posts_pagination(); ?></div>
		<?php else : ?>
			<p><?php esc_html_e( 'Nothing here yet.', 'keytobd' ); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();
