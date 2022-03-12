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
 * Renders a modal window.
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
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'optional' => true,
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		// Add title.
		if ( $this->title ) {
			$this->header['modal_title'] = [
				'type'    => 'part',
				'path'    => 'page/modal-title',
				'context' => [ 'modal_title' => $this->title ],
				'_order'  => 5,
			];
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

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = parent::render();

		if ( is_admin() ) {
			hivepress()->request->set_context( 'admin_footer', hivepress()->request->get_context( 'admin_footer' ) . $output );
		} else {
			return $output;
		}
	}
}
