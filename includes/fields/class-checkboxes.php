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
	 * Class initializer.
	 *
	 * @param array $meta Field meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => esc_html__( 'Checkboxes', 'hivepress' ),

				'settings' => [
					'multiple' => null,
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
	 * Renders field options.
	 *
	 * @param mixed $current Current value.
	 * @return string
	 */
	protected function render_options( $current = null ) {
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

			foreach ( $options as $value => $option ) {
				$output .= '<li>';

				// Get label.
				$label = $option;

				if ( is_array( $label ) ) {
					$label = hp\get_array_value( $label, 'label' );
				}

				// Get default value.
				$default = null;

				if ( in_array( (string) $value, (array) $this->value, true ) ) {
					$default = $value;
				}

				// Render option.
				$output .= ( new Checkbox(
					[
						'name'          => $this->name . '[]',
						'caption'       => $label,
						'checked_value' => $value,
						'default'       => $default,
					]
				) )->render();

				// Render child options.
				$output .= $this->render_options( $value );

				$output .= '</li>';
			}

			$output .= '</ul>';
		}

		return $output;
	}
}
