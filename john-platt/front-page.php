<?php
/**
 * Editable homepage with development-only fallback copy.
 *
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;

$development_fallbacks = require get_template_directory() . '/inc/homepage-fallbacks.php';
$homepage_id = (int) get_option( 'page_on_front' );
$read_field  = static function ( $name ) use ( $homepage_id, $development_fallbacks ) {
	$value = $homepage_id && function_exists( 'get_field' ) ? get_field( $name, $homepage_id ) : '';
	return is_scalar( $value ) && '' !== trim( (string) $value ) ? (string) $value : ( $development_fallbacks[ $name ] ?? '' );
};

$intro_label    = $read_field( 'intro_label' );
$intro_headline = $read_field( 'intro_headline' );
$intro_body     = $read_field( 'intro_body' );
$chapters       = array( 'work', 'writing', 'experiments' );
$ai_topic = get_term_by( 'name', 'AI', 'writing_topic' );
if ( ! $ai_topic ) {
	$ai_topic = get_term_by( 'slug', 'ai', 'writing_topic' );
}
$grid_links = array(
	'work'        => get_post_type_archive_link( 'project' ),
	'writing'     => john_platt_writing_topic_url( array() ),
	'experiments' => john_platt_writing_topic_url( array(), $ai_topic ? $ai_topic->slug : 'ai' ),
);

get_header();
?>
<main id="main" class="home-page" tabindex="-1">
	<section id="intro" class="editorial-grid" data-header-expanded aria-labelledby="intro-heading">
		<div class="editorial-grid__cell editorial-grid__identity">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo esc_html( $intro_label ); ?></a>
		</div>
		<div class="editorial-grid__cell editorial-grid__intro">
			<?php echo wp_kses_post( wpautop( esc_html( $intro_body ) ) ); ?>
		</div>
		<nav id="primary-navigation" class="editorial-grid__cell editorial-grid__navigation" data-home-navigation data-home-nav-origin aria-label="<?php esc_attr_e( 'Primary', 'john-platt' ); ?>">
			<?php wp_nav_menu( array( 'theme_location' => 'primary', 'container' => false, 'fallback_cb' => false, 'menu_class' => 'editorial-grid__menu', 'depth' => 1, 'items_wrap' => '<ul id="%1$s" class="%2$s" role="list">%3$s</ul>' ) ); ?>
		</nav>
		<div class="editorial-grid__cell editorial-grid__portrait" data-portrait-origin>
			<?php
			$portrait_id = john_platt_site_portrait_id();
			$portrait    = $portrait_id ? wp_get_attachment_image( $portrait_id, 'large', false, array(
				'sizes'         => '(min-width: 64rem) 28vw, (min-width: 48rem) 50vw, 100vw',
				'loading'       => 'eager',
				'fetchpriority' => 'high',
			) ) : '';
			if ( $portrait ) {
				echo '<a class="home-portrait-link" href="' . esc_url( home_url( '/' ) ) . '" aria-label="' . esc_attr( sprintf( __( '%s — Home', 'john-platt' ), get_bloginfo( 'name' ) ) ) . '">' . $portrait . '</a>'; // One responsive portrait remains the transition source.
			} else {
				echo '<p class="editorial-grid__placeholder">' . esc_html__( 'PORTRAIT / IMAGE', 'john-platt' ) . '</p>';
			}
			?>
		</div>
		<div class="editorial-grid__cell editorial-grid__statement">
			<h1 id="intro-heading"><?php echo nl2br( esc_html( $intro_headline ) ); ?></h1>
		</div>
		<div class="editorial-grid__cell editorial-grid__support">
			<?php echo wp_kses_post( wpautop( esc_html( $read_field( 'intro_supporting_body' ) ) ) ); ?>
		</div>
		<?php foreach ( $chapters as $chapter ) : ?>
			<div class="editorial-grid__cell editorial-grid__discipline">
				<?php
				$symbol_id = absint( $read_field( $chapter . '_grid_symbol_image' ) );
				$symbol = $symbol_id && wp_attachment_is_image( $symbol_id ) ? wp_get_attachment_image(
					$symbol_id,
					'medium',
					false,
					array( 'class' => 'editorial-grid__symbol', 'alt' => '', 'loading' => 'lazy' )
				) : '';
				?>
				<p>
					<?php if ( ! empty( $grid_links[ $chapter ] ) ) : ?><a class="editorial-grid__statement-link" href="<?php echo esc_url( $grid_links[ $chapter ] ); ?>"><?php endif; ?>
					<?php if ( $symbol ) : ?><?php echo $symbol; // WordPress image markup; decorative replacement for ×. ?><?php else : ?><span aria-hidden="true">× </span><?php endif; ?>
					<span><?php echo esc_html( $read_field( $chapter . '_grid_statement' ) ); ?></span>
					<?php if ( ! empty( $grid_links[ $chapter ] ) ) : ?></a><?php endif; ?>
				</p>
			</div>
		<?php endforeach; ?>
	</section>
	<?php foreach ( $chapters as $chapter_index => $chapter ) : ?>
		<?php
		$label     = $read_field( $chapter . '_label' );
		$headline  = $read_field( $chapter . '_headline' );
		$body      = $read_field( $chapter . '_body' );
		$cta_label = $read_field( $chapter . '_cta_label' );
		$cta_url   = esc_url( $read_field( $chapter . '_cta_url' ) );
		if ( ! $cta_url ) {
			$cta_url = $grid_links[ $chapter ];
		}
		if ( 'experiments' === $chapter ) {
			$cta_label = $cta_label ?: __( 'VIEW EXPERIMENTS', 'john-platt' );
		}
		$image_id  = absint( $read_field( $chapter . '_image' ) );
		$media_width = array( 'work' => '47vw', 'writing' => '20vw', 'experiments' => '40vw' );
		$image     = $image_id ? wp_get_attachment_image( $image_id, 'large', false, array(
			'loading' => 'lazy',
			'sizes'   => '(min-width: 48rem) ' . $media_width[ $chapter ] . ', 28vw',
		) ) : '';
		?>
		<section id="<?php echo esc_attr( $chapter ); ?>" class="chapter chapter--<?php echo esc_attr( $chapter ); ?>" aria-labelledby="<?php echo esc_attr( $chapter . '-heading' ); ?>">
			<div class="chapter__inner">
				<p class="chapter__meta"><?php echo esc_html( $label ); ?></p>
				<div class="chapter__content">
					<h2 id="<?php echo esc_attr( $chapter . '-heading' ); ?>"><?php echo nl2br( esc_html( $headline ) ); ?></h2>
					<div class="chapter__body"><?php echo wp_kses_post( wpautop( esc_html( $body ) ) ); ?></div>
					<?php if ( $cta_label && $cta_url ) : ?>
						<p class="chapter__cta"><a href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $cta_label ); ?></a></p>
					<?php endif; ?>
				</div>
				<div class="chapter__media">
					<?php if ( $image ) : ?>
						<?php echo $image; // WordPress attachment markup includes alt text and intrinsic dimensions. ?>
					<?php else : ?>
						<span class="chapter__graphic" aria-hidden="true"><?php echo esc_html( sprintf( '%02d', $chapter_index + 1 ) ); ?></span>
					<?php endif; ?>
				</div>
			</div>
		</section>
	<?php endforeach; ?>
</main>
<?php get_footer(); ?>
