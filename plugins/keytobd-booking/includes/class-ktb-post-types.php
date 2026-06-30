<?php
/**
 * Register the Service and Booking post types + custom booking statuses.
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post types + statuses.
 */
class KTB_Post_Types {

	/**
	 * Hook registration.
	 */
	public static function init() {
		add_action( 'init', array( __CLASS__, 'register' ) );
	}

	/**
	 * Register CPTs + statuses (also called on activation before flush).
	 */
	public static function register() {
		self::services();
		self::bookings();
		self::statuses();
	}

	/**
	 * Bookable service CPT (public, front-end browsable).
	 */
	private static function services() {
		register_post_type( 'ktb_service', array(
			'labels'        => array(
				'name'               => __( 'Services', 'keytobd-booking' ),
				'singular_name'      => __( 'Service', 'keytobd-booking' ),
				'add_new_item'       => __( 'Add Service', 'keytobd-booking' ),
				'edit_item'          => __( 'Edit Service', 'keytobd-booking' ),
				'new_item'           => __( 'New Service', 'keytobd-booking' ),
				'view_item'          => __( 'View Service', 'keytobd-booking' ),
				'search_items'       => __( 'Search Services', 'keytobd-booking' ),
				'menu_name'          => __( 'Bookings', 'keytobd-booking' ),
				'all_items'          => __( 'Services', 'keytobd-booking' ),
			),
			'public'        => true,
			'has_archive'   => true,
			'show_in_rest'  => true,
			'menu_icon'     => 'dashicons-palmtree',
			'menu_position' => 25,
			'rewrite'       => array( 'slug' => 'services' ),
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		) );
	}

	/**
	 * Booking record CPT (private; managed in admin under the Bookings menu).
	 */
	private static function bookings() {
		register_post_type( 'ktb_booking', array(
			'labels'        => array(
				'name'          => __( 'Bookings', 'keytobd-booking' ),
				'singular_name' => __( 'Booking', 'keytobd-booking' ),
				'edit_item'     => __( 'Booking Details', 'keytobd-booking' ),
				'search_items'  => __( 'Search Bookings', 'keytobd-booking' ),
				'all_items'     => __( 'All Bookings', 'keytobd-booking' ),
				'menu_name'     => __( 'Bookings', 'keytobd-booking' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=ktb_service',
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'capability_type'     => 'post',
			'supports'            => array( 'title' ),
			'map_meta_cap'        => true,
		) );
	}

	/**
	 * Custom booking post statuses.
	 */
	private static function statuses() {
		foreach ( ktb_statuses() as $status => $label ) {
			register_post_status( $status, array(
				'label'                     => $label,
				'public'                    => false,
				'internal'                  => true,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				/* translators: %s: number of bookings. */
				'label_count'               => _n_noop( $label . ' <span class="count">(%s)</span>', $label . ' <span class="count">(%s)</span>', 'keytobd-booking' ),
			) );
		}
	}
}
