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
	protected $name;

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
	 * Class constructor.
	 */
	public function __construct() {

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
