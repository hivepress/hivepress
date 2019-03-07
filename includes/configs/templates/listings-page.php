<?php
/**
 * Listings page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'listing_search_form' => [
			'type'       => 'listing_search_form',
			'order'      => 10,
			'attributes' => [
				'attributes' => [
					'data-bind' => 'submit: renderBlock',
				],
			],
		],
		'page_container'      => [
			'type'       => 'container',
			'order'      => 20,
			'attributes' => [
				'attributes' => [
					'class' => [ 'hp-row' ],
				],
			],
			'blocks'     => [
				'page_sidebar' => [
					'type'       => 'container',
					'order'      => 10,
					'attributes' => [
						'tag'        => 'aside',
						'attributes' => [
							'class'          => [ 'hp-col-sm-4', 'hp-col-xs-12' ],
							'data-component' => 'sticky',
						],
					],
					'blocks'     => [
						'listing_filter_form' => [
							'type'       => 'form',
							'order'      => 10,
							'attributes' => [
								'form_name' => 'listing_filter',
							],
						],
					],
				],
				'page_content' => [
					'type'       => 'container',
					'order'      => 20,
					'attributes' => [
						'tag'        => 'main',
						'attributes' => [
							'class' => [ 'hp-col-sm-8', 'hp-col-xs-12' ],
						],
					],
					'blocks'     => [
						'listing_search_results' => [
							'type'   => 'listing_search_results',
							'order'  => 10,
							'blocks' => [
								'todo_topbar'     => [
									'type'   => 'container',
									'order'  => 10,
									'blocks' => [
										'todo_result_count' => [
											'type'       => 'element',
											'order'      => 10,
											'attributes' => [
												'file_path' => 'result-count',
											],
										],
										'listing_sort_form' => [
											'type'       => 'form',
											'order'      => 20,
											'attributes' => [
												'form_name' => 'listing_sort',
											],
										],
									],
								],
								'todo_loop'       => [
									'type'  => 'listings',
									'order' => 20,
								],
								'todo_pagination' => [
									'type'       => 'element',
									'order'      => 30,
									'attributes' => [
										'file_path' => 'pagination',
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
