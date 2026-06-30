<?php
/**
 * Site header.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

$kt = keytobd_contact();
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link screen-reader-text" href="#content"><?php esc_html_e( 'Skip to content', 'keytobd' ); ?></a>

<?php if ( keytobd_do_elementor_location( 'header' ) ) : ?>
	<?php // Elementor Pro Theme Builder rendered the header. ?>
<?php else : ?>

<!-- Top utility bar -->
<div class="topbar">
	<div class="container topbar__inner">
		<div class="topbar__left">
			<a href="<?php echo esc_attr( keytobd_tel( $kt['phone1'] ) ); ?>" class="topbar__item"><?php keytobd_icon( 'phone', 16 ); ?> <?php echo esc_html( $kt['phone1'] ); ?></a>
			<a href="<?php echo esc_attr( keytobd_tel( $kt['phone2'] ) ); ?>" class="topbar__item topbar__item--hide-mobile"><?php echo esc_html( $kt['phone2'] ); ?></a>
			<span class="topbar__item topbar__item--hide-mobile"><?php keytobd_icon( 'map', 16 ); ?> <?php esc_html_e( "Cox's Bazar, Bangladesh", 'keytobd' ); ?></span>
		</div>
		<div class="topbar__right">
			<a href="<?php echo esc_url( $kt['facebook'] ); ?>" target="_blank" rel="noopener" aria-label="Facebook"><?php keytobd_icon( 'facebook', 16 ); ?></a>
			<?php if ( function_exists( 'wc_get_account_endpoint_url' ) ) : ?>
				<a href="<?php echo esc_url( wc_get_account_endpoint_url( 'dashboard' ) ); ?>" class="topbar__item"><?php esc_html_e( 'My Bookings', 'keytobd' ); ?></a>
			<?php endif; ?>
		</div>
	</div>
</div>

<header id="masthead" class="site-header" data-sticky>
	<div class="container site-header__inner">
		<div class="site-branding">
			<?php if ( has_custom_logo() ) : ?>
				<?php the_custom_logo(); ?>
			<?php else : ?>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="site-logo">
					<span class="site-logo__key">Key</span><span class="site-logo__to">To</span><span class="site-logo__bd">BD</span>
				</a>
			<?php endif; ?>
		</div>

		<nav class="primary-nav" aria-label="<?php esc_attr_e( 'Primary', 'keytobd' ); ?>">
			<?php
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'primary-nav__menu',
				'fallback_cb'    => 'keytobd_fallback_menu',
				'depth'          => 2,
			) );
			?>
		</nav>

		<div class="site-header__actions">
			<?php if ( function_exists( 'wc_get_cart_url' ) ) : ?>
				<a href="<?php echo esc_url( wc_get_cart_url() ); ?>" class="header-cart" aria-label="<?php esc_attr_e( 'Cart', 'keytobd' ); ?>">
					<?php keytobd_icon( 'bed', 20 ); ?>
					<span class="header-cart__count"><?php echo esc_html( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ); ?></span>
				</a>
			<?php endif; ?>
			<a href="<?php echo esc_url( keytobd_cat_link( 'tour-packages' ) ); ?>" class="btn btn--accent btn--sm header-book"><?php esc_html_e( 'Book Now', 'keytobd' ); ?></a>
			<button class="nav-toggle" aria-expanded="false" aria-controls="mobile-nav" aria-label="<?php esc_attr_e( 'Menu', 'keytobd' ); ?>">
				<?php keytobd_icon( 'menu', 24 ); ?>
			</button>
		</div>
	</div>
</header>

<!-- Mobile drawer -->
<div id="mobile-nav" class="mobile-nav" hidden>
	<div class="mobile-nav__head">
		<span class="site-logo"><span class="site-logo__key">Key</span><span class="site-logo__to">To</span><span class="site-logo__bd">BD</span></span>
		<button class="nav-close" aria-label="<?php esc_attr_e( 'Close menu', 'keytobd' ); ?>"><?php keytobd_icon( 'close', 24 ); ?></button>
	</div>
	<?php
	wp_nav_menu( array(
		'theme_location' => 'primary',
		'container'      => false,
		'menu_class'     => 'mobile-nav__menu',
		'fallback_cb'    => 'keytobd_fallback_menu',
		'depth'          => 2,
	) );
	?>
	<a href="<?php echo esc_attr( keytobd_tel( $kt['phone1'] ) ); ?>" class="btn btn--accent btn--block mobile-nav__call"><?php keytobd_icon( 'phone', 18 ); ?> <?php esc_html_e( 'Call to Book', 'keytobd' ); ?></a>
</div>
<div class="nav-overlay" hidden></div>

<?php endif; // end fallback header. ?>
