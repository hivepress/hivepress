<?php
/**
 * Password field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Password.
 */
class Password extends Text {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => null,
				'filterable' => false,
				'sortable'   => false,
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
				'max_length' => 64,
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps field properties.
	 */
	protected function boot() {

		// Set component.
		$this->attributes['data-component'] = 'password';

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

		// Render field.
		$output = '<input type="' . esc_attr( $this->display_type ) . '" name="' . esc_attr( $this->name ) . '" ' . hp\html_attributes( $this->attributes ) . '>';

		// Render button.
		$output .= '<a href="#" title="' . esc_attr__( 'Show', 'hivepress' ) . '" data-component="toggle" data-icon="eye-slash" data-caption="' . esc_attr__( 'Hide', 'hivepress' ) . '" class="hp-field__icon hp-link"><i class="hp-icon fas fa-eye"></i></a>';

		return $output;
	}
}
