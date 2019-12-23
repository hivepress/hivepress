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
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'type'     => 'CHAR',
				'title'    => esc_html__( 'Select', 'hivepress' ),

				'settings' => [
					'multiple' => [
						'label'   => esc_html__( 'Multiple', 'hivepress' ),
						'caption' => esc_html__( 'Allow multiple selection', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'   => 10,
					],

					'options'  => [
						'label'    => esc_html__( 'Options', 'hivepress' ),
						'type'     => 'select',
						'multiple' => true,
						'_order'    => 20,
					],
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
		$args = hp\merge_arrays(
			[
				'filters' => true,
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {
		$attributes = [];

		// Set required flag.
		if ( $this->required ) {
			$attributes['required'] = true;
		}

		// Set multiple flag.
		if ( $this->multiple ) {
			$attributes['multiple'] = true;
		}

		// Set placeholder.
		if ( isset( $this->placeholder ) && ! $this->multiple ) {
			$this->options = [ '' => $this->placeholder ] + $this->options;
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
	}

	/**
	 * Gets field display value.
	 *
	 * @return mixed
	 */
	public function get_display_value() {
		if ( ! is_null( $this->value ) ) {
			$options = $this->options;

			$labels = array_filter(
				array_map(
					function( $value ) use ( $options ) {
						return hp\get_array_value( $options, $value );
					},
					(array) $this->value
				),
				'strlen'
			);

			if ( ! empty( $labels ) ) {
				return implode( ', ', $labels );
			}
		}
	}

	/**
	 * Adds field filters.
	 */
	protected function add_filters() {
		parent::add_filters();

		if ( $this->multiple ) {
			$this->filters['operator'] = 'AND';
		} else {
			$this->filters['operator'] = 'IN';
		}
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		if ( is_array( $this->value ) && ! $this->multiple ) {
			if ( ! empty( $this->value ) ) {
				$this->value = reset( $this->value );
			} else {
				$this->value = null;
			}
		} elseif ( ! is_array( $this->value ) && $this->multiple ) {
			$this->value = (array) $this->value;
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( $this->multiple ) {
			$this->value = array_map( 'sanitize_text_field', (array) $this->value );
		} else {
			$this->value = sanitize_text_field( $this->value );
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) && count( array_intersect( (array) $this->value, array_map( 'strval', array_keys( $this->options ) ) ) ) !== count( (array) $this->value ) ) {
			$this->add_errors( [ sprintf( esc_html__( '"%s" field contains an invalid value.', 'hivepress' ), $this->label ) ] );
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
			$output .= '<option value="' . esc_attr( $value ) . '" ' . ( in_array( (string) $value, (array) $this->value, true ) ? 'selected' : '' ) . '>' . esc_html( $label ) . '</option>';
		}

		$output .= '</select>';

		return $output;
	}
}
