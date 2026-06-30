<?php
/**
 * Elementor & Elementor Pro compatibility.
 *
 * - Declares theme support so Elementor knows the theme cooperates.
 * - Registers the core Theme Builder locations (header, footer, single, archive)
 *   so Elementor Pro can fully take over those areas. The theme's own header.php
 *   / footer.php fall back gracefully when no Elementor template is assigned.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Tell Elementor the theme is compatible (enables full header/footer support
 * and silences the "theme does not declare support" notice).
 */
function keytobd_elementor_support() {
	add_theme_support( 'elementor' );
	// Header/footer locations come from register_all_core_location() below.
}
add_action( 'after_setup_theme', 'keytobd_elementor_support' );

/**
 * Register all core Theme Builder locations (header, footer, single, archive).
 *
 * @param \ElementorPro\Modules\ThemeBuilder\Classes\Locations_Manager $manager Locations manager.
 */
function keytobd_register_elementor_locations( $manager ) {
	$manager->register_all_core_location();
}
add_action( 'elementor/theme/register_locations', 'keytobd_register_elementor_locations' );

/**
 * Render an Elementor Theme Builder location if one is assigned.
 * Returns true when Elementor output the location (so the theme can skip its own).
 *
 * @param string $location header|footer|single|archive.
 * @return bool
 */
function keytobd_do_elementor_location( $location ) {
	return function_exists( 'elementor_theme_do_location' ) && elementor_theme_do_location( $location );
}
