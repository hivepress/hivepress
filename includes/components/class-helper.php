<?php
/**
 * Helper component.
 *
 * @package HivePress\Components
 */

namespace HivePress\Components;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Implements a facade for HivePress helpers.
 */
final class Helper extends Component {

	/**
	 * Catches calls to undefined methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @throws \BadMethodCallException Invalid method.
	 * @return mixed
	 */
	public function __call( $name, $args ) {
		$function = '\HivePress\Helpers\\' . $name;

		if ( function_exists( $function ) ) {
			return call_user_func_array( $function, $args );
		}

		throw new \BadMethodCallException();
	}
}
