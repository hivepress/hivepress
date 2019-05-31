<?php
/**
 * Listings view page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'parent' => 'page',

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

				'page_columns' => [
					'type'       => 'container',
					'order'      => 20,

					'attributes' => [
						'class' => [ 'hp-row' ],
					],

					'blocks'     => [
						'page_sidebar' => [
							'type'       => 'container',
							'tag'        => 'aside',
							'order'      => 10,

							'attributes' => [
								'class'          => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
								'data-component' => 'sticky',
							],

							'blocks'     => [
								'listing_filter_form' => [
									'type'       => 'form',
									'form'       => 'listing_filter',
									'order'      => 10,

									'attributes' => [
										'class' => [ 'hp-form--narrow', 'widget' ],
									],
								],
							],
						],

						'page_content' => [
							'type'       => 'container',
							'tag'        => 'main',
							'order'      => 20,

							'attributes' => [
								'class' => [ 'hp-page__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
							],

							'blocks'     => [
								'listing_results' => [
									'type'   => 'results',
									'order'  => 10,

									'blocks' => [
										'page_topbar' => [
											'type'       => 'container',
											'order'      => 10,

											'attributes' => [
												'class' => [ 'hp-page__topbar' ],
											],

											'blocks'     => [
												'listing_result_count' => [
													'type' => 'result_count',
													'order' => 10,
												],

												'listing_sort_form'    => [
													'type' => 'form',
													'form' => 'listing_sort',
													'order' => 20,

													'attributes' => [
														'class' => [ 'hp-form--pivot' ],
													],
												],
											],
										],

										'listings'    => [
											'type'    => 'listings',
											'columns' => 2,
											'order'   => 20,
										],

										'listing_pagination' => [
											'type'     => 'element',
											'filepath' => 'page/pagination',
											'order'    => 30,
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
];
