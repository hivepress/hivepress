<?php
/**
 * Listing submit page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing submit page template class.
 *
 * @class Listing_Submit_Page
 */
abstract class Listing_Submit_Page extends Page {

	/**
	 * Class constructor.
	 *
	 * @param array $args Template arguments.
	 */
	public function __construct( $args = [] ) {
		$args = hp\merge_trees(
			[
				'blocks' => [
					'page_container' => [
						'blocks' => [
							'page_content' => [
								'type'   => 'container',
								'tag'    => 'main',
								'_order' => 10,

								'blocks' => [
									'page_title' => [
										'type'   => 'part',
										'path'   => 'page/page-title',
										'_order' => 5,
									],
								],
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
