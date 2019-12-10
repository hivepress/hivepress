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

		// Get model fields.
		$model_fields = hp\call_class_method( '\HivePress\Models\\' . static::$model, 'get_fields' );

		// Merge fields.
		if ( ! is_null( $model_fields ) ) {
			foreach ( $fields as $field_name => $field_args ) {
				if ( isset( $model_fields[ $field_name ] ) ) {
					$fields[ $field_name ] = hp\merge_arrays( $model_fields[ $field_name ]->get_args(), $field_args );
				}
			}
		}

		parent::set_fields( $fields );
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
			$instance = hp\call_class_method( '\HivePress\Models\\' . static::$model, 'get_by_id', [ $this->id ] );

			if ( ! is_null( $instance ) ) {
				$this->set_values( $instance->serialize() );
			}
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
	}
}
