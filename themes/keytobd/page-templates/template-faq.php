<?php
/**
 * Template Name: FAQ
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();

get_template_part( 'template-parts/page-hero', null, array(
	'title'    => get_the_title() ? get_the_title() : __( 'Frequently Asked Questions', 'keytobd' ),
	'subtitle' => __( 'Everything you need to know about booking with KeyToBD.', 'keytobd' ),
	'crumbs'   => array( __( 'FAQ', 'keytobd' ) => '' ),
) );

$faqs = array(
	array( 'q' => 'How do I book a tour or hotel?', 'a' => 'Use the search box on the homepage, pick your service and date, choose an option, then complete the secure checkout. You will receive an e-voucher by email instantly.' ),
	array( 'q' => 'What payment methods do you accept?', 'a' => 'We accept bKash, Nagad, Rocket, debit/credit cards and bank transfer through our secure SSLCommerz gateway.' ),
	array( 'q' => 'Can I book Saint Martin ship tickets online?', 'a' => 'Yes. Choose your route, travel date, seat class and number of seats, then pay online. Your tickets are confirmed by email and SMS.' ),
	array( 'q' => 'Do you arrange custom or corporate tours?', 'a' => 'Absolutely. Contact us on WhatsApp or phone and our team will build a custom itinerary for your family, group or company.' ),
	array( 'q' => 'What is your cancellation policy?', 'a' => 'Cancellation terms vary by service and season and are shown on each booking page. See our Refund & Cancellation Policy for full details.' ),
	array( 'q' => 'Is rent-a-car available with a driver?', 'a' => 'Yes. Chader Gari, cars, microbuses and tourist buses are available with experienced local drivers. Self-drive options are also available on request.' ),
);
?>
<main id="content" class="site-main">
	<div class="container">
		<?php if ( trim( get_the_content() ) ) : ?>
			<div class="entry-content"><?php the_content(); ?></div>
		<?php endif; ?>
		<div class="faq-list">
			<?php foreach ( $faqs as $f ) : ?>
				<div class="faq-item">
					<button class="faq-q" aria-expanded="false"><?php echo esc_html( $f['q'] ); ?></button>
					<div class="faq-a"><p><?php echo esc_html( $f['a'] ); ?></p></div>
				</div>
			<?php endforeach; ?>
		</div>
		<div class="cta-band reveal" style="margin-top:48px;">
			<h2><?php esc_html_e( 'Still have questions?', 'keytobd' ); ?></h2>
			<p><?php esc_html_e( 'Our travel experts are one message away.', 'keytobd' ); ?></p>
			<div class="btn-row" style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;">
				<a class="btn btn--accent btn--lg" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact Us', 'keytobd' ); ?></a>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();
