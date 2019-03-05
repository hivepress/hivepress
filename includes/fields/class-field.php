<?php
/**
 * Abstract field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

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
	protected $type;

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
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {

		// Set type.
		$args['type'] = strtolower( ( new \ReflectionClass( $this ) )->getShortName() );

		// Filter arguments.
		$args = apply_filters( 'hivepress/fields/field/args', $args );

		// Set properties.
		foreach ( $args as $name => $value ) {
			$this->set_property( $name, $value );
		}
	}

	/**
	 * Sets property.
	 *
	 * @param string $name Property name.
	 * @param mixed  $value Property value.
	 */
	final protected function set_property( $name, $value ) {
		if ( property_exists( $this, $name ) ) {
			if ( method_exists( $this, 'set_' . $name ) ) {
				call_user_func_array( [ $this, 'set_' . $name ], [ $value ] );
			} else {
				$this->$name = $value;
			}
		}
	}

	/**
	 * Gets field type.
	 *
	 * @return string
	 */
	final public function get_type() {
		return $this->type;
	}

	/**
	 * Sets field value.
	 *
	 * @param mixed $value Field value.
	 */
	final public function set_value( $value ) {
		$this->value = $value;

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
	 * Sets default field value.
	 *
	 * @param mixed $value Field value.
	 */
	final protected function set_default( $value ) {
		$this->set_value( $value );
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
	 * Sets field attributes.
	 *
	 * @param array $attributes Field attributes.
	 */
	final public function set_attributes( $attributes ) {
		$this->attributes = $attributes;
	}

	/**
	 * Gets field attributes.
	 *
	 * @return array
	 */
	protected function get_attributes() {
		return $this->attributes;
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
			$this->add_errors( [ sprintf( esc_html__( '%s is required', 'hivepress' ), $this->label ) ] );
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
