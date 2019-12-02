<?php
/**
 * Abstract model.
 *
 * @package HivePress\Models
 */

namespace HivePress\Models;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract model class.
 *
 * @class Model
 */
abstract class Model {
	use Traits\Mutator;

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
	 * Instance attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

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

		/**
		 * Filters model arguments.
		 *
		 * @filter /models/{$name}
		 * @description Filters model arguments.
		 * @param string $name Model name.
		 * @param array $args Model arguments.
		 */
		$args = apply_filters( 'hivepress/v1/models/' . static::get_name(), $args );

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::set_static_property( $name, $value );
		}
	}

	/**
	 * Routes static methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @return mixed
	 */
	final public static function __callStatic( $name, $args ) {
		$model_class = static::class;

		while ( false !== $model_class ) {
			$query_class = str_ireplace( '\models\\', '\queries\\', $model_class );

			if ( class_exists( $query_class ) && ! ( new \ReflectionClass( $query_class ) )->isAbstract() ) {
				return call_user_func_array( [ new $query_class( [ 'model' => static::get_name() ] ), $name ], $args );
			}

			$model_class = get_parent_class( $model_class );
		}
	}

	/**
	 * Gets model name.
	 *
	 * @return string
	 */
	final public static function get_name() {
		return strtolower( ( new \ReflectionClass( static::class ) )->getShortName() );
	}

	/**
	 * Sets model fields.
	 *
	 * @param array $fields Model fields.
	 */
	final protected static function set_fields( $fields ) {
		static::$fields = [];

		foreach ( hp\sort_array( $fields ) as $field_name => $field_args ) {

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
	 * Gets model aliases.
	 *
	 * @return array
	 */
	final public static function get_aliases() {
		return static::$aliases;
	}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @return mixed
	 */
	final public function __call( $name, $args ) {
		$prefixes = array_filter(
			[
				'set',
				'get',
				'is',
			],
			function( $prefix ) use ( $name ) {
				return strpos( $name, $prefix . '_' ) === 0;
			}
		);

		if ( ! empty( $prefixes ) ) {
			$method = reset( $prefixes );
			$arg    = substr( $name, strlen( $method ) + 1 );

			if ( 'is' === $method ) {
				$method = 'get';
			}

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
			static::$fields[ $name ]->set_value( $value );
			$this->attributes[ $name ] = static::$fields[ $name ]->get_value();
		}
	}

	/**
	 * Gets property.
	 *
	 * @param string $name Property name.
	 */
	final protected function get_property( $name ) {
		return hp\get_array_value( $this->attributes, $name );
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
	 * Sets instance attributes.
	 *
	 * @param array $attributes Instance attributes.
	 */
	final public function fill( $attributes ) {
		foreach ( $attributes as $name => $value ) {
			call_user_func_array( [ $this, 'set_' . $name ], [ $value ] );
		}
	}

	/**
	 * Gets instance attributes.
	 *
	 * @return array
	 */
	final public function serialize() {
		$attributes = [];

		foreach ( $this->attributes as $name => $value ) {
			if ( is_null( $value ) && method_exists( $this, 'get_' . $name ) ) {
				$value = call_user_func( [ $this, 'get_' . $name ] );
			}

			if ( ! is_null( $value ) ) {
				$attributes[ $name ] = $value;
			}
		}

		return $attributes;
	}

	/**
	 * Gets instance by ID.
	 *
	 * @param int $id Instance ID.
	 * @return mixed
	 */
	abstract public static function get_by_id( $id );

	/**
	 * Deletes instance by ID.
	 *
	 * @param int $id Instance ID.
	 * @return bool
	 */
	abstract public static function delete_by_id( $id );

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
	final public function delete() {
		return $this->id && static::delete_by_id( $this->id );
	}
}
