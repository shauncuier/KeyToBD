<?php
/**
 * Bundled-plugin activator (TGMPA-style, self-contained).
 *
 * Keeps the booking engine as a SEPARATE plugin (so booking data survives theme
 * switches) but makes the theme:
 *   1. auto-activate the plugin on theme activation if it is already installed;
 *   2. offer a one-click install from the bundled zip if it is missing;
 *   3. show an admin notice if it is installed-but-deactivated.
 *
 * The bundled zip (assets/required-plugins/keytobd-booking.zip) is a build
 * artifact produced by bin/build-plugin-zip.sh from the live plugin source.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Required-plugin manager.
 */
class KeyToBD_Plugin_Activator {

	const PLUGIN_FILE = 'keytobd-booking/keytobd-booking.php';
	const SLUG        = 'keytobd-booking';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'after_switch_theme', array( __CLASS__, 'maybe_activate' ), 5 );
		add_action( 'admin_notices', array( __CLASS__, 'notice' ) );
		add_action( 'admin_post_keytobd_install_plugin', array( __CLASS__, 'handle_install' ) );
		add_action( 'admin_post_keytobd_activate_plugin', array( __CLASS__, 'handle_activate' ) );
	}

	/**
	 * Path to the bundled plugin zip.
	 */
	public static function zip_path() {
		return get_template_directory() . '/assets/required-plugins/keytobd-booking.zip';
	}

	/**
	 * Is the plugin installed (folder present)?
	 */
	public static function is_installed() {
		return file_exists( WP_PLUGIN_DIR . '/' . self::PLUGIN_FILE );
	}

	/**
	 * Is the plugin active?
	 */
	public static function is_active() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		return is_plugin_active( self::PLUGIN_FILE );
	}

	/**
	 * On theme activation: auto-activate the plugin if it is installed.
	 */
	public static function maybe_activate() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			return;
		}
		if ( self::is_installed() && ! self::is_active() ) {
			$result = activate_plugin( self::PLUGIN_FILE );
			if ( ! is_wp_error( $result ) ) {
				set_transient( 'keytobd_plugin_activated', 1, 30 );
			}
		}
	}

	/**
	 * Admin notice: prompt to install or activate when needed.
	 */
	public static function notice() {
		if ( ! current_user_can( 'install_plugins' ) || self::is_active() ) {
			return;
		}
		// Only nag on dashboard / themes / plugins / our setup screens.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		$show_on = array( 'dashboard', 'themes', 'plugins', 'appearance_page_keytobd-setup' );
		if ( $screen && ! in_array( $screen->id, $show_on, true ) ) {
			return;
		}

		if ( self::is_installed() ) {
			$url   = wp_nonce_url( admin_url( 'admin-post.php?action=keytobd_activate_plugin' ), 'keytobd_activate_plugin' );
			$label = __( 'Activate KeyToBD Booking', 'keytobd' );
			$msg   = __( 'KeyToBD Booking is installed but not active. Activate it to enable tours, services and online booking.', 'keytobd' );
		} else {
			if ( ! file_exists( self::zip_path() ) ) {
				return; // No bundled installer available.
			}
			$url   = wp_nonce_url( admin_url( 'admin-post.php?action=keytobd_install_plugin' ), 'keytobd_install_plugin' );
			$label = __( 'Install KeyToBD Booking', 'keytobd' );
			$msg   = __( 'The KeyToBD theme requires the KeyToBD Booking plugin for its tours and online booking. Install it now.', 'keytobd' );
		}
		printf(
			'<div class="notice notice-warning"><p>%s</p><p><a href="%s" class="button button-primary">%s</a></p></div>',
			esc_html( $msg ),
			esc_url( $url ),
			esc_html( $label )
		);
	}

	/**
	 * Activate handler (button).
	 */
	public static function handle_activate() {
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'keytobd' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'keytobd_activate_plugin' );
		if ( self::is_installed() ) {
			activate_plugin( self::PLUGIN_FILE );
		}
		wp_safe_redirect( admin_url( 'themes.php?page=keytobd-setup' ) );
		exit;
	}

	/**
	 * Install-from-bundled-zip handler (button) using core Plugin_Upgrader.
	 */
	public static function handle_install() {
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'keytobd' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'keytobd_install_plugin' );

		$zip = self::zip_path();
		if ( ! file_exists( $zip ) ) {
			wp_die( esc_html__( 'Bundled plugin package not found.', 'keytobd' ) );
		}

		require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
		require_once ABSPATH . 'wp-admin/includes/plugin.php';

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$installed = $upgrader->install( $zip ); // Local zip path is accepted as the package.

		if ( ! is_wp_error( $installed ) && self::is_installed() ) {
			activate_plugin( self::PLUGIN_FILE );
		}
		wp_safe_redirect( admin_url( 'themes.php?page=keytobd-setup' ) );
		exit;
	}
}
KeyToBD_Plugin_Activator::init();
