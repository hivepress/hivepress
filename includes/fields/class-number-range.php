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
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {
		Field::bootstrap();
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		Field::normalize();

		if ( is_array( $this->value ) && count( $this->value ) === 2 ) {
			$this->value = array_map(
				function( $value ) {
					return is_numeric( $value ) ? $value : null;
				},
				$this->value
			);

			if ( [ null, null ] === $this->value ) {
				$this->value = reset( $this->value );
			}
		} else {
			$this->value = null;
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			if ( $this->decimals > 0 ) {
				$decimals = $this->decimals;

				$this->value = array_map(
					function( $value ) use ( $decimals ) {
						return round( floatval( $value ), $decimals );
					},
					$this->value
				);
			} else {
				$this->value = array_map( 'intval', $this->value );
			}
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) ) {
			if ( reset( $this->value ) > end( $this->value ) ) {
				$this->add_errors( [ sprintf( esc_html__( '%s is invalid', 'hivepress' ), $this->label ) ] );
			}

			if ( ! is_null( $this->min_value ) && ( reset( $this->value ) < $this->min_value || end( $this->value ) < $this->min_value ) ) {
				$this->add_errors( [ sprintf( esc_html__( "%1\$s can't be lower than %2\$s", 'hivepress' ), $this->label, number_format_i18n( $this->min_value ) ) ] );
			}

			if ( ! is_null( $this->max_value ) && ( reset( $this->value ) > $this->max_value || end( $this->value ) > $this->max_value ) ) {
				$this->add_errors( [ sprintf( esc_html__( "%1\$s can't be greater than %2\$s", 'hivepress' ), $this->label, number_format_i18n( $this->max_value ) ) ] );
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
		$output = '<div ' . hp\html_attributes( $this->attributes ) . '>';

		// Get values.
		$values = (array) $this->value;

		// Render fields.
		$output .= ( new Number(
			array_merge(
				$this->args,
				[
					'name'        => $this->name . '[]',
					'placeholder' => esc_html__( 'Min', 'hivepress' ),
					'default'     => reset( $values ),
				]
			)
		) )->render();

		$output .= ( new Number(
			array_merge(
				$this->args,
				[
					'name'        => $this->name . '[]',
					'placeholder' => esc_html__( 'Max', 'hivepress' ),
					'default'     => end( $values ),
				]
			)
		) )->render();

		$output .= '</div>';

		return $output;
	}
}
