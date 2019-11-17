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
 * Date range field class.
 *
 * @class Date_Range
 */
class Date_Range extends Date {

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
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title' => esc_html__( 'Date Range', 'hivepress' ),
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {

		// Create fields.
		$this->min_field = new Date( array_merge( $this->args, [ 'required' => false ] ) );
		$this->max_field = new Date( array_merge( $this->args, [ 'required' => false ] ) );

		Field::bootstrap();
	}

	/**
	 * Gets field display value.
	 *
	 * @return mixed
	 */
	public function get_display_value() {
		if ( ! is_null( $this->value ) ) {
			return $this->min_field->get_display_value() . ' - ' . $this->max_field->get_display_value();
		}

		return $this->value;
	}

	/**
	 * Sets field filters.
	 */
	protected function set_filters() {
		parent::set_filters();

		$this->filters['operator'] = 'BETWEEN';
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
		$this->min_field->set_value( reset( $this->value ) );
		$this->max_field->set_value( end( $this->value ) );

		// Set range value.
		$this->value = array_filter( [ $this->min_field->get_value(), $this->max_field->get_value() ], 'strlen' );

		if ( count( $this->value ) !== 2 ) {
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
			$this->add_errors( $this->min_field->get_errors() );
			$this->add_errors( $this->max_field->get_errors() );
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

		// Render date field.
		$output .= ( new Date(
			hp\merge_arrays(
				$this->args,
				[
					'name'       => null,
					'default'    => null,
					'attributes' => [ 'data-mode' => 'range' ],
				]
			)
		) )->render();

		// Render range fields.
		$output .= ( new Hidden(
			array_merge(
				$this->args,
				[
					'name'     => $this->name . '[]',
					'required' => false,
					'default'  => $this->min_field->get_value(),
				]
			)
		) )->render();

		$output .= ( new Hidden(
			array_merge(
				$this->args,
				[
					'name'     => $this->name . '[]',
					'required' => false,
					'default'  => $this->max_field->get_value(),
				]
			)
		) )->render();

		$output .= '</div>';

		return $output;
	}
}
