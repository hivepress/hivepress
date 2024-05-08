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
	 * HTML tag.
	 *
	 * @todo Remove after changing block to Container.
	 * @var string
	 */
	protected $tag = 'div';

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
	 * Model alias.
	 *
	 * @var string
	 */
	protected $alias;

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
		$attributes['class'] = [ 'hp-block' ];

		if ( $this->model ) {

			// Get alias.
			if ( ! $this->alias ) {
				$this->alias = $this->model;
			}

			// Get class.
			$class = 'hp-' . hp\sanitize_slug( $this->alias ) . '__attributes';

			$attributes['class'][] = $class;

			if ( $this->area ) {

				// Get area.
				$area = hp\get_last_array_value( explode( '_', $this->area ) );

				if ( $area ) {
					$attributes['class'][] = $class . '--' . hp\sanitize_slug( $area );
				}

				if ( 'view_page_primary' === $this->area ) {
					$attributes['class'][] = 'widget';
					$attributes['class'][] = 'hp-widget';
				}
			}
		}

		// @todo remove when implemented globally.
		if ( 'view_page_primary' === $this->area ) {
			$attributes['data-block'] = $this->name;
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
						$this->model . '-attributes-' . hp\get_last_array_value( $parts ),
					]
				)
			);
		}

		// Render template part.
		$output .= ( new Part(
			[
				'context' => $this->context,
				'path'    => $path,
			]
		) )->render();

		if ( ! $output && $model->_get_fields( $this->area ) ) {

			// Get column width.
			$column_width = hp\get_column_width( $this->columns );

			// Get field class.
			$field_class = 'hp-' . hp\sanitize_slug( $this->alias ) . '__attribute';

			// Render attributes.
			if ( $this->tag ) {
				$output .= '<' . esc_attr( $this->tag ) . ' ' . hp\html_attributes( $this->attributes ) . '>';
			}

			if ( $this->columns > 1 ) {
				$output .= '<div class="hp-row">';
			}

			foreach ( $model->_get_fields( $this->area ) as $field ) {
				if ( ! is_null( $field->get_value() ) ) {
					if ( $this->columns > 1 ) {
						$output .= '<div class="hp-col-lg-' . esc_attr( $column_width ) . ' hp-col-xs-12">';
					}

					$output .= '<div class="' . esc_attr( $field_class . ' ' . $field_class . '--' . $field->get_slug() ) . '">' . $field->display() . '</div>';

					if ( $this->columns > 1 ) {
						$output .= '</div>';
					}
				}
			}

			if ( $this->columns > 1 ) {
				$output .= '</div>';
			}

			if ( $this->tag ) {
				$output .= '</' . esc_attr( $this->tag ) . '>';
			}
		}

		return $output;
	}
}
