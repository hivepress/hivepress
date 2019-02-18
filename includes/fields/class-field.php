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
	 * Value requirement.
	 *
	 * @var bool
	 */
	protected $required = false;

	/**
	 * Validation errors.
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
	 * Class constructor.
	 *
	 * @param array $props Field properties.
	 */
	public function __construct( $props ) {

		// Set type.
		$this->type = strtolower( ( new \ReflectionClass( $this ) )->getShortName() );

		// Set properties.
		foreach ( $props as $prop_name => $prop_value ) {
			if ( property_exists( $this, $prop_name ) ) {
				$this->$prop_name = $prop_value;
			}
		}
	}

	/**
	 * Sanitizes field value.
	 */
	abstract protected function sanitize();

	/**
	 * Validates field value.
	 */
	protected function validate() {
		if ( $this->required && is_null( $this->value ) ) {
			$this->errors[] = 'todo';
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
