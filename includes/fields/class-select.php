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
	 * Field placeholder.
	 *
	 * @var string
	 */
	protected $placeholder;

	/**
	 * Field options.
	 *
	 * @var array
	 */
	protected $options = [];

	/**
	 * Multiple flag.
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Field meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => esc_html_x( 'Select', 'field', 'hivepress' ),
				'filterable' => true,

				'settings'   => [
					'multiple' => [
						'label'   => esc_html_x( 'Multiple', 'selection', 'hivepress' ),
						'caption' => esc_html__( 'Allow multiple selection', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 100,
					],

					'options'  => [
						'label'    => esc_html__( 'Options', 'hivepress' ),
						'type'     => 'select',
						'options'  => [],
						'multiple' => true,
						'_order'   => 110,
					],
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
			[
				'placeholder' => '&mdash;',
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {
		$attributes = [];

		// Set placeholder.
		if ( ! is_null( $this->placeholder ) && ! $this->multiple ) {
			$this->options = [ '' => $this->placeholder ] + $this->options;
		}

		// Set disabled flag.
		if ( $this->disabled ) {
			$attributes['disabled'] = true;
		}

		// Set required flag.
		if ( $this->required ) {
			$attributes['required'] = true;
		}

		// Set multiple flag.
		if ( $this->multiple ) {
			$attributes['multiple'] = true;
		}

		// Set component.
		$attributes['data-component'] = 'select';

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::boot();
	}

	/**
	 * Gets field display value.
	 *
	 * @return mixed
	 */
	public function get_display_value() {
		if ( ! is_null( $this->value ) ) {
			$labels = array_filter(
				array_map(
					function( $value ) {
						return hp\get_array_value( $this->options, $value );
					},
					(array) $this->value
				),
				'strlen'
			);

			if ( $labels ) {
				return implode( ', ', $labels );
			}
		}
	}

	/**
	 * Adds field filter.
	 */
	protected function add_filter() {
		parent::add_filter();

		if ( $this->multiple ) {
			$this->filter['operator'] = 'AND';
		} else {
			$this->filter['operator'] = 'IN';
		}
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		if ( $this->multiple && ! is_null( $this->value ) ) {
			if ( [] !== $this->value ) {
				$this->value = (array) $this->value;
			} else {
				$this->value = null;
			}
		} elseif ( ! $this->multiple && is_array( $this->value ) ) {
			if ( $this->value ) {
				$this->value = hp\get_first_array_value( $this->value );
			} else {
				$this->value = null;
			}
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( $this->multiple ) {
			$this->value = array_map(
				function( $value ) {
					return is_numeric( $value ) ? absint( $value ) : sanitize_text_field( $value );
				},
				$this->value
			);
		} else {
			$this->value = is_numeric( $this->value ) ? absint( $this->value ) : sanitize_text_field( $this->value );
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) && count( array_intersect( (array) $this->value, array_keys( $this->options ) ) ) !== count( (array) $this->value ) ) {
			$this->add_errors( sprintf( esc_html__( '"%s" field contains an invalid value.', 'hivepress' ), $this->label ) );
		}

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<select name="' . esc_attr( $this->name ) . ( $this->multiple ? '[]' : '' ) . '" ' . hp\html_attributes( $this->attributes ) . '>';

		foreach ( $this->options as $value => $label ) {
			$output .= '<option value="' . esc_attr( $value ) . '" ' . ( in_array( $value, (array) $this->value, true ) ? 'selected' : '' ) . '>' . esc_html( $label ) . '</option>';
		}

		$output .= '</select>';

		return $output;
	}
}
