<?php
/**
 * Plugin deactivate form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Deactivates HivePress plugin.
 */
class Plugin_Deactivate extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'description' => esc_html__( 'Please help us improve by sharing the deactivation reason. Select "Other" to proceed without sharing.', 'hivepress' ),
				'action'      => hivepress()->router->get_url( 'plugin_deactivate_action' ),
				'redirect'    => true,

				'fields'      => [
					'reason'  => [
						'type'    => 'radio',
						'_order'  => 10,

						'options' => [
							'features' => esc_html__( 'It lacks the required features', 'hivepress' ),
							'themes'   => esc_html__( 'The layout is too basic or broken', 'hivepress' ),
							'builders' => esc_html__( 'Poor integration with page builders', 'hivepress' ),
							'plugins'  => esc_html__( 'It conflicts with other plugins', 'hivepress' ),
							'docs'     => esc_html__( 'The docs are not helpful', 'hivepress' ),
							''         => esc_html__( 'Other', 'hivepress' ),
						],
					],

					'details' => [
						'type'        => 'textarea',
						'placeholder' => esc_html__( 'Extra details, e.g. conflicting themes, plugins, missing features or docs.', 'hivepress' ),
						'max_length'  => 512,
						'_order'      => 20,

						// @todo Remove once parent fields are fully implemented.
						'attributes'  => [
							'data-component' => 'field',
							'data-parent'    => 'reason',
						],
					],
				],

				'button'      => [
					'label'      => esc_html__( 'Submit & Deactivate', 'hivepress' ),

					'attributes' => [
						'class' => [ 'button-large' ],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
