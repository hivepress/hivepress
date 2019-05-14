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
	 * Modal title.
	 *
	 * @var string
	 */
	protected $modal_title;

	/**
	 * Bootstraps block properties.
	 */
	protected function bootstrap() {

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'id'             => $this->name,
				'class'          => [ 'hp-modal' ],
				'data-component' => 'modal',
			]
		);

		// Add title.
		array_unshift(
			$this->blocks,
			new Element(
				[
					'type'      => 'element',
					'file_path' => 'modal/title',

					'context'   => [
						'title' => $this->modal_title,
					],
				]
			)
		);

		parent::bootstrap();
	}
}
