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
	 * Template name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Template title.
	 *
	 * @var string
	 */
	protected static $title;

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

		// Set name.
		$args['name'] = strtolower( ( new \ReflectionClass( static::class ) )->getShortName() );

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::set_static_property( $name, $value );
		}
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
