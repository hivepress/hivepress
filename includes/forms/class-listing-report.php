<?php
/**
 * Listing report form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

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
		$args = hp\merge_arrays(
			[
				'title'   => esc_html__( 'Report Listing', 'hivepress' ),
				'message' => esc_html__( 'Listing has been reported', 'hivepress' ),
				'action'  => hp\get_rest_url( '/listings/%id%/report' ),

				'fields'  => [
					'reason' => [
						'type'       => 'textarea',
						'max_length' => 2048,
						'required'   => true,
						'order'      => 10,
					],
				],

				'button'  => [
					'label' => esc_html__( 'Report Listing', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
