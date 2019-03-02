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
	 * Required property.
	 *
	 * @var bool
	 */
	protected $required = false;

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
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {

		// Set type.
		$args['type'] = strtolower( ( new \ReflectionClass( $this ) )->getShortName() );

		// Filter arguments.
		$args = apply_filters( 'hivepress/fields/field/args', $args );

		// todo remove.
		unset( $args['type'] );
		unset( $args['order'] );

		// Set properties.
		foreach ( $args as $name => $value ) {
			call_user_func_array( [ $this, 'set_' . $name ], [ $value ] );
		}
	}

	/**
	 * Sets field name.
	 *
	 * @param string $name Field name.
	 */
	final protected function set_name( $name ) {
		$this->name = $name;
	}

	/**
	 * Sets field label.
	 *
	 * @param string $label Field label.
	 */
	final protected function set_label( $label ) {
		$this->label = $label;
	}

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
	 * Sets default field value.
	 *
	 * @param mixed $value Field value.
	 */
	final protected function set_default( $value ) {
		$this->set_value( $value );
	}

	/**
	 * Sets required property.
	 *
	 * @param bool $required Required property.
	 */
	final protected function set_required( $required ) {
		$this->required = boolval( $required );
	}

	/**
	 * Sets field attributes.
	 *
	 * @param array $attributes Field attributes.
	 */
	final protected function set_attributes( $attributes ) {
		$this->attributes = (array) $attributes;
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
	 * Adds field errors.
	 *
	 * @param array $errors Field errors.
	 */
	final protected function add_errors( $errors ) {
		$this->errors = array_merge( $this->errors, $errors );
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
