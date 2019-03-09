<?php
/**
 * Abstract model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract model class.
 *
 * @class Model
 */
abstract class Model {

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
		$args['name'] = strtolower( ( new \ReflectionClass( static::class ) )->getShortName() );

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::set_static_property( $name, $value );
		}
	}

	/**
	 * Sets static property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 */
	final protected static function set_static_property( $name, $value ) {
		if ( property_exists( static::class, $name ) ) {
			if ( method_exists( static::class, 'set_' . $name ) ) {
				call_user_func_array( [ static::class, 'set_' . $name ], [ $value ] );
			} else {
				static::$$name = $value;
			}
		}
	}

	/**
	 * Sets model fields.
	 *
	 * @param array $fields Model fields.
	 */
	final protected static function set_fields( $fields ) {
		static::$fields = [];

		foreach ( $fields as $field_name => $field_args ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			if ( class_exists( $field_class ) ) {

				// Create field.
				static::$fields[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );
			}
		}
	}

	/**
	 * Gets model fields.
	 *
	 * @return array
	 */
	final public static function get_fields() {
		return static::$fields;
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
		if ( isset( static::$fields[ $name ] ) ) {
			$field = static::$fields[ $name ];
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
		return hp\get_array_value( $this->values, $name );
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
