<?php
/** Delay the existing Jetpack GA4 library, without a second tracker or property. @package John_Platt */
defined( 'ABSPATH' ) || exit;

function john_platt_delay_ga_markup( $markup ) {
	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $markup;
	}
	$tags = new WP_HTML_Tag_Processor( $markup );
	$changed = false;
	while ( $tags->next_tag( 'SCRIPT' ) ) {
		$src = $tags->get_attribute( 'src' );
		if ( ! is_string( $src ) ) {
			continue;
		}
		$url = wp_parse_url( $src );
		if ( ! is_array( $url ) || 'https' !== ( $url['scheme'] ?? '' ) || 'www.googletagmanager.com' !== ( $url['host'] ?? '' ) || '/gtag/js' !== ( $url['path'] ?? '' ) ) {
			continue;
		}
		parse_str( $url['query'] ?? '', $query );
		$type = strtolower( (string) $tags->get_attribute( 'type' ) );
		if ( ! is_string( $query['id'] ?? null ) || ! preg_match( '/^G-[A-Z0-9]+$/', $query['id'] ) || ! in_array( $type, array( '', 'text/javascript', 'application/javascript' ), true ) || $tags->get_attribute_names_with_prefix( 'data-' ) ) {
			continue; // Leave consent-managed/nonstandard/legacy tags under their owner's control.
		}
		$tags->set_attribute( 'type', 'application/x-john-platt-delayed-analytics' );
		$tags->set_attribute( 'data-jp-analytics-type', $type );
		$changed = true;
	}
	if ( ! $changed ) {
		return $markup;
	}
	wp_enqueue_script( 'john-platt-analytics-delay', get_theme_file_uri( '/assets/js/analytics-delay.js' ), array(), (string) filemtime( get_theme_file_path( '/assets/js/analytics-delay.js' ) ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	return $tags->get_updated_html();
}

/** Wrap only the known Jetpack callback; its exclusions and inline commands run intact. */
function john_platt_prepare_delayed_analytics() {
	if ( is_admin() || is_preview() || is_feed() || is_customize_preview() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) || ! apply_filters( 'john_platt_delay_google_analytics', true ) ) {
		return;
	}
	global $wp_filter;
	$callbacks = isset( $wp_filter['wp_head'] ) ? $wp_filter['wp_head']->callbacks : array();
	foreach ( $callbacks as $priority => $items ) {
		foreach ( $items as $item ) {
			$callback = $item['function'];
			if ( ! is_array( $callback ) || ! is_object( $callback[0] ) || 'insert_code' !== $callback[1] || ! in_array( get_class( $callback[0] ), array( 'Automattic\\Jetpack\\Google_Analytics\\Legacy', 'Jetpack_Google_Analytics_Legacy' ), true ) ) {
				continue;
			}
			remove_action( 'wp_head', $callback, $priority );
			add_action( 'wp_head', static function () use ( $callback ) {
				ob_start();
				try {
					call_user_func( $callback );
					$markup = ob_get_contents();
				} finally {
					ob_end_clean();
				}
				echo john_platt_delay_ga_markup( $markup ); // Original trusted plugin markup; only the external tag's type is changed.
			}, $priority );
		}
	}
}
add_action( 'wp', 'john_platt_prepare_delayed_analytics', 100 );
