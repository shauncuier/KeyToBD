<?php
/**
 * Reusable inner-page hero with breadcrumb.
 * Pass $args = array( 'title' => '', 'subtitle' => '', 'crumbs' => array( label => url ) ).
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

$title    = $args['title'] ?? get_the_title();
$subtitle = $args['subtitle'] ?? '';
$crumbs   = $args['crumbs'] ?? array();
?>
<section class="page-hero">
	<div class="container">
		<nav class="breadcrumb" aria-label="<?php esc_attr_e( 'Breadcrumb', 'keytobd' ); ?>">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'keytobd' ); ?></a>
			<?php foreach ( $crumbs as $label => $url ) : ?>
				<span>/</span>
				<?php if ( $url ) : ?><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a><?php else : ?><span><?php echo esc_html( $label ); ?></span><?php endif; ?>
			<?php endforeach; ?>
		</nav>
		<h1><?php echo esc_html( $title ); ?></h1>
		<?php if ( $subtitle ) : ?><p><?php echo esc_html( $subtitle ); ?></p><?php endif; ?>
	</div>
</section>
