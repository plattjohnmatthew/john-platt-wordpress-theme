<?php
/**
 * Theme setup and assets.
 *
 * @package John_Platt
 */

defined( 'ABSPATH' ) || exit;

require_once get_template_directory() . '/inc/acf-fields.php';
require_once get_template_directory() . '/inc/projects.php';
require_once get_template_directory() . '/inc/project-categories.php';
require_once get_template_directory() . '/inc/writing-topics.php';
require_once get_template_directory() . '/inc/article-contents.php';
require_once get_template_directory() . '/inc/site.php';
require_once get_template_directory() . '/inc/portrait.php';
require_once get_template_directory() . '/inc/about-contact.php';
require_once get_template_directory() . '/inc/metadata.php';
require_once get_template_directory() . '/inc/mobile-media.php';
require_once get_template_directory() . '/inc/portfolios.php';
require_once get_template_directory() . '/inc/reading-layout.php';
require_once get_template_directory() . '/inc/selective-assets.php';
require_once get_template_directory() . '/inc/delayed-analytics.php';

/** Register core theme features. */
function john_platt_setup() {
	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	register_nav_menus( array( 'primary' => __( 'Primary', 'john-platt' ) ) );
}
add_action( 'after_setup_theme', 'john_platt_setup' );

/** Load the dependency-free theme assets. */
function john_platt_enqueue_assets() {
	wp_enqueue_script( 'john-platt-header', get_theme_file_uri( '/assets/js/header.js' ), array(), (string) filemtime( get_theme_file_path( '/assets/js/header.js' ) ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	$css_path = get_theme_file_path( '/assets/css/main.css' );
	$version  = is_readable( $css_path ) ? (string) filemtime( $css_path ) : wp_get_theme()->get( 'Version' );
	wp_enqueue_style( 'john-platt-main', get_theme_file_uri( '/assets/css/main.css' ), array(), $version );
	if ( is_front_page() ) {
		wp_enqueue_script( 'john-platt-home-header', get_theme_file_uri( '/assets/js/home-header.js' ), array( 'john-platt-header' ), (string) filemtime( get_theme_file_path( '/assets/js/home-header.js' ) ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
	if ( is_front_page() || is_home() ) {
		$script_path    = get_theme_file_path( '/assets/js/main.js' );
		$script_version = is_readable( $script_path ) ? (string) filemtime( $script_path ) : wp_get_theme()->get( 'Version' );
		wp_enqueue_script(
			'john-platt-scroll',
			get_theme_file_uri( '/assets/js/main.js' ),
			array(),
			$script_version,
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}
	if ( ( is_home() && ! is_front_page() ) || is_post_type_archive( 'project' ) ) {
		wp_enqueue_script(
			'john-platt-writing-filters',
			get_theme_file_uri( '/assets/js/writing-filters.js' ),
			is_post_type_archive( 'project' ) ? array( 'john-platt-projects' ) : array( 'john-platt-scroll' ),
			(string) filemtime( get_theme_file_path( '/assets/js/writing-filters.js' ) ),
			array( 'strategy' => 'defer', 'in_footer' => true )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'john_platt_enqueue_assets' );

/** Project interaction is isolated from the homepage and Writing script. */
function john_platt_enqueue_project_assets() {
	if ( is_post_type_archive( 'project' ) || is_singular( array( 'project', 'jp_portfolio' ) ) ) {
		wp_enqueue_script( 'john-platt-projects', get_theme_file_uri( '/assets/js/projects.js' ), array( 'john-platt-header' ), (string) filemtime( get_theme_file_path( '/assets/js/projects.js' ) ), array( 'strategy' => 'defer', 'in_footer' => true ) );
	}
}
add_action( 'wp_enqueue_scripts', 'john_platt_enqueue_project_assets' );
