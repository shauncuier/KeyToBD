<?php
/**
 * Hero booking search widget (tabbed: Tours / Hotels / Cars / Ship).
 * Each panel GET-submits to the WooCommerce shop with filter params.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

// Submit target: KeyToBD Booking services archive, else WooCommerce shop, else home.
if ( post_type_exists( 'ktb_service' ) && get_post_type_archive_link( 'ktb_service' ) ) {
	$shop = get_post_type_archive_link( 'ktb_service' );
} elseif ( function_exists( 'wc_get_page_permalink' ) ) {
	$shop = wc_get_page_permalink( 'shop' );
} else {
	$shop = home_url( '/' );
}

$destinations = get_terms( array( 'taxonomy' => 'destination', 'hide_empty' => false ) );
if ( is_wp_error( $destinations ) ) {
	$destinations = array();
}
?>
<div class="search-widget reveal" role="region" aria-label="<?php esc_attr_e( 'Booking search', 'keytobd' ); ?>">
	<div class="search-tabs" role="tablist">
		<button class="search-tab is-active" role="tab" aria-selected="true" data-target="tours"><?php keytobd_icon( 'compass', 18 ); ?> <?php esc_html_e( 'Tours', 'keytobd' ); ?></button>
		<button class="search-tab" role="tab" aria-selected="false" data-target="hotels"><?php keytobd_icon( 'bed', 18 ); ?> <?php esc_html_e( 'Hotels', 'keytobd' ); ?></button>
		<button class="search-tab" role="tab" aria-selected="false" data-target="cars"><?php keytobd_icon( 'car', 18 ); ?> <?php esc_html_e( 'Rent A Car', 'keytobd' ); ?></button>
		<button class="search-tab" role="tab" aria-selected="false" data-target="ship"><?php keytobd_icon( 'ship', 18 ); ?> <?php esc_html_e( 'Ship Ticket', 'keytobd' ); ?></button>
	</div>

	<!-- Tours -->
	<div class="search-panel is-active" data-panel="tours" role="tabpanel">
		<form class="search-form" action="<?php echo esc_url( $shop ); ?>" method="get">
			<input type="hidden" name="product_cat" value="tour-packages">
			<input type="hidden" name="ktb_type" value="tour">
			<div class="search-field">
				<label for="t-dest"><?php esc_html_e( 'Destination', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'map', 18 ); ?>
					<select id="t-dest" name="destination">
						<option value=""><?php esc_html_e( 'Anywhere', 'keytobd' ); ?></option>
						<?php foreach ( $destinations as $d ) : ?>
							<option value="<?php echo esc_attr( $d->slug ); ?>"><?php echo esc_html( $d->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="search-field">
				<label for="t-date"><?php esc_html_e( 'Travel Date', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'calendar', 18 ); ?><input type="date" id="t-date" name="date"></div>
			</div>
			<div class="search-field">
				<label for="t-people"><?php esc_html_e( 'Travellers', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'users', 18 ); ?>
					<select id="t-people" name="people">
						<?php for ( $i = 1; $i <= 10; $i++ ) : ?>
							<option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i . ( 10 === $i ? '+' : '' ) ); ?></option>
						<?php endfor; ?>
					</select>
				</div>
			</div>
			<div class="search-field">
				<label for="t-q"><?php esc_html_e( 'Keyword', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'search', 18 ); ?><input type="search" id="t-q" name="ktb_q" placeholder="<?php esc_attr_e( 'e.g. Sajek', 'keytobd' ); ?>"></div>
			</div>
			<button type="submit" class="btn btn--accent"><?php keytobd_icon( 'search', 18 ); ?> <?php esc_html_e( 'Search', 'keytobd' ); ?></button>
		</form>
	</div>

	<!-- Hotels -->
	<div class="search-panel" data-panel="hotels" role="tabpanel">
		<form class="search-form" action="<?php echo esc_url( $shop ); ?>" method="get">
			<input type="hidden" name="product_cat" value="hotels-resorts">
			<input type="hidden" name="ktb_type" value="hotel">
			<div class="search-field">
				<label for="h-dest"><?php esc_html_e( 'Location', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'map', 18 ); ?>
					<select id="h-dest" name="destination">
						<option value=""><?php esc_html_e( 'All locations', 'keytobd' ); ?></option>
						<?php foreach ( $destinations as $d ) : ?>
							<option value="<?php echo esc_attr( $d->slug ); ?>"><?php echo esc_html( $d->name ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
			</div>
			<div class="search-field">
				<label for="h-in"><?php esc_html_e( 'Check-in', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'calendar', 18 ); ?><input type="date" id="h-in" name="checkin"></div>
			</div>
			<div class="search-field">
				<label for="h-out"><?php esc_html_e( 'Check-out', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'calendar', 18 ); ?><input type="date" id="h-out" name="checkout"></div>
			</div>
			<div class="search-field">
				<label for="h-guests"><?php esc_html_e( 'Guests', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'users', 18 ); ?>
					<select id="h-guests" name="guests">
						<?php for ( $i = 1; $i <= 8; $i++ ) : ?>
							<option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
						<?php endfor; ?>
					</select>
				</div>
			</div>
			<button type="submit" class="btn btn--accent"><?php keytobd_icon( 'search', 18 ); ?> <?php esc_html_e( 'Search', 'keytobd' ); ?></button>
		</form>
	</div>

	<!-- Cars -->
	<div class="search-panel" data-panel="cars" role="tabpanel">
		<form class="search-form" action="<?php echo esc_url( $shop ); ?>" method="get">
			<input type="hidden" name="product_cat" value="rent-a-car">
			<input type="hidden" name="ktb_type" value="car">
			<div class="search-field">
				<label for="c-type"><?php esc_html_e( 'Vehicle', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'car', 18 ); ?>
					<select id="c-type" name="vehicle">
						<option value=""><?php esc_html_e( 'Any vehicle', 'keytobd' ); ?></option>
						<option value="chader-gari"><?php esc_html_e( 'Chader Gari', 'keytobd' ); ?></option>
						<option value="car"><?php esc_html_e( 'Car / Sedan', 'keytobd' ); ?></option>
						<option value="microbus"><?php esc_html_e( 'Microbus', 'keytobd' ); ?></option>
						<option value="tourist-bus"><?php esc_html_e( 'Tourist Bus', 'keytobd' ); ?></option>
					</select>
				</div>
			</div>
			<div class="search-field">
				<label for="c-pickup"><?php esc_html_e( 'Pick-up', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'map', 18 ); ?><input type="text" id="c-pickup" name="pickup" placeholder="<?php esc_attr_e( "Cox's Bazar", 'keytobd' ); ?>"></div>
			</div>
			<div class="search-field">
				<label for="c-date"><?php esc_html_e( 'Pick-up date', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'calendar', 18 ); ?><input type="date" id="c-date" name="date"></div>
			</div>
			<div class="search-field">
				<label for="c-days"><?php esc_html_e( 'Days', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'clock', 18 ); ?>
					<select id="c-days" name="days">
						<?php for ( $i = 1; $i <= 14; $i++ ) : ?>
							<option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
						<?php endfor; ?>
					</select>
				</div>
			</div>
			<button type="submit" class="btn btn--accent"><?php keytobd_icon( 'search', 18 ); ?> <?php esc_html_e( 'Search', 'keytobd' ); ?></button>
		</form>
	</div>

	<!-- Ship -->
	<div class="search-panel" data-panel="ship" role="tabpanel">
		<form class="search-form" action="<?php echo esc_url( $shop ); ?>" method="get">
			<input type="hidden" name="product_cat" value="ship-tickets">
			<input type="hidden" name="ktb_type" value="ship">
			<div class="search-field">
				<label for="s-route"><?php esc_html_e( 'Route', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'ship', 18 ); ?>
					<select id="s-route" name="route">
						<option value="teknaf-saintmartin"><?php esc_html_e( 'Teknaf → Saint Martin', 'keytobd' ); ?></option>
						<option value="saintmartin-teknaf"><?php esc_html_e( 'Saint Martin → Teknaf', 'keytobd' ); ?></option>
						<option value="coxsbazar-saintmartin"><?php esc_html_e( "Cox's Bazar → Saint Martin", 'keytobd' ); ?></option>
					</select>
				</div>
			</div>
			<div class="search-field">
				<label for="s-date"><?php esc_html_e( 'Travel date', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'calendar', 18 ); ?><input type="date" id="s-date" name="date"></div>
			</div>
			<div class="search-field">
				<label for="s-class"><?php esc_html_e( 'Class', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'star', 18 ); ?>
					<select id="s-class" name="seat_class">
						<option value="economy"><?php esc_html_e( 'Economy', 'keytobd' ); ?></option>
						<option value="business"><?php esc_html_e( 'Business', 'keytobd' ); ?></option>
						<option value="vip"><?php esc_html_e( 'VIP / Cabin', 'keytobd' ); ?></option>
					</select>
				</div>
			</div>
			<div class="search-field">
				<label for="s-seats"><?php esc_html_e( 'Seats', 'keytobd' ); ?></label>
				<div class="control"><?php keytobd_icon( 'users', 18 ); ?>
					<select id="s-seats" name="seats">
						<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
							<option value="<?php echo esc_attr( $i ); ?>"><?php echo esc_html( $i ); ?></option>
						<?php endfor; ?>
					</select>
				</div>
			</div>
			<button type="submit" class="btn btn--accent"><?php keytobd_icon( 'search', 18 ); ?> <?php esc_html_e( 'Find Tickets', 'keytobd' ); ?></button>
		</form>
	</div>
</div>
