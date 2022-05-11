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
				'description' => esc_html__( 'If you have a moment, please help us improve by sharing the deactivation reason. Select "Other" to proceed without sharing.', 'hivepress' ),
				'action'      => hivepress()->router->get_url( 'plugin_deactivate_action' ),
				'redirect'    => true,

				'fields'      => [
					'reason' => [
						'type'    => 'radio',
						'_order'  => 10,

						'options' => [
							'features'  => esc_html__( 'It lacks the required features', 'hivepress' ),
							'themes'    => esc_html__( 'The layout is too basic or broken', 'hivepress' ),
							'elementor' => esc_html__( 'Poor integration with Elementor', 'hivepress' ),
							'plugins'   => esc_html__( 'It conflicts with other plugins', 'hivepress' ),
							'docs'      => esc_html__( 'The docs are not helpful', 'hivepress' ),
							''          => esc_html__( 'Other', 'hivepress' ),
						],
					],
				],

				'button'      => [
					'label' => esc_html__( 'Submit & Deactivate', 'hivepress' ),
				],

				'footer'      => '<small>' . esc_html__( 'We don\'t collect any info about your website.', 'hivepress' ) . '</small>',
			],
			$args
		);

		parent::__construct( $args );
	}
}
