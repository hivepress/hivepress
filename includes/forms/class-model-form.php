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
	 * Model name.
	 *
	 * @var string
	 */
	protected static $model;

	/**
	 * Instance ID.
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Sets form fields.
	 *
	 * @param array $fields Form fields.
	 */
	final protected static function set_fields( $fields ) {
		static::$fields = [];

		// Get model class.
		$model_class = '\HivePress\Models\\' . static::$model;

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
				static::$fields[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );
			}
		}
	}

	/**
	 * Gets model name.
	 *
	 * @return string
	 */
	final public static function get_model() {
		return static::$model;
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function bootstrap() {
		$attributes = [];

		// Set action.
		if ( isset( static::$action ) ) {
			static::$action = rtrim(
				hp\replace_tokens(
					[
						'id' => $this->id,
					],
					static::$action
				),
				'/'
			);
		}

		// Set model.
		if ( isset( static::$model ) ) {
			$attributes['data-model'] = static::$model;
		}

		// Set ID.
		if ( isset( $this->id ) ) {
			$attributes['data-id'] = $this->id;
		}

		// Set values.
		if ( isset( static::$model ) && isset( $this->id ) ) {
			$model_class = '\HivePress\Models\\' . static::$model;

			if ( class_exists( $model_class ) ) {
				$instance = $model_class::get_by_id( $this->id );

				if ( ! is_null( $instance ) ) {
					$this->set_values( $instance->serialize() );
				}
			}
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
	}
}
