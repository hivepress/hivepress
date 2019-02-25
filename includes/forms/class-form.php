<?php
/**
 * Abstract form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract form class.
 *
 * @class Form
 */
abstract class Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Form title.
	 *
	 * @var string
	 */
	protected $title;

	/**
	 * Form action.
	 *
	 * @var string
	 */
	protected $action;

	/**
	 * Form method.
	 *
	 * @var string
	 */
	protected $method = 'POST';

	/**
	 * Form captcha.
	 *
	 * @var bool
	 */
	protected $captcha;

	/**
	 * Form attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * Form errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
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

	// Forbid setting name.
	final private function set_name() {}

	/**
	 * Sets form fields.
	 *
	 * @param array $fields Form fields.
	 */
	final public function set_fields( $fields ) {
		$this->fields = [];

		foreach ( hp_sort_array( $fields ) as $field_name => $field_args ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			// Create field.
			$this->fields[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );
		}
	}

	/**
	 * Gets field value.
	 *
	 * @param string $name Field name.
	 * @return mixed
	 */
	final public function get_value( $name ) {
		if ( isset( $this->fields[ $name ] ) ) {
			return $this->fields[ $name ]->get_value();
		}
	}

	/**
	 * Gets form attributes.
	 *
	 * @return array
	 */
	public function get_attributes() {

		// Set class.
		$this->attributes['class'] = 'hp-form hp-form--' . esc_attr( str_replace( '_', '-', $this->get_name() ) ) . ' hp-js-form ' . hp_get_array_value( $this->attributes, 'class' );

		return $this->attributes;
	}

	/**
	 * Validates form values.
	 *
	 * @return bool
	 */
	public function validate() {

		// Validate fields.
		foreach ( $this->get_fields() as $field ) {
			if ( ! $field->validate() ) {
				$this->errors = array_merge( $this->errors, $field->get_errors() );
			}
		}

		return empty( $this->errors );
	}

	/**
	 * Renders form HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<form action="' . esc_url( $this->get_action() ) . '" method="' . esc_attr( $this->get_method() ) . '" ' . hp_html_attributes( $this->get_attributes() ) . '>';

		// Render fields.
		foreach ( $this->get_fields() as $field ) {
			$field->set_attributes( [ 'class' => 'hp-form__field hp-form__field--' . str_replace( '_', '-', $field->get_type() ) ] );

			$output .= $field->render();
		}

		// Render submit button.
		$output .= '<button type="submit">' . esc_html__( 'Submit', 'hivepress' ) . '</button>';

		$output .= '</form>';

		return $output;
	}
}
