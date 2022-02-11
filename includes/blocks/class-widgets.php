<?php
/**
 * Widgets block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders a widget area.
 */
class Widgets extends Block {

	/**
	 * Widget area name.
	 *
	 * @var string
	 */
	protected $area;

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		ob_start();

		dynamic_sidebar( $this->area );
		$output .= ob_get_contents();

		ob_end_clean();

		return $output;
	}
}
