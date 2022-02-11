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
 * Wraps and renders other blocks.
 */
class Container extends Block {

	/**
	 * Container tag.
	 *
	 * @var string
	 */
	protected $tag = 'div';

	/**
	 * Optional flag.
	 *
	 * @var bool
	 */
	protected $optional = false;

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
	 * Container header.
	 *
	 * @var array
	 */
	protected $header = [];

	/**
	 * Container footer.
	 *
	 * @var array
	 */
	protected $footer = [];

	/**
	 * Sets inner blocks.
	 *
	 * @param array $blocks Inner blocks.
	 */
	final protected function set_blocks( $blocks ) {
		$this->blocks = [];

		foreach ( hp\sort_array( $blocks ) as $name => $args ) {
			if ( ! isset( $args['_capability'] ) || ( is_user_logged_in() && current_user_can( $args['_capability'] ) ) || ( ! is_user_logged_in() && 'login' === $args['_capability'] ) ) {

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
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Render blocks.
		foreach ( $this->blocks as $block ) {
			$output .= $block->render();
		}

		if ( ! $this->optional || '' !== $output ) {

			// Render header.
			if ( $this->header ) {
				$output = ( new Container(
					[
						'context' => $this->context,
						'tag'     => false,
						'blocks'  => $this->header,
					]
				) )->render() . $output;
			}

			// Render footer.
			if ( $this->footer ) {
				$output = $output . ( new Container(
					[
						'context' => $this->context,
						'tag'     => false,
						'blocks'  => $this->footer,
					]
				) )->render();
			}

			// Add wrapper.
			if ( $this->tag ) {
				$output = '<' . esc_attr( $this->tag ) . ' ' . hp\html_attributes( $this->attributes ) . '>' . $output . '</' . esc_attr( $this->tag ) . '>';
			}
		}

		return $output;
	}
}
