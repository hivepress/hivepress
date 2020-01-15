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
	use Traits\Meta;

	/**
	 * Form description.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Form message.
	 *
	 * @var string
	 */
	protected $message;

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
	 * Form redirect.
	 *
	 * @var mixed
	 */
	protected $redirect;

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
	 * Form attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

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
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
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
			 * Filters form meta.
			 *
			 * @filter /forms/{$name}/meta
			 * @description Filters form meta.
			 * @param string $name Form name.
			 * @param array $meta Form meta.
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
						'class' => [ 'hp-form__button', 'button', 'alt' ],
					],
				],
			],
			$args
		);

		// Filter properties.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters form arguments.
			 *
			 * @filter /forms/{$name}
			 * @description Filters form arguments.
			 * @param string $name Form name.
			 * @param array $args Form arguments.
			 * @param object $object Form object.
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

		// Set component.
		$attributes['data-component'] = 'form';

		// Set class.
		$attributes['class'] = [ 'hp-form', 'hp-form--' . hp\sanitize_slug( static::get_meta( 'name' ) ) ];

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );
	}

	/**
	 * Gets form method.
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

			// Create field.
			$field = hp\create_class_instance( '\HivePress\Fields\\' . $args['type'], [ array_merge( $args, [ 'name' => $name ] ) ] );

			// Add field.
			if ( $field ) {
				$this->fields[ $name ] = $field;
			}
		}
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
	 * @return object
	 */
	final public function set_values( $values ) {
		foreach ( $values as $name => $value ) {
			$this->set_value( $name, $value );
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

		foreach ( array_keys( $this->fields ) as $name ) {
			$values[ $name ] = $this->get_value( $name );
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
	 * Validates field values.
	 *
	 * @return bool
	 */
	final public function validate() {
		$this->errors = [];

		// Validate fields.
		foreach ( $this->fields as $field ) {
			if ( ! $field->validate() ) {
				$this->add_errors( $field->get_errors() );
			}
		}

		// Filter errors.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters form errors.
			 *
			 * @filter /forms/{$name}/errors
			 * @description Filters form errors.
			 * @param string $name Form name.
			 * @param array $errors Form errors.
			 * @param object $object Form object.
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
				$output .= '<p class="hp-form__description">' . hp\sanitize_html( $this->description ) . '</p>';
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
						$output .= '<label class="hp-form__label"><span>' . esc_html( $field->get_label() ) . '</span>';

						if ( $field->get_statuses() ) {
							$output .= ' <small>(' . esc_html( implode( ', ', $field->get_statuses() ) ) . ')</small>';
						}

						$output .= '</label>';
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
