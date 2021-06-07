<?php
/**
 * Date field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Date field class.
 *
 * @class Date
 */
class Date extends Field {

	/**
	 * Field placeholder.
	 *
	 * @var string
	 */
	protected $placeholder;

	/**
	 * Date format.
	 *
	 * @var string
	 */
	protected $format;

	/**
	 * Date display format.
	 *
	 * @var string
	 */
	protected $display_format;

	/**
	 * Minimum date.
	 *
	 * @var string
	 */
	protected $min_date;

	/**
	 * Maximum date.
	 *
	 * @var string
	 */
	protected $max_date;

	/**
	 * Disabled dates.
	 *
	 * @var array
	 */
	protected $disabled_dates = [];

	/**
	 * Date offset.
	 *
	 * @var int
	 */
	protected $offset;

	/**
	 * Date window.
	 *
	 * @var int
	 */
	protected $window;

	/**
	 * Time flag.
	 *
	 * @var bool
	 */
	protected $time = false;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Field meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => hivepress()->translator->get_string( 'date' ),
				'type'       => 'DATE',
				'filterable' => true,
				'sortable'   => true,

				'settings'   => [
					'placeholder' => [
						'label'      => esc_html__( 'Placeholder', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 100,
					],

					'min_date'    => [
						'label'  => esc_html__( 'Minimum Date', 'hivepress' ),
						'type'   => 'date',
						'_order' => 110,
					],

					'max_date'    => [
						'label'  => esc_html__( 'Maximum Date', 'hivepress' ),
						'type'   => 'date',
						'_order' => 120,
					],

					'offset'      => [
						'label'       => esc_html__( 'Date Offset', 'hivepress' ),
						'description' => esc_html__( 'Set the number of days after today to define the minimum date.', 'hivepress' ),
						'type'        => 'number',
						'min_value'   => 0,
						'_order'      => 130,
					],

					'time'        => [
						'label'   => esc_html__( 'Time', 'hivepress' ),
						'caption' => esc_html__( 'Allow setting time', 'hivepress' ),
						'type'    => 'checkbox',
						'_order'  => 140,
					],
				],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {
		$attributes = [];

		// Set placeholder.
		if ( ! is_null( $this->placeholder ) ) {
			$attributes['placeholder'] = $this->placeholder;
		}

		// Set disabled flag.
		if ( $this->disabled ) {
			$attributes['disabled'] = true;
		}

		// Set required flag.
		if ( $this->required ) {
			$attributes['required'] = true;
		}

		// Set format.
		if ( is_null( $this->format ) ) {
			$this->format = 'Y-m-d';

			if ( $this->time ) {
				$this->format .= ' H:i:s';
			}
		}

		$attributes['data-format'] = $this->format;

		// Set display format.
		if ( is_null( $this->display_format ) ) {
			$this->display_format = get_option( 'date_format' );

			if ( $this->time ) {
				$this->display_format .= ' ' . get_option( 'time_format' );
			}
		}

		$attributes['data-display-format'] = $this->display_format;

		// Set minimum date.
		if ( ! is_null( $this->min_date ) ) {
			$attributes['data-min-date'] = $this->min_date;
		}

		// Set maximum date.
		if ( ! is_null( $this->max_date ) ) {
			$attributes['data-max-date'] = $this->max_date;
		}

		// Set disabled dates.
		if ( $this->disabled_dates ) {
			$attributes['data-disabled-dates'] = wp_json_encode(
				array_map(
					function( $date ) {
						return is_array( $date ) ? array_combine( [ 'from', 'to' ], $date ) : $date;
					},
					$this->disabled_dates
				)
			);
		}

		// Set offset.
		if ( ! is_null( $this->offset ) ) {
			$attributes['data-offset'] = $this->offset;
		}

		// Set window.
		if ( ! is_null( $this->window ) ) {
			$attributes['data-window'] = $this->window;
		}

		// Set time flag.
		if ( $this->time ) {
			$attributes['data-time'] = true;
		}

		// Set component.
		$attributes['data-component'] = 'date';

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
			$date = date_create_from_format( $this->format, $this->value );

			if ( $date ) {
				return date_i18n( $this->display_format, $date->format( 'U' ) );
			}
		}
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		if ( ! is_null( $this->value ) ) {
			$this->value = trim( wp_unslash( $this->value ) );
		}
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		$this->value = sanitize_text_field( $this->value );
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) ) {
			$date = date_create_from_format( $this->format, $this->value );

			if ( false === $date ) {
				/* translators: %s: field label. */
				$this->add_errors( sprintf( esc_html__( '"%s" field contains an invalid value.', 'hivepress' ), $this->label ) );
			} else {
				if ( ! is_null( $this->min_date ) ) {
					$min_date = date_create( $this->min_date );

					if ( $date < $min_date ) {
						/* translators: 1: field label, 2: date. */
						$this->add_errors( sprintf( esc_html__( '"%1$s" can\'t be earlier than %2$s.', 'hivepress' ), $this->label, $min_date->format( $this->display_format ) ) );
					}
				}

				if ( ! is_null( $this->max_date ) ) {
					$max_date = date_create( $this->max_date );

					if ( $date > $max_date ) {
						/* translators: 1: field label, 2: date. */
						$this->add_errors( sprintf( esc_html__( '"%1$s" can\'t be later than %2$s.', 'hivepress' ), $this->label, $max_date->format( $this->display_format ) ) );
					}
				}
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

		// Get display type.
		$display_type = 'hidden' === $this->display_type ? 'hidden' : 'text';

		return '<input type="' . esc_attr( $display_type ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . hp\html_attributes( $this->attributes ) . '>';
	}
}
