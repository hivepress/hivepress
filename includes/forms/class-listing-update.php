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
			$args,
			[
				'model'  => 'listing',
				'fields' => [
					'title'       => [
						'order' => 10,
					],

					'description' => [
						'order' => 20,
					],
				],
			]
		);

		parent::__construct( $args );
	}
}
