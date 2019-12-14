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
	 * HTML flag.
	 *
	 * @var mixed
	 */
	protected $html = false;

	/**
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'type'     => 'CHAR',
				'title'    => esc_html__( 'Text', 'hivepress' ),

				'settings' => [
					'placeholder' => [
						'label'      => esc_html__( 'Placeholder', 'hivepress' ),
						'type'       => 'text',
						'max_length' => 2048,
						'order'      => 10,
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
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'filters' => true,
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

		// Set minimum length.
		if ( ! is_null( $this->min_length ) ) {
			$attributes['minlength'] = $this->min_length;
		}

		// Set maximum length.
		if ( ! is_null( $this->max_length ) ) {
			$attributes['maxlength'] = $this->max_length;
		}

		// Set required flag.
		if ( $this->required ) {
			$attributes['required'] = true;
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
	}

	/**
	 * Adds field filters.
	 */
	protected function add_filters() {
		parent::add_filters();

		$this->filters['operator'] = 'LIKE';
	}

	/**
	 * Normalizes field value.
	 */
	protected function normalize() {
		parent::normalize();

		$this->value = wp_unslash( $this->value );
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
			if ( ! is_null( $this->min_length ) && strlen( $this->value ) < $this->min_length ) {
				$this->add_errors( [ sprintf( esc_html__( '"%1$s" should be at least %2$s characters long.', 'hivepress' ), $this->label, number_format_i18n( $this->min_length ) ) ] );
			}

			if ( ! is_null( $this->max_length ) && strlen( $this->value ) > $this->max_length ) {
				$this->add_errors( [ sprintf( esc_html__( '"%1$s" can\'t be longer than %2$s characters.', 'hivepress' ), $this->label, number_format_i18n( $this->max_length ) ) ] );
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
		return '<input type="' . esc_attr( static::get_display_type() ) . '" name="' . esc_attr( $this->name ) . '" value="' . esc_attr( $this->value ) . '" ' . hp\html_attributes( $this->attributes ) . '>';
	}
}
