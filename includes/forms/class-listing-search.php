<?php
/**
 * Listing search form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

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
		$args = array_replace_recursive(
			[
				'method' => 'GET',
				'fields' => [
					's' => [
						'placeholder' => esc_html__( 'Keywords', 'hivepress' ),
						'type'        => 'search',
						'max_length'  => 256,
						'order'       => 10,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
