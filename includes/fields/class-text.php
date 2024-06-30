<?php
/**
 * Text field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Text.
 */
class Text extends Field {

	/**
	 * Field placeholder.
	 *
	 * @var string
	 */
	protected $placeholder;

	/**
	 * Minimum text length.
	 *
	 * @var int
	 */
	protected $min_length;

	/**
	 * Maximum text length.
	 *
	 * @var int
	 */
	protected $max_length;

	/**
	 * Regex pattern.
	 *
	 * @var string
	 */
	protected $pattern;

	/**
	 * Allow HTML?
	 *
	 * @var mixed
	 */
	protected $html = false;

	/**
	 * Make text read-only?
	 *
	 * @var bool
	 */
	protected $readonly = false;

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => esc_html__( 'Text', 'hivepress' ),
				'filterable' => true,
				'sortable'   => true,

				'settings'   => [
					'placeholder' => [
						'label'      => esc_html__( 'Placeholder', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 256,
						'_order'     => 100,
					],

					'min_length'  => [
						'label'     => esc_html__( 'Minimum Length', 'hivepress' ),
						'type'      => 'number',
						'min_value' => 0,
						'_order'    => 110,
					],

					'max_length'  => [
						'label'     => esc_html__( 'Maximum Length', 'hivepress' ),
						'type'      => 'number',
						'min_value' => 1,
						'_order'    => 120,
					],

					'pattern'     => [
						'label'      => esc_html__( 'Regex Pattern', 'hivepress' ),
						'type'       => 'regex',
						'max_length' => 256,
						'_order'     => 130,
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

		// Set minimum length.
		if ( ! is_null( $this->min_length ) ) {
			$attributes['minlength'] = $this->min_length;
		}

		// Set maximum length.
		if ( ! is_null( $this->max_length ) ) {
			$attributes['maxlength'] = $this->max_length;
		}

		// Set regex pattern.
		if ( ! is_null( $this->pattern ) ) {
			$attributes['pattern'] = $this->pattern;
		}

		// Set readonly flag.
		if ( $this->readonly ) {
			$attributes['readonly'] = true;
			$attributes['title']    = esc_html__( 'Click to copy', 'hivepress' );

			$this->statuses['optional'] = null;
		}

		// Set disabled flag.
		if ( $this->disabled ) {
			$attributes['disabled'] = true;
		}

		// Set required flag.
		if ( $this->required ) {
			$attributes['required'] = true;
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::boot();
	}

	/**
	 * Adds SQL filter.
	 */
	protected function add_filter() {
		parent::add_filter();

		$this->filter['operator'] = 'LIKE';
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
		if ( empty( $this->html ) ) {
			$this->value = sanitize_text_field( $this->value );
		} elseif ( is_array( $this->html ) ) {
			$this->value = wp_kses( $this->value, $this->html );
		} else {
			$this->value = wp_kses( $this->value, 'post' );
		}
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) ) {
			if ( ! is_null( $this->min_length ) && mb_strlen( $this->value ) < $this->min_length ) {
				$this->add_errors( sprintf( hivepress()->translator->get_string( 'field_shorter_than_n_characters' ), $this->get_label( true ), number_format_i18n( $this->min_length ) ) );
			}

			if ( ! is_null( $this->max_length ) && mb_strlen( $this->value ) > $this->max_length ) {
				$this->add_errors( sprintf( hivepress()->translator->get_string( 'field_longer_than_n_characters' ), $this->get_label( true ), number_format_i18n( $this->max_length ) ) );
			}

			if ( ! is_null( $this->pattern ) && ! preg_match( '/^' . $this->pattern . '$/', $this->value ) ) {
				$this->add_errors( sprintf( hivepress()->translator->get_string( 'field_contains_invalid_value' ), $this->get_label( true ) ) );
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
		return '<input type="' . esc_attr( $this->display_type ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . hp\html_attributes( $this->attributes ) . '>';
	}
}
