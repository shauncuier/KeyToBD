<?php
/**
 * Admin: booking list columns + settings page + plugin row link.
 *
 * @package KeyToBD_Booking
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin glue.
 */
class KTB_Admin {

	/**
	 * Hooks.
	 */
	public static function init() {
		add_filter( 'manage_ktb_booking_posts_columns', array( __CLASS__, 'columns' ) );
		add_action( 'manage_ktb_booking_posts_custom_column', array( __CLASS__, 'column' ), 10, 2 );
		add_action( 'admin_menu', array( __CLASS__, 'settings_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'register_settings' ) );
		add_filter( 'plugin_action_links_' . KTB_BASENAME, array( __CLASS__, 'action_links' ) );
		add_action( 'admin_notices', array( __CLASS__, 'dashboard_hint' ) );
	}

	/**
	 * Booking list columns.
	 *
	 * @param array $cols Columns.
	 * @return array
	 */
	public static function columns( $cols ) {
		$new = array(
			'cb'         => $cols['cb'],
			'title'      => __( 'Reference', 'keytobd-booking' ),
			'ktb_svc'    => __( 'Service', 'keytobd-booking' ),
			'ktb_cust'   => __( 'Customer', 'keytobd-booking' ),
			'ktb_date'   => __( 'Travel date', 'keytobd-booking' ),
			'ktb_qty'    => __( 'Qty', 'keytobd-booking' ),
			'ktb_total'  => __( 'Total', 'keytobd-booking' ),
			'ktb_status' => __( 'Status', 'keytobd-booking' ),
			'date'       => __( 'Booked', 'keytobd-booking' ),
		);
		return $new;
	}

	/**
	 * Render a custom column.
	 *
	 * @param string $col Column key.
	 * @param int    $id  Booking ID.
	 */
	public static function column( $col, $id ) {
		switch ( $col ) {
			case 'ktb_svc':
				$svc = (int) get_post_meta( $id, '_ktb_service_id', true );
				echo $svc ? esc_html( get_the_title( $svc ) ) : '—';
				break;
			case 'ktb_cust':
				printf(
					'%s<br><small><a href="tel:%s">%s</a></small>',
					esc_html( get_post_meta( $id, '_ktb_name', true ) ),
					esc_attr( get_post_meta( $id, '_ktb_phone', true ) ),
					esc_html( get_post_meta( $id, '_ktb_phone', true ) )
				);
				break;
			case 'ktb_date':
				$d  = get_post_meta( $id, '_ktb_date', true );
				$de = get_post_meta( $id, '_ktb_date_end', true );
				echo esc_html( $d . ( $de ? ' → ' . $de : '' ) );
				break;
			case 'ktb_qty':
				echo esc_html( get_post_meta( $id, '_ktb_qty', true ) );
				break;
			case 'ktb_total':
				echo esc_html( ktb_price( get_post_meta( $id, '_ktb_total', true ) ) );
				break;
			case 'ktb_status':
				$statuses = ktb_statuses();
				$st       = get_post_status( $id );
				$colors   = array(
					'ktb-pending'   => '#9a6700',
					'ktb-confirmed' => '#137a4a',
					'ktb-cancelled' => '#b32d23',
					'ktb-completed' => '#2C5F8A',
				);
				printf(
					'<span style="display:inline-block;padding:3px 10px;border-radius:20px;font-size:11px;font-weight:600;color:#fff;background:%s">%s</span>',
					esc_attr( $colors[ $st ] ?? '#777' ),
					esc_html( $statuses[ $st ] ?? $st )
				);
				break;
		}
	}

	/**
	 * Settings submenu under Bookings.
	 */
	public static function settings_page() {
		add_submenu_page(
			'edit.php?post_type=ktb_service',
			__( 'Booking Settings', 'keytobd-booking' ),
			__( 'Settings', 'keytobd-booking' ),
			'manage_options',
			'ktb-settings',
			array( __CLASS__, 'render_settings' )
		);
	}

	/**
	 * Register the single settings option with sanitize.
	 */
	public static function register_settings() {
		register_setting( 'ktb_settings_group', 'ktb_settings', array( __CLASS__, 'sanitize' ) );
	}

	/**
	 * Sanitize settings.
	 *
	 * @param array $in Raw input.
	 * @return array
	 */
	public static function sanitize( $in ) {
		$d = ktb_default_settings();
		return array(
			'currency'               => isset( $in['currency'] ) ? sanitize_text_field( $in['currency'] ) : '৳',
			'admin_email'            => isset( $in['admin_email'] ) ? sanitize_email( $in['admin_email'] ) : get_option( 'admin_email' ),
			'auto_confirm'           => empty( $in['auto_confirm'] ) ? 0 : 1,
			'success_msg'            => isset( $in['success_msg'] ) ? sanitize_text_field( $in['success_msg'] ) : '',
			'deposit_percent'        => isset( $in['deposit_percent'] ) ? min( 100, max( 0, (float) $in['deposit_percent'] ) ) : 0,
			'min_lead_days'          => isset( $in['min_lead_days'] ) ? absint( $in['min_lead_days'] ) : 0,
			'max_party'              => isset( $in['max_party'] ) ? max( 1, absint( $in['max_party'] ) ) : 20,
			'terms_url'              => isset( $in['terms_url'] ) ? esc_url_raw( $in['terms_url'] ) : '',
			'require_terms'          => empty( $in['require_terms'] ) ? 0 : 1,
			'coupons'                => isset( $in['coupons'] ) ? sanitize_textarea_field( $in['coupons'] ) : '',
			'email_subject_admin'    => isset( $in['email_subject_admin'] ) ? sanitize_text_field( $in['email_subject_admin'] ) : $d['email_subject_admin'],
			'email_subject_customer' => isset( $in['email_subject_customer'] ) ? sanitize_text_field( $in['email_subject_customer'] ) : $d['email_subject_customer'],
			'rl_count'               => isset( $in['rl_count'] ) ? absint( $in['rl_count'] ) : 5,
			'rl_window'              => isset( $in['rl_window'] ) ? absint( $in['rl_window'] ) : 600,
			'honeypot'               => empty( $in['honeypot'] ) ? 0 : 1,
			'min_seconds'            => isset( $in['min_seconds'] ) ? absint( $in['min_seconds'] ) : 3,
			'turnstile_site'         => isset( $in['turnstile_site'] ) ? sanitize_text_field( $in['turnstile_site'] ) : '',
			'turnstile_secret'       => isset( $in['turnstile_secret'] ) ? sanitize_text_field( $in['turnstile_secret'] ) : '',
			'require_login'          => empty( $in['require_login'] ) ? 0 : 1,
			'require_verify'         => empty( $in['require_verify'] ) ? 0 : 1,
			'google_client_id'       => isset( $in['google_client_id'] ) ? sanitize_text_field( $in['google_client_id'] ) : '',
		);
	}

	/**
	 * Settings UI.
	 */
	public static function render_settings() {
		$s = get_option( 'ktb_settings', array() );
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'KeyToBD Booking — Settings', 'keytobd-booking' ); ?></h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'ktb_settings_group' ); ?>
				<table class="form-table" role="presentation">
					<tr>
						<th><label for="ktb_currency"><?php esc_html_e( 'Currency symbol', 'keytobd-booking' ); ?></label></th>
						<td><input name="ktb_settings[currency]" id="ktb_currency" type="text" value="<?php echo esc_attr( $s['currency'] ?? '৳' ); ?>" class="small-text"></td>
					</tr>
					<tr>
						<th><label for="ktb_admin_email"><?php esc_html_e( 'Notification email', 'keytobd-booking' ); ?></label></th>
						<td><input name="ktb_settings[admin_email]" id="ktb_admin_email" type="email" value="<?php echo esc_attr( $s['admin_email'] ?? get_option( 'admin_email' ) ); ?>" class="regular-text"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Auto-confirm bookings', 'keytobd-booking' ); ?></th>
						<td><label><input name="ktb_settings[auto_confirm]" type="checkbox" value="1" <?php checked( ! empty( $s['auto_confirm'] ) ); ?>> <?php esc_html_e( 'Mark new bookings as Confirmed immediately (otherwise Pending).', 'keytobd-booking' ); ?></label></td>
					</tr>
					<tr>
						<th><label for="ktb_success"><?php esc_html_e( 'Success message', 'keytobd-booking' ); ?></label></th>
						<td><input name="ktb_settings[success_msg]" id="ktb_success" type="text" value="<?php echo esc_attr( $s['success_msg'] ?? '' ); ?>" class="large-text"></td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Booking rules', 'keytobd-booking' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th><label for="ktb_deposit"><?php esc_html_e( 'Deposit (%)', 'keytobd-booking' ); ?></label></th>
						<td><input name="ktb_settings[deposit_percent]" id="ktb_deposit" type="number" min="0" max="100" step="1" value="<?php echo esc_attr( $s['deposit_percent'] ?? 0 ); ?>" class="small-text"> <span class="description"><?php esc_html_e( '0 = full payment.', 'keytobd-booking' ); ?></span></td>
					</tr>
					<tr>
						<th><label for="ktb_lead"><?php esc_html_e( 'Default lead time (days)', 'keytobd-booking' ); ?></label></th>
						<td><input name="ktb_settings[min_lead_days]" id="ktb_lead" type="number" min="0" step="1" value="<?php echo esc_attr( $s['min_lead_days'] ?? 0 ); ?>" class="small-text"></td>
					</tr>
					<tr>
						<th><label for="ktb_maxp"><?php esc_html_e( 'Max party size', 'keytobd-booking' ); ?></label></th>
						<td><input name="ktb_settings[max_party]" id="ktb_maxp" type="number" min="1" step="1" value="<?php echo esc_attr( $s['max_party'] ?? 20 ); ?>" class="small-text"></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Require terms', 'keytobd-booking' ); ?></th>
						<td><label><input name="ktb_settings[require_terms]" type="checkbox" value="1" <?php checked( ! empty( $s['require_terms'] ) ); ?>> <?php esc_html_e( 'Show a terms checkbox on the booking form.', 'keytobd-booking' ); ?></label></td>
					</tr>
					<tr>
						<th><label for="ktb_terms_url"><?php esc_html_e( 'Terms URL', 'keytobd-booking' ); ?></label></th>
						<td><input name="ktb_settings[terms_url]" id="ktb_terms_url" type="url" value="<?php echo esc_attr( $s['terms_url'] ?? '' ); ?>" class="regular-text" placeholder="https://…/terms"></td>
					</tr>
					<tr>
						<th><label for="ktb_coupons"><?php esc_html_e( 'Coupons', 'keytobd-booking' ); ?></label></th>
						<td>
							<textarea name="ktb_settings[coupons]" id="ktb_coupons" rows="4" class="large-text code" placeholder="EID10|percent|10&#10;WELCOME|fixed|500"><?php echo esc_textarea( $s['coupons'] ?? '' ); ?></textarea>
							<p class="description"><?php esc_html_e( 'One per line: CODE|percent|10 or CODE|fixed|500', 'keytobd-booking' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Customer accounts', 'keytobd-booking' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th><?php esc_html_e( 'Require login to book', 'keytobd-booking' ); ?></th>
						<td><label><input name="ktb_settings[require_login]" type="checkbox" value="1" <?php checked( ! empty( $s['require_login'] ) ); ?>> <?php esc_html_e( 'Only logged-in users can submit a booking.', 'keytobd-booking' ); ?></label>
						<?php if ( ! get_option( 'users_can_register' ) ) : ?><p class="description" style="color:#b32d23"><?php printf( wp_kses_post( __( 'Note: user registration is OFF in <a href="%s">Settings → General</a> — customers cannot self-register.', 'keytobd-booking' ) ), esc_url( admin_url( 'options-general.php' ) ) ); ?></p><?php endif; ?></td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Require verified email', 'keytobd-booking' ); ?></th>
						<td><label><input name="ktb_settings[require_verify]" type="checkbox" value="1" <?php checked( ! empty( $s['require_verify'] ) ); ?>> <?php esc_html_e( 'User must click the email verification link before booking.', 'keytobd-booking' ); ?></label></td>
					</tr>
					<tr>
						<th><label for="ktb_gid"><?php esc_html_e( 'Google Sign-In client ID', 'keytobd-booking' ); ?></label></th>
						<td>
							<input name="ktb_settings[google_client_id]" id="ktb_gid" type="text" value="<?php echo esc_attr( $s['google_client_id'] ?? '' ); ?>" class="large-text" placeholder="xxxxx.apps.googleusercontent.com">
							<p class="description"><?php esc_html_e( 'Optional. Paste a Google OAuth Client ID to show "Continue with Google" on the account page. Authorized JavaScript origin must be your site URL.', 'keytobd-booking' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Security', 'keytobd-booking' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th><label for="ktb_rlc"><?php esc_html_e( 'Rate limit', 'keytobd-booking' ); ?></label></th>
						<td>
							<input name="ktb_settings[rl_count]" id="ktb_rlc" type="number" min="0" step="1" value="<?php echo esc_attr( $s['rl_count'] ?? 5 ); ?>" class="small-text">
							<?php esc_html_e( 'submissions per', 'keytobd-booking' ); ?>
							<input name="ktb_settings[rl_window]" type="number" min="0" step="1" value="<?php echo esc_attr( $s['rl_window'] ?? 600 ); ?>" class="small-text">
							<?php esc_html_e( 'seconds, per IP (0 = off).', 'keytobd-booking' ); ?>
						</td>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Anti-bot', 'keytobd-booking' ); ?></th>
						<td>
							<label><input name="ktb_settings[honeypot]" type="checkbox" value="1" <?php checked( ! empty( $s['honeypot'] ) ); ?>> <?php esc_html_e( 'Honeypot field', 'keytobd-booking' ); ?></label><br>
							<label><?php esc_html_e( 'Min form time (seconds):', 'keytobd-booking' ); ?> <input name="ktb_settings[min_seconds]" type="number" min="0" step="1" value="<?php echo esc_attr( $s['min_seconds'] ?? 3 ); ?>" class="small-text"></label>
						</td>
					</tr>
					<tr>
						<th><label for="ktb_ts_site"><?php esc_html_e( 'Cloudflare Turnstile', 'keytobd-booking' ); ?></label></th>
						<td>
							<input name="ktb_settings[turnstile_site]" id="ktb_ts_site" type="text" value="<?php echo esc_attr( $s['turnstile_site'] ?? '' ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Site key', 'keytobd-booking' ); ?>"><br>
							<input name="ktb_settings[turnstile_secret]" type="password" value="<?php echo esc_attr( $s['turnstile_secret'] ?? '' ); ?>" class="regular-text" placeholder="<?php esc_attr_e( 'Secret key', 'keytobd-booking' ); ?>" autocomplete="off">
							<p class="description"><?php esc_html_e( 'Optional. Set both keys to require a captcha on the booking form.', 'keytobd-booking' ); ?></p>
						</td>
					</tr>
				</table>

				<h2><?php esc_html_e( 'Email subjects', 'keytobd-booking' ); ?></h2>
				<table class="form-table" role="presentation">
					<tr>
						<th><label for="ktb_es_admin"><?php esc_html_e( 'Admin subject', 'keytobd-booking' ); ?></label></th>
						<td><input name="ktb_settings[email_subject_admin]" id="ktb_es_admin" type="text" value="<?php echo esc_attr( $s['email_subject_admin'] ?? '' ); ?>" class="large-text"></td>
					</tr>
					<tr>
						<th><label for="ktb_es_cust"><?php esc_html_e( 'Customer subject', 'keytobd-booking' ); ?></label></th>
						<td><input name="ktb_settings[email_subject_customer]" id="ktb_es_cust" type="text" value="<?php echo esc_attr( $s['email_subject_customer'] ?? '' ); ?>" class="large-text"><p class="description"><?php esc_html_e( 'Tokens: {ref} {name}', 'keytobd-booking' ); ?></p></td>
					</tr>
				</table>

				<?php submit_button(); ?>
			</form>

			<hr>
			<h2><?php esc_html_e( 'Export', 'keytobd-booking' ); ?></h2>
			<p>
				<a class="button button-secondary" href="<?php echo esc_url( KTB_Export::url() ); ?>"><?php esc_html_e( 'Download all bookings (CSV)', 'keytobd-booking' ); ?></a>
				<a class="button" href="<?php echo esc_url( KTB_Export::url( 'ktb-confirmed' ) ); ?>"><?php esc_html_e( 'Confirmed only', 'keytobd-booking' ); ?></a>
			</p>

			<hr>
			<h2><?php esc_html_e( 'How to use', 'keytobd-booking' ); ?></h2>
			<ol>
				<li><?php esc_html_e( 'Add bookable items under Bookings → Add Service (set type, price, capacity).', 'keytobd-booking' ); ?></li>
				<li><?php printf( wp_kses_post( __( 'Show a booking form anywhere with %s.', 'keytobd-booking' ) ), '<code>[ktb_booking_form]</code>' ); ?></li>
				<li><?php printf( wp_kses_post( __( 'Lock a form to one service with %s.', 'keytobd-booking' ) ), '<code>[ktb_booking_form service="ID"]</code>' ); ?></li>
				<li><?php printf( wp_kses_post( __( 'List services with %s.', 'keytobd-booking' ) ), '<code>[ktb_services type="tour" count="6"]</code>' ); ?></li>
				<li><?php esc_html_e( 'A booking form is auto-added to every single Service page.', 'keytobd-booking' ); ?></li>
			</ol>
			<p><?php printf( wp_kses_post( __( 'Payment gateways can hook the %s filter to return a redirect URL (e.g. SSLCommerz / bKash).', 'keytobd-booking' ) ), '<code>ktb_payment_url</code>' ); ?></p>
		</div>
		<?php
	}

	/**
	 * Settings link on the Plugins screen.
	 *
	 * @param array $links Action links.
	 * @return array
	 */
	public static function action_links( $links ) {
		$url = admin_url( 'edit.php?post_type=ktb_service&page=ktb-settings' );
		array_unshift( $links, '<a href="' . esc_url( $url ) . '">' . esc_html__( 'Settings', 'keytobd-booking' ) . '</a>' );
		return $links;
	}

	/**
	 * One-time hint when no services exist yet.
	 */
	public static function dashboard_hint() {
		$screen = get_current_screen();
		if ( ! $screen || 'edit-ktb_service' !== $screen->id ) {
			return;
		}
		$count = wp_count_posts( 'ktb_service' );
		if ( ( (int) $count->publish ) > 0 ) {
			return;
		}
		echo '<div class="notice notice-info"><p>' . esc_html__( 'Add your first bookable service to start taking bookings. Set its type, price and capacity in the Booking Configuration box.', 'keytobd-booking' ) . '</p></div>';
	}
}
