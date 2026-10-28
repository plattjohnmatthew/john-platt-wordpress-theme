<?php
/**
 * Native Posts index with editable Posts-page introduction.
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
global $wp_query;
$writing_title = __( 'Writing', 'john-platt' );
$writing_intro = wpautop( esc_html__( 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'john-platt' ) );
$writing_page_id = (int) get_option( 'page_for_posts' );
$writing_page = $writing_page_id ? get_post( $writing_page_id ) : null;
if ( $writing_page instanceof WP_Post && 'page' === $writing_page->post_type && 'publish' === $writing_page->post_status ) {
	if ( '' !== trim( $writing_page->post_title ) ) {
		$writing_title = get_the_title( $writing_page );
	}
	if ( '' !== trim( $writing_page->post_content ) ) {
		$writing_intro = apply_filters( 'the_content', $writing_page->post_content );
	}
}
$selected_writing_topics = john_platt_selected_writing_topics();
get_header();
?>
<main id="main" class="writing-page" tabindex="-1">
	<section class="writing-intro" aria-labelledby="writing-heading">
		<div class="writing-intro__heading">
			<h1 id="writing-heading" class="writing-intro__title"><?php echo esc_html( $writing_title ); ?></h1>
			<nav class="writing-topics" aria-label="<?php esc_attr_e( 'Filter writing by topic', 'john-platt' ); ?>">
				<ul role="list">
					<li><a data-writing-topic="" href="<?php echo esc_url( john_platt_writing_topic_url( $selected_writing_topics ) ); ?>"<?php if ( ! $selected_writing_topics ) : ?> aria-current="true"<?php endif; ?>><?php esc_html_e( 'All', 'john-platt' ); ?></a></li>
					<?php foreach ( john_platt_writing_filter_terms( $selected_writing_topics ) as $topic ) : ?>
						<?php $topic_active = in_array( $topic->slug, $selected_writing_topics, true ); ?>
						<li><a data-writing-topic="<?php echo esc_attr( $topic->slug ); ?>"<?php if ( $topic_active ) : ?> class="is-active"<?php endif; ?> href="<?php echo esc_url( john_platt_writing_topic_url( $selected_writing_topics, $topic->slug ) ); ?>" aria-label="<?php echo esc_attr( sprintf( $topic_active ? __( '%s (selected; remove filter)', 'john-platt' ) : __( '%s (add filter)', 'john-platt' ), $topic->name ) ); ?>"><?php echo esc_html( $topic->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>
		<div class="writing-intro__body"><?php echo $writing_intro; // Native WordPress editor output. ?></div>
	</section>
	<p class="writing-filter-status" role="status" aria-live="polite" aria-atomic="true" data-loading-message="<?php esc_attr_e( 'Loading articles.', 'john-platt' ); ?>"></p>
	<div class="writing-list" data-results-message="<?php echo esc_attr( sprintf( _n( '%s article shown.', '%s articles shown.', (int) $wp_query->post_count, 'john-platt' ), number_format_i18n( (int) $wp_query->post_count ) ) ); ?>">
		<?php if ( have_posts() ) : ?>
			<?php while ( have_posts() ) : the_post(); ?>
				<?php get_template_part( 'template-parts/content', 'writing-row' ); ?>
			<?php endwhile; ?>
			<?php john_platt_writing_pagination(); ?>
		<?php else : ?>
			<div class="writing-list__empty">
				<h2><?php echo esc_html( $selected_writing_topics ? __( 'NO WRITING FOUND FOR THESE TOPICS.', 'john-platt' ) : __( 'No articles published yet.', 'john-platt' ) ); ?></h2>
				<?php if ( $selected_writing_topics ) : ?>
					<p><a href="<?php echo esc_url( john_platt_writing_topic_url( $selected_writing_topics ) ); ?>"><?php esc_html_e( 'SHOW ALL →', 'john-platt' ); ?></a></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
