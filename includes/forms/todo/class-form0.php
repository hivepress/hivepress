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
	 * Form message.
	 *
	 * @var string
	 */
	private $message;

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
	 * Form response.
	 *
	 * @var string
	 */
	private $response;



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
	}

	/**
	 * Renders form HTML.
	 *
	 * @return string
	 */
	public function render() {

		// Render captcha.
		if ( get_option( 'hp_recaptcha_site_key' ) && ( $this->captcha || in_array( $this->get_name(), (array) get_option( 'hp_recaptcha_forms' ), true ) ) ) {
			$output .= '<div class="g-recaptcha" data-sitekey="' . esc_attr( get_option( 'hp_recaptcha_site_key' ) ) . '"></div>';
		}
	}
}
