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

	/**
	 * Field type.
	 *
	 * @var string
	 */
	protected static $type;

	/**
	 * Field display type.
	 *
	 * @var string
	 */
	protected static $display_type;

	/**
	 * Field title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Field settings.
	 *
	 * @var array
	 */
	protected static $settings = [];

	/**
	 * Field arguments.
	 *
	 * @var array
	 */
	protected $args = [];

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
	 * Field value.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Field errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Field filters.
	 *
	 * @var mixed
	 */
	protected $filters = false;

	/**
	 * Field statuses.
	 *
	 * @var array
	 */
	protected $statuses = [];

	/**
	 * Field attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Required flag.
	 *
	 * @var bool
	 */
	protected $required = false;

	/**
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'settings' => [
					'required' => [
						'label'   => esc_html__( 'Required', 'hivepress' ),
						'caption' => esc_html__( 'Make this field required', 'hivepress' ),
						'type'    => 'checkbox',
						'order'   => 5,
					],
				],
			],
			$args
		);

		/**
		 * Filters field arguments.
		 *
		 * @filter /fields/{$type}
		 * @description Filters field arguments.
		 * @param string $type Field type or "field" to filter all fields.
		 * @param array $args Field arguments.
		 */
		$args = apply_filters( 'hivepress/v1/fields/field', $args, hp\get_class_name( static::class ) );
		$args = apply_filters( 'hivepress/v1/fields/' . hp\get_class_name( static::class ), $args );

		// Set properties.
		foreach ( $args as $name => $value ) {
			static::set_static_property( $name, $value );
		}
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {

		/**
		 * Filters field instance arguments.
		 *
		 * @filter /fields/{$type}/args
		 * @description Filters field instance arguments.
		 * @param string $type Field type or "field" to filter all fields.
		 * @param array $args Field instance arguments.
		 */
		$args = apply_filters( 'hivepress/v1/fields/field/args', $args, hp\get_class_name( static::class ) );
		$args = apply_filters( 'hivepress/v1/fields/' . hp\get_class_name( static::class ) . '/args', $args );

		// Set arguments.
		$this->args = $args;

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->bootstrap();
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {

		// Set default value.
		$this->set_value( hp\get_array_value( $this->args, 'default' ) );

		// Set filters.
		if ( false !== $this->filters ) {
			$this->filters = [];
		}

		// Set optional status.
		if ( ! $this->required ) {
			$this->statuses = hp\merge_arrays( [ 'optional' => esc_html_x( 'optional', 'field', 'hivepress' ) ], $this->statuses );
		}

		// Set class.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-field', 'hp-field--' . hp\sanitize_slug( static::get_display_type() ) ],
			]
		);
	}

	/**
	 * Gets field type.
	 *
	 * @return string
	 */
	final public static function get_type() {
		return static::$type;
	}

	/**
	 * Gets field display type.
	 *
	 * @return string
	 */
	final public static function get_display_type() {
		return static::$display_type ? static::$display_type : hp\get_class_name( static::class );
	}

	/**
	 * Gets field title.
	 *
	 * @return string
	 */
	final public static function get_title() {
		return static::$title;
	}

	/**
	 * Sets field settings.
	 *
	 * @param array $settings Field settings.
	 */
	final protected static function set_settings( $settings ) {
		foreach ( $settings as $field_name => $field_args ) {

			// Create field.
			$field = hp\create_class_instance( '\HivePress\Fields\\' . $field_args['type'], [ array_merge( $field_args, [ 'name' => $field_name ] ) ] );

			if ( ! is_null( $field ) ) {
				static::$settings[ $field_name ] = $field;
			}
		}
	}

	/**
	 * Gets field settings.
	 *
	 * @return array
	 */
	final public static function get_settings() {
		return static::$settings;
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
	 * Sets field value.
	 *
	 * @param mixed $value Field value.
	 */
	final public function set_value( $value ) {
		$this->value = $value;

		if ( ! is_null( $this->value ) ) {
			$this->normalize();

			if ( ! is_null( $this->value ) ) {
				$this->sanitize();
			}
		}

		if ( false !== $this->filters ) {
			if ( ! is_null( $this->value ) ) {
				$this->add_filters();
			} else {
				$this->filters = [];
			}
		}
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
	 * Adds field filters.
	 */
	protected function add_filters() {
		$this->filters = [
			'name'     => $this->name,
			'value'    => $this->value,
			'operator' => '=',
		];

		if ( static::get_type() ) {
			$this->filters['type'] = static::get_type();
		}
	}

	/**
	 * Gets field filters.
	 *
	 * @return mixed
	 */
	final public function get_filters() {
		return $this->filters;
	}

	/**
	 * Adds field errors.
	 *
	 * @param array $errors Field errors.
	 */
	final protected function add_errors( $errors ) {
		$this->errors = array_unique( array_merge( $this->errors, $errors ) );
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
	 * Gets field statuses.
	 *
	 * @return array
	 */
	final public function get_statuses() {
		return $this->statuses;
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
			$this->add_errors( [ sprintf( esc_html__( '"%s" field is required.', 'hivepress' ), $this->label ) ] );
		}

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	abstract public function render();
}
