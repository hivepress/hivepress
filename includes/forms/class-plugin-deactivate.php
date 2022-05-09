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
 * Plugin deactivate form class.
 *
 * @class Plugin_Deactivate
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
				'description' => esc_html__( 'Please share why you want to deactivate plugin', 'hivepress' ),
				'action'      => hivepress()->router->get_url( 'plugin_deactivate_action' ),
				'redirect'    => true,

				'fields'      => [
					'options' => [
						'type'     => 'radio',
						'options'  => [
							'I could not get the plugin to work' => esc_html__( 'I could not get the plugin to work.', 'hivepress' ),
							'It is a temporary deactivation. I am just debugging an issue.' => esc_html__( 'It is a temporary deactivation. I am just debugging an issue.', 'hivepress' ),
							'I no longer need the plugin' => esc_html__( 'I no longer need the plugin.', 'hivepress' ),
							'I found a better plugin'     => esc_html__( 'I found a better plugin.', 'hivepress' ),
						],
						'required' => true,
						'_order'   => 10,
					],
				],

				'button'      => [
					'label' => esc_html__( 'Submit & Deactivate', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
