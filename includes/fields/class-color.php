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
				'label'      => hivepress()->translator->get_string( 'color' ),
				'filterable' => false,
				'sortable'   => false,

				'settings'   => [
					'placeholder' => null,
					'min_length'  => null,
					'max_length'  => null,
					'pattern'     => null,
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
				'pattern' => '#[0-9a-fA-F]{6}',
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
