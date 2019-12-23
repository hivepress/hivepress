<?php
/**
 * Listing submit category page template.
 *
 * @template listing_submit_category_page
 * @description Listing submission page (category).
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing submit category page template class.
 *
 * @class Listing_Submit_Category_Page
 */
class Listing_Submit_Category_Page extends Listing_Submit_Page {

	/**
	 * Template blocks.
	 *
	 * @var array
	 */
	protected static $blocks = [];

	/**
	 * Class initializer.
	 *
	 * @param array $args Template arguments.
	 */
	public static function init( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_content' => [
						'blocks' => [
							'listing_categories' => [
								'type'     => 'listing_categories',
								'template' => 'submit',
								'columns'  => 3,
								'_order'    => 10,
							],
						],
					],
				],
			],
			$args,
			'blocks'
		);

		parent::init( $args );
	}
}
