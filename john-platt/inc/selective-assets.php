<?php
/** Omit known unused plugin assets on plain, theme-rendered public surfaces. @package John_Platt */
defined( 'ABSPATH' ) || exit;

/** Unknown, reusable and interactive content keeps its original plugin assets. */
function john_platt_plain_surface_content( $content ) {
	if ( '' === trim( $content ) ) {
		return true;
	}
	if ( preg_match( '/\[|coblocks|gravatar|dashicons|components-|carousel|swiper|gallery|animation|lightbox|\bon\w+\s*=|\bdata-[\w-]+\s*=/i', $content ) ) {
		return false;
	}
	$allowed = array( 'core/paragraph', 'core/heading', 'core/list', 'core/list-item', 'core/quote', 'core/separator', 'core/table', 'core/code', 'core/preformatted', 'core/group', 'core/columns', 'core/column', 'core/buttons', 'core/button' );
	$pending = parse_blocks( $content );
	while ( $pending ) {
		$block = array_pop( $pending );
		if ( empty( $block['blockName'] ) ) {
			if ( '' !== trim( $block['innerHTML'] ?? '' ) ) {
				return false; // Classic/custom HTML has no reliable block dependencies.
			}
			continue;
		}
		if ( ! in_array( $block['blockName'], $allowed, true ) ) {
			return false;
		}
		// Extra block attributes can enable plugin extensions on native blocks.
		$attrs = $block['attrs'] ?? array();
		$native_attrs = array( 'content', 'level', 'ordered', 'start', 'reversed', 'values', 'textAlign', 'align', 'anchor', 'fontSize', 'fontFamily', 'textColor', 'backgroundColor', 'gradient', 'style', 'layout', 'verticalAlignment', 'width', 'isStackedOnMobile', 'borderColor', 'className', 'placeholder', 'url', 'linkTarget', 'rel' );
		if ( array_diff( array_keys( $attrs ), $native_attrs ) || ( isset( $attrs['className'] ) && ! in_array( $attrs['className'], array( '', 'is-style-default', 'is-style-normal', 'is-style-impact' ), true ) ) ) {
			return false;
		}
		if ( preg_match( '/<(?:img|svg|video|audio|iframe|embed|object|script|style|form|input|button)\b/i', $block['innerHTML'] ?? '' ) ) {
			return false;
		}
		foreach ( $block['innerBlocks'] ?? array() as $child ) {
			$pending[] = $child;
		}
	}
	return true;
}

function john_platt_trim_surface_assets_enabled() {
	if ( is_admin() || is_user_logged_in() || is_preview() || is_feed() || is_customize_preview() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return false;
	}
	if ( is_front_page() ) {
		$enabled = true; // This template renders escaped ACF text and native media, not page blocks.
	} elseif ( is_home() ) {
		$page = get_post( (int) get_option( 'page_for_posts' ) );
		$enabled = ! $page || john_platt_plain_surface_content( $page->post_content );
	} elseif ( is_post_type_archive( 'project' ) ) {
		$page = get_page_by_path( 'projects-info' );
		$enabled = ! $page || john_platt_plain_surface_content( $page->post_content );
	} elseif ( is_page( 'contact' ) || is_page_template( 'page-contact.php' ) ) {
		$page = get_queried_object();
		$enabled = $page instanceof WP_Post && '' === $page->post_password && john_platt_plain_surface_content( $page->post_content );
	} else {
		$enabled = false;
	}
	// Site integrations can retain all assets without changing plugin settings.
	return (bool) apply_filters( 'john_platt_trim_surface_assets', $enabled );
}

/** Match exact known asset paths rather than guessed version-specific handles. */
function john_platt_optional_surface_asset( $src, $type ) {
	$path = wp_parse_url( (string) $src, PHP_URL_PATH );
	if ( ! is_string( $path ) ) {
		return false;
	}
	if ( 'scripts' === $type ) {
		return (bool) preg_match( '~/(?:coblocks/dist/js/coblocks-animation(?:\.min)?\.js|jetpack/(?:_inc/build/carousel/jetpack-carousel\.min\.js|modules/carousel/jetpack-carousel\.js))$~', $path );
	}
	return (bool) preg_match( '~/(?:wp-includes/css/dashicons(?:\.min)?\.css|wp-includes/css/dist/components/style(?:\.min)?\.css|gutenberg/build/styles/components/style(?:\.min)?\.css|coblocks/dist/style-coblocks-(?:extensions|animation)\.css|gravatar-enhanced/build/patterns-(?:shared|edit|view)\.css|jetpack/(?:_inc/blocks/swiper\.css|modules/carousel/jetpack-carousel\.css|modules/tiled-gallery/tiled-gallery/tiled-gallery\.css))$~', $path );
}

/** Keep any candidate required directly or transitively by a retained root. */
function john_platt_trim_asset_queue( $registry, $type ) {
	$candidates = array();
	foreach ( $registry->queue as $handle ) {
		$item = $registry->registered[ $handle ] ?? null;
		if ( $item && john_platt_optional_surface_asset( $item->src, $type ) ) {
			$candidates[ $handle ] = true;
		}
	}
	$pending = array_values( array_diff( $registry->queue, array_keys( $candidates ) ) );
	$required = array();
	while ( $pending ) {
		$handle = array_pop( $pending );
		if ( isset( $required[ $handle ] ) ) {
			continue;
		}
		$required[ $handle ] = true;
		foreach ( $registry->registered[ $handle ]->deps ?? array() as $dependency ) {
			$pending[] = $dependency;
		}
	}
	foreach ( $candidates as $handle => $unused ) {
		if ( ! isset( $required[ $handle ] ) ) {
			if ( 'scripts' === $type ) {
				wp_dequeue_script( $handle );
			} else {
				wp_dequeue_style( $handle );
			}
		}
	}
}

function john_platt_trim_surface_assets() {
	if ( ! john_platt_trim_surface_assets_enabled() ) {
		return;
	}
	john_platt_trim_asset_queue( wp_styles(), 'styles' );
	john_platt_trim_asset_queue( wp_scripts(), 'scripts' );
}
add_action( 'wp_enqueue_scripts', 'john_platt_trim_surface_assets', 999 );
add_action( 'wp_print_styles', 'john_platt_trim_surface_assets', 999 );
add_action( 'wp_print_scripts', 'john_platt_trim_surface_assets', 999 );
add_action( 'wp_print_footer_scripts', 'john_platt_trim_surface_assets', 9 );
