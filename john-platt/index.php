<?php
/**
 * Default content template.
 *
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" tabindex="-1">
	<?php if ( have_posts() ) : ?>
		<?php if ( ! is_singular() ) : ?>
			<h1><?php
				if ( is_home() && get_option( 'page_for_posts' ) ) {
					echo esc_html( get_the_title( (int) get_option( 'page_for_posts' ) ) );
				} elseif ( is_archive() ) {
					echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
				} elseif ( is_search() ) {
					printf( esc_html__( 'Search results for: %s', 'john-platt' ), esc_html( get_search_query() ) );
				} else {
					esc_html_e( 'Posts', 'john-platt' );
				}
			?></h1>
		<?php endif; ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<article <?php post_class(); ?>>
				<?php if ( is_singular() ) : ?>
					<h1><?php echo esc_html( get_the_title() ); ?></h1>
					<?php the_content(); ?>
					<?php wp_link_pages(); ?>
				<?php else : ?>
					<h2><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>
					<?php the_excerpt(); ?>
				<?php endif; ?>
			</article>
		<?php endwhile; ?>
		<?php the_posts_pagination(); ?>
	<?php else : ?>
		<h1><?php esc_html_e( 'Nothing found', 'john-platt' ); ?></h1>
	<?php endif; ?>
</main>
<?php get_footer(); ?>
