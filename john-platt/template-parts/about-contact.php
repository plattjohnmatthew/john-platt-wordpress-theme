<?php
/** Existing Contact editor content beneath the About biography. @package John_Platt */
defined( 'ABSPATH' ) || exit;
$contact_page = john_platt_contact_page();
if ( ! $contact_page || post_password_required() ) {
	return;
}
$details = john_platt_contact_details();
global $post;
$post = $contact_page;
setup_postdata( $post );
?>
<section id="contact" class="about-contact contact-layout" aria-labelledby="about-contact-label">
	<header class="contact-sidebar"><p id="about-contact-label" class="about-contact__label"><?php echo esc_html( get_the_title() ); ?></p></header>
	<div class="article-body contact-content">
		<?php the_content(); ?>
		<?php if ( ! post_password_required() ) : ?>
			<?php if ( $details['email'] || $details['linkedin'] ) : ?>
				<div class="contact-links">
					<?php if ( $details['email'] ) : ?><p><a href="<?php echo esc_url( 'mailto:' . $details['email'] ); ?>"><?php echo esc_html( $details['email'] ); ?></a></p><?php endif; ?>
					<?php if ( $details['linkedin'] ) : ?><p><a href="<?php echo esc_url( $details['linkedin'] ); ?>"><?php esc_html_e( 'LinkedIn', 'john-platt' ); ?></a></p><?php endif; ?>
				</div>
			<?php endif; ?>
		<?php endif; ?>
	</div>
</section>
<?php wp_reset_postdata(); ?>
