<?php
/**
 * Container block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Container block class.
 *
 * @class Container
 */
class Container extends Block {

	/**
	 * Container tag.
	 *
	 * @var string
	 */
	protected $tag = 'div';

	/**
	 * Container attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Inner blocks.
	 *
	 * @var array
	 */
	protected $blocks = [];

	/**
	 * Sets inner blocks.
	 *
	 * @param array $blocks Inner blocks.
	 */
	final protected function set_blocks( $blocks ) {
		$this->blocks = [];

		foreach ( hp\sort_array( $blocks ) as $block_name => $block_args ) {

			// Get block class.
			$block_class = '\HivePress\Blocks\\' . $block_args['type'];

			// Create block.
			if ( class_exists( $block_class ) ) {
				$this->blocks[ $block_name ] = new $block_class( hp\merge_arrays( [ 'context' => $this->context ], $block_args, [ 'name' => $block_name ] ) );
			}
		}
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( $this->tag ) {
			$output .= '<' . esc_attr( $this->tag ) . ' ' . hp\html_attributes( $this->attributes ) . '>';
		}

		// Render inner blocks.
		foreach ( $this->blocks as $block ) {
			$output .= $block->render();
		}

		if ( $this->tag ) {
			$output .= '</' . esc_attr( $this->tag ) . '>';
		}

		return $output;
	}
}
