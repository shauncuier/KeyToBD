<?php
/**
 * Shortcodes: booking form + services grid.
 *
 * [ktb_booking_form]              — full form, customer picks a service.
 * [ktb_booking_form service="12"] — locked to one service (use on a service page).
 * [ktb_services type="tour" count="6"] — grid of bookable services.
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcodes.
 */
class KTB_Shortcodes {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_shortcode( 'ktb_booking_form', array( __CLASS__, 'form' ) );
		add_shortcode( 'ktb_services', array( __CLASS__, 'services' ) );
		add_shortcode( 'ktb_booking_lookup', array( __CLASS__, 'lookup' ) );
		add_filter( 'the_content', array( __CLASS__, 'append_form_to_service' ) );
	}

	/**
	 * Booking lookup shortcode — customer checks status by reference + phone.
	 *
	 * @return string
	 */
	public static function lookup() {
		KTB_Plugin::need_assets();
		ob_start();
		?>
		<form class="ktb-form ktb-lookup" data-ktb-lookup aria-label="<?php esc_attr_e( 'Booking lookup', 'keytobd-booking' ); ?>">
			<h3 class="ktb-form__title"><?php esc_html_e( 'Check your booking', 'keytobd-booking' ); ?></h3>
			<div class="ktb-form__grid">
				<div class="ktb-field">
					<label for="ktb-lk-ref"><?php esc_html_e( 'Booking reference', 'keytobd-booking' ); ?></label>
					<input type="text" id="ktb-lk-ref" name="ref" placeholder="KTB-XXXXXX" required>
				</div>
				<div class="ktb-field">
					<label for="ktb-lk-phone"><?php esc_html_e( 'Phone', 'keytobd-booking' ); ?></label>
					<input type="tel" id="ktb-lk-phone" name="phone" required>
				</div>
			</div>
			<button type="submit" class="ktb-submit"><?php esc_html_e( 'Find my booking', 'keytobd-booking' ); ?></button>
			<div class="ktb-lookup__result" data-ktb-lookup-result hidden></div>
			<p class="ktb-msg" data-ktb-msg role="status" aria-live="polite" hidden></p>
		</form>
		<?php
		return ob_get_clean();
	}

	/**
	 * Booking form shortcode.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function form( $atts ) {
		$atts = shortcode_atts( array( 'service' => 0, 'title' => '' ), $atts, 'ktb_booking_form' );
		KTB_Plugin::need_assets();

		ob_start();
		ktb_template( 'booking-form.php', array(
			'locked_service' => (int) $atts['service'],
			'form_title'     => $atts['title'],
		) );
		return ob_get_clean();
	}

	/**
	 * Services grid shortcode.
	 *
	 * @param array $atts Attributes.
	 * @return string
	 */
	public static function services( $atts ) {
		$atts = shortcode_atts( array( 'type' => '', 'count' => 6, 'columns' => 3 ), $atts, 'ktb_services' );
		KTB_Plugin::need_assets();

		$meta = array();
		if ( $atts['type'] ) {
			$meta[] = array( 'key' => '_ktb_type', 'value' => sanitize_key( $atts['type'] ) );
		}

		$q = new WP_Query( array(
			'post_type'      => 'ktb_service',
			'posts_per_page' => (int) $atts['count'],
			'meta_query'     => $meta ? $meta : null,
		) );

		ob_start();
		if ( $q->have_posts() ) {
			echo '<div class="ktb-services" style="--cols:' . (int) $atts['columns'] . '">';
			while ( $q->have_posts() ) {
				$q->the_post();
				ktb_template( 'service-card.php', array( 'service' => ktb_get_service( get_the_ID() ) ) );
			}
			echo '</div>';
			wp_reset_postdata();
		} else {
			echo '<p>' . esc_html__( 'No services found.', 'keytobd-booking' ) . '</p>';
		}
		return ob_get_clean();
	}

	/**
	 * Auto-append the booking form on single service pages.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function append_form_to_service( $content ) {
		if ( is_singular( 'ktb_service' ) && in_the_loop() && is_main_query() && false === strpos( $content, 'ktb-form' ) ) {
			$content .= self::form( array( 'service' => get_the_ID() ) );
		}
		return $content;
	}
}
