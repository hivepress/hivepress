<?php
/**
 * Email field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Email address.
 */
class Email extends Text {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => esc_html__( 'Email', 'hivepress' ),
				'filterable' => false,
				'sortable'   => false,

				'settings'   => [
					'min_length' => null,
					'max_length' => null,
					'pattern'    => null,
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
				'max_length' => 254,
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Sets field display template.
	 *
	 * @param string $display_template Display template.
	 */
	protected function set_display_template( $display_template ) {
		if ( strpos( $display_template, '<a ' ) === false && ! hp\has_shortcode( $display_template ) ) {
			$display_template = str_replace( '%value%', '<a href="mailto:%value%">%value%</a>', $display_template );
		}

		$this->display_template = $display_template;
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		$this->value = sanitize_email( $this->value );
	}

	/**
	 * Validates field value.
	 *
	 * @return bool
	 */
	public function validate() {
		if ( parent::validate() && ! is_null( $this->value ) && ! is_email( $this->value ) ) {
			$this->add_errors( sprintf( hivepress()->translator->get_string( 'field_contains_invalid_value' ), $this->get_label( true ) ) );
		}

		return empty( $this->errors );
	}
}
