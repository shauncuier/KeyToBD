<?php
/**
 * Front page (homepage).
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();

$kt = keytobd_contact();

/* Hero background priority: Customizer image → page featured image → theme asset. */
$hero_bg = keytobd_mod( 'hero_image' );
if ( ! $hero_bg ) {
	$hero_bg = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'keytobd-hero' ) : keytobd_img( 'hero.jpg' );
}

/* Demo packages used when the shop has no products yet. */
$demo_packages = array(
	array( 'title' => "Saint Martin 3 Day Tour",        'badge' => 'Island', 'duration' => '3 Days', 'people' => '2-10 pax', 'price' => '<span class="amount">৳6,500</span>', 'rating' => '4.9', 'img' => keytobd_img( 'pkg-saintmartin.jpg' ) ),
	array( 'title' => "Sajek Valley Cloud Tour",         'badge' => 'Hills',  'duration' => '2 Days', 'people' => '2-8 pax',  'price' => '<span class="amount">৳5,200</span>', 'rating' => '4.8', 'img' => keytobd_img( 'pkg-sajek.jpg' ) ),
	array( 'title' => "Tanguar Haor Houseboat",          'badge' => 'Houseboat', 'duration' => '2 Days', 'people' => '6-20 pax', 'price' => '<span class="amount">৳4,800</span>', 'rating' => '5.0', 'img' => keytobd_img( 'pkg-tanguar.jpg' ) ),
	array( 'title' => "Cox's Bazar Beach Getaway",       'badge' => 'Beach',  'duration' => '3 Days', 'people' => '2-6 pax',  'price' => '<span class="amount">৳7,900</span>', 'rating' => '4.7', 'img' => keytobd_img( 'pkg-coxsbazar.jpg' ) ),
	array( 'title' => "Bandarban Adventure",             'badge' => 'Hills',  'duration' => '3 Days', 'people' => '2-10 pax', 'price' => '<span class="amount">৳6,900</span>', 'rating' => '4.8', 'img' => keytobd_img( 'pkg-bandarban.jpg' ) ),
	array( 'title' => "Sundarbans Mangrove Cruise",      'badge' => 'Cruise', 'duration' => '4 Days', 'people' => '4-24 pax', 'price' => '<span class="amount">৳11,500</span>', 'rating' => '4.9', 'img' => keytobd_img( 'pkg-sundarbans.jpg' ) ),
);
?>

<!-- ============ HERO ============ -->
<section class="hero">
	<div class="hero__bg">
		<img src="<?php echo esc_url( $hero_bg ); ?>" alt="" fetchpriority="high">
	</div>
	<div class="container">
		<div class="hero__inner">
			<?php if ( keytobd_mod( 'hero_eyebrow' ) ) : ?><p class="eyebrow"><?php keytobd_icon( 'leaf', 16 ); ?> <?php echo esc_html( keytobd_mod( 'hero_eyebrow' ) ); ?></p><?php endif; ?>
			<h1><?php echo esc_html( keytobd_mod( 'hero_title' ) ); ?></h1>
			<p><?php echo esc_html( keytobd_mod( 'hero_subtitle' ) ); ?></p>
			<?php if ( keytobd_mod( 'hero_rating' ) ) : ?>
				<span class="hero__rating"><span class="stars"><?php keytobd_icon( 'star', 18 ); ?><?php keytobd_icon( 'star', 18 ); ?><?php keytobd_icon( 'star', 18 ); ?><?php keytobd_icon( 'star', 18 ); ?><?php keytobd_icon( 'star', 18 ); ?></span> <?php echo esc_html( keytobd_mod( 'hero_rating' ) ); ?> <span class="sep">·</span> <span class="secure"><?php keytobd_icon( 'shieldcheck', 16 ); ?> <?php esc_html_e( 'Secure bKash · SSLCommerz', 'keytobd' ); ?></span></span>
			<?php endif; ?>
		</div>
		<?php if ( keytobd_is_on( 'hero_show_search' ) ) { keytobd_search_widget(); } ?>
	</div>
	<?php get_template_part( 'template-parts/wave' ); ?>
</section>

<!-- ============ TRUST STRIP ============ -->
<section class="trust-strip">
	<div class="container">
		<div class="trust-strip__grid">
			<div class="trust-stat"><div class="trust-stat__num">10k+</div><div class="trust-stat__label">Happy travellers</div></div>
			<div class="trust-stat"><div class="trust-stat__num">50+</div><div class="trust-stat__label">Tours & stays</div></div>
			<div class="trust-stat"><div class="trust-stat__num">7</div><div class="trust-stat__label">Regions covered</div></div>
			<div class="trust-stat"><div class="trust-stat__num">24/7</div><div class="trust-stat__label">Travel support</div></div>
		</div>
	</div>
</section>

<!-- ============ SERVICES ============ -->
<?php if ( keytobd_is_on( 'services_on' ) ) : ?>
<section class="section">
	<div class="container">
		<div class="section__head">
			<?php if ( keytobd_mod( 'services_eyebrow' ) ) : ?><p class="eyebrow"><?php echo esc_html( keytobd_mod( 'services_eyebrow' ) ); ?></p><?php endif; ?>
			<h2><?php echo esc_html( keytobd_mod( 'services_title' ) ); ?></h2>
			<?php if ( keytobd_mod( 'services_text' ) ) : ?><p><?php echo esc_html( keytobd_mod( 'services_text' ) ); ?></p><?php endif; ?>
		</div>
		<div class="services-grid">
			<?php foreach ( keytobd_services() as $s ) : ?>
				<a class="service-card reveal" href="<?php echo esc_url( $s['cat'] ? keytobd_cat_link( $s['cat'] ) : home_url( '/services/' ) ); ?>">
					<span class="service-card__icon"><?php keytobd_icon( $s['icon'], 26 ); ?></span>
					<h3><?php echo esc_html( $s['title'] ); ?></h3>
					<p><?php echo esc_html( $s['text'] ); ?></p>
					<span class="service-card__link"><?php esc_html_e( 'Explore', 'keytobd' ); ?> <?php keytobd_icon( 'arrow', 16 ); ?></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ============ POPULAR PACKAGES ============ -->
<?php if ( keytobd_is_on( 'packages_on' ) ) : ?>
<section class="section section--soft">
	<div class="container">
		<div class="section__head">
			<?php if ( keytobd_mod( 'packages_eyebrow' ) ) : ?><p class="eyebrow"><?php echo esc_html( keytobd_mod( 'packages_eyebrow' ) ); ?></p><?php endif; ?>
			<h2><?php echo esc_html( keytobd_mod( 'packages_title' ) ); ?></h2>
			<?php if ( keytobd_mod( 'packages_text' ) ) : ?><p><?php echo esc_html( keytobd_mod( 'packages_text' ) ); ?></p><?php endif; ?>
		</div>
		<div class="cards-grid">
			<?php
			$count    = (int) keytobd_mod( 'packages_count' );
			$rendered = false;

			// 1) WooCommerce products (if active and has tour packages).
			if ( function_exists( 'wc_get_product' ) && taxonomy_exists( 'product_cat' ) ) {
				$loop = new WP_Query( array(
					'post_type'      => 'product',
					'posts_per_page' => $count,
					'tax_query'      => array( array(
						'taxonomy' => 'product_cat',
						'field'    => 'slug',
						'terms'    => 'tour-packages',
					) ),
				) );
				if ( $loop->have_posts() ) {
					while ( $loop->have_posts() ) {
						$loop->the_post();
						global $product;
						$product = wc_get_product( get_the_ID() );
						get_template_part( 'template-parts/tour-card' );
					}
					wp_reset_postdata();
					$rendered = true;
				}
			}

			// 2) KeyToBD Booking services (standalone plugin route).
			if ( ! $rendered && post_type_exists( 'ktb_service' ) ) {
				$loop  = new WP_Query( array( 'post_type' => 'ktb_service', 'posts_per_page' => $count ) );
				$types = function_exists( 'ktb_service_types' ) ? ktb_service_types() : array();
				if ( $loop->have_posts() ) {
					while ( $loop->have_posts() ) {
						$loop->the_post();
						$svc  = ktb_get_service( get_the_ID() );
						$args = array(
							'title'    => $svc['title'],
							'link'     => get_permalink( $svc['id'] ),
							'price'    => '<span class="amount">' . esc_html( ktb_price( $svc['price'] ) ) . '</span>',
							'img'      => has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'keytobd-card' ) : keytobd_img( 'placeholder.svg' ),
							'badge'    => isset( $types[ $svc['type'] ] ) ? $types[ $svc['type'] ]['label'] : '',
							'duration' => $svc['duration'],
							'people'   => $svc['location'],
							'rating'   => '',
						);
						$GLOBALS['product'] = null;
						get_template_part( 'template-parts/tour-card', null, $args );
					}
					wp_reset_postdata();
					$rendered = true;
				}
			}

			// 3) Demo fallback.
			if ( ! $rendered ) {
				foreach ( $demo_packages as $pkg ) {
					$GLOBALS['product'] = null;
					get_template_part( 'template-parts/tour-card', null, $pkg );
				}
			}
			?>
		</div>
		<div style="text-align:center; margin-top:36px;">
			<a href="<?php echo esc_url( keytobd_cat_link( 'tour-packages' ) ); ?>" class="btn btn--primary btn--lg"><?php esc_html_e( 'View all packages', 'keytobd' ); ?> <?php keytobd_icon( 'arrow', 18 ); ?></a>
		</div>
	</div>
</section>

<?php endif; ?>

<!-- ============ DESTINATIONS ============ -->
<?php if ( keytobd_is_on( 'dest_on' ) ) : ?>
<section class="section">
	<div class="container">
		<div class="section__head">
			<?php if ( keytobd_mod( 'dest_eyebrow' ) ) : ?><p class="eyebrow"><?php echo esc_html( keytobd_mod( 'dest_eyebrow' ) ); ?></p><?php endif; ?>
			<h2><?php echo esc_html( keytobd_mod( 'dest_title' ) ); ?></h2>
		</div>
		<?php
		$dest_tiles = array(
			array( 'name' => "Cox's Bazar",  'meta' => "World's longest beach", 'img' => keytobd_img( 'dest-coxsbazar.jpg' ), 'slug' => 'coxs-bazar' ),
			array( 'name' => 'Saint Martin', 'meta' => 'Coral island',          'img' => keytobd_img( 'dest-saintmartin.jpg' ), 'slug' => 'saint-martin' ),
			array( 'name' => 'Sajek Valley', 'meta' => 'Land of clouds',        'img' => keytobd_img( 'dest-sajek.jpg' ), 'slug' => 'sajek-valley' ),
			array( 'name' => 'Sundarbans',   'meta' => 'Royal Bengal tiger',    'img' => keytobd_img( 'dest-sundarbans.jpg' ), 'slug' => 'sundarbans' ),
			array( 'name' => 'Tanguar Haor', 'meta' => 'Wetland houseboats',    'img' => keytobd_img( 'dest-tanguar.jpg' ), 'slug' => 'sylhet-tanguar-haor' ),
		);
		?>
		<div class="dest-grid">
			<?php foreach ( $dest_tiles as $tile ) :
				$term = get_term_by( 'slug', $tile['slug'], 'destination' );
				$url  = $term && ! is_wp_error( $term ) ? get_term_link( $term ) : home_url( '/destinations/' );
				?>
				<a class="dest-tile reveal" href="<?php echo esc_url( $url ); ?>">
					<img src="<?php echo esc_url( $tile['img'] ); ?>" alt="<?php echo esc_attr( $tile['name'] ); ?>" loading="lazy">
					<span class="dest-tile__label"><strong><?php echo esc_html( $tile['name'] ); ?></strong><span><?php echo esc_html( $tile['meta'] ); ?></span></span>
				</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<?php endif; ?>

<!-- ============ WHY US ============ -->
<?php if ( keytobd_is_on( 'why_on' ) ) : ?>
<section class="section section--soft">
	<div class="container">
		<div class="section__head">
			<?php if ( keytobd_mod( 'why_eyebrow' ) ) : ?><p class="eyebrow"><?php echo esc_html( keytobd_mod( 'why_eyebrow' ) ); ?></p><?php endif; ?>
			<h2><?php echo esc_html( keytobd_mod( 'why_title' ) ); ?></h2>
		</div>
		<div class="features-grid">
			<div class="feature reveal"><span class="feature__icon"><?php keytobd_icon( 'shield', 28 ); ?></span><h3><?php esc_html_e( 'Secure Payments', 'keytobd' ); ?></h3><p><?php esc_html_e( 'Pay safely with bKash, Nagad, cards & bank via SSLCommerz.', 'keytobd' ); ?></p></div>
			<div class="feature reveal"><span class="feature__icon"><?php keytobd_icon( 'compass', 28 ); ?></span><h3><?php esc_html_e( 'Local Experts', 'keytobd' ); ?></h3><p><?php esc_html_e( 'Cox\'s Bazar based team that knows every route first-hand.', 'keytobd' ); ?></p></div>
			<div class="feature reveal"><span class="feature__icon"><?php keytobd_icon( 'clock', 28 ); ?></span><h3><?php esc_html_e( '24/7 Support', 'keytobd' ); ?></h3><p><?php esc_html_e( 'Call or WhatsApp us any time before and during your trip.', 'keytobd' ); ?></p></div>
			<div class="feature reveal"><span class="feature__icon"><?php keytobd_icon( 'star', 28 ); ?></span><h3><?php esc_html_e( 'Best Price', 'keytobd' ); ?></h3><p><?php esc_html_e( 'Transparent pricing with no hidden booking charges.', 'keytobd' ); ?></p></div>
		</div>
	</div>
</section>

<?php endif; ?>

<!-- ============ HOW IT WORKS ============ -->
<?php if ( keytobd_is_on( 'steps_on' ) ) : ?>
<section class="section">
	<div class="container">
		<div class="section__head">
			<?php if ( keytobd_mod( 'steps_eyebrow' ) ) : ?><p class="eyebrow"><?php echo esc_html( keytobd_mod( 'steps_eyebrow' ) ); ?></p><?php endif; ?>
			<h2><?php echo esc_html( keytobd_mod( 'steps_title' ) ); ?></h2>
		</div>
		<div class="steps">
			<div class="step reveal"><span class="step__num">1</span><h3><?php esc_html_e( 'Search', 'keytobd' ); ?></h3><p><?php esc_html_e( 'Pick a service, destination and date.', 'keytobd' ); ?></p></div>
			<div class="step reveal"><span class="step__num">2</span><h3><?php esc_html_e( 'Choose', 'keytobd' ); ?></h3><p><?php esc_html_e( 'Compare options and select what fits you.', 'keytobd' ); ?></p></div>
			<div class="step reveal"><span class="step__num">3</span><h3><?php esc_html_e( 'Pay', 'keytobd' ); ?></h3><p><?php esc_html_e( 'Checkout securely with your preferred method.', 'keytobd' ); ?></p></div>
			<div class="step reveal"><span class="step__num">4</span><h3><?php esc_html_e( 'Travel', 'keytobd' ); ?></h3><p><?php esc_html_e( 'Get your e-voucher and enjoy the trip.', 'keytobd' ); ?></p></div>
		</div>
	</div>
</section>

<?php endif; ?>

<!-- ============ TESTIMONIALS ============ -->
<?php if ( keytobd_is_on( 'testi_on' ) ) : ?>
<section class="section section--soft">
	<div class="container">
		<div class="section__head">
			<?php if ( keytobd_mod( 'testi_eyebrow' ) ) : ?><p class="eyebrow"><?php echo esc_html( keytobd_mod( 'testi_eyebrow' ) ); ?></p><?php endif; ?>
			<h2><?php echo esc_html( keytobd_mod( 'testi_title' ) ); ?></h2>
		</div>
		<div class="testimonials">
			<?php
			$reviews = array();
			$tq = new WP_Query( array( 'post_type' => 'kt_testimonial', 'posts_per_page' => 6 ) );
			if ( $tq->have_posts() ) {
				while ( $tq->have_posts() ) {
					$tq->the_post();
					$rating = (int) get_post_meta( get_the_ID(), '_kt_rating', true );
					$reviews[] = array(
						'name'   => get_the_title(),
						'place'  => get_post_meta( get_the_ID(), '_kt_location', true ),
						'text'   => wp_strip_all_tags( get_the_content() ),
						'rating' => $rating ? $rating : 5,
					);
				}
				wp_reset_postdata();
			} else {
				$reviews = array(
					array( 'name' => 'Tanvir Ahmed', 'place' => 'Dhaka', 'rating' => 5, 'text' => 'Booked our Saint Martin tour in minutes. Ship tickets and hotel all sorted — smoothest trip ever.' ),
					array( 'name' => 'Nusrat Jahan', 'place' => 'Chittagong', 'rating' => 5, 'text' => 'The Tanguar Haor houseboat was magical. KeyToBD handled everything for our family of 12.' ),
					array( 'name' => 'Rakib Hasan', 'place' => 'Sylhet', 'rating' => 5, 'text' => 'Great prices and the rent-a-car (Chader Gari) for Sajek was on time. Highly recommended!' ),
				);
			}
			foreach ( $reviews as $r ) : ?>
				<div class="testimonial reveal">
					<span class="testimonial__stars"><?php for ( $i = 0; $i < (int) $r['rating']; $i++ ) { keytobd_icon( 'star', 16 ); } ?></span>
					<p>&ldquo;<?php echo esc_html( $r['text'] ); ?>&rdquo;</p>
					<div class="testimonial__author">
						<span class="testimonial__avatar"><?php echo esc_html( mb_substr( $r['name'], 0, 1 ) ); ?></span>
						<span><strong><?php echo esc_html( $r['name'] ); ?></strong><span><?php echo esc_html( $r['place'] ); ?></span></span>
					</div>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ============ BLOG TEASER ============ -->
<?php if ( keytobd_is_on( 'blog_on' ) && get_posts( array( 'numberposts' => 1 ) ) ) : ?>
<section class="section">
	<div class="container">
		<div class="section__head">
			<?php if ( keytobd_mod( 'blog_eyebrow' ) ) : ?><p class="eyebrow"><?php echo esc_html( keytobd_mod( 'blog_eyebrow' ) ); ?></p><?php endif; ?>
			<h2><?php echo esc_html( keytobd_mod( 'blog_title' ) ); ?></h2>
		</div>
		<div class="posts-grid">
			<?php
			$blog = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 3 ) );
			while ( $blog->have_posts() ) : $blog->the_post(); ?>
				<article class="post-card reveal">
					<a href="<?php the_permalink(); ?>" class="post-card__media">
						<?php if ( has_post_thumbnail() ) { the_post_thumbnail( 'keytobd-card' ); } ?>
					</a>
					<div class="post-card__body">
						<span class="post-card__date"><?php echo esc_html( get_the_date() ); ?></span>
						<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
						<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 18 ) ); ?></p>
					</div>
				</article>
			<?php endwhile; wp_reset_postdata(); ?>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ============ FINAL CTA ============ -->
<?php if ( keytobd_is_on( 'cta_on' ) ) : ?>
<section class="section">
	<div class="container">
		<div class="cta-band reveal">
			<h2><?php echo esc_html( keytobd_mod( 'cta_title' ) ); ?></h2>
			<p><?php echo esc_html( keytobd_mod( 'cta_text' ) ); ?></p>
			<div class="btn-row">
				<a href="<?php echo esc_url( keytobd_cat_link( 'tour-packages' ) ); ?>" class="btn btn--accent btn--lg"><?php esc_html_e( 'Start Booking', 'keytobd' ); ?></a>
				<a href="<?php echo esc_attr( keytobd_tel( $kt['phone1'] ) ); ?>" class="btn btn--ghost-light btn--lg"><?php keytobd_icon( 'phone', 18 ); ?> <?php echo esc_html( $kt['phone1'] ); ?></a>
			</div>
		</div>
	</div>
</section>
<?php endif; ?>

<?php
get_footer();
