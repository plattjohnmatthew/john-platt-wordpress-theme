<?php
/**
 * Template Name: Index
 * Chronological public index, independent of curated Project ordering.
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="index-page" tabindex="-1">
	<?php while ( have_posts() ) : the_post(); ?>
		<header class="index-intro">
			<h1 class="surface-title"><?php echo esc_html( get_the_title() ); ?></h1>
			<div class="index-intro__body"><?php the_content(); ?></div>
		</header>
		<?php if ( ! post_password_required() ) : ?>
			<?php
			$index_page = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
			$index_query = new WP_Query( array(
				'post_type' => array( 'post', 'project' ),
				'post_status' => 'publish',
				'posts_per_page' => 40,
				'paged' => $index_page,
				'orderby' => array( 'date' => 'DESC', 'ID' => 'DESC' ),
				'ignore_sticky_posts' => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			) );
			$index_year = '';
			?>
			<?php if ( $index_query->have_posts() ) : ?>
				<div class="index-list">
					<?php while ( $index_query->have_posts() ) : $index_query->the_post(); ?>
						<?php $entry_year = get_the_date( 'Y' ); ?>
						<?php if ( $entry_year !== $index_year ) : ?>
							<h2 class="index-year"><?php echo esc_html( $entry_year ); ?></h2>
							<?php $index_year = $entry_year; ?>
						<?php endif; ?>
						<article class="index-entry">
							<time datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>"><?php echo esc_html( get_the_date( 'd M' ) ); ?></time>
							<span class="index-entry__type"><?php echo esc_html( 'project' === get_post_type() ? __( 'Project', 'john-platt' ) : __( 'Writing', 'john-platt' ) ); ?></span>
							<h3><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h3>
						</article>
					<?php endwhile; ?>
				</div>
				<?php
				$index_pagination = paginate_links( array( 'current' => $index_page, 'total' => $index_query->max_num_pages, 'type' => 'list' ) );
				if ( $index_pagination ) {
					echo '<nav class="index-pagination" aria-label="' . esc_attr__( 'Index pages', 'john-platt' ) . '">' . wp_kses_post( $index_pagination ) . '</nav>';
				}
				?>
			<?php else : ?>
				<p class="index-empty"><?php esc_html_e( 'Nothing published yet.', 'john-platt' ); ?></p>
			<?php endif; ?>
			<?php wp_reset_postdata(); ?>
		<?php endif; ?>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
