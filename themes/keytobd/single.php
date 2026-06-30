<?php
/**
 * Single blog post.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-hero', null, array(
		'title'  => get_the_title(),
		'crumbs' => array(
			__( 'Blog', 'keytobd' ) => home_url( '/blog/' ),
			wp_trim_words( get_the_title(), 6 ) => '',
		),
	) );
	?>
	<main id="content" class="site-main">
		<div class="container">
			<article <?php post_class( 'entry-content' ); ?>>
				<?php if ( has_post_thumbnail() ) : ?>
					<figure><?php the_post_thumbnail( 'keytobd-wide' ); ?></figure>
				<?php endif; ?>
				<p class="post-meta"><?php echo esc_html( get_the_date() ); ?> &middot; <?php the_author(); ?></p>
				<?php the_content(); ?>
				<?php wp_link_pages(); ?>
				<div class="post-tags"><?php the_tags( '<span>', ', ', '</span>' ); ?></div>
			</article>
			<div class="entry-content">
				<?php
				if ( comments_open() || get_comments_number() ) {
					comments_template();
				}
				?>
			</div>
		</div>
	</main>
	<?php
endwhile;

get_footer();
