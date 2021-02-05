<?php
/**
 * Vendor search form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Vendor search form class.
 *
 * @class Vendor_Search
 */
class Vendor_Search extends Form {

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
					's'         => [
						'placeholder'  => hivepress()->translator->get_string( 'keywords' ),
						'type'         => 'text',
						'display_type' => 'search',
						'max_length'   => 256,
						'_order'       => 10,
					],

					'_category' => [
						'type' => 'hidden',
					],

					'post_type' => [
						'type'    => 'hidden',
						'default' => 'hp_vendor',
					],
				],

				'button' => [
					'label' => hivepress()->translator->get_string( 'search' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
