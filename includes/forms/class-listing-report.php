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
 * Reports listing.
 */
class Listing_Report extends Model_Form {

	/**
	 * Class initializer.
	 *
	 * @param array $meta Class meta values.
	 */
	public static function init( $meta = [] ) {
		$meta = hp\merge_arrays(
			[
				'label'   => hivepress()->translator->get_string( 'report_listing' ),
				'model'   => 'listing',
				'captcha' => false,
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
				'description' => hivepress()->translator->get_string( 'provide_details_to_verify_listing_report' ),
				'message'     => hivepress()->translator->get_string( 'listing_has_been_reported' ),
				'reset'       => true,

				'fields'      => [
					'details' => [
						'label'      => hivepress()->translator->get_string( 'details' ),
						'type'       => 'textarea',
						'max_length' => 2048,
						'required'   => true,
						'_separate'  => true,
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
