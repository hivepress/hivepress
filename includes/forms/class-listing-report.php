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
	 * Form name.
	 *
	 * @var string
	 */
	protected static $name;

	/**
	 * Form description.
	 *
	 * @var string
	 */
	protected static $description;

	/**
	 * Form title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Form message.
	 *
	 * @var string
	 */
	protected static $message;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected static $model;

	/**
	 * Form action.
	 *
	 * @var string
	 */
	protected static $action;

	/**
	 * Form method.
	 *
	 * @var string
	 */
	protected static $method = 'POST';

	/**
	 * Form captcha.
	 *
	 * @var bool
	 */
	protected static $captcha = false;

	/**
	 * Form fields.
	 *
	 * @var array
	 */
	protected static $fields = [];

	/**
	 * Form button.
	 *
	 * @var object
	 */
	protected static $button;

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'title'       => hivepress()->translator->get_string( 'report_listing' ),
				'description' => hivepress()->translator->get_string( 'provide_details_to_verify_listing_report' ),
				'message'     => hivepress()->translator->get_string( 'listing_has_been_reported' ),
				'model'       => 'listing',
				'action'      => hp\get_rest_url( '/listings/%id%/report' ),

				'fields'      => [
					'report_details' => [
						'label'      => esc_html__( 'Details', 'hivepress' ),
						'type'       => 'textarea',
						'max_length' => 2048,
						'required'   => true,
						'order'      => 10,
					],
				],

				'button'      => [
					'label' => hivepress()->translator->get_string( 'report_listing' ),
				],
			],
			$args
		);

		parent::init( $args );
	}
}
