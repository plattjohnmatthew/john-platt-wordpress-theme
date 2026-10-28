<?php
/**
 * Homepage editorial fields, using ACF Free.
 *
 * @package John_Platt
 */

defined( 'ABSPATH' ) || exit;

/** Register fields only after ACF has initialized. */
function john_platt_register_homepage_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	$sections = array(
		'intro'       => __( 'Intro', 'john-platt' ),
		'work'        => __( 'Work', 'john-platt' ),
		'writing'     => __( 'Writing', 'john-platt' ),
		'experiments' => __( 'Experiments', 'john-platt' ),
		'contact'     => __( 'Contact', 'john-platt' ),
	);
	$fields = array();

	foreach ( $sections as $section => $label ) {
		$fields[] = array(
			'key'   => 'field_john_platt_' . $section . '_tab',
			'label' => $label,
			'type'  => 'tab',
		);

		$section_fields = array(
			'label'    => array( 'label' => __( 'Label', 'john-platt' ), 'type' => 'text' ),
			'headline' => array( 'label' => __( 'Headline', 'john-platt' ), 'type' => 'text' ),
			'body'     => array(
				'label'     => __( 'Body', 'john-platt' ),
				'type'      => 'textarea',
				'rows'      => 5,
				'new_lines' => '',
			),
		);

		if ( 'intro' === $section ) {
			$section_fields['image'] = array(
				'label'         => __( 'Opening portrait / image', 'john-platt' ),
				'type'          => 'image',
				'return_format' => 'id',
				'preview_size'  => 'medium',
				'library'       => 'all',
				'instructions'  => __( 'Optional. Independent of the Work image. Leave empty for the designed portrait placeholder. Set alternative text in the Media Library.', 'john-platt' ),
			);
			$section_fields['supporting_body'] = array(
				'label'     => __( 'Supporting introduction copy', 'john-platt' ),
				'type'      => 'textarea',
				'rows'      => 4,
				'new_lines' => '',
			);
			$section_fields['headline'] = array(
				'label'        => __( 'Headline', 'john-platt' ),
				'type'         => 'textarea',
				'rows'         => 4,
				'new_lines'    => '',
				'instructions' => __( 'Use a new line wherever you want an intentional headline line break.', 'john-platt' ),
			);
		}

		if ( in_array( $section, array( 'work', 'writing', 'experiments' ), true ) ) {
			$section_fields['grid_statement'] = array(
				'label'        => __( 'Opening grid statement', 'john-platt' ),
				'type'         => 'text',
				'instructions' => __( 'Short statement in the opening grid, separate from this section label.', 'john-platt' ),
			);
			$section_fields['grid_symbol_image'] = array(
				'label'         => __( 'Opening grid symbol image', 'john-platt' ),
				'type'          => 'image',
				'return_format' => 'id',
				'preview_size'  => 'medium',
				'library'       => 'all',
				'instructions'  => __( 'Optional decorative image beside this opening grid statement. Replaces the × symbol. Leave empty to keep ×. Independent of the section image below.', 'john-platt' ),
			);
			$section_fields['cta_label'] = array(
				'label'        => __( 'CTA label', 'john-platt' ),
				'type'         => 'text',
				'instructions' => __( 'Optional. Supply both a label and URL to use a CTA.', 'john-platt' ),
			);
			$section_fields['cta_url'] = array(
				'label' => __( 'CTA URL', 'john-platt' ),
				'type'  => 'url',
			);
			$section_fields['image'] = array(
				'label'         => __( 'Image', 'john-platt' ),
				'type'          => 'image',
				'return_format' => 'id',
				'preview_size'  => 'medium',
				'library'       => 'all',
				'instructions'  => __( 'Optional. The section must work without an image. Set alternative text in the Media Library.', 'john-platt' ),
			);
		}

		if ( 'contact' === $section ) {
			$section_fields['email'] = array(
				'label' => __( 'Email', 'john-platt' ),
				'type'  => 'email',
			);
			$section_fields['linkedin_url'] = array(
				'label' => __( 'LinkedIn URL', 'john-platt' ),
				'type'  => 'url',
			);
		}

		foreach ( $section_fields as $suffix => $settings ) {
			$name     = $section . '_' . $suffix;
			$fields[] = array_merge(
				array(
					'key'      => 'field_john_platt_' . $name,
					'name'     => $name,
					'required' => 0,
				),
				$settings
			);
		}
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_john_platt_homepage_content',
			'title'    => __( 'Homepage Content', 'john-platt' ),
			'fields'   => $fields,
			'location' => array(
				array(
					array(
						'param'    => 'page_type',
						'operator' => '==',
						'value'    => 'front_page',
					),
				),
			),
			'position' => 'normal',
			'style'    => 'default',
			'active'   => true,
		)
	);
}
add_action( 'acf/init', 'john_platt_register_homepage_fields' );
