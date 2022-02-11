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
 * Implements class meta.
 */
trait Meta {

	/**
	 * Class meta values.
	 *
	 * @var array
	 */
	protected static $meta = [];

	/**
	 * Sets class meta values.
	 *
	 * @param array $meta Meta values.
	 */
	final protected static function set_meta( $meta ) {
		static::$meta[ hp\get_class_name( static::class ) ] = $meta;
	}

	/**
	 * Gets class meta values.
	 *
	 * @param string $name Meta name.
	 * @return mixed
	 */
	final public static function get_meta( $name = '' ) {
		$meta = hp\get_array_value( static::$meta, hp\get_class_name( static::class ) );

		if ( $name ) {
			$meta = hp\get_array_value( $meta, $name );
		}

		return $meta;
	}
}
