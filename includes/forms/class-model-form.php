<?php
/**
 * Model form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract model form class.
 */
abstract class Model_Form extends Form {

	/**
	 * Model object.
	 *
	 * @var object
	 */
	protected $model;

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {

		// Set model object.
		$this->set_model( hp\get_array_value( $args, 'model' ) );

		parent::__construct( $args );
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function boot() {
		$attributes = [];

		// Set model name.
		$attributes['data-model'] = static::get_meta( 'model' );

		if ( $this->model->get_id() ) {

			// Set object ID.
			$attributes['data-id'] = $this->model->get_id();

			// Set field values.
			$values = $this->model->serialize();

			foreach ( $this->fields as $field_name => $field ) {
				if ( $field->get_arg( '_separate' ) ) {
					unset( $values[ $field_name ] );
				}
			}

			$this->set_values( $values, true );
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::boot();
	}

	/**
	 * Sets model object.
	 *
	 * @param mixed $model Model object.
	 */
	final protected function set_model( $model ) {
		if ( ! isset( $this->model ) ) {
			if ( ! hp\is_class_instance( $model, '\HivePress\Models\\' . static::get_meta( 'model' ) ) ) {
				$model = hp\create_class_instance( '\HivePress\Models\\' . static::get_meta( 'model' ) );
			}

			$this->model = $model;
		}
	}

	/**
	 * Gets model object.
	 *
	 * @return object
	 */
	final public function get_model() {
		return $this->model;
	}

	/**
	 * Sets form fields.
	 *
	 * @param array $fields Form fields.
	 */
	final protected function set_fields( $fields ) {

		// Get model fields.
		$model_fields = $this->model->_get_fields();

		// Merge field arguments.
		foreach ( $fields as $name => $args ) {
			if ( ! hp\get_array_value( $args, '_separate' ) && isset( $model_fields[ $name ] ) ) {
				$fields[ $name ] = hp\merge_arrays( $model_fields[ $name ]->get_args(), $args );
			}
		}

		parent::set_fields( $fields );
	}
}
