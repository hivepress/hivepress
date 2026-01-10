<?php
/**
 * Phone field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Phone number.
 */
class Phone extends Text {

	/**
	 * Country codes.
	 *
	 * @var array
	 */
	protected $countries = [];

	/**
	 * Country code.
	 *
	 * @var string
	 */
	protected $country;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => esc_html__( 'Phone', 'hivepress' ),
				'filterable' => false,
				'sortable'   => false,

				'settings'   => [
					'min_length' => null,
					'max_length' => null,
					'pattern'    => null,

					'countries'  => [
						'label'       => esc_html__( 'Countries', 'hivepress' ),
						'description' => esc_html__( 'Select countries to restrict the available calling codes.', 'hivepress' ),
						'type'        => 'select',
						'options'     => 'countries',
						'multiple'    => true,
						'_order'      => 110,
					],

					'country'    => [
						'label'       => esc_html__( 'Default Country', 'hivepress' ),
						'description' => esc_html__( 'Select the country calling code displayed by default.', 'hivepress' ),
						'type'        => 'select',
						'options'     => 'countries',
						'_order'      => 120,
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
			$args,
			[
				'display_type' => 'tel',
				'pattern'      => '\+?[0-9\-\s]+',
				'max_length'   => 24,
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {
		$attributes = [];

		// Set countries.
		if ( $this->countries ) {
			$attributes['data-countries'] = wp_json_encode( $this->countries );
		}

		if ( $this->country ) {
			$attributes['data-country'] = $this->country;
		}

		// Set component.
		$attributes['data-component'] = 'phone';

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::boot();
	}

	/**
	 * Sets field display template.
	 *
	 * @param string $display_template Display template.
	 */
	protected function set_display_template( $display_template ) {
		$this->display_template = str_replace( '%value%', '<span data-component="phone">%value%</span>', $display_template );
	}

	/**
	 * Sets field countries.
	 *
	 * @param array $countries Country codes.
	 */
	protected function set_countries( $countries ) {
		if ( $countries ) {

			// Set display template.
			$this->display_template = str_replace( 'data-component', 'data-countries="' . hp\esc_json( wp_json_encode( $countries ) ) . '" data-component', $this->display_template );
		}

		$this->countries = $countries;
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		parent::sanitize();

		$this->value = preg_replace( '/[^\d+]/', '', $this->value );
		$this->value = preg_replace( '/(?!^)\+/', '', $this->value );
	}
}
