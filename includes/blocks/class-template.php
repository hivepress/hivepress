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

		// Create template.
		$template = hp\create_class_instance(
			'\HivePress\Templates\\' . $this->template,
			[
				[
					'context' => $this->context,
					'blocks'  => $this->blocks,
				],
			]
		);

		if ( $template ) {

			// Render template.
			$output .= ( new Container(
				[
					'tag'     => false,
					'context' => $template->get_context(),
					'blocks'  => $template->get_blocks(),
				]
			) )->render();
		}

		return $output;
	}
}
