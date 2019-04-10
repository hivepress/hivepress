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
	 * Form title.
	 *
	 * @var string
	 */
	protected static $title;

	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected static $model;

	/**
	 * Class initializer.
	 *
	 * @param array $args Form arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_arrays(
			[
				'model' => 'listing',
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
				'message' => esc_html__( 'Listing has been updated', 'hivepress' ),
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
					'label' => esc_html__( 'Update Listing', 'hivepress' ),
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
