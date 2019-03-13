<?php
/**
 * Listing search form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing search form class.
 *
 * @class Listing_Search
 */
class Listing_Search extends Form {

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
						'placeholder' => esc_html__( 'Keywords', 'hivepress' ),
						'type'        => 'search',
						'max_length'  => 256,
						'order'       => 10,
					],

					'post_type' => [
						'type'    => 'hidden',
						'default' => 'hp_listing',
					],
				],

				'button' => [
					'label' => esc_html__( 'Search', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
