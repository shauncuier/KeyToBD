<?php
/**
 * Template Name: Service Enquiry (Visa / Event / Air Ticket)
 *
 * Marketing intro + enquiry form. Paste a Fluent Forms / CF7 shortcode into the
 * page body to capture leads by email; a styled fallback form shows otherwise.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();

get_template_part( 'template-parts/page-hero', null, array(
	'title'    => get_the_title(),
	'subtitle' => get_the_excerpt() ? get_the_excerpt() : __( 'Tell us what you need and our team will get back within hours.', 'keytobd' ),
	'crumbs'   => array( get_the_title() => '' ),
) );
?>
<main id="content" class="site-main">
	<div class="container">
		<div class="contact-grid">
			<div class="entry-content" style="margin:0;">
				<?php
				if ( trim( get_the_content() ) ) {
					the_content();
				} else {
					echo '<h2>' . esc_html__( 'How it works', 'keytobd' ) . '</h2>';
					echo '<ul class="kt-ticklist">';
					foreach ( array(
						__( 'Share your requirement and travel dates.', 'keytobd' ),
						__( 'We prepare a tailored quote and document checklist.', 'keytobd' ),
						__( 'Approve and pay securely online.', 'keytobd' ),
						__( 'We handle the processing end-to-end.', 'keytobd' ),
					) as $line ) {
						echo '<li>' . esc_html( $line ) . '</li>';
					}
					echo '</ul>';
				}
				?>
			</div>

			<div>
				<form class="kt-form" method="post" action="#" novalidate>
					<h3><?php esc_html_e( 'Request a callback', 'keytobd' ); ?></h3>
					<div class="kt-form__row">
						<label>＊<?php esc_html_e( 'Name', 'keytobd' ); ?><input type="text" name="name" required></label>
						<label>＊<?php esc_html_e( 'Phone', 'keytobd' ); ?><input type="tel" name="phone" required></label>
					</div>
					<label><?php esc_html_e( 'Email', 'keytobd' ); ?><input type="email" name="email"></label>
					<label><?php esc_html_e( 'Details', 'keytobd' ); ?><textarea name="message" rows="5" placeholder="<?php esc_attr_e( 'Destination, dates, number of people…', 'keytobd' ); ?>"></textarea></label>
					<button type="submit" class="btn btn--accent btn--lg btn--block"><?php esc_html_e( 'Submit Enquiry', 'keytobd' ); ?></button>
					<p class="kt-form__note"><?php esc_html_e( 'Prefer to talk? Call 01684498885 or message us on WhatsApp.', 'keytobd' ); ?></p>
				</form>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
