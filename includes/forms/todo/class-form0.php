<?php
/**
 * Abstract form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Abstract form class.
 *
 * @class Form
 */
abstract class Form {

	/**
	 * Form message.
	 *
	 * @var string
	 */
	private $message;

	/**
	 * Form redirect.
	 *
	 * @var mixed
	 */
	protected $redirect;

	/**
	 * Sets field values.
	 *
	 * @param array $values Field values.
	 */
	final public function set_values( $values ) {
		foreach ( $values as $field_name => $value ) {
			if ( isset( $this->fields[ $field_name ] ) && '' !== $value ) {
				$this->fields[ $field_name ]->set_value( $value );
			}
		}
	}

	/**
	 * Sets field value.
	 *
	 * @param string $name Field name.
	 * @param mixed  $value Field value.
	 */
	final private function set_value( $name, $value ) {
		if ( isset( $this->get_fields()[ $name ] ) ) {
			$this->get_fields()[ $name ]->set_value( $value );
		}
	}

	/**
	 * Gets field values.
	 *
	 * @return array
	 */
	final public function get_values() {
		$values = [];

		foreach ( $this->get_fields() as $field_name => $field ) {
			$values[ $field_name ] = $field->get_value();
		}

		return $values;
	}
}
