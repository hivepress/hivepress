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
 * Filters vendors.
 */
class Vendor_Filter extends Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
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
				'action' => home_url(),
				'method' => 'GET',

				'fields' => [
					'_category' => [
						'type'        => 'radio',
						'options'     => 'terms',
						'option_args' => [ 'taxonomy' => 'hp_vendor_category' ],
						'_order'      => 10,
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
