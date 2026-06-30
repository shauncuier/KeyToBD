<?php
/**
 * Site footer.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

$kt = keytobd_contact();
?>

<?php if ( keytobd_do_elementor_location( 'footer' ) ) : ?>
	<?php // Elementor Pro Theme Builder rendered the footer. ?>
<?php else : ?>

<footer class="site-footer">
	<div class="container">

		<div class="footer-cta">
			<div class="footer-cta__text">
				<h2><?php echo esc_html( keytobd_mod( 'footer_cta_title' ) ); ?></h2>
				<p><?php echo esc_html( keytobd_mod( 'footer_cta_text' ) ); ?></p>
			</div>
			<div class="footer-cta__actions">
				<a href="<?php echo esc_url( keytobd_cat_link( 'tour-packages' ) ); ?>" class="btn btn--accent"><?php esc_html_e( 'Browse Packages', 'keytobd' ); ?></a>
				<a href="https://wa.me/<?php echo esc_attr( $kt['whatsapp'] ); ?>" class="btn btn--ghost-light" target="_blank" rel="noopener"><?php keytobd_icon( 'whatsapp', 18 ); ?> <?php esc_html_e( 'Chat on WhatsApp', 'keytobd' ); ?></a>
			</div>
		</div>

		<div class="footer-grid">
			<div class="footer-col footer-col--about">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo site-logo--light"><span class="site-logo__key">Key</span><span class="site-logo__to">To</span><span class="site-logo__bd">BD</span></a>
					<p class="footer-about"><?php echo esc_html( keytobd_mod( 'footer_about' ) ); ?></p>
					<div class="footer-socials">
						<?php if ( $kt['facebook'] ) : ?><a href="<?php echo esc_url( $kt['facebook'] ); ?>" class="footer-social" target="_blank" rel="noopener" aria-label="Facebook"><?php keytobd_icon( 'facebook', 18 ); ?></a><?php endif; ?>
						<?php if ( $kt['instagram'] ) : ?><a href="<?php echo esc_url( $kt['instagram'] ); ?>" class="footer-social" target="_blank" rel="noopener" aria-label="Instagram"><?php keytobd_icon( 'globe', 18 ); ?></a><?php endif; ?>
						<?php if ( $kt['youtube'] ) : ?><a href="<?php echo esc_url( $kt['youtube'] ); ?>" class="footer-social" target="_blank" rel="noopener" aria-label="YouTube"><?php keytobd_icon( 'compass', 18 ); ?></a><?php endif; ?>
					</div>
					<?php if ( is_active_sidebar( 'footer-1' ) ) { dynamic_sidebar( 'footer-1' ); } ?>
			</div>

			<div class="footer-col">
				<?php if ( is_active_sidebar( 'footer-2' ) ) : dynamic_sidebar( 'footer-2' ); else : ?>
					<h4 class="footer-widget__title"><?php esc_html_e( 'Services', 'keytobd' ); ?></h4>
					<ul class="footer-links">
						<li><a href="<?php echo esc_url( keytobd_cat_link( 'tour-packages' ) ); ?>"><?php esc_html_e( 'Tour Packages', 'keytobd' ); ?></a></li>
						<li><a href="<?php echo esc_url( keytobd_cat_link( 'hotels-resorts' ) ); ?>"><?php esc_html_e( 'Hotels & Resorts', 'keytobd' ); ?></a></li>
						<li><a href="<?php echo esc_url( keytobd_cat_link( 'rent-a-car' ) ); ?>"><?php esc_html_e( 'Rent A Car', 'keytobd' ); ?></a></li>
						<li><a href="<?php echo esc_url( keytobd_cat_link( 'ship-tickets' ) ); ?>"><?php esc_html_e( 'Saint Martin Ship', 'keytobd' ); ?></a></li>
						<li><a href="<?php echo esc_url( keytobd_cat_link( 'houseboat' ) ); ?>"><?php esc_html_e( 'Houseboat Tours', 'keytobd' ); ?></a></li>
					</ul>
				<?php endif; ?>
			</div>

			<div class="footer-col">
				<?php if ( is_active_sidebar( 'footer-3' ) ) : dynamic_sidebar( 'footer-3' ); else : ?>
					<h4 class="footer-widget__title"><?php esc_html_e( 'Company', 'keytobd' ); ?></h4>
					<ul class="footer-links">
						<li><a href="<?php echo esc_url( home_url( '/about/' ) ); ?>"><?php esc_html_e( 'About Us', 'keytobd' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/visa-processing/' ) ); ?>"><?php esc_html_e( 'Visa Processing', 'keytobd' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/event-management/' ) ); ?>"><?php esc_html_e( 'Event Management', 'keytobd' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/blog/' ) ); ?>"><?php esc_html_e( 'Travel Guide', 'keytobd' ); ?></a></li>
						<li><a href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php esc_html_e( 'FAQ', 'keytobd' ); ?></a></li>
					</ul>
				<?php endif; ?>
			</div>

			<div class="footer-col">
				<?php if ( is_active_sidebar( 'footer-4' ) ) : dynamic_sidebar( 'footer-4' ); else : ?>
					<h4 class="footer-widget__title"><?php esc_html_e( 'Get in Touch', 'keytobd' ); ?></h4>
					<ul class="footer-contact">
						<li><?php keytobd_icon( 'map', 18 ); ?> <span><?php echo esc_html( $kt['address'] ); ?></span></li>
						<li><?php keytobd_icon( 'phone', 18 ); ?> <a href="<?php echo esc_attr( keytobd_tel( $kt['phone1'] ) ); ?>"><?php echo esc_html( $kt['phone1'] ); ?></a> / <a href="<?php echo esc_attr( keytobd_tel( $kt['phone2'] ) ); ?>"><?php echo esc_html( $kt['phone2'] ); ?></a></li>
						<li><?php keytobd_icon( 'globe', 18 ); ?> <a href="https://keytobd.com">keytobd.com</a></li>
					</ul>
				<?php endif; ?>
			</div>
		</div>

		<div class="footer-bottom">
			<p>&copy; <?php echo esc_html( date_i18n( 'Y' ) ); ?> KeyToBD. <?php esc_html_e( 'All rights reserved.', 'keytobd' ); ?></p>
			<nav class="footer-legal" aria-label="<?php esc_attr_e( 'Legal', 'keytobd' ); ?>">
				<?php
				wp_nav_menu( array(
					'theme_location' => 'legal',
					'container'      => false,
					'menu_class'     => 'footer-legal__menu',
					'depth'          => 1,
					'fallback_cb'    => false,
				) );
				?>
			</nav>
			<?php if ( keytobd_is_on( 'footer_credit_on' ) ) : ?>
				<p class="footer-credit"><?php esc_html_e( 'Designed & developed by', 'keytobd' ); ?> <a href="https://3s-soft.com" target="_blank" rel="noopener"><strong>3s-Soft</strong></a></p>
			<?php endif; ?>
		</div>
	</div>
</footer>
<?php endif; // end fallback footer. ?>

<!-- Floating WhatsApp -->
<a href="https://wa.me/<?php echo esc_attr( $kt['whatsapp'] ); ?>" class="whatsapp-float" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'keytobd' ); ?>">
	<?php keytobd_icon( 'whatsapp', 28 ); ?>
</a>

<!-- Mobile sticky action bar -->
<div class="mobile-bar">
	<a href="<?php echo esc_attr( keytobd_tel( $kt['phone1'] ) ); ?>" class="mobile-bar__item"><?php keytobd_icon( 'phone', 20 ); ?><span><?php esc_html_e( 'Call', 'keytobd' ); ?></span></a>
	<a href="https://wa.me/<?php echo esc_attr( $kt['whatsapp'] ); ?>" class="mobile-bar__item" target="_blank" rel="noopener"><?php keytobd_icon( 'whatsapp', 20 ); ?><span><?php esc_html_e( 'WhatsApp', 'keytobd' ); ?></span></a>
	<a href="<?php echo esc_url( keytobd_cat_link( 'tour-packages' ) ); ?>" class="mobile-bar__item mobile-bar__item--primary"><?php keytobd_icon( 'calendar', 20 ); ?><span><?php esc_html_e( 'Book', 'keytobd' ); ?></span></a>
</div>

<?php wp_footer(); ?>
</body>
</html>
