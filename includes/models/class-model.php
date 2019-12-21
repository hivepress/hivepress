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
	 * Routes methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @throws \BadMethodCallException Invalid method.
	 * @return mixed
	 */
	final public function __call( $name, $args ) {
		foreach ( [ 'set', 'get', 'is' ] as $prefix ) {
			if ( strpos( $name, $prefix . '_' ) === 0 ) {
				$action    = 'is' === $prefix ? 'get' : $prefix;
				$attribute = substr( $name, strlen( $prefix . '_' ) );

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
	 */
	final protected function set_id( $id ) {
		$this->id = absint( $id );
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
	final protected function add_errors( $errors ) {
		$this->errors = array_merge( $this->errors, (array) $errors );
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
	 * Saves object to the database.
	 *
	 * @return bool
	 */
	abstract public function save();
}
