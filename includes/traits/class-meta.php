<?php
/**
 * Meta.
 *
 * @package HivePress\Traits
 */

namespace HivePress\Traits;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Meta trait.
 *
 * @trait Meta
 */
trait Meta {

	/**
	 * Gets meta value.
	 *
	 * @param string $name Meta name.
	 * @return mixed
	 */
	final public static function get_meta( $name = '' ) {
		if ( property_exists( static::class, 'meta' ) && ( new \ReflectionProperty( static::class, 'meta' ) )->isStatic() ) {
			$meta = static::$meta;

			if ( $name ) {
				$meta = hp\get_array_value( $meta, $name );
			}

			return $meta;
		}
	}
}
