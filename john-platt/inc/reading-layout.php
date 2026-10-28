<?php
/** Keep automatic social/related widgets out of long-form reading layouts. @package John_Platt */
defined( 'ABSPATH' ) || exit;
function john_platt_clean_reading_layout() {
 return ! is_admin() && is_singular( array( 'post', 'project', 'jp_portfolio' ) );
}
function john_platt_disable_reading_extras( $enabled ) {
 return john_platt_clean_reading_layout() ? false : $enabled;
}
add_filter( 'sharing_show', 'john_platt_disable_reading_extras', 1000 );
add_filter( 'jetpack_relatedposts_filter_enabled_for_request', 'john_platt_disable_reading_extras', 1000 );
function john_platt_disable_reading_likes( $disabled ) {
 return john_platt_clean_reading_layout() ? true : $disabled;
}
foreach ( array( 'post', 'project', 'jp_portfolio' ) as $type ) {
 add_filter( 'wpl_is_single_' . $type . '_disabled', 'john_platt_disable_reading_likes', 1000 );
}
function john_platt_reading_sharing_markup( $markup ) {
 return john_platt_clean_reading_layout() ? '' : $markup;
}
add_filter( 'jetpack_sharing_display_markup', 'john_platt_reading_sharing_markup', 1000 );
function john_platt_reading_extra_blocks( $html, $block ) {
 if ( john_platt_clean_reading_layout() && in_array( $block['blockName'] ?? '', array( 'jetpack/related-posts', 'jetpack/like', 'jetpack/sharing-buttons' ), true ) ) { return ''; }
 return $html;
}
add_filter( 'render_block', 'john_platt_reading_extra_blocks', 1000, 2 );
function john_platt_remove_legacy_reading_extras() {
 if ( ! john_platt_clean_reading_layout() ) { return; }
 remove_filter( 'the_content', 'sharing_display', 19 );
 remove_filter( 'the_excerpt', 'sharing_display', 19 );
 remove_filter( 'the_content', 'wpcom_likes' );
 if ( class_exists( 'Jetpack_Likes' ) && is_callable( array( 'Jetpack_Likes', 'init' ) ) ) {
  $likes = Jetpack_Likes::init();
  foreach ( array( 'the_content', 'the_excerpt', 'post_flair' ) as $hook ) { remove_filter( $hook, array( $likes, 'post_likes' ), 30 ); }
 }
}
add_action( 'wp', 'john_platt_remove_legacy_reading_extras', 100 );