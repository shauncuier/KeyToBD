<?php
/**
 * Service card. Override at theme: /keytobd-booking/service-card.php
 *
 * @var array $service Service config from ktb_get_service().
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $service ) ) {
	return;
}

$types = ktb_service_types();
$unit  = isset( $types[ $service['type'] ] ) ? $types[ $service['type'] ]['unit'] : '';
$link  = get_permalink( $service['id'] );
?>
<article class="ktb-card">
	<a href="<?php echo esc_url( $link ); ?>" class="ktb-card__media">
		<?php if ( has_post_thumbnail( $service['id'] ) ) {
			echo get_the_post_thumbnail( $service['id'], 'large' );
		} else {
			echo '<span class="ktb-card__ph"></span>';
		} ?>
		<span class="ktb-card__type"><?php echo esc_html( $types[ $service['type'] ]['label'] ?? '' ); ?></span>
	</a>
	<div class="ktb-card__body">
		<div class="ktb-card__meta">
			<?php if ( $service['duration'] ) : ?><span><?php echo esc_html( $service['duration'] ); ?></span><?php endif; ?>
			<?php if ( $service['location'] ) : ?><span><?php echo esc_html( $service['location'] ); ?></span><?php endif; ?>
		</div>
		<h3 class="ktb-card__title"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $service['title'] ); ?></a></h3>
		<div class="ktb-card__foot">
			<span class="ktb-card__price"><?php echo esc_html( ktb_price( $service['price'] ) ); ?> <small><?php echo esc_html( $unit ); ?></small></span>
			<a href="<?php echo esc_url( $link ); ?>" class="ktb-card__btn"><?php esc_html_e( 'Book Now', 'keytobd-booking' ); ?></a>
		</div>
	</div>
</article>
