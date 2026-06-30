<?php
/**
 * CSV export of bookings (admin only).
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Bookings CSV export.
 */
class KTB_Export {

	/**
	 * Hook the admin_post handler.
	 */
	public static function init() {
		add_action( 'admin_post_ktb_export_bookings', array( __CLASS__, 'download' ) );
	}

	/**
	 * Secure download URL for the export button.
	 *
	 * @param string $status Optional status filter.
	 * @return string
	 */
	public static function url( $status = '' ) {
		return wp_nonce_url(
			admin_url( 'admin-post.php?action=ktb_export_bookings' . ( $status ? '&status=' . rawurlencode( $status ) : '' ) ),
			'ktb_export',
			'ktb_nonce'
		);
	}

	/**
	 * Stream the CSV.
	 */
	public static function download() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You are not allowed to export bookings.', 'keytobd-booking' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'ktb_export', 'ktb_nonce' );

		$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : '';
		$statuses = array_key_exists( $status, ktb_statuses() ) ? array( $status ) : array_keys( ktb_statuses() );

		$ids = get_posts( array(
			'post_type'      => 'ktb_booking',
			'post_status'    => $statuses,
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'orderby'        => 'date',
			'order'          => 'DESC',
		) );

		nocache_headers();
		header( 'Content-Type: text/csv; charset=utf-8' );
		header( 'Content-Disposition: attachment; filename=keytobd-bookings-' . gmdate( 'Y-m-d' ) . '.csv' );

		$out = fopen( 'php://output', 'w' );
		fprintf( $out, "\xEF\xBB\xBF" ); // UTF-8 BOM for Excel.
		fputcsv( $out, array( 'Reference', 'Status', 'Service', 'Customer', 'Phone', 'Email', 'Date', 'End', 'Qty', 'Subtotal', 'Coupon', 'Discount', 'Total', 'Deposit Due', 'Payment', 'Booked At' ) );

		$labels = ktb_statuses();
		foreach ( $ids as $id ) {
			$svc = (int) get_post_meta( $id, '_ktb_service_id', true );
			$row = array(
				get_post_meta( $id, '_ktb_ref', true ),
				$labels[ get_post_status( $id ) ] ?? get_post_status( $id ),
				$svc ? get_the_title( $svc ) : '',
				get_post_meta( $id, '_ktb_name', true ),
				get_post_meta( $id, '_ktb_phone', true ),
				get_post_meta( $id, '_ktb_email', true ),
				get_post_meta( $id, '_ktb_date', true ),
				get_post_meta( $id, '_ktb_date_end', true ),
				get_post_meta( $id, '_ktb_qty', true ),
				get_post_meta( $id, '_ktb_subtotal', true ),
				get_post_meta( $id, '_ktb_coupon', true ),
				get_post_meta( $id, '_ktb_discount', true ),
				get_post_meta( $id, '_ktb_total', true ),
				get_post_meta( $id, '_ktb_deposit_due', true ),
				get_post_meta( $id, '_ktb_payment', true ),
				get_the_date( 'Y-m-d H:i', $id ),
			);
			fputcsv( $out, array_map( 'ktb_csv_cell', $row ) );
		}
		fclose( $out ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		exit;
	}
}
KTB_Export::init();
