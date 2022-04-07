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
 */
abstract class Model {
	use Traits\Mutator {
		set_property as _set_property;
	}

	use Traits\Meta {
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
	 * @param array $meta Class meta values.
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
			 * Filters the model class meta. The class meta stores properties related to the model type rather than a specific model object. The dynamic part of the hook refers to the model name (e.g. `listing`). You can check the available models in the `includes/models` directory of HivePress.
			 *
			 * @hook hivepress/v1/models/{model_name}/meta
			 * @param {array} $meta Class meta values.
			 * @return {array} Class meta values.
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
			 * Filters the model properties. The dynamic part of the hook refers to the model name (e.g. `listing`). You can check the available models in the `includes/models` directory of HivePress.
			 *
			 * @hook hivepress/v1/models/{model_name}
			 * @param {array} $props Model properties.
			 * @param {object} $model Model object.
			 * @return {array} Model properties.
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
			if ( ! isset( $args['_alias'] ) ) {
				if ( hp\get_array_value( $args, '_relation' ) === 'many_to_many' ) {
					$args['_alias'] = hp\call_class_method( '\HivePress\Models\\' . hp\get_array_value( $args, '_model' ), '_get_meta', [ 'alias' ] );
				} elseif ( hp\get_array_value( $args, '_external' ) ) {
					$args['_alias'] = hp\prefix( $name );
				}
			}

			// Set context.
			$args['context']['model'] = static::_get_meta( 'name' );

			// Create field.
			$field = hp\create_class_instance( '\HivePress\Fields\\' . $args['type'], [ array_merge( $args, [ 'name' => $name ] ) ] );

			// Add field.
			if ( $field ) {
				$this->fields[ $name ] = $field;
			}
		}
	}

	/**
	 * Gets object fields.
	 *
	 * @return array
	 */
	public function _get_fields() {
		return $this->fields;
	}

	/**
	 * Catches calls to undefined static methods.
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
	 * Catches calls to undefined methods.
	 *
	 * @param string $name Method name.
	 * @param array  $args Method arguments.
	 * @throws \BadMethodCallException Invalid method.
	 * @return mixed
	 */
	final public function __call( $name, $args ) {

		// Get model query.
		if ( 'query' === $name ) {
			return static::_get_query( $this );
		}

		// Get or set field value.
		$prefixes = [
			'set',
			'get',
			'is',
			'has',
			'display',
			'save',
		];

		foreach ( $prefixes as $prefix ) {
			if ( strpos( $name, $prefix . '_' ) === 0 ) {

				// Get field name.
				$action = ! in_array( $prefix, [ 'set', 'save' ], true ) ? 'get' : $prefix;
				$field  = substr( $name, strlen( $prefix . '_' ) );

				// Get field arguments.
				if ( 'get' === $action ) {
					$args = [ $args ];

					if ( 'display' === $prefix ) {
						$args[] = true;
					}
				}

				array_unshift( $args, $field );

				// Get field value.
				$value = call_user_func_array( [ $this, '_' . $action . '_value' ], $args );

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
	 * @param array  $args Field arguments.
	 * @param bool   $display Format value for display?
	 * @return mixed
	 */
	final protected function _get_value( $name, $args = [], $display = false ) {

		// Get model field.
		$field = null;

		if ( strpos( $name, '__' ) && ! isset( $this->fields[ $name ] ) ) {
			list($name, $field) = explode( '__', $name );
		}

		if ( isset( $this->fields[ $name ] ) ) {

			// Get field value.
			$value = null;

			if ( $display && empty( $field ) ) {
				$value = $this->fields[ $name ]->get_display_value();
			} else {
				$value = $this->fields[ $name ]->get_value();

				// Get model name.
				$model = $this->fields[ $name ]->get_arg( '_model' );

				if ( $model && 'id' !== $field ) {

					// Get model objects.
					if ( ! isset( $this->values[ $name ] ) ) {
						if ( is_array( $value ) ) {
							$value = array_filter(
								array_map(
									function( $id ) use ( $model ) {
										return hivepress()->model->get_model_object( $model, $id );
									},
									$value
								)
							);
						} else {
							$value = hivepress()->model->get_model_object( $model, $value );
						}

						$this->values[ $name ] = is_null( $value ) ? false : $value;
					} else {
						$value = $this->values[ $name ];

						if ( false === $value ) {
							$value = null;
						}
					}

					if ( $field ) {

						// Get object method.
						$method = ( $display ? 'display' : 'get' ) . '_' . $field;

						// Get object values.
						if ( is_array( $value ) ) {
							$value = array_map(
								function( $object ) use ( $method, $args ) {
									return call_user_func_array( [ $object, $method ], $args );
								},
								$value
							);
						} elseif ( $value ) {
							$value = call_user_func_array( [ $value, $method ], $args );
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
	final public function set_id( $id ) {
		$this->id = absint( $id );

		// Get fields.
		$fields = array_map(
			function( $field ) {
				return array_merge(
					$field->get_args(),
					[
						'default' => $field->get_value(),
					]
				);
			},
			$this->fields
		);

		// Filter fields.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters model fields. At the time of this hook the model object ID is already available. The dynamic part of the hook refers to the model name (e.g. `listing`). You can check the available models in the `includes/models` directory of HivePress.
			 *
			 * @hook hivepress/v1/models/{model_name}/fields
			 * @param {array} $fields Model fields.
			 * @param {object} $model Model object.
			 * @return {array} Model fields.
			 */
			$fields = apply_filters( 'hivepress/v1/models/' . hp\get_class_name( $class ) . '/fields', $fields, $this );
		}

		// Set fields.
		$this->_set_fields( $fields );

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
	 * Saves field value.
	 *
	 * @param string $name Field name.
	 */
	final protected function _save_value( $name ) {
		return $this->save( [ $name ] );
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
	 * @param array $names Field names.
	 * @return bool
	 */
	final public function validate( $names = [] ) {
		$this->errors = [];

		// Filter fields.
		$fields = $this->fields;

		if ( $names ) {
			$fields = array_filter(
				$fields,
				function( $field ) use ( $names ) {
					return in_array( $field->get_name(), $names, true );
				}
			);
		}

		// Validate fields.
		foreach ( $fields as $field ) {
			if ( ! $field->validate() ) {
				$this->_add_errors( $field->get_errors() );
			}
		}

		// Filter errors.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters the model validation errors. With this hook, you can implement custom validation checks and add new error messages to the filtered array. The dynamic part of the hook refers to the model name (e.g. `listing`). You can check the available models in the `includes/models` directory of HivePress.
			 *
			 * @hook hivepress/v1/models/{model_name}/errors
			 * @param {array} $errors Model errors.
			 * @param {object} $model Model object.
			 * @return {array} Model errors.
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
	 * @param array $names Field names.
	 * @return bool
	 */
	abstract public function save( $names = [] );

	/**
	 * Deletes object.
	 *
	 * @param int $id Object ID.
	 * @return bool
	 */
	abstract public function delete( $id = null );
}
