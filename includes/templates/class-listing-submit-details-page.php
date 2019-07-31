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
 * Listing submit details page template class.
 *
 * @class Listing_Submit_Details_Page
 */
class Listing_Submit_Details_Page extends Listing_Submit_Page {

	/**
	 * Template name.
	 *
	 * @var string
	 */
	protected static $name;

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
							'listing_submit_form' => [
								'type'   => 'form',
								'form'   => 'listing_submit',
								'order'  => 10,

								'footer' => [
									'form_actions' => [
										'type'       => 'container',
										'order'      => 10,

										'attributes' => [
											'class' => [ 'hp-form__actions' ],
										],

										'blocks'     => [
											'listing_category_change_link' => [
												'type'     => 'element',
												'filepath' => 'listing/submit/listing-category-change-link',
												'order'    => 10,
											],
										],
									],
								],
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
