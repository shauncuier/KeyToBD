<?php
/**
 * Theme options — central defaults + accessor.
 *
 * Every editable string, color, toggle and link the theme exposes in the
 * Customizer is keyed here. Templates read values through keytobd_mod() so a
 * site owner can change ALL front-end content without touching code.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Default value for every theme option.
 *
 * @return array<string,mixed>
 */
function keytobd_defaults() {
	return array(
		// Colors — "Feel the Nature" palette.
		'color_navy'        => '#0E2F44',
		'color_blue'        => '#1E6F8E',
		'color_sky'         => '#2A8DA8',
		'color_accent'      => '#FF6B35',
		'color_accent_dark' => '#E8551F',
		'color_teal'        => '#1FB6A6',
		'color_ink'         => '#14202B',

		// Contact / business.
		'phone1'            => '01684498885',
		'phone2'            => '01620203261',
		'whatsapp'          => '8801684498885',
		'email'             => '',
		'facebook'          => 'https://www.facebook.com/Keytobd',
		'instagram'         => '',
		'youtube'           => '',
		'address'           => "Hotel Motel Zone, Sugonda, Cox's Bazar, Bangladesh 4700",
		'hours'             => 'Open 7 days · 9:00 AM – 11:00 PM',
		'website'           => 'keytobd.com',
		'map_query'         => "Sugonda Point, Cox's Bazar, Bangladesh",

		// Hero.
		'hero_image'        => '',
		'hero_eyebrow'      => 'All travel solutions at your doorstep',
		'hero_title'        => 'Discover Bangladesh, booked in a few taps',
		'hero_subtitle'     => "Tours, hotels, rent-a-car, Saint Martin ship tickets and houseboat trips — plan and pay online with KeyToBD, your Cox's Bazar travel partner.",
		'hero_rating'       => '4.9/5 from 1,200+ happy travellers',
		'hero_show_search'  => true,

		// Section: services.
		'services_on'       => true,
		'services_eyebrow'  => 'What we do',
		'services_title'    => 'One platform for every trip',
		'services_text'     => 'Book multiple services in a single, secure checkout — no phone calls required.',

		// Section: packages.
		'packages_on'       => true,
		'packages_eyebrow'  => 'Top rated',
		'packages_title'    => 'Popular tour packages',
		'packages_text'     => 'Hand-picked trips travellers love this season.',
		'packages_count'    => 6,

		// Section: destinations.
		'dest_on'           => true,
		'dest_eyebrow'      => 'Where to go',
		'dest_title'        => 'Trending destinations',

		// Section: why us.
		'why_on'            => true,
		'why_eyebrow'       => 'Why KeyToBD',
		'why_title'         => 'Travel with total confidence',

		// Section: steps.
		'steps_on'          => true,
		'steps_eyebrow'     => 'Simple & fast',
		'steps_title'       => 'Book in 4 easy steps',

		// Section: testimonials.
		'testi_on'          => true,
		'testi_eyebrow'     => 'Traveller stories',
		'testi_title'       => 'Loved by travellers',

		// Section: blog.
		'blog_on'           => true,
		'blog_eyebrow'      => 'Travel guide',
		'blog_title'        => 'Tips & inspiration',

		// Section: CTA.
		'cta_on'            => true,
		'cta_title'         => 'Ready for your next adventure?',
		'cta_text'          => 'Talk to our travel experts or start booking online right now.',

		// Footer.
		'footer_about'      => 'Keytobd offers all travel solutions at your doorstep — book tours, stays, transport and tickets online with confidence.',
		'footer_cta_title'  => 'Plan your next trip with KeyToBD',
		'footer_cta_text'   => 'Tours, hotels, cars, ship tickets and houseboats — booked online in minutes.',
		'footer_credit_on'  => true,
	);
}

/**
 * Get a theme option, falling back to its registered default.
 *
 * @param string $key Option key (without the kt_ prefix).
 * @return mixed
 */
function keytobd_mod( $key ) {
	$defaults = keytobd_defaults();
	$default  = $defaults[ $key ] ?? '';
	return get_theme_mod( 'kt_' . $key, $default );
}

/**
 * Boolean option helper.
 */
function keytobd_is_on( $key ) {
	return (bool) keytobd_mod( $key );
}
