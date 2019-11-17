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
	protected $placeholder;

	/**
	 * Date format.
	 *
	 * @var string
	 */
	protected $format = 'Y-m-d';

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
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'    => esc_html__( 'Date', 'hivepress' ),

				'settings' => [
					'placeholder'    => [
						'label' => esc_html__( 'Placeholder', 'hivepress' ),
						'type'  => 'text',
						'order' => 10,
					],

					'display_format' => [
						'label'    => esc_html__( 'Date Format', 'hivepress' ),
						'type'     => 'text',
						'default'  => get_option( 'date_format' ),
						'required' => true,
						'order'    => 20,
					],

					'min_date'       => [
						'label' => esc_html__( 'Minimum Date', 'hivepress' ),
						'type'  => 'date',
						'order' => 30,
					],

					'max_date'       => [
						'label' => esc_html__( 'Maximum Date', 'hivepress' ),
						'type'  => 'date',
						'order' => 40,
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
				'filters'        => true,
				'display_format' => get_option( 'date_format' ),
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

		// Set placeholder.
		if ( ! is_null( $this->placeholder ) ) {
			$attributes['placeholder'] = $this->placeholder;
		}

		// Set required property.
		if ( $this->required ) {
			$attributes['required'] = true;
		}

		// Set format.
		if ( ! is_null( $this->format ) ) {
			$attributes['data-format'] = $this->format;
		}

		// Set display format.
		if ( ! is_null( $this->display_format ) ) {
			$attributes['data-display-format'] = $this->display_format;
		}

		// Set minimum date.
		if ( ! is_null( $this->min_date ) ) {
			$attributes['data-min-date'] = $this->min_date;
		}

		// Set maximum date.
		if ( ! is_null( $this->max_date ) ) {
			$attributes['data-max-date'] = $this->max_date;
		}

		// Set datepicker.
		$attributes['data-component'] = 'date';

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
			return date_create_from_format( $this->format, $this->value )->format( $this->display_format );
		}

		return $this->value;
	}

	/**
	 * Adds field filters.
	 */
	protected function add_filters() {
		parent::add_filters();

		$this->filters['type'] = 'DATE';
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		if ( ! is_string( $this->value ) ) {
			$this->value = null;
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
			$date = date_create_from_format( '!' . $this->format, $this->value );

			if ( false === $date || $date->format( $this->format ) !== $this->value ) {
				$this->add_errors( [ sprintf( esc_html__( '%s is invalid.', 'hivepress' ), $this->label ) ] );
			} else {
				if ( ! is_null( $this->min_date ) ) {
					$min_date = date_create( $this->min_date );

					if ( $date < $min_date ) {
						$this->add_errors( [ sprintf( esc_html__( "%1\$s can't be earlier than %2\$s.", 'hivepress' ), $this->label, $min_date->format( $this->display_format ) ) ] );
					}
				}

				if ( ! is_null( $this->max_date ) ) {
					$max_date = date_create( $this->max_date );

					if ( $date > $max_date ) {
						$this->add_errors( [ sprintf( esc_html__( "%1\$s can't be later than %2\$s.", 'hivepress' ), $this->label, $max_date->format( $this->display_format ) ) ] );
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
		return '<input type="text" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . hp\html_attributes( $this->attributes ) . '>';
	}
}
