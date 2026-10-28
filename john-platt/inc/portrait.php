<?php
/** Shared editable site portrait. @package John_Platt */
defined( 'ABSPATH' ) || exit;

/** Return an image attachment ID, or zero when no valid portrait is available. */
function john_platt_site_portrait_id() {
	$homepage_id = (int) get_option( 'page_on_front' );
	if ( ! $homepage_id || ! function_exists( 'get_field' ) ) {
		return 0;
	}
	$value = get_field( 'intro_image', $homepage_id );
	$id = is_numeric( $value ) ? absint( $value ) : 0;
	return $id && wp_attachment_is_image( $id ) ? $id : 0;
}
/** Use the editable homepage portrait for WordPress's native favicon links. */
function john_platt_portrait_site_icon_url( $url, $size, $blog_id ) {
	// Do not substitute this site's portrait when another multisite blog is requested.
	if ( $blog_id && (int) $blog_id !== get_current_blog_id() ) {
		return $url;
	}
	$portrait_id = john_platt_site_portrait_id();
	if ( ! $portrait_id ) {
		return $url;
	}
	$portrait_url = wp_get_attachment_image_url( $portrait_id, array( absint( $size ), absint( $size ) ) );
	return $portrait_url ?: $url;
}
add_filter( 'get_site_icon_url', 'john_platt_portrait_site_icon_url', 10, 3 );