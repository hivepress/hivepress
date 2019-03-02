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
	public static function init( $args = [] ) {

		// Set name.
		self::$name = strtolower( ( new \ReflectionClass( static::class ) )->getShortName() );

		// Set properties.
		foreach ( $args as $arg_name => $arg_value ) {
			call_user_func_array( [ static::class, 'set_' . $arg_name ], [ $arg_value ] );
		}
	}

	/**
	 * Sets model fields.
	 *
	 * @param array $fields Model fields.
	 */
	final protected static function set_fields( $fields ) {
		self::$fields = [];

		foreach ( $fields as $field_name => $field_args ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			// Create field.
			self::$fields[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );
		}
	}

	/**
	 * Gets instance fields.
	 *
	 * @return array
	 */
	final public static function get_fields() {
		return self::$fields;
	}

	/**
	 * Sets model aliases.
	 *
	 * @param array $aliases Model aliases.
	 */
	final protected static function set_aliases( $aliases ) {
		self::$aliases = $aliases;
	}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 */
	final public function __call( $name, $args ) {
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

			return call_user_func_array( [ $this, $method . '_property' ], array_merge( [ $arg ], $args ) );
		}
	}

	/**
	 * Sets property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 */
	final protected function set_property( $name, $value ) {
		if ( isset( self::$fields[ $name ] ) ) {
			$field = self::$fields[ $name ];
			$field->set_value( $value );
			$this->values[ $name ] = $field->get_value();
		}
	}

	/**
	 * Gets property.
	 *
	 * @param string $name Property name.
	 */
	final protected function get_property( $name ) {
		if ( isset( $this->values[ $name ] ) ) {
			return $this->values[ $name ];
		}
	}

	/**
	 * Sets instance ID.
	 *
	 * @param int $id Instance ID.
	 */
	final protected function set_id( $id ) {
		$this->id = absint( $id );
	}

	/**
	 * Gets instance ID.
	 *
	 * @return mixed
	 */
	final public function get_id() {
		return $this->id;
	}

	/**
	 * Adds instance errors.
	 *
	 * @param array $errors Instance errors.
	 */
	final protected function add_errors( $errors ) {
		$this->errors = array_merge( $this->errors, $errors );
	}

	/**
	 * Gets instance errors.
	 *
	 * @return array
	 */
	final public function get_errors() {
		return $this->errors;
	}

	/**
	 * Sets instance values.
	 *
	 * @param array $values Instance values.
	 */
	final public function fill( $values ) {
		foreach ( $values as $field_name => $value ) {
			call_user_func_array( [ $this, 'set_' . $field_name ], [ $value ] );
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
