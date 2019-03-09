<?php
/**
 * Checkboxes field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Checkboxes field class.
 *
 * @class Checkboxes
 */
class Checkboxes extends Select {

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
	 * @var string
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
				'title' => esc_html__( 'Checkboxes', 'hivepress' ),
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'multiple' => true,
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp\html_attributes( $this->get_attributes() ) . '>';

		foreach ( $this->options as $value => $label ) {
			$output .= ( new Checkbox(
				[
					'name'    => $this->name . '_' . $value,
					'caption' => $label,
				]
			) )->render();
		}

		$output .= '</div>';

		return $output;
	}
}
