<?php
/**
 * Branded auth UI (rendered by [keytobd_account]).
 *
 * @var string $tab      login|register|forgot
 * @var string $err      error code (or '')
 * @var string $ok       success code (or '')
 * @var string $redirect redirect_to URL
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$kt        = function_exists( 'keytobd_contact' ) ? keytobd_contact() : array( 'whatsapp' => '' );
$action    = function_exists( 'KeyToBD_Auth_Pages::account_url' ) ? '' : '';
$post_to   = KeyToBD_Auth_Pages::account_url();
$hp        = class_exists( 'KTB_Security' ) ? KTB_Security::hp_field() : 'ktb_website';
$client_id = function_exists( 'ktb_get_setting' ) ? ktb_get_setting( 'google_client_id' ) : '';
$bg        = function_exists( 'keytobd_img' ) ? keytobd_img( 'hero.jpg' ) : '';
$tab       = in_array( $tab, array( 'login', 'register', 'forgot' ), true ) ? $tab : 'login';

/* ---------- Logged-in dashboard ---------- */
if ( is_user_logged_in() ) :
	$u        = wp_get_current_user();
	$verified = class_exists( 'KTB_Auth' ) ? KTB_Auth::is_verified( $u->ID ) : true;
	?>
	<div class="ktb-auth ktb-auth--dash">
		<div class="ktb-auth__card">
			<span class="ktb-auth__avatar"><?php echo esc_html( strtoupper( mb_substr( $u->display_name, 0, 1 ) ) ); ?></span>
			<h2><?php printf( esc_html__( 'Hi, %s', 'keytobd' ), esc_html( $u->display_name ) ); ?></h2>
			<p class="ktb-auth__email"><?php echo esc_html( $u->user_email ); ?></p>
			<?php if ( $verified ) : ?>
				<span class="ktb-auth__badge ok"><?php keytobd_icon( 'shieldcheck', 16 ); ?> <?php esc_html_e( 'Email verified', 'keytobd' ); ?></span>
			<?php else : ?>
				<span class="ktb-auth__badge warn"><?php esc_html_e( 'Email not verified', 'keytobd' ); ?></span>
				<div data-ktb-resend-wrap style="margin-top:12px">
					<button type="button" class="btn btn--accent btn--sm" data-ktb-resend><?php esc_html_e( 'Resend verification email', 'keytobd' ); ?></button>
					<p class="ktb-msg" data-ktb-msg role="status" aria-live="polite" hidden></p>
				</div>
			<?php endif; ?>
			<div class="ktb-auth__links">
				<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( home_url( '/track-booking/' ) ); ?>"><?php esc_html_e( 'My bookings', 'keytobd' ); ?></a>
				<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( keytobd_cat_link( 'tour-packages' ) ); ?>"><?php esc_html_e( 'Book a trip', 'keytobd' ); ?></a>
				<a class="btn btn--ghost btn--sm" href="<?php echo esc_url( wp_logout_url( home_url( '/' ) ) ); ?>"><?php esc_html_e( 'Log out', 'keytobd' ); ?></a>
			</div>
		</div>
	</div>
	<?php
	return;
endif;
?>
<div class="ktb-auth" data-ktb-auth>
	<!-- Brand panel -->
	<aside class="ktb-auth__brand" style="<?php echo $bg ? 'background-image:url(' . esc_url( $bg ) . ')' : ''; ?>">
		<div class="ktb-auth__brandinner">
			<span class="site-logo site-logo--light"><span class="site-logo__key">Key</span><span class="site-logo__to">To</span><span class="site-logo__bd">BD</span></span>
			<h2><?php esc_html_e( 'Your gateway to Bangladesh', 'keytobd' ); ?></h2>
			<ul class="ktb-auth__points">
				<li><?php keytobd_icon( 'shieldcheck', 18 ); ?> <?php esc_html_e( 'Secure bKash / card payments', 'keytobd' ); ?></li>
				<li><?php keytobd_icon( 'check', 18 ); ?> <?php esc_html_e( 'Instant e-voucher', 'keytobd' ); ?></li>
				<li><?php keytobd_icon( 'clock', 18 ); ?> <?php esc_html_e( '24/7 travel support', 'keytobd' ); ?></li>
			</ul>
		</div>
	</aside>

	<!-- Form panel -->
	<div class="ktb-auth__panel">
		<div class="ktb-auth__card">
			<?php if ( $err && ( $m = KeyToBD_Auth_Pages::message( $err ) ) ) : ?>
				<div class="ktb-msg bad" role="alert"><?php echo esc_html( $m ); ?></div>
			<?php endif; ?>
			<?php if ( $ok && ( $m = KeyToBD_Auth_Pages::message( $ok ) ) ) : ?>
				<div class="ktb-msg ok" role="status"><?php echo esc_html( $m ); ?></div>
			<?php endif; ?>

			<?php if ( $client_id ) : ?>
				<div class="ktb-google" data-ktb-google data-redirect="<?php echo esc_attr( $redirect ); ?>"></div>
				<div class="ktb-auth__or"><span><?php esc_html_e( 'or', 'keytobd' ); ?></span></div>
			<?php endif; ?>

			<div class="ktb-auth__tabs" role="tablist">
				<button type="button" class="ktb-auth__tab<?php echo 'login' === $tab ? ' is-active' : ''; ?>" data-auth-tab="login"><?php esc_html_e( 'Log in', 'keytobd' ); ?></button>
				<button type="button" class="ktb-auth__tab<?php echo 'register' === $tab ? ' is-active' : ''; ?>" data-auth-tab="register"><?php esc_html_e( 'Create account', 'keytobd' ); ?></button>
			</div>

			<!-- LOGIN -->
			<form class="ktb-auth__form<?php echo 'login' === $tab ? ' is-active' : ''; ?>" data-auth-panel="login" method="post" action="<?php echo esc_url( $post_to ); ?>">
				<input type="hidden" name="ktb_auth_action" value="login">
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>">
				<?php wp_nonce_field( 'ktb_auth', 'ktb_auth_nonce' ); ?>
				<label class="ktb-field"><span><?php esc_html_e( 'Email', 'keytobd' ); ?></span>
					<input type="email" name="email" autocomplete="email" required></label>
				<label class="ktb-field"><span><?php esc_html_e( 'Password', 'keytobd' ); ?></span>
					<span class="ktb-pw"><input type="password" name="password" autocomplete="current-password" required><button type="button" class="ktb-pw__toggle" data-pw-toggle aria-label="<?php esc_attr_e( 'Show password', 'keytobd' ); ?>"><?php keytobd_icon( 'eye', 16 ); ?></button></span></label>
				<div class="ktb-auth__row">
					<label class="ktb-check"><input type="checkbox" name="remember" value="1"> <?php esc_html_e( 'Remember me', 'keytobd' ); ?></label>
					<button type="button" class="ktb-auth__link" data-auth-tab="forgot"><?php esc_html_e( 'Forgot password?', 'keytobd' ); ?></button>
				</div>
				<button type="submit" class="btn btn--accent btn--block btn--lg"><?php esc_html_e( 'Log in', 'keytobd' ); ?></button>
			</form>

			<!-- REGISTER -->
			<form class="ktb-auth__form<?php echo 'register' === $tab ? ' is-active' : ''; ?>" data-auth-panel="register" method="post" action="<?php echo esc_url( $post_to ); ?>">
				<input type="hidden" name="ktb_auth_action" value="register">
				<input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect ); ?>">
				<?php wp_nonce_field( 'ktb_auth', 'ktb_auth_nonce' ); ?>
				<div class="ktb-hp" aria-hidden="true" style="position:absolute;left:-9999px"><input type="text" name="<?php echo esc_attr( $hp ); ?>" tabindex="-1" autocomplete="off"></div>
				<label class="ktb-field"><span><?php esc_html_e( 'Full name', 'keytobd' ); ?></span>
					<input type="text" name="name" autocomplete="name" required></label>
				<label class="ktb-field"><span><?php esc_html_e( 'Email', 'keytobd' ); ?></span>
					<input type="email" name="email" autocomplete="email" required></label>
				<label class="ktb-field"><span><?php esc_html_e( 'Phone (optional)', 'keytobd' ); ?></span>
					<input type="tel" name="phone" autocomplete="tel"></label>
				<label class="ktb-field"><span><?php esc_html_e( 'Password', 'keytobd' ); ?></span>
					<span class="ktb-pw"><input type="password" name="password" autocomplete="new-password" minlength="8" required data-pw-meter><button type="button" class="ktb-pw__toggle" data-pw-toggle aria-label="<?php esc_attr_e( 'Show password', 'keytobd' ); ?>"><?php keytobd_icon( 'eye', 16 ); ?></button></span></label>
				<div class="ktb-pw__bar" data-pw-bar><span></span></div>
				<button type="submit" class="btn btn--accent btn--block btn--lg"><?php esc_html_e( 'Create account', 'keytobd' ); ?></button>
				<p class="ktb-auth__fine"><?php esc_html_e( 'We will email a link to verify your address.', 'keytobd' ); ?></p>
			</form>

			<!-- FORGOT -->
			<form class="ktb-auth__form<?php echo 'forgot' === $tab ? ' is-active' : ''; ?>" data-auth-panel="forgot" method="post" action="<?php echo esc_url( $post_to ); ?>">
				<input type="hidden" name="ktb_auth_action" value="forgot">
				<?php wp_nonce_field( 'ktb_auth', 'ktb_auth_nonce' ); ?>
				<h3 class="ktb-auth__formtitle"><?php esc_html_e( 'Reset your password', 'keytobd' ); ?></h3>
				<label class="ktb-field"><span><?php esc_html_e( 'Account email', 'keytobd' ); ?></span>
					<input type="email" name="email" autocomplete="email" required></label>
				<button type="submit" class="btn btn--accent btn--block btn--lg"><?php esc_html_e( 'Send reset link', 'keytobd' ); ?></button>
				<button type="button" class="ktb-auth__link center" data-auth-tab="login"><?php esc_html_e( 'Back to log in', 'keytobd' ); ?></button>
			</form>
		</div>
	</div>
</div>
