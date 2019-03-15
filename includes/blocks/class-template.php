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
	protected $template_name;

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		// Get template.
		$template_args = hivepress()->get_config( 'templates/' . $this->template_name );

		if ( ! is_null( $template_args ) ) {
			foreach ( hp\sort_array( $template_args['blocks'] ) as $block_name => $block_args ) {

				// Get block class.
				$block_class = '\HivePress\Blocks\\' . $block_args['type'];

				// Render block.
				if ( class_exists( $block_class ) ) {
					$output .= ( new $block_class( hp\merge_arrays( $this->context, $block_args, [ 'name' => $block_name ] ) ) )->render();
				}
			}
		}

		return $output;
	}
}
