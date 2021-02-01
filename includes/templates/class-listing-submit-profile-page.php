<?php
/**
 * Listing submit profile page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing submit profile page template class.
 *
 * @class Listing_Submit_Profile_Page
 */
class Listing_Submit_Profile_Page extends Listing_Submit_Page {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_content' => [
						'blocks' => [
							'user_update_profile_form' => [
								'type'   => 'form',
								'form'   => 'user_update_profile',
								'_order' => 10,
							],
						],
					],
				],
			],
			$args
		);

		parent::__construct( $args );
	}
}
