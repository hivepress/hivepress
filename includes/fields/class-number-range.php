<?php
/**
 * Number range field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Number range field class.
 *
 * @class Number_Range
 */
class Number_Range extends Number {

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
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'    => esc_html__( 'Number Range', 'hivepress' ),

				'settings' => [
					'placeholder' => null,
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Gets field attributes.
	 *
	 * @return array
	 */
	protected function get_attributes() {
		// todo.
		return $this->attributes;
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		// todo.
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		// todo.
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp\html_attributes( $this->get_attributes() ) . '>';

		// Get values.
		$values = (array) $this->value;

		// Render fields.
		$field_args = [
			'name'      => $this->name . '[]',
			'decimals'  => $this->decimals,
			'min_value' => $this->min_value,
			'max_value' => $this->max_value,
		];

		$output .= ( new Number(
			array_merge(
				$field_args,
				[
					'placeholder' => esc_html__( 'Min', 'hivepress' ),
					'default'     => reset( $values ),
				]
			)
		) )->render();

		$output .= ( new Number(
			array_merge(
				$field_args,
				[
					'placeholder' => esc_html__( 'Max', 'hivepress' ),
					'default'     => end( $values ),
				]
			)
		) )->render();

		$output .= '</div>';

		return $output;
	}
}
