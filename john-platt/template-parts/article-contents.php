<?php
/** Automatic H2/H3 navigation. @package John_Platt */
defined( 'ABSPATH' ) || exit;
if ( empty( $args['items'] ) ) {
	return;
}
?>
<nav class="article-contents" aria-labelledby="<?php echo esc_attr( $args['label_id'] ); ?>">
	<h2 id="<?php echo esc_attr( $args['label_id'] ); ?>" class="article-contents__title"><?php esc_html_e( 'In this article', 'john-platt' ); ?></h2>
	<ol>
		<?php foreach ( $args['items'] as $item ) : ?>
			<li>
				<a href="<?php echo esc_url( '#' . rawurlencode( $item['id'] ) ); ?>"><?php echo esc_html( $item['title'] ); ?></a>
				<?php if ( $item['children'] ) : ?>
					<ol>
						<?php foreach ( $item['children'] as $child ) : ?>
							<li><a href="<?php echo esc_url( '#' . rawurlencode( $child['id'] ) ); ?>"><?php echo esc_html( $child['title'] ); ?></a></li>
						<?php endforeach; ?>
					</ol>
				<?php endif; ?>
			</li>
		<?php endforeach; ?>
	</ol>
</nav>