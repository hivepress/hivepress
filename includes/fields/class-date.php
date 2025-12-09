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
 * Date and time.
 */
class Date extends Field {

	/**
	 * Field placeholder.
	 *
	 * @var string
	 */
	protected $placeholder;

	/**
	 * Stored date format.
	 *
	 * @var string
	 */
	protected $format;

	/**
	 * Displayed date format.
	 *
	 * @var string
	 */
	protected $display_format;

	/**
	 * Minimum available date.
	 *
	 * @var string
	 */
	protected $min_date;

	/**
	 * Maximum available date.
	 *
	 * @var string
	 */
	protected $max_date;

	/**
	 * Enabled dates.
	 *
	 * @var array
	 */
	protected $enabled_dates = [];

	/**
	 * Disabled dates.
	 *
	 * @var array
	 */
	protected $disabled_dates = [];

	/**
	 * Disabled days of the week.
	 *
	 * @var array
	 */
	protected $disabled_days = [];

	/**
	 * The number of days unavailable from today.
	 *
	 * @var int
	 */
	protected $offset;

	/**
	 * The number of days available from today.
	 *
	 * @var int
	 */
	protected $window;

	/**
	 * Allow selecting multiple dates?
	 *
	 * @var bool
	 */
	protected $multiple = false;

	/**
	 * Allow selecting time?
	 *
	 * @var bool
	 */
	protected $time = false;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
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

		// Set display type.
		if ( 'hidden' !== $this->display_type ) {
			$this->display_type = 'date';
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

		// Set enabled dates.
		if ( $this->enabled_dates ) {
			$attributes['data-enabled-dates'] = wp_json_encode( $this->get_ranges( $this->enabled_dates ) );
		}

		// Set disabled dates.
		if ( $this->disabled_dates ) {
			$attributes['data-disabled-dates'] = wp_json_encode( $this->get_ranges( $this->disabled_dates ) );
		}

		// Set disabled days.
		if ( $this->disabled_days ) {
			$attributes['data-disabled-days'] = '[' . implode( ',', $this->disabled_days ) . ']';
		}

		// Set offset.
		if ( ! is_null( $this->offset ) ) {
			$attributes['data-offset'] = $this->offset;
		}

		// Set window.
		if ( ! is_null( $this->window ) ) {
			$attributes['data-window'] = $this->window;
		}

		// Set multiple mode.
		if ( $this->multiple ) {
			$attributes['data-mode'] = 'multiple';
		}

		// Set time flag.
		if ( $this->time ) {
			$attributes['data-time'] = 'true';
		}

		// Set options.
		$attributes['data-options'] = wp_json_encode(
			[
				'locale' => [
					'firstDayOfWeek' => absint( get_option( 'start_of_week' ) ),
				],
			]
		);

		// Set component.
		$attributes['data-component'] = 'date';

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::boot();
	}

	/**
	 * Gets field value for display.
	 *
	 * @return mixed
	 */
	public function get_display_value() {

		// @todo add support for multiple dates.
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
			if ( $this->multiple && ! is_array( $this->value ) ) {
				$this->value = explode( ',', $this->value );
			}

			$this->value = array_map(
				function( $value ) {
					return trim( wp_unslash( $value ) );
				},
				(array) $this->value
			);

			if ( ! $this->multiple ) {
				$this->value = hp\get_first_array_value( $this->value );
			} elseif ( [] === $this->value ) {
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
					return sanitize_text_field( $value );
				},
				$this->value
			);
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
		if ( parent::validate() && ! is_null( $this->value ) ) {

			// Validate fields.
			$errors = [];

			foreach ( (array) $this->value as $value ) {
				$date = date_create_from_format( $this->format, $value );

				if ( false === $date ) {
					$errors[] = sprintf( hivepress()->translator->get_string( 'field_contains_invalid_value' ), $this->get_label( true ) );
				} else {
					if ( ! is_null( $this->min_date ) ) {
						$min_date = date_create( $this->min_date );

						if ( $date < $min_date ) {
							/* translators: 1: field label, 2: date. */
							$errors[] = sprintf( esc_html__( '"%1$s" can\'t be earlier than %2$s.', 'hivepress' ), $this->get_label( true ), $min_date->format( $this->display_format ) );
						}
					}

					if ( ! is_null( $this->max_date ) ) {
						$max_date = date_create( $this->max_date );

						if ( $date > $max_date ) {
							/* translators: 1: field label, 2: date. */
							$errors[] = sprintf( esc_html__( '"%1$s" can\'t be later than %2$s.', 'hivepress' ), $this->get_label( true ), $max_date->format( $this->display_format ) );
						}
					}
				}
			}

			// Add errors.
			if ( $errors ) {
				$this->add_errors( array_unique( $errors ) );
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

		// Get value.
		$value = $this->value;

		if ( $this->multiple && ! is_null( $value ) ) {
			$value = implode( ', ', $value );
		}

		// Render field.
		$output .= ( new Text(
			array_merge(
				$this->args,
				[
					'display_type' => 'text',
					'default'      => $value,

					'attributes'   => [
						'data-input' => '',
					],
				]
			)
		) )->render();

		// Render clear button.
		$output .= '<a title="' . esc_attr__( 'Clear', 'hivepress' ) . '" data-clear><i class="hp-icon fas fa-times"></i></a>';

		$output .= '</div>';

		return $output;
	}

	/**
	 * Gets date ranges.
	 *
	 * @param array $dates Dates or ranges.
	 * @return array
	 */
	protected function get_ranges( $dates ) {
		return array_map(
			function( $date ) {
				return is_array( $date ) ? array_combine( [ 'from', 'to' ], $date ) : $date;
			},
			$dates
		);
	}
}
