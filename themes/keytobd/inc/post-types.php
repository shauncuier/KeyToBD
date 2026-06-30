<?php
/**
 * Custom post type: Testimonial.
 * Title = traveller name, content = quote, meta _kt_location = city/role.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register the testimonial CPT.
 */
function keytobd_register_testimonials() {
	register_post_type( 'kt_testimonial', array(
		'labels'        => array(
			'name'          => __( 'Testimonials', 'keytobd' ),
			'singular_name' => __( 'Testimonial', 'keytobd' ),
			'add_new_item'  => __( 'Add Testimonial', 'keytobd' ),
			'edit_item'     => __( 'Edit Testimonial', 'keytobd' ),
			'menu_name'     => __( 'Testimonials', 'keytobd' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_rest'  => true,
		'menu_icon'     => 'dashicons-format-quote',
		'menu_position' => 26,
		'supports'      => array( 'title', 'editor', 'thumbnail' ),
	) );
}
add_action( 'init', 'keytobd_register_testimonials' );

/**
 * Location/role meta box.
 */
function keytobd_testimonial_metabox() {
	add_meta_box( 'kt_testimonial_meta', __( 'Details', 'keytobd' ), function ( $post ) {
		wp_nonce_field( 'kt_testimonial_meta', 'kt_testimonial_nonce' );
		$loc    = get_post_meta( $post->ID, '_kt_location', true );
		$rating = get_post_meta( $post->ID, '_kt_rating', true );
		$rating = '' === $rating ? '5' : $rating;
		echo '<p><label><strong>' . esc_html__( 'Location / role', 'keytobd' ) . '</strong><br>';
		echo '<input type="text" name="kt_location" value="' . esc_attr( $loc ) . '" style="width:100%" placeholder="Dhaka"></label></p>';
		echo '<p><label><strong>' . esc_html__( 'Rating (1-5)', 'keytobd' ) . '</strong><br>';
		echo '<input type="number" min="1" max="5" name="kt_rating" value="' . esc_attr( $rating ) . '" style="width:80px"></label></p>';
	}, 'kt_testimonial', 'side' );
}
add_action( 'add_meta_boxes', 'keytobd_testimonial_metabox' );

/**
 * Save testimonial meta.
 */
function keytobd_save_testimonial_meta( $post_id ) {
	if ( ! isset( $_POST['kt_testimonial_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['kt_testimonial_nonce'] ), 'kt_testimonial_meta' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( isset( $_POST['kt_location'] ) ) {
		update_post_meta( $post_id, '_kt_location', sanitize_text_field( wp_unslash( $_POST['kt_location'] ) ) );
	}
	if ( isset( $_POST['kt_rating'] ) ) {
		update_post_meta( $post_id, '_kt_rating', max( 1, min( 5, absint( $_POST['kt_rating'] ) ) ) );
	}
}
add_action( 'save_post_kt_testimonial', 'keytobd_save_testimonial_meta' );
