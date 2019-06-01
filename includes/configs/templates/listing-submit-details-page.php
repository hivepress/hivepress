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
		'page_content' => [
			'blocks' => [
				'listing_submit_form' => [
					'type'   => 'form',
					'form'   => 'listing_submit',
					'order'  => 10,

					'footer' => [
						'form_actions' => [
							'type'       => 'container',
							'order'      => 10,

							'attributes' => [
								'class' => [ 'hp-form__actions' ],
							],

							'blocks'     => [
								'listing_category_change_link' => [
									'type'     => 'element',
									'filepath' => 'listing-category/submit/page/change-link',
									'order'    => 10,
								],
							],
						],
					],
				],
			],
		],
	],
];
