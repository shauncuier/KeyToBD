<?php
/**
 * Email notifications.
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Emails.
 */
class KTB_Emails {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'ktb_booking_created', array( __CLASS__, 'on_created' ), 10, 2 );
		add_action( 'ktb_booking_status_changed', array( __CLASS__, 'on_status' ), 10, 3 );
	}

	/**
	 * HTML email headers.
	 */
	private static function headers() {
		return array( 'Content-Type: text/html; charset=UTF-8' );
	}

	/**
	 * Replace {ref} / {name} tokens in a subject line.
	 *
	 * @param string $tmpl Template.
	 * @param array  $m    Booking meta.
	 * @return string
	 */
	private static function subject( $tmpl, $m ) {
		$tmpl = $tmpl ? $tmpl : __( 'Booking {ref}', 'keytobd-booking' );
		return strtr( $tmpl, array( '{ref}' => $m['ref'] ?? '', '{name}' => $m['name'] ?? '' ) );
	}

	/**
	 * Build a labelled detail table from booking meta.
	 *
	 * @param array $m Meta.
	 * @return string
	 */
	private static function table( $m ) {
		$service = ! empty( $m['service_id'] ) ? get_the_title( $m['service_id'] ) : '';
		$rows = array(
			__( 'Reference', 'keytobd-booking' ) => $m['ref'],
			__( 'Service', 'keytobd-booking' )   => $service,
			__( 'Date', 'keytobd-booking' )      => $m['date'] . ( ! empty( $m['date_end'] ) ? ' → ' . $m['date_end'] : '' ),
			__( 'Quantity', 'keytobd-booking' )  => $m['qty'],
		);
		if ( ! empty( $m['discount'] ) ) {
			$rows[ __( 'Subtotal', 'keytobd-booking' ) ] = ktb_price( $m['subtotal'] ?? $m['total'] );
			$rows[ __( 'Coupon', 'keytobd-booking' ) ]   = ( $m['coupon'] ?? '' ) . ' (−' . ktb_price( $m['discount'] ) . ')';
		}
		$rows[ __( 'Total', 'keytobd-booking' ) ] = ktb_price( $m['total'] );
		if ( ! empty( $m['deposit_due'] ) ) {
			$rows[ __( 'Deposit due', 'keytobd-booking' ) ] = ktb_price( $m['deposit_due'] );
		}
		$rows[ __( 'Name', 'keytobd-booking' ) ]  = $m['name'];
		$rows[ __( 'Phone', 'keytobd-booking' ) ] = $m['phone'];
		$rows[ __( 'Email', 'keytobd-booking' ) ] = $m['email'];
		$html = '<table style="border-collapse:collapse;width:100%;max-width:520px">';
		foreach ( $rows as $k => $v ) {
			if ( '' === $v && 0 !== $v ) {
				continue;
			}
			$html .= sprintf(
				'<tr><td style="padding:8px 10px;border-bottom:1px solid #eee;color:#555;font-weight:600">%s</td><td style="padding:8px 10px;border-bottom:1px solid #eee">%s</td></tr>',
				esc_html( $k ),
				esc_html( $v )
			);
		}
		$html .= '</table>';
		if ( ! empty( $m['notes'] ) ) {
			$html .= '<p style="margin-top:12px"><strong>' . esc_html__( 'Notes', 'keytobd-booking' ) . ':</strong><br>' . esc_html( $m['notes'] ) . '</p>';
		}
		return $html;
	}

	/**
	 * Wrap content in a simple branded shell.
	 *
	 * @param string $title Heading.
	 * @param string $body  HTML body.
	 * @return string
	 */
	private static function wrap( $title, $body ) {
		$brand = get_bloginfo( 'name' );
		ob_start();
		?>
		<div style="font-family:Arial,Helvetica,sans-serif;color:#16222E;max-width:560px;margin:0 auto">
			<div style="background:#143C5E;color:#fff;padding:18px 22px;border-radius:10px 10px 0 0">
				<strong style="font-size:18px"><?php echo esc_html( $brand ); ?></strong>
			</div>
			<div style="border:1px solid #eee;border-top:0;padding:22px;border-radius:0 0 10px 10px">
				<h2 style="margin:0 0 14px;font-size:18px"><?php echo esc_html( $title ); ?></h2>
				<?php echo $body; // phpcs:ignore WordPress.Security.EscapeOutput ?>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * On new booking: notify admin + acknowledge customer.
	 *
	 * @param int   $booking_id Booking ID.
	 * @param array $m          Meta.
	 */
	public static function on_created( $booking_id, $m ) {
		$admin = ktb_get_setting( 'admin_email', get_option( 'admin_email' ) );
		$table = self::table( $m );
		$edit  = admin_url( 'post.php?post=' . (int) $booking_id . '&action=edit' );

		// Admin.
		wp_mail(
			$admin,
			self::subject( ktb_get_setting( 'email_subject_admin' ), $m ),
			self::wrap( __( 'New booking request', 'keytobd-booking' ), $table . '<p style="margin-top:14px"><a href="' . esc_url( $edit ) . '" style="background:#FF6B35;color:#fff;padding:10px 16px;border-radius:8px;text-decoration:none">' . esc_html__( 'Manage booking', 'keytobd-booking' ) . '</a></p>' ),
			self::headers()
		);

		// Customer.
		if ( ! empty( $m['email'] ) ) {
			wp_mail(
				$m['email'],
				self::subject( ktb_get_setting( 'email_subject_customer' ), $m ),
				self::wrap(
					sprintf( __( 'Thank you, %s!', 'keytobd-booking' ), $m['name'] ),
					'<p>' . esc_html__( 'We have received your booking request and will confirm it shortly. Keep this reference for your records.', 'keytobd-booking' ) . '</p>' . $table
				),
				self::headers()
			);
		}
	}

	/**
	 * On status change: email the customer when confirmed.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $new        New status.
	 * @param string $old        Old status.
	 */
	public static function on_status( $booking_id, $new, $old ) {
		if ( 'ktb-confirmed' !== $new ) {
			return;
		}
		$email = get_post_meta( $booking_id, '_ktb_email', true );
		if ( ! $email ) {
			return;
		}
		$m = array();
		foreach ( array( 'ref', 'service_id', 'date', 'date_end', 'qty', 'total', 'name', 'phone', 'email', 'notes' ) as $k ) {
			$m[ $k ] = get_post_meta( $booking_id, '_ktb_' . $k, true );
		}
		wp_mail(
			$email,
			sprintf( __( 'Your booking %s is confirmed', 'keytobd-booking' ), $m['ref'] ),
			self::wrap(
				__( 'Booking confirmed ✅', 'keytobd-booking' ),
				'<p>' . esc_html__( 'Great news — your booking is confirmed. We look forward to hosting your trip!', 'keytobd-booking' ) . '</p>' . self::table( $m )
			),
			self::headers()
		);
	}
}
