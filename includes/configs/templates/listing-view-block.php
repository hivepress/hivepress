<?php
/**
 * Listing view block template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'container' => [
			'type'       => 'container',
			'tag'        => 'article',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-listing', 'hp-listing--view-block' ],
			],

			'blocks'     => [
				'header'  => [
					'type'       => 'container',
					'tag'        => 'header',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-listing__header' ],
					],

					'blocks'     => [
						'image' => [
							'type'      => 'element',
							'file_path' => 'listing/view/block/image',
							'order'     => 10,
						],
					],
				],

				'content' => [
					'type'       => 'container',
					'order'      => 20,

					'attributes' => [
						'class' => [ 'hp-listing__content' ],
					],

					'blocks'     => [
						'title'                => [
							'type'      => 'element',
							'file_path' => 'listing/view/block/title',
							'order'     => 10,
						],

						'details_primary'      => [
							'type'       => 'container',
							'order'      => 20,

							'attributes' => [
								'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
							],

							'blocks'     => [
								'category' => [
									'type'      => 'element',
									'file_path' => 'listing/view/category',
									'order'     => 10,
								],

								'date'     => [
									'type'      => 'element',
									'file_path' => 'listing/view/date',
									'order'     => 20,
								],
							],
						],

						'attributes_secondary' => [
							'type'      => 'element',
							'file_path' => 'listing/view/block/attributes-secondary',
							'order'     => 30,
						],
					],
				],

				'footer'  => [
					'type'       => 'container',
					'tag'        => 'footer',
					'order'      => 30,

					'attributes' => [
						'class' => [ 'hp-listing__footer' ],
					],

					'blocks'     => [
						'attributes_primary' => [
							'type'      => 'element',
							'file_path' => 'listing/view/block/attributes-primary',
							'order'     => 10,
						],

						'actions_primary'    => [
							'type'       => 'container',
							'order'      => 20,

							'attributes' => [
								'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary' ],
							],

							'blocks'     => [
								'message_link' => [
									'type'      => 'element',
									'file_path' => 'message/send-link',
									'order'     => 10,
								],

								'todo'         => [
									'type'  => 'listing_favorite',
									'class' => [ 'hp-listing__action' ],
									'order' => 20,
								],
							],
						],
					],
				],
			],
		],
	],
];
