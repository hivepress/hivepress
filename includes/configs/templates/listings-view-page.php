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
			'tag'        => 'header',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-page__header' ],
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
				'class' => [ 'hp-row' ],
			],

			'blocks'     => [
				'sidebar' => [
					'type'       => 'container',
					'tag'        => 'aside',
					'order'      => 10,

					'attributes' => [
						'class'          => [ 'hp-page__sidebar', 'hp-col-sm-4', 'hp-col-xs-12' ],
						'data-component' => 'sticky',
					],

					'blocks'     => [
						'filter_form' => [
							'type'       => 'form',
							'form_name'  => 'listing_filter',
							'order'      => 10,

							'attributes' => [
								'class' => [ 'hp-form--narrow', 'widget' ],
							],
						],
					],
				],

				'content' => [
					'type'       => 'container',
					'tag'        => 'main',
					'order'      => 20,

					'attributes' => [
						'class' => [ 'hp-page__content', 'hp-col-sm-8', 'hp-col-xs-12' ],
					],

					'blocks'     => [
						'topbar'     => [
							'type'       => 'container',
							'order'      => 10,

							'attributes' => [
								'class' => [ 'hp-page__topbar' ],
							],

							'blocks'     => [
								'result_count' => [
									'type'  => 'result_count',
									'order' => 10,
								],

								'sort_form'    => [
									'type'       => 'form',
									'form_name'  => 'listing_sort',
									'order'      => 20,

									'attributes' => [
										'class' => [ 'hp-form--pivot' ],
									],
								],
							],
						],

						'results'    => [
							'type'    => 'listings',
							'columns' => 2,
							'order'   => 20,
						],

						'pagination' => [
							'type'      => 'element',
							'file_path' => 'pagination',
							'order'     => 30,
						],
					],
				],
			],
		],
	],
];
