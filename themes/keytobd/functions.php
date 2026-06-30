<?php
/**
 * KeyToBD theme bootstrap.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // No direct access.
}

define( 'KEYTOBD_VERSION', '1.0.0' );
define( 'KEYTOBD_DIR', get_template_directory() );
define( 'KEYTOBD_URI', get_template_directory_uri() );

/**
 * Theme supports, menus, image sizes.
 */
function keytobd_setup() {
	load_theme_textdomain( 'keytobd', KEYTOBD_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array(
		'height'      => 64,
		'width'       => 220,
		'flex-height' => true,
		'flex-width'  => true,
	) );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );

	// WooCommerce.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus( array(
		'primary' => __( 'Primary Menu', 'keytobd' ),
		'footer'  => __( 'Footer Menu', 'keytobd' ),
		'legal'   => __( 'Legal Menu', 'keytobd' ),
	) );

	// Card / hero crops.
	add_image_size( 'keytobd-card', 640, 440, true );
	add_image_size( 'keytobd-wide', 1280, 720, true );
	add_image_size( 'keytobd-hero', 1920, 1000, true );
}
add_action( 'after_setup_theme', 'keytobd_setup' );

/**
 * Content width.
 */
function keytobd_content_width() {
	$GLOBALS['content_width'] = 1200;
}
add_action( 'after_setup_theme', 'keytobd_content_width', 0 );

/**
 * Front-end assets.
 */
function keytobd_assets() {
	// Google fonts (Poppins display + Inter body).
	wp_enqueue_style(
		'keytobd-fonts',
		'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap',
		array(),
		null
	);

	wp_enqueue_style( 'keytobd-main', KEYTOBD_URI . '/assets/css/main.css', array(), KEYTOBD_VERSION );

	wp_enqueue_script( 'keytobd-main', KEYTOBD_URI . '/assets/js/main.js', array(), KEYTOBD_VERSION, true );
	wp_localize_script( 'keytobd-main', 'KeyToBD', array(
		'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
		'shopUrl'  => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
		'whatsapp' => '8801684498885',
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'keytobd_assets' );

/**
 * Widget areas.
 */
function keytobd_widgets() {
	$cols = array(
		'footer-1' => __( 'Footer — About', 'keytobd' ),
		'footer-2' => __( 'Footer — Services', 'keytobd' ),
		'footer-3' => __( 'Footer — Destinations', 'keytobd' ),
		'footer-4' => __( 'Footer — Contact', 'keytobd' ),
	);
	foreach ( $cols as $id => $name ) {
		register_sidebar( array(
			'name'          => $name,
			'id'            => $id,
			'before_widget' => '<div class="footer-widget %2$s">',
			'after_widget'  => '</div>',
			'before_title'  => '<h4 class="footer-widget__title">',
			'after_title'   => '</h4>',
		) );
	}
}
add_action( 'widgets_init', 'keytobd_widgets' );

/**
 * Business contact details — pulled from the Customizer (with code defaults),
 * single source of truth reused across templates.
 *
 * @return array<string,string>
 */
function keytobd_contact() {
	$email = keytobd_mod( 'email' );
	return array(
		'phone1'    => keytobd_mod( 'phone1' ),
		'phone2'    => keytobd_mod( 'phone2' ),
		'whatsapp'  => keytobd_mod( 'whatsapp' ),
		'email'     => $email ? $email : get_option( 'admin_email' ),
		'facebook'  => keytobd_mod( 'facebook' ),
		'instagram' => keytobd_mod( 'instagram' ),
		'youtube'   => keytobd_mod( 'youtube' ),
		'address'   => keytobd_mod( 'address' ),
		'hours'     => keytobd_mod( 'hours' ),
		'website'   => keytobd_mod( 'website' ),
	);
}

/**
 * Build a tel: href from a local BD number (e.g. "01684498885" => "tel:+8801684498885").
 */
function keytobd_tel( $number ) {
	$digits = preg_replace( '/\D+/', '', $number );
	if ( 0 === strpos( $digits, '0' ) ) {
		$digits = '88' . $digits;
	}
	return 'tel:+' . $digits;
}

require_once KEYTOBD_DIR . '/inc/options.php';
require_once KEYTOBD_DIR . '/inc/template-tags.php';
require_once KEYTOBD_DIR . '/inc/taxonomies.php';
require_once KEYTOBD_DIR . '/inc/post-types.php';
require_once KEYTOBD_DIR . '/inc/customizer.php';
require_once KEYTOBD_DIR . '/inc/customizer-css.php';
require_once KEYTOBD_DIR . '/inc/elementor.php';
require_once KEYTOBD_DIR . '/inc/woocommerce.php';
require_once KEYTOBD_DIR . '/inc/plugin-activator.php';
require_once KEYTOBD_DIR . '/inc/setup.php';
require_once KEYTOBD_DIR . '/inc/auth.php';
