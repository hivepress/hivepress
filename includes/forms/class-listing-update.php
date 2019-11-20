<?php
/**
 * Listing update form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing update form class.
 *
 * @class Listing_Update
 */
class Listing_Update extends Model_Form {

	/**
	 * Form name.
	 *
	 * @var string
	 */
	protected static $name;

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
				'message' => hivepress()->translator->get_string( 'listing_has_been_updated' ),
				'model'   => 'listing',
				'action'  => hp\get_rest_url( '/listings/%id%' ),

				'fields'  => [
					'image_ids'   => [
						'order' => 10,
					],

					'title'       => [
						'order' => 20,
					],

					'description' => [
						'order' => 30,
					],
				],

				'button'  => [
					'label' => hivepress()->translator->get_string( 'update_listing' ),
				],
			],
			$args
		);

		parent::init( $args );
	}
}
