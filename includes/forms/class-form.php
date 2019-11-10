<?php
/**
 * Abstract form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;
use HivePress\Traits;
use HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract form class.
 *
 * @class Form
 */
abstract class Form {
	use Traits\Mutator;

	/**
	 * Form name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Form title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Form description.
	 *
	 * @var string
	 */
	protected static $description;

	/**
	 * Form message.
	 *
	 * @var string
	 */
	protected static $message;

	/**
	 * Form action.
	 *
	 * @var string
	 */
	protected static $action;

	/**
	 * Form method.
	 *
	 * @var string
	 */
	protected static $method = 'POST';

	/**
	 * Form captcha.
	 *
	 * @var bool
	 */
	protected static $captcha = false;

	/**
	 * Form redirect.
	 *
	 * @var mixed
	 */
	protected static $redirect = false;

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Form button.
	 *
	 * @var object
	 */
	protected static $button;

	/**
	 * Form header.
	 *
	 * @var string
	 */
	protected $header;

	/**
	 * Form footer.
	 *
	 * @var string
	 */
	protected $footer;

	/**
	 * Form attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Form errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {

		// Set name.
		$args['name'] = strtolower( ( new \ReflectionClass( static::class ) )->getShortName() );

		/**
		 * Filters form arguments.
		 *
		 * @filter /forms/{$name}
		 * @description Filters form arguments.
		 * @param string $name Form name or "form" to filter all forms.
		 * @param array $args Form arguments.
		 */
		$args = apply_filters( 'hivepress/v1/forms/form', $args );
		$args = apply_filters( 'hivepress/v1/forms/' . $args['name'], $args );

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::set_static_property( $name, $value );
		}
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {

		/**
		 * Filters form arguments.
		 *
		 * @filter /forms/form/args
		 * @description Filters form arguments.
		 * @param array $args Form arguments.
		 * @param string $name Form name.
		 */
		$args = apply_filters( 'hivepress/v1/forms/form/args', $args, static::$name );

		unset( $args['name'] );

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->bootstrap();
	}

	/**
	 * Gets form title.
	 *
	 * @return string
	 */
	final public static function get_title() {
		return static::$title;
	}

	/**
	 * Sets form method.
	 *
	 * @param string $method Form method.
	 */
	final protected static function set_method( $method ) {
		static::$method = strtoupper( $method );
	}

	/**
	 * Gets form method.
	 *
	 * @return string
	 */
	final public static function get_method() {
		return static::$method;
	}

	/**
	 * Sets form fields.
	 *
	 * @param array $fields Form fields.
	 */
	protected static function set_fields( $fields ) {
		static::$fields = [];

		foreach ( hp\sort_array( $fields ) as $field_name => $field_args ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			if ( class_exists( $field_class ) ) {

				// Create field.
				static::$fields[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );
			}
		}
	}

	/**
	 * Sets form button.
	 *
	 * @param array $button Button arguments.
	 */
	final protected static function set_button( $button ) {
		if ( ! is_null( $button ) ) {
			static::$button = new Fields\Button(
				hp\merge_arrays(
					[
						'label'      => esc_html__( 'Submit', 'hivepress' ),
						'type'       => 'button',
						'attributes' => [
							'class' => [ 'hp-form__button', 'button', 'alt' ],
						],
					],
					$button
				)
			);
		}
	}

	/**
	 * Adds form errors.
	 *
	 * @param array $errors Form errors.
	 */
	final protected function add_errors( $errors ) {
		$this->errors = array_merge( $this->errors, $errors );
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
			if ( isset( static::$fields[ $field_name ] ) ) {
				static::$fields[ $field_name ]->set_value( $value );
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

		foreach ( static::$fields as $field_name => $field ) {
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
		if ( isset( static::$fields[ $name ] ) ) {
			return static::$fields[ $name ]->get_value();
		}
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function bootstrap() {
		$attributes = [];

		// Set action.
		if ( strpos( static::$action, get_rest_url() ) === 0 ) {
			$attributes['action']      = '#';
			$attributes['data-action'] = static::$action;
		} else {
			$attributes['action'] = static::$action;
		}

		// Set method.
		if ( ! in_array( static::$method, [ 'GET', 'POST' ], true ) ) {
			$attributes['method']      = 'POST';
			$attributes['data-method'] = static::$method;
		} else {
			$attributes['method'] = static::$method;
		}

		// Set message.
		if ( static::$message ) {
			$attributes['data-message'] = static::$message;
		}

		// Set redirect.
		if ( static::$redirect ) {
			$redirect = hp\get_array_value( $_GET, 'redirect', 'true' );

			if ( hp\validate_redirect( $redirect ) ) {
				$attributes['data-redirect'] = esc_url( $redirect );
			} else {
				$attributes['data-redirect'] = 'true';
			}
		}

		// Set component.
		$attributes['data-component'] = 'form';

		// Set class.
		$attributes['class'] = [ 'hp-form', 'hp-form--' . hp\sanitize_slug( static::$name ) ];

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );
	}

	/**
	 * Validates field values.
	 *
	 * @return bool
	 */
	final public function validate() {

		// Verify captcha.
		if ( static::$captcha ) {
			$response = wp_remote_get(
				'https://www.google.com/recaptcha/api/siteverify?' . http_build_query(
					[
						'secret'   => get_option( 'hp_recaptcha_secret_key' ),
						'response' => hp\get_array_value( $_POST, 'g-recaptcha-response' ),
					]
				)
			);

			if ( ! hp\get_array_value( json_decode( wp_remote_retrieve_body( $response ), true ), 'success', false ) ) {
				$this->add_errors( [ esc_html__( 'Captcha is invalid.', 'hivepress' ) ] );
			}
		}

		// Validate fields.
		if ( empty( $this->errors ) ) {
			foreach ( static::$fields as $field ) {
				if ( ! $field->validate() ) {
					$this->add_errors( $field->get_errors() );
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
		$output = '<form ' . hp\html_attributes( $this->attributes ) . '>';

		// Render header.
		if ( isset( $this->header ) || isset( static::$description ) ) {
			$output .= '<header class="hp-form__header">';

			if ( isset( $this->header ) ) {
				$output .= $this->header;
			}

			if ( isset( static::$description ) ) {
				$output .= '<p class="hp-form__description">' . hp\sanitize_html( static::$description ) . '</p>';
			}

			$output .= '<div class="hp-form__messages" data-element="messages"></div>';
			$output .= '</header>';
		} else {
			$output .= '<div class="hp-form__messages" data-element="messages"></div>';
		}

		// Render fields.
		$output .= '<div class="hp-form__fields">';

		foreach ( static::$fields as $field ) {
			if ( $field::get_type() !== 'hidden' ) {
				$output .= '<div class="hp-form__field hp-form__field--' . esc_attr( hp\sanitize_slug( $field::get_type() ) ) . '">';

				// Render label.
				if ( $field->get_label() ) {
					$output .= '<label class="hp-form__label">' . esc_html( $field->get_label() ) . '</label>';
				}
			}

			// Render field.
			$output .= $field->render();

			if ( $field::get_type() !== 'hidden' ) {
				$output .= '</div>';
			}
		}

		$output .= '</div>';

		// Render captcha.
		if ( static::$captcha ) {
			$output .= '<div class="hp-form__captcha">';
			$output .= '<div class="g-recaptcha" data-sitekey="' . esc_attr( get_option( 'hp_recaptcha_site_key' ) ) . '"></div>';
			$output .= '</div>';
		}

		// Render footer.
		if ( isset( static::$button ) || isset( $this->footer ) ) {
			$output .= '<div class="hp-form__footer">';

			if ( isset( static::$button ) ) {
				$output .= static::$button->render();
			}

			if ( isset( $this->footer ) ) {
				$output .= $this->footer;
			}

			$output .= '</div>';
		}

		$output .= '</form>';

		return $output;
	}
}
