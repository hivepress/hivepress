<?php
/**
 * Abstract field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract field class.
 *
 * @class Field
 */
abstract class Field {

	/**
	 * Field type.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Field name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Field label.
	 *
	 * @var string
	 */
	private $label;

	/**
	 * Field value.
	 *
	 * @var mixed
	 */
	protected $value;

	/**
	 * Value requirement.
	 *
	 * @var bool
	 */
	private $required = false;

	/**
	 * Field attributes.
	 *
	 * @var array
	 */
	private $attributes = [];

	/**
	 * Validation errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {

		// Filter field arguments.
		$args = apply_filters( 'hivepress/fields/field/args', $args );

		// Set type.
		$this->type = strtolower( ( new \ReflectionClass( $this ) )->getShortName() );

		// Set value.
		if ( isset( $args['default'] ) ) {
			$this->set_value( $args['default'] );
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

	// Forbid setting type.
	final private function set_type() {}

	/**
	 * Sets field value.
	 *
	 * @param mixed $value Field value.
	 */
	final public function set_value( $value ) {

		// Set value.
		$this->value = $value;

		// Sanitize value.
		$this->sanitize();
	}

	/**
	 * Sanitizes field value.
	 */
	abstract protected function sanitize();

	/**
	 * Validates field value.
	 */
	public function validate() {
		if ( $this->required && is_null( $this->value ) ) {
			$this->errors[] = hp_sanitize_html( sprintf( __( '%s is required.', 'hivepress' ), '<strong>' . $this->get_label() . '</strong>' ) );
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
