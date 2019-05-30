<?php
/**
 * Header block template.
 *
 * @package HivePress\Configs\Templates
 */

use HivePress\Helpers as hp;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

return [
	'blocks' => [
		'main_menu' => [
			'type'       => 'container',
			'order'      => 10,

			'attributes' => [
				'class' => [ 'hp-menu', 'hp-menu--main' ],
			],

			'blocks'     => [
				'user_account_link'     => [
					'type'     => 'element',
					'filepath' => 'user/account-link',
					'order'    => 10,
				],

				'listing_submit_button' => [
					'type'     => 'element',
					'filepath' => 'listing/submit-button',
					'order'    => 20,
				],
			],
		],
	],
];
