<?php
/**
 * Security helpers: per-IP rate limiting + anti-bot (honeypot + time trap).
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Security gate.
 */
class KTB_Security {

	/**
	 * Hook the optional Turnstile verifier onto the booking validation gate.
	 */
	public static function init() {
		add_filter( 'ktb_validate_booking', array( __CLASS__, 'verify_turnstile' ), 10, 1 );
	}

	/**
	 * Verify a Cloudflare Turnstile token when a secret key is configured.
	 * No-op (passes through) when Turnstile is not set up.
	 *
	 * @param true|WP_Error $ok Current gate result.
	 * @return true|WP_Error
	 */
	public static function verify_turnstile( $ok ) {
		if ( is_wp_error( $ok ) ) {
			return $ok;
		}
		$secret = (string) ktb_get_setting( 'turnstile_secret' );
		if ( '' === $secret ) {
			return $ok; // Not configured → skip.
		}
		$token = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		if ( '' === $token ) {
			return new WP_Error( 'ktb_captcha', __( 'Please complete the captcha.', 'keytobd-booking' ) );
		}
		$resp = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
			'timeout' => 8,
			'body'    => array(
				'secret'   => $secret,
				'response' => $token,
				'remoteip' => ktb_client_ip(),
			),
		) );
		if ( is_wp_error( $resp ) ) {
			return $ok; // Fail-open on network error (don't block legit customers).
		}
		$data = json_decode( wp_remote_retrieve_body( $resp ), true );
		if ( empty( $data['success'] ) ) {
			return new WP_Error( 'ktb_captcha', __( 'Captcha verification failed. Please try again.', 'keytobd-booking' ) );
		}
		return $ok;
	}

	/**
	 * Throttle an action per client IP using a transient counter.
	 *
	 * @param string $action Bucket name (e.g. 'create', 'lookup').
	 * @return bool True if allowed, false if over the limit.
	 */
	public static function throttle( $action ) {
		$count  = (int) ktb_get_setting( 'rl_count' );
		$window = (int) ktb_get_setting( 'rl_window' );
		if ( $count <= 0 || $window <= 0 ) {
			return true;
		}
		$key  = 'ktb_rl_' . $action . '_' . md5( ktb_client_ip() );
		$hits = (int) get_transient( $key );
		if ( $hits >= $count ) {
			return false;
		}
		set_transient( $key, $hits + 1, $window );
		return true;
	}

	/**
	 * Per-install honeypot field name (salt-derived so targeted bots can't
	 * hard-code an allow-list against a fixed `ktb_website` name).
	 *
	 * @return string
	 */
	public static function hp_field() {
		return 'ktb_' . substr( md5( wp_salt( 'nonce' ) . 'ktb_hp' ), 0, 10 );
	}

	/**
	 * Issue a signed, time-stamped token: "<time>.<hmac>". The HMAC binds the
	 * timestamp to the site's secret salt so a bot cannot back-date `ktb_t`.
	 *
	 * @return string
	 */
	public static function time_token() {
		$t = time();
		return $t . '.' . hash_hmac( 'sha256', (string) $t, wp_salt( 'nonce' ) );
	}

	/**
	 * Validate a signed time token: signature must match AND age must sit in
	 * [min_seconds, 1 day].
	 *
	 * @param string $token Submitted token.
	 * @return bool
	 */
	private static function valid_token( $token ) {
		$token = (string) $token;
		if ( false === strpos( $token, '.' ) ) {
			return false;
		}
		list( $t, $sig ) = explode( '.', $token, 2 );
		if ( ! ctype_digit( $t ) ) {
			return false;
		}
		$expected = hash_hmac( 'sha256', $t, wp_salt( 'nonce' ) );
		if ( ! hash_equals( $expected, (string) $sig ) ) {
			return false;
		}
		$age = time() - (int) $t;
		$min = (int) ktb_get_setting( 'min_seconds' );
		return $age >= $min && $age <= DAY_IN_SECONDS;
	}

	/**
	 * Bot heuristics: honeypot must be empty + a valid, aged, signed time token.
	 *
	 * @param array $req Request data ($_POST).
	 * @return bool True if the submission looks human.
	 */
	public static function is_human( $req ) {
		if ( ktb_get_setting( 'honeypot' ) ) {
			$field = self::hp_field();
			if ( ! empty( $req[ $field ] ) || ! empty( $req['ktb_website'] ) ) { // legacy name too.
				return false;
			}
		}
		if ( (int) ktb_get_setting( 'min_seconds' ) > 0 ) {
			if ( empty( $req['ktb_t'] ) || ! self::valid_token( wp_unslash( $req['ktb_t'] ) ) ) {
				return false;
			}
		}
		return true;
	}
}
