<?php
/**
 * Vendor filter form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor filter form class.
 *
 * @class Vendor_Filter
 */
class Vendor_Filter extends Form {

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

				'fields' => [
					'_category' => [
						'type'    => 'radio',
						'options' => [],
						'_order'  => 10,
					],

					'_sort'     => [
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

				'button' => [
					'label' => hivepress()->translator->get_string( 'filter' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
