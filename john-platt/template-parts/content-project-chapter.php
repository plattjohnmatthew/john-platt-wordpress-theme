<?php
/**
 * Editable Project archive chapter; static layout is canonical.
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
$project_is_writing = 'post' === get_post_type();
$project_presentation_mode = $project_is_writing ? 'simple' : john_platt_project_field( 'presentation_mode' );
if ( ! in_array( $project_presentation_mode, array( 'immersive', 'editorial', 'simple' ), true ) ) {
	$project_presentation_mode = 'immersive';
}
$project_media = $project_is_writing ? array( 'video' => '', 'video_id' => 0, 'image' => get_post_thumbnail_id(), 'poster' => '', 'fit' => 'contain' ) : john_platt_project_media();
$project_layout = john_platt_project_media_layout( $project_media );
$project_has_media = (bool) ( $project_media['video'] || $project_media['image'] );
$project_is_immersive = 'immersive' === $project_presentation_mode && $project_has_media;
$project_narrative = ! post_password_required() && ! $project_is_writing ? john_platt_project_field( 'archive_narrative' ) : '';
if ( '' === $project_narrative ) {
	$project_narrative = wp_strip_all_tags( strip_shortcodes( get_the_excerpt() ), true );
}
?>
<article style="--project-media-share: <?php echo esc_attr( number_format( $project_layout['share'], 4, '.', '' ) ); ?>%; --project-media-ratio: <?php echo esc_attr( number_format( $project_layout['ratio'], 6, '.', '' ) ); ?>;"<?php if ( $project_is_immersive ) : ?> data-project-state="detail"<?php endif; ?> data-project-mode="<?php echo esc_attr( $project_presentation_mode ); ?>" data-video-fill-opening="<?php echo john_platt_project_video_fill_opening( get_the_ID() ) ? 'true' : 'false'; ?>" aria-labelledby="project-heading-<?php echo esc_attr( (string) get_the_ID() ); ?>" <?php post_class( 'project-chapter' . ( $project_has_media ? ' project-chapter--media' : '' ) . ( $project_is_immersive ? ' project-chapter--immersive' : '' ) ); ?>>
	<?php if ( $project_is_immersive ) : ?><div class="project-chapter__stage"><?php endif; ?>
	<div class="project-chapter__inner">
		<?php if ( 'editorial' !== $project_presentation_mode && ( $project_has_media || 'simple' !== $project_presentation_mode ) ) : ?>
			<div class="project-chapter__media"><?php get_template_part( 'template-parts/project', 'media', array( 'media' => $project_media ) ); ?></div>
		<?php endif; ?>
		<div class="project-chapter__content">
			<?php global $wp_query; ?>
			<p class="project-chapter__number"><?php echo esc_html( sprintf( '%02d / %s', isset( $args['number'] ) ? absint( $args['number'] ) : ( max( 1, (int) get_query_var( 'paged' ) ) - 1 ) * (int) get_query_var( 'posts_per_page' ) + (int) $wp_query->current_post + 1, ! empty( $args['portfolio'] ) && $project_is_writing ? __( 'WRITING', 'john-platt' ) : __( 'PROJECT', 'john-platt' ) ) ); ?></p>
			<h2 id="project-heading-<?php echo esc_attr( (string) get_the_ID() ); ?>" class="project-chapter__title"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2>
			<?php if ( ! post_password_required() && ! $project_is_writing ) : ?>
				<?php get_template_part( 'template-parts/project', 'details' ); ?>
			<?php endif; ?>
			<div class="project-chapter__narrative"><?php echo wp_kses_post( wpautop( esc_html( $project_narrative ) ) ); ?></div>
			<p class="project-chapter__cta"><a href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( $project_is_writing ? __( 'Read article', 'john-platt' ) : __( 'Read project', 'john-platt' ) ); ?></a></p>
		</div>
		<?php if ( 'editorial' === $project_presentation_mode ) : ?>
			<div class="project-chapter__media"><?php get_template_part( 'template-parts/project', 'media', array( 'media' => $project_media ) ); ?></div>
		<?php endif; ?>
	</div>
	<?php if ( $project_is_immersive ) : ?></div><?php endif; ?>
</article>
