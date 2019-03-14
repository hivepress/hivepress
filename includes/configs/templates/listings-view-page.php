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
	'blocks' => [
		'header'  => [
			'type'       => 'container',
			'order'      => 10,

			'attributes' => [
				'tag'        => 'header',
				'attributes' => [
					'class' => 'hp-page__header',
				],
			],

			'blocks'     => [
				'search_form' => [
					'type'  => 'listing_search_form',
					'order' => 10,
				],
			],
		],

		'columns' => [
			'type'       => 'container',
			'order'      => 20,

			'attributes' => [
				'attributes' => [
					'class' => [ 'hp-row' ],
				],
			],

			'blocks'     => [
				'sidebar' => [
					'type'       => 'container',
					'order'      => 10,

					'attributes' => [
						'tag'        => 'aside',
						'attributes' => [
							'class'          => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
							'data-component' => 'sticky',
						],
					],

					'blocks'     => [
						'filter_form' => [
							'type'       => 'form',
							'order'      => 10,

							'attributes' => [
								'form_name'  => 'listing_filter',
								'attributes' => [
									'class' => [ 'hp-form--narrow' ],
								],
							],
						],
					],
				],

				'content' => [
					'type'       => 'container',
					'order'      => 20,

					'attributes' => [
						'tag'        => 'main',
						'attributes' => [
							'class' => [ 'hp-page__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
						],
					],

					'blocks'     => [
						'topbar'     => [
							'type'   => 'container',
							'order'  => 10,

							'blocks' => [
								'result_count' => [
									'type'       => 'element',
									'order'      => 10,

									'attributes' => [
										'file_path' => 'result-count',
									],
								],

								'sort_form'    => [
									'type'       => 'form',
									'order'      => 20,

									'attributes' => [
										'form_name' => 'listing_sort',
									],
								],
							],
						],

						'results'    => [
							'type'       => 'listings',
							'order'      => 20,

							'attributes' => [
								'columns' => 2,
							],
						],

						'pagination' => [
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
];
