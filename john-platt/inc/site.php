<?php
/** Site page routes shared by structural links. @package John_Platt */
defined( 'ABSPATH' ) || exit;

/** Resolve a published editorial page, including a renamed template page. */
function john_platt_page_url( $slug ) {
	$page = get_page_by_path( $slug );
	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		return get_permalink( $page );
	}
	if ( in_array( $slug, array( 'about', 'contact', 'index' ), true ) ) {
		$pages = get_posts( array(
			'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => 1,
			'meta_key' => '_wp_page_template', 'meta_value' => 'page-' . $slug . '.php',
		) );
		if ( $pages ) {
			return get_permalink( $pages[0] );
		}
	}
	return '';
}
