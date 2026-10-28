<?php
/** Minimal sharing metadata; canonical URLs remain WordPress-owned. @package John_Platt */
defined( 'ABSPATH' ) || exit;

/** Defer all sharing metadata to recognized SEO plugins. */
function john_platt_sharing_metadata() {
	if ( defined( 'WPSEO_VERSION' ) || defined( 'RANK_MATH_VERSION' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' ) || defined( 'THE_SEO_FRAMEWORK_VERSION' ) || function_exists( 'aioseo' ) || function_exists( 'the_seo_framework' ) || is_404() || is_search() || is_feed() || is_paged() || (int) get_query_var( 'page' ) > 1 ) {
		return;
	}
	$content_id = is_singular() ? get_queried_object_id() : 0;
	$url = $content_id ? get_permalink( $content_id ) : '';
	if ( is_home() ) {
		$content_id = (int) get_option( 'page_for_posts' );
		$url = $content_id ? get_permalink( $content_id ) : home_url( '/' );
	} elseif ( is_post_type_archive( 'project' ) ) {
		$url = get_post_type_archive_link( 'project' );
	}
	if ( ! $url || ( $content_id && post_password_required( $content_id ) ) ) {
		return;
	}
	$description = $content_id && has_excerpt( $content_id ) ? get_the_excerpt( $content_id ) : '';
	if ( is_front_page() && function_exists( 'get_field' ) ) {
		$intro = get_field( 'intro_body', $content_id );
		$description = is_string( $intro ) ? $intro : $description;
	} elseif ( ! $description && $content_id && 'page' === get_post_type( $content_id ) ) {
		$description = get_post_field( 'post_content', $content_id );
	}
	$description = wp_trim_words( wp_strip_all_tags( strip_shortcodes( $description ) ), 35, '' );
	$image = $content_id ? get_the_post_thumbnail_url( $content_id, 'large' ) : '';
	$tags = array(
		'og:title' => wp_get_document_title(),
		'og:site_name' => get_bloginfo( 'name' ),
		'og:type' => is_singular( array( 'post', 'project' ) ) ? 'article' : 'website',
		'og:url' => $url,
	);
	if ( $description ) {
		$tags['og:description'] = $description;
		echo '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}
	if ( $image ) {
		$tags['og:image'] = $image;
		$alt = get_post_meta( get_post_thumbnail_id( $content_id ), '_wp_attachment_image_alt', true );
		if ( is_string( $alt ) && $alt ) {
			$tags['og:image:alt'] = $alt;
		}
	}
	foreach ( $tags as $property => $value ) {
		echo '<meta property="' . esc_attr( $property ) . '" content="' . ( in_array( $property, array( 'og:url', 'og:image' ), true ) ? esc_url( $value ) : esc_attr( $value ) ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="' . esc_attr( $image ? 'summary_large_image' : 'summary' ) . '">' . "\n";
}
add_action( 'wp_head', 'john_platt_sharing_metadata', 5 );
