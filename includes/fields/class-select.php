<?php
/**
 * Select field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Select field class.
 *
 * @class Select
 */
class Select extends Field {

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
	 * Field placeholder.
	 *
	 * @var string
	 */
	protected $placeholder = '&mdash;';

	/**
	 * Select options.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Multiple property.
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'    => esc_html__( 'Select', 'hivepress' ),

				'settings' => [
					'multiple' => [
						'label'   => esc_html__( 'Multiple', 'hivepress' ),
						'caption' => esc_html__( 'Allow multiple selection', 'hivepress' ),
						'type'    => 'checkbox',
						'order'   => 10,
					],

					'options'  => [
						'type' => 'hidden',
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {
		$attributes = [];

		// Set required property.
		if ( $this->required ) {
			$attributes['required'] = true;
		}

		// Set multiple property.
		if ( $this->multiple ) {
			$attributes['multiple'] = true;
		}

		// Add default option.
		if ( isset( $this->placeholder ) && ! $this->multiple ) {
			$this->options = [ '' => $this->placeholder ] + $this->options;
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		if ( [] === $this->value ) {
			$this->value = null;
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			if ( $this->multiple ) {
				$this->value = array_map( 'sanitize_text_field', (array) $this->value );
			} else {
				$this->value = sanitize_text_field( $this->value );
			}
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) && count( array_intersect( array_map( 'strval', (array) $this->value ), array_map( 'strval', array_keys( $this->options ) ) ) ) === 0 ) {
			if ( $this->multiple ) {
				$this->add_errors( [ sprintf( esc_html__( '%s are invalid', 'hivepress' ), $this->label ) ] );
			} else {
				$this->add_errors( [ sprintf( esc_html__( '%s is invalid', 'hivepress' ), $this->label ) ] );
			}
		}

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<select name="' . esc_attr( $this->name ) . '" ' . hp\html_attributes( $this->attributes ) . '>';

		foreach ( $this->options as $value => $label ) {
			$output .= '<option value="' . esc_attr( $value ) . '" ' . selected( $this->value, $value, false ) . '>' . esc_html( $label ) . '</option>';
		}

		$output .= '</select>';

		return $output;
	}
}
