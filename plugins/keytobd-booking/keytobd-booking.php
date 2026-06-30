<?php
/**
 * Plugin Name:       KeyToBD Booking
 * Plugin URI:        https://keytobd.com
 * Description:       Self-contained online booking engine for travel services — tour packages, hotels, rent-a-car, Saint Martin ship tickets and houseboats. Bookable services, availability checks, customer bookings, email notifications and a pluggable payment hook (SSLCommerz / bKash).
 * Version:           1.3.0
 * Author:            3s-Soft
 * Author URI:        https://3s-soft.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       keytobd-booking
 * Domain Path:       /languages
 * Requires at least: 6.4
 * Requires PHP:      7.4
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'KTB_VERSION', '1.3.0' );
define( 'KTB_FILE', __FILE__ );
define( 'KTB_DIR', plugin_dir_path( __FILE__ ) );
define( 'KTB_URL', plugin_dir_url( __FILE__ ) );
define( 'KTB_BASENAME', plugin_basename( __FILE__ ) );

require_once KTB_DIR . 'includes/ktb-functions.php';
require_once KTB_DIR . 'includes/class-ktb-security.php';
require_once KTB_DIR . 'includes/class-ktb-auth.php';
require_once KTB_DIR . 'includes/class-ktb-query.php';
require_once KTB_DIR . 'includes/class-ktb-export.php';
require_once KTB_DIR . 'includes/class-ktb-post-types.php';
require_once KTB_DIR . 'includes/class-ktb-meta.php';
require_once KTB_DIR . 'includes/class-ktb-shortcodes.php';
require_once KTB_DIR . 'includes/class-ktb-ajax.php';
require_once KTB_DIR . 'includes/class-ktb-emails.php';
require_once KTB_DIR . 'includes/class-ktb-admin.php';
require_once KTB_DIR . 'includes/class-ktb-plugin.php';

/**
 * Boot the plugin.
 *
 * @return KTB_Plugin
 */
function ktb() {
	return KTB_Plugin::instance();
}
ktb();

/**
 * Activation: register CPTs/statuses so rewrite rules flush correctly, then seed.
 */
function ktb_activate() {
	KTB_Post_Types::register(); // ensure rules exist before flush.
	if ( ! get_option( 'ktb_settings' ) ) {
		add_option( 'ktb_settings', ktb_default_settings() );
	}
	// Booking requires login by default → allow customer self-registration.
	if ( ktb_get_setting( 'require_login' ) && ! get_option( 'users_can_register' ) ) {
		update_option( 'users_can_register', 1 );
	}
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'ktb_activate' );

/**
 * Deactivation: flush rewrite rules.
 */
function ktb_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'ktb_deactivate' );
