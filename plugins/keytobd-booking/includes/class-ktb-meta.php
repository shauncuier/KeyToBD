<?php
/**
 * Meta boxes for services (config) and bookings (details).
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Meta boxes.
 */
class KTB_Meta {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'add_meta_boxes', array( __CLASS__, 'add' ) );
		add_action( 'save_post_ktb_service', array( __CLASS__, 'save_service' ) );
		add_action( 'save_post_ktb_booking', array( __CLASS__, 'save_booking' ) );
	}

	/**
	 * Register boxes.
	 */
	public static function add() {
		add_meta_box( 'ktb_service_cfg', __( 'Booking Configuration', 'keytobd-booking' ), array( __CLASS__, 'service_box' ), 'ktb_service', 'normal', 'high' );
		add_meta_box( 'ktb_booking_dtl', __( 'Booking Details', 'keytobd-booking' ), array( __CLASS__, 'booking_box' ), 'ktb_booking', 'normal', 'high' );
	}

	/**
	 * Service config fields.
	 *
	 * @param WP_Post $post Service.
	 */
	public static function service_box( $post ) {
		wp_nonce_field( 'ktb_service_save', 'ktb_service_nonce' );
		$type     = get_post_meta( $post->ID, '_ktb_type', true );
		$price    = get_post_meta( $post->ID, '_ktb_price', true );
		$capacity = get_post_meta( $post->ID, '_ktb_capacity', true );
		$location = get_post_meta( $post->ID, '_ktb_location', true );
		$duration = get_post_meta( $post->ID, '_ktb_duration', true );
		?>
		<style>.ktb-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}.ktb-grid label{display:block;font-weight:600;margin-bottom:4px}.ktb-grid input,.ktb-grid select{width:100%}</style>
		<div class="ktb-grid">
			<p>
				<label for="ktb_type"><?php esc_html_e( 'Service type', 'keytobd-booking' ); ?></label>
				<select id="ktb_type" name="ktb_type">
					<?php foreach ( ktb_service_types() as $key => $t ) : ?>
						<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $type, $key ); ?>><?php echo esc_html( $t['label'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="ktb_price"><?php printf( esc_html__( 'Price (%s)', 'keytobd-booking' ), esc_html( ktb_currency() ) ); ?></label>
				<input type="number" id="ktb_price" name="ktb_price" min="0" step="0.01" value="<?php echo esc_attr( $price ); ?>">
			</p>
			<p>
				<label for="ktb_capacity"><?php esc_html_e( 'Capacity per date (0 = unlimited)', 'keytobd-booking' ); ?></label>
				<input type="number" id="ktb_capacity" name="ktb_capacity" min="0" step="1" value="<?php echo esc_attr( $capacity ); ?>">
			</p>
			<p>
				<label for="ktb_duration"><?php esc_html_e( 'Duration (e.g. 3 Days / 2 Nights)', 'keytobd-booking' ); ?></label>
				<input type="text" id="ktb_duration" name="ktb_duration" value="<?php echo esc_attr( $duration ); ?>">
			</p>
			<p>
				<label for="ktb_rating"><?php esc_html_e( 'Rating (0–5, blank = 4.8)', 'keytobd-booking' ); ?></label>
				<input type="number" id="ktb_rating" name="ktb_rating" min="0" max="5" step="0.1" value="<?php echo esc_attr( get_post_meta( $post->ID, '_ktb_rating', true ) ); ?>">
			</p>
			<p>
				<label for="ktb_reviews"><?php esc_html_e( 'Review count', 'keytobd-booking' ); ?></label>
				<input type="number" id="ktb_reviews" name="ktb_reviews" min="0" step="1" value="<?php echo esc_attr( get_post_meta( $post->ID, '_ktb_reviews', true ) ); ?>">
			</p>
			<p>
				<label for="ktb_min_pax"><?php esc_html_e( 'Min party size', 'keytobd-booking' ); ?></label>
				<input type="number" id="ktb_min_pax" name="ktb_min_pax" min="1" step="1" value="<?php echo esc_attr( get_post_meta( $post->ID, '_ktb_min_pax', true ) ); ?>" placeholder="1">
			</p>
			<p>
				<label for="ktb_max_pax"><?php esc_html_e( 'Max party size', 'keytobd-booking' ); ?></label>
				<input type="number" id="ktb_max_pax" name="ktb_max_pax" min="1" step="1" value="<?php echo esc_attr( get_post_meta( $post->ID, '_ktb_max_pax', true ) ); ?>" placeholder="<?php echo esc_attr( ktb_get_setting( 'max_party' ) ); ?>">
			</p>
			<p>
				<label for="ktb_lead_days"><?php esc_html_e( 'Min lead time (days)', 'keytobd-booking' ); ?></label>
				<input type="number" id="ktb_lead_days" name="ktb_lead_days" min="0" step="1" value="<?php echo esc_attr( get_post_meta( $post->ID, '_ktb_lead_days', true ) ); ?>" placeholder="0">
			</p>
			<p style="grid-column:1/-1">
				<label><?php esc_html_e( 'Unavailable weekdays (blackout)', 'keytobd-booking' ); ?></label>
				<?php
				$black = get_post_meta( $post->ID, '_ktb_blackout', true );
				$black = is_array( $black ) ? array_map( 'intval', $black ) : array();
				$days  = array( __( 'Sun', 'keytobd-booking' ), __( 'Mon', 'keytobd-booking' ), __( 'Tue', 'keytobd-booking' ), __( 'Wed', 'keytobd-booking' ), __( 'Thu', 'keytobd-booking' ), __( 'Fri', 'keytobd-booking' ), __( 'Sat', 'keytobd-booking' ) );
				echo '<span style="display:flex;gap:14px;flex-wrap:wrap">';
				foreach ( $days as $i => $d ) {
					printf(
						'<label style="font-weight:400"><input type="checkbox" name="ktb_blackout[]" value="%d" %s> %s</label>',
						(int) $i,
						checked( in_array( $i, $black, true ), true, false ),
						esc_html( $d )
					);
				}
				echo '</span>';
				?>
			</p>
			<p style="grid-column:1/-1">
				<label for="ktb_location"><?php esc_html_e( 'Location / route', 'keytobd-booking' ); ?></label>
				<input type="text" id="ktb_location" name="ktb_location" value="<?php echo esc_attr( $location ); ?>" placeholder="<?php esc_attr_e( "Cox's Bazar", 'keytobd-booking' ); ?>">
			</p>
		</div>
		<p class="description"><?php esc_html_e( 'Set the price unit by service type: tour = per person, hotel/houseboat = per night, car = per day, ship = per seat. Featured image and description below appear on the service page and cards.', 'keytobd-booking' ); ?></p>
		<?php
	}

	/**
	 * Save service config.
	 *
	 * @param int $post_id Service ID.
	 */
	public static function save_service( $post_id ) {
		if ( ! isset( $_POST['ktb_service_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ktb_service_nonce'] ), 'ktb_service_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		$types = array_keys( ktb_service_types() );
		$type  = isset( $_POST['ktb_type'] ) ? sanitize_key( $_POST['ktb_type'] ) : 'tour';
		update_post_meta( $post_id, '_ktb_type', in_array( $type, $types, true ) ? $type : 'tour' );
		update_post_meta( $post_id, '_ktb_price', isset( $_POST['ktb_price'] ) ? (float) $_POST['ktb_price'] : 0 );
		update_post_meta( $post_id, '_ktb_capacity', isset( $_POST['ktb_capacity'] ) ? absint( $_POST['ktb_capacity'] ) : 0 );
		update_post_meta( $post_id, '_ktb_duration', isset( $_POST['ktb_duration'] ) ? sanitize_text_field( wp_unslash( $_POST['ktb_duration'] ) ) : '' );
		update_post_meta( $post_id, '_ktb_location', isset( $_POST['ktb_location'] ) ? sanitize_text_field( wp_unslash( $_POST['ktb_location'] ) ) : '' );
		update_post_meta( $post_id, '_ktb_rating', isset( $_POST['ktb_rating'] ) && '' !== $_POST['ktb_rating'] ? min( 5, max( 0, (float) $_POST['ktb_rating'] ) ) : '' );
		update_post_meta( $post_id, '_ktb_reviews', isset( $_POST['ktb_reviews'] ) ? absint( $_POST['ktb_reviews'] ) : 0 );
		update_post_meta( $post_id, '_ktb_min_pax', isset( $_POST['ktb_min_pax'] ) ? absint( $_POST['ktb_min_pax'] ) : 0 );
		update_post_meta( $post_id, '_ktb_max_pax', isset( $_POST['ktb_max_pax'] ) ? absint( $_POST['ktb_max_pax'] ) : 0 );
		update_post_meta( $post_id, '_ktb_lead_days', isset( $_POST['ktb_lead_days'] ) && '' !== $_POST['ktb_lead_days'] ? absint( $_POST['ktb_lead_days'] ) : '' );
		$blackout = isset( $_POST['ktb_blackout'] ) && is_array( $_POST['ktb_blackout'] ) ? array_map( 'absint', wp_unslash( $_POST['ktb_blackout'] ) ) : array();
		update_post_meta( $post_id, '_ktb_blackout', array_values( array_filter( $blackout, function ( $d ) { return $d >= 0 && $d <= 6; } ) ) );
	}

	/**
	 * Booking details (read-mostly, editable by admin).
	 *
	 * @param WP_Post $post Booking.
	 */
	public static function booking_box( $post ) {
		wp_nonce_field( 'ktb_booking_save', 'ktb_booking_nonce' );
		$f = function ( $k ) use ( $post ) {
			return get_post_meta( $post->ID, '_ktb_' . $k, true );
		};
		$service_id = (int) $f( 'service_id' );
		?>
		<style>.ktb-b table{width:100%;border-collapse:collapse}.ktb-b td{padding:8px 6px;border-bottom:1px solid #eee;vertical-align:top}.ktb-b td:first-child{width:160px;color:#555;font-weight:600}</style>
		<div class="ktb-b">
			<table>
				<tr><td><?php esc_html_e( 'Reference', 'keytobd-booking' ); ?></td><td><strong><?php echo esc_html( $f( 'ref' ) ); ?></strong></td></tr>
				<tr><td><?php esc_html_e( 'Service', 'keytobd-booking' ); ?></td><td><?php echo $service_id ? esc_html( get_the_title( $service_id ) ) : '—'; ?></td></tr>
				<tr><td><?php esc_html_e( 'Customer', 'keytobd-booking' ); ?></td><td><?php echo esc_html( $f( 'name' ) ); ?></td></tr>
				<tr><td><?php esc_html_e( 'Phone', 'keytobd-booking' ); ?></td><td><a href="tel:<?php echo esc_attr( $f( 'phone' ) ); ?>"><?php echo esc_html( $f( 'phone' ) ); ?></a></td></tr>
				<tr><td><?php esc_html_e( 'Email', 'keytobd-booking' ); ?></td><td><a href="mailto:<?php echo esc_attr( $f( 'email' ) ); ?>"><?php echo esc_html( $f( 'email' ) ); ?></a></td></tr>
				<tr><td><?php esc_html_e( 'Date', 'keytobd-booking' ); ?></td><td><?php echo esc_html( $f( 'date' ) ); ?><?php echo $f( 'date_end' ) ? ' → ' . esc_html( $f( 'date_end' ) ) : ''; ?></td></tr>
				<tr><td><?php esc_html_e( 'Quantity', 'keytobd-booking' ); ?></td><td><?php echo esc_html( $f( 'qty' ) ); ?></td></tr>
				<tr><td><?php esc_html_e( 'Total', 'keytobd-booking' ); ?></td><td><strong><?php echo esc_html( ktb_price( $f( 'total' ) ) ); ?></strong></td></tr>
				<tr><td><?php esc_html_e( 'Payment', 'keytobd-booking' ); ?></td><td><?php echo esc_html( $f( 'payment' ) ? $f( 'payment' ) : __( 'Unpaid', 'keytobd-booking' ) ); ?></td></tr>
				<tr><td><?php esc_html_e( 'Notes', 'keytobd-booking' ); ?></td><td><?php echo esc_html( $f( 'notes' ) ); ?></td></tr>
			</table>
			<p style="margin-top:12px">
				<label for="ktb_status"><strong><?php esc_html_e( 'Status', 'keytobd-booking' ); ?></strong></label><br>
				<select id="ktb_status" name="ktb_status">
					<?php foreach ( ktb_statuses() as $st => $label ) : ?>
						<option value="<?php echo esc_attr( $st ); ?>" <?php selected( $post->post_status, $st ); ?>><?php echo esc_html( $label ); ?></option>
					<?php endforeach; ?>
				</select>
				<span class="description"><?php esc_html_e( 'Changing to Confirmed emails the customer.', 'keytobd-booking' ); ?></span>
			</p>
		</div>
		<?php
	}

	/**
	 * Save booking status change from the admin box.
	 *
	 * @param int $post_id Booking ID.
	 */
	public static function save_booking( $post_id ) {
		if ( ! isset( $_POST['ktb_booking_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['ktb_booking_nonce'] ), 'ktb_booking_save' ) ) {
			return;
		}
		if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		if ( empty( $_POST['ktb_status'] ) ) {
			return;
		}
		$new = sanitize_key( $_POST['ktb_status'] );
		if ( ! array_key_exists( $new, ktb_statuses() ) ) {
			return;
		}
		$current = get_post_status( $post_id );
		if ( $new === $current ) {
			return;
		}

		// Avoid recursion on wp_update_post.
		remove_action( 'save_post_ktb_booking', array( __CLASS__, 'save_booking' ) );
		wp_update_post( array( 'ID' => $post_id, 'post_status' => $new ) );
		add_action( 'save_post_ktb_booking', array( __CLASS__, 'save_booking' ) );

		do_action( 'ktb_booking_status_changed', $post_id, $new, $current );
	}
}
