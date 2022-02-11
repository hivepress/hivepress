<?php
/**
 * Callback block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders the callback result.
 */
class Callback extends Block {

	/**
	 * Callback name.
	 *
	 * @var string
	 */
	protected $callback;

	/**
	 * Callback parameters.
	 *
	 * @var array
	 */
	protected $params = [];

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( function_exists( $this->callback ) ) {
			ob_start();

			call_user_func_array( $this->callback, $this->params );
			$output .= ob_get_contents();

			ob_end_clean();
		}

		return $output;
	}
}
