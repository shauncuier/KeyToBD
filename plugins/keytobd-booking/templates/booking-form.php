<?php
/**
 * Booking form template. Override at theme: /keytobd-booking/booking-form.php
 *
 * @var int    $locked_service Service ID to lock to (0 = customer chooses).
 * @var string $form_title     Optional heading.
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$locked_service = isset( $locked_service ) ? (int) $locked_service : 0;
$form_title     = isset( $form_title ) ? $form_title : '';
$types          = ktb_service_types();

// Service list when not locked.
$services = array();
if ( ! $locked_service ) {
	$services = get_posts( array( 'post_type' => 'ktb_service', 'posts_per_page' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
}

$locked = $locked_service ? ktb_get_service( $locked_service ) : null;
$today  = gmdate( 'Y-m-d' );
?>
<form id="ktb-book" class="ktb-form" data-ktb-form aria-label="<?php esc_attr_e( 'Booking form', 'keytobd-booking' ); ?>">
	<?php if ( $form_title ) : ?><h3 class="ktb-form__title"><?php echo esc_html( $form_title ); ?></h3>
	<?php elseif ( ! $locked_service ) : ?><h3 class="ktb-form__title"><?php esc_html_e( 'Book your trip', 'keytobd-booking' ); ?></h3><?php endif; ?>

	<div class="ktb-form__grid">
		<?php if ( $locked ) : ?>
			<input type="hidden" name="service_id" value="<?php echo esc_attr( $locked['id'] ); ?>" data-type="<?php echo esc_attr( $locked['type'] ); ?>" data-price="<?php echo esc_attr( $locked['price'] ); ?>" data-range="<?php echo ! empty( $types[ $locked['type'] ]['range'] ) ? '1' : '0'; ?>">
			<div class="ktb-field ktb-field--full">
				<span class="ktb-locked"><?php echo esc_html( $locked['title'] ); ?> · <strong><?php echo esc_html( ktb_price( $locked['price'] ) ); ?></strong> <?php echo esc_html( $types[ $locked['type'] ]['unit'] ); ?></span>
			</div>
		<?php else : ?>
			<div class="ktb-field ktb-field--full">
				<label for="ktb-service"><?php esc_html_e( 'Service', 'keytobd-booking' ); ?> <span>*</span></label>
				<select id="ktb-service" name="service_id" required>
					<option value=""><?php esc_html_e( 'Choose a service…', 'keytobd-booking' ); ?></option>
					<?php foreach ( $services as $s ) :
						$cfg = ktb_get_service( $s->ID ); ?>
						<option value="<?php echo esc_attr( $s->ID ); ?>"
							data-type="<?php echo esc_attr( $cfg['type'] ); ?>"
							data-price="<?php echo esc_attr( $cfg['price'] ); ?>"
							data-range="<?php echo ! empty( $types[ $cfg['type'] ]['range'] ) ? '1' : '0'; ?>">
							<?php echo esc_html( $s->post_title . ' — ' . ktb_price( $cfg['price'] ) ); ?>
						</option>
					<?php endforeach; ?>
				</select>
			</div>
		<?php endif; ?>

		<div class="ktb-field">
			<label for="ktb-date"><?php esc_html_e( 'Date', 'keytobd-booking' ); ?> <span>*</span></label>
			<input type="date" id="ktb-date" name="date" min="<?php echo esc_attr( $today ); ?>" required>
		</div>

		<div class="ktb-field ktb-field--end" data-ktb-range hidden>
			<label for="ktb-date-end"><?php esc_html_e( 'End date', 'keytobd-booking' ); ?></label>
			<input type="date" id="ktb-date-end" name="date_end" min="<?php echo esc_attr( $today ); ?>">
		</div>

		<div class="ktb-field">
			<label for="ktb-qty" data-ktb-qty-label><?php esc_html_e( 'Quantity', 'keytobd-booking' ); ?> <span>*</span></label>
			<input type="number" id="ktb-qty" name="qty" min="1" value="1" required>
		</div>

		<div class="ktb-field">
			<label for="ktb-name"><?php esc_html_e( 'Full name', 'keytobd-booking' ); ?> <span>*</span></label>
			<input type="text" id="ktb-name" name="name" required>
		</div>
		<div class="ktb-field">
			<label for="ktb-phone"><?php esc_html_e( 'Phone', 'keytobd-booking' ); ?> <span>*</span></label>
			<input type="tel" id="ktb-phone" name="phone" required>
		</div>
		<div class="ktb-field">
			<label for="ktb-email"><?php esc_html_e( 'Email', 'keytobd-booking' ); ?></label>
			<input type="email" id="ktb-email" name="email">
		</div>

		<div class="ktb-field">
			<label for="ktb-coupon"><?php esc_html_e( 'Coupon code (optional)', 'keytobd-booking' ); ?></label>
			<input type="text" id="ktb-coupon" name="coupon" autocomplete="off" placeholder="<?php esc_attr_e( 'e.g. EID10', 'keytobd-booking' ); ?>">
		</div>

		<div class="ktb-field ktb-field--full">
			<label for="ktb-notes"><?php esc_html_e( 'Notes (optional)', 'keytobd-booking' ); ?></label>
			<textarea id="ktb-notes" name="notes" rows="3" placeholder="<?php esc_attr_e( 'Pickup point, special requests…', 'keytobd-booking' ); ?>"></textarea>
		</div>
	</div>

	<?php if ( ktb_get_setting( 'require_terms' ) ) :
		$terms_url = ktb_get_setting( 'terms_url' );
		?>
		<label class="ktb-terms">
			<input type="checkbox" name="agree" value="1" required>
			<?php if ( $terms_url ) : ?>
				<?php printf( wp_kses( __( 'I agree to the <a href="%s" target="_blank" rel="noopener">terms &amp; conditions</a>.', 'keytobd-booking' ), array( 'a' => array( 'href' => array(), 'target' => array(), 'rel' => array() ) ) ), esc_url( $terms_url ) ); ?>
			<?php else : ?>
				<?php esc_html_e( 'I agree to the terms & conditions.', 'keytobd-booking' ); ?>
			<?php endif; ?>
		</label>
	<?php endif; ?>

	<?php // Anti-bot: salt-named honeypot (must stay empty) + signed form-open token. ?>
	<div class="ktb-hp" aria-hidden="true" style="position:absolute;left:-9999px;top:-9999px;height:0;overflow:hidden;">
		<label><?php esc_html_e( 'Website', 'keytobd-booking' ); ?><input type="text" name="<?php echo esc_attr( KTB_Security::hp_field() ); ?>" tabindex="-1" autocomplete="off"></label>
	</div>
	<input type="hidden" name="ktb_t" value="<?php echo esc_attr( KTB_Security::time_token() ); ?>">

	<div class="ktb-summary" data-ktb-summary hidden>
		<span class="ktb-summary__label"><?php esc_html_e( 'Estimated total', 'keytobd-booking' ); ?></span>
		<span class="ktb-summary__total" data-ktb-total>—</span>
		<span class="ktb-summary__avail" data-ktb-avail></span>
	</div>

	<?php $ts_site = ktb_get_setting( 'turnstile_site' ); ?>
	<?php if ( $ts_site ) : ?>
		<div class="cf-turnstile" data-sitekey="<?php echo esc_attr( $ts_site ); ?>" style="margin:14px 0;"></div>
		<?php wp_enqueue_script( 'ktb-turnstile', 'https://challenges.cloudflare.com/turnstile/v0/api.js', array(), null, true ); ?>
	<?php endif; ?>

	<button type="submit" class="ktb-submit"><?php esc_html_e( 'Request Booking', 'keytobd-booking' ); ?></button>
	<p class="ktb-msg" data-ktb-msg role="status" aria-live="polite" hidden></p>
</form>
