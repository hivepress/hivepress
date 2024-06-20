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
 */
abstract class Form {
	use Traits\Mutator;
	use Traits\Meta;

	/**
	 * Form description.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Success message.
	 *
	 * @var string
	 */
	protected $message;

	/**
	 * Action URL.
	 *
	 * @var string
	 */
	protected $action = '';

	/**
	 * HTTP method.
	 *
	 * @var string
	 */
	protected $method = 'POST';

	/**
	 * Redirect on success?
	 *
	 * @var mixed
	 */
	protected $redirect;

	/**
	 * Reset on success?
	 *
	 * @var bool
	 */
	protected $reset = false;

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * Form button.
	 *
	 * @var object
	 */
	protected $button;

	/**
	 * Form errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * HTML attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Header HTML.
	 *
	 * @var string
	 */
	protected $header;

	/**
	 * Footer HTML.
	 *
	 * @var string
	 */
	protected $footer;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'name' => hp\get_class_name( static::class ),
			],
			$meta
		);

		// Filter meta.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters the form class meta. The class meta stores properties related to the form type rather than a specific form instance. For example, it stores the form captcha settings. The dynamic part of the hook refers to the form name (e.g. `listing_update`). You can check the available forms in the `includes/forms` directory of HivePress.
			 *
			 * @hook hivepress/v1/forms/{form_name}/meta
			 * @param {array} $meta Class meta values.
			 * @return {array} Class meta values.
			 */
			$meta = apply_filters( 'hivepress/v1/forms/' . hp\get_class_name( $class ) . '/meta', $meta );
		}

		// Set meta.
		static::set_meta( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'button' => [
					'label'        => esc_html__( 'Submit', 'hivepress' ),
					'display_type' => 'submit',

					'attributes'   => [
						'class' => [ 'hp-form__button', 'button-primary', 'alt' ],
					],
				],
			],
			$args
		);

		// Filter properties.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters the form properties. The dynamic part of the hook refers to the form name (e.g. `listing_update`). You can check the available forms in the `includes/forms` directory of HivePress.
			 *
			 * @hook hivepress/v1/forms/{form_name}
			 * @param {array} $props Form properties.
			 * @param {object} $form Form object.
			 * @return {array} Form properties.
			 */
			$args = apply_filters( 'hivepress/v1/forms/' . hp\get_class_name( $class ), $args, $this );
		}

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->boot();
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function boot() {
		$attributes = [];

		// Set message.
		if ( $this->message ) {
			$attributes['data-message'] = $this->message;
		}

		// Set action.
		if ( strpos( $this->action, get_rest_url() ) === 0 ) {
			$attributes['action']      = '#';
			$attributes['data-action'] = esc_url( $this->action );
		} else {
			$attributes['action'] = esc_url( $this->action );
		}

		// Set method.
		if ( ! in_array( $this->method, [ 'GET', 'POST' ], true ) ) {
			$attributes['method']      = 'POST';
			$attributes['data-method'] = $this->method;
		} else {
			$attributes['method'] = $this->method;
		}

		// Set redirect.
		if ( $this->redirect ) {
			if ( is_bool( $this->redirect ) ) {
				$attributes['data-redirect'] = 'true';
			} else {
				$attributes['data-redirect'] = esc_url( $this->redirect );
			}
		}

		// Set reset.
		if ( $this->reset ) {
			$attributes['data-reset'] = 'true';
		}

		// Set component.
		$attributes['data-component'] = 'form';

		// Set attributes.
		$attributes['class'] = [ 'hp-form', 'hp-form--' . hp\sanitize_slug( static::get_meta( 'name' ) ) ];

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );
	}

	/**
	 * Gets HTTP method.
	 *
	 * @return string
	 */
	final public function get_method() {
		return $this->method;
	}

	/**
	 * Sets form fields.
	 *
	 * @param array $fields Form fields.
	 */
	protected function set_fields( $fields ) {
		$this->fields = [];

		foreach ( hp\sort_array( $fields ) as $name => $args ) {
			if ( isset( $args['type'] ) ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $args['type'], [ array_merge( $args, [ 'name' => $name ] ) ] );

				// Add field.
				if ( $field ) {
					$this->fields[ $name ] = $field;
				}
			}
		}
	}

	/**
	 * Gets form fields.
	 *
	 * @return array
	 */
	final public function get_fields() {
		return $this->fields;
	}

	/**
	 * Sets form button.
	 *
	 * @param array $button Button arguments.
	 */
	final protected function set_button( $button ) {
		if ( $button ) {
			$this->button = new Fields\Button( $button );
		}
	}

	/**
	 * Adds form errors.
	 *
	 * @param array $errors Form errors.
	 */
	final protected function add_errors( $errors ) {
		$this->errors = array_merge( $this->errors, (array) $errors );
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
	 * @param bool  $override Override only passed values?
	 * @return object
	 */
	public function set_values( $values, $override = false ) {
		$names = [];

		if ( $override ) {
			$names = array_keys( $values );
		} else {
			$names = array_keys( $this->fields );
		}

		foreach ( $names as $name ) {
			$this->set_value( $name, hp\get_array_value( $values, $name ) );
		}

		return $this;
	}

	/**
	 * Sets field value.
	 *
	 * @param string $name Field name.
	 * @param mixed  $value Field value.
	 * @return object
	 */
	final public function set_value( $name, $value ) {
		if ( isset( $this->fields[ $name ] ) ) {
			$this->fields[ $name ]->set_value( $value );
		}

		return $this;
	}

	/**
	 * Gets field values.
	 *
	 * @return array
	 */
	final public function get_values() {
		$values = [];

		foreach ( $this->fields as $name => $field ) {
			if ( ! $field->is_disabled() ) {
				$values[ $name ] = $field->get_value();
			}
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
		$field = hp\get_array_value( $this->fields, $name );

		if ( $field && ! $field->is_disabled() ) {
			return $field->get_value();
		}
	}

	/**
	 * Validates field values.
	 *
	 * @return bool
	 */
	final public function validate() {
		$this->errors = [];

		// Validate fields.
		foreach ( $this->fields as $field ) {
			if ( ! $field->is_disabled() && ! $field->validate() ) {
				$this->add_errors( $field->get_errors() );
			}
		}

		// Filter errors.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters the form validation errors. With this hook, you can implement custom validation checks and add new error messages to the filtered array. The dynamic part of the hook refers to the form name (e.g. `listing_update`). You can check the available forms in the `includes/forms` directory of HivePress.
			 *
			 * @hook hivepress/v1/forms/{form_name}/errors
			 * @param {array} $errors Form errors.
			 * @param {object} $form Form object.
			 * @return {array} Form errors.
			 */
			$this->errors = apply_filters( 'hivepress/v1/forms/' . hp\get_class_name( $class ) . '/errors', $this->errors, $this );
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
		if ( $this->description || $this->header ) {
			$output .= '<div class="hp-form__header">';

			if ( $this->description ) {
				$output .= '<p class="hp-form__description">' . do_shortcode( wp_kses_post( $this->description ) ) . '</p>';
			}

			if ( $this->header ) {
				$output .= $this->header;
			}

			$output .= '<div class="hp-form__messages" data-component="messages"></div>';
			$output .= '</div>';
		} else {
			$output .= '<div class="hp-form__messages" data-component="messages"></div>';
		}

		// Render fields.
		if ( $this->fields ) {
			$output .= '<div class="hp-form__fields">';

			foreach ( $this->fields as $field ) {
				if ( $field->get_display_type() !== 'hidden' ) {
					$output .= '<div class="hp-form__field hp-form__field--' . esc_attr( hp\sanitize_slug( $field->get_display_type() ) ) . '">';

					// Render label.
					if ( $field->get_label() ) {

						// @deprecated "hp-form__label" class since version 1.3.2.
						$output .= '<label class="hp-field__label hp-form__label"><span>' . esc_html( $field->get_label() ) . '</span>';

						if ( $field->get_statuses() ) {
							$output .= ' <small>(' . esc_html( implode( ', ', $field->get_statuses() ) ) . ')</small>';
						}

						$output .= '</label>';
					}

					// Render description.
					if ( $field->get_description() ) {
						$output .= '<div class="hp-field__description">' . do_shortcode( wp_kses_post( $field->get_description() ) ) . '</div>';
					}
				}

				// Render field.
				$output .= $field->render();

				if ( $field->get_display_type() !== 'hidden' ) {
					$output .= '</div>';
				}
			}

			$output .= '</div>';
		}

		// Render footer.
		if ( $this->button || $this->footer ) {
			$output .= '<div class="hp-form__footer">';

			if ( $this->button ) {
				$output .= $this->button->render();
			}

			if ( $this->footer ) {
				$output .= $this->footer;
			}

			$output .= '</div>';
		}

		$output .= '</form>';

		return $output;
	}
}
