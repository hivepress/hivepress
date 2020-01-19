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
	use Traits \Mutator {
		set_property as _set_property;
	}

	use Traits \Meta {
		get_meta as _get_meta;
		set_meta as _set_meta;
	}

	/**
	 * Object ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Object fields.
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * Object values.
	 *
	 * @var array
	 */
	protected $values = [];

	/**
	 * Object errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Class initializer.
	 *
	 * @param array $meta Model meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'name' => hp\get_class_name( static::class ),
			],
			$meta
		);

		// Filter meta.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters model meta.
			 *
			 * @filter /models/{$name}/meta
			 * @description Filters model meta.
			 * @param string $name Model name.
			 * @param array $meta Model meta.
			 */
			$meta = apply_filters( 'hivepress/v1/models/' . hp\get_class_name( $class ) . '/meta', $meta );
		}

		// Set meta.
		static::_set_meta( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Model arguments.
	 */
	public function __construct( $args = [] ) {

		// Filter properties.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters model arguments.
			 *
			 * @filter /models/{$name}
			 * @description Filters model arguments.
			 * @param string $name Model name.
			 * @param array $args Model arguments.
			 * @param object $object Model object.
			 */
			$args = apply_filters( 'hivepress/v1/models/' . hp\get_class_name( $class ), $args, $this );
		}

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

			// Set alias.
			if ( hp\get_array_value( $args, '_external' ) && ! isset( $args['_alias'] ) ) {
				$args['_alias'] = hp\prefix( $name );
			}

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
	public function _get_fields() {
		return $this->fields;
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

		// Get or set field value.
		foreach ( [ 'set', 'get', 'is', 'display' ] as $prefix ) {
			if ( strpos( $name, $prefix . '_' ) === 0 ) {

				// Get field name.
				$action = 'set' !== $prefix ? 'get' : $prefix;
				$field  = substr( $name, strlen( $prefix . '_' ) );

				if ( 'display' === $prefix ) {
					$args[] = true;
				}

				// Get field value.
				$value = call_user_func_array( [ $this, '_' . $action . '_value' ], array_merge( [ $field ], $args ) );

				if ( 'set' !== $action ) {
					return $value;
				}

				return $this;
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
		if ( empty( $model ) ) {
			$model = new static();
		}

		foreach ( array_reverse( hp\get_class_parents( static::class ) ) as $class ) {

			// Create query.
			$query = hp\create_class_instance( '\HivePress\Queries\\' . hp\get_class_name( $class ), [ [ 'model' => $model ] ] );

			if ( $query ) {
				return $query;
			}
		}
	}

	/**
	 * Sets field value.
	 *
	 * @param string $name Field name.
	 * @param mixed  $value Field value.
	 */
	final protected function _set_value( $name, $value ) {
		if ( isset( $this->fields[ $name ] ) ) {

			// Get object IDs.
			if ( $this->fields[ $name ]->get_arg( '_model' ) ) {
				if ( is_array( $value ) ) {
					$value = array_map(
						function( $object ) {
							return is_object( $object ) ? $object->get_id() : $object;
						},
						$value
					);
				} elseif ( is_object( $value ) ) {
					$value = $value->get_id();
				}
			}

			// Set field value.
			$this->fields[ $name ]->set_value( $value );
		}
	}

	/**
	 * Gets field value.
	 *
	 * @param string $name Field name.
	 * @param bool   $display Display flag.
	 * @return mixed
	 */
	final protected function _get_value( $name, $display = false ) {

		// Get model field.
		$field = null;

		if ( strpos( $name, '__' ) ) {
			list($name, $field) = explode( '__', $name );
		}

		if ( isset( $this->fields[ $name ] ) ) {

			// Get field value.
			$value = null;

			if ( $display && empty( $field ) ) {
				$value = $this->fields[ $name ]->get_display_value();
			} else {
				$value = $this->fields[ $name ]->get_value();

				if ( $this->fields[ $name ]->get_arg( '_model' ) && 'id' !== $field ) {

					// Get model object.
					$model = hp\create_class_instance( '\HivePress\Models\\' . $this->fields[ $name ]->get_arg( '_model' ) );

					if ( $model ) {

						// Get object method.
						$method = null;

						if ( $field ) {
							$method = ( $display ? 'display' : 'get' ) . '_' . $field;
						}

						// Get object fields.
						if ( is_array( $value ) ) {
							$value = array_map(
								function( $id ) use ( $model, $method ) {
									$object = $model->query()->get_by_id( $id );

									if ( $object && $method ) {
										$object = call_user_func( [ $object, $method ] );
									}

									return $object;
								},
								$value
							);
						} else {
							$value = $model->query()->get_by_id( $value );

							if ( $value && $method ) {
								$value = call_user_func( [ $value, $method ] );
							}
						}
					}
				}
			}

			return $value;
		}
	}

	/**
	 * Sets object ID.
	 *
	 * @param int $id Object ID.
	 * @return object
	 */
	final protected function set_id( $id ) {
		$this->id = absint( $id );

		if ( has_filter( 'hivepress/v1/models/' . static::_get_meta( 'name' ) . '/fields' ) ) {

			/**
			 * Filters model fields.
			 *
			 * @filter /models/{$name}/fields
			 * @description Filters model fields.
			 * @param string $name Model name.
			 * @param array $fields Model fields.
			 * @param object $object Model object.
			 */
			$this->_set_fields(
				apply_filters(
					'hivepress/v1/models/' . static::_get_meta( 'name' ) . '/fields',
					array_map(
						function( $field ) {
							return array_merge(
								$field->get_args(),
								[
									'default' => $field->get_value(),
								]
							);
						},
						$this->fields
					),
					$this
				)
			);
		}

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
	 * Sets field values.
	 *
	 * @param array $values Field values.
	 * @return object
	 */
	final public function fill( $values ) {
		unset( $values['id'] );

		foreach ( $values as $name => $value ) {
			call_user_func( [ $this, 'set_' . $name ], $value );
		}

		return $this;
	}

	/**
	 * Gets field values.
	 *
	 * @return array
	 */
	final public function serialize() {
		$values = [];

		foreach ( $this->fields as $name => $field ) {

			// Get model field.
			$field_name = $name;

			if ( $field->get_arg( '_model' ) ) {
				$field_name .= '__id';
			}

			// Get field value.
			$values[ $name ] = call_user_func( [ $this, 'get_' . $field_name ] );
		}

		return $values;
	}

	/**
	 * Validates field values.
	 *
	 * @return bool
	 */
	final public function validate() {
		$this->errors = [];

		// Validate fields.
		foreach ( $this->fields as $field ) {
			if ( ! $field->validate() ) {
				$this->_add_errors( $field->get_errors() );
			}
		}

		// Filter errors.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters model errors.
			 *
			 * @filter /models/{$name}/errors
			 * @description Filters model errors.
			 * @param string $name Model name.
			 * @param array $errors Model errors.
			 * @param object $object Model object.
			 */
			$this->errors = apply_filters( 'hivepress/v1/models/' . hp\get_class_name( $class ) . '/errors', $this->errors, $this );
		}

		return empty( $this->errors );
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
