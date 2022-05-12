<?php
/**
 * Attributes block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;
use HivePress\Models;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders attributes.
 */
class Attributes extends Block {

	/**
	 * Columns number.
	 *
	 * @var int
	 */
	protected $columns;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Attributes area name.
	 *
	 * @var string
	 */
	protected $area;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'    => hivepress()->translator->get_string( 'attributes' ),

				'settings' => [
					'columns' => [
						'label'    => hivepress()->translator->get_string( 'columns_number' ),
						'type'     => 'select',
						'default'  => 2,
						'required' => true,
						'_order'   => 10,

						'options'  => [
							1 => '1',
							2 => '2',
							3 => '3',
							4 => '4',
						],
					],
				],
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Bootstraps block properties.
	 */
	protected function boot() {

		if ( ! $this->columns ) {
			// Set columns.
			$this->columns = 2;
		}

		parent::boot();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {

		$output = '';

		if ( $this->model && $this->area ) {

			// Get model name.
			$model_name = $this->model;

			// Get model.
			$model = $this->get_context( $this->model );

			// Get attribute area details.
			$area_details = explode( '_', $this->area );

			// Get attribute position.
			$attribute_position = hp\get_last_array_value( $area_details );

			array_shift( $area_details );

			// Get attribute display place.
			$attribute_place = hp\get_first_array_value( $area_details );

			if ( file_exists( get_stylesheet_directory() . '/templates/' . $model_name . '/view/' . $attribute_place . '/' . $model_name . '-attributes-' . $attribute_position . '.php' ) ) {
				$output .= ( new Part( [ 'path' => $model_name . '/view/' . $attribute_place . '/' . $model_name . '-attributes-' . $attribute_position ] ) )->render();
			} else {
				if ( $model && $model->_get_fields( $this->area ) ) {

					// Get column width.
					$column_width = hp\get_column_width( $this->columns );

					if ( 'secondary' === $attribute_position ) {
						$output .= '<div class="hp-' . $model_name . '__attributes hp-' . $model_name . '__attributes--' . $attribute_position . '">';
						$output .= '<div class="hp-row">';

						foreach ( $model->_get_fields( $this->area ) as $field ) {
							if ( ! is_null( $field->get_value() ) ) {
								$output .= '<div class="hp-col-lg-' . esc_attr( $column_width ) . ' hp-col-xs-12">';
								$output .= '<div class="hp-' . $model_name . '__attribute hp-' . $model_name . '__attribute--' . esc_attr( $field->get_slug() ) . '">' . $field->display() . '</div>';
								$output .= '</div>';
							}
						}

						$output .= '</div>';
						$output .= '</div>';
					} elseif ( 'primary' === $attribute_position ) {
						if ( 'page' === $attribute_place ) {
							$output .= '<div class="hp-' . $model_name . '__attributes hp-' . $model_name . '__attributes--' . $attribute_position . ' hp-widget widget">';
						} elseif ( 'block' === $attribute_place ) {
							$output .= '<div class="hp-' . $model_name . '__attributes hp-' . $model_name . '__attributes--' . $attribute_position . '">';
						}

						foreach ( $model->_get_fields( $this->area ) as $field ) {
							if ( ! is_null( $field->get_value() ) ) {
								$output .= '<div class="hp-' . $model_name . '__attribute hp-' . $model_name . '__attribute--' . esc_attr( $field->get_slug() ) . '">' . $field->display() . '</div>';
							}
						}

						$output .= '</div>';
					}
				}
			}
		}

		return $output;
	}
}
