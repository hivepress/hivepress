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
	 * Field attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Field errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Required property.
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

		// Set type.
		$args = array_merge( [ 'type' => strtolower( ( new \ReflectionClass( static::class ) )->getShortName() ) ], $args );

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
		 * Filters field arguments.
		 *
		 * @filter /fields/field/args
		 * @description Filters field arguments.
		 * @param array $args Field arguments.
		 */
		$args = apply_filters( 'hivepress/v1/fields/field/args', array_merge( $args, [ 'type' => static::$type ] ) );

		// Set arguments.
		$this->args = $args;

		unset( $args['type'] );

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}

		// Bootstrap properties.
		$this->bootstrap();
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
		static::$settings = [];

		foreach ( $settings as $field_name => $field_args ) {

			// Get field class.
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			if ( class_exists( $field_class ) ) {

				// Create field.
				static::$settings[ $field_name ] = new $field_class( array_merge( $field_args, [ 'name' => $field_name ] ) );
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

		$this->normalize();
		$this->sanitize();
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
	 * Adds field errors.
	 *
	 * @param array $errors Field errors.
	 */
	final protected function add_errors( $errors ) {
		$this->errors = array_merge( $this->errors, $errors );
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
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {

		// Set class.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'hp-field', 'hp-field--' . hp\sanitize_slug( static::$type ) ],
			]
		);

		// Set default value.
		$default = hp\get_array_value( $this->args, 'default' );

		if ( ! is_null( $default ) ) {
			$this->set_value( $default );
		}
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
		if ( $this->required && is_null( $this->value ) ) {
			$this->add_errors( [ sprintf( esc_html__( '%s is required.', 'hivepress' ), $this->label ) ] );
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
