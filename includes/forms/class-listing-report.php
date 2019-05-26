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
	 * Form name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Form title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'  => esc_html__( 'Report Listing', 'hivepress' ),

				'fields' => [
					'reason' => [
						'label'      => esc_html__( 'Reason', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 2048,
						'required'   => true,
						'order'      => 10,
					],
				],
			],
			$args
		);

		parent::init( $args );
	}

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'message' => esc_html__( 'Listing has been reported', 'hivepress' ),
				'action'  => hp\get_rest_url( '/listings/%id%/report' ),

				'button'  => [
					'label' => esc_html__( 'Report Listing', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
