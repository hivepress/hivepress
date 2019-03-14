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

			// todo.
			$attributes = $this->attributes;
			unset( $attributes['tag'] );
			unset( $attributes['attributes'] );

			// Create block.
			if ( class_exists( $block_class ) ) {
				$this->blocks[ $block_name ] = new $block_class( hp\merge_arrays( [ 'attributes' => $attributes ], $block_args, [ 'name' => $block_name ] ) );
			}
		}
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function bootstrap() {
		$attributes = [];

		// Set container tag.
		if ( ! isset( $this->attributes['tag'] ) ) {
			$this->attributes['tag'] = 'div';
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<' . esc_attr( $this->get_attribute( 'tag' ) ) . ' ' . hp\html_attributes( $this->get_attribute( 'attributes' ) ) . '>';

		// Render inner blocks.
		foreach ( $this->blocks as $block ) {
			$output .= $block->render();
		}

		$output .= '</' . esc_attr( $this->get_attribute( 'tag' ) ) . '>';

		return $output;
	}
}
