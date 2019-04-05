<?php
/**
 * Checkbox field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Checkbox field class.
 *
 * @class Checkbox
 */
class Checkbox extends Field {

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
	 * Checkbox caption.
	 *
	 * @var string
	 */
	protected $caption;

	/**
	 * Toggle value.
	 *
	 * @var mixed
	 */
	protected $toggle = true;

	/**
	 * Class initializer.
	 *
	 * @param array $args Field arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'    => esc_html__( 'Checkbox', 'hivepress' ),
				'settings' => [
					'caption' => [
						'label' => esc_html__( 'Caption', 'hivepress' ),
						'type'  => 'text',
						'order' => 10,
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function bootstrap() {
		$attributes = [];

		// Set caption.
		if ( is_null( $this->caption ) ) {
			$this->caption = $this->label;
		}

		// Set required property.
		if ( $this->required ) {
			$attributes['required'] = true;
		}

		$this->attributes = hp\merge_arrays( $this->attributes, $attributes );

		parent::bootstrap();
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		if ( ! is_null( $this->value ) ) {
			if ( is_bool( $this->toggle ) ) {
				$this->value = boolval( $this->value );
			} else {
				$this->value = sanitize_text_field( $this->value );
			}
		}
	}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		// todo.
		$id = $this->name . '_' . uniqid();

		return '<label for="' . esc_attr( $id ) . '"><input type="' . esc_attr( static::$type ) . '" name="' . esc_attr( $this->name ) . '" id="' . esc_attr( $id ) . '" value="' . esc_attr( $this->toggle ) . '" ' . checked( $this->value, $this->toggle, false ) . ' ' . hp\html_attributes( $this->attributes ) . '><span>' . hp\sanitize_html( $this->caption ) . '</span></label>';
	}
}
