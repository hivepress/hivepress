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
	 * Form name.
	 *
	 * @var string
	 */
	private $name;

	/**
	 * Form title.
	 *
	 * @var string
	 */
	private $title;

	/**
	 * Form action.
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Form method.
	 *
	 * @var string
	 */
	private $method = 'POST';

	/**
	 * Form captcha.
	 *
	 * @var bool
	 */
	private $captcha;

	/**
	 * Form attributes.
	 *
	 * @var array
	 */
	private $attributes = [];

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	private $fields = [];

	/**
	 * Form errors.
	 *
	 * @var array
	 */
	private $errors = [];

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {

		// Set name.
		$this->name = strtolower( ( new \ReflectionClass( $this ) )->getShortName() );

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

	// Forbid setting name.
	final private function set_name() {}

	/**
	 * Sets form fields.
	 *
	 * @param array $fields Form fields.
	 */
	final public function set_fields( $fields ) {
		$this->fields = [];

		foreach ( $fields as $field_name => $field_args ) {
			$field_class = '\HivePress\Fields\\' . $field_args['type'];

			$this->fields[ $field_name ] = new $field_class( $field_args );
		}
	}

	/**
	 * Renders form HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<form action="' . esc_url( $this->get_action() ) . '" method="' . esc_url( $this->get_method() ) . '" ' . hp_html_attributes( $this->get_attributes() ) . '>';

		foreach ( $this->get_fields() as $field_name => $field ) {
			$output .= $field->render();
		}

		$output .= '</form>';

		return $output;
	}
}
