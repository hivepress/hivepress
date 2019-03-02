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
	protected $captcha = false;

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

		// Filter arguments.
		$args = apply_filters( 'hivepress/forms/form/args', array_merge( $args, [ 'name' => $this->name ] ) );

		unset( $args['name'] );

		// Set properties.
		foreach ( $args as $arg_name => $arg_value ) {
			call_user_func_array( [ $this, 'set_' . $arg_name ], [ $arg_value ] );
		}
	}

	/**
	 * Sets form title.
	 *
	 * @param string $title Form title.
	 */
	final protected function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Gets form title.
	 *
	 * @return string
	 */
	final public function get_title() {
		return $this->title;
	}

	/**
	 * Sets form action.
	 *
	 * @param string $action Form action.
	 */
	final protected function set_action( $action ) {
		$this->action = $action;
	}

	/**
	 * Sets form method.
	 *
	 * @param string $method Form method.
	 */
	final protected function set_method( $method ) {
		$this->method = strtoupper( $method );
	}

	/**
	 * Sets form captcha.
	 *
	 * @param string $captcha Form captcha.
	 */
	final protected function set_captcha( $captcha ) {
		$this->captcha = boolval( $captcha );
	}

	/**
	 * Sets form fields.
	 *
	 * @param array $fields Form fields.
	 */
	protected function set_fields( $fields ) {
		$this->fields = [];

		foreach ( hp_sort_array( $fields ) as $field_name => $field_args ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			// Create field.
			$this->fields[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );
		}
	}

	/**
	 * Sets form errors.
	 *
	 * @param array $errors Form errors.
	 */
	final protected function set_errors( $errors ) {
		$this->errors = $errors;
	}

	/**
	 * Gets form errors.
	 *
	 * @return array
	 */
	final public function get_errors() {
		return $this->errors;
	}

	/**
	 * Sets field values.
	 *
	 * @param array $values Field values.
	 */
	final public function set_values( $values ) {
		foreach ( $values as $field_name => $value ) {
			if ( isset( $this->fields[ $field_name ] ) ) {
				$this->fields[ $field_name ]->set_value( $value );
			}
		}
	}

	/**
	 * Gets field values.
	 *
	 * @return array
	 */
	final public function get_values() {
		$values = [];

		foreach ( $this->fields as $field_name => $field ) {
			$values[ $field_name ] = $field->get_value();
		}

		return $values;
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
	final protected function get_attributes() {

		// Set action.
		if ( strpos( $this->action, hp_get_rest_url() ) === 0 ) {
			$this->attributes['action']   = '';
			$this->attributes['data-url'] = $this->action;
		} else {
			$this->attributes['action'] = $this->action;
		}

		// Set method.
		if ( ! in_array( $this->method, [ 'GET', 'POST' ], true ) ) {
			$this->attributes['method']      = 'POST';
			$this->attributes['data-method'] = $this->method;
		} else {
			$this->attributes['method'] = $this->method;
		}

		// Set class.
		$this->attributes['class'] = 'hp-form hp-form--' . esc_attr( str_replace( '_', '-', $this->name ) ) . ' hp-js-form ' . hp_get_array_value( $this->attributes, 'class' );

		return $this->attributes;
	}

	/**
	 * Validates field values.
	 *
	 * @return bool
	 */
	final public function validate() {

		// Verify captcha.
		if ( $this->captcha ) {
			$response = wp_remote_get(
				'https://www.google.com/recaptcha/api/siteverify?' . http_build_query(
					[
						'secret'   => get_option( 'hp_recaptcha_secret_key' ),
						'response' => hp_get_array_value( $_REQUEST, 'g-recaptcha-response' ),
					]
				)
			);

			if ( is_wp_error( $response ) || ! hp_get_array_value( json_decode( $response['body'], true ), 'success', false ) ) {
				$this->set_errors( array_merge( $this->errors, esc_html__( 'Captcha is invalid', 'hivepress' ) ) );
			}
		}

		// Validate fields.
		if ( empty( $this->errors ) ) {
			foreach ( $this->fields as $field ) {
				if ( ! $field->validate() ) {
					$this->set_errors( array_merge( $this->errors, $field->get_errors() ) );
				}
			}
		}

		return empty( $this->errors );
	}

	/**
	 * Renders form HTML.
	 *
	 * @return string
	 */
	final public function render() {
		$output = '<form ' . hp_html_attributes( $this->get_attributes() ) . '>';

		// Render fields.
		foreach ( $this->fields as $field ) {
			$field->set_attributes( [ 'class' => 'hp-form__field hp-form__field--' . str_replace( '_', '-', $field->get_type() ) ] );

			$output .= $field->render();
		}

		// Render captcha.
		if ( $this->captcha ) {
			$output .= '<div class="g-recaptcha" data-sitekey="' . esc_attr( get_option( 'hp_recaptcha_site_key' ) ) . '"></div>';
		}

		// Render submit button.
		$output .= '<button type="submit">' . esc_html__( 'Submit', 'hivepress' ) . '</button>';

		$output .= '</form>';

		return $output;
	}
}
