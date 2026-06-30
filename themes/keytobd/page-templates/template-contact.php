<?php
/**
 * Template Name: Contact
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();
$kt = keytobd_contact();

get_template_part( 'template-parts/page-hero', null, array(
	'title'    => get_the_title() ? get_the_title() : __( 'Contact Us', 'keytobd' ),
	'subtitle' => __( "We're here 24/7 — call, WhatsApp, or send us a message.", 'keytobd' ),
	'crumbs'   => array( __( 'Contact', 'keytobd' ) => '' ),
) );
?>
<main id="content" class="site-main">
	<div class="container">
		<div class="contact-grid">
			<div class="contact-info">
				<div class="contact-info__item"><?php keytobd_icon( 'map', 22 ); ?><div><strong><?php esc_html_e( 'Office', 'keytobd' ); ?></strong><?php echo esc_html( $kt['address'] ); ?></div></div>
				<div class="contact-info__item"><?php keytobd_icon( 'phone', 22 ); ?><div><strong><?php esc_html_e( 'Phone', 'keytobd' ); ?></strong><a href="<?php echo esc_attr( keytobd_tel( $kt['phone1'] ) ); ?>"><?php echo esc_html( $kt['phone1'] ); ?></a> / <a href="<?php echo esc_attr( keytobd_tel( $kt['phone2'] ) ); ?>"><?php echo esc_html( $kt['phone2'] ); ?></a></div></div>
				<div class="contact-info__item"><?php keytobd_icon( 'whatsapp', 22 ); ?><div><strong>WhatsApp</strong><a href="https://wa.me/<?php echo esc_attr( $kt['whatsapp'] ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Chat with us', 'keytobd' ); ?></a></div></div>
				<div class="contact-info__item"><?php keytobd_icon( 'facebook', 22 ); ?><div><strong>Facebook</strong><a href="<?php echo esc_url( $kt['facebook'] ); ?>" target="_blank" rel="noopener">/Keytobd</a></div></div>
				<div class="contact-info__item"><?php keytobd_icon( 'clock', 22 ); ?><div><strong><?php esc_html_e( 'Hours', 'keytobd' ); ?></strong><?php echo esc_html( $kt['hours'] ); ?></div></div>
			</div>

			<div>
				<?php
				// Drop a [contact-form-7] / [fluentform] shortcode in the page content; otherwise show a native fallback form.
				$content = trim( get_the_content() );
				if ( $content ) :
					echo '<div class="entry-content" style="margin:0;">';
					the_content();
					echo '</div>';
				else : ?>
					<form class="kt-form" method="post" action="#" novalidate>
						<div class="kt-form__row">
							<label>＊<?php esc_html_e( 'Full name', 'keytobd' ); ?><input type="text" name="name" required></label>
							<label>＊<?php esc_html_e( 'Phone', 'keytobd' ); ?><input type="tel" name="phone" required></label>
						</div>
						<div class="kt-form__row">
							<label><?php esc_html_e( 'Email', 'keytobd' ); ?><input type="email" name="email"></label>
							<label><?php esc_html_e( 'Service', 'keytobd' ); ?>
								<select name="service">
									<option><?php esc_html_e( 'Tour Package', 'keytobd' ); ?></option>
									<option><?php esc_html_e( 'Hotel Booking', 'keytobd' ); ?></option>
									<option><?php esc_html_e( 'Rent A Car', 'keytobd' ); ?></option>
									<option><?php esc_html_e( 'Ship Ticket', 'keytobd' ); ?></option>
									<option><?php esc_html_e( 'Houseboat', 'keytobd' ); ?></option>
									<option><?php esc_html_e( 'Visa / Event', 'keytobd' ); ?></option>
								</select>
							</label>
						</div>
						<label><?php esc_html_e( 'Message', 'keytobd' ); ?><textarea name="message" rows="5"></textarea></label>
						<button type="submit" class="btn btn--accent btn--lg"><?php esc_html_e( 'Send Message', 'keytobd' ); ?></button>
						<p class="kt-form__note"><?php esc_html_e( 'Tip: install Fluent Forms or Contact Form 7 and paste its shortcode into this page to receive emails.', 'keytobd' ); ?></p>
					</form>
				<?php endif; ?>
			</div>
		</div>

		<div class="contact-map" style="margin-top:40px;">
			<iframe title="<?php esc_attr_e( 'KeyToBD office map', 'keytobd' ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade"
				src="https://www.google.com/maps?q=<?php echo rawurlencode( keytobd_mod( 'map_query' ) ); ?>&output=embed"></iframe>
		</div>
	</div>
</main>
<?php
get_footer();
