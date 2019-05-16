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
		'container' => [
			'type'       => 'container',
			'tag'        => 'tr',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-listing', 'hp-listing--edit-block' ],
			],

			'blocks'     => [
				'title'           => [
					'type'      => 'element',
					'file_path' => 'listing/edit/title',
					'order'     => 10,
				],

				'status'          => [
					'type'      => 'element',
					'file_path' => 'listing/edit/status',
					'order'     => 20,
				],

				'date'            => [
					'type'      => 'element',
					'file_path' => 'listing/edit/date',
					'order'     => 30,
				],

				'actions_primary' => [
					'type'       => 'container',
					'tag'        => 'td',
					'order'      => 40,

					'attributes' => [
						'class' => [ 'hp-listing__actions', 'hp-listing__actions--primary' ],
					],

					'blocks'     => [
						'view_link' => [
							'type'      => 'element',
							'file_path' => 'listing/edit/view-link',
							'order'     => 10,
						],
					],
				],
			],
		],
	],
];
