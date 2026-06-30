<?php
/**
 * Search form.
 *
 * @package KeyToBD
 * @author  3s-Soft
 */
?>
<form role="search" method="get" class="kt-searchform" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="kt-s"><?php esc_html_e( 'Search for:', 'keytobd' ); ?></label>
	<div class="search-field"><div class="control" style="background:#fff;">
		<?php keytobd_icon( 'search', 18 ); ?>
		<input type="search" id="kt-s" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search tours, hotels, guides…', 'keytobd' ); ?>">
		<button type="submit" class="btn btn--accent btn--sm"><?php esc_html_e( 'Go', 'keytobd' ); ?></button>
	</div></div>
</form>
