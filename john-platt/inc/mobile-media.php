<?php
/** Native article/case-study video defaults; no device detection or content copies. @package John_Platt */
defined( 'ABSPATH' ) || exit;

function john_platt_content_video_defaults( $content ) {
	if ( ! is_singular( array( 'post', 'project' ) ) || ! class_exists( 'WP_HTML_Tag_Processor' ) || false === stripos( $content, '<video' ) ) {
		return $content;
	}
	$tags = new WP_HTML_Tag_Processor( $content );
	while ( $tags->next_tag( 'VIDEO' ) ) {
		$tags->set_attribute( 'playsinline', true );
		if ( null === $tags->get_attribute( 'preload' ) ) {
			$tags->set_attribute( 'preload', 'metadata' );
		}
		$width = absint( $tags->get_attribute( 'width' ) );
		$height = absint( $tags->get_attribute( 'height' ) );
		if ( $width && $height ) {
			$style = (string) $tags->get_attribute( 'style' );
			$tags->set_attribute( 'style', rtrim( $style, " \t\n\r\0\x0B;" ) . ';--project-video-ratio:' . $width . '/' . $height . ';' );
		}
	}
	return $tags->get_updated_html();
}
add_filter( 'the_content', 'john_platt_content_video_defaults', 25 );

/** Imported article images may not have attachment classes for core's defaults. */
function john_platt_content_image_defaults( $content ) {
	if ( is_admin() || is_feed() || ! is_singular( array( 'post', 'project' ) ) || post_password_required() || ! class_exists( 'WP_HTML_Tag_Processor' ) || false === stripos( $content, '<img' ) ) {
		return $content;
	}
	$tags = new WP_HTML_Tag_Processor( $content );
	$first_image = true;
	$has_featured_image = has_post_thumbnail();
	while ( $tags->next_tag( 'IMG' ) ) {
		// Keep explicit author/core priorities. Without a featured image, the
		// first body image may be the opening image and must not be deferred.
		if ( null === $tags->get_attribute( 'loading' ) && 'high' !== strtolower( (string) $tags->get_attribute( 'fetchpriority' ) ) && ( $has_featured_image || ! $first_image ) ) {
			$tags->set_attribute( 'loading', 'lazy' );
		}
		if ( null === $tags->get_attribute( 'decoding' ) ) {
			$tags->set_attribute( 'decoding', 'async' );
		}
		$first_image = false;
	}
	// No remote lookups, fabricated dimensions or changes to image destinations.
	return $tags->get_updated_html();
}
add_filter( 'the_content', 'john_platt_content_image_defaults', 25 );

/** Native iframe lazy loading keeps provider resize scripts and no-JS fallback intact. */
function john_platt_content_embed_defaults( $content ) {
	if ( is_admin() || is_feed() || ! is_singular( array( 'post', 'project' ) ) || post_password_required() || ! class_exists( 'WP_HTML_Tag_Processor' ) || false === stripos( $content, '<iframe' ) ) {
		return $content;
	}
	$tags = new WP_HTML_Tag_Processor( $content );
	while ( $tags->next_tag( 'IFRAME' ) ) {
		$src = (string) $tags->get_attribute( 'src' );
		$style = (string) $tags->get_attribute( 'style' );
		$hidden = null !== $tags->get_attribute( 'hidden' ) || 'true' === $tags->get_attribute( 'aria-hidden' ) || preg_match( '/display\s*:\s*none|visibility\s*:\s*hidden|(?:width|height)\s*:\s*0(?:px|em|rem|%|[;\s]|$)/i', $style );
		foreach ( array( 'width', 'height' ) as $dimension ) {
			$value = $tags->get_attribute( $dimension );
			if ( is_numeric( $value ) && (float) $value < 10 ) {
				$hidden = true; // Tracking/communication frames must not be deferred.
			}
		}
		if ( ! $hidden && null === $tags->get_attribute( 'loading' ) && null === $tags->get_attribute( 'srcdoc' ) && preg_match( '~^(?:https?:)?//~i', $src ) ) {
			$tags->set_attribute( 'loading', 'lazy' );
		}
	}
	return $tags->get_updated_html();
}
add_filter( 'the_content', 'john_platt_content_embed_defaults', 35 );
