<?php
/**
 * Listing renew complete page template.
 *
 * @template listing_renew_complete_page
 * @description Listing renewal page (completed).
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing renewal page (completion step).
 */
class Listing_Renew_Complete_Page extends Listing_Renew_Page {

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
							'listing_complete_message' => [
								'type'   => 'part',
								'path'   => 'listing/renew/listing-complete-message',
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
