<?php
/**
 * Radio field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Radio field class.
 *
 * @class Radio
 */
class Radio extends Select {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Field meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => esc_html__( 'Radio Buttons', 'hivepress' ),

				'settings' => [
					'multiple'        => null,
					'filter_operator' => null,
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
				'multiple' => false,
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

				// Get label.
				if ( is_array( $label ) ) {
					$label = hp\get_array_value( $label, 'label' );
				}

				// Get ID.
				$id = $this->name . '_' . uniqid();

				// Render option.
				$output .= '<label for="' . esc_attr( $id ) . '"><input type="radio" name="' . esc_attr( $this->name ) . '" id="' . esc_attr( $id ) . '" value="' . esc_attr( $value ) . '" ' . checked( $this->value, $value, false ) . '><span>' . esc_html( $label ) . '</span></label>';

				// Render child options.
				$output .= $this->render_options( $value, $level + 1 );

				$output .= '</li>';
			}

			$output .= '</ul>';
		}

		return $output;
	}
}
