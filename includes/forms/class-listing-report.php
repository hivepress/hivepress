<?php
/**
 * Listing report form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing report form class.
 *
 * @class Listing_Report
 */
class Listing_Report extends Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = merge_arrays(
			$args,
			[
				'title'  => esc_html__( 'Report Listing', 'hivepress' ),
				'fields' => [
					'reason' => [
						'type'       => 'textarea',
						'max_length' => 2048,
						'required'   => true,
						'order'      => 10,
					],
				],
			]
		);

		parent::__construct( $args );
	}
}
