<?php
/** Useful, quiet not-found page. @package John_Platt */
defined( 'ABSPATH' ) || exit;
$routes = array( __( 'Home', 'john-platt' ) => home_url( '/' ) );
$writing_id = (int) get_option( 'page_for_posts' );
if ( $writing_id ) { $routes[ __( 'Writing', 'john-platt' ) ] = get_permalink( $writing_id ); }
$projects_url = get_post_type_archive_link( 'project' );
if ( $projects_url ) { $routes[ __( 'Projects', 'john-platt' ) ] = $projects_url; }
$index_url = john_platt_page_url( 'index' );
if ( $index_url ) { $routes[ __( 'Index', 'john-platt' ) ] = $index_url; }
get_header();
?>
<main id="main" class="error-page" tabindex="-1">
	<p class="error-code">404</p>
	<h1 class="error-title"><?php esc_html_e( "This page isn't here.", 'john-platt' ); ?></h1>
	<nav class="error-routes" aria-label="<?php esc_attr_e( 'Useful pages', 'john-platt' ); ?>">
		<ul role="list">
			<?php foreach ( $routes as $label => $url ) : ?><li><a href="<?php echo esc_url( $url ); ?>"><?php echo esc_html( $label ); ?></a></li><?php endforeach; ?>
		</ul>
	</nav>
</main>
<?php get_footer(); ?>
