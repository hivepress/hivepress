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
	use Traits\Meta;

	/**
	 * Field meta.
	 *
	 * @var array
	 */
	protected static $meta;

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
	 * Field filter.
	 *
	 * @var mixed
	 */
	protected $filter;

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
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'meta' => [
					'name'     => hp\get_class_name( static::class ),
					'type'     => 'CHAR',

					'settings' => [
						'required' => [
							'label'   => esc_html__( 'Required', 'hivepress' ),
							'caption' => esc_html__( 'Make this field required', 'hivepress' ),
							'type'    => 'checkbox',
							'_order'  => 5,
						],
					],
				],
			],
			$args
		);

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
		$args = hp\merge_arrays(
			[
				'display_type' => static::get_meta( 'name' ),
			],
			$args
		);

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

		// Set optional status.
		if ( ! $this->required ) {
			$this->statuses = hp\merge_arrays( [ 'optional' => esc_html_x( 'optional', 'field', 'hivepress' ) ], $this->statuses );
		}

		// Set class.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-field', 'hp-field--' . hp\sanitize_slug( $this->get_display_type() ) ],
			]
		);
	}

	/**
	 * Sets field meta.
	 *
	 * @param array $meta Field meta.
	 */
	final protected static function set_meta( $meta ) {

		// Set settings.
		if ( isset( $meta['settings'] ) ) {
			$settings = [];

			foreach ( hp\sort_array( $meta['settings'] ) as $name => $args ) {

				// Create field.
				$field = hp\create_class_instance( '\HivePress\Fields\\' . $args['type'], [ array_merge( $args, [ 'name' => $name ] ) ] );

				// Add field.
				if ( $field ) {
					$settings[ $name ] = $field;
				}
			}

			$meta['settings'] = $settings;
		}

		static::$meta = $meta;
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
	 * Gets display type.
	 *
	 * @return string
	 */
	final public function get_display_type() {
		return $this->display_type;
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
	final public function set_value( $value ) {
		$this->value  = $value;
		$this->filter = null;

		if ( ! is_null( $this->value ) ) {
			$this->normalize();

			if ( ! is_null( $this->value ) ) {
				$this->sanitize();

				if ( ! is_null( $this->value ) && static::get_meta( 'filterable' ) ) {
					$this->add_filter();
				}
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
	 * Adds field filter.
	 */
	final protected function add_filter() {
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
}
