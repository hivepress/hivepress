<?php
/**
 * Abstract block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract block class.
 *
 * @class Block
 */
abstract class Block {
	use Traits\Mutator;

	/**
	 * Block title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Block settings.
	 *
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Block name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Block context.
	 *
	 * @var array
	 */
	protected $context = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Block arguments.
	 */
	public static function init( $args = [] ) {

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::set_static_property( $name, $value );
		}
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Block arguments.
	 */
	public function __construct( $args = [] ) {

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->bootstrap();
	}

	/**
	 * Gets block type.
	 *
	 * @return string
	 */
	final public static function get_type() {
		return strtolower( ( new \ReflectionClass( static::class ) )->getShortName() );
	}

	/**
	 * Gets block title.
	 *
	 * @return string
	 */
	final public static function get_title() {
		return static::$title;
	}

	/**
	 * Sets block settings.
	 *
	 * @param array $settings Block settings.
	 */
	final protected static function set_settings( $settings ) {
		static::$settings = [];

		foreach ( $settings as $field_name => $field_args ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			if ( class_exists( $field_class ) ) {

				// Create field.
				static::$settings[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );
			}
		}
	}

	/**
	 * Gets block settings.
	 *
	 * @return array
	 */
	final public static function get_settings() {
		return static::$settings;
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function bootstrap() {}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	abstract public function render();
}
