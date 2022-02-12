<?php
/**
 * Image size field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Image size settings.
 */
class Image_Size extends Field {

	/**
	 * Width field.
	 *
	 * @var object
	 */
	protected $width_field;

	/**
	 * Height field.
	 *
	 * @var object
	 */
	protected $height_field;

	/**
	 * Crop left field.
	 *
	 * @var object
	 */
	protected $crop_left_field;

	/**
	 * Crop top field.
	 *
	 * @var object
	 */
	protected $crop_top_field;

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Set defaults.
		$field_args = [
			'min_value'  => 0,
			'default'    => 0,
			'required'   => true,

			'attributes' => [
				'class' => [ 'small-text' ],
			],
		];

		// Create fields.
		$this->width_field = new Number(
			array_merge(
				$field_args,
				[
					'label' => esc_html__( 'Width', 'hivepress' ),
					'name'  => $this->name . '[width]',
				]
			)
		);

		$this->height_field = new Number(
			array_merge(
				$field_args,
				[
					'label' => esc_html__( 'Height', 'hivepress' ),
					'name'  => $this->name . '[height]',
				]
			)
		);

		$this->crop_left_field = new Select(
			[
				'label'   => esc_html__( 'Crop Position', 'hivepress' ),
				'name'    => $this->name . '[crop][]',

				'options' => [
					'left'   => esc_html__( 'Left', 'hivepress' ),
					'center' => esc_html__( 'Center', 'hivepress' ),
					'right'  => esc_html__( 'Right', 'hivepress' ),
				],
			]
		);

		$this->crop_top_field = new Select(
			[
				'label'   => esc_html__( 'Crop Position', 'hivepress' ),
				'name'    => $this->name . '[crop][]',

				'options' => [
					'top'    => esc_html__( 'Top', 'hivepress' ),
					'center' => esc_html__( 'Center', 'hivepress' ),
					'bottom' => esc_html__( 'Bottom', 'hivepress' ),
				],
			]
		);

		parent::boot();
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {

		// Set size values.
		$this->width_field->set_value( hp\get_array_value( $this->value, 'width' ) );
		$this->height_field->set_value( hp\get_array_value( $this->value, 'height' ) );

		// Set crop values.
		$crop = hp\get_array_value( $this->value, 'crop' );

		if ( is_array( $crop ) ) {
			$this->crop_left_field->set_value( hp\get_first_array_value( $crop ) );
			$this->crop_top_field->set_value( hp\get_last_array_value( $crop ) );
		} elseif ( $crop ) {
			$this->crop_left_field->set_value( 'center' );
			$this->crop_top_field->set_value( 'center' );
		}

		// Set field value.
		if ( $this->width_field->get_value() || $this->height_field->get_value() ) {
			$this->value = [
				'width'  => (int) $this->width_field->get_value(),
				'height' => (int) $this->height_field->get_value(),
				'crop'   => false,
			];

			if ( $this->crop_left_field->get_value() && $this->crop_top_field->get_value() ) {
				$this->value['crop'] = [ $this->crop_left_field->get_value(), $this->crop_top_field->get_value() ];
			}
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
		if ( parent::validate() && ! is_null( $this->value ) ) {

			// Validate fields.
			$this->width_field->validate();
			$this->height_field->validate();
			$this->crop_left_field->validate();
			$this->crop_top_field->validate();

			// Add errors.
			$this->add_errors(
				array_unique(
					array_merge(
						$this->width_field->get_errors(),
						$this->height_field->get_errors(),
						$this->crop_left_field->get_errors(),
						$this->crop_top_field->get_errors()
					)
				)
			);
		}

		return empty( $this->errors );
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '<fieldset ' . hp\html_attributes( $this->attributes ) . '>';

		// Render fields.
		$output .= '<label for="' . esc_attr( $this->width_field->get_name() ) . '">' . esc_html( $this->width_field->get_label() ) . '</label>&nbsp;';
		$output .= $this->width_field->render() . '<br>';

		$output .= '<label for="' . esc_attr( $this->height_field->get_name() ) . '">' . esc_html( $this->height_field->get_label() ) . '</label>&nbsp;';
		$output .= $this->height_field->render() . '<br>';

		$output .= '<label for="' . esc_attr( $this->crop_left_field->get_name() ) . '">' . esc_html( $this->crop_left_field->get_label() ) . '</label>&nbsp;';
		$output .= $this->crop_left_field->render() . '<br>';

		$output .= '<label for="' . esc_attr( $this->crop_top_field->get_name() ) . '">&nbsp;</label>&nbsp;';
		$output .= $this->crop_top_field->render();

		$output .= '</fieldset>';

		return $output;
	}
}
