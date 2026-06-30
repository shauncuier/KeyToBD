<?php
/**
 * Default page template.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();

while ( have_posts() ) :
	the_post();
	get_template_part( 'template-parts/page-hero', null, array(
		'title'  => get_the_title(),
		'crumbs' => array( get_the_title() => '' ),
	) );
	?>
	<main id="content" class="site-main">
		<div class="container">
			<div class="entry-content">
				<?php the_content(); ?>
				<?php wp_link_pages(); ?>
			</div>
		</div>
	</main>
	<?php
endwhile;

get_footer();
