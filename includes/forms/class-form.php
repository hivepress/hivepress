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
	private $fields = [];

	/**
	 * Form errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Form response.
	 *
	 * @var string
	 */
	private $response;

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

		// Get field values.
		$values = $this->get_method() === 'POST' ? $_POST : $_GET;

		foreach ( hp_sort_array( $fields ) as $field_name => $field_args ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			// Create field.
			$this->fields[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );

			// Set field value.
			if ( isset( $values[ $field_name ] ) && '' !== $values[ $field_name ] ) {
				$this->fields[ $field_name ]->set_value( $values[ $field_name ] );
			}
		}
	}

	/**
	 * Sets field values.
	 *
	 * @param array $values Field values.
	 */
	final public function set_values( $values ) {
		foreach ( $values as $field_name => $value ) {
			if ( isset( $this->fields[ $field_name ] ) && '' !== $value ) {
				$this->fields[ $field_name ]->set_value( $value );
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
	public function get_attributes() {

		// Set class.
		$this->attributes['class'] = 'hp-form hp-form--' . esc_attr( str_replace( '_', '-', $this->get_name() ) ) . ' hp-js-form ' . hp_get_array_value( $this->attributes, 'class' );

		// Set name.
		$this->attributes['data-name'] = $this->get_name();

		return $this->attributes;
	}

	/**
	 * Gets field values.
	 *
	 * @return array
	 */
	final public function get_values() {
		$values = [];

		foreach ( $this->get_fields() as $field_name => $field ) {
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
	 * Submits form values.
	 */
	public function submit() {
		if ( $this->get_method() === 'POST' ) {

			// Verify nonce.
			// todo.
			if ( ! wp_verify_nonce( wp_create_nonce( $this->get_name() ), $this->get_name() ) ) {
				$this->errors[] = esc_html__( 'Nonce is invalid.', 'hivepress' );
			} elseif ( get_option( 'hp_recaptcha_secret_key' ) && ( $this->captcha || in_array( $this->get_name(), (array) get_option( 'hp_recaptcha_forms' ), true ) ) ) {

				// Verify captcha.
				$response = wp_remote_get(
					'https://www.google.com/recaptcha/api/siteverify?' . http_build_query(
						[
							'secret'   => get_option( 'hp_recaptcha_secret_key' ),
							'response' => hp_get_array_value( $_POST, 'g-recaptcha-response' ),
						]
					)
				);

				if ( is_wp_error( $response ) || ! hp_get_array_value( json_decode( $response['body'], true ), 'success', false ) ) {
					$this->errors[] = esc_html__( 'Captcha is invalid.', 'hivepress' );
				}
			}
		}

		// Validate fields.
		if ( empty( $this->errors ) ) {
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
