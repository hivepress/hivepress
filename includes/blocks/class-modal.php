<?php
/**
 * Modal block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Modal block class.
 *
 * @class Modal
 */
class Modal extends Container {

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'blocks' => [
					'title' => [
						'type'       => 'element',
						'order'      => 5,

						'attributes' => [
							'file_path' => 'modal/title',
							'title'     => hp\get_array_value( $args, 'title' ),
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function bootstrap() {

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'attributes' => [
					'id'             => $this->name,
					'class'          => [ 'hp-modal' ],
					'data-component' => 'modal',
				],
			]
		);

		parent::bootstrap();
	}
}
