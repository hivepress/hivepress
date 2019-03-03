<?php
/**
 * Listing sort form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing sort form class.
 *
 * @class Listing_Sort
 */
class Listing_Sort extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = array_replace_recursive(
			$args,
			[
				'method' => 'GET',
				'fields' => [
					'sort' => [
						'label'   => esc_html__( 'Sort by', 'hivepress' ),
						'type'    => 'select',
						'options' => [],
						'order'   => 10,
					],
				],
			]
		);

		parent::__construct( $args );
	}
}
