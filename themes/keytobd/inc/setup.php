<?php
/**
 * One-click site setup: create pages, menus and demo content on theme
 * activation, plus an Appearance → Theme Setup screen to re-run it.
 *
 * Runs AFTER the existing term/category seeds (priority 20). Idempotent: a
 * one-time flag means deleted pages are not resurrected on re-activation, and
 * every creator guards on existence so nothing duplicates.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Site setup / demo importer.
 */
class KeyToBD_Setup {

	const FLAG = 'keytobd_setup_done';

	/**
	 * Hooks.
	 */
	public static function init() {
		add_action( 'after_switch_theme', array( __CLASS__, 'auto_run' ), 20 );
		add_action( 'admin_menu', array( __CLASS__, 'menu' ) );
		add_action( 'admin_post_keytobd_run_setup', array( __CLASS__, 'handle_run' ) );
	}

	/**
	 * Auto-run once per theme version on activation.
	 */
	public static function auto_run() {
		if ( get_option( self::FLAG ) === KEYTOBD_VERSION ) {
			return;
		}
		self::run_all();
		update_option( self::FLAG, KEYTOBD_VERSION );
	}

	/**
	 * Run every setup step.
	 *
	 * @param bool $force_content Re-seed demo content even if some exists.
	 */
	public static function run_all( $force_content = false ) {
		$pages = self::create_pages();
		self::set_front_page( $pages );
		self::create_menus( $pages );
		self::seed_content( $force_content );
		flush_rewrite_rules();
	}

	/**
	 * The page map: slug => [title, template, content].
	 *
	 * @return array<string,array>
	 */
	private static function page_defs() {
		return array(
			'home'              => array( 'Home', '', '' ),
			'about'             => array( 'About Us', 'page-templates/template-about.php', '' ),
			'contact'           => array( 'Contact', 'page-templates/template-contact.php', '' ),
			'faq'               => array( 'FAQ', 'page-templates/template-faq.php', '' ),
			'visa-processing'   => array( 'Visa Processing', 'page-templates/template-enquiry.php', 'We handle tourist and visit visa processing end to end.' ),
			'event-management'  => array( 'Event Management', 'page-templates/template-enquiry.php', 'Corporate retreats, group tours and event logistics across Bangladesh.' ),
			'blog'              => array( 'Travel Guide', '', '' ),
			'track-booking'     => array( 'Track Booking', '', '[ktb_booking_lookup]' ),
			'terms'             => array( 'Terms & Conditions', '', 'Placeholder terms & conditions. Edit this page with your booking, payment and travel terms.' ),
			'privacy-policy'    => array( 'Privacy Policy', '', 'Placeholder privacy policy. Describe how customer data is collected and used.' ),
			'refund-policy'     => array( 'Refund & Cancellation Policy', '', 'Placeholder cancellation policy. State refund windows and charges per service.' ),
		);
	}

	/**
	 * Create pages that don't exist yet.
	 *
	 * @return array<string,int> slug => page ID.
	 */
	private static function create_pages() {
		$ids = array();
		foreach ( self::page_defs() as $slug => $def ) {
			list( $title, $template, $content ) = $def;
			$existing = get_page_by_path( $slug );
			if ( $existing ) {
				// WP ships some pages (e.g. Privacy Policy) as drafts → publish so the URL works.
				if ( 'publish' !== $existing->post_status ) {
					wp_update_post( array( 'ID' => $existing->ID, 'post_status' => 'publish' ) );
				}
				$ids[ $slug ] = (int) $existing->ID;
				continue;
			}
			$id = wp_insert_post( array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $title,
				'post_name'    => $slug,
				'post_content' => $content,
			) );
			if ( ! is_wp_error( $id ) ) {
				if ( $template ) {
					update_post_meta( $id, '_wp_page_template', $template );
				}
				$ids[ $slug ] = (int) $id;
			}
		}
		return $ids;
	}

	/**
	 * Set the static front + posts page (without overriding a user's choice).
	 *
	 * @param array $pages slug => ID.
	 */
	private static function set_front_page( $pages ) {
		if ( 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' ) ) {
			return; // User already configured a front page.
		}
		if ( ! empty( $pages['home'] ) ) {
			update_option( 'show_on_front', 'page' );
			update_option( 'page_on_front', $pages['home'] );
		}
		if ( ! empty( $pages['blog'] ) ) {
			update_option( 'page_for_posts', $pages['blog'] );
		}
	}

	/**
	 * Build + assign primary and legal menus (only when empty).
	 *
	 * @param array $pages slug => ID.
	 */
	private static function create_menus( $pages ) {
		$locations = get_theme_mod( 'nav_menu_locations', array() );

		// Primary.
		$primary = self::ensure_menu( 'Primary Menu' );
		if ( $primary && ! wp_get_nav_menu_items( $primary ) ) {
			self::add_link( $primary, __( 'Home', 'keytobd' ), home_url( '/' ) );
			self::add_link( $primary, __( 'Tours', 'keytobd' ), function_exists( 'keytobd_cat_link' ) ? keytobd_cat_link( 'tour-packages' ) : home_url( '/services/' ) );
			self::add_link( $primary, __( 'Hotels', 'keytobd' ), function_exists( 'keytobd_cat_link' ) ? keytobd_cat_link( 'hotels-resorts' ) : home_url( '/services/' ) );
			self::add_link( $primary, __( 'Destinations', 'keytobd' ), home_url( '/destinations/' ) );
			if ( ! empty( $pages['about'] ) ) {
				self::add_link( $primary, __( 'About', 'keytobd' ), get_permalink( $pages['about'] ) );
			}
			if ( ! empty( $pages['contact'] ) ) {
				self::add_link( $primary, __( 'Contact', 'keytobd' ), get_permalink( $pages['contact'] ) );
			}
			if ( ! empty( $pages['track-booking'] ) ) {
				self::add_link( $primary, __( 'Track Booking', 'keytobd' ), get_permalink( $pages['track-booking'] ) );
			}
		}
		if ( $primary ) {
			$locations['primary'] = $primary;
		}

		// Legal.
		$legal = self::ensure_menu( 'Legal Menu' );
		if ( $legal && ! wp_get_nav_menu_items( $legal ) ) {
			foreach ( array( 'terms', 'privacy-policy', 'refund-policy' ) as $slug ) {
				if ( ! empty( $pages[ $slug ] ) ) {
					self::add_link( $legal, get_the_title( $pages[ $slug ] ), get_permalink( $pages[ $slug ] ) );
				}
			}
		}
		if ( $legal ) {
			$locations['legal'] = $legal;
		}

		set_theme_mod( 'nav_menu_locations', $locations );
	}

	/**
	 * Get or create a nav menu, return its term ID.
	 */
	private static function ensure_menu( $name ) {
		$menu = wp_get_nav_menu_object( $name );
		if ( $menu ) {
			return (int) $menu->term_id;
		}
		$id = wp_create_nav_menu( $name );
		return is_wp_error( $id ) ? 0 : (int) $id;
	}

	/**
	 * Add a custom link item to a menu.
	 */
	private static function add_link( $menu_id, $title, $url ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'  => $title,
			'menu-item-url'    => $url,
			'menu-item-status' => 'publish',
			'menu-item-type'   => 'custom',
		) );
	}

	/**
	 * Seed sample services (plugin), testimonials (theme) and one blog post.
	 *
	 * @param bool $force Re-seed even if content exists.
	 */
	private static function seed_content( $force = false ) {
		// Services — require the booking plugin (CPT + helpers).
		if ( post_type_exists( 'ktb_service' ) ) {
			$have = get_posts( array( 'post_type' => 'ktb_service', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) );
			if ( $force || ! $have ) {
				$services = array(
					array( 'Saint Martin 3 Day Tour', 'tour', 6500, 40, '3 Days 2 Nights', 'Teknaf / Saint Martin', 'saint-martin' ),
					array( 'Sajek Valley Cloud Tour', 'tour', 5200, 30, '2 Days 1 Night', 'Rangamati / Sajek', 'sajek-valley' ),
					array( 'Tanguar Haor Houseboat', 'houseboat', 4800, 20, '2 Days 1 Night', 'Sunamganj / Tanguar Haor', 'sylhet-tanguar-haor' ),
					array( "Cox's Bazar Sea Pearl Resort", 'hotel', 7900, 12, 'per night', "Cox's Bazar", 'coxs-bazar' ),
					array( 'Chader Gari (Jeep) Rental', 'car', 5500, 6, 'per day', 'Bandarban / Sajek', 'bandarban' ),
					array( 'Teknaf to Saint Martin Ship Ticket', 'ship', 1200, 300, 'one way', 'Teknaf to Saint Martin', 'saint-martin' ),
				);
				foreach ( $services as $s ) {
					list( $title, $type, $price, $cap, $duration, $location, $dest ) = $s;
					if ( get_posts( array( 'post_type' => 'ktb_service', 'title' => $title, 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) ) ) {
						continue;
					}
					$id = wp_insert_post( array( 'post_type' => 'ktb_service', 'post_status' => 'publish', 'post_title' => $title ) );
					if ( is_wp_error( $id ) ) {
						continue;
					}
					update_post_meta( $id, '_ktb_type', $type );
					update_post_meta( $id, '_ktb_price', $price );
					update_post_meta( $id, '_ktb_capacity', $cap );
					update_post_meta( $id, '_ktb_duration', $duration );
					update_post_meta( $id, '_ktb_location', $location );
					$term = get_term_by( 'slug', $dest, 'destination' );
					if ( $term && ! is_wp_error( $term ) ) {
						wp_set_object_terms( $id, array( (int) $term->term_id ), 'destination', false );
					}
				}
			}
		}

		// Testimonials (theme CPT).
		if ( post_type_exists( 'kt_testimonial' ) ) {
			$have = get_posts( array( 'post_type' => 'kt_testimonial', 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) );
			if ( $force || ! $have ) {
				$reviews = array(
					array( 'Tanvir Ahmed', 'Dhaka', 5, 'Booked our Saint Martin tour in minutes. Ship tickets and hotel all sorted — smoothest trip ever.' ),
					array( 'Nusrat Jahan', 'Chittagong', 5, 'The Tanguar Haor houseboat was magical. KeyToBD handled everything for our family of 12.' ),
					array( 'Rakib Hasan', 'Sylhet', 5, 'Great prices and the Chader Gari for Sajek was on time. Highly recommended!' ),
				);
				foreach ( $reviews as $r ) {
					if ( get_posts( array( 'post_type' => 'kt_testimonial', 'title' => $r[0], 'posts_per_page' => 1, 'fields' => 'ids', 'post_status' => 'any' ) ) ) {
						continue;
					}
					$id = wp_insert_post( array( 'post_type' => 'kt_testimonial', 'post_status' => 'publish', 'post_title' => $r[0], 'post_content' => $r[3] ) );
					if ( ! is_wp_error( $id ) ) {
						update_post_meta( $id, '_kt_location', $r[1] );
						update_post_meta( $id, '_kt_rating', $r[2] );
					}
				}
			}
		}

		// One sample blog post.
		if ( ! get_page_by_path( 'top-5-places-coxs-bazar', OBJECT, 'post' ) && ( $force || ! get_posts( array( 'numberposts' => 1 ) ) ) ) {
			wp_insert_post( array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => "Top 5 Places to Visit Around Cox's Bazar",
				'post_name'    => 'top-5-places-coxs-bazar',
				'post_content' => "From Himchari to Inani Beach and Maheshkhali Island, here are our favourite spots near the world's longest sea beach.",
			) );
		}
	}

	/* ---------------- Admin screen ---------------- */

	/**
	 * Add Appearance → Theme Setup.
	 */
	public static function menu() {
		add_theme_page(
			__( 'KeyToBD Setup', 'keytobd' ),
			__( 'Theme Setup', 'keytobd' ),
			'manage_options',
			'keytobd-setup',
			array( __CLASS__, 'render' )
		);
	}

	/**
	 * Setup screen with status checklist + re-run buttons.
	 */
	public static function render() {
		$plugin_active = class_exists( 'KeyToBD_Plugin_Activator' ) && KeyToBD_Plugin_Activator::is_active();
		$front_ok      = 'page' === get_option( 'show_on_front' ) && get_option( 'page_on_front' );
		$svc_count     = post_type_exists( 'ktb_service' ) ? (int) wp_count_posts( 'ktb_service' )->publish : 0;
		$tick          = function ( $ok ) {
			return $ok
				? '<span style="color:#1a7f37">&#10004;</span>'
				: '<span style="color:#b32d23">&#10008;</span>';
		};
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'KeyToBD — Theme Setup', 'keytobd' ); ?></h1>
			<p><?php esc_html_e( 'One-click setup creates all pages, menus and demo content. Re-run any time after editing.', 'keytobd' ); ?></p>

			<table class="widefat" style="max-width:640px;margin:16px 0">
				<tbody>
					<tr><td><?php esc_html_e( 'Booking plugin active', 'keytobd' ); ?></td><td><?php echo $tick( $plugin_active ); // phpcs:ignore ?></td></tr>
					<tr><td><?php esc_html_e( 'Static front page set', 'keytobd' ); ?></td><td><?php echo $tick( (bool) $front_ok ); // phpcs:ignore ?></td></tr>
					<tr><td><?php esc_html_e( 'Pages created', 'keytobd' ); ?></td><td><?php echo $tick( (bool) get_page_by_path( 'contact' ) ); // phpcs:ignore ?></td></tr>
					<tr><td><?php esc_html_e( 'Sample services', 'keytobd' ); ?></td><td><?php echo esc_html( $svc_count ); ?></td></tr>
				</tbody>
			</table>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( 'keytobd_run_setup' ); ?>
				<input type="hidden" name="action" value="keytobd_run_setup">
				<p>
					<button class="button button-primary" name="mode" value="pages"><?php esc_html_e( 'Create pages + menus', 'keytobd' ); ?></button>
					<button class="button" name="mode" value="content"><?php esc_html_e( 'Import demo content', 'keytobd' ); ?></button>
					<button class="button" name="mode" value="all"><?php esc_html_e( 'Run everything', 'keytobd' ); ?></button>
				</p>
			</form>
			<?php if ( isset( $_GET['done'] ) ) : // phpcs:ignore WordPress.Security.NonceVerification ?>
				<div class="notice notice-success inline"><p><?php esc_html_e( 'Setup complete.', 'keytobd' ); ?></p></div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Handle the manual re-run buttons.
	 */
	public static function handle_run() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Not allowed.', 'keytobd' ), '', array( 'response' => 403 ) );
		}
		check_admin_referer( 'keytobd_run_setup' );
		$mode = isset( $_POST['mode'] ) ? sanitize_key( wp_unslash( $_POST['mode'] ) ) : 'all';
		if ( 'content' === $mode ) {
			self::seed_content( true );
			flush_rewrite_rules();
		} elseif ( 'pages' === $mode ) {
			$pages = self::create_pages();
			self::set_front_page( $pages );
			self::create_menus( $pages );
			flush_rewrite_rules();
		} else {
			self::run_all( true );
		}
		wp_safe_redirect( admin_url( 'themes.php?page=keytobd-setup&done=1' ) );
		exit;
	}
}
KeyToBD_Setup::init();
