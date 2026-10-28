<?php
/**
 * Native Writing topics, managed independently of ACF.
 *
 * @package John_Platt
 */

defined( 'ABSPATH' ) || exit;

/** Register non-hierarchical topics for standard Posts only. */
function john_platt_register_writing_topics() {
	register_taxonomy(
		'writing_topic',
		array( 'post' ),
		array(
			'labels'            => array(
				'name'                       => __( 'Writing Topics', 'john-platt' ),
				'singular_name'              => __( 'Writing Topic', 'john-platt' ),
				'menu_name'                  => __( 'Writing Topics', 'john-platt' ),
				'search_items'               => __( 'Search Writing Topics', 'john-platt' ),
				'popular_items'              => __( 'Popular Writing Topics', 'john-platt' ),
				'all_items'                  => __( 'All Writing Topics', 'john-platt' ),
				'edit_item'                  => __( 'Edit Writing Topic', 'john-platt' ),
				'view_item'                  => __( 'View Writing Topic', 'john-platt' ),
				'update_item'                => __( 'Update Writing Topic', 'john-platt' ),
				'add_new_item'               => __( 'Add New Writing Topic', 'john-platt' ),
				'new_item_name'              => __( 'New Writing Topic Name', 'john-platt' ),
				'separate_items_with_commas' => __( 'Separate writing topics with commas', 'john-platt' ),
				'add_or_remove_items'        => __( 'Add or remove writing topics', 'john-platt' ),
				'choose_from_most_used'      => __( 'Choose from the most used writing topics', 'john-platt' ),
				'not_found'                  => __( 'No writing topics found.', 'john-platt' ),
			),
			'public'            => true,
			'hierarchical'      => false,
			'show_ui'           => true,
			'show_in_menu'      => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => false,
		)
	);
}
add_action( 'init', 'john_platt_register_writing_topics' );

/** Missing order metadata has the same safe value as a newly created topic. */
function john_platt_writing_topic_order( $term_id ) {
	return (int) get_term_meta( $term_id, 'writing_topic_order', true );
}

/** Shared input for the native taxonomy Add and Edit forms. */
function john_platt_writing_topic_order_input( $value ) {
	wp_nonce_field( 'john_platt_save_writing_topic_order', 'john_platt_writing_topic_order_nonce' );
	?>
	<input type="number" name="writing_topic_order" id="writing-topic-order" value="<?php echo esc_attr( (string) $value ); ?>" step="1" aria-describedby="writing-topic-order-description">
	<p id="writing-topic-order-description" class="description"><?php esc_html_e( 'Lower numbers appear first. For example: 10, 20, 30, 40. Topics with no saved order use 0. Equal orders sort by topic name.', 'john-platt' ); ?></p>
	<?php
}

/** Add an optional integer order to new Writing Topics. */
function john_platt_add_writing_topic_order_field() {
	?>
	<div class="form-field">
		<label for="writing-topic-order"><?php esc_html_e( 'Display Order', 'john-platt' ); ?></label>
		<?php john_platt_writing_topic_order_input( 0 ); ?>
	</div>
	<?php
}
add_action( 'writing_topic_add_form_fields', 'john_platt_add_writing_topic_order_field' );

/** Show the stored order on the native Writing Topic Edit screen. */
function john_platt_edit_writing_topic_order_field( $term ) {
	?>
	<tr class="form-field">
		<th scope="row"><label for="writing-topic-order"><?php esc_html_e( 'Display Order', 'john-platt' ); ?></label></th>
		<td><?php john_platt_writing_topic_order_input( john_platt_writing_topic_order( $term->term_id ) ); ?></td>
	</tr>
	<?php
}
add_action( 'writing_topic_edit_form_fields', 'john_platt_edit_writing_topic_order_field' );

/** Save only explicit, authorized native-form submissions for this taxonomy. */
function john_platt_save_writing_topic_order( $term_id ) {
	if ( ! isset( $_POST['john_platt_writing_topic_order_nonce'], $_POST['writing_topic_order'] ) || ! is_string( $_POST['john_platt_writing_topic_order_nonce'] ) || ! is_string( $_POST['writing_topic_order'] ) ) {
		return;
	}
	if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['john_platt_writing_topic_order_nonce'] ) ), 'john_platt_save_writing_topic_order' ) ) {
		return;
	}
	$taxonomy = get_taxonomy( 'writing_topic' );
	$term     = get_term( $term_id, 'writing_topic' );
	if ( ! $taxonomy || ! current_user_can( $taxonomy->cap->manage_terms ) || ! $term instanceof WP_Term ) {
		return;
	}
	update_term_meta( $term_id, 'writing_topic_order', (int) wp_unslash( $_POST['writing_topic_order'] ) );
}
add_action( 'created_writing_topic', 'john_platt_save_writing_topic_order' );
add_action( 'edited_writing_topic', 'john_platt_save_writing_topic_order' );

/** Normalize a comma-separated selection against current taxonomy terms. */
function john_platt_parse_writing_topics( $value ) {
	if ( ! is_string( $value ) || '' === trim( $value ) ) {
		return array();
	}
	$slugs = array_values( array_unique( array_filter( array_map( 'sanitize_title', explode( ',', $value ) ), static function ( $slug ) { return '' !== $slug; } ) ) );
	if ( ! $slugs ) {
		return array();
	}
	$terms = get_terms( array(
		'taxonomy'   => 'writing_topic',
		'hide_empty' => false,
		'slug'       => $slugs,
		'fields'     => 'slugs',
	) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$terms = array_values( array_unique( $terms ) );
	sort( $terms, SORT_STRING );
	return $terms;
}

/** Filter only the assigned Writing Posts page's native main query. */
function john_platt_filter_writing_topics( $query ) {
	if ( is_admin() || ! $query->is_main_query() || ! $query->is_home() || $query->is_front_page() || ! (int) get_option( 'page_for_posts' ) ) {
		return;
	}
	$raw = isset( $_GET['topics'] ) && is_string( $_GET['topics'] ) ? wp_unslash( $_GET['topics'] ) : '';
	$selected = john_platt_parse_writing_topics( $raw );
	$query->set( 'john_platt_writing_topics', $selected );
	if ( ! $selected ) {
		return;
	}
	$topic_filter = array(
		'taxonomy'         => 'writing_topic',
		'field'            => 'slug',
		'terms'            => $selected,
		'operator'         => 'IN',
		'include_children' => false,
	);
	// WordPress otherwise broadens the default post type when a tax query is added.
	$query->set( 'post_type', 'post' );
	$existing = $query->get( 'tax_query' );
	// Keep any independent constraints; IN provides OR between selected topics.
	$query->set( 'tax_query', $existing ? array( 'relation' => 'AND', $existing, $topic_filter ) : array( $topic_filter ) );
}
add_action( 'pre_get_posts', 'john_platt_filter_writing_topics' );

/** Validated, sorted selections for templates and their pagination links. */
function john_platt_selected_writing_topics() {
	$selected = get_query_var( 'john_platt_writing_topics', array() );
	return is_array( $selected ) ? $selected : array();
}

/** Hide unused topics, except active selections visitors must be able to remove. */
function john_platt_writing_filter_terms( $selected ) {
	$terms = get_terms( array( 'taxonomy' => 'writing_topic', 'hide_empty' => false ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$terms = array_values( array_filter( $terms, static function ( $term ) use ( $selected ) {
		return $term->count > 0 || in_array( $term->slug, $selected, true );
	} ) );
	usort( $terms, static function ( $left, $right ) {
		$order = john_platt_writing_topic_order( $left->term_id ) <=> john_platt_writing_topic_order( $right->term_id );
		return $order ?: ( strcasecmp( $left->name, $right->name ) ?: $left->term_id <=> $right->term_id );
	} );
	return $terms;
}

/** Toggle a validated term and always return the first Writing results page. */
function john_platt_writing_topic_url( $selected, $toggle = '' ) {
	$page_id = (int) get_option( 'page_for_posts' );
	$url = $page_id ? get_permalink( $page_id ) : home_url( '/' );
	if ( '' === $toggle ) {
		return $url;
	}
	if ( in_array( $toggle, $selected, true ) ) {
		$selected = array_values( array_diff( $selected, array( $toggle ) ) );
	} else {
		$selected[] = $toggle;
	}
	$selected = array_values( array_unique( $selected ) );
	sort( $selected, SORT_STRING );
	return $selected ? add_query_arg( 'topics', implode( ',', $selected ), $url ) : $url;
}

/** Normalize final links because core merges request arguments after add_args. */
function john_platt_writing_pagination() {
	$selected = john_platt_selected_writing_topics();
	$normalize = static function ( $url ) use ( $selected ) {
		$url = remove_query_arg( 'topics', $url );
		return $selected ? add_query_arg( 'topics', implode( ',', $selected ), $url ) : $url;
	};
	add_filter( 'paginate_links', $normalize );
	try {
		the_posts_pagination();
	} finally {
		remove_filter( 'paginate_links', $normalize );
	}
}
