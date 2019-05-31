<?php
/**
 * Listing edit block template.
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
			'tag'        => 'tr',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-listing', 'hp-listing--edit-block' ],
			],

			'blocks'     => [
				'listing_title'           => [
					'type'     => 'element',
					'filepath' => 'listing/edit/block/title',
					'order'    => 10,
				],

				'listing_status'          => [
					'type'     => 'element',
					'filepath' => 'listing/edit/block/status',
					'order'    => 20,
				],

				'listing_date'            => [
					'type'     => 'element',
					'filepath' => 'listing/edit/block/date',
					'order'    => 30,
				],

				'listing_actions_primary' => [
					'type'       => 'container',
					'tag'        => 'td',
					'order'      => 40,

					'attributes' => [
						'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary' ],
					],

					'blocks'     => [
						'listing_view_link' => [
							'type'     => 'element',
							'filepath' => 'listing/edit/block/view-link',
							'order'    => 10,
						],
					],
				],
			],
		],
	],
];
