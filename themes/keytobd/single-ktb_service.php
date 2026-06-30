<?php
/**
 * Single service — conversion-focused layout: hero facts, highlights, review,
 * the booking form (auto-appended to the_content), and a sticky price card.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();

while ( have_posts() ) :
	the_post();

	$svc   = function_exists( 'ktb_get_service' ) ? ktb_get_service( get_the_ID() ) : array( 'type' => '', 'price' => 0, 'duration' => '', 'location' => '', 'capacity' => 0, 'rating' => 4.8, 'reviews' => 0 );
	$types = function_exists( 'ktb_service_types' ) ? ktb_service_types() : array();
	$unit  = isset( $types[ $svc['type'] ] ) ? $types[ $svc['type'] ]['unit'] : '';
	$badge = isset( $types[ $svc['type'] ] ) ? $types[ $svc['type'] ]['label'] : '';
	$kt    = keytobd_contact();

	// Seats-left urgency from real availability (next bookable day).
	$urgency = '';
	if ( ! empty( $svc['capacity'] ) && function_exists( 'ktb_availability' ) ) {
		$nextday = gmdate( 'Y-m-d', strtotime( '+1 day' ) );
		$remain  = ktb_availability( $svc['id'], $nextday );
		if ( PHP_INT_MAX !== $remain && $remain > 0 && $remain <= 12 ) {
			/* translators: %d: remaining seats. */
			$urgency = sprintf( _n( 'Only %d spot left for the next date', 'Only %d spots left for the next date', $remain, 'keytobd' ), $remain );
		}
	}

	get_template_part( 'template-parts/page-hero', null, array(
		'title'    => get_the_title(),
		'subtitle' => $svc['location'],
		'crumbs'   => array(
			__( 'Services', 'keytobd' )                  => get_post_type_archive_link( 'ktb_service' ),
			$badge ? $badge : __( 'Service', 'keytobd' ) => '',
		),
	) );
	?>
	<main id="content" class="site-main">
		<div class="container">
			<div class="ktb-single">

				<div class="ktb-single__main">
					<figure class="ktb-single__media">
						<?php if ( has_post_thumbnail() ) : ?>
							<?php the_post_thumbnail( 'keytobd-wide' ); ?>
						<?php else : ?>
							<img src="<?php echo esc_url( keytobd_get_service_image( get_the_ID() ) ); ?>" alt="<?php the_title_attribute(); ?>">
						<?php endif; ?>
					</figure>

					<div class="ktb-single__facts">
						<span class="ktb-fact ktb-fact--rating"><?php keytobd_icon( 'star', 16 ); ?> <strong><?php echo esc_html( number_format( (float) $svc['rating'], 1 ) ); ?></strong> <?php echo (int) $svc['reviews'] > 0 ? esc_html( sprintf( '(%s)', number_format_i18n( $svc['reviews'] ) ) ) : esc_html__( 'Excellent', 'keytobd' ); ?></span>
						<?php if ( $badge ) : ?><span class="ktb-fact"><?php keytobd_icon( 'compass', 16 ); ?> <?php echo esc_html( $badge ); ?></span><?php endif; ?>
						<?php if ( $svc['duration'] ) : ?><span class="ktb-fact"><?php keytobd_icon( 'clock', 16 ); ?> <?php echo esc_html( $svc['duration'] ); ?></span><?php endif; ?>
						<?php if ( $svc['location'] ) : ?><span class="ktb-fact"><?php keytobd_icon( 'map', 16 ); ?> <?php echo esc_html( $svc['location'] ); ?></span><?php endif; ?>
					</div>

					<div class="ktb-highlights">
						<h2><?php esc_html_e( 'Why book this', 'keytobd' ); ?></h2>
						<ul>
							<li><?php keytobd_icon( 'check', 16 ); ?> <?php esc_html_e( 'Free cancellation up to 48 hours', 'keytobd' ); ?></li>
							<li><?php keytobd_icon( 'check', 16 ); ?> <?php esc_html_e( 'Instant confirmation & e-voucher', 'keytobd' ); ?></li>
							<li><?php keytobd_icon( 'check', 16 ); ?> <?php esc_html_e( 'Experienced local guide', 'keytobd' ); ?></li>
							<li><?php keytobd_icon( 'check', 16 ); ?> <?php esc_html_e( 'Best price, no hidden charges', 'keytobd' ); ?></li>
						</ul>
					</div>

					<div class="entry-content" style="margin:0;max-width:none;">
						<?php the_content(); // includes the auto-appended booking form. ?>
					</div>

					<?php
					$rev = get_posts( array( 'post_type' => 'kt_testimonial', 'posts_per_page' => 1, 'orderby' => 'rand' ) );
					if ( $rev ) :
						$r = $rev[0];
						?>
						<figure class="ktb-review">
							<span class="ktb-review__stars"><?php for ( $i = 0; $i < 5; $i++ ) { keytobd_icon( 'star', 16 ); } ?></span>
							<blockquote><?php echo esc_html( wp_strip_all_tags( $r->post_content ) ); ?></blockquote>
							<figcaption><strong><?php echo esc_html( $r->post_title ); ?></strong> · <?php echo esc_html( get_post_meta( $r->ID, '_kt_location', true ) ); ?></figcaption>
						</figure>
					<?php endif; ?>
				</div>

				<aside class="ktb-single__aside">
					<div class="ktb-pricecard">
						<div class="ktb-pricecard__top">
							<span class="ktb-pricecard__from"><?php esc_html_e( 'from', 'keytobd' ); ?></span>
							<span class="ktb-pricecard__amount"><?php echo esc_html( ktb_price( $svc['price'] ) ); ?></span>
							<span class="ktb-pricecard__unit"><?php echo esc_html( $unit ); ?></span>
						</div>
						<?php if ( $urgency ) : ?>
							<span class="ktb-urgency"><?php keytobd_icon( 'flame', 14 ); ?> <?php echo esc_html( $urgency ); ?></span>
						<?php endif; ?>
						<a href="#ktb-book" class="btn btn--accent btn--block btn--lg"><?php esc_html_e( 'Check availability', 'keytobd' ); ?></a>
						<ul class="ktb-pricecard__list">
							<li><?php keytobd_icon( 'shieldcheck', 16 ); ?> <?php esc_html_e( 'Secure bKash / card payment', 'keytobd' ); ?></li>
							<li><?php keytobd_icon( 'check', 16 ); ?> <?php esc_html_e( 'Free cancellation 48h', 'keytobd' ); ?></li>
							<li><?php keytobd_icon( 'clock', 16 ); ?> <?php esc_html_e( '24/7 travel support', 'keytobd' ); ?></li>
						</ul>
						<a class="ktb-pricecard__wa" href="https://wa.me/<?php echo esc_attr( $kt['whatsapp'] ); ?>" target="_blank" rel="noopener"><?php keytobd_icon( 'whatsapp', 18 ); ?> <?php esc_html_e( 'Ask on WhatsApp', 'keytobd' ); ?></a>
					</div>
				</aside>

			</div>
		</div>
	</main>

	<!-- Mobile sticky price/book bar -->
	<div class="ktb-bookbar">
		<div class="ktb-bookbar__price"><strong><?php echo esc_html( ktb_price( $svc['price'] ) ); ?></strong> <span><?php echo esc_html( $unit ); ?></span></div>
		<a href="#ktb-book" class="btn btn--accent"><?php esc_html_e( 'Book now', 'keytobd' ); ?></a>
	</div>
	<?php
endwhile;

get_footer();
