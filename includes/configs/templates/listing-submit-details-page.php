<?php
/**
 * Listing submit details page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'parent' => 'listing_submit_page',

	'blocks' => [
		'content' => [
			'blocks' => [
				'submit_form' => [
					'type'        => 'form',
					'form_name'   => 'listing_submit',
					'order'       => 10,

					'form_footer' => [
						'actions' => [
							'type'       => 'container',
							'order'      => 10,

							'attributes' => [
								'class' => [ 'hp-form__actions' ],
							],

							'blocks'     => [
								'category_link' => [
									'type'      => 'element',
									'file_path' => 'listing-category/submit/change-link',
									'order'     => 10,
								],
							],
						],
					],
				],
			],
		],
	],
];
