<?php
/**
 * Container block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Container block class.
 *
 * @class Container
 */
class Container extends Block {

	/**
	 * Inner blocks.
	 *
	 * @var array
	 */
	protected $blocks = [];

	/**
	 * Sets inner blocks.
	 *
	 * @param mixed $blocks Inner blocks.
	 */
	final protected function set_blocks( $blocks ) {
		$this->blocks = [];

		foreach ( $blocks as $block_name => $block_args ) {

			// Get block class.
			$block_class = '\HivePress\Blocks\\' . $block_args['type'];

			// Create block.
			$this->blocks[ $block_name ] = new $block_class( $block_args );
		}
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<' . esc_attr( $this->get_attribute( 'tag' ) ) . ' ' . hp_html_attributes( $this->get_attribute( 'attributes' ) ) . '>';

		// Render inner blocks.
		foreach ( $this->blocks as $block ) {
			$output .= $block->render();
		}

		$output .= '</' . esc_attr( $this->get_attribute( 'tag' ) ) . '>';

		return $output;
	}
}
