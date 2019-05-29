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
	 * Block type.
	 *
	 * @var string
	 */
	protected static $type;

	/**
	 * Modal title.
	 *
	 * @var string
	 */
	protected $modal_title;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Bootstraps block properties.
	 */
	protected function bootstrap() {

		// Get ID.
		$id = $this->name;

		if ( isset( $this->model ) ) {
			$id .= '_' . get_the_ID();
		}

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'id'             => $id,
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
					'context'   => array_merge( $this->context, [ 'title' => $this->modal_title ] ),
				]
			)
		);

		parent::bootstrap();
	}
}
