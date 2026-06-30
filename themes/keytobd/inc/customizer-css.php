<?php
/**
 * Output brand colors from the Customizer as CSS variables.
 * These override the static fallbacks in assets/css/main.css :root.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Print dynamic :root overrides in <head>.
 */
function keytobd_dynamic_css() {
	$map = array(
		'--navy'        => 'color_navy',
		'--blue'        => 'color_blue',
		'--sky'         => 'color_sky',
		'--accent'      => 'color_accent',
		'--accent-dark' => 'color_accent_dark',
		'--teal'        => 'color_teal',
		'--ink'         => 'color_ink',
	);

	$lines = array();
	foreach ( $map as $var => $key ) {
		$val = keytobd_mod( $key );
		if ( $val ) {
			$lines[] = sprintf( '%s:%s;', $var, $val );
		}
	}

	if ( empty( $lines ) ) {
		return;
	}

	printf(
		"<style id=\"keytobd-dynamic\">:root{%s}</style>\n",
		implode( '', $lines ) // phpcs:ignore WordPress.Security.EscapeOutput
	);
}
add_action( 'wp_head', 'keytobd_dynamic_css', 20 );
