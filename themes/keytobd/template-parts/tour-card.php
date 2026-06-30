<?php
/**
 * Tour / booking product card.
 *
 * Works inside a WooCommerce product loop (uses global $product) OR with a
 * $args fallback array for demo content when the shop has no products yet.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

$args = isset( $args ) && is_array( $args ) ? $args : array();
global $product;

if ( $product instanceof WC_Product ) {
	$title  = $product->get_name();
	$link   = get_permalink( $product->get_id() );
	$price  = $product->get_price_html();
	$img    = has_post_thumbnail( $product->get_id() ) ? $product->get_image( 'keytobd-card' ) : sprintf( '<img src="%s" alt="%s" loading="lazy">', esc_url( keytobd_get_service_image( $product->get_id() ) ), esc_attr( $title ) );
	$rating  = (float) $product->get_average_rating() ?: 4.8;
	$reviews = (int) $product->get_review_count();
	$cats    = wc_get_product_category_list( $product->get_id() );
	$badge   = wp_strip_all_tags( $cats );
	$excerpt = wp_trim_words( $product->get_short_description(), 14 );
} else {
	$title   = $args['title'] ?? 'Sample Package';
	$link    = $args['link'] ?? '#';
	$price   = $args['price'] ?? '<span class="amount">৳9,500</span>';
	$img     = sprintf( '<img src="%s" alt="%s" loading="lazy">', esc_url( $args['img'] ?? keytobd_img( 'placeholder.jpg' ) ), esc_attr( $title ) );
	$rating  = $args['rating'] ?? 4.8;
	$reviews = $args['reviews'] ?? 0;
	$badge   = $args['badge'] ?? 'Tour';
	$excerpt = $args['excerpt'] ?? '';
}
$rating   = $rating ? number_format( (float) $rating, 1 ) : '4.8';
$duration = $args['duration'] ?? '';
$people   = $args['people'] ?? '';
?>
<article class="tour-card reveal">
	<div class="tour-card__media">
		<a href="<?php echo esc_url( $link ); ?>"><?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<?php if ( $badge ) : ?><span class="tour-card__badge"><?php keytobd_icon( 'compass', 13 ); ?> <?php echo esc_html( $badge ); ?></span><?php endif; ?>
		<button type="button" class="tour-card__fav" aria-label="<?php esc_attr_e( 'Save to wishlist', 'keytobd' ); ?>"><?php keytobd_icon( 'heart', 16 ); ?></button>
	</div>
	<div class="tour-card__body">
		<div class="tour-card__meta">
			<?php if ( $duration ) : ?><span><?php keytobd_icon( 'clock', 14 ); ?> <?php echo esc_html( $duration ); ?></span><?php endif; ?>
			<?php if ( $people ) : ?><span><?php keytobd_icon( 'users', 14 ); ?> <?php echo esc_html( $people ); ?></span><?php endif; ?>
		</div>
		<h3><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $title ); ?></a></h3>
		<?php if ( $excerpt ) : ?><p class="tour-card__excerpt"><?php echo esc_html( $excerpt ); ?></p><?php endif; ?>
		<span class="tour-card__rating"><span class="stars"><?php keytobd_icon( 'star', 14 ); ?></span> <strong><?php echo esc_html( $rating ); ?></strong> <?php if ( (int) $reviews > 0 ) : ?><small>(<?php echo esc_html( number_format_i18n( $reviews ) ); ?>)</small><?php else : ?><small><?php esc_html_e( 'Excellent', 'keytobd' ); ?></small><?php endif; ?></span>
		<div class="tour-card__foot">
			<div class="tour-card__price">
				<span class="from"><?php esc_html_e( 'From', 'keytobd' ); ?></span>
				<?php echo wp_kses_post( $price ); ?>
			</div>
			<a href="<?php echo esc_url( $link ); ?>" class="btn btn--accent btn--sm"><?php esc_html_e( 'Book', 'keytobd' ); ?></a>
		</div>
	</div>
</article>
