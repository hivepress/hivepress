<?php
/**
 * Listing update form.
 *
 * @package HivePress\Forms
 */

namespace HivePress\Forms;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing update form class.
 *
 * @class Listing_Update
 */
class Listing_Update extends Model_Form {

	/**
	 * Class constructor.
	 *
	 * @param array $args Form arguments.
	 */
	public function __construct( $args = [] ) {
		$args = array_replace_recursive(
			[
				'model'  => 'listing',
				'fields' => [
					'title'       => [
						'order' => 10,
					],

					'description' => [
						'order' => 20,
					],

					'image_ids'   => [
						'order' => 30,
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
