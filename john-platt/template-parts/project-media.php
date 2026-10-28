<?php
/**
 * Shared optional Project media with native playback controls.
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
if ( post_password_required() ) {
	return;
}
$project_media = $args['media'] ?? john_platt_project_media();
$project_video_id = $project_media['video_id'];
$project_video_url = $project_media['video'];
$project_image = $project_media['image'] ? wp_get_attachment_image( $project_media['image'], 'large', false, array( 'class' => 'project-media__image project-media--' . $project_media['fit'], 'loading' => 'lazy', 'sizes' => '100vw' ) ) : '';
$project_poster = $project_media['poster'];
if ( $project_video_url ) :
	$project_video_meta = wp_get_attachment_metadata( $project_video_id );
	?>
	<div class="project-media__playback">
	<video id="project-video-<?php echo esc_attr( (string) get_the_ID() ); ?>" class="project-media__video project-media--<?php echo esc_attr( $project_media['fit'] ); ?>" controls muted loop playsinline preload="none" aria-label="<?php echo esc_attr( sprintf( __( 'Project video: %s', 'john-platt' ), get_the_title() ) ); ?>"<?php if ( $project_poster ) : ?> poster="<?php echo esc_url( $project_poster ); ?>"<?php endif; ?><?php if ( ! empty( $project_video_meta['width'] ) && ! empty( $project_video_meta['height'] ) ) : ?> width="<?php echo esc_attr( absint( $project_video_meta['width'] ) ); ?>" height="<?php echo esc_attr( absint( $project_video_meta['height'] ) ); ?>" style="--project-video-ratio: <?php echo esc_attr( absint( $project_video_meta['width'] ) . ' / ' . absint( $project_video_meta['height'] ) ); ?>;"<?php endif; ?>>
		<source src="<?php echo esc_url( $project_video_url ); ?>" type="video/mp4">
		<?php echo $project_image; // Native responsive attachment markup. ?>
		<a href="<?php echo esc_url( $project_video_url ); ?>"><?php esc_html_e( 'Open project video', 'john-platt' ); ?></a>
	</video>
	<div class="project-media__controls">
	<button type="button" class="project-media__toggle" aria-controls="project-video-<?php echo esc_attr( (string) get_the_ID() ); ?>" hidden data-pause-label="<?php esc_attr_e( 'Pause video', 'john-platt' ); ?>" data-play-label="<?php esc_attr_e( 'Play video', 'john-platt' ); ?>"><?php esc_html_e( 'Pause video', 'john-platt' ); ?></button>
	<?php if ( is_post_type_archive( 'project' ) ) : ?>
		<button type="button" class="project-media__sound" aria-controls="project-video-<?php echo esc_attr( (string) get_the_ID() ); ?>" aria-label="<?php esc_attr_e( 'Unmute video', 'john-platt' ); ?>" title="<?php esc_attr_e( 'Unmute video', 'john-platt' ); ?>" hidden data-muted="true" data-mute-label="<?php esc_attr_e( 'Mute video', 'john-platt' ); ?>" data-unmute-label="<?php esc_attr_e( 'Unmute video', 'john-platt' ); ?>">
			<svg viewBox="0 0 24 24" width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">
				<path d="M11 5 6 9H3v6h3l5 4Z"/>
				<path class="project-media__sound-off" d="m16 9 6 6m0-6-6 6"/>
				<g class="project-media__sound-on"><path d="M15 8a6 6 0 0 1 0 8m3-11a10 10 0 0 1 0 14"/></g>
			</svg>
		</button>
	<?php endif; ?>
	</div>
	<div class="project-media__failure" hidden>
		<?php if ( $project_image ) : ?>
			<?php echo $project_image; // Native attachment markup. ?>
		<?php elseif ( $project_poster ) : ?>
			<?php echo wp_get_attachment_image( $project_media['poster_id'], 'large', false, array( 'class' => 'project-media__image project-media--' . $project_media['fit'] ) ); // Preserve Media Library alt text and dimensions. ?>
		<?php else : ?>
			<p><a href="<?php echo esc_url( $project_video_url ); ?>"><?php esc_html_e( 'Open project video', 'john-platt' ); ?></a></p>
		<?php endif; ?>
	</div>
	</div>
	<?php if ( $project_image ) : ?>
		<details class="project-media__fallback"><summary><?php esc_html_e( 'View still image', 'john-platt' ); ?></summary><?php echo $project_image; ?></details>
	<?php endif; ?>
<?php elseif ( $project_image ) : ?>
	<?php echo $project_image; // Native responsive attachment markup. ?>
<?php else : ?>
	<div class="project-media__empty" aria-hidden="true"><span>×</span></div>
<?php endif; ?>
