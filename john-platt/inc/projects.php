<?php
/**
 * Native Project content type and optional ACF Free details.
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;

function john_platt_register_projects() {
	register_post_type(
		'project',
		array(
			'labels' => array(
				'name' => __( 'Projects', 'john-platt' ),
				'singular_name' => __( 'Project', 'john-platt' ),
				'add_new_item' => __( 'Add New Project', 'john-platt' ),
				'edit_item' => __( 'Edit Project', 'john-platt' ),
			),
			'public' => true,
			'show_in_rest' => true,
			'has_archive' => 'projects',
			'rewrite' => array( 'slug' => 'projects', 'with_front' => false ),
			'menu_icon' => 'dashicons-portfolio',
			'supports' => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
		)
	);
}
add_action( 'init', 'john_platt_register_projects' );

/** Persist rewrite rules only on theme activation, never on ordinary requests. */
function john_platt_projects_rewrite_rules() {
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'john_platt_projects_rewrite_rules' );

/** Order only the public Project archive's native main query. */
function john_platt_projects_order( $query ) {
	if ( ! is_admin() && $query->is_main_query() && $query->is_post_type_archive( 'project' ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC', 'ID' => 'DESC' ) );
	}
}
add_action( 'pre_get_posts', 'john_platt_projects_order' );

function john_platt_register_project_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	$fields = array();
	$definitions = array(
		'presentation_mode' => array(
			'label' => __( 'Presentation Mode', 'john-platt' ),
			'type' => 'select',
			'choices' => array(
				'immersive' => __( 'Immersive media', 'john-platt' ),
				'editorial' => __( 'Editorial / text-first', 'john-platt' ),
				'simple' => __( 'Simple', 'john-platt' ),
			),
			'default_value' => 'immersive',
			'return_format' => 'value',
			'instructions' => __( 'Immersive media: Start with large media and transition into the text/media layout. Editorial / text-first: Prioritize typography and written content. Simple: Show the final static text/media layout without immersive scrolling.', 'john-platt' ),
		),
		'archive_narrative' => array( 'label' => __( 'Archive narrative', 'john-platt' ), 'type' => 'textarea', 'rows' => 8, 'new_lines' => '', 'instructions' => __( 'Optional longer introduction for the Projects archive. The excerpt is used when empty.', 'john-platt' ) ),
		'client' => array( 'label' => __( 'Client / company', 'john-platt' ), 'type' => 'text' ),
		'year' => array( 'label' => __( 'Year', 'john-platt' ), 'type' => 'text' ),
		'role' => array( 'label' => __( 'Role', 'john-platt' ), 'type' => 'text' ),
		'cta_label' => array( 'label' => __( 'External link label', 'john-platt' ), 'type' => 'text' ),
		'cta_url' => array( 'label' => __( 'External link URL', 'john-platt' ), 'type' => 'url' ),
		'video_poster' => array( 'label' => __( 'Optional video poster', 'john-platt' ), 'type' => 'image', 'return_format' => 'id' ),
		'media_fit' => array( 'label' => __( 'Media fit', 'john-platt' ), 'type' => 'select', 'choices' => array( 'contain' => __( 'Contain — preserve entire image', 'john-platt' ), 'cover' => __( 'Cover — crop to fill', 'john-platt' ) ), 'default_value' => 'contain', 'return_format' => 'value' ),
		'video_fill_opening' => array(
			'label' => __( 'Fill video opening frame', 'john-platt' ),
			'type' => 'true_false',
			'ui' => 1,
			'default_value' => 1,
			'message' => __( 'Zoom/crop the immersive video opening to fill its frame.', 'john-platt' ),
			'instructions' => __( 'Enabled by default. Turn off to show the entire video with space around it. The final right-hand column always shows the complete video. Does not affect static/mobile layouts or case studies.', 'john-platt' ),
		),
		'video' => array( 'label' => __( 'Optional MP4', 'john-platt' ), 'type' => 'file', 'return_format' => 'id', 'mime_types' => 'mp4', 'instructions' => __( 'Select an uploaded MP4 or a public VideoPress MP4 from the Media Library. Eligible desktop immersive previews play muted; static layouts retain native controls. Featured image supplies the poster and fallback. Add captions within the video or an equivalent transcript in the case study when speech is meaningful.', 'john-platt' ) ),
	);
	foreach ( $definitions as $name => $definition ) {
		$fields[] = array_merge( array( 'key' => 'field_john_platt_project_' . $name, 'name' => 'project_' . $name, 'required' => 0 ), $definition );
	}
	acf_add_local_field_group(
		array(
			'key' => 'group_john_platt_project_details',
			'title' => __( 'Project Details', 'john-platt' ),
			'fields' => $fields,
			'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'project' ) ) ),
		)
	);
}
add_action( 'acf/init', 'john_platt_register_project_fields' );

/** Optional fields must remain safe with ACF disabled. */
function john_platt_project_field( $name ) {
	$value = function_exists( 'get_field' ) ? get_field( 'project_' . $name, get_the_ID() ) : '';
	return is_scalar( $value ) ? trim( (string) $value ) : '';
}

/** Resolve valid attachments once so templates agree on all four media states. */
function john_platt_project_media() {
	if ( post_password_required() ) {
		return array( 'video' => '', 'image' => 0, 'poster' => '', 'poster_id' => 0, 'fit' => 'contain', 'video_id' => 0 );
	}
	$video_id = absint( john_platt_project_field( 'video' ) );
	$video_mime = $video_id ? get_post_mime_type( $video_id ) : '';
	$video_supported = 'video/mp4' === $video_mime || ( 'video/videopress' === $video_mime && ( is_post_type_archive( 'project' ) || is_singular( 'jp_portfolio' ) ) );
	$video = $video_id && 'attachment' === get_post_type( $video_id ) && $video_supported ? wp_get_attachment_url( $video_id ) : '';
	if ( $video && 'video/videopress' === $video_mime ) {
		$video_metadata = wp_get_attachment_metadata( $video_id );
		$video_path = wp_parse_url( $video, PHP_URL_PATH );
		// WordPress.com changes the attachment MIME but retains its public MP4 URL.
		// Private VideoPress playback needs its authenticated player, not this preview.
		if ( ! empty( $video_metadata['videopress']['is_private'] ) || ! is_string( $video_path ) || ! preg_match( '/\.mp4$/i', $video_path ) ) {
			$video = '';
		}
	}
	$image_id = get_post_thumbnail_id();
	$image_id = $image_id && wp_attachment_is_image( $image_id ) && wp_get_attachment_image_url( $image_id, 'large' ) ? $image_id : 0;
	$poster_id = absint( john_platt_project_field( 'video_poster' ) );
	$poster = $poster_id && wp_attachment_is_image( $poster_id ) ? wp_get_attachment_image_url( $poster_id, 'large' ) : '';
	$poster_id = $poster ? $poster_id : 0;
	if ( ! $poster && $image_id ) {
		$poster_id = $image_id;
		$poster = wp_get_attachment_image_url( $image_id, 'large' );
	}
	$fit = john_platt_project_field( 'media_fit' );
	return array(
		'video' => $video ?: '',
		'video_id' => $video ? $video_id : 0,
		'image' => $image_id,
		'poster' => $poster ?: '',
		'poster_id' => $poster_id,
		'fit' => in_array( $fit, array( 'contain', 'cover' ), true ) ? $fit : 'contain',
	);
}

/** Missing saved values default on, including when ACF is unavailable. */
function john_platt_project_video_fill_opening( $post_id ) {
	return '0' !== (string) get_post_meta( (int) $post_id, 'project_video_fill_opening', true );
}
/** Derive the archive split from the same attachment used for playback/display. */
function john_platt_project_media_layout( $media ) {
	$id = $media['video_id'] ?: $media['image'];
	$metadata = $id ? wp_get_attachment_metadata( $id ) : array();
	$width = isset( $metadata['width'] ) ? (float) $metadata['width'] : 0;
	$height = isset( $metadata['height'] ) ? (float) $metadata['height'] : 0;
	$ratio = $width > 0 && $height > 0 ? $width / $height : ( $media['video_id'] ? 16 / 9 : 1 );
	return array( 'ratio' => $ratio, 'share' => max( 24, min( 40, 14 + 20 * $ratio ) ) );
}
/** Make the native manual order visible in the Project admin list. */
function john_platt_project_columns( $columns ) {
	$columns['john_platt_order'] = __( 'Order', 'john-platt' );
	return $columns;
}
add_filter( 'manage_project_posts_columns', 'john_platt_project_columns' );

/** Display only the Project-specific column; preserve signed menu_order values. */
function john_platt_project_order_column( $column, $post_id ) {
	if ( 'john_platt_order' === $column ) {
		echo esc_html( (string) (int) get_post_field( 'menu_order', $post_id ) );
	}
}
add_action( 'manage_project_posts_custom_column', 'john_platt_project_order_column', 10, 2 );
