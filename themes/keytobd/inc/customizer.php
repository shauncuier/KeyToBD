<?php
/**
 * Customizer — exposes every editable option under one "KeyToBD Options" panel.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register all theme controls.
 *
 * @param WP_Customize_Manager $wp_customize Customizer instance.
 */
function keytobd_customize_register( $wp_customize ) {
	$defaults = keytobd_defaults();

	$wp_customize->add_panel( 'kt_panel', array(
		'title'    => __( 'KeyToBD Options', 'keytobd' ),
		'priority' => 20,
	) );

	/**
	 * Helper to add a control + setting in one call.
	 *
	 * @param string $key     Option key (no kt_ prefix).
	 * @param string $section Section id.
	 * @param string $label   Control label.
	 * @param string $type    text|textarea|checkbox|color|image|select|number.
	 * @param array  $choices Options for select.
	 */
	$add = function ( $key, $section, $label, $type = 'text', $choices = array() ) use ( $wp_customize, $defaults ) {
		$id        = 'kt_' . $key;
		$default   = $defaults[ $key ] ?? '';
		$transport = in_array( $type, array( 'color', 'image' ), true ) ? 'postMessage' : 'refresh';

		$sanitize = 'sanitize_text_field';
		switch ( $type ) {
			case 'textarea':
				$sanitize = 'wp_kses_post';
				break;
			case 'checkbox':
				$sanitize = function ( $v ) { return (bool) $v; };
				break;
			case 'color':
				$sanitize = 'sanitize_hex_color';
				break;
			case 'image':
				$sanitize = 'esc_url_raw';
				break;
			case 'number':
				$sanitize = 'absint';
				break;
			case 'select':
				$sanitize = function ( $v ) use ( $choices ) { return array_key_exists( $v, $choices ) ? $v : ''; };
				break;
		}

		$wp_customize->add_setting( $id, array(
			'default'           => $default,
			'sanitize_callback' => $sanitize,
			'transport'         => $transport,
		) );

		if ( 'color' === $type ) {
			$wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, $id, array(
				'label'   => $label,
				'section' => $section,
			) ) );
		} elseif ( 'image' === $type ) {
			$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, $id, array(
				'label'   => $label,
				'section' => $section,
			) ) );
		} else {
			$args = array(
				'label'   => $label,
				'section' => $section,
				'type'    => $type,
			);
			if ( 'select' === $type ) {
				$args['choices'] = $choices;
			}
			$wp_customize->add_control( $id, $args );
		}
	};

	// ---- Colors ----
	$wp_customize->add_section( 'kt_colors', array( 'title' => __( 'Brand Colors', 'keytobd' ), 'panel' => 'kt_panel' ) );
	$add( 'color_navy', 'kt_colors', __( 'Navy (header/footer)', 'keytobd' ), 'color' );
	$add( 'color_blue', 'kt_colors', __( 'Primary Blue', 'keytobd' ), 'color' );
	$add( 'color_sky', 'kt_colors', __( 'Sky Blue', 'keytobd' ), 'color' );
	$add( 'color_accent', 'kt_colors', __( 'Accent / Book button', 'keytobd' ), 'color' );
	$add( 'color_accent_dark', 'kt_colors', __( 'Accent (hover)', 'keytobd' ), 'color' );
	$add( 'color_teal', 'kt_colors', __( 'Teal (highlights)', 'keytobd' ), 'color' );
	$add( 'color_ink', 'kt_colors', __( 'Body Text', 'keytobd' ), 'color' );

	// ---- Contact ----
	$wp_customize->add_section( 'kt_contact', array( 'title' => __( 'Contact & Business', 'keytobd' ), 'panel' => 'kt_panel' ) );
	$add( 'phone1', 'kt_contact', __( 'Primary phone', 'keytobd' ) );
	$add( 'phone2', 'kt_contact', __( 'Secondary phone', 'keytobd' ) );
	$add( 'whatsapp', 'kt_contact', __( 'WhatsApp number (88…)', 'keytobd' ) );
	$add( 'email', 'kt_contact', __( 'Email', 'keytobd' ) );
	$add( 'address', 'kt_contact', __( 'Address', 'keytobd' ), 'textarea' );
	$add( 'hours', 'kt_contact', __( 'Opening hours', 'keytobd' ) );
	$add( 'website', 'kt_contact', __( 'Website label', 'keytobd' ) );
	$add( 'map_query', 'kt_contact', __( 'Google Map search query', 'keytobd' ) );

	// ---- Social ----
	$wp_customize->add_section( 'kt_social', array( 'title' => __( 'Social Links', 'keytobd' ), 'panel' => 'kt_panel' ) );
	$add( 'facebook', 'kt_social', __( 'Facebook URL', 'keytobd' ) );
	$add( 'instagram', 'kt_social', __( 'Instagram URL', 'keytobd' ) );
	$add( 'youtube', 'kt_social', __( 'YouTube URL', 'keytobd' ) );

	// ---- Hero ----
	$wp_customize->add_section( 'kt_hero', array( 'title' => __( 'Homepage Hero', 'keytobd' ), 'panel' => 'kt_panel' ) );
	$add( 'hero_image', 'kt_hero', __( 'Background image', 'keytobd' ), 'image' );
	$add( 'hero_eyebrow', 'kt_hero', __( 'Eyebrow', 'keytobd' ) );
	$add( 'hero_title', 'kt_hero', __( 'Heading', 'keytobd' ), 'textarea' );
	$add( 'hero_subtitle', 'kt_hero', __( 'Subtitle', 'keytobd' ), 'textarea' );
	$add( 'hero_rating', 'kt_hero', __( 'Rating text', 'keytobd' ) );
	$add( 'hero_show_search', 'kt_hero', __( 'Show search widget', 'keytobd' ), 'checkbox' );

	// ---- Homepage sections (toggle + headings) ----
	$wp_customize->add_section( 'kt_sections', array( 'title' => __( 'Homepage Sections', 'keytobd' ), 'panel' => 'kt_panel' ) );
	$rows = array(
		'services' => __( 'Services', 'keytobd' ),
		'packages' => __( 'Packages', 'keytobd' ),
		'dest'     => __( 'Destinations', 'keytobd' ),
		'why'      => __( 'Why Us', 'keytobd' ),
		'steps'    => __( 'How it works', 'keytobd' ),
		'testi'    => __( 'Testimonials', 'keytobd' ),
		'blog'     => __( 'Blog', 'keytobd' ),
		'cta'      => __( 'Final CTA', 'keytobd' ),
	);
	foreach ( $rows as $slug => $name ) {
		$add( $slug . '_on', 'kt_sections', sprintf( __( 'Show: %s', 'keytobd' ), $name ), 'checkbox' );
		if ( isset( $defaults[ $slug . '_eyebrow' ] ) ) {
			$add( $slug . '_eyebrow', 'kt_sections', sprintf( __( '%s — eyebrow', 'keytobd' ), $name ) );
		}
		if ( isset( $defaults[ $slug . '_title' ] ) ) {
			$add( $slug . '_title', 'kt_sections', sprintf( __( '%s — title', 'keytobd' ), $name ) );
		}
		if ( isset( $defaults[ $slug . '_text' ] ) ) {
			$add( $slug . '_text', 'kt_sections', sprintf( __( '%s — text', 'keytobd' ), $name ), 'textarea' );
		}
	}
	$add( 'packages_count', 'kt_sections', __( 'Packages to show', 'keytobd' ), 'number' );

	// ---- Footer ----
	$wp_customize->add_section( 'kt_footer', array( 'title' => __( 'Footer', 'keytobd' ), 'panel' => 'kt_panel' ) );
	$add( 'footer_cta_title', 'kt_footer', __( 'Footer CTA title', 'keytobd' ) );
	$add( 'footer_cta_text', 'kt_footer', __( 'Footer CTA text', 'keytobd' ), 'textarea' );
	$add( 'footer_about', 'kt_footer', __( 'Footer about text', 'keytobd' ), 'textarea' );
	$add( 'footer_credit_on', 'kt_footer', __( 'Show 3s-Soft credit', 'keytobd' ), 'checkbox' );

	// Live-preview the site title/desc that ship with core.
	$wp_customize->get_setting( 'blogname' )->transport        = 'postMessage';
	$wp_customize->get_setting( 'blogdescription' )->transport = 'postMessage';
}
add_action( 'customize_register', 'keytobd_customize_register' );

/**
 * Live-preview JS for color/image controls.
 */
function keytobd_customize_preview_js() {
	wp_enqueue_script( 'keytobd-customize-preview', KEYTOBD_URI . '/assets/js/customize-preview.js', array( 'customize-preview' ), KEYTOBD_VERSION, true );
}
add_action( 'customize_preview_init', 'keytobd_customize_preview_js' );
