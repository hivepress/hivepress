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

		foreach ( hp\sort_array( $blocks ) as $name => $args ) {

			// Create block.
			$block = hp\create_class_instance(
				'\HivePress\Blocks\\' . $args['type'],
				[
					hp\merge_arrays(
						[
							'context' => $this->context,
						],
						$args,
						[
							'name' => $name,
						]
					),
				]
			);

			if ( $block ) {
				$this->blocks[ $name ] = $block;
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
