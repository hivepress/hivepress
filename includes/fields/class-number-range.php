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
class Number_Range extends Field {

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
				'title' => esc_html__( 'Number Range', 'hivepress' ),
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		// todo.
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp\html_attributes( $this->get_attributes() ) . '>';

		// Render fields.
		// todo.
		$output .= ( new Number(
			[
				'placeholder' => esc_html__( 'Min', 'hivepress' ),
				'name'        => $this->name . '[]',
				'default'     => null,
			]
		) )->render();

		$output .= ( new Number(
			[
				'placeholder' => esc_html__( 'Max', 'hivepress' ),
				'name'        => $this->name . '[]',
				'default'     => null,
			]
		) )->render();

		$output .= '</div>';

		return $output;
	}
}
