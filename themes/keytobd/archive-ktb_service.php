<?php
/**
 * Services archive — advanced dynamic catalog.
 *
 * Filter bar (type, destination, price range, date, keyword, sort) drives an
 * AJAX live update with shareable URLs and a load-more pager. Fully functional
 * without JS too: the same params filter the main query via pre_get_posts.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

get_header();

$has_plugin = function_exists( 'ktb_service_types' ) && class_exists( 'KTB_Query' );
$types      = $has_plugin ? ktb_service_types() : array();
$archive    = get_post_type_archive_link( 'ktb_service' );
$cur        = $has_plugin ? KTB_Query::params( $_GET ) : array( 'type' => '', 'destination' => '', 'q' => '', 'min' => '', 'max' => '', 'sort' => '', 'date' => '', 'per_page' => 9 ); // phpcs:ignore WordPress.Security.NonceVerification
$bounds     = $has_plugin ? KTB_Query::price_bounds() : array( 'min' => 0, 'max' => 100000 );
$dests      = get_terms( array( 'taxonomy' => 'destination', 'hide_empty' => false ) );
$dests      = is_wp_error( $dests ) ? array() : $dests;

global $wp_query;
$found  = (int) $wp_query->found_posts;
$min_v  = '' !== $cur['min'] ? (int) $cur['min'] : $bounds['min'];
$max_v  = '' !== $cur['max'] ? (int) $cur['max'] : $bounds['max'];

$title = __( 'Our Services', 'keytobd' );
if ( $cur['type'] && isset( $types[ $cur['type'] ] ) ) {
	$title = $types[ $cur['type'] ]['label'];
}

get_template_part( 'template-parts/page-hero', null, array(
	'title'    => $title,
	'subtitle' => __( 'Filter, compare and book online in minutes.', 'keytobd' ),
	'crumbs'   => array( __( 'Services', 'keytobd' ) => '' ),
) );
?>
<main id="content" class="site-main">
	<div class="container">
		<div class="ktb-archive" data-ktb-archive
			data-action="ktb_filter"
			data-price-min="<?php echo esc_attr( $bounds['min'] ); ?>"
			data-price-max="<?php echo esc_attr( $bounds['max'] ); ?>"
			data-per-page="<?php echo esc_attr( $cur['per_page'] ); ?>">

			<!-- ========= FILTER SIDEBAR ========= -->
			<aside class="ktb-filterbar" data-ktb-filterbar>
				<button type="button" class="ktb-filter-close" data-ktb-filter-close aria-label="<?php esc_attr_e( 'Close filters', 'keytobd' ); ?>"><?php keytobd_icon( 'close', 22 ); ?></button>
				<form class="ktb-filterform" method="get" action="<?php echo esc_url( $archive ); ?>">
					<div class="ktb-fgroup">
						<span class="ktb-fgroup__label"><?php esc_html_e( 'Service type', 'keytobd' ); ?></span>
						<div class="ktb-types" role="group">
							<button type="button" class="ktb-chip<?php echo '' === $cur['type'] ? ' is-active' : ''; ?>" data-type=""><?php esc_html_e( 'All', 'keytobd' ); ?></button>
							<?php foreach ( $types as $key => $t ) : ?>
								<button type="button" class="ktb-chip<?php echo $cur['type'] === $key ? ' is-active' : ''; ?>" data-type="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $t['label'] ); ?></button>
							<?php endforeach; ?>
						</div>
						<input type="hidden" name="ktb_type" data-ktb-filter="type" value="<?php echo esc_attr( $cur['type'] ); ?>">
					</div>

					<div class="ktb-fgroup">
						<label class="ktb-fgroup__label" for="f-dest"><?php esc_html_e( 'Destination', 'keytobd' ); ?></label>
						<select id="f-dest" name="destination" data-ktb-filter="destination">
							<option value=""><?php esc_html_e( 'Anywhere', 'keytobd' ); ?></option>
							<?php foreach ( $dests as $d ) : ?>
								<option value="<?php echo esc_attr( $d->slug ); ?>" <?php selected( $cur['destination'], $d->slug ); ?>><?php echo esc_html( $d->name ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>

					<div class="ktb-fgroup">
						<span class="ktb-fgroup__label"><?php esc_html_e( 'Price range', 'keytobd' ); ?></span>
						<div class="ktb-range" data-ktb-range>
							<div class="ktb-range__track"><div class="ktb-range__fill" data-range-fill></div></div>
							<input type="range" class="ktb-range__min" min="<?php echo esc_attr( $bounds['min'] ); ?>" max="<?php echo esc_attr( $bounds['max'] ); ?>" value="<?php echo esc_attr( $min_v ); ?>" step="100" data-ktb-filter="min" name="min" aria-label="<?php esc_attr_e( 'Minimum price', 'keytobd' ); ?>">
							<input type="range" class="ktb-range__max" min="<?php echo esc_attr( $bounds['min'] ); ?>" max="<?php echo esc_attr( $bounds['max'] ); ?>" value="<?php echo esc_attr( $max_v ); ?>" step="100" data-ktb-filter="max" name="max" aria-label="<?php esc_attr_e( 'Maximum price', 'keytobd' ); ?>">
						</div>
						<div class="ktb-range__out">
							<span data-range-lo><?php echo esc_html( ktb_price( $min_v ) ); ?></span>
							<span data-range-hi><?php echo esc_html( ktb_price( $max_v ) ); ?></span>
						</div>
					</div>

					<div class="ktb-fgroup">
						<label class="ktb-fgroup__label" for="f-date"><?php esc_html_e( 'Available on', 'keytobd' ); ?></label>
						<input type="date" id="f-date" name="date" data-ktb-filter="date" value="<?php echo esc_attr( $cur['date'] ); ?>" min="<?php echo esc_attr( gmdate( 'Y-m-d' ) ); ?>">
					</div>

					<div class="ktb-fgroup">
						<label class="ktb-fgroup__label" for="f-q"><?php esc_html_e( 'Keyword', 'keytobd' ); ?></label>
						<input type="search" id="f-q" name="ktb_q" data-ktb-filter="q" value="<?php echo esc_attr( $cur['q'] ); ?>" placeholder="<?php esc_attr_e( 'e.g. Sajek, houseboat…', 'keytobd' ); ?>">
					</div>

					<button type="button" class="btn btn--ghost btn--block ktb-reset" data-ktb-reset><?php esc_html_e( 'Reset filters', 'keytobd' ); ?></button>
					<noscript><button type="submit" class="btn btn--accent btn--block"><?php esc_html_e( 'Apply', 'keytobd' ); ?></button></noscript>
				</form>
			</aside>

			<!-- ========= RESULTS ========= -->
			<section class="ktb-results-wrap">
				<button type="button" class="btn btn--ghost ktb-filter-open" data-ktb-filter-open><?php keytobd_icon( 'menu', 18 ); ?> <?php esc_html_e( 'Filters', 'keytobd' ); ?></button>
				<header class="ktb-results-head">
					<span class="ktb-count" data-ktb-count aria-live="polite"><?php
						/* translators: %d service count. */
						printf( esc_html( _n( '%d service found', '%d services found', $found, 'keytobd' ) ), absint( $found ) );
					?></span>
					<label class="ktb-sort">
						<span class="screen-reader-text"><?php esc_html_e( 'Sort by', 'keytobd' ); ?></span>
						<select data-ktb-filter="sort" name="sort">
							<option value="newest" <?php selected( $cur['sort'], 'newest' ); ?>><?php esc_html_e( 'Newest', 'keytobd' ); ?></option>
							<option value="price_low" <?php selected( $cur['sort'], 'price_low' ); ?>><?php esc_html_e( 'Price: low to high', 'keytobd' ); ?></option>
							<option value="price_high" <?php selected( $cur['sort'], 'price_high' ); ?>><?php esc_html_e( 'Price: high to low', 'keytobd' ); ?></option>
							<option value="name" <?php selected( $cur['sort'], 'name' ); ?>><?php esc_html_e( 'Name A–Z', 'keytobd' ); ?></option>
						</select>
					</label>
				</header>

				<div class="ktb-activepills" data-ktb-pills></div>

				<div class="cards-grid ktb-results" data-ktb-results aria-busy="false">
					<?php
					if ( have_posts() ) {
						while ( have_posts() ) {
							the_post();
							$svc = ktb_get_service( get_the_ID() );
							echo apply_filters( 'ktb_service_card_html', '', $svc, get_the_ID() ); // phpcs:ignore WordPress.Security.EscapeOutput
						}
					} else {
						echo '<p class="ktb-empty">' . esc_html__( 'No services match your filters.', 'keytobd' ) . '</p>';
					}
					?>
				</div>

				<div class="ktb-loadmore-wrap">
					<button class="btn btn--primary ktb-loadmore" data-ktb-more data-paged="1" data-max="<?php echo esc_attr( (int) $wp_query->max_num_pages ); ?>" <?php echo ( (int) $wp_query->max_num_pages > 1 ) ? '' : 'hidden'; ?>>
						<?php esc_html_e( 'Load more', 'keytobd' ); ?>
					</button>
				</div>
			</section>
		</div>
	</div>
</main>
<?php
get_footer();
