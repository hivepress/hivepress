<?php
/**
 * Color field.
 *
 * @package HivePress\Fields
 */

namespace HivePress\Fields;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Color.
 */
class Color extends Text {
	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'      => esc_html__( 'Color', 'hivepress' ),
				'filterable' => false,
				'sortable'   => false,

				'settings'   => [
					'min_length'  => null,
					'max_length'  => null,
					'pattern'     => null,
					'placeholder' => null,
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
		$this->attributes = hp\merge_arrays( $this->attributes, [ 'data-component' => 'color' ] );

		parent::boot();
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
				'pattern'      => '^#([A-Fa-f0-9]{3}){1,2}$',
				'display_type' => 'text',
			]
		);

		parent::__construct( $args );
	}

	/**
	 * Sanitizes field value.
	 */
	protected function sanitize() {
		$this->value = sanitize_hex_color( $this->value );
	}
}
