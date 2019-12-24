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
class Listing_Report extends Model_Form {

	/**
	 * Form meta.
	 *
	 * @var array
	 */
	protected static $meta;

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'meta' => [
					'label'   => hivepress()->translator->get_string( 'report_listing' ),
					'model'   => 'listing',
					'captcha' => false,
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
				'description' => hivepress()->translator->get_string( 'provide_details_to_verify_listing_report' ),
				'message'     => hivepress()->translator->get_string( 'listing_has_been_reported' ),

				'fields'      => [
					'details' => [
						'label'      => esc_html__( 'Details', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 2048,
						'required'   => true,
						'_excluded'  => true,
						'_order'     => 10,
					],
				],

				'button'      => [
					'label' => hivepress()->translator->get_string( 'report_listing' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}

	/**
	 * Bootstraps form properties.
	 */
	protected function boot() {

		// Set action.
		if ( $this->model->get_id() ) {
			$this->action = hivepress()->router->get_url(
				'listing_report_action',
				[
					'listing_id' => $this->model->get_id(),
				]
			);
		}

		parent::boot();
	}
}
