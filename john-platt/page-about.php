<?php
/**
 * Template Name: About
 * Native page content and optional portrait.
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="about-page" tabindex="-1">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'about-layout' ); ?>>
			<header class="about-sidebar">
				<h1 class="surface-title"><?php echo esc_html( get_the_title() ); ?></h1>
				<?php if ( ! post_password_required() && has_post_thumbnail() ) : ?>
					<figure class="about-portrait"><?php the_post_thumbnail( 'large', array( 'sizes' => '(min-width: 1024px) 28vw, 100vw' ) ); ?></figure>
				<?php endif; ?>
			</header>
			<div class="article-body about-content">
				<?php the_content(); ?>
				<?php wp_link_pages( array( 'before' => '<nav aria-label="' . esc_attr__( 'Page sections', 'john-platt' ) . '">', 'after' => '</nav>' ) ); ?>
			</div>
		</article>
	<?php endwhile; ?>
	<?php get_template_part( 'template-parts/about', 'contact' ); ?>
</main>
<?php get_footer(); ?>
