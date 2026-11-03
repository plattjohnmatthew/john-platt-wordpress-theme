<?php
/**
 * Native Project case study.
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="article-page project-page" tabindex="-1">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
		$project_media = john_platt_project_media();
		$project_article_image = $project_media['image'] && ! $project_media['video'] && ! post_password_required();
		?>
		<article <?php post_class( 'project-article' . ( $project_article_image ? ' project-article--article-image' : '' ) ); ?>>
			<header class="article-header">
				<div class="article-header__meta">
					<?php if ( $project_article_image ) : ?>
						<figure class="article-featured article-header__image">
							<div class="article-header__visual"><?php echo wp_get_attachment_image( $project_media['image'], 'full', false, array( 'class' => 'article-featured__image', 'sizes' => '(min-width: 64rem) 28vw, 100vw' ) ); ?></div>
							<?php $project_caption = wp_get_attachment_caption( $project_media['image'] ); ?>
							<?php if ( $project_caption ) : ?><figcaption><?php echo wp_kses_post( wpautop( $project_caption ) ); ?></figcaption><?php endif; ?>
						</figure>
					<?php endif; ?>
					<p class="project-header__archive"><a href="<?php echo esc_url( get_post_type_archive_link( 'project' ) ); ?>"><?php esc_html_e( 'Projects', 'john-platt' ); ?></a></p>
				</div>
				<div class="article-header__content">
					<h1 class="article-header__title"><?php echo esc_html( get_the_title() ); ?></h1>
					<?php if ( ! post_password_required() ) : ?>
						<?php get_template_part( 'template-parts/project', 'details', array( 'show_cta' => false ) ); ?>
						<?php if ( has_excerpt() ) : ?><p class="article-header__standfirst"><?php echo esc_html( wp_strip_all_tags( strip_shortcodes( get_the_excerpt() ), true ) ); ?></p><?php endif; ?>
					<?php endif; ?>
				</div>
			</header>
			<?php if ( $project_media['video'] ) : ?>
				<div class="project-featured"><?php get_template_part( 'template-parts/project', 'media', array( 'media' => $project_media ) ); ?></div>
			<?php endif; ?>
			<div class="article-body">
				<?php the_content(); ?>
				<?php wp_link_pages( array( 'before' => '<nav class="article-pages" aria-label="' . esc_attr__( 'Case study pages', 'john-platt' ) . '">', 'after' => '</nav>' ) ); ?>
			</div>

			<?php
			$project_navigation = get_the_post_navigation(
				array(
					'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous project', 'john-platt' ) . '</span><span class="nav-title">%title</span>',
					'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next project', 'john-platt' ) . '</span><span class="nav-title">%title</span>',
				)
			);
			$project_has_cta = ! post_password_required() && john_platt_project_field( 'cta_label' ) && esc_url( john_platt_project_field( 'cta_url' ), array( 'http', 'https' ) );
			if ( $project_navigation || $project_has_cta ) :
				?>
				<footer class="article-footer project-footer">
					<?php if ( $project_has_cta ) : ?>
						<?php get_template_part( 'template-parts/project', 'details', array( 'show_metadata' => false ) ); ?>
					<?php endif; ?>
					<?php echo $project_navigation; // Native adjacent-post navigation stays within the current post type. ?>
				</footer>
			<?php endif; ?>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
