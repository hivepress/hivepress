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
 * Model form class.
 *
 * @class Model_Form
 */
abstract class Model_Form extends Form {

	/**
	 * Form model.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Sets form fields.
	 *
	 * @param array $fields Form fields.
	 */
	final protected function set_fields( $fields ) {
		$this->fields = [];

		// Get model class.
		$model_class = '\HivePress\Models\\' . $this->model;

		// Get model fields.
		$model_fields = [];

		if ( class_exists( $model_class ) ) {
			$model_fields = $model_class::get_fields();
		}

		foreach ( hp\sort_array( $fields ) as $field_name => $field_args ) {
			if ( isset( $model_fields[ $field_name ] ) ) {
				$field_args = hp\merge_arrays( $model_fields[ $field_name ]->get_args(), $field_args );
			}

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			if ( class_exists( $field_class ) ) {

				// Create field.
				$this->fields[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );
			}
		}
	}
}
