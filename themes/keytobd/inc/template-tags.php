<?php
/**
 * Reusable template helpers / partial renderers.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Inline SVG icon set (no icon-font dependency).
 *
 * @param string $name Icon key.
 * @param int    $size Pixel size.
 */
function keytobd_icon( $name, $size = 22 ) {
	echo keytobd_icon_str( $name, $size ); // phpcs:ignore WordPress.Security.EscapeOutput
}

/**
 * Return an inline SVG icon as a string.
 *
 * @param string $name Icon key.
 * @param int    $size Pixel size.
 * @return string
 */
function keytobd_icon_str( $name, $size = 22 ) {
	$paths = array(
		'search'    => '<circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>',
		'phone'     => '<path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/>',
		'whatsapp'  => '<path fill="currentColor" stroke="none" d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.019-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>',
		'map'       => '<path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/>',
		'calendar'  => '<rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
		'users'     => '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/>',
		'compass'   => '<circle cx="12" cy="12" r="10"/><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"/>',
		'leaf'      => '<path d="M11 20A7 7 0 0 1 4 13c0-5 4-9 16-9 0 8-5 13-9 13z"/><path d="M4 21c3-4 6-6 9-7"/>',
		'shieldcheck' => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>',
		'flame'     => '<path d="M12 2c1 4 5 5 5 9a5 5 0 0 1-10 0c0-2 1-3 2-4 0 2 1 3 2 3 1-2-1-4-1-8z"/>',
		'bed'       => '<path d="M2 17v-5a3 3 0 0 1 3-3h14a3 3 0 0 1 3 3v5"/><path d="M2 17h20v3H2z"/><path d="M6 9V7a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v2"/>',
		'car'       => '<path d="M5 17h14l1-5H4l1 5z"/><path d="M3 12l2-6a2 2 0 0 1 2-1h10a2 2 0 0 1 2 1l2 6"/><circle cx="7.5" cy="17.5" r="1.5"/><circle cx="16.5" cy="17.5" r="1.5"/>',
		'ship'      => '<path d="M3 14l9-4 9 4-2.5 6H5.5L3 14z"/><path d="M12 10V4l4 2"/><line x1="12" y1="2" x2="12" y2="4"/>',
		'star'      => '<polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/>',
		'heart'     => '<path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.7l-1-1.1a5.5 5.5 0 0 0-7.8 7.8L12 21l8.8-8.6a5.5 5.5 0 0 0 0-7.8z"/>',
		'shield'    => '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>',
		'check'     => '<polyline points="20 6 9 17 4 12"/>',
		'arrow'     => '<line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/>',
		'clock'     => '<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>',
		'facebook'  => '<path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/>',
		'globe'     => '<circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>',
		'menu'      => '<line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/>',
		'close'     => '<line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>',
	);

	if ( empty( $paths[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="icon icon--%1$s" width="%2$d" height="%2$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%3$s</svg>',
		esc_attr( $name ),
		(int) $size,
		$paths[ $name ]
	);
}

/**
 * Render a KeyToBD Booking service as the theme's branded tour-card.
 * Hooked to the plugin's `ktb_service_card_html` filter so the dynamic AJAX
 * archive and the no-JS server render produce identical markup.
 *
 * @param string $html      Existing HTML (empty to take over).
 * @param array  $svc       Service config from ktb_get_service().
 * @param int    $post_id   Service post ID.
 * @return string
 */
function keytobd_service_card_html( $html, $svc, $post_id ) {
	$types = function_exists( 'ktb_service_types' ) ? ktb_service_types() : array();
	$args  = array(
		'title'    => $svc['title'],
		'link'     => get_permalink( $post_id ),
		'price'    => '<span class="amount">' . esc_html( ktb_price( $svc['price'] ) ) . '</span>',
		'img'      => keytobd_get_service_image( $post_id ),
		'badge'    => isset( $types[ $svc['type'] ] ) ? $types[ $svc['type'] ]['label'] : '',
		'duration' => $svc['duration'],
		'people'   => $svc['location'],
		'rating'   => isset( $svc['rating'] ) ? $svc['rating'] : 4.8,
		'reviews'  => isset( $svc['reviews'] ) ? $svc['reviews'] : 0,
		'excerpt'  => wp_trim_words( get_the_excerpt( $post_id ), 14 ),
	);
	$GLOBALS['product'] = null;
	ob_start();
	get_template_part( 'template-parts/tour-card', null, $args );
	return ob_get_clean();
}
add_filter( 'ktb_service_card_html', 'keytobd_service_card_html', 10, 3 );

/**
 * Fallback primary menu when no menu is assigned to the `primary` location.
 * Mirrors the planned site map so the theme is usable out of the box.
 */
function keytobd_fallback_menu() {
	$items = array(
		home_url( '/' )                          => __( 'Home', 'keytobd' ),
		keytobd_cat_link( 'tour-packages' )      => __( 'Tours', 'keytobd' ),
		keytobd_cat_link( 'hotels-resorts' )     => __( 'Hotels', 'keytobd' ),
		keytobd_cat_link( 'rent-a-car' )         => __( 'Rent A Car', 'keytobd' ),
		keytobd_cat_link( 'ship-tickets' )       => __( 'Ship Tickets', 'keytobd' ),
		home_url( '/destinations/' )             => __( 'Destinations', 'keytobd' ),
		home_url( '/about/' )                    => __( 'About', 'keytobd' ),
		home_url( '/contact/' )                  => __( 'Contact', 'keytobd' ),
	);
	echo '<ul class="primary-nav__menu">';
	foreach ( $items as $url => $label ) {
		printf( '<li class="menu-item"><a href="%s">%s</a></li>', esc_url( $url ), esc_html( $label ) );
	}
	echo '</ul>';
}

/**
 * Theme image URL inside assets/img.
 * Falls back to the bundled SVG placeholder when the real photo isn't present,
 * so the template looks complete before the client uploads media.
 */
function keytobd_img( $file ) {
	$file = ltrim( $file, '/' );
	$path = KEYTOBD_DIR . '/assets/img/' . $file;
	if ( ! file_exists( $path ) ) {
		return KEYTOBD_URI . '/assets/img/placeholder.svg';
	}
	return KEYTOBD_URI . '/assets/img/' . $file;
}

/**
 * Render the hero booking search widget (tabbed: Tours / Hotels / Cars / Ship).
 * Submits to the shop with query args; works as a real GET filter against WooCommerce.
 */
function keytobd_search_widget() {
	get_template_part( 'template-parts/hero', 'search' );
}

/**
 * The six headline services.
 *
 * @return array<int,array<string,string>>
 */
function keytobd_services() {
	return array(
		array( 'icon' => 'compass', 'title' => 'Tour Packages',     'text' => 'Family, group & corporate tours across Bangladesh.', 'cat' => 'tour-packages' ),
		array( 'icon' => 'bed',     'title' => 'Hotels & Resorts',  'text' => 'Book stays to match your budget and preference.',   'cat' => 'hotels-resorts' ),
		array( 'icon' => 'car',     'title' => 'Rent A Car',        'text' => 'Chader Gari, tourist bus, car & bus rentals.',     'cat' => 'rent-a-car' ),
		array( 'icon' => 'ship',    'title' => 'Saint Martin Ship', 'text' => 'Reserve ship tickets for any Saint Martin liner.', 'cat' => 'ship-tickets' ),
		array( 'icon' => 'compass', 'title' => 'Houseboat Tours',   'text' => 'Tanguar Haor, Kaptai Lake, Padma & Cox\'s Bazar.',  'cat' => 'houseboat' ),
		array( 'icon' => 'globe',   'title' => 'Visa & Events',     'text' => 'Visa processing and full event management.',        'cat' => '' ),
	);
}

/**
 * Map a theme service slug to a KeyToBD Booking service type.
 *
 * @return array<string,string>
 */
function keytobd_type_map() {
	return array(
		'tour-packages'  => 'tour',
		'hotels-resorts' => 'hotel',
		'rent-a-car'     => 'car',
		'ship-tickets'   => 'ship',
		'houseboat'      => 'houseboat',
	);
}

/**
 * Resolve a booking link for a service slug.
 *
 * Priority: WooCommerce product category (if active) → KeyToBD Booking service
 * archive filtered by type → site home. This keeps every "Book Now" / category
 * link working whether the site uses WooCommerce or the standalone booking plugin.
 *
 * @param string $slug Theme service slug (e.g. tour-packages).
 * @return string
 */
function keytobd_cat_link( $slug ) {
	// WooCommerce path.
	if ( $slug && function_exists( 'wc_get_page_permalink' ) && taxonomy_exists( 'product_cat' ) ) {
		$term = get_term_by( 'slug', $slug, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			return get_term_link( $term );
		}
	}

	// KeyToBD Booking plugin path.
	if ( post_type_exists( 'ktb_service' ) ) {
		$archive = get_post_type_archive_link( 'ktb_service' );
		if ( $archive ) {
			$map = keytobd_type_map();
			if ( $slug && isset( $map[ $slug ] ) ) {
				return add_query_arg( 'ktb_type', $map[ $slug ], $archive );
			}
			return $archive;
		}
	}

	return function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
}

/**
 * Get the service image URL. Fall back to destination-specific photos
 * or keyword-matched photos instead of placeholder.svg when no thumbnail is set.
 */
function keytobd_get_service_image( $post_id ) {
	if ( has_post_thumbnail( $post_id ) ) {
		return get_the_post_thumbnail_url( $post_id, 'keytobd-card' );
	}

	// 1. Try destination taxonomy terms
	$terms = get_the_terms( $post_id, 'destination' );
	if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			switch ( $term->slug ) {
				case 'coxs-bazar':
					return keytobd_img( 'pkg-coxsbazar.jpg' );
				case 'saint-martin':
					return keytobd_img( 'pkg-saintmartin.jpg' );
				case 'sajek-valley':
					return keytobd_img( 'pkg-sajek.jpg' );
				case 'sundarbans':
					return keytobd_img( 'pkg-sundarbans.jpg' );
				case 'sylhet-tanguar-haor':
					return keytobd_img( 'pkg-tanguar.jpg' );
				case 'bandarban':
					return keytobd_img( 'pkg-bandarban.jpg' );
			}
		}
	}

	// 2. Try title matching keywords
	$title = strtolower( get_the_title( $post_id ) );
	if ( false !== strpos( $title, 'sajek' ) ) {
		return keytobd_img( 'pkg-sajek.jpg' );
	}
	if ( false !== strpos( $title, 'martin' ) ) {
		return keytobd_img( 'pkg-saintmartin.jpg' );
	}
	if ( false !== strpos( $title, 'tanguar' ) || false !== strpos( $title, 'haor' ) ) {
		return keytobd_img( 'pkg-tanguar.jpg' );
	}
	if ( false !== strpos( $title, 'cox' ) || false !== strpos( $title, 'beach' ) || false !== strpos( $title, 'sea' ) ) {
		return keytobd_img( 'pkg-coxsbazar.jpg' );
	}
	if ( false !== strpos( $title, 'bandarban' ) ) {
		return keytobd_img( 'pkg-bandarban.jpg' );
	}
	if ( false !== strpos( $title, 'sundarban' ) ) {
		return keytobd_img( 'pkg-sundarbans.jpg' );
	}

	// 3. Fallback based on post type / category or default list
	$defaults = array(
		'pkg-saintmartin.jpg',
		'pkg-sajek.jpg',
		'pkg-tanguar.jpg',
		'pkg-coxsbazar.jpg',
		'pkg-bandarban.jpg',
		'pkg-sundarbans.jpg',
	);
	$index = $post_id % count( $defaults );
	return keytobd_img( $defaults[ $index ] );
}
