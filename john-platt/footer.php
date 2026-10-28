<?php
/**
 * Minimal site-wide footer.
 *
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
?>
<footer class="site-footer">
	<p class="site-footer__copyright">&copy; <?php echo esc_html( wp_date( 'Y' ) ); ?> <?php echo esc_html( get_bloginfo( 'name' ) ); ?></p>
	<?php $footer_contact = john_platt_contact_details(); ?>
	<p class="site-footer__inquiries"><?php if ( $footer_contact['email'] ) : ?><?php esc_html_e( 'Inquiries:', 'john-platt' ); ?> <a href="<?php echo esc_url( 'mailto:' . $footer_contact['email'] ); ?>"><?php echo esc_html( $footer_contact['email'] ); ?></a><?php endif; ?></p>
	<?php $index_url = john_platt_page_url( 'index' ); ?>
	<?php if ( $index_url ) : ?><a class="site-footer__index" href="<?php echo esc_url( $index_url ); ?>"><?php esc_html_e( 'Index', 'john-platt' ); ?></a><?php endif; ?>
</footer>
<?php wp_footer(); ?>
</body>
</html>
