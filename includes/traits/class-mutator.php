<?php
/**
 * Mutator.
 *
 * @package HivePress\Traits
 */

namespace HivePress\Traits;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Implements property mutation.
 */
trait Mutator {

	/**
	 * Sets a property value.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 * @param string $prefix Method prefix.
	 */
	final protected function set_property( $name, $value, $prefix = '' ) {
		$method = $prefix . 'set_' . $name;

		if ( method_exists( $this, $method ) ) {
			call_user_func( [ $this, $method ], $value );
		} elseif ( property_exists( $this, $name ) ) {
			$this->$name = $value;
		}
	}
}
