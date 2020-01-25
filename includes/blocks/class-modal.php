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
	protected $title;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Add title.
		if ( $this->title ) {
			array_unshift(
				$this->blocks,
				new Part(
					[
						'path'    => 'page/modal-title',
						'context' => [ 'modal_title' => $this->title ],
					]
				)
			);
		}

		// Get ID.
		$id = $this->name;

		if ( $this->model ) {
			$object = $this->get_context( $this->model );

			if ( hp\is_class_instance( $object, '\HivePress\Models\\' . $this->model ) ) {
				$id .= '_' . $object->get_id();
			}
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

		parent::boot();
	}
}
