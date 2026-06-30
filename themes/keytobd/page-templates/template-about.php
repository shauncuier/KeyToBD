<?php
/**
 * Template Name: About
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();

get_template_part( 'template-parts/page-hero', null, array(
	'title'    => get_the_title() ? get_the_title() : __( 'About KeyToBD', 'keytobd' ),
	'subtitle' => __( 'Your trusted travel partner from Cox\'s Bazar.', 'keytobd' ),
	'crumbs'   => array( __( 'About', 'keytobd' ) => '' ),
) );
?>
<main id="content" class="site-main">
	<div class="container">
		<div class="entry-content">
			<?php
			if ( trim( get_the_content() ) ) {
				the_content();
			} else { ?>
				<p class="lead"><?php esc_html_e( 'Keytobd is offering all travel solutions at your doorstep. From the world\'s longest sea beach at Cox\'s Bazar to the haors of Sylhet and the hills of Bandarban, we help travellers explore Bangladesh with ease and confidence.', 'keytobd' ); ?></p>
				<p><?php esc_html_e( 'Our services reflect our commitment to customers — you can book everything online at your fingertips: custom tours, hotel booking, air tickets, event management, visa processing and self packages.', 'keytobd' ); ?></p>
				<h2><?php esc_html_e( 'Why travellers choose us', 'keytobd' ); ?></h2>
				<ul class="kt-ticklist">
					<li><?php esc_html_e( 'Local expertise across every major destination in Bangladesh.', 'keytobd' ); ?></li>
					<li><?php esc_html_e( 'Transparent pricing and secure online payments.', 'keytobd' ); ?></li>
					<li><?php esc_html_e( 'One checkout for tours, stays, transport and tickets.', 'keytobd' ); ?></li>
					<li><?php esc_html_e( '24/7 support before and during your trip.', 'keytobd' ); ?></li>
				</ul>
			<?php }
			?>
		</div>

		<div class="features-grid" style="margin-top:48px;">
			<div class="feature"><span class="feature__icon"><?php keytobd_icon( 'users', 28 ); ?></span><h3>10,000+</h3><p><?php esc_html_e( 'Happy travellers', 'keytobd' ); ?></p></div>
			<div class="feature"><span class="feature__icon"><?php keytobd_icon( 'compass', 28 ); ?></span><h3>50+</h3><p><?php esc_html_e( 'Tour packages', 'keytobd' ); ?></p></div>
			<div class="feature"><span class="feature__icon"><?php keytobd_icon( 'map', 28 ); ?></span><h3>20+</h3><p><?php esc_html_e( 'Destinations', 'keytobd' ); ?></p></div>
			<div class="feature"><span class="feature__icon"><?php keytobd_icon( 'star', 28 ); ?></span><h3>4.9/5</h3><p><?php esc_html_e( 'Average rating', 'keytobd' ); ?></p></div>
		</div>
	</div>
</main>
<?php
get_footer();
