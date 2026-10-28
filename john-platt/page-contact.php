<?php
/**
 * Template Name: Contact
 * Native introduction with shared homepage contact details.
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
$details = john_platt_contact_details();
$email = $details['email'];
$linkedin = $details['linkedin'];
$cta_url = get_post_meta( get_queried_object_id(), 'contact_cta_url', true );
$cta_url = is_string( $cta_url ) && '' !== trim( $cta_url ) ? $cta_url : ( $email ? 'mailto:' . $email : '' );
$cta_image = (int) get_post_meta( get_queried_object_id(), 'contact_cta_image', true );
if ( ! $cta_image ) {
	$homepage_id = (int) get_option( 'page_on_front' );
	$cta_image = (int) get_post_meta( $homepage_id, 'work_grid_symbol_image', true );
	if ( ! $cta_image ) {
		$cta_image = (int) get_post_meta( $homepage_id, 'work_image', true );
	}
}
get_header();
?>
<main id="main" class="contact-page" tabindex="-1">
	<?php while ( have_posts() ) : the_post(); ?>
		<article <?php post_class( 'contact-layout' ); ?>>
			<header class="contact-sidebar"><h1 class="surface-title"><?php echo esc_html( get_the_title() ); ?></h1></header>
			<div class="article-body contact-content">
				<?php the_content(); ?>
				<?php if ( ! post_password_required() && esc_url( $cta_url ) ) : ?>
					<p class="contact-cta">
						<a href="<?php echo esc_url( $cta_url ); ?>">
							<?php if ( $cta_image ) : ?>
								<?php echo wp_get_attachment_image( $cta_image, 'thumbnail', false, array( 'class' => 'contact-cta__image', 'alt' => '', 'aria-hidden' => 'true' ) ); ?>
							<?php endif; ?>
							<span><?php esc_html_e( 'GET IN TOUCH', 'john-platt' ); ?></span>
						</a>
					</p>
				<?php endif; ?>
				<?php wp_link_pages( array( 'before' => '<nav aria-label="' . esc_attr__( 'Page sections', 'john-platt' ) . '">', 'after' => '</nav>' ) ); ?>
				<?php if ( ! post_password_required() && ( $email || $linkedin ) ) : ?>
					<div class="contact-links">
						<?php if ( $email ) : ?><p><a href="<?php echo esc_url( 'mailto:' . $email ); ?>"><?php echo esc_html( $email ); ?></a></p><?php endif; ?>
						<?php if ( $linkedin ) : ?><p><a href="<?php echo esc_url( $linkedin ); ?>"><?php esc_html_e( 'LinkedIn', 'john-platt' ); ?></a></p><?php endif; ?>
					</div>
				<?php endif; ?>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
