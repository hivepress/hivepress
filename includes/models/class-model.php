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
	protected $name;

	/**
	 * Instance ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Instance fields.
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * Instance aliases.
	 *
	 * @var array
	 */
	protected $aliases = [];

	/**
	 * Instance errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {

		// Set name.
		$this->name = strtolower( ( new \ReflectionClass( $this ) )->getShortName() );

		// Set properties.
		foreach ( $args as $arg_name => $arg_value ) {
			call_user_func_array( [ $this, 'set_' . $arg_name ], [ $arg_value ] );
		}
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

			return call_user_func_array( [ $this, $method ], array_merge( [ $arg ], $args ) );
		}
	}

	/**
	 * Sets property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 */
	final private function set( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			$this->$name = $value;
		}
	}

	/**
	 * Gets property.
	 *
	 * @param string $name Property name.
	 */
	final private function get( $name ) {
		if ( property_exists( $this, $name ) ) {
			return $this->$name;
		}
	}

	// Forbid setting name and ID.
	final private function set_name() {}
	final private function set_id() {}

	/**
	 * Sets model fields.
	 *
	 * @param array $fields Model fields.
	 */
	final public function set_fields( $fields ) {
		$this->fields = [];

		foreach ( $fields as $field_name => $field_args ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			// Create field.
			$this->fields[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );
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
