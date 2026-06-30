<?php
/**
 * Template Name: Full Width (Elementor)
 *
 * Theme header + footer, but the content area is edge-to-edge with no container,
 * sidebar, title or narrow wrapper — ideal for building pages with Elementor
 * (or the block editor) section by section.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();
?>
<main id="content" class="site-main site-main--full">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</main>
<?php
get_footer();
