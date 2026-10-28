<?php
/** Native heading anchors and contents for Writing articles. @package John_Platt */
defined( 'ABSPATH' ) || exit;

/** Existing articles remain enabled; saved choices also work without ACF. */
function john_platt_article_contents_enabled( $post_id ) {
	return '0' !== (string) get_post_meta( (int) $post_id, 'article_contents_enabled', true );
}

/** One ACF Free toggle on ordinary Writing posts only. */
function john_platt_register_article_settings() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( array(
		'key' => 'group_john_platt_article_settings',
		'title' => __( 'Writing Article Settings', 'john-platt' ),
		'fields' => array( array(
			'key' => 'field_john_platt_article_contents_enabled',
			'name' => 'article_contents_enabled',
			'label' => __( 'Enable article contents', 'john-platt' ),
			'type' => 'true_false',
			'ui' => 1,
			'default_value' => 1,
			'message' => __( 'Show the “In this article” navigation.', 'john-platt' ),
			'instructions' => __( 'Lists H2 and H3 headings only when the article has at least two H2 sections. Related-content widgets are excluded. Turn off to hide the menu for this article.', 'john-platt' ),
		) ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) ),
		'position' => 'side',
	) );
}
add_action( 'acf/init', 'john_platt_register_article_settings' );
/** Add anchors without reserializing blocks, embeds or other article markup. */
function john_platt_article_contents( $html ) {
	$result = array( 'html' => $html, 'items' => array(), 'label_id' => '' );
	if ( ! class_exists( 'WP_HTML_Tag_Processor' ) || ! method_exists( 'WP_HTML_Tag_Processor', 'next_token' ) || ! method_exists( 'WP_HTML_Tag_Processor', 'get_modifiable_text' ) ) {
		return $result;
	}
	$reader = new WP_HTML_Tag_Processor( $html );
	$ids = array( 'main' => 1 );
	$headings = array();
	$excluded_tag = null;
	$excluded_depth = 0;
	$h2_count = 0;
	$current = null;
	while ( $reader->next_token() ) {
		$tag = $reader->get_tag();
		// Ignore plugin-generated related/sharing sections, without removing their markup.
		$excluded = null !== $excluded_tag;
		if ( ! $excluded && ! $reader->is_tag_closer() && $tag ) {
			$class = (string) $reader->get_attribute( 'class' );
			$id = (string) $reader->get_attribute( 'id' );
			if ( str_starts_with( $id, 'jp-relatedposts' ) || preg_match( '/(?:^|\s)(?:jp-relatedposts(?:-[\w-]+)?|jetpack-related-posts|wp-block-jetpack-related-posts|related-posts|sharedaddy)(?:\s|$)/', $class ) ) {
				$excluded_tag = $tag;
				$excluded_depth = 0;
				$excluded = true;
			}
		}
		if ( $excluded && $tag === $excluded_tag ) {
			$excluded_depth += $reader->is_tag_closer() ? -1 : 1;
			if ( $excluded_depth <= 0 ) { $excluded_tag = null; }
		}
		if ( ! $reader->is_tag_closer() ) {
			$id = $reader->get_attribute( 'id' );
			if ( is_string( $id ) && '' !== $id ) {
				$ids[ $id ] = ( $ids[ $id ] ?? 0 ) + 1;
			}
		}
		if ( in_array( $tag, array( 'H2', 'H3' ), true ) ) {
			if ( ! $reader->is_tag_closer() ) {
				if ( null !== $current ) {
					$headings[] = $current;
				}
				$current = array( 'level' => $tag, 'id' => $reader->get_attribute( 'id' ), 'title' => '', 'excluded' => $excluded );
			} elseif ( null !== $current && $tag === $current['level'] ) {
				$headings[] = $current;
				$current = null;
			}
		} elseif ( null !== $current && '#text' === $reader->get_token_type() ) {
			$current['title'] .= $reader->get_modifiable_text();
		} elseif ( null !== $current && 'BR' === $tag ) {
			$current['title'] .= ' ';
		}
	}
	if ( null !== $current ) {
		$headings[] = $current;
	}
	$label_id = 'article-contents-heading';
	$suffix = 2;
	while ( isset( $ids[ $label_id ] ) ) {
		$label_id = 'article-contents-heading-' . $suffix++;
	}
	$ids[ $label_id ] = 1;
	foreach ( $headings as &$heading ) {
		$heading['title'] = trim( preg_replace( '/[\s\p{Z}]+/u', ' ', $heading['title'] ) );
		if ( '' === $heading['title'] || $heading['excluded'] ) {
			continue;
		}
		if ( 'H2' === $heading['level'] ) { ++$h2_count; }
		$id = $heading['id'];
		if ( ! is_string( $id ) || '' === $id || 1 !== ( $ids[ $id ] ?? 0 ) ) {
			$base = 'article-section-' . ( sanitize_title( $heading['title'] ) ?: 'heading' );
			$id = $base;
			$suffix = 2;
			while ( isset( $ids[ $id ] ) ) {
				$id = $base . '-' . $suffix++;
			}
			$ids[ $id ] = 1;
		}
		$heading['id'] = $id;
		$entry = array( 'id' => $id, 'title' => $heading['title'], 'children' => array() );
		if ( 'H2' === $heading['level'] || ! $result['items'] ) {
			$result['items'][] = $entry;
		} else {
			$last = count( $result['items'] ) - 1;
			$result['items'][ $last ]['children'][] = $entry;
		}
	}
	unset( $heading );
	if ( ! $result['items'] ) {
		return $result;
	}
	$writer = new WP_HTML_Tag_Processor( $html );
	$index = 0;
	while ( $writer->next_tag() ) {
		if ( ! in_array( $writer->get_tag(), array( 'H2', 'H3' ), true ) ) {
			continue;
		}
		$heading = $headings[ $index++ ] ?? null;
		if ( ! $heading || '' === $heading['title'] || $heading['excluded'] ) {
			continue;
		}
		$writer->set_attribute( 'id', $heading['id'] );
		if ( null === $writer->get_attribute( 'tabindex' ) ) {
			$writer->set_attribute( 'tabindex', '-1' );
		}
	}
	$result['html'] = $writer->get_updated_html();
	$result['label_id'] = $h2_count > 1 ? $label_id : '';
	if ( $h2_count < 2 ) { $result['items'] = array(); }
	return $result;
}
/** Place navigation after the introduction, immediately before the first H2, leaving authored markup intact. */
function john_platt_place_article_contents( $html, $menu ) {
	if ( '' === trim( $menu ) || ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
		return $html;
	}
	$reader = new WP_HTML_Tag_Processor( $html );
	if ( $reader->next_tag( 'H2' ) ) {
		// Locate the native token without treating comments or scripts as headings.
		$marker = 'data-article-menu-' . substr( hash( 'sha256', $html ), 0, 16 );
		$reader->set_attribute( $marker, 'insert' );
		$marked = $reader->get_updated_html();
		$attribute = $marker . '="insert"';
		$position = strpos( $marked, $attribute );
		$start = strrpos( substr( $marked, 0, $position ), '<' );
		$marked = substr_replace( $marked, $menu, $start, 0 );
		return str_replace( ' ' . $attribute, '', $marked );
	}
	return $html;
}