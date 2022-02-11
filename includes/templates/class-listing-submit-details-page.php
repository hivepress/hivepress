<?php
/**
 * Listing submit details page template.
 *
 * @template listing_submit_details_page
 * @description Listing submission page (details).
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing submission page (listing details step).
 */
class Listing_Submit_Details_Page extends Listing_Submit_Page {

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
							'listing_submit_form' => [
								'type'   => 'form',
								'form'   => 'listing_submit',
								'_order' => 10,

								'footer' => [
									'form_actions' => [
										'type'       => 'container',
										'_order'     => 10,

										'attributes' => [
											'class' => [ 'hp-form__actions' ],
										],

										'blocks'     => [
											'listing_category_change_link' => [
												'type'   => 'part',
												'path'   => 'listing/submit/listing-category-change-link',
												'_order' => 10,
											],
										],
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
