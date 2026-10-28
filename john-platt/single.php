<?php
/**
 * Native Writing article.
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
get_header();
?>
<main id="main" class="article-page" tabindex="-1">
	<?php while ( have_posts() ) : the_post(); ?>
		<?php
		$article_standfirst = ! post_password_required() && has_excerpt() ? trim( wp_strip_all_tags( strip_shortcodes( get_the_excerpt() ), true ) ) : '';
		$article_image = ! post_password_required() ? get_the_post_thumbnail( null, 'large', array( 'class' => 'article-featured__image', 'sizes' => '(min-width: 64rem) 28vw, 100vw' ) ) : '';
		?>
		<article <?php post_class( 'writing-article' ); ?>>
			<header class="article-header">
				<div class="article-header__meta">

					<?php if ( $article_image ) : ?>
						<figure class="article-featured article-header__image">
							<div class="article-header__visual">
								<?php echo $article_image; // Native attachment markup. ?>
							</div>
							<?php $article_caption = wp_get_attachment_caption( get_post_thumbnail_id() ); ?>
							<?php if ( $article_caption ) : ?>
								<figcaption><?php echo wp_kses_post( wpautop( $article_caption ) ); ?></figcaption>
							<?php endif; ?>
						</figure>
					<?php endif; ?>

					<p class="article-header__date"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
				</div>
				<div class="article-header__content">
					<h1 class="article-header__title"><?php echo esc_html( get_the_title() ); ?></h1>
					<?php if ( $article_standfirst ) : ?>
						<p class="article-header__standfirst"><?php echo esc_html( $article_standfirst ); ?></p>
					<?php endif; ?>
				</div>
			</header>
			<div class="article-body">
				<?php
				ob_start();
				the_content();
				$article_html = ob_get_clean();
				$article_contents = ! post_password_required() ? john_platt_article_contents( $article_html ) : array( 'html' => $article_html, 'items' => array() );
				if ( ! john_platt_article_contents_enabled( get_the_ID() ) ) {
					$article_contents['items'] = array(); // Keep heading anchors stable when only the menu is disabled.
				}
				ob_start();
				get_template_part( 'template-parts/article', 'contents', $article_contents );
				$article_menu = ob_get_clean();
				echo john_platt_place_article_contents( $article_contents['html'], $article_menu ); // Native content and escaped navigation markup.
				?>
				<?php wp_link_pages( array( 'before' => '<nav class="article-pages" aria-label="' . esc_attr__( 'Article pages', 'john-platt' ) . '">', 'after' => '</nav>' ) ); ?>
			</div>
			<?php
			$article_navigation = get_the_post_navigation(
				array(
					'prev_text' => '<span class="nav-subtitle">' . esc_html__( 'Previous', 'john-platt' ) . '</span><span class="nav-title">%title</span>',
					'next_text' => '<span class="nav-subtitle">' . esc_html__( 'Next', 'john-platt' ) . '</span><span class="nav-title">%title</span>',
				)
			);
			if ( $article_navigation ) :
				?>
				<footer class="article-footer"><?php echo $article_navigation; // Native navigation markup. ?></footer>
			<?php endif; ?>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
