<?php
/**
 * Seeding required pages on theme activation.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Seed the default pages on theme activation.
 */
function keytobd_seed_pages() {
	$pages = array(
		'home' => array(
			'title'    => 'Home',
			'template' => 'default',
		),
		'blog' => array(
			'title'    => 'Blog',
			'template' => 'default',
		),
		'about' => array(
			'title'    => 'About Us',
			'template' => 'page-templates/template-about.php',
		),
		'contact' => array(
			'title'    => 'Contact',
			'template' => 'page-templates/template-contact.php',
		),
		'faq' => array(
			'title'    => 'FAQ',
			'template' => 'page-templates/template-faq.php',
		),
		'visa-processing' => array(
			'title'    => 'Visa Processing',
			'template' => 'page-templates/template-enquiry.php',
		),
		'event-management' => array(
			'title'    => 'Event Management',
			'template' => 'page-templates/template-enquiry.php',
		),
	);

	foreach ( $pages as $slug => $data ) {
		$page_exists = get_page_by_path( $slug );
		if ( ! $page_exists ) {
			$page_id = wp_insert_post( array(
				'post_title'   => $data['title'],
				'post_name'    => $slug,
				'post_status'  => 'publish',
				'post_type'    => 'page',
				'post_content' => '',
			) );

			if ( ! is_wp_error( $page_id ) && $page_id ) {
				if ( 'default' !== $data['template'] ) {
					update_post_meta( $page_id, '_wp_page_template', $data['template'] );
				}

				if ( 'home' === $slug ) {
					update_option( 'show_on_front', 'page' );
					update_option( 'page_on_front', $page_id );
				} elseif ( 'blog' === $slug ) {
					update_option( 'page_for_posts', $page_id );
				}
			}
		} else {
			$current_template = get_post_meta( $page_exists->ID, '_wp_page_template', true );
			if ( 'default' !== $data['template'] && $current_template !== $data['template'] ) {
				update_post_meta( $page_exists->ID, '_wp_page_template', $data['template'] );
			}

			if ( 'home' === $slug && 'page' !== get_option( 'show_on_front' ) ) {
				update_option( 'show_on_front', 'page' );
				update_option( 'page_on_front', $page_exists->ID );
			} elseif ( 'blog' === $slug && ! get_option( 'page_for_posts' ) ) {
				update_option( 'page_for_posts', $page_exists->ID );
			}
		}
	}
}
add_action( 'after_switch_theme', 'keytobd_seed_pages' );
