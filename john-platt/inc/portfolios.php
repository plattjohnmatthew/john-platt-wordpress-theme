<?php
/** Unlisted, application-specific selections of existing Writing and Projects. @package John_Platt */
defined( 'ABSPATH' ) || exit;

function john_platt_register_portfolios() {
 register_post_type( 'jp_portfolio', array(
  'labels' => array( 'name' => __( 'Portfolios', 'john-platt' ), 'singular_name' => __( 'Portfolio', 'john-platt' ), 'add_new_item' => __( 'Add New Portfolio', 'john-platt' ), 'edit_item' => __( 'Edit Portfolio', 'john-platt' ) ),
  'public' => false, 'publicly_queryable' => true, 'show_ui' => true,
  'show_in_rest' => false, 'exclude_from_search' => true, 'show_in_nav_menus' => false,
  'has_archive' => false, 'rewrite' => array( 'slug' => 'portfolio', 'with_front' => false, 'feeds' => false ),
  'query_var' => true, 'menu_icon' => 'dashicons-portfolio',
  'supports' => array( 'title', 'editor', 'revisions' ),
 ) );
 // Once per feature installation, including updates to an already-active theme.
 if ( '1' !== get_option( 'john_platt_portfolio_routes' ) ) {
  flush_rewrite_rules( false );
  update_option( 'john_platt_portfolio_routes', '1', false );
 }
}
add_action( 'init', 'john_platt_register_portfolios', 20 );

function john_platt_portfolio_item_ids( $portfolio_id ) {
 $ids = get_post_meta( $portfolio_id, '_john_platt_portfolio_items', true );
 return is_array( $ids ) ? array_values( array_unique( array_filter( array_map( 'absint', $ids ) ) ) ) : array();
}
function john_platt_editable_portfolios() {
 return get_posts( array( 'post_type' => 'jp_portfolio', 'post_status' => array( 'publish', 'draft', 'pending', 'future', 'private' ), 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
}
function john_platt_portfolio_meta_boxes() {
 add_meta_box( 'john-platt-portfolio-items', __( 'Selected Writing and Projects', 'john-platt' ), 'john_platt_portfolio_items_box', 'jp_portfolio', 'normal', 'high' );
 foreach ( array( 'post', 'project' ) as $type ) {
  add_meta_box( 'john-platt-portfolio-membership', __( 'Application Portfolios', 'john-platt' ), 'john_platt_portfolio_membership_box', $type, 'side' );
 }
}
add_action( 'add_meta_boxes', 'john_platt_portfolio_meta_boxes' );

function john_platt_portfolio_items_box( $post ) {
 wp_nonce_field( 'john_platt_portfolio_items_' . $post->ID, 'john_platt_portfolio_items_nonce' );
 $selected = john_platt_portfolio_item_ids( $post->ID );
 $items = get_posts( array( 'post_type' => array( 'post', 'project' ), 'post_status' => array( 'publish', 'draft', 'pending', 'future', 'private' ), 'numberposts' => -1, 'orderby' => 'title', 'order' => 'ASC' ) );
 echo '<p>' . esc_html__( 'Write the application introduction in the editor above. Select existing items below. Lower order numbers appear first; ties keep the current order. Only published, non-password-protected items appear to visitors.', 'john-platt' ) . '</p>';
 echo '<p><strong>' . esc_html__( 'Unlisted, not private: anyone with the link can view this portfolio. Search engines are instructed not to index it. No public menu or archive entry is created.', 'john-platt' ) . '</strong></p>';
 echo '<table class="widefat striped"><thead><tr><th>' . esc_html__( 'Include', 'john-platt' ) . '</th><th>' . esc_html__( 'Item', 'john-platt' ) . '</th><th>' . esc_html__( 'Order', 'john-platt' ) . '</th></tr></thead><tbody>';
 // Keep selected rows first in their saved order for straightforward editing.
 usort( $items, static function ( $a, $b ) use ( $selected ) {
  $a_index = array_search( $a->ID, $selected, true ); $b_index = array_search( $b->ID, $selected, true );
  if ( false !== $a_index || false !== $b_index ) { return ( false === $a_index ? PHP_INT_MAX : $a_index ) <=> ( false === $b_index ? PHP_INT_MAX : $b_index ); }
  return strnatcasecmp( $a->post_title, $b->post_title ) ?: $a->ID <=> $b->ID;
 } );
 foreach ( $items as $item ) {
  if ( ! current_user_can( 'edit_post', $item->ID ) ) { continue; }
  $index = array_search( $item->ID, $selected, true );
  $order = false === $index ? 1000 : ( $index + 1 ) * 10;
  $title = $item->post_title ?: __( '(no title)', 'john-platt' );
  echo '<tr><td><input type="checkbox" id="portfolio-item-' . esc_attr( $item->ID ) . '" name="portfolio_items[]" value="' . esc_attr( $item->ID ) . '" ' . checked( false !== $index, true, false ) . ' aria-label="' . esc_attr( sprintf( __( 'Include %s', 'john-platt' ), $title ) ) . '"></td><td><label for="portfolio-item-' . esc_attr( $item->ID ) . '">' . esc_html( $title ) . '</label><br><small>' . esc_html( ( 'post' === $item->post_type ? __( 'Writing', 'john-platt' ) : __( 'Project', 'john-platt' ) ) . ' — ' . $item->post_status ) . '</small></td><td><input type="number" step="1" name="portfolio_order[' . esc_attr( $item->ID ) . ']" value="' . esc_attr( $order ) . '" style="width:6em" aria-label="' . esc_attr( sprintf( __( 'Order for %s', 'john-platt' ), $title ) ) . '"></td></tr>';
 }
 echo '</tbody></table>';
 if ( ! $items ) { echo '<p>' . esc_html__( 'Create Writing posts or Projects first, then return here to select them.', 'john-platt' ) . '</p>'; }
}
function john_platt_portfolio_membership_box( $post ) {
 wp_nonce_field( 'john_platt_portfolio_membership_' . $post->ID, 'john_platt_portfolio_membership_nonce' );
 echo '<p>' . esc_html__( 'Include this item in an application portfolio. Edit the portfolio to change its introduction and order.', 'john-platt' ) . '</p>';
 $found = false;
 foreach ( john_platt_editable_portfolios() as $portfolio ) {
  if ( ! current_user_can( 'edit_post', $portfolio->ID ) ) { continue; }
  $found = true;
  echo '<p><label><input type="checkbox" name="application_portfolios[]" value="' . esc_attr( $portfolio->ID ) . '" ' . checked( in_array( $post->ID, john_platt_portfolio_item_ids( $portfolio->ID ), true ), true, false ) . '> ' . esc_html( $portfolio->post_title ?: __( '(no title)', 'john-platt' ) ) . '</label></p>';
 }
 if ( ! $found ) { echo '<p>' . esc_html__( 'Create one under Portfolios → Add New first.', 'john-platt' ) . '</p>'; }
}

function john_platt_save_portfolio_selections( $post_id, $post ) {
 if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
 if ( 'jp_portfolio' === $post->post_type ) {
  $nonce = $_POST['john_platt_portfolio_items_nonce'] ?? '';
  if ( ! is_string( $nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'john_platt_portfolio_items_' . $post_id ) ) { return; }
  $values = isset( $_POST['portfolio_items'] ) && is_array( $_POST['portfolio_items'] ) ? wp_unslash( $_POST['portfolio_items'] ) : array();
  $orders = isset( $_POST['portfolio_order'] ) && is_array( $_POST['portfolio_order'] ) ? wp_unslash( $_POST['portfolio_order'] ) : array();
  $ids = array();
  foreach ( $values as $value ) {
   if ( ! is_scalar( $value ) ) { continue; }
   $id = absint( $value );
   if ( in_array( get_post_type( $id ), array( 'post', 'project' ), true ) && current_user_can( 'edit_post', $id ) && ! in_array( $id, $ids, true ) ) { $ids[] = $id; }
  }
  $prior = john_platt_portfolio_item_ids( $post_id );
  $ranks = array_flip( array_values( array_unique( array_merge( $prior, $ids ) ) ) );
  usort( $ids, static function ( $a, $b ) use ( $orders, $ranks ) {
   $a_order = isset( $orders[$a] ) && is_scalar( $orders[$a] ) ? (int) $orders[$a] : 1000;
   $b_order = isset( $orders[$b] ) && is_scalar( $orders[$b] ) ? (int) $orders[$b] : 1000;
   return ( $a_order <=> $b_order ) ?: ( $ranks[$a] <=> $ranks[$b] );
  } );
  update_post_meta( $post_id, '_john_platt_portfolio_items', $ids );
 } elseif ( in_array( $post->post_type, array( 'post', 'project' ), true ) ) {
  $nonce = $_POST['john_platt_portfolio_membership_nonce'] ?? '';
  if ( ! is_string( $nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'john_platt_portfolio_membership_' . $post_id ) ) { return; }
  $values = isset( $_POST['application_portfolios'] ) && is_array( $_POST['application_portfolios'] ) ? wp_unslash( $_POST['application_portfolios'] ) : array();
  $chosen = array_map( 'absint', array_filter( $values, 'is_scalar' ) );
  foreach ( john_platt_editable_portfolios() as $portfolio ) {
   if ( ! current_user_can( 'edit_post', $portfolio->ID ) ) { continue; }
   $ids = john_platt_portfolio_item_ids( $portfolio->ID );
   $updated = array_values( array_diff( $ids, array( $post_id ) ) );
   if ( in_array( $portfolio->ID, $chosen, true ) ) { $updated = in_array( $post_id, $ids, true ) ? $ids : array_merge( $ids, array( $post_id ) ); }
   if ( $updated !== $ids ) { update_post_meta( $portfolio->ID, '_john_platt_portfolio_items', $updated ); }
  }
 }
}
add_action( 'save_post', 'john_platt_save_portfolio_selections', 20, 2 );

function john_platt_portfolio_robots( $robots ) {
 if ( is_singular( 'jp_portfolio' ) ) { unset( $robots['index'] ); $robots['noindex'] = true; }
 return $robots;
}
add_filter( 'wp_robots', 'john_platt_portfolio_robots', 99 );
function john_platt_portfolio_response() {
 if ( is_singular( 'jp_portfolio' ) ) {
  header( 'X-Robots-Tag: noindex, follow', true );
  // Do not allow sharing/like plugins to append controls to the private-purpose introduction.
  remove_filter( 'the_content', 'sharing_display', 19 );
  remove_filter( 'the_content', 'wpcom_likes' );
 } elseif ( in_array( 'jp_portfolio', (array) get_query_var( 'post_type' ), true ) ) {
  global $wp_query;
  $wp_query->set_404();
  $wp_query->is_feed = false;
  $wp_query->posts = array(); $wp_query->post_count = 0;
  status_header( 404 );
 }
}
add_action( 'template_redirect', 'john_platt_portfolio_response' );
function john_platt_portfolio_sitemap_types( $types ) {
 unset( $types['jp_portfolio'] );
 return array_filter( $types, static function ( $type ) { return 'jp_portfolio' !== $type; } );
}
add_filter( 'wp_sitemaps_post_types', 'john_platt_portfolio_sitemap_types' );
add_filter( 'jetpack_sitemap_post_types', 'john_platt_portfolio_sitemap_types' );
add_filter( 'jetpack_sitemap_news_sitemap_post_types', 'john_platt_portfolio_sitemap_types' );
function john_platt_portfolio_seo_sitemap_exclude( $exclude, $type ) { return 'jp_portfolio' === $type ? true : $exclude; }
add_filter( 'wpseo_sitemap_exclude_post_type', 'john_platt_portfolio_seo_sitemap_exclude', 10, 2 );
add_filter( 'rank_math/sitemap/exclude_post_type', 'john_platt_portfolio_seo_sitemap_exclude', 10, 2 );