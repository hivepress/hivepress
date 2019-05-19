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
				'title'    => esc_html__( 'Checkboxes', 'hivepress' ),
				'settings' => [
					'multiple' => null,
				],
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

		// Set multiple property.
		$args['multiple'] = true;

		parent::__construct( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {
		Field::bootstrap();
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
			(array) $this->options,
			function( $option ) use ( $current ) {
				$parent = hp\get_array_value( $option, 'parent' );

				return ( is_null( $current ) && is_null( $parent ) ) || ( ! is_null( $current ) && $parent === $current );
			}
		);

		// Render options.
		if ( ! empty( $options ) ) {
			$output .= '<ul>';

			foreach ( $options as $value => $option ) {
				$output .= '<li>';

				// Get label.
				$label = $option;

				if ( is_array( $option ) ) {
					$label = $option['label'];
				}

				// Get default value.
				$default = null;

				if ( in_array( (string) $value, array_map( 'strval', (array) $this->value ), true ) ) {
					$default = $value;
				}

				// Render option.
				$output .= ( new Checkbox(
					[
						'name'    => $this->name . '[]',
						'caption' => $label,
						'sample'  => $value,
						'default' => $default,
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
