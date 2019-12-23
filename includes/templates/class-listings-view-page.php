<?php
/**
 * Listings view page template.
 *
 * @template listings_view_page
 * @description Listings page in view context.
 * @package HivePress\Templates
 */

namespace HivePress\Templates;

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

/**
 * Listings view page template class.
 *
 * @class Listings_View_Page
 */
class Listings_View_Page extends Page {

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
								'_order'      => 10,

								'attributes' => [
									'class' => [ 'hp-page__header' ],
								],

								'blocks'     => [
									'listing_search_form' => [
										'type'  => 'listing_search_form',
										'_order' => 10,
									],
								],
							],

							'page_columns' => [
								'type'       => 'container',
								'_order'      => 20,

								'attributes' => [
									'class' => [ 'hp-row' ],
								],

								'blocks'     => [
									'page_sidebar' => [
										'type'       => 'container',
										'tag'        => 'aside',
										'_order'      => 10,

										'attributes' => [
											'class' => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
											'data-component' => 'sticky',
										],

										'blocks'     => [
											'listing_filter_form' => [
												'type'  => 'form',
												'form'  => 'listing_filter',
												'_order' => 10,

												'attributes' => [
													'class' => [ 'hp-form--narrow', 'hp-widget', 'widget' ],
												],
											],

											'page_sidebar_widgets'         => [
												'type'  => 'widgets',
												'area'  => 'listings_sidebar',
												'_order' => 20,
											],
										],
									],

									'page_content' => [
										'type'       => 'container',
										'tag'        => 'main',
										'_order'      => 20,

										'attributes' => [
											'class' => [ 'hp-page__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
										],

										'blocks'     => [
											'listings_container' => [
												'type'   => 'results',
												'_order'  => 10,

												'blocks' => [
													'page_topbar' => [
														'type'       => 'container',
														'_order'      => 10,

														'attributes' => [
															'class' => [ 'hp-page__topbar' ],
														],

														'blocks'     => [
															'listing_count' => [
																'type' => 'result_count',
																'_order' => 10,
															],

															'listing_sort_form'    => [
																'type' => 'form',
																'form' => 'listing_sort',
																'_order' => 20,

																'attributes' => [
																	'class' => [ 'hp-form--pivot' ],
																],
															],
														],
													],

													'listings'    => [
														'type'    => 'listings',
														'columns' => 2,
														'_order'   => 20,
													],

													'listing_pagination' => [
														'type'     => 'element',
														'filepath' => 'page/pagination',
														'_order'    => 30,
													],
												],
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
