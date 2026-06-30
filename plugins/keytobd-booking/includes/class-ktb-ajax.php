<?php
/**
 * AJAX: availability check + create booking.
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AJAX handlers.
 */
class KTB_Ajax {

	/**
	 * Hooks (both logged-in and guests).
	 */
	public static function init() {
		add_action( 'wp_ajax_ktb_availability', array( __CLASS__, 'availability' ) );
		add_action( 'wp_ajax_nopriv_ktb_availability', array( __CLASS__, 'availability' ) );
		add_action( 'wp_ajax_ktb_create_booking', array( __CLASS__, 'create' ) );
		add_action( 'wp_ajax_nopriv_ktb_create_booking', array( __CLASS__, 'create' ) );
		add_action( 'wp_ajax_ktb_filter', array( __CLASS__, 'filter' ) );
		add_action( 'wp_ajax_nopriv_ktb_filter', array( __CLASS__, 'filter' ) );
		add_action( 'wp_ajax_ktb_lookup', array( __CLASS__, 'lookup' ) );
		add_action( 'wp_ajax_nopriv_ktb_lookup', array( __CLASS__, 'lookup' ) );
	}

	/**
	 * Customer booking lookup by reference + phone (throttled, no enumeration).
	 */
	public static function lookup() {
		self::check();
		if ( ! KTB_Security::throttle( 'lookup' ) ) {
			wp_send_json_error( array( 'message' => __( 'Too many lookups. Please wait and try again.', 'keytobd-booking' ) ), 429 );
		}

		$ref   = isset( $_POST['ref'] ) ? strtoupper( ktb_clamp( sanitize_text_field( wp_unslash( $_POST['ref'] ) ), 32 ) ) : '';
		$phone = isset( $_POST['phone'] ) ? ktb_clamp( sanitize_text_field( wp_unslash( $_POST['phone'] ) ), 20 ) : '';
		if ( '' === $ref || '' === $phone ) {
			wp_send_json_error( array( 'message' => __( 'Enter your reference and phone number.', 'keytobd-booking' ) ) );
		}

		$ids = get_posts( array(
			'post_type'      => 'ktb_booking',
			'post_status'    => array_keys( ktb_statuses() ),
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array( array( 'key' => '_ktb_ref', 'value' => $ref ) ),
		) );

		// Constant-ish response: require both ref AND matching phone, never reveal which failed.
		$ok = false;
		$id = $ids ? (int) $ids[0] : 0;
		if ( $id ) {
			$stored = preg_replace( '/\D+/', '', (string) get_post_meta( $id, '_ktb_phone', true ) );
			$given  = preg_replace( '/\D+/', '', $phone );
			$ok     = $stored && hash_equals( $stored, $given );
		}
		if ( ! $ok ) {
			wp_send_json_error( array( 'message' => __( 'No booking found for that reference and phone.', 'keytobd-booking' ) ) );
		}

		$statuses = ktb_statuses();
		$status   = get_post_status( $id );
		$svc      = (int) get_post_meta( $id, '_ktb_service_id', true );
		wp_send_json_success( array(
			'ref'     => $ref,
			'service' => $svc ? get_the_title( $svc ) : '',
			'date'    => get_post_meta( $id, '_ktb_date', true ),
			'qty'     => (int) get_post_meta( $id, '_ktb_qty', true ),
			'total'   => ktb_price( get_post_meta( $id, '_ktb_total', true ) ),
			'status'  => $statuses[ $status ] ?? $status,
			'paid'    => get_post_meta( $id, '_ktb_payment', true ) ? __( 'Paid', 'keytobd-booking' ) : __( 'Unpaid', 'keytobd-booking' ),
		) );
	}

	/**
	 * Live catalog filter — returns rendered cards + meta for the dynamic archive.
	 */
	public static function filter() {
		self::check();

		$p = KTB_Query::params( $_POST );
		$q = KTB_Query::run( $p );

		$shown    = (int) $q->post_count;
		$found    = (int) $q->found_posts;
		$max      = (int) $q->max_num_pages;
		$html     = KTB_Query::render_cards( $q );
		$cur_lo   = ( '' !== $p['min'] ) ? $p['min'] : null;
		$cur_hi   = ( '' !== $p['max'] ) ? $p['max'] : null;

		wp_send_json_success( array(
			'html'      => $html,
			'shown'     => $shown,
			'found'     => $found,
			'max_pages' => $max,
			'paged'     => $p['paged'],
			'count_fmt' => sprintf(
				/* translators: %d: number of services. */
				_n( '%d service found', '%d services found', $found, 'keytobd-booking' ),
				$found
			),
			'min'       => $cur_lo,
			'max'       => $cur_hi,
		) );
	}

	/**
	 * Verify nonce or die.
	 */
	private static function check() {
		if ( ! check_ajax_referer( 'ktb_nonce', 'nonce', false ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed. Refresh and retry.', 'keytobd-booking' ) ), 403 );
		}
	}

	/**
	 * Live availability + price preview.
	 */
	public static function availability() {
		self::check();

		$service_id = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;
		$date       = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';
		$date_end   = isset( $_POST['date_end'] ) ? sanitize_text_field( wp_unslash( $_POST['date_end'] ) ) : '';
		$qty        = isset( $_POST['qty'] ) ? max( 1, absint( $_POST['qty'] ) ) : 1;
		$coupon_in  = isset( $_POST['coupon'] ) ? sanitize_text_field( wp_unslash( $_POST['coupon'] ) ) : '';

		if ( ! $service_id || 'ktb_service' !== get_post_type( $service_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid service.', 'keytobd-booking' ) ) );
		}
		if ( ! self::valid_date( $date ) ) {
			wp_send_json_error( array( 'message' => __( 'Choose a valid date.', 'keytobd-booking' ) ) );
		}

		$service  = ktb_get_service( $service_id );
		$units    = self::units( $service['type'], $date, $date_end );
		$remain   = ktb_availability( $service_id, $date );
		$subtotal = $service['price'] * $qty * $units;

		// Coupon preview + date-rule notice (non-blocking here; enforced on submit).
		$coupon  = ktb_apply_coupon( $coupon_in, $subtotal );
		$total   = max( 0, $subtotal - $coupon['discount'] );
		$rules   = ktb_validate_date_rules( $service, $date );
		$deposit = (float) ktb_get_setting( 'deposit_percent' );

		wp_send_json_success( array(
			'available'    => $remain >= $qty && true === $rules && $qty >= $service['min_pax'] && $qty <= $service['max_pax'],
			'remaining'    => PHP_INT_MAX === $remain ? null : $remain,
			'units'        => $units,
			'subtotal'     => $subtotal,
			'discount'     => $coupon['discount'],
			'coupon_valid' => '' === $coupon_in ? null : $coupon['valid'],
			'coupon_label' => $coupon['valid'] ? $coupon['label'] : '',
			'total'        => $total,
			'total_fmt'    => ktb_price( $total ),
			'deposit_fmt'  => $deposit > 0 ? ktb_price( round( $total * min( 100, $deposit ) / 100, 2 ) ) : '',
			'min_pax'      => $service['min_pax'],
			'max_pax'      => $service['max_pax'],
			'notice'       => true === $rules ? '' : $rules,
		) );
	}

	/**
	 * Create a booking.
	 */
	public static function create() {
		self::check();

		// Auth gate: login + verified email (if enabled).
		if ( KTB_Auth::require_login() && ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'Please log in to book.', 'keytobd-booking' ), 'login' => wp_login_url() ), 401 );
		}
		if ( KTB_Auth::require_verify() && is_user_logged_in() && ! KTB_Auth::is_verified() ) {
			wp_send_json_error( array( 'message' => __( 'Please verify your email address before booking.', 'keytobd-booking' ), 'verify' => true ), 403 );
		}

		// Anti-bot + per-IP throttle.
		if ( ! KTB_Security::is_human( $_POST ) ) {
			wp_send_json_error( array( 'message' => __( 'Submission blocked. Please try again.', 'keytobd-booking' ) ), 400 );
		}
		if ( ! KTB_Security::throttle( 'create' ) ) {
			wp_send_json_error( array( 'message' => __( 'Too many attempts. Please wait a few minutes and try again.', 'keytobd-booking' ) ), 429 );
		}

		$service_id = isset( $_POST['service_id'] ) ? absint( $_POST['service_id'] ) : 0;
		$name       = isset( $_POST['name'] ) ? ktb_clamp( sanitize_text_field( wp_unslash( $_POST['name'] ) ), 120 ) : '';
		$email      = isset( $_POST['email'] ) ? ktb_clamp( sanitize_email( wp_unslash( $_POST['email'] ) ), 120 ) : '';
		$phone      = isset( $_POST['phone'] ) ? ktb_clamp( sanitize_text_field( wp_unslash( $_POST['phone'] ) ), 20 ) : '';
		$date       = isset( $_POST['date'] ) ? ktb_clamp( sanitize_text_field( wp_unslash( $_POST['date'] ) ), 10 ) : '';
		$date_end   = isset( $_POST['date_end'] ) ? ktb_clamp( sanitize_text_field( wp_unslash( $_POST['date_end'] ) ), 10 ) : '';
		$qty        = isset( $_POST['qty'] ) ? max( 1, absint( $_POST['qty'] ) ) : 1;
		$notes      = isset( $_POST['notes'] ) ? ktb_clamp( sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ), 2000 ) : '';
		$coupon_in  = isset( $_POST['coupon'] ) ? ktb_clamp( sanitize_text_field( wp_unslash( $_POST['coupon'] ) ), 40 ) : '';
		$agreed     = ! empty( $_POST['agree'] );

		// When logged in, bind the booking to the account's (verified) email.
		$user_id = get_current_user_id();
		if ( $user_id ) {
			$user  = wp_get_current_user();
			$email = $user->user_email;
			if ( '' === $name ) {
				$name = $user->display_name;
			}
		}

		// Validation.
		if ( ! $service_id || 'ktb_service' !== get_post_type( $service_id ) || 'publish' !== get_post_status( $service_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid service.', 'keytobd-booking' ) ) );
		}
		if ( '' === $name || '' === $phone ) {
			wp_send_json_error( array( 'message' => __( 'Name and phone are required.', 'keytobd-booking' ) ) );
		}
		if ( ! preg_match( '/^[0-9+\-\s()]{6,20}$/', $phone ) ) {
			wp_send_json_error( array( 'message' => __( 'Enter a valid phone number.', 'keytobd-booking' ) ) );
		}
		if ( $email && ! is_email( $email ) ) {
			wp_send_json_error( array( 'message' => __( 'Enter a valid email address.', 'keytobd-booking' ) ) );
		}
		if ( ! self::valid_date( $date ) ) {
			wp_send_json_error( array( 'message' => __( 'Choose a valid date.', 'keytobd-booking' ) ) );
		}
		if ( ktb_get_setting( 'require_terms' ) && ! $agreed ) {
			wp_send_json_error( array( 'message' => __( 'Please accept the terms & conditions.', 'keytobd-booking' ) ) );
		}

		$service = ktb_get_service( $service_id );

		// Date rules: past / lead-time / blackout weekday.
		$date_ok = ktb_validate_date_rules( $service, $date );
		if ( true !== $date_ok ) {
			wp_send_json_error( array( 'message' => $date_ok ) );
		}

		// Party-size bounds.
		if ( $qty < $service['min_pax'] ) {
			/* translators: %d minimum party size. */
			wp_send_json_error( array( 'message' => sprintf( __( 'Minimum %d for this service.', 'keytobd-booking' ), $service['min_pax'] ) ) );
		}
		if ( $qty > $service['max_pax'] ) {
			/* translators: %d maximum party size. */
			wp_send_json_error( array( 'message' => sprintf( __( 'Maximum %d per booking. Contact us for larger groups.', 'keytobd-booking' ), $service['max_pax'] ) ) );
		}

		// Availability re-check at submit time (race-safe enough for SMB volume).
		$remain = ktb_availability( $service_id, $date );
		if ( $remain < $qty ) {
			wp_send_json_error( array( 'message' => __( 'Sorry, that date just sold out. Try another date or quantity.', 'keytobd-booking' ) ) );
		}

		/**
		 * Extensible validation gate. Add-ons (reCAPTCHA, blocklists) return a
		 * WP_Error to reject, or true to allow.
		 *
		 * @param true|WP_Error $ok    Current result.
		 * @param array         $input Sanitized input.
		 */
		$gate = apply_filters( 'ktb_validate_booking', true, compact( 'service_id', 'name', 'email', 'phone', 'date', 'qty' ) );
		if ( is_wp_error( $gate ) ) {
			wp_send_json_error( array( 'message' => $gate->get_error_message() ) );
		}

		$units    = self::units( $service['type'], $date, $date_end );
		$subtotal = $service['price'] * $qty * $units;

		// Coupon.
		$coupon = ktb_apply_coupon( $coupon_in, $subtotal );
		$total  = max( 0, $subtotal - $coupon['discount'] );

		// Deposit.
		$deposit_pct = (float) ktb_get_setting( 'deposit_percent' );
		$deposit_due = $deposit_pct > 0 ? round( $total * min( 100, $deposit_pct ) / 100, 2 ) : 0.0;

		$ref = ktb_generate_ref();

		$booking_id = wp_insert_post( array(
			'post_type'   => 'ktb_booking',
			'post_status' => ktb_get_setting( 'auto_confirm' ) ? 'ktb-confirmed' : 'ktb-pending',
			'post_title'  => sprintf( '%s — %s', $ref, $name ),
		), true );

		if ( is_wp_error( $booking_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Could not save your booking. Please call us.', 'keytobd-booking' ) ) );
		}

		$meta = array(
			'ref'         => $ref,
			'service_id'  => $service_id,
			'name'        => $name,
			'email'       => $email,
			'phone'       => $phone,
			'date'        => $date,
			'date_end'    => self::valid_date( $date_end ) ? $date_end : '',
			'qty'         => $qty,
			'units'       => $units,
			'subtotal'    => $subtotal,
			'coupon'      => $coupon['valid'] ? $coupon['code'] : '',
			'discount'    => $coupon['discount'],
			'total'       => $total,
			'deposit_due' => $deposit_due,
			'notes'       => $notes,
			'payment'     => '',
			'user_id'     => $user_id,
			'ip'          => md5( ktb_client_ip() ),
		);
		foreach ( $meta as $k => $v ) {
			update_post_meta( $booking_id, '_ktb_' . $k, $v );
		}

		/**
		 * Fires after a booking is created (emails, CRM, etc.).
		 *
		 * @param int   $booking_id Booking post ID.
		 * @param array $meta       Booking meta.
		 */
		do_action( 'ktb_booking_created', $booking_id, $meta );

		/**
		 * Payment URL filter — a gateway add-on (SSLCommerz/bKash) can return a
		 * redirect URL here. Empty string = no online payment (pay on confirmation).
		 *
		 * @param string $url        Payment redirect URL.
		 * @param int    $booking_id Booking ID.
		 * @param array  $meta       Booking meta.
		 */
		$pay_url = apply_filters( 'ktb_payment_url', '', $booking_id, $meta );

		wp_send_json_success( array(
			'ref'         => $ref,
			'total'       => $total,
			'total_fmt'   => ktb_price( $total ),
			'discount'    => $coupon['discount'],
			'coupon'      => $coupon['valid'] ? $coupon['code'] : '',
			'deposit_due' => $deposit_due,
			'deposit_fmt' => $deposit_due > 0 ? ktb_price( $deposit_due ) : '',
			'pay_url'     => esc_url_raw( $pay_url ),
			'message'     => ktb_get_setting( 'success_msg' ),
		) );
	}

	/**
	 * Nights/days between two dates (min 1) for range services; 1 otherwise.
	 *
	 * @param string $type     Service type.
	 * @param string $date     Start Y-m-d.
	 * @param string $date_end End Y-m-d.
	 * @return int
	 */
	private static function units( $type, $date, $date_end ) {
		$types = ktb_service_types();
		$range = ! empty( $types[ $type ]['range'] );
		if ( ! $range || ! self::valid_date( $date_end ) || ! self::valid_date( $date ) ) {
			return 1;
		}
		$start = strtotime( $date );
		$end   = strtotime( $date_end );
		$nights = (int) floor( ( $end - $start ) / DAY_IN_SECONDS );
		return max( 1, $nights );
	}

	/**
	 * Validate a Y-m-d date string.
	 *
	 * @param string $date Date.
	 * @return bool
	 */
	private static function valid_date( $date ) {
		if ( ! $date ) {
			return false;
		}
		$d = DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}
}
