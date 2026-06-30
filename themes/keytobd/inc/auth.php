<?php
/**
 * Branded customer authentication — a single /account/ page with Login /
 * Register / Forgot tabs and optional Google sign-in. Replaces the bare
 * wp-login.php experience for customers. Verification (KTB_Auth) + booking gate
 * are reused; filtering the login/register URLs auto-redirects the gate here.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer auth pages + handlers.
 */
class KeyToBD_Auth_Pages {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_shortcode( 'keytobd_account', array( __CLASS__, 'shortcode' ) );

		add_filter( 'login_url', array( __CLASS__, 'login_url' ), 10, 2 );
		add_filter( 'register_url', array( __CLASS__, 'register_url' ), 10, 1 );
		add_filter( 'lostpassword_url', array( __CLASS__, 'lostpassword_url' ), 10, 2 );

		add_action( 'login_init', array( __CLASS__, 'maybe_redirect_wp_login' ) );
		add_action( 'template_redirect', array( __CLASS__, 'handle_post' ) );
		add_action( 'template_redirect', array( __CLASS__, 'maybe_redirect_logged_in' ) );

		add_action( 'wp_ajax_nopriv_ktb_google_login', array( __CLASS__, 'google_login' ) );
		add_action( 'wp_ajax_ktb_google_login', array( __CLASS__, 'google_login' ) );

		// Lightly brand the WP password-reset screens we still rely on.
		add_action( 'login_enqueue_scripts', array( __CLASS__, 'skin_wp_login' ) );
	}

	/**
	 * /account/ page URL (+ optional redirect_to).
	 */
	public static function account_url( $redirect = '' ) {
		$page = get_page_by_path( 'account' );
		$url  = $page ? get_permalink( $page ) : home_url( '/account/' );
		if ( $redirect ) {
			$url = add_query_arg( 'redirect_to', rawurlencode( $redirect ), $url );
		}
		return $url;
	}

	/** Filter: login URL. */
	public static function login_url( $url, $redirect ) {
		return self::account_url( $redirect );
	}
	/** Filter: register URL. */
	public static function register_url( $url ) {
		return add_query_arg( 'tab', 'register', self::account_url() );
	}
	/** Filter: lost-password URL. */
	public static function lostpassword_url( $url, $redirect ) {
		return add_query_arg( 'tab', 'forgot', self::account_url( $redirect ) );
	}

	/**
	 * Is the current page the account page?
	 */
	private static function is_account_page() {
		$page = get_page_by_path( 'account' );
		return $page && is_page( $page->ID );
	}

	/**
	 * Redirect non-admin GETs of wp-login.php to the branded page.
	 */
	public static function maybe_redirect_wp_login() {
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( $_REQUEST['action'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		// Leave logout + password-reset + protected-post flows to core.
		if ( in_array( $action, array( 'logout', 'rp', 'resetpass', 'postpass', 'confirmaction' ), true ) ) {
			return;
		}
		if ( 'GET' !== ( $_SERVER['REQUEST_METHOD'] ?? 'GET' ) ) {
			return;
		}
		$redirect = isset( $_REQUEST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_REQUEST['redirect_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$tab      = ( 'register' === $action ) ? 'register' : '';
		$url      = self::account_url( $redirect );
		if ( $tab ) {
			$url = add_query_arg( 'tab', $tab, $url );
		}
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Logged-in users shouldn't see the auth tabs — bounce to redirect_to/home.
	 * (The account page itself shows their dashboard via the shortcode.)
	 */
	public static function maybe_redirect_logged_in() {
		// Only act if a redirect_to is present on the account page (post-login bounce).
		if ( ! self::is_account_page() || ! is_user_logged_in() ) {
			return;
		}
		if ( isset( $_GET['redirect_to'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$to = wp_validate_redirect( esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ), home_url( '/' ) );
			wp_safe_redirect( $to );
			exit;
		}
	}

	/**
	 * Shortcode → render the split-screen auth UI (or logged-in dashboard).
	 */
	public static function shortcode() {
		self::enqueue();
		ob_start();
		$tab      = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'login'; // phpcs:ignore WordPress.Security.NonceVerification
		$err      = isset( $_GET['ktb_auth_err'] ) ? sanitize_key( $_GET['ktb_auth_err'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$ok       = isset( $_GET['ktb_auth_ok'] ) ? sanitize_key( $_GET['ktb_auth_ok'] ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		$redirect = isset( $_GET['redirect_to'] ) ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
		include get_template_directory() . '/template-parts/auth.php';
		return ob_get_clean();
	}

	/**
	 * Enqueue auth assets (+ Google script when configured).
	 */
	private static function enqueue() {
		wp_enqueue_style( 'keytobd-main' );
		wp_enqueue_script( 'keytobd-auth', KEYTOBD_URI . '/assets/js/auth.js', array(), KEYTOBD_VERSION, true );
		wp_localize_script( 'keytobd-auth', 'KeyToBDAuth', array(
			'ajaxUrl'  => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'ktb_google' ),
			'clientId' => function_exists( 'ktb_get_setting' ) ? ktb_get_setting( 'google_client_id' ) : '',
		) );
		if ( function_exists( 'ktb_get_setting' ) && ktb_get_setting( 'google_client_id' ) ) {
			wp_enqueue_script( 'google-gis', 'https://accounts.google.com/gsi/client', array(), null, true );
		}
	}

	/**
	 * Map a result code to a user-facing message.
	 */
	public static function message( $code ) {
		$map = array(
			'invalid'   => __( 'Incorrect email or password. Please try again.', 'keytobd' ),
			'throttle'  => __( 'Too many attempts. Please wait a few minutes and try again.', 'keytobd' ),
			'fields'    => __( 'Please fill in all required fields.', 'keytobd' ),
			'email'     => __( 'Please enter a valid email address.', 'keytobd' ),
			'weak'      => __( 'Password must be at least 8 characters.', 'keytobd' ),
			'exists'    => __( 'Could not create the account — the email may already be registered.', 'keytobd' ),
			'bot'       => __( 'Submission blocked. Please try again.', 'keytobd' ),
			'reset'     => __( 'If that email exists, a reset link is on its way.', 'keytobd' ),
			'registered'=> __( 'Account created! Check your email to verify your address.', 'keytobd' ),
		);
		return $map[ $code ] ?? '';
	}

	/**
	 * Process login / register / forgot POSTs.
	 */
	public static function handle_post() {
		if ( empty( $_POST['ktb_auth_action'] ) ) {
			return;
		}
		$action   = sanitize_key( wp_unslash( $_POST['ktb_auth_action'] ) );
		$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
		$base     = self::account_url( $redirect );

		if ( ! isset( $_POST['ktb_auth_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ktb_auth_nonce'] ), 'ktb_auth' ) ) {
			self::bounce( $base, 'login', 'invalid' );
		}

		if ( 'login' === $action ) {
			self::do_login( $base, $redirect );
		} elseif ( 'register' === $action ) {
			self::do_register( $base, $redirect );
		} elseif ( 'forgot' === $action ) {
			self::do_forgot( $base );
		}
	}

	/**
	 * Redirect helper with tab + code.
	 */
	private static function bounce( $base, $tab, $code, $is_error = true ) {
		$url = add_query_arg( array( 'tab' => $tab, ( $is_error ? 'ktb_auth_err' : 'ktb_auth_ok' ) => $code ), $base );
		wp_safe_redirect( $url );
		exit;
	}

	/**
	 * Login.
	 */
	private static function do_login( $base, $redirect ) {
		if ( class_exists( 'KTB_Security' ) && ! KTB_Security::throttle( 'login' ) ) {
			self::bounce( $base, 'login', 'throttle' );
		}
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$pass  = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
		if ( '' === $email || '' === $pass ) {
			self::bounce( $base, 'login', 'fields' );
		}
		$user = get_user_by( 'email', $email );
		if ( ! $user ) {
			self::bounce( $base, 'login', 'invalid' ); // generic — no enumeration.
		}
		$signed = wp_signon( array(
			'user_login'    => $user->user_login,
			'user_password' => $pass,
			'remember'      => ! empty( $_POST['remember'] ),
		), is_ssl() );
		if ( is_wp_error( $signed ) ) {
			self::bounce( $base, 'login', 'invalid' );
		}
		wp_set_current_user( $signed->ID );
		$to = $redirect ? wp_validate_redirect( $redirect, home_url( '/' ) ) : home_url( '/' );
		wp_safe_redirect( $to );
		exit;
	}

	/**
	 * Register a customer.
	 */
	private static function do_register( $base, $redirect ) {
		// Anti-bot honeypot (reuse rotated field when available).
		$hp = class_exists( 'KTB_Security' ) ? KTB_Security::hp_field() : 'ktb_website';
		if ( ! empty( $_POST[ $hp ] ) || ! empty( $_POST['ktb_website'] ) ) {
			self::bounce( $base, 'register', 'bot' );
		}
		if ( class_exists( 'KTB_Security' ) && ! KTB_Security::throttle( 'register' ) ) {
			self::bounce( $base, 'register', 'throttle' );
		}

		$name  = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$pass  = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
		$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : '';
		if ( function_exists( 'ktb_clamp' ) ) {
			$name = ktb_clamp( $name, 120 );
			$phone = ktb_clamp( $phone, 20 );
		}

		if ( '' === $name || '' === $email || '' === $pass ) {
			self::bounce( $base, 'register', 'fields' );
		}
		if ( ! is_email( $email ) ) {
			self::bounce( $base, 'register', 'email' );
		}
		if ( strlen( $pass ) < 8 ) {
			self::bounce( $base, 'register', 'weak' );
		}
		if ( email_exists( $email ) || username_exists( $email ) ) {
			self::bounce( $base, 'register', 'exists' );
		}

		$user_id = wp_insert_user( array(
			'user_login'   => $email,
			'user_email'   => $email,
			'user_pass'    => $pass,
			'display_name' => $name,
			'first_name'   => $name,
			'role'         => 'subscriber',
		) );
		if ( is_wp_error( $user_id ) ) {
			self::bounce( $base, 'register', 'exists' );
		}
		if ( $phone ) {
			update_user_meta( $user_id, 'billing_phone', $phone );
		}
		// user_register fired KTB_Auth verification email automatically.

		// Auto-login then send them to the booking (where the verify gate shows).
		wp_set_auth_cookie( $user_id, true, is_ssl() );
		wp_set_current_user( $user_id );
		$to = $redirect ? wp_validate_redirect( $redirect, home_url( '/' ) ) : self::account_url();
		wp_safe_redirect( $to );
		exit;
	}

	/**
	 * Forgot password — generic confirmation (anti-enumeration).
	 */
	private static function do_forgot( $base ) {
		if ( class_exists( 'KTB_Security' ) && ! KTB_Security::throttle( 'forgot' ) ) {
			self::bounce( $base, 'forgot', 'throttle' );
		}
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$user  = $email ? get_user_by( 'email', $email ) : false;
		if ( $user ) {
			// Reuse core: set the username then call retrieve_password().
			$_POST['user_login'] = $user->user_login;
			retrieve_password();
		}
		self::bounce( $base, 'forgot', 'reset', false );
	}

	/**
	 * Google Sign-In — verify the ID token server-side, then log in / create.
	 */
	public static function google_login() {
		if ( ! check_ajax_referer( 'ktb_google', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'keytobd' ) ), 403 );
		}
		$client_id = function_exists( 'ktb_get_setting' ) ? (string) ktb_get_setting( 'google_client_id' ) : '';
		if ( '' === $client_id ) {
			wp_send_json_error( array( 'message' => __( 'Google sign-in is not configured.', 'keytobd' ) ), 400 );
		}
		$cred = isset( $_POST['credential'] ) ? sanitize_text_field( wp_unslash( $_POST['credential'] ) ) : '';
		if ( '' === $cred ) {
			wp_send_json_error( array( 'message' => __( 'Missing Google credential.', 'keytobd' ) ), 400 );
		}

		$resp = wp_remote_get( 'https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode( $cred ), array( 'timeout' => 8 ) );
		if ( is_wp_error( $resp ) || 200 !== wp_remote_retrieve_response_code( $resp ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not verify Google account.', 'keytobd' ) ), 400 );
		}
		$claims = json_decode( wp_remote_retrieve_body( $resp ), true );

		$aud_ok   = isset( $claims['aud'] ) && hash_equals( $client_id, (string) $claims['aud'] );
		$iss_ok   = isset( $claims['iss'] ) && in_array( $claims['iss'], array( 'accounts.google.com', 'https://accounts.google.com' ), true );
		$exp_ok   = isset( $claims['exp'] ) && (int) $claims['exp'] > time();
		$email_ok = ! empty( $claims['email'] ) && is_email( $claims['email'] )
			&& isset( $claims['email_verified'] ) && in_array( $claims['email_verified'], array( true, 'true', 1, '1' ), true );

		if ( ! ( $aud_ok && $iss_ok && $exp_ok && $email_ok ) ) {
			wp_send_json_error( array( 'message' => __( 'Google verification failed.', 'keytobd' ) ), 401 );
		}

		$email = sanitize_email( $claims['email'] );
		$user  = get_user_by( 'email', $email );
		if ( ! $user ) {
			$name    = ! empty( $claims['name'] ) ? sanitize_text_field( $claims['name'] ) : $email;
			$user_id = wp_insert_user( array(
				'user_login'   => $email,
				'user_email'   => $email,
				'user_pass'    => wp_generate_password( 24 ),
				'display_name' => $name,
				'role'         => 'subscriber',
			) );
			if ( is_wp_error( $user_id ) ) {
				wp_send_json_error( array( 'message' => __( 'Could not create your account.', 'keytobd' ) ), 500 );
			}
			$user = get_user_by( 'id', $user_id );
		}
		// Google emails are pre-verified → satisfy the booking gate immediately.
		update_user_meta( $user->ID, '_ktb_email_verified', 1 );

		wp_set_auth_cookie( $user->ID, true, is_ssl() );
		wp_set_current_user( $user->ID );

		$redirect = isset( $_POST['redirect_to'] ) ? esc_url_raw( wp_unslash( $_POST['redirect_to'] ) ) : '';
		$to       = $redirect ? wp_validate_redirect( $redirect, home_url( '/' ) ) : home_url( '/' );
		wp_send_json_success( array( 'redirect' => $to ) );
	}

	/**
	 * Brand the WP password-reset screens (logo + accent).
	 */
	public static function skin_wp_login() {
		$navy   = function_exists( 'keytobd_mod' ) ? keytobd_mod( 'color_navy' ) : '#0E2F44';
		$accent = function_exists( 'keytobd_mod' ) ? keytobd_mod( 'color_accent' ) : '#FF6B35';
		echo '<style>body.login{background:' . esc_attr( $navy ) . '}.login h1 a{background-image:none;width:auto;height:auto;text-indent:0;font:700 26px/1 Poppins,sans-serif;color:#fff}.login h1 a:after{content:"KeyToBD"}.wp-core-ui .button-primary{background:' . esc_attr( $accent ) . ';border-color:' . esc_attr( $accent ) . '}.login #backtoblog a,.login #nav a{color:#cfe0ee}</style>';
	}
}
KeyToBD_Auth_Pages::init();
