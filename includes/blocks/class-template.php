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
	 * Block type.
	 *
	 * @var string
	 */
	protected static $type;

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

		// Get template class.
		$template_class = 'HivePress\Templates\\' . $this->template;

		if ( class_exists( $template_class ) ) {

			// Get blocks.
			$blocks = $template_class::get_blocks();

			if ( ! empty( $this->blocks ) ) {
				$blocks = hp\merge_trees( [ 'blocks' => $blocks ], [ 'blocks' => $this->blocks ], 'blocks' );
				$blocks = reset( $blocks );
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
