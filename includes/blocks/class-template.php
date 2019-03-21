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

			// Merge blocks.
			if ( isset( $template_args['parent'] ) ) {
				$parent_args = hivepress()->get_config( 'templates/' . $template_args['parent'] );

				if ( ! is_null( $parent_args ) ) {
					$template_args['blocks'] = $this->merge_blocks( $parent_args['blocks'], $template_args['blocks'] );
				}
			}

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

	/**
	 * Merges blocks.
	 *
	 * @param array $parent_blocks Parent blocks.
	 * @param array $child_blocks Child blocks.
	 * @return array
	 */
	private function merge_blocks( $parent_blocks, $child_blocks ) {
		foreach ( $parent_blocks as $block_name => $parent_block ) {
			$child_block = hp\get_array_value( $child_blocks, $block_name );

			if ( isset( $parent_block['blocks'] ) ) {
				$parent_blocks[ $block_name ]['blocks'] = $this->merge_blocks( $parent_block['blocks'], $child_blocks );
			}

			if ( ! is_null( $child_block ) ) {
				$parent_blocks[ $block_name ] = hp\merge_arrays( $parent_block, $child_block );
			}
		}

		return $parent_blocks;
	}
}
