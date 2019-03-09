<?php
/**
 * Listing filter form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing filter form class.
 *
 * @class Listing_Filter
 */
class Listing_Filter extends Form {

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
					'post_type' => [
						'type'    => 'hidden',
						'default' => 'hp_listing',
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
