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
	use Traits\Mutator {
		set_property as _set_property;
		set_static_property as _set_static_property;
	}

	use Traits\Meta {
		get_meta as _get_meta;
	}

	/**
	 * Model meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Model fields.
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * Model aliases.
	 *
	 * @var array
	 */
	protected $aliases = [];

	/**
	 * Model relations.
	 *
	 * @var array
	 */
	protected $relations = [];

	/**
	 * Object ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Object attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Object errors.
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
		$args = hp\merge_arrays(
			[
				'meta' => [
					'name' => hp\get_class_name( static::class ),
				],
			],
			$args
		);

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::_set_static_property( $name, $value, '_' );
		}
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {

		/**
		 * Filters model arguments.
		 *
		 * @filter /models/{$name}
		 * @description Filters model arguments.
		 * @param string $name Model name.
		 * @param array $args Model arguments.
		 */
		$args = apply_filters( 'hivepress/v1/models/' . static::_get_meta( 'name' ), $args );

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->_set_property( $name, $value, '_' );
		}

		// Bootstrap properties.
		$this->boot();
	}

	/**
	 * Bootstraps model properties.
	 */
	protected function boot() {}

	/**
	 * Sets model fields.
	 *
	 * @param array $fields Model fields.
	 */
	final protected function _set_fields( $fields ) {
		$this->fields = [];

		foreach ( $fields as $name => $args ) {

			// Create field.
			$field = hp\create_class_instance( '\HivePress\Fields\\' . $args['type'], [ array_merge( $args, [ 'name' => $name ] ) ] );

			// Add field.
			if ( $field ) {
				$this->fields[ $name ] = $field;
			}
		}
	}

	/**
	 * Gets model fields.
	 *
	 * @return array
	 */
	final public function _get_fields() {
		return $this->fields;
	}

	/**
	 * Gets model aliases.
	 *
	 * @return array
	 */
	final public function _get_aliases() {
		return $this->aliases;
	}

	/**
	 * Gets model relations.
	 *
	 * @return array
	 */
	final public function _get_relations() {
		return $this->relations;
	}

	/**
	 * Routes static methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @throws \BadMethodCallException Invalid method.
	 * @return mixed
	 */
	final public static function __callStatic( $name, $args ) {

		// Get model query.
		if ( 'query' === $name ) {
			return static::_get_query();
		}

		throw new \BadMethodCallException();
	}

	/**
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @throws \BadMethodCallException Invalid method.
	 * @return mixed
	 */
	final public function __call( $name, $args ) {

		// Get model query.
		if ( 'query' === $name ) {
			return $this->_get_query( $this );
		}

		// Get or set object attribute.
		foreach ( [ 'set', 'get', 'is' ] as $prefix ) {
			if ( strpos( $name, $prefix . '_' ) === 0 ) {

				// Get attribute name.
				$action    = 'is' === $prefix ? 'get' : $prefix;
				$attribute = substr( $name, strlen( $prefix . '_' ) );

				// Get attrute value.
				$value = call_user_func_array( [ $this, '_' . $action . '_attribute' ], array_merge( [ $attribute ], $args ) );

				if ( 'set' === $action ) {
					return $this;
				}

				return $value;
			}
		}

		throw new \BadMethodCallException();
	}

	/**
	 * Gets model query.
	 *
	 * @param object $model Model object.
	 * @return object
	 */
	final protected static function _get_query( $model = null ) {

		// Create model object.
		if ( is_null( $model ) ) {
			$model = new static();
		}

		// Get model class.
		$class = static::class;

		while ( $class ) {

			// Create query.
			$query = hp\create_class_instance( '\HivePress\Queries\\' . hp\get_class_name( $class ), [ [ 'model' => $model ] ] );

			if ( $query ) {
				return $query;
			}

			// Get parent class.
			$class = get_parent_class( $class );
		}
	}

	/**
	 * Sets object attribute.
	 *
	 * @param string $name Attribute name.
	 * @param mixed  $value Attribute value.
	 */
	final protected function _set_attribute( $name, $value ) {
		if ( isset( $this->fields[ $name ] ) ) {
			$this->attributes[ $name ] = $this->fields[ $name ]->set_value( $value )->get_value();
		}
	}

	/**
	 * Gets object attribute.
	 *
	 * @param string $name Attribute name.
	 * @return mixed
	 */
	final protected function _get_attribute( $name ) {
		return hp\get_array_value( $this->attributes, $name );
	}

	/**
	 * Sets object ID.
	 *
	 * @param int $id Object ID.
	 * @return object
	 */
	final protected function set_id( $id ) {
		$this->id = absint( $id );

		return $this;
	}

	/**
	 * Gets object ID.
	 *
	 * @return int
	 */
	final public function get_id() {
		return $this->id;
	}

	/**
	 * Adds object errors.
	 *
	 * @param mixed $errors Object errors.
	 */
	final protected function _add_errors( $errors ) {
		$this->errors = array_merge( $this->errors, (array) $errors );
	}

	/**
	 * Gets object errors.
	 *
	 * @return array
	 */
	final public function _get_errors() {
		return $this->errors;
	}

	/**
	 * Sets object attributes.
	 *
	 * @param array $attributes Object attributes.
	 * @return object
	 */
	final public function fill( $attributes ) {
		foreach ( $attributes as $name => $value ) {
			call_user_func_array( [ $this, 'set_' . $name ], [ $value ] );
		}

		return $this;
	}

	/**
	 * Gets object attributes.
	 *
	 * @return array
	 */
	final public function serialize() {
		return $this->attributes;
	}

	/**
	 * Gets object.
	 *
	 * @param int $id Object ID.
	 * @return mixed
	 */
	abstract public function get( $id );

	/**
	 * Saves object.
	 *
	 * @return bool
	 */
	abstract public function save();

	/**
	 * Deletes object.
	 *
	 * @param int $id Object ID.
	 * @return bool
	 */
	abstract public function delete( $id = null );
}
