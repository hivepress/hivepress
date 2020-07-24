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
 *
 * @class Field
 */
abstract class Field {
	use Traits\Mutator;

	use Traits \Meta {
		set_meta as _set_meta;
	}

	/**
	 * Field arguments.
	 *
	 * @var array
	 */
	protected $args = [];

	/**
	 * Field display type.
	 *
	 * @var string
	 */
	protected $display_type;

	/**
	 * Field display template.
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
	 * Field filter.
	 *
	 * @var mixed
	 */
	protected $filter;

	/**
	 * Disabled flag.
	 *
	 * @var bool
	 */
	protected $disabled = false;

	/**
	 * Required flag.
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
	 * Field attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Class initializer.
	 *
	 * @param array $meta Field meta.
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
						'label'      => esc_html__( 'Description', 'hivepress' ),
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
			 * Filters field meta.
			 *
			 * @filter /fields/{$type}/meta
			 * @description Filters field meta.
			 * @param string $type Field type.
			 * @param array $meta Field meta.
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
			 * Filters field arguments.
			 *
			 * @filter /fields/{$type}
			 * @description Filters field arguments.
			 * @param string $type Field type.
			 * @param array $args Field arguments.
			 * @param object $object Field object.
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
	 * Sets meta values.
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
	 * Sets display template.
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
	 * Gets field label.
	 *
	 * @return string
	 */
	final public function get_label() {
		return $this->label;
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
	 * Gets field display value.
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
	 * Adds field filter.
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
	 * Gets field filter.
	 *
	 * @return mixed
	 */
	final public function get_filter() {
		return $this->filter;
	}

	/**
	 * Updates field filter.
	 */
	final public function update_filter() {
		if ( ! is_null( $this->value ) && static::get_meta( 'filterable' ) ) {
			$this->add_filter();
		}
	}

	/**
	 * Checks disabled flag.
	 *
	 * @return bool
	 */
	final public function is_disabled() {
		return $this->disabled;
	}

	/**
	 * Checks required flag.
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
			$this->add_errors( sprintf( esc_html__( '"%s" field is required.', 'hivepress' ), $this->label ) );
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
		return hp\replace_tokens(
			[
				'label' => '<strong>' . $this->label . '</strong>',
				'value' => '<span>' . $this->get_display_value() . '</span>',
			],
			$this->display_template
		);
	}
}
