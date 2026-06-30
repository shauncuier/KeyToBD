<?php
/**
 * Shared service query builder — used by both the no-JS archive (pre_get_posts)
 * and the AJAX live filter so results are always identical.
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Service catalog query helpers.
 */
class KTB_Query {

	/**
	 * Normalize raw request params.
	 *
	 * @param array $raw $_GET / $_POST.
	 * @return array
	 */
	public static function params( $raw ) {
		return array(
			'type'        => isset( $raw['ktb_type'] ) ? sanitize_key( wp_unslash( $raw['ktb_type'] ) ) : '',
			'destination' => isset( $raw['destination'] ) ? sanitize_title( wp_unslash( $raw['destination'] ) ) : '',
			'q'           => isset( $raw['ktb_q'] ) ? sanitize_text_field( wp_unslash( $raw['ktb_q'] ) ) : '',
			'min'         => isset( $raw['min'] ) && '' !== $raw['min'] ? (float) $raw['min'] : '',
			'max'         => isset( $raw['max'] ) && '' !== $raw['max'] ? (float) $raw['max'] : '',
			'sort'        => isset( $raw['sort'] ) ? sanitize_key( wp_unslash( $raw['sort'] ) ) : '',
			'date'        => isset( $raw['date'] ) ? sanitize_text_field( wp_unslash( $raw['date'] ) ) : '',
			'paged'       => isset( $raw['paged'] ) ? max( 1, absint( $raw['paged'] ) ) : 1,
			'per_page'    => isset( $raw['per_page'] ) ? min( 24, max( 3, absint( $raw['per_page'] ) ) ) : 9,
		);
	}

	/**
	 * Build WP_Query args from normalized params.
	 *
	 * @param array $p Params from self::params().
	 * @return array
	 */
	public static function build_args( $p ) {
		$args = array(
			'post_type'           => 'ktb_service',
			'post_status'         => 'publish',
			'posts_per_page'      => $p['per_page'],
			'paged'               => $p['paged'],
			'ignore_sticky_posts' => true,
		);

		// Meta: type + price range.
		$meta = array( 'relation' => 'AND' );
		if ( $p['type'] && array_key_exists( $p['type'], ktb_service_types() ) ) {
			$meta[] = array( 'key' => '_ktb_type', 'value' => $p['type'] );
		}
		if ( '' !== $p['min'] ) {
			$meta[] = array( 'key' => '_ktb_price', 'value' => $p['min'], 'type' => 'NUMERIC', 'compare' => '>=' );
		}
		if ( '' !== $p['max'] ) {
			$meta[] = array( 'key' => '_ktb_price', 'value' => $p['max'], 'type' => 'NUMERIC', 'compare' => '<=' );
		}
		if ( count( $meta ) > 1 ) {
			$args['meta_query'] = $meta;
		}

		// Destination taxonomy.
		if ( $p['destination'] ) {
			$args['tax_query'] = array( array(
				'taxonomy' => 'destination',
				'field'    => 'slug',
				'terms'    => $p['destination'],
			) );
		}

		// Sort.
		switch ( $p['sort'] ) {
			case 'price_low':
				$args['meta_key'] = '_ktb_price';
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'ASC';
				break;
			case 'price_high':
				$args['meta_key'] = '_ktb_price';
				$args['orderby']  = 'meta_value_num';
				$args['order']    = 'DESC';
				break;
			case 'name':
				$args['orderby'] = 'title';
				$args['order']   = 'ASC';
				break;
			default:
				$args['orderby'] = 'date';
				$args['order']   = 'DESC';
		}

		// Date availability — exclude services with no remaining slots on that date.
		if ( $p['date'] && self::valid_date( $p['date'] ) ) {
			$out = self::sold_out_ids( $p['date'] );
			if ( $out ) {
				$args['post__not_in'] = $out;
			}
		}

		// Keyword handled via posts_search clause (kept on branded archive).
		if ( $p['q'] ) {
			$args['ktb_kw'] = $p['q'];
		}

		return $args;
	}

	/**
	 * Run a fresh catalog query (AJAX path).
	 *
	 * @param array $p Params.
	 * @return WP_Query
	 */
	public static function run( $p ) {
		$args = self::build_args( $p );
		$kw   = isset( $args['ktb_kw'] ) ? $args['ktb_kw'] : '';
		if ( $kw ) {
			add_filter( 'posts_search', array( __CLASS__, 'kw_clause' ), 10, 2 );
		}
		$q = new WP_Query( $args );
		if ( $kw ) {
			remove_filter( 'posts_search', array( __CLASS__, 'kw_clause' ), 10 );
		}
		return $q;
	}

	/**
	 * Keyword LIKE clause (title/content) — fires for any query carrying ktb_kw.
	 *
	 * @param string   $search Existing SQL.
	 * @param WP_Query $q      Query.
	 * @return string
	 */
	public static function kw_clause( $search, $q ) {
		$kw = $q->get( 'ktb_kw' );
		if ( ! $kw ) {
			return $search;
		}
		global $wpdb;
		$like = '%' . $wpdb->esc_like( $kw ) . '%';
		return $wpdb->prepare( " AND ({$wpdb->posts}.post_title LIKE %s OR {$wpdb->posts}.post_content LIKE %s) ", $like, $like );
	}

	/**
	 * Render the result cards for a query, delegating card markup to the theme
	 * (filter `ktb_service_card_html`) so AJAX + server output match exactly.
	 *
	 * @param WP_Query $q Query.
	 * @return string
	 */
	public static function render_cards( $q ) {
		$html = '';
		if ( $q->have_posts() ) {
			while ( $q->have_posts() ) {
				$q->the_post();
				$svc  = ktb_get_service( get_the_ID() );
				$card = apply_filters( 'ktb_service_card_html', '', $svc, get_the_ID() );
				if ( '' === $card ) {
					ob_start();
					ktb_template( 'service-card.php', array( 'service' => $svc ) );
					$card = ob_get_clean();
				}
				$html .= $card;
			}
			wp_reset_postdata();
		}
		return $html;
	}

	/**
	 * Min/max price across all published services (for the slider bounds).
	 *
	 * @return array{min:int,max:int}
	 */
	public static function price_bounds() {
		global $wpdb;
		$row = $wpdb->get_row( "SELECT MIN(CAST(meta_value AS DECIMAL(12,2))) AS lo, MAX(CAST(meta_value AS DECIMAL(12,2))) AS hi FROM {$wpdb->postmeta} WHERE meta_key = '_ktb_price'" );
		$min = $row && null !== $row->lo ? (int) floor( $row->lo ) : 0;
		$max = $row && null !== $row->hi ? (int) ceil( $row->hi ) : 100000;
		if ( $max <= $min ) {
			$max = $min + 1000;
		}
		return array( 'min' => $min, 'max' => $max );
	}

	/**
	 * Service IDs that are fully booked on a date.
	 *
	 * @param string $date Y-m-d.
	 * @return int[]
	 */
	private static function sold_out_ids( $date ) {
		$ids = get_posts( array( 'post_type' => 'ktb_service', 'posts_per_page' => -1, 'fields' => 'ids', 'post_status' => 'publish' ) );
		$out = array();
		foreach ( $ids as $id ) {
			if ( 0 === ktb_availability( $id, $date ) ) {
				$out[] = $id;
			}
		}
		return $out;
	}

	/**
	 * Validate Y-m-d.
	 *
	 * @param string $date Date.
	 * @return bool
	 */
	private static function valid_date( $date ) {
		$d = DateTime::createFromFormat( 'Y-m-d', $date );
		return $d && $d->format( 'Y-m-d' ) === $date;
	}
}
