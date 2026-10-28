<?php
/**
 * Optional native Project metadata and external link.
 * @package John_Platt
 */
defined( 'ABSPATH' ) || exit;
$project_details = array(
	'client' => __( 'Client', 'john-platt' ),
	'year' => __( 'Year', 'john-platt' ),
	'role' => __( 'Role', 'john-platt' ),
);
$project_values = array();
foreach ( $project_details as $key => $label ) {
	$value = john_platt_project_field( $key );
	if ( '' !== $value ) {
		$project_values[ $key ] = $value;
	}
}
if ( $project_values && ( $args['show_metadata'] ?? true ) ) :
	?>
	<dl class="project-details">
		<?php foreach ( $project_values as $key => $value ) : ?>
			<div><dt><?php echo esc_html( $project_details[ $key ] ); ?></dt><dd><?php echo esc_html( $value ); ?></dd></div>
		<?php endforeach; ?>
	</dl>
<?php endif; ?>
<?php
$project_cta_label = john_platt_project_field( 'cta_label' );
$project_cta_url = esc_url( john_platt_project_field( 'cta_url' ), array( 'http', 'https' ) );
if ( $project_cta_label && $project_cta_url && ( $args['show_cta'] ?? true ) ) :
	?>
	<p class="project-external"><a href="<?php echo esc_url( $project_cta_url ); ?>"><?php echo esc_html( $project_cta_label ); ?></a></p>
<?php endif; ?>
