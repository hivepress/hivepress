<?php
/**
 * Button field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Button.
 */
class Button extends Field {

	/**
	 * Button caption.
	 *
	 * @var string
	 */
	protected $caption;

	/**
	 * Class constructor.
	 *
	 * @param array $args Field arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			$args,
			[
				'statuses' => [
					'optional' => null,
				],
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Set caption.
		if ( is_null( $this->caption ) ) {
			$this->caption = $this->label;
		}

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'class' => [ 'button' ],
			]
		);

		parent::boot();
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {}

	/**
	 * Renders field HTML.
	 *
	 * @return string
	 */
	public function render() {
		return '<button type="' . esc_attr( $this->display_type ) . '" ' . hp\html_attributes( $this->attributes ) . '><span>' . esc_html( $this->caption ) . '</span></button>';
	}
}
