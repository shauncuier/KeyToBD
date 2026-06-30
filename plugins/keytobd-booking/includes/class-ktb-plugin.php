<?php
/**
 * Plugin singleton — wires every module + front-end assets.
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main controller.
 */
final class KTB_Plugin {

	/**
	 * Singleton.
	 *
	 * @var KTB_Plugin|null
	 */
	private static $instance = null;

	/**
	 * Get instance.
	 *
	 * @return KTB_Plugin
	 */
	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Wire modules.
	 */
	private function __construct() {
		KTB_Security::init();
		KTB_Post_Types::init();
		KTB_Meta::init();
		KTB_Shortcodes::init();
		KTB_Ajax::init();
		KTB_Emails::init();
		KTB_Admin::init();

		add_action( 'wp_enqueue_scripts', array( $this, 'assets' ) );
		add_action( 'init', array( $this, 'i18n' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_archive' ) );
	}

	/**
	 * Filter the public service archive by ?ktb_type= and keyword (?s=).
	 * Lets the theme's hero search submit straight to /services/.
	 *
	 * @param WP_Query $q Query.
	 */
	public function filter_archive( $q ) {
		if ( is_admin() || ! $q->is_main_query() || ! $q->is_post_type_archive( 'ktb_service' ) ) {
			return;
		}

		$p    = KTB_Query::params( $_GET ); // phpcs:ignore WordPress.Security.NonceVerification
		$args = KTB_Query::build_args( $p );

		// Apply the shared args onto the main query.
		foreach ( array( 'posts_per_page', 'meta_query', 'tax_query', 'meta_key', 'orderby', 'order', 'post__not_in' ) as $key ) {
			if ( isset( $args[ $key ] ) ) {
				$q->set( $key, $args[ $key ] );
			}
		}

		// Keyword (kept inside the branded archive, no generic search.php).
		if ( ! empty( $args['ktb_kw'] ) ) {
			$q->set( 'ktb_kw', $args['ktb_kw'] );
			add_filter( 'posts_search', array( 'KTB_Query', 'kw_clause' ), 10, 2 );
		}
	}

	/**
	 * Load translations.
	 */
	public function i18n() {
		load_plugin_textdomain( 'keytobd-booking', false, dirname( KTB_BASENAME ) . '/languages' );
	}

	/**
	 * Front-end CSS/JS — only when a shortcode/service page needs it.
	 */
	public function assets() {
		wp_register_style( 'ktb', KTB_URL . 'assets/css/ktb.css', array(), KTB_VERSION );
		wp_register_script( 'ktb', KTB_URL . 'assets/js/ktb.js', array(), KTB_VERSION, true );
		wp_localize_script( 'ktb', 'KTB', array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'ktb_nonce' ),
			'currency' => ktb_currency(),
			'i18n'     => array(
				'checking'   => __( 'Checking availability…', 'keytobd-booking' ),
				'unavailable'=> __( 'Sorry, not enough availability for that date.', 'keytobd-booking' ),
				'submitting' => __( 'Submitting…', 'keytobd-booking' ),
				'error'      => __( 'Something went wrong. Please try again.', 'keytobd-booking' ),
				'required'   => __( 'Please fill all required fields.', 'keytobd-booking' ),
			),
		) );

		wp_register_script( 'ktb-archive', KTB_URL . 'assets/js/ktb-archive.js', array( 'ktb' ), KTB_VERSION, true );

		// Enqueue lazily when our shortcode/CPT is present.
		if ( is_singular( 'ktb_service' ) || is_post_type_archive( 'ktb_service' ) ) {
			wp_enqueue_style( 'ktb' );
			wp_enqueue_script( 'ktb' );
		}
		if ( is_post_type_archive( 'ktb_service' ) ) {
			wp_enqueue_script( 'ktb-archive' );
		}
	}

	/**
	 * Ensure assets are loaded (called by shortcodes).
	 */
	public static function need_assets() {
		wp_enqueue_style( 'ktb' );
		wp_enqueue_script( 'ktb' );
	}
}
