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
	 * Return the output?
	 *
	 * @var bool
	 */
	protected $return = false;

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( function_exists( $this->callback ) ) {
			if ( $this->return ) {
				$output = call_user_func_array( $this->callback, $this->params );
			} else {
				ob_start();

				call_user_func_array( $this->callback, $this->params );
				$output = ob_get_contents();

				ob_end_clean();
			}
		}

		return $output;
	}
}
