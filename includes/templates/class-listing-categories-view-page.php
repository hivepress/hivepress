<?php
/**
 * Listing categories view page template.
 *
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listing categories view page template class.
 *
 * @class Listing_Categories_View_Page
 */
class Listing_Categories_View_Page extends Page {

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
					'page_container' => [
						'blocks' => [
							'page_header'  => [
								'type'       => 'container',
								'tag'        => 'header',
								'order'      => 10,

								'attributes' => [
									'class' => [ 'hp-page__header' ],
								],

								'blocks'     => [
									'listing_search_form' => [
										'type'  => 'listing_search_form',
										'order' => 10,
									],
								],
							],

							'page_content' => [
								'type'       => 'container',
								'tag'        => 'main',
								'order'      => 20,

								'attributes' => [
									'class' => [ 'hp-page__content' ],
								],

								'blocks'     => [
									'listing_categories' => [
										'type'    => 'listing_categories',
										'columns' => 3,
										'order'   => 10,
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
