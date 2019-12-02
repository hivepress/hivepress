<?php
/**
 * Listing delete form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing delete form class.
 *
 * @class Listing_Delete
 */
class Listing_Delete extends Model_Form {

	/**
	 * Form description.
	 *
	 * @var string
	 */
	protected static $description;

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
	 * Form redirect.
	 *
	 * @var mixed
	 */
	protected static $redirect = false;

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
				'description' => hivepress()->translator->get_string( 'confirm_listing_deletion' ),
				'model'       => 'listing',
				'action'      => hp\get_rest_url( '/listings/%id%' ),
				'method'      => 'DELETE',
				'redirect'    => true,
				'fields'      => [],

				'button'      => [
					'label' => hivepress()->translator->get_string( 'delete_listing' ),
				],
			],
			$args
		);

		parent::init( $args );
	}
}
