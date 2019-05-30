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
		'listing_container' => [
			'type'       => 'container',
			'tag'        => 'article',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-listing', 'hp-listing--view-block' ],
			],

			'blocks'     => [
				'listing_header'  => [
					'type'       => 'container',
					'tag'        => 'header',
					'order'      => 10,

					'attributes' => [
						'class' => [ 'hp-listing__header' ],
					],

					'blocks'     => [
						'listing_image' => [
							'type'     => 'element',
							'filepath' => 'listing/view/block/image',
							'order'    => 10,
						],
					],
				],

				'listing_content' => [
					'type'       => 'container',
					'order'      => 20,

					'attributes' => [
						'class' => [ 'hp-listing__content' ],
					],

					'blocks'     => [
						'listing_title'                => [
							'type'     => 'element',
							'filepath' => 'listing/view/block/title',
							'order'    => 10,
						],

						'listing_details_primary'      => [
							'type'       => 'container',
							'order'      => 20,

							'attributes' => [
								'class' => [ 'hp-listing__details', 'hp-listing__details--primary' ],
							],

							'blocks'     => [
								'listing_category' => [
									'type'     => 'element',
									'filepath' => 'listing/view/category',
									'order'    => 10,
								],

								'listing_date'     => [
									'type'     => 'element',
									'filepath' => 'listing/view/date',
									'order'    => 20,
								],
							],
						],

						'listing_attributes_secondary' => [
							'type'     => 'element',
							'filepath' => 'listing/view/block/attributes-secondary',
							'order'    => 30,
						],
					],
				],

				'listing_footer'  => [
					'type'       => 'container',
					'tag'        => 'footer',
					'order'      => 30,

					'attributes' => [
						'class' => [ 'hp-listing__footer' ],
					],

					'blocks'     => [
						'listing_attributes_primary' => [
							'type'     => 'element',
							'filepath' => 'listing/view/block/attributes-primary',
							'order'    => 10,
						],

						'listing_actions_primary'    => [
							'type'       => 'container',
							'order'      => 20,

							'attributes' => [
								'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary' ],
							],

							'blocks'     => [],
						],
					],
				],
			],
		],
	],
];
