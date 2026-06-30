<?php
/**
 * Custom taxonomy: Destinations.
 *
 * Links WooCommerce booking products AND blog posts to a place
 * (Cox's Bazar, Saint Martin, Sundarbans, Sylhet/Tanguar Haor, etc.).
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the Destination taxonomy.
 */
function keytobd_register_destination_tax() {
	$labels = array(
		'name'              => __( 'Destinations', 'keytobd' ),
		'singular_name'     => __( 'Destination', 'keytobd' ),
		'search_items'      => __( 'Search Destinations', 'keytobd' ),
		'all_items'         => __( 'All Destinations', 'keytobd' ),
		'edit_item'         => __( 'Edit Destination', 'keytobd' ),
		'update_item'       => __( 'Update Destination', 'keytobd' ),
		'add_new_item'      => __( 'Add New Destination', 'keytobd' ),
		'new_item_name'     => __( 'New Destination Name', 'keytobd' ),
		'menu_name'         => __( 'Destinations', 'keytobd' ),
	);

	register_taxonomy(
		'destination',
		array( 'product', 'post', 'ktb_service' ),
		array(
			'labels'            => $labels,
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'destinations' ),
		)
	);
}
add_action( 'init', 'keytobd_register_destination_tax' );

/**
 * Seed the default destinations on theme activation.
 */
function keytobd_seed_destinations() {
	$places = array(
		"Cox's Bazar",
		'Saint Martin',
		'Sundarbans',
		'Sylhet & Tanguar Haor',
		'Rangamati & Kaptai',
		'Bandarban',
		'Sajek Valley',
	);
	foreach ( $places as $place ) {
		if ( ! term_exists( $place, 'destination' ) ) {
			wp_insert_term( $place, 'destination' );
		}
	}
}
add_action( 'after_switch_theme', 'keytobd_seed_destinations' );
