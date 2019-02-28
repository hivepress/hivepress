<?php
/**
 * Abstract model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract model class.
 *
 * @class Model
 */
abstract class Model {

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Model fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Model aliases.
	 *
	 * @var array
	 */
	protected static $aliases = [];

	/**
	 * Instance ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Instance values.
	 *
	 * @var array
	 */
	protected $values = [];

	/**
	 * Instance errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Model arguments.
	 */
	public static function __init( $args = [] ) {

		// Set name.
		self::$name = strtolower( ( new \ReflectionClass( static::class ) )->getShortName() );

		// Set properties.
		foreach ( $args as $arg_name => $arg_value ) {
			call_user_func_array( [ static::class, 'set_' . $arg_name ], [ $arg_value ] );
		}
	}

	/**
	 * Class constructor.
	 *
	 * @param array $values Instance values.
	 */
	public function __construct( $values ) {
		$this->fill( $values );
	}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 */
	final public static function __callStatic( $name, $args ) {
		$prefixes = array_filter(
			[
				'set',
				'get',
			],
			function( $prefix ) use ( $name ) {
				return strpos( $name, $prefix . '_' ) === 0;
			}
		);

		if ( ! empty( $prefixes ) ) {
			$method = reset( $prefixes );
			$arg    = substr( $name, strlen( $method ) + 1 );

			return call_user_func_array( [ static::class, $method . '_property' ], array_merge( [ $arg ], $args ) );
		}
	}

	/**
	 * Sets property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 */
	final private static function set_property( $name, $value ) {
		if ( property_exists( static::class, $name ) ) {
			self::$$name = $value;
		}
	}

	/**
	 * Gets property.
	 *
	 * @param string $name Property name.
	 */
	final private static function get_property( $name ) {
		if ( property_exists( static::class, $name ) ) {
			return self::$$name;
		}
	}

	// Forbid setting name and ID.
	final private static function set_name() {}
	final private static function set_id() {}

	/**
	 * Sets model fields.
	 *
	 * @param array $fields Model fields.
	 */
	final public static function set_fields( $fields ) {
		self::$fields = [];

		foreach ( $fields as $field_name => $field_args ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			// Create field.
			self::$fields[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );
		}
	}

	/**
	 * Sets instance field values.
	 *
	 * @param array $values Field values.
	 */
	public function fill( $values ) {
		foreach ( $values as $field_name => $value ) {
			if ( isset( self::$fields[ $field_name ] ) ) {
				self::$fields[ $field_name ]->set_value( $value );
				$this->values[ $field_name ] = self::$fields[ $field_name ]->get_value();
				self::$fields[ $field_name ]->set_value( null );
			}
		}
	}

	/**
	 * Saves instance to the database.
	 *
	 * @return bool
	 */
	abstract public function save();

	/**
	 * Deletes instance from the database.
	 *
	 * @return bool
	 */
	abstract public function delete();
}
