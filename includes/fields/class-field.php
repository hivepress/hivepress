<?php
/**
 * Abstract field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;
use HivePress\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract field class.
 */
abstract class Field {
	use Traits\Mutator;
	use Traits\Context;

	use Traits\Meta {
		set_meta as _set_meta;
	}

	/**
	 * Field arguments.
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Display type.
	 *
	 * @var string
	 */
	protected $display_type;

	/**
	 * Display template.
	 *
	 * @var string
	 */
	protected $display_template;

	/**
	 * Field name.
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Field label.
	 *
	 * @var string
	 */
	protected $label;

	/**
	 * Field description.
	 *
	 * @var string
	 */
	protected $description;

	/**
	 * Field statuses.
	 *
	 * @var array
	 */
	protected $statuses = [];

	/**
	 * Field value.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Parent field value.
	 *
	 * @var mixed
	 */
	protected $parent_value;

	/**
	 * SQL filter.
	 *
	 * @var mixed
	 */
	protected $filter;

	/**
	 * Disable this field?
	 *
	 * @var bool
	 */
	protected $disabled = false;

	/**
	 * Is value required?
	 *
	 * @var bool
	 */
	protected $required = false;

	/**
	 * Field errors.
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
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'name'       => hp\get_class_name( static::class ),
				'type'       => 'CHAR',
				'editable'   => true,
				'filterable' => false,
				'sortable'   => false,

				'settings'   => [
					'required'    => [
						'label'    => esc_html_x( 'Required', 'field', 'hivepress' ),
						'caption'  => esc_html__( 'Make this field required', 'hivepress' ),
						'type'     => 'checkbox',
						'_context' => 'edit',
						'_order'   => 10,
					],

					'description' => [
						'label'      => hivepress()->translator->get_string( 'description' ),
						'type'       => 'textarea',
						'max_length' => 2048,
						'html'       => true,
						'_context'   => 'edit',
						'_order'     => 20,
					],
				],
			],
			$meta
		);

		// Filter meta.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters the field class meta. The class meta stores properties related to the field type rather than a specific field instance. For example, it stores the field settings displayed for an attribute. The dynamic part of the hook refers to the field type (e.g. `textarea`). You can check the available field types in the `includes/fields` directory of HivePress.
			 *
			 * @hook hivepress/v1/fields/{field_type}/meta
			 * @param {array} $meta Class meta values.
			 * @return {array} Class meta values.
			 */
			$meta = apply_filters( 'hivepress/v1/fields/' . hp\get_class_name( $class ) . '/meta', $meta );
		}

		// Set meta.
		static::set_meta( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'display_type'     => hp\get_class_name( static::class ),
				'display_template' => '%value%',
			],
			$args
		);

		// Filter properties.
		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters the field properties. The dynamic part of the hook refers to the field type (e.g. `textarea`). You can check the available field types in the `includes/fields` directory of HivePress.
			 *
			 * @hook hivepress/v1/fields/{field_type}
			 * @param {array} $props Field properties.
			 * @param {object} $field Field object.
			 * @return {array} Field properties.
			 */
			$args = apply_filters( 'hivepress/v1/fields/' . hp\get_class_name( $class ), $args, $this );
		}

		// Set arguments.
		$this->args = $args;

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->boot();
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Set default value.
		if ( isset( $this->args['default'] ) ) {
			$this->set_value( $this->args['default'] );
		}

		// Set optional status.
		if ( ! $this->required ) {
			$this->statuses = array_merge( [ 'optional' => esc_html_x( 'optional', 'field', 'hivepress' ) ], $this->statuses );
		}

		$this->statuses = array_filter( $this->statuses );

		// Set attributes.
		if ( 'hidden' === $this->display_type ) {
			$this->attributes = array_filter(
				$this->attributes,
				function( $name ) {
					return strpos( $name, 'data-' ) === 0;
				},
				ARRAY_FILTER_USE_KEY
			);
		}

		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-field', 'hp-field--' . hp\sanitize_slug( $this->display_type ) ],
			]
		);
	}

	/**
	 * Sets class meta values.
	 *
	 * @param array $meta Meta values.
	 */
	final protected static function set_meta( $meta ) {

		// Get settings.
		$settings = array_filter( hp\get_array_value( $meta, 'settings', [] ) );

		if ( $settings ) {
			$meta['settings'] = [];

			foreach ( $settings as $name => $args ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $args['type'], [ array_merge( $args, [ 'name' => $name ] ) ] );

				// Add field.
				if ( $field ) {
					$meta['settings'][ $name ] = $field;
				}
			}
		}

		static::_set_meta( $meta );
	}

	/**
	 * Gets field arguments.
	 *
	 * @return array
	 */
	final public function get_args() {
		return $this->args;
	}

	/**
	 * Gets field argument.
	 *
	 * @param string $name Argument name.
	 * @return mixed
	 */
	final public function get_arg( $name ) {
		return hp\get_array_value( $this->args, $name );
	}

	/**
	 * Gets display type.
	 *
	 * @return string
	 */
	final public function get_display_type() {
		return $this->display_type;
	}

	/**
	 * Sets field display template.
	 *
	 * @param string $display_template Display template.
	 */
	protected function set_display_template( $display_template ) {
		$this->display_template = $display_template;
	}

	/**
	 * Gets field name.
	 *
	 * @return string
	 */
	final public function get_name() {
		return $this->name;
	}

	/**
	 * Gets field slug.
	 *
	 * @return string
	 */
	final public function get_slug() {
		return hp\sanitize_slug( $this->name );
	}

	/**
	 * Gets field label.
	 *
	 * @param mixed $default Default label.
	 * @return string
	 */
	final public function get_label( $default = null ) {
		$label = $this->label;

		if ( ! $label && $default ) {
			$label = true === $default ? $this->name : $default;
		}

		return $label;
	}

	/**
	 * Gets field description.
	 *
	 * @return string
	 */
	final public function get_description() {
		return $this->description;
	}

	/**
	 * Gets field statuses.
	 *
	 * @return array
	 */
	final public function get_statuses() {
		return $this->statuses;
	}

	/**
	 * Sets field value.
	 *
	 * @param mixed $value Field value.
	 * @return object
	 */
	public function set_value( $value ) {
		$this->value  = $value;
		$this->filter = null;

		if ( ! is_null( $this->value ) ) {
			$this->normalize();

			if ( ! is_null( $this->value ) ) {
				$this->sanitize();

				$this->update_filter();
			}
		}

		return $this;
	}

	/**
	 * Gets field value.
	 *
	 * @return mixed
	 */
	final public function get_value() {
		return $this->value;
	}

	/**
	 * Gets field value for display.
	 *
	 * @return mixed
	 */
	public function get_display_value() {
		return $this->value;
	}

	/**
	 * Sets parent field value.
	 *
	 * @param mixed $value Field value.
	 * @return object
	 */
	public function set_parent_value( $value ) {
		$this->parent_value = $value;

		return $this;
	}

	/**
	 * Adds SQL filter.
	 */
	protected function add_filter() {
		$this->filter = [
			'name'     => $this->name,
			'type'     => static::get_meta( 'type' ),
			'value'    => $this->value,
			'operator' => '=',
		];
	}

	/**
	 * Gets SQL filter.
	 *
	 * @return mixed
	 */
	final public function get_filter() {
		return $this->filter;
	}

	/**
	 * Updates SQL filter.
	 *
	 * @param bool $force Force update?
	 */
	final public function update_filter( $force = false ) {
		if ( $force || ( ! is_null( $this->value ) && static::get_meta( 'filterable' ) ) ) {
			$this->add_filter();
		}
	}

	/**
	 * Checks if field is disabled.
	 *
	 * @return bool
	 */
	final public function is_disabled() {
		return $this->disabled;
	}

	/**
	 * Checks if field is required.
	 *
	 * @return bool
	 */
	final public function is_required() {
		return $this->required;
	}

	/**
	 * Adds field errors.
	 *
	 * @param mixed $errors Field errors.
	 */
	final protected function add_errors( $errors ) {
		$this->errors = array_merge( $this->errors, (array) $errors );
	}

	/**
	 * Gets field errors.
	 *
	 * @return array
	 */
	final public function get_errors() {
		return $this->errors;
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		if ( '' === $this->value ) {
			$this->value = null;
		}
	}

	/**
	 * Sanitizes field value.
	 */
	abstract protected function sanitize();

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		$this->errors = [];

		if ( $this->required && is_null( $this->value ) ) {

			/* translators: %s: field label. */
			$this->errors['required'] = sprintf( esc_html__( '"%s" field is required.', 'hivepress' ), $this->get_label( true ) );
		}

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	abstract public function render();

	/**
	 * Displays field HTML.
	 *
	 * @return string
	 */
	public function display() {

		// Check shortcodes.
		$shortcode = hp\has_shortcode( $this->display_template );

		// Get value.
		$value = $this->get_display_value();

		foreach ( hp\get_class_parents( static::class ) as $class ) {

			/**
			 * Filters the field display value. The dynamic part of the hook refers to the field type (e.g. `textarea`). You can check the available field types in the `includes/fields` directory of HivePress.
			 *
			 * @hook hivepress/v1/fields/{field_type}/display_value
			 * @param {string} $value Display value.
			 * @return {string} Display value.
			 */
			$value = apply_filters( 'hivepress/v1/fields/' . hp\get_class_name( $class ) . '/display_value', $value, $this );
		}

		if ( $shortcode ) {
			$value = strip_shortcodes( $value );
		}

		// Render output.
		$output = hp\replace_tokens(
			array_merge(
				$this->context,
				[
					'label' => '<strong>' . $this->label . '</strong>',
					'value' => $value,
				]
			),
			$this->display_template,
			true
		);

		if ( $shortcode ) {
			$output = do_shortcode( $output );
		}

		return $output;
	}
}
