<?php
/**
 * Native Project archive.
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
$projects_intro_page = get_page_by_path( 'projects-info' );
$projects_intro = '';
if ( $projects_intro_page instanceof WP_Post && 'publish' === $projects_intro_page->post_status && '' === $projects_intro_page->post_password && '' !== trim( $projects_intro_page->post_content ) ) {
	$projects_intro = apply_filters( 'the_content', $projects_intro_page->post_content );
}
global $wp_query;
$selected_project_categories = john_platt_selected_project_categories();
get_header();
?>
<main id="main" class="projects-page" tabindex="-1">
	<header class="projects-intro">
		<div class="projects-intro__heading">
			<h1 class="projects-intro__title"><?php post_type_archive_title(); ?></h1>
			<nav class="writing-topics project-topics" aria-label="<?php esc_attr_e( 'Filter projects by category', 'john-platt' ); ?>">
				<ul role="list">
					<li><a data-writing-topic="" href="<?php echo esc_url( john_platt_project_category_url() ); ?>"<?php if ( ! $selected_project_categories ) : ?> aria-current="true"<?php endif; ?>><?php esc_html_e( 'All', 'john-platt' ); ?></a></li>
					<?php foreach ( john_platt_project_filter_terms( $selected_project_categories ) as $category ) : ?>
						<?php $active = in_array( $category->slug, $selected_project_categories, true ); ?>
						<li><a data-writing-topic="<?php echo esc_attr( $category->slug ); ?>"<?php if ( $active ) : ?> class="is-active"<?php endif; ?> href="<?php echo esc_url( john_platt_project_category_url( $selected_project_categories, $category->slug ) ); ?>" aria-label="<?php echo esc_attr( sprintf( $active ? __( '%s (selected; remove filter)', 'john-platt' ) : __( '%s (add filter)', 'john-platt' ), $category->name ) ); ?>"><?php echo esc_html( $category->name ); ?></a></li>
					<?php endforeach; ?>
				</ul>
			</nav>
		</div>
		<?php if ( '' !== $projects_intro ) : ?>
			<div class="projects-intro__body"><?php echo $projects_intro; // Native WordPress editor output. ?></div>
		<?php endif; ?>
	</header>
	<p class="writing-filter-status" role="status" aria-live="polite" aria-atomic="true" data-loading-message="<?php esc_attr_e( 'Loading projects.', 'john-platt' ); ?>"></p>
	<div class="project-list" data-results-message="<?php echo esc_attr( sprintf( _n( '%s project shown.', '%s projects shown.', (int) $wp_query->post_count, 'john-platt' ), number_format_i18n( (int) $wp_query->post_count ) ) ); ?>">
	<?php if ( have_posts() ) : ?>
		<?php while ( have_posts() ) : the_post(); ?>
			<?php get_template_part( 'template-parts/content', 'project-chapter' ); ?>
		<?php endwhile; ?>
		<?php john_platt_project_pagination(); ?>
	<?php else : ?>
				<div class="projects-empty">
			<h2><?php echo esc_html( $selected_project_categories ? __( 'NO PROJECTS FOUND FOR THESE CATEGORIES.', 'john-platt' ) : __( 'No projects published yet.', 'john-platt' ) ); ?></h2>
			<?php if ( $selected_project_categories ) : ?><p><a href="<?php echo esc_url( john_platt_project_category_url() ); ?>"><?php esc_html_e( 'SHOW ALL →', 'john-platt' ); ?></a></p><?php endif; ?>
		</div>
	<?php endif; ?>
	</div>
</main>
<?php get_footer(); ?>
