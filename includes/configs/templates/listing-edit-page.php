<?php
/**
 * Listing edit page template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'parent' => 'account_page',

	'blocks' => [
		'content' => [
			'blocks' => [
				'listing_delete_modal' => [
					'type'        => 'modal',
					'caption' => esc_html__( 'Delete Listing', 'hivepress' ),
					'order'       => 5,

					'blocks'      => [
						'delete_form' => [
							'type'       => 'form',
							'form'  => 'listing_delete',
							'order'      => 10,

							'attributes' => [
								'class' => [ 'hp-form--narrow' ],
							],
						],
					],
				],

				'update_form'          => [
					'type'        => 'form',
					'form'   => 'listing_update',
					'order'       => 10,

					'footer' => [
						'actions' => [
							'type'       => 'container',
							'order'      => 10,

							'attributes' => [
								'class' => [ 'hp-form__actions' ],
							],

							'blocks'     => [
								'delete_link' => [
									'type'      => 'element',
									'filepath' => 'listing/edit/delete-link',
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
