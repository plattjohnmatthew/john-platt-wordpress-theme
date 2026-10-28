<?php
/** Combined public About/Contact presentation; existing editorial data stays intact. @package John_Platt */
defined( 'ABSPATH' ) || exit;

/** The former Contact page remains the native editor source for the lower section. */
function john_platt_contact_page() {
	$page = get_page_by_path( 'contact' );
	if ( $page instanceof WP_Post && 'publish' === $page->post_status ) {
		return $page;
	}
	$pages = get_posts( array(
		'post_type' => 'page', 'post_status' => 'publish', 'numberposts' => 1,
		'meta_key' => '_wp_page_template', 'meta_value' => 'page-contact.php',
	) );
	return $pages ? $pages[0] : null;
}

/** Keep the established ACF contact details as one shared source. */
function john_platt_contact_details() {
	$homepage_id = (int) get_option( 'page_on_front' );
	$email = $homepage_id && function_exists( 'get_field' ) ? get_field( 'contact_email', $homepage_id ) : '';
	$linkedin = $homepage_id && function_exists( 'get_field' ) ? get_field( 'contact_linkedin_url', $homepage_id ) : '';
	return array(
		'email' => is_string( $email ) && is_email( $email ) ? $email : '',
		'linkedin' => is_string( $linkedin ) ? esc_url( $linkedin, array( 'http', 'https' ) ) : '',
	);
}

/** The Contact page is now the public destination; preserve old About bookmarks. */
function john_platt_redirect_contact() {
	if ( is_admin() || is_preview() || is_feed() ) {
		return;
	}
	$about = get_page_by_path( 'about' );
	$contact = john_platt_contact_page();
	if ( $about && $contact && is_page( $about->ID ) && $about->ID !== $contact->ID ) {
		wp_safe_redirect( get_permalink( $contact ), 301, 'John Platt' );
		exit;
	}
}
add_action( 'template_redirect', 'john_platt_redirect_contact', 5 );

/** Keep the managed menu order, replacing its About destination with Contact. */
function john_platt_combined_primary_menu( $items, $args ) {
	if ( 'primary' !== ( $args->theme_location ?? '' ) ) {
		return $items;
	}
	$contact = john_platt_contact_page();
	$about_url = john_platt_page_url( 'about' );
	if ( ! $contact ) {
		return $items;
	}
	$contact_url = get_permalink( $contact );
	$result = array();
	$seen = false;
	foreach ( $items as $item ) {
		$url = untrailingslashit( preg_replace( '/[?#].*$/', '', $item->url ) );
		$is_contact = $url === untrailingslashit( $contact_url );
		$is_about = $about_url && $url === untrailingslashit( $about_url );
		if ( $is_about || $is_contact ) {
			if ( $seen ) {
				continue;
			}
			$seen = true;
			$item->title = __( 'Contact', 'john-platt' );
			$item->url = $contact_url;
			$item->object_id = $contact->ID;
			$item->current = is_page( $contact->ID );
			$item->classes = array_diff( $item->classes, array( 'current-menu-item', 'current_page_item' ) );
			if ( $item->current ) {
				$item->classes[] = 'current-menu-item';
			}
		}
		$result[] = $item;
	}
	return $result;
}
add_filter( 'wp_nav_menu_objects', 'john_platt_combined_primary_menu', 10, 2 );

/** Editable CTA settings on the Contact page, independent of ACF Pro. */
function john_platt_register_contact_cta() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( array(
		'key' => 'group_john_platt_contact_cta',
		'title' => __( 'Contact CTA', 'john-platt' ),
		'fields' => array(
			array(
				'key' => 'field_john_platt_contact_cta_url',
				'name' => 'contact_cta_url',
				'label' => __( 'Get in Touch destination', 'john-platt' ),
				'type' => 'text',
				'default_value' => '',
				'instructions' => __( 'Use mailto:you@example.com for email, or a full https:// URL.', 'john-platt' ),
			),
			array(
				'key' => 'field_john_platt_contact_cta_image',
				'name' => 'contact_cta_image',
				'label' => __( 'CTA hand image', 'john-platt' ),
				'type' => 'image',
				'return_format' => 'id',
				'preview_size' => 'thumbnail',
				'instructions' => __( 'Optional override. Otherwise reuses the homepage pointing-hand image.', 'john-platt' ),
			),
		),
		'location' => array(
			array( array( 'param' => 'page', 'operator' => '==', 'value' => (string) ( john_platt_contact_page()->ID ?? 0 ) ) ),
			array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'page-contact.php' ) ),
		),
	) );
}
add_action( 'acf/init', 'john_platt_register_contact_cta' );
