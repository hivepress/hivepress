<?php
/**
 * Attributes block.
 *
 * @package HivePress\Blocks
 */

namespace HivePress\Blocks;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Renders model attributes.
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
	 * Display area.
	 *
	 * @var string
	 */
	protected $area;

	/**
	 * File path.
	 *
	 * @var string
	 * @deprecated since version 1.6.3.
	 */
	protected $path;

	/**
	 * HTML attributes.
	 *
	 * @var array
	 */
	protected $attributes = [];

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'settings' => [
					'columns' => [
						'label'    => hivepress()->translator->get_string( 'columns_number' ),
						'type'     => 'select',
						'default'  => 1,
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
		$attributes = [];

		// Set class.
		$attributes['class'] = [];

		if ( $this->model ) {

			// Get class.
			$class = 'hp-' . hp\sanitize_slug( $this->model ) . '__attributes';

			$attributes['class'][] = $class;

			if ( $this->area ) {

				// Get area.
				$area = hp\get_array_value( explode( '_', $this->area ), 2 );

				if ( $area ) {
					$attributes['class'][] = $class . '--' . hp\sanitize_slug( $area );
				}

				if ( 'view_page_primary' === $this->area ) {
					$attributes['class'][] = 'widget';
					$attributes['class'][] = 'hp-widget';
				}
			}
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::boot();
	}

	/**
	 * Renders block HTML.
	 *
	 * @return string
	 */
	public function render() {
		$output = '';

		if ( ! $this->model || ! $this->area ) {
			return $output;
		}

		// Get model.
		$model = $this->get_context( $this->model );

		if ( ! $model ) {
			return $output;
		}

		// Get file path.
		$path = $this->path;

		if ( ! $path ) {
			$parts = explode( '_', $this->area );

			$path = implode(
				'/',
				array_merge(
					[
						$this->model,
					],
					array_slice( $parts, 0, 2 ),
					[
						$this->model . '-attributes-' . hp\get_array_value( $parts, 2 ),
					]
				)
			);
		}

		if ( file_exists( get_stylesheet_directory() . '/' . $path . '.php' ) ) {

			// Render template part.
			$output .= ( new Part(
				[
					'path' => $path,
				]
			) )->render();
		} elseif ( $model->_get_fields( $this->area ) ) {

			// Get column width.
			$column_width = hp\get_column_width( $this->columns );

			// Get field class.
			$field_class = 'hp-' . hp\sanitize_slug( $this->model ) . '__attribute';

			// Render attributes.
			$output .= '<div ' . hp\html_attributes( $this->attributes ) . '>';
			$output .= '<div class="hp-row">';

			foreach ( $model->_get_fields( $this->area ) as $field ) {
				if ( ! is_null( $field->get_value() ) ) {
					$output .= '<div class="hp-col-lg-' . esc_attr( $column_width ) . ' hp-col-xs-12">';
					$output .= '<div class="' . $field_class . ' ' . $field_class . '--' . esc_attr( $field->get_slug() ) . '">' . $field->display() . '</div>';
					$output .= '</div>';
				}
			}

			$output .= '</div>';
			$output .= '</div>';
		}

		return $output;
	}
}
