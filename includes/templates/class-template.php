<?php
/**
 * Abstract template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract template class.
 *
 * @class Template
 */
abstract class Template {
	use Traits\Mutator;

	/**
	 * Template blocks.
	 *
	 * @var array
	 */
	protected static $blocks = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Template arguments.
	 */
	public static function init( $args = [] ) {

		/**
		 * Filters template arguments.
		 *
		 * @filter /templates/{$name}
		 * @description Filters template arguments.
		 * @param string $name Template name.
		 * @param array $args Template arguments.
		 */
		$args = apply_filters( 'hivepress/v1/templates/' . static::get_name(), $args );

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::set_static_property( $name, $value );
		}
	}

	/**
	 * Gets template name.
	 *
	 * @return string
	 */
	final public static function get_name() {
		return hp\get_class_name( static::class );
	}

	/**
	 * Gets template blocks.
	 *
	 * @return array
	 */
	final public static function get_blocks() {
		return static::$blocks;
	}
}
