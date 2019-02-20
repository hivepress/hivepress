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
	protected $title;

	/**
	 * Form action.
	 *
	 * @var string
	 */
	protected $action = '';

	/**
	 * Form method.
	 *
	 * @var string
	 */
	protected $method = 'POST';

	/**
	 * Form attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected $fields = [];

	/**
	 * Form errors.
	 *
	 * @var array
	 */
	protected $errors = [];

	/**
	 * Form captcha.
	 *
	 * @var bool
	 */
	protected $captcha;

	/**
	 * Class constructor.
	 */
	public function __construct() {

		// todo
		foreach ( $this->fields as $field_id => $field_args ) {
			$field_class               = '\HivePress\Fields\\' . $field_args['type'];
			$this->fields[ $field_id ] = new $field_class( $field_args );
		}
	}

	/**
	 * Renders form HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<form action="' . esc_url( $this->action ) . '" method="' . esc_url( $this->method ) . '" ' . hp_html_attributes( $this->attributes ) . '>';

		// Render fields.
		foreach ( $this->fields as $field_name => $field ) {
			$output .= $field->render();
		}

		$output .= '</form>';

		return $output;
	}
}
