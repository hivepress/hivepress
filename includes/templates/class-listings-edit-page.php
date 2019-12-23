<?php
/**
 * Listings edit page template.
 *
 * @template listing_edit_page
 * @description Listing page in edit context.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listings edit page template class.
 *
 * @class Listings_Edit_Page
 */
class Listings_Edit_Page extends User_Account_Page {

	/**
	 * Template meta.
	 *
	 * @var array
	 */
	protected static $meta;

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
							'listings' => [
								'type'     => 'listings',
								'template' => 'edit',
								'_order'   => 10,
							],
						],
					],
				],
			],
			$args,
			'blocks'
		);

		parent::__construct( $args );
	}
}
