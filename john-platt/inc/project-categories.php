<?php
/** Project category filters and optional Writing entries, without duplicate public pages. @package John_Platt */
defined( 'ABSPATH' ) || exit;

function john_platt_register_project_categories() {
	register_taxonomy( 'project_category', array( 'project', 'post' ), array(
		'labels' => array(
			'name' => __( 'Project Categories', 'john-platt' ),
			'singular_name' => __( 'Project Category', 'john-platt' ),
			'add_new_item' => __( 'Add New Project Category', 'john-platt' ),
			'edit_item' => __( 'Edit Project Category', 'john-platt' ),
		),
		'public' => true, 'hierarchical' => false, 'show_ui' => true,
		'show_in_rest' => true, 'show_admin_column' => true, 'rewrite' => false,
		'meta_box_cb' => false, 'show_in_quick_edit' => false,
	) );
	register_term_meta( 'project_category', 'project_category_order', array(
		'type' => 'integer', 'single' => true, 'default' => 0,
		'sanitize_callback' => static function ( $value ) { return (int) $value; },
	) );
}
add_action( 'init', 'john_platt_register_project_categories' );

function john_platt_project_category_order_input( $value ) {
	wp_nonce_field( 'john_platt_project_category_order', 'john_platt_project_category_nonce' );
	?>
	<input type="number" id="project-category-order" name="project_category_order" step="1" value="<?php echo esc_attr( (string) $value ); ?>">
	<p class="description"><?php esc_html_e( 'Lower numbers appear first. Equal orders sort by category name.', 'john-platt' ); ?></p>
	<?php
}
function john_platt_add_project_category_order() {
	echo '<div class="form-field"><label for="project-category-order">' . esc_html__( 'Display Order', 'john-platt' ) . '</label>';
	john_platt_project_category_order_input( 0 );
	echo '</div>';
}
function john_platt_edit_project_category_order( $term ) {
	echo '<tr class="form-field"><th><label for="project-category-order">' . esc_html__( 'Display Order', 'john-platt' ) . '</label></th><td>';
	john_platt_project_category_order_input( (int) get_term_meta( $term->term_id, 'project_category_order', true ) );
	echo '</td></tr>';
}
add_action( 'project_category_add_form_fields', 'john_platt_add_project_category_order' );
add_action( 'project_category_edit_form_fields', 'john_platt_edit_project_category_order' );

function john_platt_save_project_category_order( $term_id ) {
	$nonce = $_POST['john_platt_project_category_nonce'] ?? '';
	$value = $_POST['project_category_order'] ?? null;
	if ( ! is_string( $nonce ) || ! is_string( $value ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'john_platt_project_category_order' ) ) {
		return;
	}
	$taxonomy = get_taxonomy( 'project_category' );
	if ( $taxonomy && current_user_can( $taxonomy->cap->manage_terms ) ) {
		update_term_meta( $term_id, 'project_category_order', (int) wp_unslash( $value ) );
	}
}
add_action( 'created_project_category', 'john_platt_save_project_category_order' );
add_action( 'edited_project_category', 'john_platt_save_project_category_order' );

function john_platt_parse_project_categories( $value ) {
	if ( ! is_string( $value ) ) {
		return array();
	}
	$slugs = array_values( array_unique( array_filter( array_map( 'sanitize_title', explode( ',', $value ) ) ) ) );
	if ( ! $slugs ) {
		return array();
	}
	$terms = get_terms( array( 'taxonomy' => 'project_category', 'hide_empty' => false, 'slug' => $slugs, 'fields' => 'slugs' ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	sort( $terms, SORT_STRING );
	return array_values( array_unique( $terms ) );
}

/** Keep the native archive query and ordering; only selected Writing posts are admitted. */
function john_platt_filter_project_categories( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'project' ) || $query->is_feed() ) {
		return;
	}
	$selected = john_platt_parse_project_categories( isset( $_GET['categories'] ) && is_string( $_GET['categories'] ) ? wp_unslash( $_GET['categories'] ) : '' );
	$query->set( 'john_platt_project_archive', true );
	$query->set( 'john_platt_project_categories', $selected );
	$query->set( 'post_type', array( 'project', 'post' ) );
	if ( $selected ) {
		$filter = array( 'taxonomy' => 'project_category', 'field' => 'slug', 'terms' => $selected, 'operator' => 'IN', 'include_children' => false );
		$existing = $query->get( 'tax_query' );
		$query->set( 'tax_query', $existing ? array( 'relation' => 'AND', $existing, $filter ) : array( $filter ) );
	}
}
add_action( 'pre_get_posts', 'john_platt_filter_project_categories', 20 );

function john_platt_project_archive_where( $where, $query ) {
	if ( $query->is_main_query() && ( ( ! is_admin() && $query->get( 'john_platt_project_archive' ) ) || ( is_admin() && $query->get( 'john_platt_project_admin' ) ) ) ) {
		global $wpdb;
		$where .= " AND ({$wpdb->posts}.post_type = 'project' OR ({$wpdb->posts}.post_type = 'post' AND EXISTS (SELECT 1 FROM {$wpdb->postmeta} jp_selected WHERE jp_selected.post_id = {$wpdb->posts}.ID AND jp_selected.meta_key = '_john_platt_show_project' AND jp_selected.meta_value = '1')))";
	}
	return $where;
}
add_filter( 'posts_where', 'john_platt_project_archive_where', 10, 2 );

function john_platt_selected_project_categories() {
	$value = get_query_var( 'john_platt_project_categories', array() );
	return is_array( $value ) ? $value : array();
}
function john_platt_project_category_url( $selected = array(), $toggle = '' ) {
	$url = get_post_type_archive_link( 'project' );
	if ( '' === $toggle ) {
		return $url;
	}
	$selected = in_array( $toggle, $selected, true ) ? array_diff( $selected, array( $toggle ) ) : array_merge( $selected, array( $toggle ) );
	$selected = array_values( array_unique( $selected ) );
	sort( $selected, SORT_STRING );
	return $selected ? add_query_arg( 'categories', implode( ',', $selected ), $url ) : $url;
}
function john_platt_project_filter_terms( $selected ) {
	$terms = get_terms( array( 'taxonomy' => 'project_category', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	// Native term counts include articles not selected for Projects. Check actual eligible entries.
	global $wpdb;
	$used = array_map( 'intval', $wpdb->get_col( "SELECT DISTINCT tt.term_id FROM {$wpdb->term_taxonomy} tt INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID WHERE tt.taxonomy = 'project_category' AND p.post_status = 'publish' AND (p.post_type = 'project' OR (p.post_type = 'post' AND EXISTS (SELECT 1 FROM {$wpdb->postmeta} pm WHERE pm.post_id = p.ID AND pm.meta_key = '_john_platt_show_project' AND pm.meta_value = '1')))" ) );
	$terms = array_values( array_filter( $terms, static function ( $term ) use ( $used, $selected ) {
		return in_array( (int) $term->term_id, $used, true ) || in_array( $term->slug, $selected, true );
	} ) );
	usort( $terms, static function ( $a, $b ) {
		$order = (int) get_term_meta( $a->term_id, 'project_category_order', true ) <=> (int) get_term_meta( $b->term_id, 'project_category_order', true );
		return $order ?: ( strcasecmp( $a->name, $b->name ) ?: $a->term_id <=> $b->term_id );
	} );
	return $terms;
}
function john_platt_project_pagination() {
	$selected = john_platt_selected_project_categories();
	$normalize = static function ( $url ) use ( $selected ) {
		$url = remove_query_arg( 'categories', $url );
		return $selected ? add_query_arg( 'categories', implode( ',', $selected ), $url ) : $url;
	};
	add_filter( 'paginate_links', $normalize );
	try { the_posts_pagination(); } finally { remove_filter( 'paginate_links', $normalize ); }
}

/** A native metabox works with the standard editor and without ACF. */
function john_platt_writing_project_box() {
	add_meta_box( 'john-platt-project-categories', __( 'Project Categories', 'john-platt' ), 'john_platt_project_category_controls', 'project', 'side' );
	add_meta_box( 'john-platt-writing-project', __( 'Show in Projects', 'john-platt' ), 'john_platt_writing_project_controls', 'post', 'side' );
}
add_action( 'add_meta_boxes', 'john_platt_writing_project_box' );
function john_platt_writing_project_controls( $post ) {
	wp_nonce_field( 'john_platt_writing_project', 'john_platt_writing_project_nonce' );
	?>
	<p><label><input type="checkbox" name="john_platt_show_project" value="1" <?php checked( '1', get_post_meta( $post->ID, '_john_platt_show_project', true ) ); ?>> <?php esc_html_e( 'Include this article in the Projects archive', 'john-platt' ); ?></label></p>
	<p><?php esc_html_e( 'Uses this article’s title, excerpt and featured image. Opens the original article; no duplicate page is created. Assign categories using Project Categories.', 'john-platt' ); ?></p>
	<p><label for="writing-project-order"><?php esc_html_e( 'Project Display Order', 'john-platt' ); ?></label><br>
	<input id="writing-project-order" type="number" step="1" name="john_platt_project_order" value="<?php echo esc_attr( (string) $post->menu_order ); ?>"></p>
	<?php john_platt_project_category_controls( $post ); ?>
	<?php
}
function john_platt_save_writing_project( $post_id ) {
	static $saving = false;
	if ( $saving || wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$nonce = $_POST['john_platt_writing_project_nonce'] ?? '';
	if ( ! is_string( $nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'john_platt_writing_project' ) ) {
		return;
	}
	update_post_meta( $post_id, '_john_platt_show_project', isset( $_POST['john_platt_show_project'] ) && '1' === $_POST['john_platt_show_project'] ? '1' : '0' );
	if ( isset( $_POST['john_platt_project_order'] ) && is_string( $_POST['john_platt_project_order'] ) ) {
		$saving = true;
		wp_update_post( array( 'ID' => $post_id, 'menu_order' => (int) wp_unslash( $_POST['john_platt_project_order'] ) ) );
		$saving = false;
	}
}
add_action( 'save_post_post', 'john_platt_save_writing_project' );
/** A mixed native query needs the explicit Project archive template. */
function john_platt_project_archive_template( $template ) {
	if ( is_post_type_archive( 'project' ) && get_query_var( 'john_platt_project_archive' ) ) {
		$archive = locate_template( 'archive-project.php' );
		return $archive ?: $template;
	}
	return $template;
}
add_filter( 'template_include', 'john_platt_project_archive_template' );
/** Existing terms only; multiple assignments use one dropdown per selection. */
function john_platt_project_category_controls( $post ) {
	wp_nonce_field( 'john_platt_assign_project_categories', 'john_platt_project_categories_nonce' );
	$terms = get_terms( array( 'taxonomy' => 'project_category', 'hide_empty' => false, 'orderby' => 'name' ) );
	$selected = wp_get_object_terms( $post->ID, 'project_category', array( 'fields' => 'ids' ) );
	$selected = is_wp_error( $selected ) ? array() : $selected;
	if ( is_wp_error( $terms ) ) { $terms = array(); }
	$selected[] = 0;
	?>
	<div class="jp-project-category-controls">
		<p><?php esc_html_e( 'Select existing Project categories. Create or rename them under Projects → Project Categories.', 'john-platt' ); ?></p>
		<div class="jp-project-category-selects">
			<?php foreach ( $selected as $index => $value ) : ?>
				<p><label><?php esc_html_e( 'Project category', 'john-platt' ); ?><br>
					<select name="john_platt_project_category_ids[]" style="max-width:100%">
						<option value="0"><?php esc_html_e( '— No category —', 'john-platt' ); ?></option>
						<?php foreach ( $terms as $term ) : ?><option value="<?php echo esc_attr( (string) $term->term_id ); ?>" <?php selected( (int) $value, (int) $term->term_id ); ?>><?php echo esc_html( $term->name ); ?></option><?php endforeach; ?>
					</select>
				</label></p>
			<?php endforeach; ?>
		</div>
		<button type="button" class="button jp-add-project-category" hidden><?php esc_html_e( 'Add another category', 'john-platt' ); ?></button>
	</div>
	<?php
}
function john_platt_save_project_category_assignment( $post_id ) {
	if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! in_array( get_post_type( $post_id ), array( 'post', 'project' ), true ) || ! current_user_can( 'edit_post', $post_id ) ) { return; }
	$nonce = $_POST['john_platt_project_categories_nonce'] ?? '';
	$taxonomy = get_taxonomy( 'project_category' );
	if ( ! $taxonomy || ! current_user_can( $taxonomy->cap->assign_terms ) || ! is_string( $nonce ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $nonce ) ), 'john_platt_assign_project_categories' ) ) { return; }
	$input = $_POST['john_platt_project_category_ids'] ?? array();
	if ( ! is_array( $input ) ) { return; }
	$ids = array();
	foreach ( $input as $value ) {
		if ( ! is_scalar( $value ) || ! ctype_digit( (string) $value ) || (int) $value < 1 ) { continue; }
		$term = get_term( (int) $value, 'project_category' );
		if ( $term instanceof WP_Term ) { $ids[] = $term->term_id; }
	}
	wp_set_object_terms( $post_id, array_values( array_unique( $ids ) ), 'project_category' );
}
add_action( 'save_post', 'john_platt_save_project_category_assignment', 20 );

/** Keep flagged articles visible in the existing Projects admin list. */
function john_platt_project_admin_query( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || ! function_exists( 'get_current_screen' ) ) { return; }
	$screen = get_current_screen();
	if ( ! $screen || 'edit' !== $screen->base || 'project' !== $screen->post_type || 'project' !== $query->get( 'post_type' ) ) { return; }
	$query->set( 'post_type', array( 'project', 'post' ) );
	$query->set( 'john_platt_project_admin', true );
}
add_action( 'pre_get_posts', 'john_platt_project_admin_query', 20 );

function john_platt_project_entry_column( $columns ) {
	$columns['john_platt_entry_type'] = __( 'Entry type', 'john-platt' );
	return $columns;
}
add_filter( 'manage_project_posts_columns', 'john_platt_project_entry_column' );
function john_platt_project_entry_column_value( $column, $post_id ) {
	if ( 'john_platt_entry_type' === $column ) {
		echo esc_html( 'post' === get_post_type( $post_id ) ? __( 'Writing article', 'john-platt' ) : __( 'Project', 'john-platt' ) );
	}
}
add_action( 'manage_project_posts_custom_column', 'john_platt_project_entry_column_value', 10, 2 );
add_action( 'manage_post_posts_custom_column', 'john_platt_project_entry_column_value', 10, 2 );

function john_platt_project_category_editor_assets() {
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->post_type, array( 'post', 'project' ), true ) || 'post' !== $screen->base ) { return; }
	$dependencies = $screen->is_block_editor() ? array( 'wp-dom-ready', 'wp-data', 'wp-edit-post' ) : array();
	wp_enqueue_script( 'john-platt-project-editor', get_theme_file_uri( '/assets/js/project-editor.js' ), $dependencies, (string) filemtime( get_theme_file_path( '/assets/js/project-editor.js' ) ), true );
}
add_action( 'admin_enqueue_scripts', 'john_platt_project_category_editor_assets' );
/** Native status tabs and pagination totals include the same flagged articles. */
function john_platt_project_admin_counts( $counts, $post_type, $perm ) {
	if ( 'project' !== $post_type || ! is_admin() || ! function_exists( 'get_current_screen' ) ) { return $counts; }
	$screen = get_current_screen();
	if ( ! $screen || 'edit' !== $screen->base || 'project' !== $screen->post_type ) { return $counts; }
	global $wpdb;
	$private = '';
	if ( 'readable' === $perm && ! current_user_can( 'read_private_posts' ) ) {
		$private = $wpdb->prepare( " AND (p.post_status != 'private' OR p.post_author = %d)", get_current_user_id() );
	}
	$rows = $wpdb->get_results( "SELECT p.post_status, COUNT(*) AS total FROM {$wpdb->posts} p WHERE p.post_type = 'post' AND EXISTS (SELECT 1 FROM {$wpdb->postmeta} pm WHERE pm.post_id = p.ID AND pm.meta_key = '_john_platt_show_project' AND pm.meta_value = '1') {$private} GROUP BY p.post_status" );
	$counts = clone $counts;
	foreach ( $rows as $row ) {
		$counts->{$row->post_status} = ( $counts->{$row->post_status} ?? 0 ) + (int) $row->total;
	}
	return $counts;
}
add_filter( 'wp_count_posts', 'john_platt_project_admin_counts', 10, 3 );
add_action( 'manage_post_posts_custom_column', 'john_platt_project_order_column', 10, 2 );