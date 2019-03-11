<?php
/**
 * Listing page template.
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
			'order'      => 10,

			'attributes' => [
				'attributes' => [
					'class' => [ 'hp-listing', 'hp-listing--page' ],
				],
			],

			'blocks'     => [
				'content' => [
					'type'       => 'container',
					'order'      => 10,

					'attributes' => [
						'attributes' => [
							'class' => [ 'hp-listing__content' ],
						],
					],
				],
			],
		],
	],
];
