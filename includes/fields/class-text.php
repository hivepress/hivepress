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
 * Text field class.
 *
 * @class Text
 */
class Text extends Field {

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
	 * @var string
	 */
	protected static $settings = [];

	/**
	 * Field placeholder.
	 *
	 * @var string
	 */
	protected $placeholder;

	/**
	 * Minimum length.
	 *
	 * @var int
	 */
	protected $min_length;

	/**
	 * Maximum length.
	 *
	 * @var int
	 */
	protected $max_length;

	/**
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'    => esc_html__( 'Text', 'hivepress' ),
				'settings' => [
					'placeholder' => [
						'label' => esc_html__( 'Placeholder', 'hivepress' ),
						'type'  => 'text',
						'order' => 10,
					],

					'min_length'  => [
						'label'     => esc_html__( 'Minimum Length', 'hivepress' ),
						'type'      => 'number',
						'min_value' => 0,
						'order'     => 20,
					],

					'max_length'  => [
						'label'     => esc_html__( 'Maximum Length', 'hivepress' ),
						'type'      => 'number',
						'min_value' => 1,
						'order'     => 30,
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Gets field attributes.
	 *
	 * @return array
	 */
	protected function get_attributes() {
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

		return hp\merge_arrays( parent::get_attributes(), $attributes );
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
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
			if ( ! is_null( $this->min_length ) && strlen( $this->value ) < $this->min_length ) {
				$this->add_errors( [ sprintf( esc_html__( '%1\$s should be at least %2\$s characters long', 'hivepress' ), $this->label, number_format_i18n( $this->min_length ) ) ] );
			}

			if ( ! is_null( $this->max_length ) && strlen( $this->value ) > $this->max_length ) {
				$this->add_errors( [ sprintf( esc_html__( "%1\$s can't be longer than %2\$s characters", 'hivepress' ), $this->label, number_format_i18n( $this->max_length ) ) ] );
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
		return '<input type="' . esc_attr( static::$type ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . hp\html_attributes( $this->get_attributes() ) . '>';
	}
}
