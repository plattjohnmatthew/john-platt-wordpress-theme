<?php
/**
 * Native Post archive row.
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
$writing_row_excerpt = trim( wp_strip_all_tags( strip_shortcodes( get_the_excerpt() ), true ) );
$writing_row_image = get_the_post_thumbnail(
	null,
	'large',
	array(
		'class' => 'writing-row__image',
		'loading' => 'lazy',
		'sizes' => '(min-width: 64rem) 40vw, (min-width: 48rem) 35vw, 100vw',
	)
);
?>
<article <?php post_class( 'writing-row' ); ?>>
	<div class="writing-row__content">
		<p class="writing-row__meta"><time datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date() ); ?></time></p>
		<h2 class="writing-row__title"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>
		<?php if ( '' !== $writing_row_excerpt ) : ?>
			<p class="writing-row__excerpt"><?php echo esc_html( $writing_row_excerpt ); ?></p>
		<?php endif; ?>
	</div>
	<div class="writing-row__media">
		<div class="writing-row__visual"<?php echo $writing_row_image ? '' : ' aria-hidden="true"'; ?>>
			<?php echo $writing_row_image; // WordPress attachment markup preserves alt text and responsive attributes. ?>
		</div>
	</div>
</article>
