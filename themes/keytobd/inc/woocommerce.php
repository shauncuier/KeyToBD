<?php
/**
 * WooCommerce integration for the booking engine.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seed the five booking product categories on theme activation.
 */
function keytobd_seed_product_cats() {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return;
	}
	$cats = array(
		'Tour Packages'    => 'tour-packages',
		'Hotels & Resorts' => 'hotels-resorts',
		'Rent-A-Car'       => 'rent-a-car',
		'Ship Tickets'     => 'ship-tickets',
		'Houseboat'        => 'houseboat',
	);
	foreach ( $cats as $name => $slug ) {
		if ( ! term_exists( $slug, 'product_cat' ) ) {
			wp_insert_term( $name, 'product_cat', array( 'slug' => $slug ) );
		}
	}
}
add_action( 'after_switch_theme', 'keytobd_seed_product_cats' );

/**
 * Unwrap WooCommerce content and inject the theme's own wrappers so shop pages
 * inherit the site shell (header/footer + .site-main container).
 */
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );

add_action( 'woocommerce_before_main_content', 'keytobd_wc_wrapper_start', 10 );
function keytobd_wc_wrapper_start() {
	echo '<main id="content" class="site-main wc-main"><div class="container">';
}

add_action( 'woocommerce_after_main_content', 'keytobd_wc_wrapper_end', 10 );
function keytobd_wc_wrapper_end() {
	echo '</div></main>';
}

/**
 * Products per shop page.
 */
add_filter( 'loop_shop_per_page', function () {
	return 12;
} );

/**
 * Products per row.
 */
add_filter( 'loop_shop_columns', function () {
	return 3;
} );

/**
 * Relabel the add-to-cart button across booking categories.
 */
add_filter( 'woocommerce_product_single_add_to_cart_text', function () {
	return __( 'Book Now', 'keytobd' );
} );
add_filter( 'woocommerce_product_add_to_cart_text', function () {
	return __( 'Book Now', 'keytobd' );
} );

/**
 * Set BDT as currency symbol display nicely if store uses it.
 */
add_filter( 'woocommerce_currency_symbol', function ( $symbol, $currency ) {
	if ( 'BDT' === $currency ) {
		$symbol = '৳';
	}
	return $symbol;
}, 10, 2 );

/**
 * Show a "From ৳x" price prefix on bookable/variable products in the loop.
 */
add_filter( 'woocommerce_get_price_html', function ( $price, $product ) {
	if ( ! is_admin() && $product->is_type( array( 'variable', 'booking' ) ) && is_shop() ) {
		$price = '<span class="price-from">' . esc_html__( 'From', 'keytobd' ) . '</span> ' . $price;
	}
	return $price;
}, 10, 2 );

/**
 * Trust strip directly under the booking button on a single product.
 * Reassures the customer at the point of conversion.
 */
add_action( 'woocommerce_after_add_to_cart_button', 'keytobd_single_trust_strip', 20 );
function keytobd_single_trust_strip() {
	$kt = keytobd_contact();
	echo '<ul class="kt-trust">';
	echo '<li>' . wp_kses_post( keytobd_icon_str( 'shield' ) ) . esc_html__( 'Secure payment', 'keytobd' ) . '</li>';
	echo '<li>' . wp_kses_post( keytobd_icon_str( 'check' ) ) . esc_html__( 'Instant e-voucher', 'keytobd' ) . '</li>';
	echo '<li>' . wp_kses_post( keytobd_icon_str( 'clock' ) ) . esc_html__( '24/7 support', 'keytobd' ) . '</li>';
	echo '</ul>';
	printf(
		'<a class="kt-need-help" href="https://wa.me/%s" target="_blank" rel="noopener">%s %s</a>',
		esc_attr( $kt['whatsapp'] ),
		wp_kses_post( keytobd_icon_str( 'whatsapp' ) ),
		esc_html__( 'Need help booking? Chat with us', 'keytobd' )
	);
}

/**
 * Friendly confirmation note on the order-received (thank you) page —
 * positions the WooCommerce order as a travel booking voucher.
 */
add_action( 'woocommerce_thankyou', 'keytobd_voucher_note', 5 );
function keytobd_voucher_note( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}
	echo '<div class="kt-voucher"><h2>' . esc_html__( 'Your booking is confirmed!', 'keytobd' ) . '</h2>';
	echo '<p>' . esc_html__( 'A booking voucher has been emailed to you. Show it at check-in or boarding. Our team will contact you with trip details.', 'keytobd' ) . '</p></div>';
}
