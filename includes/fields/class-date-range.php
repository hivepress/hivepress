<?php
/**
 * Date range field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Date range.
 */
class Date_Range extends Date {

	/**
	 * Minimum field.
	 *
	 * @var object
	 */
	protected $min_field;

	/**
	 * Maximum field.
	 *
	 * @var object
	 */
	protected $max_field;

	/**
	 * Minimum number of days.
	 *
	 * @var int
	 */
	protected $min_length;

	/**
	 * Maximum number of days.
	 *
	 * @var int
	 */
	protected $max_length;

	/**
	 * Marked date ranges.
	 *
	 * @var array
	 */
	protected $ranges = [];

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => esc_html__( 'Date Range', 'hivepress' ),
				'editable' => false,
				'sortable' => false,
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Create fields.
		$this->min_field = new Date( array_merge( $this->args, [ 'required' => false ] ) );
		$this->max_field = new Date( array_merge( $this->args, [ 'required' => false ] ) );

		Field::boot();
	}

	/**
	 * Gets field value for display.
	 *
	 * @return mixed
	 */
	public function get_display_value() {
		if ( ! is_null( $this->value ) ) {
			return $this->min_field->get_display_value() . ' - ' . $this->max_field->get_display_value();
		}
	}

	/**
	 * Adds SQL filter.
	 */
	protected function add_filter() {
		parent::add_filter();

		$this->filter['operator'] = 'BETWEEN';
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		Field::normalize();

		if ( is_array( $this->value ) && count( $this->value ) === 2 ) {
			sort( $this->value );
		} else {
			$this->value = null;
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {

		// Set field values.
		$this->min_field->set_value( hp\get_first_array_value( $this->value ) );
		$this->max_field->set_value( hp\get_last_array_value( $this->value ) );

		// Set range value.
		if ( ! is_null( $this->min_field->get_value() ) && ! is_null( $this->max_field->get_value() ) ) {
			$this->value = [ $this->min_field->get_value(), $this->max_field->get_value() ];
		} else {
			$this->value = null;
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( Field::validate() && ! is_null( $this->value ) ) {

			// Validate fields.
			$this->min_field->validate();
			$this->max_field->validate();

			// Add errors.
			$this->add_errors( array_unique( array_merge( $this->min_field->get_errors(), $this->max_field->get_errors() ) ) );
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

		// Get field arguments.
		$field_args = array_merge(
			$this->args,
			[
				'display_type' => 'text',
				'name'         => null,
				'default'      => null,

				'attributes'   => [
					'data-mode' => 'range',
				],
			]
		);

		if ( ! is_null( $this->min_length ) ) {
			$field_args['attributes']['data-min-length'] = $this->min_length;
		}

		if ( ! is_null( $this->max_length ) ) {
			$field_args['attributes']['data-max-length'] = $this->max_length;
		}

		if ( $this->ranges ) {
			$field_args['attributes']['data-ranges'] = hp\esc_json( wp_json_encode( $this->ranges ) );
		}

		// Render date field.
		$output .= ( new Date( $field_args ) )->render();

		// Render range fields.
		$output .= ( new Hidden(
			array_merge(
				$this->args,
				[
					'display_type' => 'hidden',
					'name'         => $this->name . '[]',
					'required'     => false,
					'default'      => $this->min_field->get_value(),
					'attributes'   => [],
				]
			)
		) )->render();

		$output .= ( new Hidden(
			array_merge(
				$this->args,
				[
					'display_type' => 'hidden',
					'name'         => $this->name . '[]',
					'required'     => false,
					'default'      => $this->max_field->get_value(),
					'attributes'   => [],
				]
			)
		) )->render();

		$output .= '</div>';

		return $output;
	}
}
