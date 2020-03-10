<?php
/**
 * Vendor sort form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor sort form class.
 *
 * @class Vendor_Sort
 */
class Vendor_Sort extends Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Form meta.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'model' => 'vendor',
			],
			$meta
		);

		parent::init( $meta );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'action' => home_url( '/' ),
				'method' => 'GET',
				'button' => null,

				'fields' => [
					'_sort'     => [
						'label'       => esc_html__( 'Sort by', 'hivepress' ),
						'type'        => 'select',
						'placeholder' => null,
						'options'     => [],
						'required'    => true,
						'_order'      => 10,

						'attributes'  => [
							'data-theme' => 'inline',
						],
					],

					'_category' => [
						'type' => 'hidden',
					],

					's'         => [
						'type' => 'hidden',
					],

					'post_type' => [
						'type'    => 'hidden',
						'default' => 'hp_vendor',
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function boot() {

		// Set attributes.
		$this->attributes = hp\merge_arrays(
			$this->attributes,
			[
				'data-autosubmit' => 'true',
			]
		);

		parent::boot();
	}
}
