<?php
/**
 * Shared helper functions.
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default settings — single source of truth.
 *
 * @return array<string,mixed>
 */
function ktb_default_settings() {
	return array(
		'currency'       => '৳',
		'admin_email'    => get_option( 'admin_email' ),
		'auto_confirm'   => 0,
		'success_msg'    => __( 'Thank you! Your booking request has been received. We will confirm shortly.', 'keytobd-booking' ),
		// Flexibility.
		'deposit_percent'=> 0,        // 0 = full payment; else % deposit.
		'min_lead_days'  => 0,        // earliest bookable = today + N days.
		'max_party'      => 20,       // hard cap on qty per booking.
		'terms_url'      => '',
		'require_terms'  => 0,
		'coupons'        => '',       // one per line: CODE|percent|10  or  CODE|fixed|500.
		'email_subject_admin'    => __( 'New booking {ref} — {name}', 'keytobd-booking' ),
		'email_subject_customer' => __( 'We received your booking {ref}', 'keytobd-booking' ),
		// Security.
		'rl_count'       => 5,        // max submissions...
		'rl_window'      => 600,      // ...per N seconds per IP.
		'honeypot'       => 1,
		'min_seconds'    => 3,        // form must be open at least N seconds.
		'turnstile_site'   => '',     // Cloudflare Turnstile site key (optional).
		'turnstile_secret' => '',     // Cloudflare Turnstile secret key (optional).
	);
}

/**
 * Get a plugin setting (falls back to registered default).
 *
 * @param string $key     Setting key.
 * @param mixed  $default Explicit fallback (else uses ktb_default_settings()).
 * @return mixed
 */
function ktb_get_setting( $key, $default = null ) {
	$settings = get_option( 'ktb_settings', array() );
	if ( isset( $settings[ $key ] ) && '' !== $settings[ $key ] ) {
		return $settings[ $key ];
	}
	if ( null !== $default ) {
		return $default;
	}
	$defaults = ktb_default_settings();
	return $defaults[ $key ] ?? '';
}

/**
 * Resolve the client IP for rate-limiting.
 *
 * SECURITY: `REMOTE_ADDR` is the only value an attacker cannot spoof on a direct
 * request to the origin, so it is the default. Forwarded headers (X-Forwarded-For,
 * CF-Connecting-IP) are honoured ONLY when the site explicitly opts in by defining
 * `KTB_TRUSTED_PROXY` (true) — i.e. the origin really sits behind a trusted proxy/
 * CDN that overwrites those headers. When trusted, we take the LAST hop of XFF
 * (the one the trusted proxy appended), not the first (client-controlled) entry.
 *
 * @return string
 */
function ktb_client_ip() {
	$remote = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
	$ip     = filter_var( $remote, FILTER_VALIDATE_IP ) ? $remote : '0.0.0.0';

	if ( defined( 'KTB_TRUSTED_PROXY' ) && KTB_TRUSTED_PROXY ) {
		if ( ! empty( $_SERVER['HTTP_CF_CONNECTING_IP'] ) ) {
			$cf = sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_CONNECTING_IP'] ) );
			if ( filter_var( $cf, FILTER_VALIDATE_IP ) ) {
				$ip = $cf;
			}
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$hops = array_map( 'trim', explode( ',', sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) ) );
			$last = end( $hops ); // The hop appended by our own trusted proxy.
			if ( filter_var( $last, FILTER_VALIDATE_IP ) ) {
				$ip = $last;
			}
		}
	}

	/**
	 * Filter the resolved client IP (for bespoke proxy/CDN setups).
	 *
	 * @param string $ip Resolved IP.
	 */
	return (string) apply_filters( 'ktb_client_ip', $ip );
}

/**
 * Parse the coupons setting into a map.
 *
 * @return array<string,array{type:string,amount:float}>
 */
function ktb_coupons() {
	$raw  = (string) ktb_get_setting( 'coupons' );
	$out  = array();
	foreach ( preg_split( '/\r\n|\r|\n/', $raw ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line ) );
		if ( count( $parts ) < 3 ) {
			continue;
		}
		$code = strtoupper( $parts[0] );
		$type = 'fixed' === strtolower( $parts[1] ) ? 'fixed' : 'percent';
		$out[ $code ] = array( 'type' => $type, 'amount' => (float) $parts[2] );
	}
	return $out;
}

/**
 * Compute a coupon discount against a subtotal.
 *
 * @param string $code     Raw code.
 * @param float  $subtotal Subtotal.
 * @return array{valid:bool,discount:float,code:string,label:string}
 */
function ktb_apply_coupon( $code, $subtotal ) {
	$code = strtoupper( trim( (string) $code ) );
	$res  = array( 'valid' => false, 'discount' => 0.0, 'code' => '', 'label' => '' );
	if ( '' === $code ) {
		return $res;
	}
	$coupons = ktb_coupons();
	if ( ! isset( $coupons[ $code ] ) ) {
		return $res;
	}
	$c = $coupons[ $code ];
	if ( 'percent' === $c['type'] ) {
		$discount = $subtotal * min( 100, max( 0, $c['amount'] ) ) / 100;
		$label    = rtrim( rtrim( (string) $c['amount'], '0' ), '.' ) . '% off';
	} else {
		$discount = min( $subtotal, max( 0, $c['amount'] ) );
		$label    = ktb_price( $c['amount'] ) . ' off';
	}
	return array( 'valid' => true, 'discount' => round( $discount, 2 ), 'code' => $code, 'label' => $label );
}

/**
 * Currency symbol.
 */
function ktb_currency() {
	return ktb_get_setting( 'currency', '৳' );
}

/**
 * Format a money amount.
 *
 * @param float $amount Amount.
 * @return string
 */
function ktb_price( $amount ) {
	return ktb_currency() . number_format_i18n( (float) $amount, 0 );
}

/**
 * Service types and the label/unit each uses.
 *
 * @return array<string,array<string,string>>
 */
function ktb_service_types() {
	return array(
		'tour'      => array( 'label' => __( 'Tour Package', 'keytobd-booking' ), 'unit' => __( 'per person', 'keytobd-booking' ), 'qty' => __( 'Travellers', 'keytobd-booking' ), 'range' => false ),
		'hotel'     => array( 'label' => __( 'Hotel / Resort', 'keytobd-booking' ), 'unit' => __( 'per night', 'keytobd-booking' ), 'qty' => __( 'Rooms', 'keytobd-booking' ), 'range' => true ),
		'car'       => array( 'label' => __( 'Rent-A-Car', 'keytobd-booking' ), 'unit' => __( 'per day', 'keytobd-booking' ), 'qty' => __( 'Vehicles', 'keytobd-booking' ), 'range' => true ),
		'ship'      => array( 'label' => __( 'Ship Ticket', 'keytobd-booking' ), 'unit' => __( 'per seat', 'keytobd-booking' ), 'qty' => __( 'Seats', 'keytobd-booking' ), 'range' => false ),
		'houseboat' => array( 'label' => __( 'Houseboat', 'keytobd-booking' ), 'unit' => __( 'per night', 'keytobd-booking' ), 'qty' => __( 'Guests', 'keytobd-booking' ), 'range' => true ),
	);
}

/**
 * Booking statuses (post status => label).
 *
 * @return array<string,string>
 */
function ktb_statuses() {
	return array(
		'ktb-pending'   => __( 'Pending', 'keytobd-booking' ),
		'ktb-confirmed' => __( 'Confirmed', 'keytobd-booking' ),
		'ktb-cancelled' => __( 'Cancelled', 'keytobd-booking' ),
		'ktb-completed' => __( 'Completed', 'keytobd-booking' ),
	);
}

/**
 * Read service config meta.
 *
 * @param int $service_id Service post ID.
 * @return array<string,mixed>
 */
function ktb_get_service( $service_id ) {
	$type = get_post_meta( $service_id, '_ktb_type', true );
	$rating  = (float) get_post_meta( $service_id, '_ktb_rating', true );
	$reviews = (int) get_post_meta( $service_id, '_ktb_reviews', true );
	$min_pax = (int) get_post_meta( $service_id, '_ktb_min_pax', true );
	$max_pax = (int) get_post_meta( $service_id, '_ktb_max_pax', true );
	$lead    = get_post_meta( $service_id, '_ktb_lead_days', true );
	$black   = get_post_meta( $service_id, '_ktb_blackout', true );
	return array(
		'id'        => (int) $service_id,
		'title'     => get_the_title( $service_id ),
		'type'      => $type ? $type : 'tour',
		'price'     => (float) get_post_meta( $service_id, '_ktb_price', true ),
		'capacity'  => (int) get_post_meta( $service_id, '_ktb_capacity', true ),
		'location'  => get_post_meta( $service_id, '_ktb_location', true ),
		'duration'  => get_post_meta( $service_id, '_ktb_duration', true ),
		'rating'    => $rating > 0 ? $rating : 4.8,
		'reviews'   => $reviews,
		'min_pax'   => $min_pax > 0 ? $min_pax : 1,
		'max_pax'   => $max_pax > 0 ? $max_pax : (int) ktb_get_setting( 'max_party' ),
		'lead_days' => '' !== $lead ? (int) $lead : (int) ktb_get_setting( 'min_lead_days' ),
		'blackout'  => is_array( $black ) ? array_map( 'intval', $black ) : array(),
	);
}

/**
 * Sum of booked quantity for a service on a given date (active statuses only).
 *
 * @param int    $service_id Service ID.
 * @param string $date       Y-m-d.
 * @return int
 */
function ktb_booked_qty( $service_id, $date ) {
	$ids = get_posts( array(
		'post_type'      => 'ktb_booking',
		'post_status'    => array( 'ktb-pending', 'ktb-confirmed' ),
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'meta_query'     => array(
			'relation' => 'AND',
			array( 'key' => '_ktb_service_id', 'value' => (int) $service_id ),
			array( 'key' => '_ktb_date', 'value' => $date ),
		),
	) );

	$qty = 0;
	foreach ( $ids as $id ) {
		$qty += (int) get_post_meta( $id, '_ktb_qty', true );
	}
	return $qty;
}

/**
 * Remaining availability for a service on a date. Capacity 0 = unlimited.
 *
 * @param int    $service_id Service ID.
 * @param string $date       Y-m-d.
 * @return int  Remaining slots; PHP_INT_MAX when unlimited.
 */
function ktb_availability( $service_id, $date ) {
	$capacity = (int) get_post_meta( $service_id, '_ktb_capacity', true );
	if ( $capacity <= 0 ) {
		return PHP_INT_MAX;
	}
	return max( 0, $capacity - ktb_booked_qty( $service_id, $date ) );
}

/**
 * Neutralize a value for CSV output (anti formula/CSV-injection).
 * Prefixes cells that begin with =, +, -, @, tab or CR/LF with a single quote so
 * spreadsheet apps treat them as text, and strips other control characters.
 *
 * @param mixed $value Raw cell value.
 * @return string
 */
function ktb_csv_cell( $value ) {
	$value = (string) $value;
	// Drop control chars except normal whitespace handled below.
	$value = preg_replace( '/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $value );
	if ( '' !== $value && in_array( $value[0], array( '=', '+', '-', '@', "\t", "\r", "\n" ), true ) ) {
		$value = "'" . $value;
	}
	return $value;
}

/**
 * Clamp a string to a max length (multibyte-safe) after trimming.
 *
 * @param string $value Value.
 * @param int    $max   Max characters.
 * @return string
 */
function ktb_clamp( $value, $max ) {
	$value = trim( (string) $value );
	return function_exists( 'mb_substr' ) ? mb_substr( $value, 0, $max ) : substr( $value, 0, $max );
}

/**
 * Validate a requested date against a service's lead-time and blackout weekdays.
 *
 * @param array  $service ktb_get_service() result.
 * @param string $date    Y-m-d.
 * @return true|string  True if OK, else an error message.
 */
function ktb_validate_date_rules( $service, $date ) {
	$ts = strtotime( $date . ' 00:00:00' );
	if ( ! $ts ) {
		return __( 'Choose a valid date.', 'keytobd-booking' );
	}
	$today = strtotime( gmdate( 'Y-m-d' ) . ' 00:00:00' );
	if ( $ts < $today ) {
		return __( 'That date is in the past.', 'keytobd-booking' );
	}
	$lead = (int) ( $service['lead_days'] ?? 0 );
	if ( $lead > 0 && $ts < strtotime( "+{$lead} day", $today ) ) {
		/* translators: %d: lead days. */
		return sprintf( _n( 'This service must be booked at least %d day ahead.', 'This service must be booked at least %d days ahead.', $lead, 'keytobd-booking' ), $lead );
	}
	$blackout = $service['blackout'] ?? array();
	if ( $blackout && in_array( (int) gmdate( 'w', $ts ), $blackout, true ) ) {
		return __( 'This service is not available on that weekday. Please pick another date.', 'keytobd-booking' );
	}
	return true;
}

/**
 * Generate a unique booking reference.
 *
 * @return string
 */
function ktb_generate_ref() {
	return 'KTB-' . strtoupper( wp_generate_password( 6, false, false ) );
}

/**
 * Locate a template, allowing theme overrides at /keytobd-booking/{name}.
 *
 * @param string $name Template filename.
 * @param array  $args Variables to expose.
 */
function ktb_template( $name, $args = array() ) {
	$override = locate_template( 'keytobd-booking/' . $name );
	$file     = $override ? $override : KTB_DIR . 'templates/' . $name;
	if ( ! file_exists( $file ) ) {
		return;
	}
	if ( $args ) {
		extract( $args, EXTR_SKIP ); // phpcs:ignore WordPress.PHP.DontExtract
	}
	include $file;
}
