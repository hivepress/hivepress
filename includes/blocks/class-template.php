<?php
/**
 * Template block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Template block class.
 *
 * @class Template
 */
class Template extends Block {

	/**
	 * Template name.
	 *
	 * @var string
	 */
	protected $template;

	/**
	 * Template blocks.
	 *
	 * @var array
	 */
	protected $blocks = [];

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get blocks.
		// todo.
		$template = hp\create_class_instance( '\HivePress\Templates\\' . $this->template );

		$blocks = $template->get_blocks();

		if ( ! is_null( $blocks ) ) {

			// Merge blocks.
			if ( ! empty( $this->blocks ) ) {
				$blocks = hp\merge_trees( [ 'blocks' => $blocks ], [ 'blocks' => $this->blocks ], 'blocks' )['blocks'];
			}

			// Render blocks.
			$output .= ( new Container(
				[
					'context' => $this->context,
					'tag'     => false,
					'blocks'  => $blocks,
				]
			) )->render();
		}

		return $output;
	}
}
