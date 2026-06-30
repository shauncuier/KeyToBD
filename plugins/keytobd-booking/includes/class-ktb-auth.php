<?php
/**
 * Customer authentication + email verification for booking.
 *
 * Booking can be gated so only logged-in users with a VERIFIED email may book.
 * Uses WordPress-native login/registration; this class adds the verification
 * email + an HMAC-signed verify link + a throttled resend, and the gate helpers.
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Auth / email verification.
 */
class KTB_Auth {

	const META = '_ktb_email_verified';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'user_register', array( __CLASS__, 'on_register' ) );
		add_action( 'init', array( __CLASS__, 'handle_verify_link' ) );
		add_action( 'wp_ajax_ktb_resend_verify', array( __CLASS__, 'ajax_resend' ) );
	}

	/**
	 * Is booking gated behind login?
	 */
	public static function require_login() {
		return (bool) ktb_get_setting( 'require_login' );
	}

	/**
	 * Is a verified email required to book?
	 */
	public static function require_verify() {
		return (bool) ktb_get_setting( 'require_verify' );
	}

	/**
	 * Has this user verified their email? Staff (manage_options) are auto-trusted.
	 *
	 * @param int|null $user_id Defaults to current user.
	 * @return bool
	 */
	public static function is_verified( $user_id = null ) {
		$user_id = $user_id ? (int) $user_id : get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		return (bool) get_user_meta( $user_id, self::META, true );
	}

	/**
	 * Gate state for the booking form: 'guest' | 'unverified' | 'ok'.
	 *
	 * @return string
	 */
	public static function gate_state() {
		if ( self::require_login() && ! is_user_logged_in() ) {
			return 'guest';
		}
		if ( self::require_verify() && is_user_logged_in() && ! self::is_verified() ) {
			return 'unverified';
		}
		return 'ok';
	}

	/**
	 * Signed token binding user id + current email (changing email invalidates it).
	 *
	 * @param WP_User $user User.
	 * @return string
	 */
	private static function token( $user ) {
		return hash_hmac( 'sha256', $user->ID . '|' . $user->user_email, wp_salt( 'auth' ) );
	}

	/**
	 * Verification link for a user.
	 *
	 * @param WP_User $user User.
	 * @return string
	 */
	private static function link( $user ) {
		return add_query_arg(
			array( 'ktb_verify' => $user->ID, 'token' => self::token( $user ) ),
			home_url( '/' )
		);
	}

	/**
	 * On new registration: mark unverified + email the link.
	 *
	 * @param int $user_id New user ID.
	 */
	public static function on_register( $user_id ) {
		if ( user_can( $user_id, 'manage_options' ) ) {
			update_user_meta( $user_id, self::META, 1 );
			return;
		}
		update_user_meta( $user_id, self::META, 0 );
		self::send_link( $user_id );
	}

	/**
	 * Send (or resend) the verification email.
	 *
	 * @param int $user_id User ID.
	 * @return bool
	 */
	public static function send_link( $user_id ) {
		$user = get_userdata( $user_id );
		if ( ! $user || ! is_email( $user->user_email ) ) {
			return false;
		}
		$link = self::link( $user );
		$body = '<p>' . sprintf(
			/* translators: %s: site name */
			esc_html__( 'Welcome to %s! Please confirm your email address to start booking.', 'keytobd-booking' ),
			esc_html( get_bloginfo( 'name' ) )
		) . '</p>'
		. '<p style="margin:18px 0"><a href="' . esc_url( $link ) . '" style="background:#FF6B35;color:#fff;padding:12px 20px;border-radius:8px;text-decoration:none;font-weight:600">'
		. esc_html__( 'Verify my email', 'keytobd-booking' ) . '</a></p>'
		. '<p style="font-size:12px;color:#777">' . esc_html__( 'If the button does not work, copy this link:', 'keytobd-booking' ) . '<br>' . esc_url( $link ) . '</p>';

		$html = '<div style="font-family:Arial,Helvetica,sans-serif;color:#14202B;max-width:560px;margin:0 auto">'
			. '<div style="background:#0E2F44;color:#fff;padding:18px 22px;border-radius:10px 10px 0 0"><strong style="font-size:18px">' . esc_html( get_bloginfo( 'name' ) ) . '</strong></div>'
			. '<div style="border:1px solid #eee;border-top:0;padding:22px;border-radius:0 0 10px 10px">' . $body . '</div></div>';

		return wp_mail(
			$user->user_email,
			sprintf( __( 'Verify your email for %s', 'keytobd-booking' ), get_bloginfo( 'name' ) ),
			$html,
			array( 'Content-Type: text/html; charset=UTF-8' )
		);
	}

	/**
	 * Handle the verification link click.
	 */
	public static function handle_verify_link() {
		if ( ! isset( $_GET['ktb_verify'], $_GET['token'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return;
		}
		$uid   = absint( $_GET['ktb_verify'] ); // phpcs:ignore WordPress.Security.NonceVerification
		$token = sanitize_text_field( wp_unslash( $_GET['token'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
		$user  = $uid ? get_userdata( $uid ) : false;

		$redirect = home_url( '/' );
		if ( $user && hash_equals( self::token( $user ), $token ) ) {
			update_user_meta( $uid, self::META, 1 );
			$redirect = add_query_arg( 'ktb_verified', '1', $redirect );
		} else {
			$redirect = add_query_arg( 'ktb_verified', '0', $redirect );
		}
		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * AJAX: resend the verification email (logged-in, throttled).
	 */
	public static function ajax_resend() {
		if ( ! check_ajax_referer( 'ktb_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'keytobd-booking' ) ), 403 );
		}
		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Please log in first.', 'keytobd-booking' ) ), 401 );
		}
		if ( ! KTB_Security::throttle( 'resend' ) ) {
			wp_send_json_error( array( 'message' => __( 'Please wait before requesting another email.', 'keytobd-booking' ) ), 429 );
		}
		$uid = get_current_user_id();
		if ( self::is_verified( $uid ) ) {
			wp_send_json_success( array( 'message' => __( 'Your email is already verified.', 'keytobd-booking' ) ) );
		}
		self::send_link( $uid );
		wp_send_json_success( array( 'message' => __( 'Verification email sent. Check your inbox.', 'keytobd-booking' ) ) );
	}
}
