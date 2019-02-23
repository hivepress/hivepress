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
	private $name;

	/**
	 * Form title.
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Form message.
	 *
	 * @var string
	 */
	private $message;

	/**
	 * Form action.
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Form method.
	 *
	 * @var string
	 */
	private $method = 'POST';

	/**
	 * Form redirect.
	 *
	 * @var mixed
	 */
	protected $redirect;

	/**
	 * Form captcha.
	 *
	 * @var bool
	 */
	private $captcha;

	/**
	 * Form attributes.
	 *
	 * @var array
	 */
	private $attributes = [];

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	private $fields = [];

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

		// Set nonce.
		if ( $this->get_method() === 'POST' ) {
			$this->set_fields(
				[
					'nonce' => [
						'type' => 'hidden',
					],
				]
			);
		}

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
		foreach ( hp_sort_array( $fields ) as $field_name => $field_args ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			// Create field.
			$this->fields[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );

			// Set field value.
			$values = $this->get_method() === 'POST' ? $_POST : $_GET;

			if ( isset( $values[ $field_name ] ) && '' !== $values[ $field_name ] ) {
				$this->fields[ $field_name ]->set_value( $values[ $field_name ] );
			}
		}
	}

	/**
	 * Sets field value.
	 *
	 * @param string $name Field name.
	 * @param mixed  $value Field value.
	 */
	final private function set_value( $name, $value ) {
		if ( isset( $this->get_fields()[ $name ] ) ) {
			$this->get_fields()[ $name ]->set_value( $value );
		}
	}

	/**
	 * Gets form attributes.
	 *
	 * @return array
	 */
	final public function get_attributes() {
		$attributes = $this->attributes;

		// Set class.
		$attributes['class'] = 'hp-form hp-form--' . esc_attr( str_replace( '_', '-', $this->get_name() ) ) . ' hp-js-form ' . hp_get_array_value( $attributes, 'class' );

		// Set name.
		$attributes['data-name'] = $this->get_name();

		return $attributes;
	}

	/**
	 * Gets field value.
	 *
	 * @param string $name Field name.
	 * @return mixed
	 */
	final private function get_value( $name ) {
		if ( isset( $this->get_fields()[ $name ] ) ) {
			return $this->get_fields()[ $name ]->get_value();
		}
	}

	/**
	 * Submits form values.
	 */
	public function submit() {

		// Verify nonce.
		// todo.
		if ( $this->get_method() === 'POST' && ! wp_verify_nonce( wp_create_nonce( $this->get_name() ), $this->get_name() ) ) {
			$this->errors[] = esc_html__( 'Nonce is invalid.', 'hivepress' );
		} else {

			// Validate fields.
			foreach ( $this->get_fields() as $field ) {
				if ( ! $field->validate() ) {
					$this->errors = array_merge( $this->errors, $field->get_errors() );
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
	public function render() {
		$output = '<form action="' . esc_url( $this->get_action() ) . '" method="' . esc_attr( $this->get_method() ) . '" ' . hp_html_attributes( $this->get_attributes() ) . '>';

		// Set nonce value.
		$this->set_value( 'nonce', wp_create_nonce( $this->get_name() ) );

		// Render fields.
		foreach ( $this->get_fields() as $field_name => $field ) {
			$field->set_attributes( [ 'class' => 'hp-form__field hp-form__field--' . str_replace( '_', '-', $field->get_type() ) ] );

			$output .= $field->render();
		}

		// Render captcha.
		if ( get_option( 'hp_recaptcha_site_key' ) && ( $this->captcha || in_array( $this->get_name(), (array) get_option( 'hp_recaptcha_forms' ), true ) ) ) {
			$output .= '<div class="g-recaptcha" data-sitekey="' . esc_attr( get_option( 'hp_recaptcha_site_key' ) ) . '"></div>';
		}

		// Render submit button.
		$output .= '<button type="submit">' . esc_html__( 'Submit', 'hivepress' ) . '</button>';

		$output .= '</form>';

		return $output;
	}
}
