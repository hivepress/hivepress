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
 * Multiple checkboxes.
 */
class Checkboxes extends Select {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => esc_html__( 'Checkboxes', 'hivepress' ),

				'settings' => [
					'placeholder' => null,
					'multiple'    => null,
				],
			],
			$meta
		);

		parent::init( $meta );
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
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Normalize options.
		if ( ! is_array( $this->options ) ) {
			$this->options = [];
		}

		Field::boot();
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<div ' . hp\html_attributes( $this->attributes ) . '>';

		// Render options.
		$output .= $this->render_options();

		$output .= '</div>';

		return $output;
	}

	/**
	 * Renders checkboxes.
	 *
	 * @param mixed $current Current value.
	 * @param int   $level Nesting level.
	 * @return string
	 */
	protected function render_options( $current = null, $level = 0 ) {
		$output = '';

		// Filter options.
		$options = array_filter(
			$this->options,
			function( $option ) use ( $current ) {
				$parent = hp\get_array_value( $option, 'parent' );

				return ( is_null( $current ) && is_null( $parent ) ) || ( ! is_null( $current ) && $parent === $current );
			}
		);

		// Render options.
		if ( $options ) {
			$output .= '<ul>';

			foreach ( $options as $value => $label ) {
				$output .= '<li>';

				// Get attributes.
				$attributes = [];

				if ( is_array( $label ) ) {
					$attributes = hp\get_array_value( $label, 'attributes', [] );
					$label      = hp\get_array_value( $label, 'label' );
				}

				// Get default value.
				$default = null;

				if ( in_array( $value, (array) $this->value, true ) ) {
					$default = $value;
				}

				// Render option.
				$output .= ( new Checkbox(
					[
						'name'        => $this->name . '[]',
						'caption'     => $label,
						'check_value' => $value,
						'default'     => $default,
						'attributes'  => $attributes,
					]
				) )->render();

				// Render child options.
				$output .= $this->render_options( $value, $level + 1 );

				$output .= '</li>';
			}

			$output .= '</ul>';
		}

		return $output;
	}
}
