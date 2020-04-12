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
 * Helper component class.
 *
 * @class Helper
 */
final class Helper extends Component {

	/**
	 * Routes methods.
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
