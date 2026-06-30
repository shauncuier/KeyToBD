<?php
/**
 * Organic wave divider (SVG). Pass $args['class'] for fill control
 * (e.g. 'wave-divider--sand'). Purely decorative.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */

$class = isset( $args['class'] ) ? ' ' . sanitize_html_class( $args['class'] ) : '';
?>
<div class="wave-divider<?php echo esc_attr( $class ); ?>" aria-hidden="true">
	<svg viewBox="0 0 1440 64" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
		<path d="M0,40 C180,64 360,8 540,20 C720,32 900,64 1080,52 C1260,40 1380,16 1440,24 L1440,64 L0,64 Z"></path>
	</svg>
</div>
