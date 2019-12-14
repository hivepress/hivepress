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
 * Mutator trait.
 *
 * @trait Mutator
 */
trait Mutator {

	/**
	 * Sets property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 */
	final protected function set_property( $name, $value ) {
		if ( method_exists( $this, 'set_' . $name ) && ! ( new \ReflectionMethod( $this, 'set_' . $name ) )->isStatic() ) {
			call_user_func_array( [ $this, 'set_' . $name ], [ $value ] );
		} elseif ( property_exists( $this, $name ) && ! ( new \ReflectionProperty( $this, $name ) )->isStatic() ) {
			$this->$name = $value;
		}
	}

	/**
	 * Sets static property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 */
	final protected static function set_static_property( $name, $value ) {
		if ( method_exists( static::class, 'set_' . $name ) && ( new \ReflectionMethod( static::class, 'set_' . $name ) )->isStatic() ) {
			call_user_func_array( [ static::class, 'set_' . $name ], [ $value ] );
		} elseif ( property_exists( static::class, $name ) && ( new \ReflectionProperty( static::class, $name ) )->isStatic() ) {
			static::$$name = $value;
		}
	}
}
