<?php
/**
 * Block field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Block field class.
 *
 * @class Block
 */
class Block extends Field {

	/**
	 * Field block.
	 *
	 * @var object
	 */
	protected $block;

	/**
	 * Sets field block.
	 *
	 * @param string $type Block type.
	 */
	final protected function set_block( $type ) {
		$this->block = hp\create_class_instance( '\HivePress\Blocks\\' . $type, [ $this->args ] );
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( $this->block ) {
			$output .= $this->block->render();
		}

		return $output;
	}
}
