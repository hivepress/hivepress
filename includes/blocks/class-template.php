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
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get template class.
		$template_class = 'HivePress\Templates\\' . $this->template;

		if ( class_exists( $template_class ) ) {

			// Render blocks.
			$output .= ( new Container(
				[
					'context' => $this->context,
					'tag'     => false,
					'blocks'  => $template_class::get_blocks(),
				]
			) )->render();
		}

		return $output;
	}
}
