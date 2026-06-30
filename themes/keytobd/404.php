<?php
/**
 * 404 not found.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();
?>
<main id="content" class="site-main">
	<div class="container" style="text-align:center;max-width:620px;">
		<p class="eyebrow"><?php esc_html_e( 'Error 404', 'keytobd' ); ?></p>
		<h1 style="font-size:clamp(3rem,8vw,5rem);"><?php esc_html_e( 'Lost your way?', 'keytobd' ); ?></h1>
		<p style="color:var(--muted);font-size:1.1rem;"><?php esc_html_e( "The page you are looking for has packed its bags. Let's get you back on the map.", 'keytobd' ); ?></p>
		<div style="margin:24px 0;"><?php get_search_form(); ?></div>
		<div class="btn-row" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
			<a class="btn btn--accent btn--lg" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Back to Home', 'keytobd' ); ?></a>
			<a class="btn btn--ghost btn--lg" href="<?php echo esc_url( keytobd_cat_link( 'tour-packages' ) ); ?>"><?php esc_html_e( 'Browse Tours', 'keytobd' ); ?></a>
		</div>
	</div>
</main>
<?php
get_footer();
